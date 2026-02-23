<?php
namespace backend\components;

use common\models\SiteConfig;
use common\models\SmsLog;
use Yii;

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
        $site_config_model = new SiteConfig();
        $sms_channel = $site_config_model->getInfoByKeyNoCache("sms_send_channel");
        $sendResult = array('status' => false, 'msg' => "");

        // 类文件放内部引用，用来减少报错，梦网的 Phonecode 类大量的引用 PHP4 的语法，导致存在大量报错
        if (!$sms_channel || $sms_channel == "chuanglan") {
            include_once dirname(dirname(dirname(__FILE__))) . '/vendor/sms/ChuanglanSmsApi.php';

            $clapi  = new \ChuanglanSmsApi();
            $sendResult["msg"] = $clapi->sendSMS($mobile, $message);

            if (!is_null(json_decode($sendResult["msg"]))) {
                $_sendResultJson = json_decode($sendResult["msg"], true);
                if (isset($_sendResultJson['code']) && $_sendResultJson['code'] == '0') {
                    $sendResult['status'] = true;
                }

                if (isset($_sendResultJson['code']) && $_sendResultJson['code'] != '0') {
                    Yii::$app->CommonLogger->logError("短信发送失败: " . $sendResult["msg"]);
                }
            } else {
                Yii::$app->CommonLogger->logError("短信发送失败: " . $sendResult["msg"]);
            }
        } else {
            include_once dirname(dirname(dirname(__FILE__))) . '/vendor/sms/Phonecode.php';

            $sendResult = \Phonecode::sendPhoneCode($mobile, $message);
        }

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