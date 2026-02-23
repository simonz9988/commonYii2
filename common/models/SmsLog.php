<?php

namespace common\models;

use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;
use common\components\HttpClient;
use common\components\MyRedis;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Cws\V20180312\Models\Site;
use TencentCloud\Sms\V20190711\Models\SendSmsRequest;
use TencentCloud\Sms\V20190711\SmsClient;
use Yii;

/**
 * This is the model class for table "sea_sms_log".
 *
 * @property int $id
 * @property string $mobile 手机号码
 * @property string $code  短信验证码
 * @property string $type 发送类型，例：signup、forget
 * @property string $create_time 添加时间(服务器请求时间)
 * @property string $status 发送状态，例：SEND/UNSEND/SENDING
 * @property string $callback_time 反馈时间(服务器请求时间)
 * @property string $content 短信内容
 * @property string $callback_content 服务商反馈内容
 */
class SmsLog extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sms_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'code', 'type', 'create_time', 'status', 'content'], 'required'],
            [['create_time', 'callback_time'], 'safe'],
            [['content', 'callback_content'], 'string'],
            [['mobile'], 'string', 'max' => 11],
            [['code'], 'string', 'max' => 20],
            [['type', 'status'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'code' => 'Code',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'status' => 'Status',
            'callback_time' => 'Callback Time',
            'content' => 'Content',
            'callback_content' => 'Callback Content',
        ];
    }

    /**
     * @var array
     * REGISTER -注册
     * LOGIN - 注册
     * FORGET - 忘记密码
     * BIND_ADDRESS - 绑定地址(针对矿机使用不到)
     * RESET_CASH - 重置现金密码
     * RESET_MOBILE - 重置手机号码
     * LOGIN_MODIFY - 登录情况下修改密码
     */
    public $type_arr = ['REGISTER','LOGIN','FORGET','BIND_ADDRESS','RESET_CASH','RESET_MOBILE','LOGIN_MODIFY','CASH_OUT'];

    /**
     * 获取当日总的发送记录列表
     * @param $mobile
     * @return array
     */
    public function getTodayListByMobile($mobile){

        $params['cond'] = ' mobile=:mobile AND create_time >= :create_time';
        $params['args'] = [':mobile'=>$mobile,':create_time'=>date('Y-m-d 00:00:00')];
        $params['orderby'] = 'create_time DESC';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 设置指定session
     * @param $mobile
     * @return mixed
     */
    public function setRedisBySession($mobile){
        $redis_key = 'SmsLimit:'.Yii::$app->session->id;
        $redis_model = new MyRedis();
        $expired = strtotime(date('Y-m-d 23:59:59')) - time();
        $redis_model->incrBy($redis_key,1,$expired);

        $redis_key = 'SmsIpLimit:'.getLongIp();
        $redis_model = new MyRedis();
        $expired = strtotime(date('Y-m-d 23:59:59')) - time();
        $redis_model->incrBy($redis_key,1,$expired);

        $redis_key = 'SmsLimitSetting:'.$mobile.':'.Yii::$app->session->getId();
        $redis_model->set($redis_key,time(),$expired);

        $redis_key = 'SmsIpLimitSetting:'.$mobile.':'.getLongIp();
        $redis_model->set($redis_key,time(),$expired);
        return true ;
    }

    /**
     * 根据Session获取指定类型的session信息
     * @param string $type
     * @return bool
     */
    public function getRedisBySession($type='SmsLimit'){
        $redis_key = $type.':'.Yii::$app->session->id;
        $redis_model = new MyRedis();
        return $redis_model->get($redis_key);
    }

    /**
     * 根据IP获取执行类型的session信息
     * @param string $type
     * @return bool
     */
    public function getRedisByIp($type='SmsLimit'){
        $redis_key = $type.':'.getLongIp();
        $redis_model = new MyRedis();
        return $redis_model->get($redis_key);
    }

    /**
     * 发送你记录
     * @param $mobile
     * @param $msg
     * @param $type
     * @return mixed
     */
    public function sendMsg($mobile,$type){

        // 判断当前session是否一分钟内发送过
        $session_setting_info = $this->getRedisBySession('SmsLimitSetting:'.$mobile);
        if($session_setting_info){
            $ext = time() - $session_setting_info;
            if($ext <= 60){
                return false ;
            }
        }

        // 判断当前session是否查过当天操作的数目限制
        $session_limit_info = $this->getRedisBySession('SmsLimit');
        if($session_limit_info && $session_limit_info > 20){
            return false ;
        }

        // 判断当前IP
        $session_setting_info = $this->getRedisByIp('SmsIpLimitSetting:'.$mobile);
        if($session_setting_info){
            $ext = time() - $session_setting_info;
            if($ext <= 60){
                return false ;
            }
        }


        // 判断当前IP是否查过当天操作的数目限制
        $session_limit_info = $this->getRedisByIp('SmsIpLimit');
        if($session_limit_info && $session_limit_info > 20){
            return false ;
        }

        $today_list = $this->getTodayListByMobile($mobile);

        // 当前手机一分钟之内是否已经发送过 不区分类型
        $lasted_info = $today_list ? $today_list[0] : [];
        if($lasted_info){
            $ext = time() - strtotime($lasted_info['create_time']) ;
            if($ext <=60){
                return false ;
            }
        }

        // 判断当天是否超过发送条数
        $today_total_num = count($today_list);
        if($today_total_num >= 10){
            return false ;
        }

        // 执行发送操作
        $code = mt_rand(100000,999999);
        $site_config_obj = new SiteConfig() ;
        $is_real_send_sms = $site_config_obj->getByKey('is_real_send_sms') ;
        if($is_real_send_sms =='N'){
            $send_rst = true;
        }else{
            //$send_rst = $this->doAliSend($mobile,$code);
            $send_rst = $this->doOtherSend($mobile,$code);
        }

        if($send_rst){

            $add_data['mobile'] = $mobile ;
            $add_data['code'] = $code ;
            $add_data['type'] = $type ;
            $add_data['create_time'] = date('Y-m-d H:i:s') ;
            $add_data['modify_time'] = date('Y-m-d H:i:s') ;
            $add_data['status'] = 'SEND' ;
            $add_data['callback_time'] = date('Y-m-d H:i:s') ; ;
            $add_data['content'] = '' ;
            $add_data['callback_content'] = $send_rst ;
            $this->baseInsert(self::tableName(),$add_data);

            $this->setRedisBySession($mobile);
            return true ;
        }

        return false ;
    }

    /**
     * 执行发送操作
     * @param $mobile
     * @param $code
     * @return bool|false|string
     */
    public function doTencentSend($mobile,$code){

        try {
            /* 必要步骤：
             * 实例化一个认证对象，入参需要传入腾讯云账户密钥对 secretId 和 secretKey
             * 本示例采用从环境变量读取的方式，需要预先在环境变量中设置这两个值
             * 您也可以直接在代码中写入密钥对，但需谨防泄露，不要将代码复制、上传或者分享给他人
             * CAM 密钥查询：https://console.cloud.tencent.com/cam/capi */

            $site_config = new SiteConfig();
            $tencent_sms_secret_id = $site_config->getByKey('tencent_sms_secret_id');
            $tencent_sms_secret_key = $site_config->getByKey('tencent_sms_secret_key');
            $tencent_sms_app_id = $site_config->getByKey('tencent_sms_app_id');
            $tencent_sms_app_sign = $site_config->getByKey('tencent_sms_app_sign');
            $tencent_sms_template_id = $site_config->getByKey('tencent_sms_template_id');
            $cred = new Credential($tencent_sms_secret_id, $tencent_sms_secret_key);
            //$cred = new Credential(getenv("TENCENTCLOUD_SECRET_ID"), getenv("TENCENTCLOUD_SECRET_KEY"));

            // 实例化一个 http 选项，可选，无特殊需求时可以跳过
            $httpProfile = new HttpProfile();
            $httpProfile->setReqMethod("GET");  // POST 请求（默认为 POST 请求）
            $httpProfile->setReqTimeout(30);    // 请求超时时间，单位为秒（默认60秒）
            $httpProfile->setEndpoint("sms.tencentcloudapi.com");  // 指定接入地域域名（默认就近接入）

            // 实例化一个 client 选项，可选，无特殊需求时可以跳过
            $clientProfile = new ClientProfile();
            $clientProfile->setSignMethod("TC3-HMAC-SHA256");  // 指定签名算法（默认为 HmacSHA256）
            $clientProfile->setHttpProfile($httpProfile);

            // 实例化 SMS 的 client 对象，clientProfile 是可选的
            $client = new SmsClient($cred, "ap-shanghai", $clientProfile);

            // 实例化一个 sms 发送短信请求对象，每个接口都会对应一个 request 对象。
            $req = new SendSmsRequest();

            /* 填充请求参数，这里 request 对象的成员变量即对应接口的入参
             * 您可以通过官网接口文档或跳转到 request 对象的定义处查看请求参数的定义
             * 基本类型的设置:
               * 帮助链接：
               * 短信控制台：https://console.cloud.tencent.com/smsv2
               * sms helper：https://cloud.tencent.com/document/product/382/3773 */

            /* 短信应用 ID: 在 [短信控制台] 添加应用后生成的实际 SDKAppID，例如1400006666 */
            $req->SmsSdkAppid = $tencent_sms_app_id;
            /* 短信签名内容: 使用 UTF-8 编码，必须填写已审核通过的签名，可登录 [短信控制台] 查看签名信息 */
            $req->Sign = $tencent_sms_app_sign;
            /* 短信码号扩展号: 默认未开通，如需开通请联系 [sms helper] */
            $req->ExtendCode = "0";
            /* 下发手机号码，采用 e.164 标准，+[国家或地区码][手机号]
               * 例如+8613711112222， 其中前面有一个+号 ，86为国家码，13711112222为手机号，最多不要超过200个手机号*/
            $req->PhoneNumberSet = array("+86".$mobile);
            /* 国际/港澳台短信 senderid: 国内短信填空，默认未开通，如需开通请联系 [sms helper] */
            $req->SenderId = "";
            /* 用户的 session 内容: 可以携带用户侧 ID 等上下文信息，server 会原样返回 */
            $req->SessionContext = "xxx";
            /* 模板 ID: 必须填写已审核通过的模板 ID。可登录 [短信控制台] 查看模板 ID */
            $req->TemplateID = $tencent_sms_template_id;
            /* 模板参数: 若无模板参数，则设置为空*/
            $req->TemplateParamSet = array($code,60);


            // 通过 client 对象调用 SendSms 方法发起请求。注意请求方法名与请求对象是对应的
            $resp = $client->SendSms($req);

            // 输出 JSON 格式的字符串回包
            return ($resp->toJsonString());

            // 可以取出单个值，您可以通过官网接口文档或跳转到 response 对象的定义处查看返回字段的定义

        }
        catch(TencentCloudSDKException $e) {
            return false ;
        }
    }

    /**
     * 判断当前类型的验证码是否有效
     * @param $mobile
     * @param $type
     * @param $code
     * @return mixed
     */
    public function checkCode($mobile,$type,$code){

        $params['cond'] = 'is_use = :is_use AND create_time >=:start_time AND type=:type AND code=:code AND mobile=:mobile';
        $start_time = date('Y-m-d H:i:s',time()-3600) ;
        $params['args'] = [':is_use'=>'N',':start_time'=>$start_time,':type'=>$type,':code'=>$code,':mobile'=>$mobile];

        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if(!$info){
            return false ;
        }

        return $info ;
    }

    /**
     * @param $mobile
     * @param $code
     * @param $type
     * @return mixed
     */
    public function updateByMobileAndCode($mobile,$code,$type){

        $update_data['is_use'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $where_cond = ' is_use=:is_use AND mobile=:mobile AND code=:code AND type=:type';
        $where_args = [':is_use'=>'N',':mobile'=>$mobile,':code'=>$code,':type'=>$type] ;
        $update_res = $this->baseUpdate(self::tableName(),$update_data,$where_cond,$where_args);
        if(!$update_res){
            return false ;
        }

        $batch_cond = 'is_use=:is_use AND mobile=:mobile AND type=:type';
        $batch_args = [':is_use'=>'N',':mobile'=>$mobile,':type'=>$type] ;
        $this->baseUpdate(self::tableName(),$update_data,$batch_cond,$batch_args) ;
        return true ;

    }

    /**
     * 执行阿里的短信推送
     * @param $mobile
     * @param $code
     * @return mixed
     */
    public function doAliSend($mobile,$code){

        $site_config = new SiteConfig() ;
        $alibaba_sms_app_key = $site_config->getByKey('alibaba_sms_app_key') ;
        $alibaba_sms_app_secret = $site_config->getByKey('alibaba_sms_app_secret') ;
        $alibaba_sms_sign_name = $site_config->getByKey('alibaba_sms_sign_name') ;
        $alibaba_sms_template_code = $site_config->getByKey('alibaba_sms_template_code') ;
        $client = new AlibabaCloud();
        $client::accessKeyClient($alibaba_sms_app_key, $alibaba_sms_app_secret)
            ->regionId('cn-hangzhou')
            ->asDefaultClient();


            $result = $client::rpc()
                ->product('Dysmsapi')
                // ->scheme('https') // https | http
                ->version('2017-05-25')
                ->action('SendSms')
                ->method('POST')
                ->host('dysmsapi.aliyuncs.com')
                ->options([
                    'query' => [
                        'RegionId' => "cn-hangzhou",
                        'PhoneNumbers' => $mobile,
                        'SignName' => $alibaba_sms_sign_name,
                        'TemplateCode' => $alibaba_sms_template_code,
                        'TemplateParam' => json_encode(['code'=>$code]),
                    ],
                ])
                ->request();

            $res = $result->toArray() ;
            return isset($res['Code']) && $res['Code']=='OK' ? true :false ;

    }

    public function doOtherSend($mobile,$code){
        $url = 'http://120.77.14.55:8888/sms.aspx?action=send&userid=14651&account=siaex&password=123456&mobile='.$mobile.'&content=【SIAEX】您的验证码是'.$code.'&sendTime=&extno=';
        curlGo($url);
        return true;


    }
}
