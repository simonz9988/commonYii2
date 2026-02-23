<?php
/**
 * 以旧换新售后
 */
namespace backend\components;

use common\models\Order;
use Yii ;
use yii\base\Exception;
use yii\db\Expression;

use common\models\PushTask;
use common\models\Areas;
use common\models\TradeInApply;
use common\models\TradeInAfterMarketDoc;
use backend\components\PushTaskCommon;


class PushTaskTradeIn extends PushTaskCommon
{
    /**
     * 以旧换新推送任务
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($push_task_status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('TRADEIN', $push_task_status);
        if(!$push_task_list){
            return false;
        }
        
        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);
        
        /*** 业务相关数据(需要根据不同的业务而定) start ***/
        $trade_in_after_market_doc_model = new TradeInAfterMarketDoc();
        $trade_in_after_market_list = $trade_in_after_market_doc_model->getTradeInAfterMarketDocListById($business_ids,
            'id,user_id,username,trade_in_id,trade_in_apply_id,apply_goods_name,apply_materiel_no,estimated_price,sn,delivery_code,freight_code,pay_type,contact,mobile,province,city,area,address'
        );
        if(!$trade_in_after_market_list){
            return false;
        }
        /*** 业务相关数据 end ***/

        // 数据处理 追加 push_task_id,push_url 字段 (不用动)
        foreach($push_task_list as $row){
            if(isset($trade_in_after_market_list[$row['business_id']])){
                $trade_in_after_market_list[$row['business_id']]['push_task_id'] = $row['id'];
                $trade_in_after_market_list[$row['business_id']]['push_url'] = $row['push_url'];
            }
        }

        // 循环推送维修信息到售后中心 (不用动)
        foreach($trade_in_after_market_list as $row){
            $this->pushDataToAsm($row);
        }
    }
    
    /**
     * 发送售后单到售后中心
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToAsm($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];
        
        $push_task_model = new PushTask();
    
        $now_time = date("Y-m-d H:i:s");
    
        // 推送相关数据处理
        $push_data = $this->getPushData($request_data);

        // 发送售后信息到中台
        $response_data = $this->pushDataToAsmCommon($request_data, $push_data);
        if(!$response_data){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            //售后工单号
            $oms_data   = $response_data['data'] ;
            $asmOrderNo = $oms_data['asmOrderNo'];
            
            // 更新任务为已推送
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED', 'asm_order_no' => $asmOrderNo, 'modify_time' => $now_time]);
            
            // 提交事务
            $transaction->commit();
            
            return true;
        }catch (Exception $e) {
            
            //回滚事务
            $transaction->rollback();

            return false;
        }
    }
    
    /**
     * 获取推送的以旧换新数据
     * @param  array   $trade_in_data
     * @return array
     */
    private function getPushData($trade_in_data){
    
        // 售后编号（以旧换新）
        $after_market_type = 6;
        $type_arr = $this->type_arr;
        
        // 工单类型
        $order_type = $type_arr[$after_market_type];
    
        // 问题描述
        $problemDesc = $this->getProblemDescFromTradeIn($trade_in_data);

        //获取以旧换新申请表信息
        $trade_in_apply_id = $trade_in_data['trade_in_apply_id'];
        $trade_in_apply_model = new TradeInApply();
        $trade_in_apply_params['cond'] = ' id=:id';
        $trade_in_apply_params['args'] = [':id'=>$trade_in_apply_id];
        $trade_in_apply_params['fields'] = 'order_id';
        $trade_in_apply_info = $trade_in_apply_model->findOneByWhere('sdb_trade_in_apply',$trade_in_apply_params);
        $order_id = $trade_in_apply_info['order_id'];

        //获取订单号
        $order_model = new Order();
        $order_params['cond'] = ' id=:id';
        $order_params['args'] = [':id'=>$order_id];
        $order_params['fields'] = 'order_no';
        $order_info = $order_model->findOneByWhere('sdb_order',$order_params);
        $rst['shopNo'] = $this->asmShopNo;
        $rst['originalOrderNo'] = $order_info['order_no'];
        $rst['problemDesc'] = $problemDesc;                 // 问题描述
        $rst['customerNick'] = $trade_in_data['username'];  // 客户名称 对应user表的username
        $rst['customerName'] = $trade_in_data['contact'];   // 顾客姓名
        $rst['customerPhone'] = $trade_in_data['mobile'];   // 顾客手机
        $rst['orderType'] = $order_type;                    // 工单类型
    
        $work_order_callback_url = $this->getWorkOrderCallbackUrlFromTradeIn();         // 工单关闭回调地址
        $rst['callBackNotifyUrl'] = $work_order_callback_url;                           // 回调地址 工单完成关闭
        $rst['validateCode'] = $this->createValidateCodeFromTradeIn($trade_in_data);    // 发往中台唯一标识符
        $rst['orderSource'] = 'asm_order_source_ecovacs';
        $rst['contactInfo'] = $this->getTradeInContactInfo($trade_in_data);
    
        $order_info = array();
        return $this->formateOmsDataByAftermarketType($rst, $trade_in_data, $order_info) ;

    }
    
    /**
     *获取以旧换新问题描述
     * @param  array   $trade_in_data [description]
     * @return string
     */
    private function getProblemDescFromTradeIn($trade_in_data){
    
        $rst = '';
        if($trade_in_data ){
        
            $rst  = '申请机型：'.$trade_in_data['apply_goods_name'].'，';
            $rst .= '申请物料号：'.$trade_in_data['apply_materiel_no'].'，';
            $rst .= 'SN码：'.$trade_in_data['sn'].'，';
            $rst .= '快递公司：'.$trade_in_data['freight_code'].'，';
            $rst .= '快递单号：'.$trade_in_data['delivery_code'].'，';
            $rst .= '估值：'.$trade_in_data['estimated_price'];
        }
    
        return $rst ;
    }
    
    /**
     * 返回以旧换新联系信息
     * @param   $doc_info  售后单信息
     * @return  string
     */
    private function getTradeInContactInfo($doc_info){
   
        $rst = '';
    
        if($doc_info){
        
            $contact = isset($doc_info['contact'])?$doc_info['contact']:'';
            $mobile = isset($doc_info['mobile'])?$doc_info['mobile']:'';
        
            $province = isset($doc_info['province'])?$doc_info['province']:'';
            $city = isset($doc_info['city'])?$doc_info['city']:'';
            $area = isset($doc_info['area'])?$doc_info['area']:'';
            $area_obj = new Areas();
            $province = $area_obj->getAreaName($province);
            $city = $area_obj->getAreaName($city);
            $area = $area_obj->getAreaName($area);
        
            $address = isset($doc_info['address'])?$doc_info['address']:'';
            $address = $province.$city.$area.$address;
        
            $rst .= '申请人姓名：'.$contact.'，';
            $rst .= '申请人手机：'.$mobile.'，';
            $rst .= '申请人地址：'.$address;
        }
    
        return $rst ;
    }

    /**
     * 获取接单的推送数据
     * @param  string   $asmOrderNo 售后工单号
     * @param  string   $orderMemo 取消原因
     * @param  string   $originalOrderNo 渠道来源单号
     * @return array
     */
    private function getCancelPushData($request_data){

        $asmOrderNo = $request_data['asm_order_no'];
        $orderMemo = '退款取消以旧换新申请';
        $originalOrderNo = $request_data['order_no'];
        $rst['asmOrderNo'] = $asmOrderNo ;
        $rst['orderMemo'] = $orderMemo ;
        $rst['originalOrderNo'] = $originalOrderNo ;

        return $rst ;
    }

    /**
     * 发送售后单到售后中心
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushCancelDataToAsm($request_data){

        $push_task_model = new PushTask();

        $now_time = date("Y-m-d H:i:s");

        // 推送相关数据处理
        $push_data = $this->getCancelPushData($request_data);

        //添加以旧换新标志
        $request_data['is_trade_in'] = true ;
        // 发送售后信息到中台
        $response_data = $this->pushDataToAsmCommon($request_data, $push_data);
        if(!$response_data){
            return false;
        }


        //更新以旧换新状态为已关闭
        $trade_in_apply_id  = $request_data['trade_in_apply_id'];
        $rst = $push_task_model->baseUpdate('sdb_trade_in_apply',['status'=>'CANCEL','update_time'=>$now_time],'id=:id',[':id'=>$trade_in_apply_id]);

        return $rst ;

    }
}
