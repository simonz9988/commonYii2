<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_status_daily_record".
 *
 * @property int $id
 * @property int $device_id 设备ID
 * @property int $date 所属日期(20210114)
 * @property int $date_timestamp 对应日期对应的时间戳方便筛选
 * @property string $device_no 设备编号
 * @property string $battery_volume 电池电量
 * @property string $battery_voltage 电池电压
 * @property string $battery_charging_current 电池充电电流
 * @property string $ambient_temperature 环境温度
 * @property string $battery_panel_charging_voltage 电池面板充电电压
 * @property string $charging_current 充电电流
 * @property string $charging_power 充电电压
 * @property string $cumulative_charge 累计充电电量(KW时)
 * @property string $load_status 负载状态(有/无)
 * @property string $switch_status 开关状态
 * @property string $brightness 亮度%
 * @property string $create_time 创建时间
 */
class SunnyDeviceStatusDailyRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_status_daily_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id', 'date', 'date_timestamp'], 'integer'],
            [['create_time'], 'safe'],
            [['device_no', 'battery_volume', 'battery_voltage', 'battery_charging_current', 'ambient_temperature', 'battery_panel_charging_voltage', 'charging_current', 'charging_power', 'cumulative_charge', 'load_status', 'switch_status', 'brightness'], 'string', 'max' => 50],
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
            'date' => 'Date',
            'date_timestamp' => 'Date Timestamp',
            'device_no' => 'Device No',
            'battery_volume' => 'Battery Volume',
            'battery_voltage' => 'Battery Voltage',
            'battery_charging_current' => 'Battery Charging Current',
            'ambient_temperature' => 'Ambient Temperature',
            'battery_panel_charging_voltage' => 'Battery Panel Charging Voltage',
            'charging_current' => 'Charging Current',
            'charging_power' => 'Charging Power',
            'cumulative_charge' => 'Cumulative Charge',
            'load_status' => 'Load Status',
            'switch_status' => 'Switch Status',
            'brightness' => 'Brightness',
            'create_time' => 'Create Time',
        ];
    }

    /**

    ;

     */
}
