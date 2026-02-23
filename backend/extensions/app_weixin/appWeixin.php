<?php
/**
 * @copyright Copyright(c) 2017
 * @brief 微信支付接口
 * @author haigang.chen
 * @date 2017-05-24
 * @version 2.0
 * @note
 */

namespace backend\extensions\app_weixin;

use Yii;
use backend\extensions\app_weixin\lib\WxPayApi;
use backend\extensions\app_weixin\lib\WxPayConfig;
use backend\extensions\app_weixin\lib\WxPayRefund;
use backend\extensions\app_weixin\lib\WxPayRefundQuery;
use backend\extensions\app_weixin\lib\WxPayOrderQuery;

// 设置时区
ini_set('date.timezone','Asia/Shanghai');

/**
 * @class alipay_trade
 * @brief 支付宝接口
 */
class appWeixin{
    
    /**
     * 退款
     * @param   string $trade_no         微信支付交易号
     * @param   string $total_amount     订单总金额
     * @param   string $refund_amount    需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
     * @param   string $refund_trade_no  标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
     * @param   string $refund_reason    退款的原因说明
     * @return  array
     */
    public function refund($trade_no, $total_amount, $refund_amount, $refund_trade_no, $refund_reason = '')
    {
        // 请求数据
        $request_data = compact('trade_no', 'total_amount', 'refund_amount', 'refund_trade_no', 'refund_reason');
        
        // 构造参数
        $input = new WxPayRefund();
        $input->SetTransaction_id($trade_no);
        $input->SetTotal_fee($total_amount*100);
        $input->SetRefund_fee($refund_amount*100);
        $input->SetOut_refund_no($refund_trade_no);
        $input->SetRefund_desc($refund_reason);
        $input->SetOp_user_id(WxPayConfig::MCHID);
        $input->SetNotifyUrl(SHOP_URL.'/refund/weixinNotifyCallback');

        $response = WxPayApi::refund($input);

        // 失败记录报警日志
        if(!$response || $response['return_code'] != 'SUCCESS' || $response['result_code'] != 'SUCCESS'){
            $error_info = [
                'request'  => $request_data,
                'response' => $response,
            ];
            Yii::$app->CommonLogger->logError("APP微信退款返回失败：".json_encode($error_info,JSON_UNESCAPED_UNICODE));
        }
        
        // 组装日志数据
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'APP微信退款返回信息',
            'redundancy_id' => $trade_no,
            'old_content' => [],
            'new_content' => $response
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
        
        return $response;
    }
    
    /**
     * 退款查询
     * @param   string $trade_no         微信支付交易号
     * @param   string $refund_trade_no  标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
     * @return  array
     */
    public function refundQuery($trade_no, $refund_trade_no)
    {
        
        // 构造参数
        $input = new WxPayRefundQuery();
        $input->SetOut_refund_no($refund_trade_no);
        $response = WxPayApi::refundQuery($input);

        // 组装日志数据
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'APP微信退款查询返回信息',
            'redundancy_id' => $trade_no,
            'old_content' => [],
            'new_content' => $response
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
        
        return $response;
    }
    
    /**
     * 订单查询
     * @param   string $pay_order_no  商户订单号
     * @return  array
     */
    public function orderQuery($pay_order_no)
    {
        require(__DIR__ . '/lib/WxPayDataBase.php');
        
        // 构造参数
        $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($pay_order_no);
        return WxPayApi::orderQuery($input);
    }
}

