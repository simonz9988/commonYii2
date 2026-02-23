<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "sea_admin_role".
 *
 * @property string $id
 * @property string $creater 创建人员
 * @property string $create_time 创建时间
 * @property string $modifier 更新人员
 * @property string $modify_time 更新时间
 * @property string $version 版本信息
 * @property string $role_key 角色的唯一标识
 * @property string $role_value 角色名称
 * @property string $role_description 相关描述
 * @property int $is_open 是否有效 1-有效 0-无效
 * @property int $sort 排序
 */
class AdminRole extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_role';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['version'], 'required'],
            [['version', 'is_open', 'sort'], 'integer'],
            [['creater', 'modifier', 'role_key', 'role_value'], 'string', 'max' => 50],
            [['role_description'], 'string', 'max' => 200],
            [['role_key'], 'unique'],
            [['role_value'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'creater' => 'Creater',
            'create_time' => 'Create Time',
            'modifier' => 'Modifier',
            'modify_time' => 'Modify Time',
            'version' => 'Version',
            'role_key' => 'Role Key',
            'role_value' => 'Role Value',
            'role_description' => 'Role Description',
            'is_open' => 'Is Open',
            'sort' => 'Sort',
        ];
    }

    /**
     * 判断用户是否具有该权限
     * @param $adminUserInfo
     * @param $controller
     * @param $function
     * @return bool
     */
    public function checkRolePrivilege($adminUserInfo,$controller,$function){

        if($adminUserInfo['username']=='admin'){
            return true ;
        }

        $role_id = $adminUserInfo['role_id'];
        $privilege_model = new AdminPrivilege();
        $privilege_info = $privilege_model->checkExists($controller,$function);
        if(!$privilege_info){
            return false ;
        }

        $params['cond'] = 'role_id =:role_id AND privilege_id=:privilege_id ';
        $params['args'] = [':role_id'=>$role_id,':privilege_id'=>$privilege_info['id']];
        $info = $this->findOneByWhere('sea_admin_role_privilege',$params,self::getDb()) ;
        if(!$info){
            return false ;
        }


        return true;
    }

    /**
     * 获取用户的所有的权限列表
     * @param $adminUserInfo
     * @return mixed
     */
    public function getUserPrivilegeList($adminUserInfo){
        $role_id = $adminUserInfo['role_id'];
        $params['cond'] = 'role_id =:role_id  ';
        $params['args'] = [':role_id'=>$role_id];
        $list = $this->findAllByWhere('sea_admin_role_privilege',$params,self::getDb()) ;
        if(!$list){
            return  [] ;
        }
        $privilege_id = [];
        foreach($list as $v){
            $privilege_id[] = $v['privilege_id'];
        }
        $privilege_model = new AdminPrivilege();
        $p_params['cond'] = ' id in ('.implode(',',$privilege_id).') AND id >:id';
        $p_params['args'] = [':id'=>0];
        $p_list = $this->findAllByWhere($privilege_model::tableName(),$p_params,self::getDb());
        $res = [] ;
        if($p_list){
            foreach($p_list as $v){
                $res[] = '/'.$v['controller'].'/'.$v['function'];
            }
        }
        return $res ;

    }

}
