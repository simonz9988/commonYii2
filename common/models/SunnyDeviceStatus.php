<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_status".
 *
 * @property int $id
 * @property int $device_id 设备ID
 * @property int $index_id 主键ID(对应USB端口号)
 * @property string $status 对应状态
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceStatus extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_status';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id', 'index_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['status'], 'string', 'max' => 255],
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
            'index_id' => 'Index ID',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
}
