<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_company_detail".
 *
 * @property int $id
 * @property int $company_id 公司ID
 * @property int $language_id 语言ID
 * @property string $name 名称
 * @property string $address 地址
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyCompanyDetail extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_company_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'language_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['name', 'address'], 'string', 'max' => 255],
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
            'language_id' => 'Language ID',
            'name' => 'Name',
            'address' => 'Address',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 删除关联公司的补充信息
     * @param $company_id
     * @return mixed
     */
    public function deleteByCompanyId($company_id){

        $update_data['is_deleted'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        return $this->baseUpdate(self::tableName(),$update_data,'company_id=:id',[':id'=>$company_id]);
    }
}
