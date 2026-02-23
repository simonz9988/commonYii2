<?php

namespace common\models;

use common\components\Filter;
use Yii;
use yii\db\Expression;
use JmesPath\Parser;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\ValidationData;

/**
 * This is the model class for table "sdb_member".
 *
 * @property string $id
 * @property string $user_name 用户名
 * @property string $nickname 用户昵称
 * @property string $mobile 手机号码
 * @property string $apiKey
 * @property string $secretKey
 * @property string $status 是否有效
 * @property int $is_open 是否删除
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class Member extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_member';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_open'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['user_name', 'nickname', 'mobile', 'status'], 'string', 'max' => 50],
            [['apiKey', 'secretKey'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_name' => 'User Name',
            'nickname' => 'Nickname',
            'mobile' => 'Mobile',
            'apiKey' => 'Api Key',
            'secretKey' => 'Secret Key',
            'status' => 'Status',
            'is_open' => 'Is Open',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public function getAll(){
        $params['cond'] = 'status = :status AND is_open =:is_open';
        $params['args'] = [':status'=>'enabled',':is_open'=>1];
        $params['orderby'] = ' id desc ';
        $list = $this->findAllByWhere($this->tableName(), $params, self::getDb());
        return $list ;
    }

    /**
     * 通过地址信息获取用户信息
     * @param string $address  用户地址信息
     * @param string $fields   最终返回字段
     * @return array
     */
    public function getUserInfoByAddress($address , $fields='*'){
        $address = strtolower($address) ;
        $params['fields'] = $fields ;
        $params['cond'] = 'eth_address =:eth_address';
        $params['args'] = [':eth_address'=>$address];
        $info = $this->findOneByWhere('sea_user',$params,self::getDb()) ;
        return $info ;
    }

    /**
     * 判断是否为超级玩家
     * @param $user_id
     * @return boolean
     */
    public function checkIsSuper($user_id){
        $params['cond'] = 'id =:user_id AND is_super=:is_super';
        $params['args'] = [':user_id'=>$user_id,':is_super'=>'Y'];
        $params['fields'] = 'id';
        $info = $this->findOneByWhere('sea_user',$params);
        return $info ? true : false ;
    }

    /**
     * 根据用户ID返回用户信息
     * @param $user_id
     * @param $fields
     * @return mixed
     */
    public function getUserInfoById($user_id,$fields='*'){

        $params['cond'] = 'id =:user_id';
        $params['args'] = [':user_id'=>$user_id];
        $params['fields'] = $fields;
        $info = $this->findOneByWhere('sea_user',$params);
        return $info ;
    }

    /**
     * 获取用户信息
     * @param $username
     * @param $password
     * @return mixed
     */
    public function getUserInfo($username,$password){

        if(!$username || !$password){
            return false ;
        }
        $params['cond'] = '( username =:username OR mobile=:username OR email=:username ) AND password=:password';
        $params['args'] = [':username'=>$username,':password'=>($password)];
        $params['fields'] = '*';

        $info = $this->findOneByWhere('sea_user',$params);
        return $info ;
    }

    /**
     * 获取用户信息
     * @param $username
     * @return mixed
     */
    public function getUserInfoByUsername($username){

        if(!$username ){
            return false ;
        }
        $params['cond'] = '( username =:username OR mobile=:username OR email=:username ) ';
        $params['args'] = [':username'=>$username];
        $params['fields'] = '*';

        $info = $this->findOneByWhere('sea_user',$params);
        return $info ;
    }

    /**
     * 根据手机号码获取用户信息
     * @param $mobile
     * @return array|bool
     */
    public function getInfoByMobile($mobile){
        $params['cond'] = '( username =:username OR mobile=:username )';
        $params['args'] = [':username'=>$mobile];
        $params['fields'] = '*';
        $info = $this->findOneByWhere('sea_user',$params);
        return $info ;
    }

    /**
     * 通过邀请码获取用户信息
     * @param $invite_code
     * @param $fields
     * @return mixed
     */
    public function getInfoByInviteCode($invite_code,$fields='*'){
        $params['cond'] = 'invite_code =:invite_code';
        $params['args'] = [':invite_code'=>$invite_code];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info ;
    }

    /**
     * 通过邀请码获取用户信息
     * @param $username
     * @param $fields
     * @return mixed
     */
    public function getInfoByUsername($username,$fields='*'){
        $params['cond'] = 'username =:username';
        $params['args'] = [':username'=>$username];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info ;
    }

    /**
     * 用户注册
     * @param $mobile
     * @param $code
     * @param $password
     * @param $repeat_password
     * @param $invite
     * @param $is_use_wallet
     * @return mixed
     */
    public function doRegister($mobile,$code,$password,$repeat_password,$invite,$is_use_wallet=true){

        // 验证手机号码是否重复
        $filter = new Filter();
        $check_mobile = $filter->C_mobile($mobile);
        if(!$check_mobile){
            return ['code'=>100004] ;
        }

        $user_info = $this->getInfoByMobile($mobile);
        if($user_info){
            return ['code'=>100009] ;
        }

        // 邀请码是否存在
        if(!$invite){
            //return ['code'=>100010] ;
        }

        // 验证码
        $invite_user_info = $this->getInfoByInviteCode($invite);
        if(!$invite_user_info){
            //return ['code'=>100010] ;
        }

        // 密码是否一致
        if(!$password || !$repeat_password){
            return ['code'=>100011] ;
        }

        if($password != $repeat_password){
            return ['code'=>100012] ;
        }

        // 验证登录验证码是否一致
        $sms_log_obj = new SmsLog();
        $check_code_res = $sms_log_obj->checkCode($mobile,'REGISTER',$code);
        if(!$check_code_res){
            return ['code'=>100013] ;
        }

        //分配一个入账的eth地址
        $user_wallet_obj = new UserWallet() ;
        if($is_use_wallet){
            $user_wallet_info = $user_wallet_obj->getUsefulInfo();
            if(!$user_wallet_info){
                return ['code'=>100023];
            }
        }


        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            // 当前时间
            $now = date('Y-m-d H:i:s');
            // 执行注册
            $type = 'PERSON' ;
            $add_data['username'] = $this->createUserName($type) ;
            $add_data['password'] = md5($password) ;
            $add_data['email'] = '' ;
            $add_data['nickName'] = '' ;
            $add_data['avatarUrl'] = '' ;
            $add_data['address'] = '' ;
            $add_data['trans_eth_address'] = $user_wallet_info['address'] ;
            $add_data['type'] = $type ;
            $add_data['reg_from'] = 'FRONT' ;
            $add_data['audit_status'] = 'SUCCESS' ;
            $add_data['audit_idea'] = '' ;
            $add_data['mobile'] = $mobile ;
            $add_data['inviter_user_id'] = $invite_user_info ? $invite_user_info['id'] : 0;

            $invite_code = $this->createInviteCode();
            $add_data['invite_code'] = $invite_code ;
            $add_data['inviter_username'] = $invite_user_info ? $invite_user_info['username'] :'' ;
            $add_data['user_root_path'] = $invite_user_info ? $invite_user_info['user_root_path'].$invite_user_info['id'].'--':'--0--' ;
            $add_data['user_level'] = $invite_user_info ? $invite_user_info['user_level'] + 1 : 0  ;
            $add_data['is_open'] = 1  ;
            $add_data['last_login'] = NULL  ;
            $add_data['create_time'] = $now  ;
            $add_data['modify_time'] = $now  ;
            $user_id = $this->baseInsert('sea_user',$add_data);
            if(!$user_id){
                return ['code'=>100014];
            }

            //更新地址池信息
            $user_wallet_update_data['user_id'] = $user_id ;
            $user_wallet_update_data['mobile'] = $mobile ;
            $user_wallet_update_data['modify_time'] = date('Y-m-d H:i:s') ;
            $user_wallet_obj->baseUpdate($user_wallet_obj::tableName(),$user_wallet_update_data,'id=:id AND user_id=0',[':id'=>$user_wallet_info['id']]);

            //赠送用户使用积分
            $robot_balance_obj = new RobotUserBalance();
            $robot_balance_obj->addRegisterIntegral($user_id);

            // 更新短信验证码
            $sms_log_obj->updateByMobileAndCode($mobile,$code,'REGISTER');

            // ################### 插入用户关系 #######################
            $this->addParentTools($user_id,$invite_user_info['id']);

            $transaction->commit();
            return ['code'=>1];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['code'=>100024];
        }
    }

    /**
     * 用户根据邮箱注册
     * @param $email
     * @param $code
     * @param $password
     * @param $repeat_password
     * @param $invite
     * @param $is_use_wallet
     * @return mixed
     */
    public function doRegisterByEmail($email,$code,$password,$repeat_password,$invite,$is_use_wallet=true){

        // 验证手机号码是否重复
        $filter = new Filter();
        $check_mobile = $filter->C_email($email);
        if(!$check_mobile){
            return ['code'=>100080] ;
        }

        $user_info = $this->getInfoByMobile($email);
        if($user_info){
            return ['code'=>100081] ;
        }

        // 邀请码是否存在
        if(!$invite){
            //return ['code'=>100010] ;
        }

        // 验证码
        $invite_user_info = $this->getInfoByInviteCode($invite);
        if(!$invite_user_info){
            //return ['code'=>100010] ;
        }

        // 密码是否一致
        if(!$password || !$repeat_password){
            return ['code'=>100011] ;
        }

        if($password != $repeat_password){
            return ['code'=>100012] ;
        }

        // 验证登录验证码是否一致
        $email_log_obj = new EmailCode();
        $check_code_res = $email_log_obj->checkCode($email,$code,'REGISTER');
        if(!$check_code_res){
            return ['code'=>100013] ;
        }

        //分配一个入账的eth地址
        $user_wallet_obj = new UserWallet() ;
        if($is_use_wallet){
            $user_wallet_info = $user_wallet_obj->getUsefulInfo();
            if(!$user_wallet_info){
                return ['code'=>100023];
            }
        }


        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            // 当前时间
            $now = date('Y-m-d H:i:s');
            // 执行注册
            $type = 'PERSON' ;
            $add_data['username'] = $this->createUserName($type) ;
            $add_data['password'] = md5($password) ;
            $add_data['email'] = $email ;
            $add_data['nickName'] = '' ;
            $add_data['avatarUrl'] = '' ;
            $add_data['address'] = '' ;
            $add_data['trans_eth_address'] = $user_wallet_info['address'] ;
            $add_data['type'] = $type ;
            $add_data['reg_from'] = 'FRONT' ;
            $add_data['audit_status'] = 'SUCCESS' ;
            $add_data['audit_idea'] = '' ;
            $add_data['mobile'] = '' ;
            $add_data['inviter_user_id'] = $invite_user_info ? $invite_user_info['id'] : 0;

            $invite_code = $this->createInviteCode();
            $add_data['invite_code'] = $invite_code ;
            $add_data['inviter_username'] = $invite_user_info ? $invite_user_info['username'] :'' ;
            $add_data['user_root_path'] = $invite_user_info ? $invite_user_info['user_root_path'].$invite_user_info['id'].'--':'--0--' ;
            $add_data['user_level'] = $invite_user_info ? $invite_user_info['user_level'] + 1 : 0  ;
            $add_data['is_open'] = 1  ;
            $add_data['last_login'] = NULL  ;
            $add_data['create_time'] = $now  ;
            $add_data['modify_time'] = $now  ;
            $user_id = $this->baseInsert('sea_user',$add_data);
            if(!$user_id){
                return ['code'=>100014];
            }

            //更新地址池信息
            if($is_use_wallet){
                $user_wallet_update_data['user_id'] = $user_id ;
                $user_wallet_update_data['email'] = $email ;
                $user_wallet_update_data['modify_time'] = date('Y-m-d H:i:s') ;
                $user_wallet_obj->baseUpdate($user_wallet_obj::tableName(),$user_wallet_update_data,'id=:id AND user_id=0',[':id'=>$user_wallet_info['id']]);

            }

            //赠送用户使用积分
            $robot_balance_obj = new RobotUserBalance();
            $robot_balance_obj->addRegisterIntegral($user_id);

            // 更新短信验证码
            $email_log_obj->updateByEmailAndCode($email,$code,'REGISTER');

            // ################### 插入用户关系 #######################
            $this->addParentTools($user_id,$invite_user_info['id']);

            $transaction->commit();
            return ['code'=>1];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['code'=>100024];
        }
    }

    /**
     * 创建用户名
     * @param string $type 用户类型
     * @return string
     */
    public function createUserName($type){
        //$str = str_pad(mt_rand(1,9999999),8 , "0", STR_PAD_LEFT);
        $str = mt_rand(10000000,99999999);

        switch($type)
        {
            case 'PERSON'://个人
                $sult= "P".$str;
                $sult= $str;
                break;
            case 'COMPANY'://供应商
                $sult= "C".$str;
                break;
            default:
                $sult = 'N'.$str;
        }
        //查找是否重复
        $info = $this->getInfoByUsername($sult,'id');
        if($info){
            $this->createUserName($type);
        }
        return $sult;
    }

    /**
     * 创建邀请码
     * @param $code_length
     * @return mixed
     */
    public function createInviteCode($code_length=6){
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code = '';
        for ($i = 0; $i < $code_length; $i++) {
            $code .= $characters[mt_rand(0, strlen($characters)-1)];
        }
        $info = $this->getInfoByInviteCode($code,'id');
        if($info){
            return $this->createInviteCode($code_length);
        }
        return $code ;
    }

    /**
     * 根据用户ID返回手机号码
     * @param $user_id
     * @return mixed
     */
    public function getMobileByUserId($user_id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$user_id];
        $params['fields'] = 'mobile';
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info && !is_null($info['mobile']) ? $info['mobile'] : '';
    }

    /**
     * 根据
     * @param $user_id
     * @param $address
     * @return mixed
     */
    public function checkEthAddress($user_id,$address){

        $params['cond'] = 'id !=:user_id AND eth_address=:address';
        $params['args'] = [':user_id'=>$user_id,':address'=>$address];
        $params['fields'] = 'id';
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info ;
    }

    /**
     * 获取用户
     * @param $user_id
     * @return mixed
     */
    public function getUserTransEthAddress($user_id){
        $params['cond'] = 'id =:user_id ';
        $params['args'] = [':user_id'=>$user_id];
        $params['fields'] = 'trans_eth_address';
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info ? $info['trans_eth_address']:'';
    }

    /**
     * 处理入金金额
     * @param $user_id
     * @param $value
     * @param $blockHash
     * @param $is_admin
     * @return mixed
     */
    public function addMiningMachineCashInRecord($user_id,$value,$blockHash,$is_admin=false){
        $value = round($value,2);

        if($value <=0){
            return false ;
        }

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();

        try {
            // 更新余额
            $update_data['balance'] = new Expression("balance + " . $value);
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate('sea_user',$update_data,'id=:id',[':id'=>$user_id]);

            // 查询用户级别信息
            $user_info = $this->getUserInfoById($user_id);
            $inviter_user_id = $user_info['inviter_user_id'];
            $user_root_path = $user_info['user_root_path'];
            $user_level = $user_info['user_level'];

            // 入金金额
            $add_data['user_id'] = $user_id ;
            $add_data['amount'] = $value ;
            $add_data['status'] = 'PUSHED' ;
            $add_data['inviter_user_id'] = $inviter_user_id ;
            $add_data['user_root_path'] = $user_root_path ;
            $add_data['user_level'] = $user_level ;
            $add_data['blockHash'] = $blockHash ;
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $record_obj = new MiningMachineCashInRecord();
            $record_obj->baseInsert($record_obj::tableName(),$add_data);

            // 增加用户资产
            $balance_obj = new MiningMachineUserBalance();
            $balance_obj->addByCashIn($user_id,$value);

            $balance_obj = new RobotUserBalance();
            $balance_obj->addByCashIn($user_id,$value);

            // 增加入金记录
            $balance_record_obj = new MiningMachineUserBalanceRecord() ;
            $balance_record_obj->addRecordByCashIn($user_id,$value,$is_admin);

            $balance_record_obj = new RobotUserBalanceRecord() ;
            $balance_record_obj->addRecordByCashIn($user_id,$value,$is_admin);

            $transaction->commit();
            return true ;
        } catch (\Exception $e) {

            $transaction->rollBack();
            $this->setError(100037);
            return false;
        }
    }

    /**
     * 更新用户团队金额
     * @param $user_root_path
     * @param $value
     * @param $self_user_id
     * @return mixed
     */
    public function updateTeamTotalByUserRootPath($user_root_path,$value,$self_user_id){

        $parent_user_list = explode('--',$user_root_path);
        if(!$parent_user_list){
            return false ;
        }

        foreach($parent_user_list as $user_id){
            if($user_id){
                $update_data['team_total'] = new Expression("team_total + " . $value);
                $update_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate('sea_user',$update_data,'id=:id',[':id'=>$user_id]);
            }
        }

        // 业绩也要算自己(废弃，不需要算自己)
        //$update_data['team_total'] = new Expression("team_total + " . $value);
        $self_update_data['self_total'] = new Expression("self_total + " . $value);
        $self_update_data['modify_time'] = date('Y-m-d H:i:s');
        $this->baseUpdate('sea_user',$self_update_data,'id=:id',[':id'=>$self_user_id]);
    }

    /**
     * 判断是否已经版定google验证码
     * @param $user_id
     * @return mixed
     */
    public function checkIsBindGoogle($user_id){

        $user_info = $this->getUserInfoById($user_id,'is_bind_google');
        $is_bind_google = $user_info['is_bind_google'];
        return $is_bind_google =='Y' ? true :false ;
    }

    /**
     * 依据手机号码判断是否已经存在
     * @param $mobile
     * @return mixed
     */
    public function checkExistsByMobile($mobile){

        $params['cond'] = 'mobile=:mobile';
        $params['args'] = [':mobile'=>$mobile];
        $params['fields'] = 'id';
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info ;
    }

    /**
     * @param $user_id
     * @param $new_mobile
     * @return mixed
     */
    public function changeMobile($user_id,$new_mobile){

        $update_data['mobile'] = $new_mobile ;
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        return $this->baseUpdate('sea_user',$update_data,'id=:id',[':id'=>$user_id]);
    }

    /**
     * 根据用户ID返回用户余额
     * @param $user_id
     * @return mixed
     */
    public function getBalanceByUserId($user_id){

        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$user_id];
        $params['fields'] = 'balance';
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info ? $info['balance']:0 ;
    }

    /**
     * 更新账户余额
     * @param $user_id
     * @param $total
     * @return mixed
     */
    public function addBalance($user_id,$total){
        $update_data['balance'] = new Expression('balance +'.$total);
        $update_data['modify_time']  =date('Y-m-d H:i:s');
        return $this->baseUpdate('sea_user',$update_data,'id=:id',[':id'=>$user_id]);
    }

    /**
     * 新增获得的的推荐奖励
     * @param $user_id
     * @param $parent_level
     * @param $value
     * @param $order_id
     * @return mixed
     */
    public function addShareTotal($user_id,$parent_level,$value,$order_id){

        //用户余额
        $user_info = $this->getUserInfoById($user_id);
        //特殊用户不产生收益
        if($user_info['type']=='SPECIAL'){
            return false ;
        }
        $balance = $user_info['balance'];
        // 根据总算力来计算
        $order_obj = new MiningMachineOrder() ;
        $user_calc_power = $order_obj->getTotalCalcPowerByUserId($user_id);
        //var_dump($user_id,$user_calc_power);exit;
        $level = 0 ;
        $site_config_obj = new SiteConfig();
        $fenxiang1_total = $site_config_obj->getByKey('fenxiang1_total');
        $fenxiang2_total = $site_config_obj->getByKey('fenxiang2_total');
        if($user_calc_power >= $fenxiang1_total && $user_calc_power < $fenxiang2_total){
            $level = 1 ;
        }elseif($user_calc_power >= $fenxiang2_total){
            $level = 2 ;
        }

        if(!$level){
            return  false ;
        }

        $percent = 0 ;
        if($parent_level ==1){
            $percent = $level ==1?$site_config_obj->getByKey('fenxiang1_level1'):$site_config_obj->getByKey('fenxiang2_level1');
        }else if($parent_level ==2){
            $percent = $level ==1?$site_config_obj->getByKey('fenxiang1_level2'):$site_config_obj->getByKey('fenxiang2_level2');
        }

        $get_value =  ($value*$percent)/100 ;
        $get_value = numberSprintf($get_value,6);

        // 新增记录
        if($get_value <=0){
            return false ;
        }

        // 插入类型
        $type = 'SHARE_'.$parent_level ;
        $earn_obj = new MiningMachineEarn();
        $check_repeat = $earn_obj->checkRepeatInsert($user_id,$type,$order_id);
        if($check_repeat){
            return false ;
        }

        // 判断是否已经重复添加
        // 当前时间
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $get_value ;
        $add_data['business_id'] = $order_id ;
        $add_data['coin'] = 'USDT' ;
        $add_data['date'] = date('Ymd') ;
        $add_data['date_timestamp'] = strtotime(date('Y-m-d 00:00:00')) ;
        $add_data['type'] = $type ;
        $add_data['user_level'] = $user_info['user_level'] ;
        $add_data['user_root_path'] = $user_info['user_root_path'] ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;

        $this->baseInsert($earn_obj::tableName(),$add_data) ;

        // 用户余额新增
        $this->addBalance($user_id,$get_value);

        // 用户资产
        $balance_obj = new MiningMachineUserBalance();
        $balance_obj->addByShare($user_id,$get_value);

        // 资产变更记录
        $balance_record_obj = new MiningMachineUserBalanceRecord();
        $balance_record_obj->addByShare($user_id,$get_value,$type);
    }

    /**
     * 处理分享收益
     * @param $user_info
     * @param $value
     * @param $order_id
     * @return mixed
     */
    public function dealShareEarn($user_info,$value,$order_id){

        $user_level = $user_info['user_level'];
        if($user_level <1){
            return false ;
        }

        // 查询2个级别
        $user_root_path = explode('--',$user_info['user_root_path']);
        $user_list  = [] ;
        if($user_root_path){
            foreach($user_root_path as $v){
                if($v){
                    $user_list[] = $v ;
                }
            }
        }

        $user_list = array_reverse($user_list);

        $first_user_id = isset($user_list[0]) ? $user_list[0] : 0 ;
        if($first_user_id){
            $this->addShareTotal($first_user_id,1,$value,$order_id);
        }

        $second_user_id = isset($user_list[1]) ? $user_list[1] : 0 ;
        if($second_user_id){
            $this->addShareTotal($second_user_id,2,$value,$order_id);
        }

    }

    /**
     * 处理团队收益
     * @param $user_info
     * @param $order_amount
     * @param $add_total_power
     * @return mixed
     */
    public function dealTeamEarn($user_info,$order_amount,$add_total_power){

        $user_level = $user_info['user_level'];
        if($user_level <=1){
            return false ;
        }

        // 支付的用户信息
        $pay_user_info = $user_info ;

        // 查询2个级别
        $user_root_path = explode('--',$user_info['user_root_path']);
        $user_list  = [] ;

        $user_info_list = [] ;
        $user_level_list = [];

        if($user_root_path){
            foreach($user_root_path as $v){
                if($v){
                    $user_list[] = $v ;

                    $user_info = $this->getUserInfoById($v) ;
                    $user_info_list[$v] = $user_info ;

                    // 当次就算升级收益去出扣减此次增量
                    $check_other_level = $this->getTeamLevel($user_info,$add_total_power,$pay_user_info) ;
                    $user_level_list[$v] = $check_other_level ;
                }
            }
        }

        // 支付者的用户级别
        $pay_user_level = $this->getTeamLevel($pay_user_info,0);
        sort($user_list) ;

        $user_earn_list = [];

        $site_config = new SiteConfig();
        $bili_dict_info['0'] = 0;
        $bili_dict_info['1'] = $site_config->getByKey('p1_xinzeng_yeji_bili');
        $bili_dict_info['2'] = $site_config->getByKey('p2_xinzeng_yeji_bili');
        $bili_dict_info['3'] = $site_config->getByKey('p3_xinzeng_yeji_bili');

        foreach($user_list as $k=>$user_id){

            $user_level = $user_level_list[$user_id];

            if($user_level == 0 ){
                continue ;
            }

            if(isset($user_list[$k+1])){
                $next_user_id = $user_list[$k+1];
                $next_user_level = $user_level_list[$next_user_id];
                if($user_level <= $next_user_level){
                    continue ;
                }

                $user_earn = $order_amount*($bili_dict_info[$user_level]-$bili_dict_info[$next_user_level])*0.01 ;
            }else{
                $user_earn = $order_amount*($bili_dict_info[$user_level]-$bili_dict_info[$pay_user_level])*0.01 ;
            }



            $user_info = $user_info_list[$user_id];
            $user_earn_list[$user_id] = $this->dealTeamEarnByUserInfo($user_info,$user_earn);

        }

        return true ;
    }

    /**
     * @param $user_info
     * @param $user_earn 用户实际营收值
     * @return int
     */
    public function dealTeamEarnByUserInfo($user_info,$earn_total){
        if($user_info['type'] =='SPECIAL'){
            return 0 ;
        }

        $earn_total = numberSprintf($earn_total,6);

        if($earn_total <=0){
            return  0 ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');
        $earn_obj = new MiningMachineEarn();

        $user_id = $user_info['id'];
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $earn_total ;
        $add_data['business_id'] = 0 ;
        $add_data['coin'] = 'USDT' ;
        $add_data['date'] = date('Ymd') ;
        $add_data['date_timestamp'] = strtotime(date('Y-m-d 00:00:00')) ;
        $add_data['type'] = 'TEAM_EARN' ;
        $add_data['user_level'] = $user_info['user_level'] ;
        $add_data['user_root_path'] = $user_info['user_root_path'] ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        $this->baseInsert($earn_obj::tableName(),$add_data);

        // 变更账户余额
        $this->addBalance($user_id,$earn_total);

        // 更新用户资产
        $balance_obj = new MiningMachineUserBalance();
        $balance_obj->addByShare($user_id,$earn_total);

        // 新增用户资产记录
        $balance_record_obj = new MiningMachineUserBalanceRecord();
        $balance_record_obj->addByShareTeam($user_id,$earn_total);

        return $earn_total ;
    }

    /**
     * 获取总的邀请人数
     * @param $user_id
     * @return mixed
     */
    public function getTotalInviteNum($user_id){

        $params['cond'] = 'user_root_path like "%--'.$user_id.'--%" AND id>:id';
        $params['args'] = [':id'=>0];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        $total = $info && !is_null($info['total']) ? $info['total'] : 0 ;
        return $total ;
    }

    /**
     * 获取总的邀请列表
     * @param $user_id
     * @param $max_level
     * @return mixed
     */
    public function getInviteUserList($user_id,$max_level){

        $params['cond'] = 'user_root_path like "%--'.$user_id.'--%" AND user_level <=:user_level';
        $params['args'] = [':user_level'=>$max_level];
        $params['fields'] = 'username,mobile,create_time';
        $params['orderby'] = 'id desc';
        $list = $this->findAllByWhere('sea_user',$params,self::getDb());
        if($list){
            foreach ($list as $k=>$v){
                $mobile = $v['mobile'];
                $list[$k]['mobile'] = formatPhone($mobile);
            }
        }
        return $list ;
    }

    /**
     * 获取用户团队级别
     * @param $user_info
     * @param $value  对应的新增算力总和
     * @param $pay_user_info
     * @return int|mixed
     */
    public function getTeamLevel($user_info,$value,$pay_user_info=[]){

        $site_config = new SiteConfig();
        //$team_total = $user_info['team_total'] - $user_info['self_total'] ;
        $team_total = $user_info['team_total']  ;
        $before_team_total = $team_total - $value ;

        $p1_tuandui_yeji = $site_config->getByKey('p1_tuandui_yeji');
        $p2_tuandui_yeji = $site_config->getByKey('p2_tuandui_yeji');
        $p3_tuandui_yeji = $site_config->getByKey('p3_tuandui_yeji');

        // 判断基础业绩是否符合要求
        $earn_obj = new MiningMachineEarn();
        $dict_info = compact('p1_tuandui_yeji','p2_tuandui_yeji','p3_tuandui_yeji');
        $team_level = $earn_obj->getTeamLevel($before_team_total,$dict_info) ;

        if(!$team_level){
            return 0 ;
        }

        //  判断团队其他业绩是否符合要求
        $dict_info['p1_qita_yeji'] = $site_config->getByKey('p1_qita_yeji');
        $dict_info['p2_qita_yeji'] = $site_config->getByKey('p1_qita_yeji');
        $dict_info['p3_qita_yeji'] = $site_config->getByKey('p3_qita_yeji');
        $check_other_level = $earn_obj->checkOtherLevel($team_level,$dict_info,$user_info,$value,$pay_user_info);

        return $check_other_level ;
    }

    /**
     * 根据手机号码获取指定的用户ID集合
     * @param $mobile
     * @return mixed
     */
    public function getUserIdsByMobile($mobile){

        $params['cond'] = ' mobile like :mobile';
        $params['args'] = [':mobile'=>'%'.$mobile.'%'];
        $params['fields'] = 'id';
        $list = $this->findAllByWhere('sea_user',$params,self::getDb());
        if(!$list){
            return  [] ;
        }
        $res = [];
        foreach($list as $v){
            $res[] = $v['id'];
        }
        return $res ;
    }

    /**
     * 根据手机号码获取指定的用户ID集合
     * @param $mobile
     * @param $fields
     * @return mixed
     */
    public function getUserInfoByMobile($mobile,$fields="*"){

        $params['cond'] = ' mobile = :mobile';
        $params['args'] = [':mobile'=>$mobile];
        $params['fields'] = $fields;
        $info = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info ;
    }

    /**
     * 处理入金金额
     * @param $user_id
     * @param $value
     * @param $blockHash
     * @param $is_admin
     * @return mixed
     */
    public function addRobotCashInRecord($user_id,$value,$blockHash,$is_admin=false){
        $value = round($value,2);

        if($value <=0){
            return false ;
        }

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();

        try {
            // 更新余额
            $update_data['balance'] = new Expression("balance + " . $value);
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate('sea_user',$update_data,'id=:id',[':id'=>$user_id]);

            // 查询用户级别信息
            $user_info = $this->getUserInfoById($user_id);
            $inviter_user_id = $user_info['inviter_user_id'];
            $user_root_path = $user_info['user_root_path'];
            $user_level = $user_info['user_level'];

            // 入金金额
            $add_data['user_id'] = $user_id ;
            $add_data['amount'] = $value ;
            $add_data['status'] = 'NOPUSH' ;// 标记为未处理
            $add_data['inviter_user_id'] = $inviter_user_id ;
            $add_data['user_root_path'] = $user_root_path ;
            $add_data['user_level'] = $user_level ;
            $add_data['blockHash'] = $blockHash ;
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $record_obj = new RobotCashInRecord();
            $record_obj->baseInsert($record_obj::tableName(),$add_data);

            // 增加用户资产
            $balance_obj = new RobotUserBalance();
            $balance_obj->addByCashIn($user_id,$value);

            // 增加入金记录
            $balance_record_obj = new RobotUserBalanceRecord() ;
            $balance_record_obj->addRecordByCashIn($user_id,$value,$is_admin);

            $transaction->commit();
            return true ;
        } catch (\Exception $e) {

            $transaction->rollBack();
            $this->setError(100037);
            return false;
        }
    }


    ###############################################################################################################
    /**
     * 根据注册用户ID生成关系数据
     * @params $user_id
     * @params $parent_id
     * @return mixed
     */
    public function addParentTools($userid = '',$parentid = ''){
        if($userid === '' || $parentid === ''){
            return false;
        }
        //查询父级关系
        $add_datas = [];
        $parent_info = $this->getUserParentList($parentid);
        if($parent_info){
            foreach($parent_info as $k=>$v){
                if($v['parentid']!='0'){
                    $add_datas[] = [
                        'userid'=>$userid,
                        'parentid'=>$v['parentid'],
                        'level'=>($v['level']+1),
                        'addtime'=>time()
                    ];
                }
            }
            array_push($add_datas,['userid'=>$userid,'parentid'=>$parentid,'level'=>1,'addtime'=>time()]);
        }else{
            $add_datas[] = [
                'userid'=>$userid,
                'parentid'=>0,
                'level'=>0,
                'addtime'=>time()
            ];
        }
        foreach($add_datas as $k=>$v){
            $this->baseInsert('sea_robot_user_parent',$v);
        }
    }

    /**
     * 根据用户父级ID返回用户信息
     * @param $userid
     * @param $fields
     * @return mixed
     */
    public function getUserParentList($userid = ''){
        $params = [
            'cond'=>' userid = :userid',
            'args'=>[':userid'=>$userid],
            'fields'=>'*',
            'orderby'=>'level ASC'
        ];
        $list = $this->findAllByWhere('sea_robot_user_parent',$params,self::getDb());
        return $list ? $list : [];
    }

    /**
     * 修改用户邀请人
     * @params $userid
     * @params $inviter_user_id
     * @params $inviter_username
     * @return boolean
     */
    public function modifyUserInviter($userid = '', $inviter_user_id = '', $inviter_username = ''){
        if($userid == '' || $inviter_user_id == '' || $inviter_username == ''){
            return false;
        }
        $update_data = [
            'inviter_user_id'=>$inviter_user_id,
            'inviter_username'=>$inviter_username
        ];
        return $this->baseUpdate('sea_user',$update_data,'id=:id',[':id'=>$userid]);
    }

    /**
     * 获取自己邀请码信息
     * @param $user_id
     * @return string
     */
    public function getSelfInviteCode($user_id){
        $user_info = $this->getUserInfoById($user_id,'invite_code');
        return $user_info?$user_info['invite_code']:'';
    }

    /**
     * 根据邀请码检查是否合法（填写了下级的邀请码）
     * @param $user_id
     * @param $invite_code
     * @return boolean
     */
    public function verifyInviteCode($user_id,$invite_code){
        $sqlstr = "SELECT a.userid,b.invite_code FROM sea_robot_user_parent AS a LEFT JOIN sea_user AS b ON a.userid=b.id WHERE a.parentid='".$user_id."' AND b.invite_code='".$invite_code."'";
        $command = Yii::$app->db->createCommand($sqlstr);
        $res = $command->queryOne();
        return $res ? true : false;
    }

    /**
     * 后台扣减USDT余额
     * @param $user_id
     * @param $value
     * @param $blockHash
     * @param $is_admin
     * @return mixed
     */
    public function reduceUsdtByAdmin($user_id,$value){
        $value = round($value,2);

        if($value <=0){
            return false ;
        }

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();

        try {
            // 更新余额
            $update_data['balance'] = new Expression("balance - " . $value);
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate('sea_user',$update_data,'id=:id AND balance - '.$value.' > 0',[':id'=>$user_id]);

            $balance_obj = new RobotUserBalance();
            $coin = 'USDT';
            $res = $balance_obj->reduceCoinBalanceByAdmin($user_id,$coin,$value);
            if(!$res){
                return false ;
            }

            $transaction->commit();
            return true ;
        } catch (\Exception $e) {

            $transaction->rollBack();
            $this->setError(100037);
            return false;
        }
    }

    // 获取授权登录ID信息
    public  function getLoginUserIdByAccessToken(){
        $token = isset($_SERVER['HTTP_ACCESSTOKEN'])?$_SERVER['HTTP_ACCESSTOKEN'] :[];

        try {

            $token = (new \Lcobucci\JWT\Parser())->parse($token);

            //数据校验
            $data = new ValidationData(); // 使用当前时间来校验数据
            if (!$token->validate($data)) {
                //数据校验失败
                return 0;
            }

        }catch (\Exception $e){
            return 0;
        }

        //token校验
        $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();//生成JWT时使用的加密方式
        if (!$token->verify($signer, new Key(JWT_SECRET_TOKEN))) {
            //token校验失败
            return 0;
        }

        $res = object_to_array($token->getClaim('user'));
        $login_user_id = isset($res['user_id']) ? $res['user_id'] : 0;
        return $login_user_id;
    }
}