<?php

namespace common\models;

use common\components\Filter;
use Yii;

/**
 * This is the model class for table "sea_sunny_manager".
 *
 * @property int $id
 * @property int $company_id 关联公司ID
 * @property int $country_id 国家ID
 * @property string $email 邮箱
 * @property string $passowrd 密码
 * @property string $note 备注
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyManager extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_manager';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'country_id'], 'integer'],
            [['note'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['email', 'passowrd'], 'string', 'max' => 255],
            [['is_deleted'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => 'Company ID',
            'country_id' => 'Country ID',
            'email' => 'Email',
            'passowrd' => 'Passowrd',
            'note' => 'Note',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据邮箱获取用户信息
     * @param $email
     * @return array|bool
     */
    public function getInfoByEmail($email){
        $params['cond'] = 'email=:email';
        $params['args'] = [':email'=>$email];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据邮箱和密码获取用户信息
     * @param $email
     * @param $password
     * @return array|bool
     */
    public function getInfoByEmailAndPassword($email,$password){
        $params['cond'] = 'email=:email AND password=:passowrd';
        $params['args'] = [':email'=>$email,':passowrd'=>md5($password)];
    
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }
    /**
     * 根据Id获取用户信息
     * @param $id
     * @return array|bool
     */
    public function getInfoById($id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取用户指定的公司ID
     * @param $id
     * @return mixed
     */
    public function getCompanyIdById($id){
        $info = $this->getInfoById($id);
        return $info?$info['company_id'] : 0 ;
    }

    /**
     * 用户根据邮箱注册
     * @param $email
     * @param $code
     * @param $password
     * @param $repeat_password
     * @param $company_code
     * @return mixed
     */
    public function doRegisterByEmail($email,$code,$password,$repeat_password,$company_code){

        // 验证手机号码是否重复
        $filter = new Filter();
        $check_mobile = $filter->C_email($email);
        if(!$check_mobile){
            return ['code'=>100069] ;
        }

        $user_info = $this->getInfoByEmail($email);
        if($user_info){
            return ['code'=>100068] ;
        }

        // 公司信息
        $company_obj = new SunnyCompany();
        $company_info = $company_obj->getInfoByUniqueKey($company_code);
        if(!$company_info){
            return ['code'=>100070] ;
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

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            // 当前时间
            $now = date('Y-m-d H:i:s');

            $add_data['company_id'] = $company_info['id'] ;
            $add_data['country_id'] = $company_info['country_id'] ;
            $add_data['password'] = md5($password) ;
            $add_data['email'] = $email ;
            $add_data['note'] = '' ;
            $add_data['is_deleted'] = 'N' ;
            $add_data['create_time'] = $now ;
            $add_data['modify_time'] = $now ;
            $this->baseInsert(self::tableName(),$add_data);

            $transaction->commit();
            return ['code'=>1];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['code'=>100024];
        }
    }

    /**
     * 充值密码
     * @param $email
     * @param $code
     * @param $password
     * @param $repeat_password
     * @return mixed
     */
    public function resetPassword($email,$code,$password,$repeat_password){
        $filter = new Filter();
        $check_mobile = $filter->C_email($email);
        if(!$check_mobile){
            return ['code'=>100069] ;
        }

        $user_info = $this->getInfoByEmail($email);
        if(!$user_info){
            return ['code'=>100071] ;
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
        $check_code_res = $email_log_obj->checkCode($email,$code,'FORGET');
        if(!$check_code_res){
            return ['code'=>100072] ;
        }

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            // 当前时间
            $now = date('Y-m-d H:i:s');

            // 更新操作
            $add_data['password'] = md5($password) ;
            $add_data['modify_time'] = $now ;

            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$user_info['id']]);

            $transaction->commit();
            return ['code'=>1];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['code'=>100073];
        }

    }

    public function returnRoleTypeList(){
        //角色类型 REPAIR-维修员 WORKER-施工员 OBSERVER-观察员
        return [
            'REPAIR' =>'维修员',
            'WORKER' =>'施工员',
            'OBSERVER' =>'观察员',
        ];
    }

    /**
     * 判断唯一key值是否重复
     * @param $unique_key
     * @param $id
     * @return mixed
     */
    public function checkRepeatKey($unique_key,$id){

        $params['cond'] = 'email=:unique_key AND id !=:id';
        $params['args'] = [':unique_key'=>$unique_key,':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取指定公司的所有列表
     * @param $company_id
     * @param $fields
     * @return mixed
     */
    public function getListByCompanyId($company_id,$fields="*"){
        $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted';
        $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据邮箱获取用户信息
     * @param $username
     * @return array|bool
     */
    public function getInfoByUsername($username){
        $params['cond'] = 'username=:username AND is_deleted=:is_deleted';
        $params['args'] = [':username'=>$username,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }
}
