<?php
namespace common\components;

use common\models\SmsLog;
use Yii;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/sms/Phonecode.php';

/**
 *
 * 发短信的类
 * 记录日志
 */

class SMS {

    /**
     * 发送短信
     * @param string $mobile mobile phone number
     * @param string $message a text message
     * @return bool
     */
    public function send($mobile, $message) {
        $sendResult = \Phonecode::sendPhoneCode($mobile, $message);

        $this->_addSendLog($mobile, date('Y-m-d H:i:s'), 'notice', $message, $sendResult['status'], $sendResult['msg']);

        if ($sendResult['status'] == false) {
            return false;
        }

        return true;
    }

    /**
     * _addSendLog($mobile, $sendTime, $type, $message, $status, $callbackContent)
     * 记录短信日志
     *
     * @param string $mobile 电话号码
     * @param string $sendTime 开始发送时间
     * @param string $type 类型
     * @param string $message 短信内容
     * @param string $status 状态
     * @param string $callbackContent 发送状态回执
     * @return int
     */
    private function _addSendLog($mobile, $sendTime, $type, $message, $status, $callbackContent) {
        $sms_model = new SmsLog();
        return $sms_model->addLog($mobile, $sendTime, $type, $message, $status, $callbackContent);
    }
}