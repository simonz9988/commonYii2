<?php
/**
 * 退款
 * @date 2018-05-24
 */
namespace backend\components;

use Yii;
use common\models\Payment;
use common\models\RefundProcess;
use common\models\User;
use backend\extensions\alipay_trade\alipayTrade;
use backend\extensions\weixin\weixin;
use backend\extensions\app_weixin\appWeixin;
use backend\extensions\weixinmini\weixinmini;
use backend\extensions\cmb_life\cmbLife;
use backend\extensions\ccb_installment\ccbInstallment;
use backend\extensions\globalapp_alipay_trade\globalAppAlipayTrade;
use backend\extensions\globalapp_weixin\globalAppWeixin;

class Refund
{
    // 渠道(MALL:商城)
    private $channel = [1 => 'MALL'];
    
    // 允许的退款类型
    private $allow_refund_type = ['direct_alipay', 'wap_alipay', 'wechat', 'weixin', 'app_weixin', 'wap_weixin', 'weixin_mini', 'wap_cmb_life', 'cmb_life', 'wap_ccb_installment', 'ccb_installment', 'globalapp_wechat', 'globalapp_alipay'];
    
    // 重复退款锁KEY
    private $repeat_refund_key = 'Mall:Repeat:Refund:Lock:';

    /**
     * 执行退款
     *
     * @param  array  $refund_params    参数
     *         array(
                'payment_id'    => 1                   支付类型id，允许范围：（1-99）
                'business_id'   => 999                 业务id（当前使用的是售后单号），最大允许10位的数字
                'total_amount'  => 999                 当前订单总金额
                'refund_amount' => 9.99                退款金额
                'trade_no'      => '2018052122226557'  支付交易单号
                'pay_order_no'  => '120077408110'      支付单号
                'channel'       => 'MALL'              渠道（MALL：商城）
     *          'refund_reason' => ''                  退款原因
              );
     * @return array
     *
     *
     */
    public function doRefund($refund_params){

        // 验证参数正确性
        $check_res = $this->checkParams($refund_params);
        if($check_res['code'] != 1){
            return $check_res;
        }
        
        // 支付类型id
        $payment_id    = $refund_params['payment_id'];
        // 业务id
        $business_id   = $refund_params['business_id'];
        // 渠道
        $channel       = $refund_params['channel'];
        // 支付交易单号
        $trade_no      = $refund_params['trade_no'];
        // 订单总金额
        $total_amount  = $refund_params['total_amount'];
        // 退款金额
        $refund_amount = $refund_params['refund_amount'];
    
        // 组装日志数据
        $log_data = array(
            'class_name'    => __CLASS__,
            'function_name' => __FUNCTION__,
            'action'        => '退款请求参数',
            'redundancy_id' => $trade_no,
            'old_content'   => [],
            'new_content'   => $refund_params
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
        
        $payment_model = new Payment();
        // 查询支付方式
        $payment_info = $payment_model->getRowInfoByWhere(['id' => $payment_id], 'id,class_name,config_param');
        if(!$payment_info){
            return ['code' => 702002, 'msg' => getErrorDictMsg(702002)];
        }

        // 不允许自动退款的类型
        if(!in_array($payment_info['class_name'], $this->allow_refund_type)){
            return ['code' => 410008, 'msg' => getErrorDictMsg(410008)];
        }
        
        // 防止重复退款锁
        $redis_key = $this->repeat_refund_key.$business_id.'_'.$trade_no.'_'.$channel;
        $repeat_refund_info = Yii::$app->MyRedis->get($redis_key);
        if($repeat_refund_info){
            return ['code' => 410015, 'msg' => getErrorDictMsg(410015)];
        }else{
            // 设置防止重复退款锁（默认20秒）
            Yii::$app->MyRedis->set($redis_key, 1, 30);
        }

        $refund_process_model = new RefundProcess();
        
        /*
         * 退款状态信息
         * TODO 退款信息不存在生成退款单号，存在且未成功拿当前退款单号，已成功直接返回成功
         */
        $refund_status_info = $refund_process_model->getInfoByBusinessId($business_id, $trade_no, $channel, 'id,refund_trade_no,refund_amount,status');

        // 是否退款查询
        $is_refund_query = false;

        if(!$refund_status_info){
            // 建行分期由于退款流水号字段限定太短，不能使用现有方法, 使用售后id作为退款流水号
            if($payment_info['class_name'] == 'wap_ccb_installment' || $payment_info['class_name'] == 'ccb_installment'){
                $refund_trade_no = $this->createCcbRefundTradeNo($refund_params);
            }else{
                // 生成退款单号
                $refund_trade_no = $this->createRefundTradeNo($refund_params);
            }
            
            // 保存退款信息
            $refund_status_id = $refund_process_model->saveInfo($business_id, $trade_no, $refund_trade_no, $refund_amount, $channel);
            if(!$refund_status_id){
                return ['code' => 410007, 'msg' => getErrorDictMsg(410007)];
            }
        }else{
            // 金额不一致返回错误
            if($refund_amount != $refund_status_info['refund_amount']){
                return ['code' => 410006, 'msg' => getErrorDictMsg(410006)];
            }
            
            // 成功
            if($refund_status_info['status'] == 'SUCCESS'){
                return ['code' => 1, 'msg' => getErrorDictMsg(1)];
            }else{
                $refund_trade_no = $refund_status_info['refund_trade_no'];
                $is_refund_query = true;
            }
    
            $refund_status_id = $refund_status_info['id'];
        }

        // 支付配置信息
        $config_param = json_decode($payment_info['config_param'], true);

        $res = [];
        
        // 支付宝退款
        if($payment_info['class_name'] == 'direct_alipay' || $payment_info['class_name'] == 'wap_alipay' ){
           
            // 执行退款
            $res = $this->alipayRefundProcess($config_param, $refund_params, $refund_trade_no, $is_refund_query);
            
        }
        // 微信退款
        elseif ($payment_info['class_name'] == 'wechat' || $payment_info['class_name'] == 'weixin' || $payment_info['class_name'] == 'wap_weixin'){
           
            // 执行退款
            $res = $this->weixinRefundProcess($refund_params, $refund_trade_no, $is_refund_query);
        }
        // 微信小程序退款
        elseif ($payment_info['class_name'] == 'weixin_mini'){
    
            // 执行退款
            $res = $this->weixinminiRefundProcess($refund_params, $refund_trade_no, $is_refund_query);
        }
        // APP微信退款
        elseif ($payment_info['class_name'] == 'app_weixin'){
    
            // 执行退款
            $res = $this->appWeixinRefundProcess($refund_params, $refund_trade_no, $is_refund_query);
        }
        // 招行掌上生活分期退款
        elseif ($payment_info['class_name'] == 'wap_cmb_life' || $payment_info['class_name'] == 'cmb_life'){
        
            // 执行退款
            $res = $this->cmbLifeRefundProcess($config_param, $refund_params, $refund_trade_no, $is_refund_query);
        }
        // 建设银行分期退款
        elseif ($payment_info['class_name'] == 'ccb_installment' || $payment_info['class_name'] == 'wap_ccb_installment'){
    
            // 执行退款
            $res = $this->ccbInstallmentRefundProcess($config_param, $refund_params, $refund_trade_no, $is_refund_query);
        }
        // globalApp支付宝退款
        elseif($payment_info['class_name'] == 'globalapp_alipay'){
        
            // 执行退款
            $res = $this->globalAppAlipayRefundProcess($config_param, $refund_params, $refund_trade_no, $is_refund_query);
        
        }
        // globalApp微信退款
        elseif ($payment_info['class_name'] == 'globalapp_wechat'){
    
            // 执行退款
            $res = $this->globalAppWeixinRefundProcess($refund_params, $refund_trade_no, $is_refund_query);
        }
        
        // 返回信息处理
        if($res && $res['code'] == 1){
            // 针对招行分期的特别处理
            if(($payment_info['class_name'] == 'wap_cmb_life' || $payment_info['class_name'] == 'cmb_life') && isset($res['data']['status'])){
                
                // 针对招行掌上生活分期处理：状态 1：待退款 2：退款成功 3：退款失败 4：退款未知
                if($res['data']['status'] == 2){
                    // 更新退款状态为成功
                    $update_res = $refund_process_model->updateInfo($refund_status_id, 'SUCCESS');
                }else{
                    // 更新退款状态为失败
                    $update_res = $refund_process_model->updateInfo($refund_status_id, 'FAILED');
                }
            }else{
                
                // 更新退款状态为成功
                $update_res = $refund_process_model->updateInfo($refund_status_id, 'SUCCESS');
            }
            
        }else{
            // 更新退款状态为失败
            $update_res = $refund_process_model->updateInfo($refund_status_id, 'FAILED');
        }

        // 更新失败记录报错日志
        if(!$update_res){

            $error_info = [
                'request'  => $refund_params,
                'response' => $res,
            ];
            Yii::$app->CommonLogger->logError("退款更新状态失败：".json_encode($error_info,JSON_UNESCAPED_UNICODE));
        }
    
        return $res;
    }
    
    /**
     * 验证参数的正确性
     * @param  array  $refund_params     退款参数
     * @return array
     */
    private function checkParams($refund_params){
        // 业务单号
        if(!isset($refund_params['business_id']) || strlen($refund_params['business_id']) > 10){
            return ['code' => 410001, 'msg' => getErrorDictMsg(410001)];
        }
    
        // 渠道
        if(!in_array($refund_params['channel'], $this->channel)){
            return ['code' => 410002, 'msg' => getErrorDictMsg(410002)];
        }
    
        // 退款金额
        if (!preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $refund_params['refund_amount'])) {
            return ['code' => 410005, 'msg' => getErrorDictMsg(410005)];
        }
    
        // 支付单号
        if (!preg_match('/^\d{10,20}$/', $refund_params['pay_order_no'])) {
            return ['code' => 410010, 'msg' => getErrorDictMsg(410010)];
        }
        
        return ['code' => 1, 'msg' => getErrorDictMsg(1)];
    }
    
    /**
     * 生成退款单号
     * 标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
     * 生成规则：（订单号：120077408110（15位不足右侧补0） + 渠道：长度两位不足前面补0（01） + 支付方式id:长度两位不足前面补0（01） + 业务单号:长度10位不足前面补0） + 秒 + 毫秒
     * @param  array  $refund_params     退款参数
     * @return string
     */
    private function createRefundTradeNo($refund_params){
        // 获取渠道对应键值
        $channel = array_search($refund_params['channel'], $this->channel);

        return str_pad($refund_params['pay_order_no'],20,'0',STR_PAD_RIGHT).str_pad($channel,2,'0',STR_PAD_LEFT).str_pad($refund_params['payment_id'],2,'0',STR_PAD_LEFT).str_pad($refund_params['business_id'],10,'0',STR_PAD_LEFT);
    }
    
    /**
     * 生成建行分期退款单号
     * 标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传。
     * 生成规则：业务单号:长度10位不足前面补0）
     * @param  array  $refund_params     退款参数
     * @return string
     */
    private function createCcbRefundTradeNo($refund_params){
        return str_pad($refund_params['business_id'],10,'0',STR_PAD_LEFT);
    }
    
    /**
     * 支付宝退款处理
     * @param  array  $config_param      配置信息
     * @param  array  $refund_params     退款参数
     * @param  string $refund_trade_no   退款单号
     * @param  bool   $is_refund_query   是否查询退款信息
     * @return array
     */
    private function alipayRefundProcess($config_param, $refund_params, $refund_trade_no, $is_refund_query){
        
        // 支付宝相关
        $alipay_trade = new alipayTrade();
    
        // 设置参数
        $alipay_trade->setConfig($config_param);
        
        // 查询退款状态
        if($is_refund_query){
            $refund_query_res = $alipay_trade->refundQuery($refund_params['trade_no'], $refund_trade_no, '');
            
            // 查询到已退款直接返回成功
            if($refund_query_res && $refund_query_res['code'] == '10000' && $refund_query_res['refund_amount'] > 0){
        
                return ['code' => 1, 'msg' => getErrorDictMsg(1)];
        
            }
        }
    
        // 执行退款
        $refund_res = $alipay_trade->refund($refund_params['trade_no'], $refund_params['refund_amount'], $refund_trade_no, $refund_params['refund_reason'], '');

        // 返回信息处理
        if($refund_res && $refund_res['code'] == '10000'){
        
            return ['code' => 1, 'msg' => getErrorDictMsg(1)];
        
        }else{
        
            return ['code' => 410003, 'msg' => json_encode($refund_res, JSON_UNESCAPED_UNICODE)];
        }
    }

    /**
     * 微信退款处理
     * @param  array  $refund_params     退款参数
     * @param  string $refund_trade_no   退款单号
     * @param  bool   $is_refund_query   是否查询退款信息
     * @return array
     */
    private function weixinRefundProcess($refund_params, $refund_trade_no, $is_refund_query){
        
        // 微信相关
        $weixin = new weixin();
    
        // 查询退款状态
        if($is_refund_query){
            $refund_query_res = $weixin->refundQuery($refund_params['trade_no'], $refund_trade_no);

            // 查询到已退款直接返回成功
            if($refund_query_res && $refund_query_res['return_code'] == 'SUCCESS' && $refund_query_res['result_code'] == 'SUCCESS' && $refund_query_res['refund_status_0'] == 'SUCCESS'){
            
                return ['code' => 1, 'msg' => getErrorDictMsg(1)];
            
            }
            // 当数据返回 为 FAIL 时抛出错误
            elseif(!$refund_query_res || $refund_query_res['return_code'] == 'FAIL'){
                return ['code' => 410016, 'msg' => json_encode($refund_query_res, JSON_UNESCAPED_UNICODE)];
            }
        }

        // 执行退款
        $res = $weixin->refund($refund_params['trade_no'], $refund_params['total_amount'], $refund_params['refund_amount'], $refund_trade_no, $refund_params['refund_reason']);
        
        // 返回信息处理
        if($res && $res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS'){
            
            return ['code' => 1, 'msg' => getErrorDictMsg(1)];
            
        }else{
            
            return ['code' => 410004, 'msg' => json_encode($res, JSON_UNESCAPED_UNICODE)];
        }
    }
    
    /**
     * 微信小程序退款处理
     * @param  array  $refund_params     退款参数
     * @param  string $refund_trade_no   退款单号
     * @param  bool   $is_refund_query   是否查询退款信息
     * @return array
     */
    private function weixinminiRefundProcess($refund_params, $refund_trade_no, $is_refund_query){
        
        // 微信相关
        $weixinmini = new weixinmini();
        
        // 查询退款状态
        if($is_refund_query){
            $refund_query_res = $weixinmini->refundQuery($refund_params['trade_no'], $refund_trade_no);
            
            // 查询到已退款直接返回成功
            if($refund_query_res && $refund_query_res['return_code'] == 'SUCCESS' && $refund_query_res['result_code'] == 'SUCCESS' && $refund_query_res['refund_status_0'] == 'SUCCESS'){
                
                return ['code' => 1, 'msg' => getErrorDictMsg(1)];
                
            }
            // 当数据返回 为 FAIL 时抛出错误
            elseif(!$refund_query_res || $refund_query_res['return_code'] == 'FAIL'){
                return ['code' => 410017, 'msg' => json_encode($refund_query_res, JSON_UNESCAPED_UNICODE)];
            }
        }
        
        // 执行退款
        $res = $weixinmini->refund($refund_params['trade_no'], $refund_params['total_amount'], $refund_params['refund_amount'], $refund_trade_no, $refund_params['refund_reason']);
        
        // 返回信息处理
        if($res && $res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS'){
            
            return ['code' => 1, 'msg' => getErrorDictMsg(1)];
            
        }else{
            
            return ['code' => 410004, 'msg' => json_encode($res, JSON_UNESCAPED_UNICODE)];
        }
    }
    
    /**
     * APP微信退款处理
     * @param  array  $refund_params     退款参数
     * @param  string $refund_trade_no   退款单号
     * @param  bool   $is_refund_query   是否查询退款信息
     * @return array
     */
    private function appWeixinRefundProcess($refund_params, $refund_trade_no, $is_refund_query){
        
        // 微信相关
        $app_weixin = new appWeixin();
        
        // 查询退款状态
        if($is_refund_query){
            $refund_query_res = $app_weixin->refundQuery($refund_params['trade_no'], $refund_trade_no);
            
            // 查询到已退款直接返回成功
            if($refund_query_res && $refund_query_res['return_code'] == 'SUCCESS' && $refund_query_res['result_code'] == 'SUCCESS' && $refund_query_res['refund_status_0'] == 'SUCCESS'){
                
                return ['code' => 1, 'msg' => getErrorDictMsg(1)];
                
            }
            // 当数据返回 为 FAIL 时抛出错误
            elseif(!$refund_query_res || $refund_query_res['return_code'] == 'FAIL'){
                return ['code' => 410018, 'msg' => json_encode($refund_query_res, JSON_UNESCAPED_UNICODE)];
            }
        }
        
        // 执行退款
        $res = $app_weixin->refund($refund_params['trade_no'], $refund_params['total_amount'], $refund_params['refund_amount'], $refund_trade_no, $refund_params['refund_reason']);
        
        // 返回信息处理
        if($res && $res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS'){
            
            return ['code' => 1, 'msg' => getErrorDictMsg(1)];
            
        }else{
            
            return ['code' => 410009, 'msg' => json_encode($res, JSON_UNESCAPED_UNICODE)];
        }
    }
    
    /**
     * 招行掌上生活分期退款
     * @param  array  $config_param      配置信息
     * @param  array  $refund_params     退款参数
     * @param  string $refund_trade_no   退款单号
     * @param  bool   $is_refund_query   是否查询退款信息
     * @return array
     */
    private function cmbLifeRefundProcess($config_param, $refund_params, $refund_trade_no, $is_refund_query){
        
        // 招行掌上生活分期
        $cmb_life = new cmbLife();
    
        // 第一次请求需要发送短信
        if($is_refund_query == false){
            $user_id = $refund_params['user_id'];
        
            // 招行分期未在指定时间内退款的短信告知用户
            $user_model = new User();
            $user_info = $user_model->getUserInfoById($user_id, "mobile");
            $msg = '尊敬的会员：您提交的退款申请正在审核中，退款金额'.$refund_params['refund_amount'].'元将在15个工作日内退还至原付款账户，去查看：http://t.cn/EZPJUnn，回T退订';
            Yii::$app->SMS->send($user_info['mobile'], $msg);
        }

        // 支付T日不支持部分退款，T+1日起支持部分退款,时间必须超过24小时才能进行退款
        if($refund_params['refund_amount'] < $refund_params['total_amount'] && strtotime($refund_params['create_time']) > strtotime("-24 hour")){
            return ['code' => 410011, 'msg' => '部分退款，T+1日起支持部分退款,时间必须超过24小时才能进行退款(该错误不用做处理)'];
        }
        
        // 设置参数
        $cmb_life->setConfig($config_param);
        
        // 查询退款状态
        if($is_refund_query){
            $refund_query_res = $cmb_life->refundQuery($refund_params['pay_order_no'], $refund_trade_no);

            // 查询到已退款直接返回成功
            if($refund_query_res && $refund_query_res['respCode'] == '1000'){
                $refund_status = (int)$refund_query_res['refundStatus'];
                
                // 0:为未找到退款订单,1：待退款，2:为成功，3:为失败，4:为未知
                if($refund_status == 2){
                    return ['code' => 1, 'msg' => getErrorDictMsg(1), 'data' => ['status' => $refund_status]];
                }
                
            }
            // 当数据返回 respCode 不等于 1000 时抛出错误
            elseif(!$refund_query_res || $refund_query_res['respCode'] != '1000'){
                return ['code' => 410019, 'msg' => json_encode($refund_query_res, JSON_UNESCAPED_UNICODE)];
            }
        }
        
        // 执行退款
        $res = $cmb_life->refund($refund_params['pay_order_no'], $refund_params['refund_amount'], $refund_trade_no);
        
        // 返回信息处理
        if($res && $res['respCode'] == '1000' && $res['refundStatus'] == 2){
            
            return ['code' => 1, 'msg' => getErrorDictMsg(1), 'data' => ['status' => (int)$res['refundStatus']]];
            
        }else{
            
            return ['code' => 410011, 'msg' => json_encode($res, JSON_UNESCAPED_UNICODE)];
        }
    }
    
    /**
     * 建设银行分期退款
     * @param  array  $config_param      配置信息
     * @param  array  $refund_params     退款参数
     * @param  string $refund_trade_no   退款单号
     * @param  bool   $is_refund_query   是否查询退款信息
     * @return array
     */
    private function ccbInstallmentRefundProcess($config_param, $refund_params, $refund_trade_no, $is_refund_query){
        
        // 建行分期
        $ccb_installment = new ccbInstallment();
        
        // 设置参数
        $ccb_installment->setConfig($config_param);
    
        // 生成请求序列号以毫秒时间戳形式
        $request_sn = $ccb_installment->msectime();

        // 查询退款状态
        if($is_refund_query){
            $refund_query_res = $ccb_installment->refundQuery($refund_params['pay_order_no'], $request_sn);
            
            // 查询到已退款直接返回成功
            if($refund_query_res && $refund_query_res['RETURN_CODE'] == '000000'){
                $refund_query_list = $refund_query_res['TX_INFO']['LIST'];
                // 判断是否是一维数组
                if(count($refund_query_list) == count($refund_query_list, 1)){
                    // STATUS:0:失败,1:成功,2:待银行确认,5:待银行确认
                    if($refund_query_list['STATUS'] == '1' && $refund_query_list['REFUND_CODE'] == $refund_trade_no){
                        return ['code' => 1, 'msg' => getErrorDictMsg(1), 'data' => ['status' => $refund_query_list['STATUS']]];
                    }
                }else{
                    foreach($refund_query_list as $row){
                        // STATUS:0:失败,1:成功,2:待银行确认,5:待银行确认
                        if($row['STATUS'] == '1' && $row['REFUND_CODE'] == $refund_trade_no){
                            return ['code' => 1, 'msg' => getErrorDictMsg(1), 'data' => ['status' => $row['STATUS']]];
                        }
                    }
                }
            }
            // 当没有返回数据
            elseif(!$refund_query_res){
                return ['code' => 410020, 'msg' => json_encode($refund_query_res, JSON_UNESCAPED_UNICODE)];
            }
        }
        
        // 执行退款
        $res = $ccb_installment->refund($refund_params['pay_order_no'], $refund_params['refund_amount'], $refund_trade_no, $request_sn);
        
        // 返回信息处理
        if($res && $res['RETURN_CODE'] == '000000' && $res['TX_INFO']['AMOUNT'] == $refund_params['refund_amount']){
            
            return ['code' => 1, 'msg' => getErrorDictMsg(1), 'data' => ['status' => $res['RETURN_CODE']]];
            
        }else{
            
            return ['code' => 410021, 'msg' => json_encode($res, JSON_UNESCAPED_UNICODE)];
        }
    }
    
    /**
     * globalApp支付宝退款处理
     * @param  array  $config_param      配置信息
     * @param  array  $refund_params     退款参数
     * @param  string $refund_trade_no   退款单号
     * @param  bool   $is_refund_query   是否查询退款信息
     * @return array
     */
    private function globalAppAlipayRefundProcess($config_param, $refund_params, $refund_trade_no, $is_refund_query){
        
        // 支付宝相关
        $alipay_trade = new globalAppAlipayTrade();
        
        // 设置参数
        $alipay_trade->setConfig($config_param);
        
        // 查询退款状态
        if($is_refund_query){
            $refund_query_res = $alipay_trade->refundQuery($refund_params['trade_no'], $refund_trade_no, '');
            
            // 查询到已退款直接返回成功
            if($refund_query_res && $refund_query_res['code'] == '10000' && $refund_query_res['refund_amount'] > 0){
                
                return ['code' => 1, 'msg' => getErrorDictMsg(1)];
                
            }
        }
        
        // 执行退款
        $refund_res = $alipay_trade->refund($refund_params['trade_no'], $refund_params['refund_amount'], $refund_trade_no, $refund_params['refund_reason'], '');
        
        // 返回信息处理
        if($refund_res && $refund_res['code'] == '10000'){
            
            return ['code' => 1, 'msg' => getErrorDictMsg(1)];
            
        }else{
            
            return ['code' => 410003, 'msg' => json_encode($refund_res, JSON_UNESCAPED_UNICODE)];
        }
    }
    
    /**
     * globalApp微信退款处理
     * @param  array  $refund_params     退款参数
     * @param  string $refund_trade_no   退款单号
     * @param  bool   $is_refund_query   是否查询退款信息
     * @return array
     */
    private function globalAppWeixinRefundProcess($refund_params, $refund_trade_no, $is_refund_query){
        
        // 微信相关
        $app_weixin = new globalAppWeixin();
        
        // 查询退款状态
        if($is_refund_query){
            $refund_query_res = $app_weixin->refundQuery($refund_params['trade_no'], $refund_trade_no);
            
            // 查询到已退款直接返回成功
            if($refund_query_res && $refund_query_res['return_code'] == 'SUCCESS' && $refund_query_res['result_code'] == 'SUCCESS' && $refund_query_res['refund_status_0'] == 'SUCCESS'){
                
                return ['code' => 1, 'msg' => getErrorDictMsg(1)];
                
            }
            // 当数据返回 为 FAIL 时抛出错误
            elseif(!$refund_query_res || $refund_query_res['return_code'] == 'FAIL'){
                return ['code' => 410018, 'msg' => json_encode($refund_query_res, JSON_UNESCAPED_UNICODE)];
            }
        }
        
        // 执行退款
        $res = $app_weixin->refund($refund_params['trade_no'], $refund_params['total_amount'], $refund_params['refund_amount'], $refund_trade_no, $refund_params['refund_reason']);
        
        // 返回信息处理
        if($res && $res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS'){
            
            return ['code' => 1, 'msg' => getErrorDictMsg(1)];
            
        }else{
            
            return ['code' => 410009, 'msg' => json_encode($res, JSON_UNESCAPED_UNICODE)];
        }
    }
}