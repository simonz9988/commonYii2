<?php
namespace backend\components;
use common\models\OauthUser;
use common\models\PushTask;
use common\models\SuggestFeedback;
use common\models\TaskWorkOrderLog;
use backend\components\PushTaskCommon;
use Yii ;



class  PushSuggestFeedback extends PushTaskCommon{
    /**
     * 意见反馈推送任务
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    //CRM对应官网的商铺编码
    private $crm_shop_no = '0003008500';



   public function doPushTask($push_task_status){

        // step1 根据status 获取未推送过的信息
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('SUGGEST_FEEDBACK', $push_task_status);
        if(!$push_task_list){
            return false;
        }

        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);
        //step2 根据推送任务中业务ID 查找相关意见反馈数据
        $suggest_feedback_obj = new SuggestFeedback();
        $suggest_task_list = $suggest_feedback_obj->getSuggestFeedbackList($business_ids);
       if(!$suggest_task_list){
           return false;
       }

       // 数据处理 追加 push_task_id,push_url 字段 (不用动)
       foreach($push_task_list as $row){
           if(isset($suggest_task_list[$row['business_id']])){
               $suggest_task_list[$row['business_id']]['push_task_id'] = $row['id'];
               $suggest_task_list[$row['business_id']]['push_url'] = $row['push_url'];
           }
       }

        //step3 循环向CRM推送数据
       foreach($suggest_task_list as $row){
           $this->pushDataToCrm($row);
       }
    }

    /**
     * 发送数据到CRM
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToCrm($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];

        $push_task_model = new PushTask();

        $now_time = date("Y-m-d H:i:s");

        // 推送相关数据处理
        $push_data = $this->getPushData($request_data);
        // 发送意见反馈信息到CRM
        $response_data = $this->pushSuggestFeedbackToCrm($request_data,$push_data);
        if(!$response_data){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新任务为已推送
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED','modify_time' => $now_time]);

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
     * 获取意见反馈处理完成后的回调地址
     */
    public function delSuggestFeedbackCallbackUrl(){
        return SHOP_ADMIN_URL.'/crm-callback/deal-suggest-feedback';
    }

    /**
     * 组装推送给CRM的数据
     * @param array $request_data 推送的原始数据
     * @return array
     */
    public function getPushData($request_data){
        $push_data = [
            'channelCode' => $this->crm_shop_no,
            'accountType' => 'GW_ACCOUNT',
            'account' => $request_data['username'],
            'feedbackSource' => $request_data['channel'],
            'originalNo' => $request_data['feedback_no'],
            'materialNo' => $request_data['material_no'],
            'feedbackTime' => strtotime($request_data['create_time'])*1000,
            'contactName' => $request_data['contact_user'],
            'contactPhone' => $request_data['contact_mobile'],
            'content' => $request_data['content'],
            'callbackUrl' => $this->delSuggestFeedbackCallbackUrl(),
        ];
        if(isset($request_data['file_list']) && $request_data['file_list']){
            $file_list = [];
            foreach($request_data['file_list'] as &$file){
                if($file['file_type'] == 'IMAGE'){
                    $file_list[] = [
                        'type' => 'IMG',
                        'url'  => private_static_url($file['file_url'], $request_data["user_id"])
                    ];
                }

                if($file['file_type'] == 'VIDEO'){
                    $file_list[] = [
                        'type' => 'VIDEO',
                        'url'  => private_static_url($file['file_url'], $request_data["user_id"])
                    ];
                }
            }
            if($file_list){
                $push_data['fileList'] = $file_list;
            }
        }
        return $push_data;
    }

    /**
     * 发送意见反馈信息到CRM
     * @param  array $request_data 请求信息
     * @param  array $push_data    推送数据
     * @return array
     */
    public function pushSuggestFeedbackToCrm($request_data, $push_data){

        // 推送任务ID（sdb_push_task 的 主键 id）
        $push_task_id = $request_data['push_task_id'];

        $now_time = date("Y-m-d H:i:s");

        // 推送任务日志
        $task_work_order_data = [
            'push_task_id' => $push_task_id,
            'request_data' => json_encode($push_data),
            'response_data' => '',
            'create_time' => $now_time,
            'modify_time' => $now_time,
        ];
        $task_work_order_log_model = new TaskWorkOrderLog();
        $task_work_order_log_id = $task_work_order_log_model->saveInfo($task_work_order_data);
        if(!$task_work_order_log_id){
            return [];
        }

        // 推送url
        $create_crm_suggest_feedback_url = $request_data['push_url'];
        // 执行推送
        $response_data = curlGo($create_crm_suggest_feedback_url,$push_data,false,null,'json');
        // 保存返回信息
        $task_work_order_log_model->updateInfo($task_work_order_log_id, ['response_data' => $response_data]);

        $response_data = json_decode($response_data,true);
        $is_return_json = 	json_last_error() == JSON_ERROR_NONE ;
        if(!$is_return_json){
            // json 编码错误
            return [];
        }

        // 判断返回值
        $oms_return_code = $response_data['code'];
        if($oms_return_code !='0000'){
            // 更新任务为失败
            $push_task_model = new PushTask();
            $push_task_model->updateInfo($push_task_id, ['status' => 'FAILED', 'modify_time' => $now_time]);

            // CRM返回失败
            $log_data['request_data'] = $request_data;
            $log_data['push_data'] = $push_data;
            $log_data['response_data'] = $response_data;
            Yii::$app->CommonLogger->logErrorConsole("CRM意见反馈返回失败：".json_encode($log_data));
            return [];
        }

        return $response_data;
    }

    /**
     * 意见反馈发送模板消息 推送任务
     * @param string $push_task_status 业务类型
     * @return boolean
     */
    public function doSendMsgPushTask($push_task_status){
        // step1 根据status 获取未推送过的信息
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('FEEDBACK_SEND_MSG', $push_task_status);

        if(!$push_task_list){
            return false;
        }

        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);
        //step2 根据推送任务中业务ID 查找相关意见反馈数据
        $suggest_feedback_obj = new SuggestFeedback();
        $suggest_task_list = $suggest_feedback_obj->getSuggestFeedbackListByIds($business_ids,'id,user_id,reply_content,reply_time');
        if(!$suggest_task_list){
            return false;
        }

        $send_msg_task_list = [];
        foreach($suggest_task_list as $key => $val){
            $send_msg_task_list[$val['id']] = $val;
        }

        // 数据处理 追加 push_task_id,push_url 字段 (不用动)
        foreach($push_task_list as $row){
            if(isset($send_msg_task_list[$row['business_id']])){
                $send_msg_task_list[$row['business_id']]['push_task_id'] = $row['id'];
                $send_msg_task_list[$row['business_id']]['push_url'] = $row['push_url'];
            }
        }

        //step3 循环向CRM推送数据
        foreach($send_msg_task_list as $row){
            $this->pushDataToBcs($row);
        }
   }


    /**
     * 发送数据到Bcs 短信平台
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToBcs($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];

        $push_task_model = new PushTask();

        $now_time = date("Y-m-d H:i:s");
        // 推送相关数据处理
        $push_data = $this->getPushDataForBcs($request_data);
        // 发送意见反馈信息到CRM
        $response_data = Yii::$app->Bcs->sendSuggestFeedbackReplyNotice($request_data['id'],$push_data);

        if(!$response_data){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新任务为已推送
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED','modify_time' => $now_time]);

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
     * 组装相关发送模板消息的数据
     * @param array $info 意见反馈信息
     * @return array
     */
    public function getPushDataForBcs($info){
        //获取通过官网微信登陆或绑定过的用户openid
        $oauth_user_model = new OauthUser();
        $oauth_user_info = $oauth_user_model->getOauthUserInfo($info['user_id'],8);
        $data = [
                'id' => $info['id'],
                'reply_content' => cut_str($info['reply_content'],30),
                'reply_time' => $info['reply_time'],
                'oauth_user_id' => $oauth_user_info['oauth_user_id'],
                'url' => ACCOUNT_URL.'/webapp/member/suggest-feedback'  //意见反馈模板消息的链接地址
            ];
        $suggest_feedback_model = new SuggestFeedback();
        $send_data = $suggest_feedback_model->getSendBcsData($data);
        return $send_data;
    }
}