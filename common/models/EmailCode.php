<?php

namespace common\models;

use common\components\MyRedis;
use PHPMailer\PHPMailer\PHPMailer;
use Yii;

/**
 * This is the model class for table "sea_email_code".
 *
 * @property int $id
 * @property string $email 邮箱地址
 * @property string $code
 * @property string $type 类型
 * @property string $status 状态
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class EmailCode extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_email_code';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['email', 'status'], 'string', 'max' => 50],
            [['code', 'type'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'email' => 'Email',
            'code' => 'Code',
            'type' => 'Type',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据邮箱判断是否存在
     * @param $email
     * @param $type
     * @return mixed
     */
    public function checkExistsByEmail($email,$type){
        $params['cond'] = 'email=:email AND status=:status AND type=:type';
        $params['args'] = [':email'=>$email,':status'=>'UNDEAL',':type'=>$type];
        $params['orderby'] = 'id DESC';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;

    }

    /**
     * 发送邮箱验证码
     * @param $email
     * @param $type
     * @param $code
     * @return bool|string
     */
    public function sendByEmail($email,$type,$code=''){

        // 判断是否一分钟之内已经发送
        $exists_info = $this->checkExistsByEmail($email,$type);
        if($exists_info){
            $ext = time() - strtotime($exists_info['create_time']) ;
            if($ext <=60){
                return false ;
            }
        }

        $code = $code ? $code : mt_rand(100000,999999) ;

        #TODO 执行邮件发送操作
        $mail = new PHPMailer(true);
        $site_config = new SiteConfig();

        $email_host  = $site_config->getByKey('email_host');
        $email_username  = $site_config->getByKey('email_username');
        $email_password  = $site_config->getByKey('email_password');
        $email_port  = $site_config->getByKey('email_port');
        $email_from_name  = $site_config->getByKey('email_from_name');
        //Server settings
        $mail->SMTPDebug = 0;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->CharSet ="UTF-8";
        $mail->Host       = $email_host;                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = $email_username;                     // SMTP username
        $mail->Password   = $email_password;                               // SMTP password
       //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        // 不同邮箱服务使用的协议是不一样的 当前是163使用的 ，Godaddy使用的是上面的
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        $mail->Port       = $email_port;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

        //Recipients
        $email_from_name = $email_from_name ?$email_from_name :'Mailer';
        $mail->setFrom($email_username, $email_from_name);
        $mail->addAddress($email, 'User');     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        //$mail->addReplyTo('info@example.com', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        // Attachments
        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        // Content
        $email_title = $site_config->getByKey('email_title');
        $email_mark = $site_config->getByKey('email_mark');
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $email_title?$email_title:'验证码';
        $mail->Body    = $email_mark.'您的验证码为<b>'.$code.'</b>';
        $mail->AltBody = $email_mark.'您的验证码为:'.$code;

        $mail->send();


        $add_data['email'] = $email ;
        $add_data['code'] =  $code;
        $add_data['type'] =  $type;
        $add_data['status'] =  'UNDEAL';
        $now = date('Y-m-d H:i:s');
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;

        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 判断当前验证码是否有效
     * @param $email
     * @param $code
     * @param $type
     * @return mixed
     */
    public function checkCode($email,$code,$type){

        $params['cond'] = 'is_use = :is_use AND create_time >=:start_time AND type=:type AND code=:code AND email=:email';
        $start_time = date('Y-m-d H:i:s',time()-3600) ;
        $params['args'] = [':is_use'=>'N',':start_time'=>$start_time,':type'=>$type,':code'=>$code,':email'=>$email];

        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        if(!$info){
            return false ;
        }

        return $info ;
    }

    /**
     * @param $email
     * @param $code
     * @param $type
     * @return mixed
     */
    public function updateByEmailAndCode($email,$code,$type){

        $update_data['is_use'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $where_cond = ' is_use=:is_use AND email=:email AND code=:code AND type=:type';
        $where_args = [':is_use'=>'N',':email'=>$email,':code'=>$code,':type'=>$type] ;
        $update_res = $this->baseUpdate(self::tableName(),$update_data,$where_cond,$where_args);
        if(!$update_res){
            return false ;
        }

        $batch_cond = 'is_use=:is_use AND email=:email AND type=:type';
        $batch_args = [':is_use'=>'N',':email'=>$email,':type'=>$type] ;
        $this->baseUpdate(self::tableName(),$update_data,$batch_cond,$batch_args) ;
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
     * 获取当日总的发送记录列表
     * @param $email
     * @return array
     */
    public function getTodayListByEmail($email){

        $params['cond'] = ' email=:email AND create_time >= :create_time';
        $params['args'] = [':email'=>$email,':create_time'=>date('Y-m-d 00:00:00')];
        $params['orderby'] = 'create_time DESC';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 发送你记录
     * @param $email
     * @param $type
     * @return mixed
     */
    public function sendMsg($email,$type){

        // 判断当前session是否一分钟内发送过
        $session_setting_info = $this->getRedisBySession('EmailLimitSetting:'.$email);
        if($session_setting_info){
            $ext = time() - $session_setting_info;
            if($ext <= 60){
                return false ;
            }
        }

        // 判断当前session是否查过当天操作的数目限制
        $session_limit_info = $this->getRedisBySession('EmailLimit');
        if($session_limit_info && $session_limit_info > 20){
            return false ;
        }

        // 判断当前IP
        $session_setting_info = $this->getRedisByIp('EmailIpLimitSetting:'.$email);
        if($session_setting_info){
            $ext = time() - $session_setting_info;
            if($ext <= 60){
                return false ;
            }
        }


        // 判断当前IP是否查过当天操作的数目限制
        $session_limit_info = $this->getRedisByIp('EmailIpLimit');
        if($session_limit_info && $session_limit_info > 20){
            return false ;
        }

        $today_list = $this->getTodayListByEmail($email);

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
            $send_rst = $this->sendByEmail($email,$type,$code);
        }

        if($send_rst){

            $add_data['email'] = $email ;
            $add_data['code'] = $code ;
            $add_data['type'] = $type ;
            $add_data['create_time'] = date('Y-m-d H:i:s') ;
            $add_data['modify_time'] = date('Y-m-d H:i:s') ;
            $add_data['status'] = 'SEND' ;
            $this->baseInsert(self::tableName(),$add_data);

            $this->setRedisBySession($email);
            return true ;
        }

        return false ;
    }

    /**
     * 设置指定session
     * @param $email
     * @return mixed
     */
    public function setRedisBySession($email){
        $redis_key = 'EmailLimit:'.Yii::$app->session->id;
        $redis_model = new MyRedis();
        $expired = strtotime(date('Y-m-d 23:59:59')) - time();
        $redis_model->incrBy($redis_key,1,$expired);

        $redis_key = 'EmailIpLimit:'.getLongIp();
        $redis_model = new MyRedis();
        $expired = strtotime(date('Y-m-d 23:59:59')) - time();
        $redis_model->incrBy($redis_key,1,$expired);

        $redis_key = 'EmailLimitSetting:'.$email.':'.Yii::$app->session->getId();
        $redis_model->set($redis_key,time(),$expired);

        $redis_key = 'EmailIpLimitSetting:'.$email.':'.getLongIp();
        $redis_model->set($redis_key,time(),$expired);
        return true ;
    }



}
