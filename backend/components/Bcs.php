<?php
/**
 * 与中台短信平台相关方法 https://bcs.ecovacs.cn
 * User: dean.zhang
 * Date: 2018/4/11
 * Time: 10:26
 */
namespace backend\components;


use common\models\BcsLog;
use common\models\SuggestFeedback;
use common\models\SuggestFeedbackLog;

class Bcs
{
    private  $bcs_api_url = BCS_API_URL;//短信平台地址



    /**
     * 获取发送模板消息的api地址
     */
    public function getSendWechatTemplateMsgUrl(){
        return $this->bcs_api_url.'/api/wechat/message/template/send';
    }

    /**
     * 调用短信平台接口，发送意见反馈模板消息
     * @param int $feedback_id 意见反馈ID
     * @param array $send_data 发送的数据
     * @return array
     */
    public function sendSuggestFeedbackReplyNotice($feedback_id,$send_data){
        $send_url = $this->getSendWechatTemplateMsgUrl();
        $result = curlGo($send_url,$send_data,false,null,'json');
        //记录回调日志
        $suggest_feedback_log_model = new SuggestFeedbackLog();
        $suggest_feedback_log_model->insertLogData('SEND_MSG',json_encode($send_data,JSON_UNESCAPED_UNICODE),$result,$feedback_id);

        $response_data = json_decode($result,true);
        if($response_data['code'] == '0000'){
            //回填发送消息的状态
            $update_data = [
                'send_status' => 'SENDING',
                'send_message_no' => $response_data['data']
            ];
            $suggest_feedback = new SuggestFeedback();
            $suggest_feedback->baseUpdate($suggest_feedback->tableName(),$update_data,'id=:id',[':id'=>$feedback_id],'db_official');

            return true;
        }else{
            return false;
        }
    }

    /**
     * 积分大乐透开奖结果通知，发送模板消息
     * @param integer $business_id 业务ID
     * @param array $send_data 发送数据
     */
    public function sendIntegralLottoPrizeNotice($business_id,$send_data){
        $send_url = $this->getSendWechatTemplateMsgUrl();
        $result = curlGo($send_url,$send_data,false,null,'json');
        //记录回调日志
        $log_data = [
            'business_id' => $business_id,
            'business_type' => 'INTEGRAL_LOTTO',
            'send_data' => json_encode($send_data,JSON_UNESCAPED_UNICODE),
            'response_data' => $result,
            'send_url' => $send_url
        ];
        $log_model = new BcsLog();
        $log_model->insertLogData($log_data);
    }
}