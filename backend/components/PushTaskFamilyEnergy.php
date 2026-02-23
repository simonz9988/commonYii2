<?php
/**
 * 同事圈能量操作相关推送
 */
namespace backend\components;

use common\models\Goods;
use common\models\OrderFamilyEnergy;
use Yii ;
use yii\base\Exception;

use common\models\PushTask;


class PushTaskFamilyEnergy extends PushTaskCommon
{

    /**
     * 取消能量推送任务
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($push_task_status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('CANCEL_FAMILY_ENERGY', $push_task_status);
        if(!$push_task_list){
            return false;
        }

        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);
        
        /*** 业务相关数据(需要根据不同的业务而定) start ***/
        $energy_model = new OrderFamilyEnergy();
        $fields = '*';
        $energy_push_task_list = $energy_model->getTaskList($business_ids,$fields);
        if(!$energy_push_task_list){
            return false;
        }
        /*** 业务相关数据 end ***/

        // 数据处理 追加 push_task_id,push_url 字段
        foreach($push_task_list as $row){
            if(isset($energy_push_task_list[$row['business_id']])){
                $energy_push_task_list[$row['business_id']]['push_task_id'] = $row['id'];
                $energy_push_task_list[$row['business_id']]['push_url'] = $row['push_url'];
            }
        }

        // 将信息循环推送到门店
        foreach($energy_push_task_list as $row){
            $this->pushDataToCc($row);
        }
    }
    
    /**
     * 发送请求到同事圈的接口
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToCc($request_data){

        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];
        
        $push_task_model = new PushTask();

        // 当前时间
        $now_time = date("Y-m-d H:i:s");

        // 推送相关数据处理
        $push_data = $this->getPushData($request_data);

        // 发送售后信息到中台
        $response_data = $this->pushDataToCcCommon($request_data, $push_data);
        if(!$response_data){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新任务为已推送
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED', 'modify_time' => $now_time]);
            
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
     * 格式化需要推送的数据
     * @param  array   $request_data
     * @return array
     */
    private function getPushData($request_data){

        $res['energy_record_id'] = $request_data['energy_record_id'] ;
        $res['open_id'] = $request_data['open_id'] ;

        return $res ;
    }



}
