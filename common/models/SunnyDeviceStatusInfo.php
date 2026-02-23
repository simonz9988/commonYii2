<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_status_info".
 *
 * @property int $id
 * @property int $device_id 设备ID
 * @property int $company_id 公司ID
 * @property int $category_id 分类ID
 * @property int $parent_id 父级ID
 * @property string $device_no 设备编号
 * @property string $battery_panel_charging_voltage 太阳能板电压)
 * @property string $battery_panel_charging_current 太阳能板电流
 * @property string $charging_power 太阳能充电功率
 * @property string $battery_volume 电池电量（蓄电池电量）
 * @property string $battery_voltage 电池电压(蓄电池电压)
 * @property string $battery_charging_current 电池充电电流（充电电流）
 * @property string $battery_temperature 蓄电池温度(新增)
 * @property string $charging_current (直流负载电流)充电电流
 * @property string $load_dc_power (直流负载电压)充电电压
 * @property string $cumulative_charge (直流负载功率)累计充电电量(KW时)
 * @property string $load_status 负载状态(路灯状态)
 * @property string $switch_status 开关状态(当前值和负载状态保持一致)
 * @property string $brightness 亮度%
 * @property string $charge_status 充电状态
 * @property string $ambient_temperature 环境温度（控制器温度）
 * @property string $is_deleted
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class SunnyDeviceStatusInfo extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_status_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id', 'company_id', 'category_id', 'parent_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['device_no', 'battery_panel_charging_voltage', 'battery_panel_charging_current', 'charging_power', 'battery_volume', 'battery_voltage', 'battery_charging_current', 'battery_temperature', 'charging_current', 'load_dc_power', 'cumulative_charge', 'load_status', 'switch_status', 'brightness', 'charge_status', 'ambient_temperature'], 'string', 'max' => 50],
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
            'company_id' => 'Company ID',
            'category_id' => 'Category ID',
            'parent_id' => 'Parent ID',
            'device_no' => 'Device No',
            'battery_panel_charging_voltage' => 'Battery Panel Charging Voltage',
            'battery_panel_charging_current' => 'Battery Panel Charging Current',
            'charging_power' => 'Charging Power',
            'battery_volume' => 'Battery Volume',
            'battery_voltage' => 'Battery Voltage',
            'battery_charging_current' => 'Battery Charging Current',
            'battery_temperature' => 'Battery Temperature',
            'charging_current' => 'Charging Current',
            'load_dc_power' => 'Load Dc Power',
            'cumulative_charge' => 'Cumulative Charge',
            'load_status' => 'Load Status',
            'switch_status' => 'Switch Status',
            'brightness' => 'Brightness',
            'charge_status' => 'Charge Status',
            'ambient_temperature' => 'Ambient Temperature',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据设备ID返回有效信息
     * @param $device_id
     * @return mixed
     */
    public function getInfoByDeviceId($device_id){
        $params['cond'] = 'device_id=:device_id AND is_deleted=:is_deleted';
        $params['args'] = [':device_id'=>$device_id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据转换后的信息同步记录
     * @param $post_data
     * @return mixed
     */
    public function syncByAddData($post_data){

        $add_data['battery_panel_charging_voltage'] = $post_data['battery_panel_charging_voltage'];
        $add_data['battery_panel_charging_current'] = $post_data['battery_panel_charging_current'];
        $add_data['charging_power'] = $post_data['charging_power'];
        $add_data['battery_volume'] = $post_data['battery_volume'];
        $add_data['battery_voltage'] = $post_data['battery_voltage'];
        $add_data['battery_charging_current'] = $post_data['battery_charging_current'];
        $add_data['battery_temperature'] = $post_data['battery_temperature'];
        $add_data['charging_current'] = $post_data['charging_current'];
        $add_data['load_dc_power'] = $post_data['load_dc_power'];
        $add_data['cumulative_charge'] = $post_data['cumulative_charge'];
        $add_data['load_status'] = $post_data['load_status'];
        $add_data['switch_status'] = $post_data['switch_status'];
        $add_data['brightness'] = $post_data['brightness'];
        $add_data['charge_status'] = $post_data['charge_status'];
        $add_data['ambient_temperature'] = $post_data['ambient_temperature'];

        $info = $this->getInfoByDeviceId($post_data['device_id']);
        if($info){
            // 更新
            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$info['id']]);
        }else{

            // 查询设备基本信息
            $device_obj = new SunnyDevice();
            $device_info = $device_obj->getInfoById($post_data['device_id']);
            // 新增
            $add_data['device_id'] = $device_info['id'];
            $add_data['company_id'] = $device_info['company_id'];
            $add_data['category_id'] = $device_info['category_id'];
            $add_data['parent_id'] = $device_info['parent_id'];
            $add_data['device_no'] = $device_info['device_no'];
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $this->baseInsert(self::tableName(),$add_data);
        }

        return true ;
    }

    /**
     * 总发电功率 发电电压*发电电流
     * @param $customer_id
     * @param $project_id
     * @return mixed
     */
    public function getTotalGeneratingPower($customer_id,$project_id){
        $params['cond'] = 'customer_id=:customer_id  AND is_deleted=:is_deleted' ;
        $params['args'] = [':customer_id'=>$customer_id,':is_deleted'=>'N'];
        if($project_id){
            $params['cond'] = 'customer_id=:customer_id  AND is_deleted=:is_deleted AND project_id=:project_id' ;
            $params['args'] = [':customer_id'=>$customer_id,':is_deleted'=>'N',':project_id'=>$project_id];
        }
        $params['fields'] = ' sum(battery_panel_charging_voltage*battery_panel_charging_current) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }

    /**
     * 返回充电状态列表
     * @return string[]
     */
    public function getChargeList(){
        return  ['未开启充电','启动充电模式','mppt 充电模式','均衡充电模式','提升充电模式','浮充充电模式','限流(超功率)'];
    }

    /**
     * 获取充电状态名称
     * @param $charge_status
     * @return mixed
     */
    public function getChargeStatusName($charge_status){
        $list = $this->getChargeList();
        $name = isset($list[$charge_status]) ? $list[$charge_status] :'';
        return $name ;
    }
}
