<?php

namespace backend\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "sea_admin".
 *
 * @property string $id
 * @property string $username
 * @property string $nickname
 * @property string $password
 * @property int $logincount
 * @property string $loginip
 * @property int $logintime
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 * @property int $groupid
 * @property int $role_id 角色ID
 * @property int $state
 * @property int $is_open 是否有效1-有效0-无效
 * @property int $sort 排序
 */
class Admin extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['logincount', 'logintime', 'groupid', 'role_id', 'state', 'is_open', 'sort'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['groupid', 'role_id'], 'required'],
            [['username', 'nickname'], 'string', 'max' => 20],
            [['password'], 'string', 'max' => 32],
            [['loginip'], 'string', 'max' => 16],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'nickname' => 'Nickname',
            'password' => 'Password',
            'logincount' => 'Logincount',
            'loginip' => 'Loginip',
            'logintime' => 'Logintime',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'groupid' => 'Groupid',
            'role_id' => 'Role ID',
            'state' => 'State',
            'is_open' => 'Is Open',
            'sort' => 'Sort',
        ];
    }

    /**
     * 根据用户名和密码获取用户信息
     * @param $username
     * @param $password
     * @return mixed
     */
    public function getUserInfoByPassword($username,$password){

        $params['cond'] = 'username=:username AND password=:password';
        $params['args'] = [':username'=>$username,':password'=>md5($password)];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb()) ;
        return $info  ;
    }

    //获取后台用户所有的json信息
    public function getUserMenu(){

        $privilege_model = new AdminPrivilege() ;
        $menu_arr = $privilege_model->getAllMenuByUser();

        $rst = array();
        if($menu_arr){
            foreach($menu_arr as $v){
                $all_sun = isset($v['all_sun'])?$v['all_sun']:array();
                $menus_arr = array();
                if($all_sun){
                    foreach($all_sun as $sun_v){
                        $menus_arr[] = array(
                            'menuname' => $sun_v['name'],
                            'icon'=>'icon-nav',
                            'menu_cate_unique_key'=>$sun_v['menu_cate_unique_key'],
                            'menu_cate_name'=>$sun_v['menu_cate_name'],
                            'unique_key'=>$sun_v['controller'].'_'.$sun_v['function'],
                            'url'=>'/'.$sun_v['controller'].'/'.$sun_v['function'],
                        );
                    }
                }

                $rst[] = array(
                    'menuid'=>$v['id'],
                    'icon'=>'icon-sys',
                    'menu_cate_unique_key'=>$v['menu_cate_unique_key'],
                    'menu_cate_name'=>$v['menu_cate_name'],
                    'menuname'=>$v['name'],
                    'unique_key'=>$v['controller'].'_'.$v['function'],
                    'menus'=>$menus_arr,
                );
            }
        }
        //pre($rst,1);
        $info['menus'] = $rst ;
        return $rst;

    }

    public function getAllowedMenusCate($adminMenuList){

        $rst = array();
        if($adminMenuList){
            foreach($adminMenuList as $v){
                if(!isset($rst[$v['menu_cate_unique_key']])) {
                    $rst[$v['menu_cate_unique_key']] = array(
                        'menu_cate_name'=>$v['menu_cate_name']
                    );
                }
            }
        }
        return $rst ;
    }

    public function getUserRoleName($role_id){


        $params['where_arr']['id'] = $role_id ;
        $params['return_type'] = 'row';
        $info = $this->findByWhere('sea_admin_role',$params);
        $rst = isset($info['role_value'])?$info['role_value']:'';

        return $rst ;
    }

    /**
     * 创建google对应的私钥
     * @param int $code_length
     * @return mixed
     */
    public function generateGooglePrivateKey( $code_length = 12) {
        $characters = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";


        $google_private_key = "";

        // 生成随机数
        for ($i = 0; $i < $code_length; $i++) {
            $google_private_key .= $characters[mt_rand(0, strlen($characters)-1)];
        }



        $db = new Query();
        $rst = $db->select('google_private_key')->from("sea_admin")->where([
            "google_private_key" => $google_private_key
        ])->one();

        if ($rst) {
            return $this->generateGooglePrivateKey();
        } else {
            return $google_private_key;
        }
    }

    /**
     * 根据后台用户ID返回邮箱信息
     * @param $admin_user_id
     * @return string
     */
    public function getEmailByUserId($admin_user_id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$admin_user_id];
        $params['fields'] = 'email';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['email']:'';
    }

    /**
     * 根据私钥返回
     * @param $secret_key
     * @return mixed
     */
    public function getInfoBySecretKey($secret_key){
        $params['cond'] = 'secret_key=:secret_key';
        $params['args'] = [':secret_key'=>$secret_key];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据用户标识符
     * @param $mark_key
     * @return array|bool
     */
    public function getInfoByUserMark($mark_key){
        $params['cond'] = 'mark_key=:mark_key';
        $params['args'] = [':mark_key'=>$mark_key];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }


}
