<?php
/**
 * 维修售后
 */
namespace backend\components;

use Yii ;
use yii\base\Exception;
use yii\db\Expression;

use common\models\PushTask;
use common\models\TradeInApply;
use common\models\Areas;
use common\models\TradeInAfterMarketDoc;
use backend\components\PushTaskCommon;



class PushTaskSfExpress extends PushTaskCommon
{
    /**
     * 维修推送任务
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($push_task_status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('SFEXPRESS', $push_task_status);
        if(!$push_task_list){
            return false;
        }
        
        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);

        /*** 业务相关数据(需要根据不同的业务而定) start ***/
        $trade_in_apply_model = new TradeInApply();

        $trade_in_apply_push_task_list  = $trade_in_apply_model->getNotSendListById($business_ids,
            'TOSEND',
            'id,trade_in_id,user_id,username,contact,mobile,province,city,area,address,sn,apply_goods_name,apply_materiel_no,estimated_price,act_estimated_price,note,status,freight_code,order_id'
        );
        if(!$trade_in_apply_push_task_list){
            return false;
        }
        /*** 业务相关数据 end ***/

        // 数据处理 追加 push_task_id,push_url 字段 (不用动)
        foreach($push_task_list as $row){
            if(isset($trade_in_apply_push_task_list[$row['business_id']])){
                $trade_in_apply_push_task_list[$row['business_id']]['push_task_id'] = $row['id'];
                // 快递比较特殊push_url暂时不用
                $trade_in_apply_push_task_list[$row['business_id']]['push_url'] = $row['push_url'];
            }
        }

        // 循环推送
        foreach($trade_in_apply_push_task_list as $row){
            $this->pushDataToSf($row);
        }
    }
    
    /**
     * 推送顺丰物流信息
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToSf($trade_in_apply_data){

        // 任务id sdb_push_task 主键
        $push_task_id = $trade_in_apply_data['push_task_id'];
    
        $trade_in_apply_model = new TradeInApply();
    
        $push_task_model = new PushTask();
    
        $now_time = date("Y-m-d H:i:s");
    
        $areas_obj = new Areas();
        $province_name = $areas_obj->getAreaName($trade_in_apply_data['province']);
        $city_name = $areas_obj->getAreaName($trade_in_apply_data['city']);
        $area_name = $areas_obj->getAreaName($trade_in_apply_data['area']);
        $address = $province_name.$city_name.$area_name.$trade_in_apply_data['address'];
    
        $params = ['apply_id'=>$trade_in_apply_data['id'],'userId'=>$trade_in_apply_data['user_id'],'shipperContact'=>$trade_in_apply_data['contact'],'shipperMobile'=>$trade_in_apply_data['mobile'],'shipperAddress'=>$address];
        $params['goods'] = ['0'=>['name'=>$trade_in_apply_data['apply_goods_name']]];
    
        // 逆向物流订单接口
        $delivery_result = Yii::$app->SfExpress->reverseExpressOrder($params);

        // 发快递失败
        if (!$delivery_result || $delivery_result['isSucceed'] != 1) {
            // 更新以旧换新物流订单任务状态为失败
            $push_task_model->updateInfo($push_task_id, ['status' => 'FAILED', 'modify_time' => $now_time]);
            
            return false;
        }
    
        $transaction = Yii::$app->db->beginTransaction();
        try {
        
            $delivery_msg = json_decode($delivery_result['errorMsg'], true);
    
            $delivery_update = array(
                'freight_code'  => 'SF',
                'delivery_code' => $delivery_msg['waybillNo'],
                'status'        => 'TORECEIVE',
                'update_time'   => $now_time
            );
    
            // 更新以旧换新申请单
            $trade_in_apply_model->updateData($trade_in_apply_data['id'], $delivery_update);
    
            // 更新以旧换新物流订单任务状态
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED', 'modify_time' => $now_time]);
    
            // 创建以旧换新任务工单
            $trade_in_after_market_doc_model = new TradeInAfterMarketDoc();
            // 合并数组
            $trade_in_after_market_doc_data = array_merge($trade_in_apply_data, $delivery_update);
    
            $trade_in_after_market_doc_res = $trade_in_after_market_doc_model->createDoc($trade_in_after_market_doc_data);
            if($trade_in_after_market_doc_res['code'] != '1'){
                Yii::$app->CommonLogger->logErrorConsole("创建以旧换新售后单失败：".json_encode(['code' => $trade_in_after_market_doc_res['code'], 'msg'=>getErrorDictMsg($trade_in_after_market_doc_res['code'])]), 'aftermarket');
                
                //回滚事务
                $transaction->rollback();
    
                return true;
            }
    
            // 提交事务
            $transaction->commit();
    
            return true;

        }catch (Exception $e) {
    
            //回滚事务
            $transaction->rollback();
    
            // 更新以旧换新物流订单任务状态为失败
            $push_task_model->updateInfo($push_task_id, ['status' => 'FAILED', 'modify_time' => $now_time]);
    
            return false;
        }
    }
 
}
