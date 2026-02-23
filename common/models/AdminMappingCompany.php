<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_admin_mapping_company".
 *
 * @property int $id
 * @property int $admin_id 管理员ID
 * @property int $company_id 公司ID
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class AdminMappingCompany extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_mapping_company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id', 'company_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
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
            'admin_id' => 'Admin ID',
            'company_id' => 'Company ID',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回列表信息
     * @param $admin_id
     * @return mixed
     */
    public function getListByAdminId($admin_id){
        $params['cond'] = 'admin_id=:admin_id AND is_deleted=:is_deleted';
        $params['args'] = [':admin_id'=>$admin_id,':is_deleted'=>'N'] ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    public function getCompanyIdsByAdminId($admin_id){

        $list = $this->getListByAdminId($admin_id);
        if(!$list){
            return [];
        }

        $res = [] ;
        foreach($list as $v){
            $res[] = $v['company_id'];
        }
        return $res ;
    }

    /**
     * 根据管理员ID删除所有关联关系
     * @param $admin_id
     * @return mixed
     */
    public function deleteByAdminId($admin_id){
        $update_data['is_deleted'] ='Y';
        $update_data['modify_time'] =date('Y-m-d H:i:s');
        return $this->baseUpdate(self::tableName(),$update_data,'admin_id=:admin_id',[':admin_id'=>$admin_id]) ;
    }
}
