<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "sea_admin_role_privilege".
 *
 * @property string $id
 * @property string $creater
 * @property string $create_time
 * @property string $modifier
 * @property string $modify_time
 * @property string $version
 * @property string $role_id
 * @property string $privilege_id
 */
class AdminRolePrivilege extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_role_privilege';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['creater', 'modifier', 'version'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['version', 'role_id', 'privilege_id'], 'integer'],
            [['creater', 'modifier'], 'string', 'max' => 50],
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
            'role_id' => 'Role ID',
            'privilege_id' => 'Privilege ID',
        ];
    }

    /**
     * 根据角色ID返回权限列表
     * @param $role_id
     * @return mixed
     */
    public function getListByRoleId($role_id){
        $params['cond'] = 'role_id=:role_id';
        $params['args'] = [':role_id'=>$role_id];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
}
