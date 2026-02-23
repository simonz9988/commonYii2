<?php
/**
 * 新零售地址推送
 */
namespace backend\components;

use common\models\Areas;
use common\models\Goods;
use Yii ;
use yii\base\Exception;

use common\models\PushTask;


class PushTaskNewRetailAddress extends PushTaskCommon
{

    /**
     * 新零售推送任务
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($push_task_status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('NEW_RETAIL_AREAS', $push_task_status);
        if(!$push_task_list){
            return false;
        }

        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);
        
        /*** 业务相关数据(需要根据不同的业务而定) start ***/
        $area_model = new Areas();
        $address_push_task_list = $area_model->getNewRetailSyncList($business_ids);
        if(!$address_push_task_list){
            return false;
        }
        /*** 业务相关数据 end ***/

        // 数据处理 追加 push_task_id,push_url 字段
        foreach($push_task_list as $row){
            if(isset($address_push_task_list[$row['business_id']])){
                $address_push_task_list[$row['business_id']]['push_task_id'] = $row['id'];
                $address_push_task_list[$row['business_id']]['push_url'] = $row['push_url'];
            }
        }

        // 将信息循环推送到门店
        foreach($address_push_task_list as $row){
            $this->pushDataToStore($row);
        }
    }
    
    /**
     * 发送商品信息到门店
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToStore($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];
        
        $push_task_model = new PushTask();

        // 当前时间
        $now_time = date("Y-m-d H:i:s");
    
        // 推送相关数据处理
        $push_data =['empty_info'=>''];

        // 发送售后信息到中台
        $response_data = $this->pushDataToNewRetailAreas($request_data, $push_data);
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
    

}
