<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_country_detail".
 *
 * @property int $id
 * @property int $country_id 国家ID
 * @property int $language_id 语言
 * @property string $country_name 展示名称
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class CountryDetail extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_country_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'language_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['country_name'], 'string', 'max' => 255],
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
            'country_id' => 'Country ID',
            'language_id' => 'Language ID',
            'country_name' => 'Country Name',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
}
