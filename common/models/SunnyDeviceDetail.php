<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_detail".
 *
 * @property int $id
 * @property int $device_id 设备ID
 * @property int $language_id 对应语言表 ID
 * @property string $name 设备名称
 * @property string $sop_url 安装SOP(文件存储路径)
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceDetail extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id', 'language_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['name', 'sop_url'], 'string', 'max' => 255],
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
            'device_id' => 'Device ID',
            'language_id' => 'Language ID',
            'name' => 'Name',
            'sop_url' => 'Sop Url',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
}
