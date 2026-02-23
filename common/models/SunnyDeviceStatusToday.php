<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_status_today".
 *
 * @property int $id
 * @property int $device_id 设备ID
 * @property int $company_id 公司ID
 * @property int $category_id 分类ID
 * @property int $parent_id 父级ID
 * @property string $device_no 设备编号
 * @property string $date 所属日期
 * @property int $date_timestamp 当日日期时间戳
 * @property string $bat_min_volt_today 蓄电池当天最低电
 * @property string $bat_max_volt_today 蓄电池当天最高电
 * @property string $bat_max_chg_current_today 当天放电最大电流
 * @property string $bat_max_charge_power_today 当天充电最大功率
 * @property string $bat_max_discharge_power_today 当天放电最大功率
 * @property string $bat_charge_ah_today 当天充电安时数
 * @property string $bat_discharge_ah_today 当天放电安时数
 * @property string $generat_energy_today 当天发电量
 * @property string $used_energy_today 当天用电量
 * @property string $load_status 负载状态
 * @property string $brightness 亮度
 * @property string $charge_status 充电状态
 * @property string $fault_info 错误信息
 * @property string $bat_highest_temper 当天电池最高温度
 * @property string $bat_lowest_temper 当天电池最低温度
 * @property string $led_sensor_on_time 当天亮灯时间 （有人
 * @property string $led_sensor_off_time 当天亮灯时间 （无人）
 * @property string $led_light_on_index 亮灯指数
 * @property string $power_save_index 能耗指数
 * @property string $sys_health_index 健康指数
 * @property string $bat_charge_time 当天充电时间
 * @property string $night_length 夜晚长度
 * @property string $is_deleted
 * @property string $create_time 创建时间
 * @property string $modify_time
 */
class SunnyDeviceStatusToday extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_status_today';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id', 'company_id', 'category_id', 'parent_id', 'date_timestamp'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['device_no', 'date', 'bat_min_volt_today', 'bat_max_volt_today', 'bat_max_chg_current_today', 'bat_max_charge_power_today', 'bat_max_discharge_power_today', 'bat_charge_ah_today', 'bat_discharge_ah_today', 'generat_energy_today', 'used_energy_today', 'load_status', 'brightness', 'charge_status'], 'string', 'max' => 50],
            [['fault_info', 'bat_highest_temper', 'bat_lowest_temper', 'led_sensor_on_time', 'led_sensor_off_time', 'led_light_on_index', 'power_save_index', 'sys_health_index', 'bat_charge_time', 'night_length'], 'string', 'max' => 255],
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
            'date' => 'Date',
            'date_timestamp' => 'Date Timestamp',
            'bat_min_volt_today' => 'Bat Min Volt Today',
            'bat_max_volt_today' => 'Bat Max Volt Today',
            'bat_max_chg_current_today' => 'Bat Max Chg Current Today',
            'bat_max_charge_power_today' => 'Bat Max Charge Power Today',
            'bat_max_discharge_power_today' => 'Bat Max Discharge Power Today',
            'bat_charge_ah_today' => 'Bat Charge Ah Today',
            'bat_discharge_ah_today' => 'Bat Discharge Ah Today',
            'generat_energy_today' => 'Generat Energy Today',
            'used_energy_today' => 'Used Energy Today',
            'load_status' => 'Load Status',
            'brightness' => 'Brightness',
            'charge_status' => 'Charge Status',
            'fault_info' => 'Fault Info',
            'bat_highest_temper' => 'Bat Highest Temper',
            'bat_lowest_temper' => 'Bat Lowest Temper',
            'led_sensor_on_time' => 'Led Sensor On Time',
            'led_sensor_off_time' => 'Led Sensor Off Time',
            'led_light_on_index' => 'Led Light On Index',
            'power_save_index' => 'Power Save Index',
            'sys_health_index' => 'Sys Health Index',
            'bat_charge_time' => 'Bat Charge Time',
            'night_length' => 'Night Length',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 判断当天的信息是否存在
     * @param $device_id
     * @return mixed
     */
    public function checkTodayIsExists($device_id){
        $params['cond'] = 'date=:date AND device_id=:device_id AND is_deleted=:is_deleted ';
        $params['args'] = [':date'=>date("Y-m-d"),':device_id'=>$device_id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        return $info ;
    }

    /**
     * 判断当天的信息是否存在
     * @param $device_id
     * @return mixed
     */
    public function getInfoByDateAndDeviceId($device_id,$date){
        $params['cond'] = 'date=:date AND device_id=:device_id AND is_deleted=:is_deleted ';
        $params['args'] = [':date'=>$date,':device_id'=>$device_id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        return $info ;
    }

    /**
     * 更新设备信息
     * @param $device_info
     * @param $post_data
     * @return mixed
     */
    public function  addRecord($device_info,$post_data){

        $info = $this->checkTodayIsExists($device_info['id']);

        $add_data['bat_min_volt_today'] = $post_data['bat_min_volt_today'];
        $add_data['bat_max_volt_today'] = $post_data['bat_max_volt_today'];
        $add_data['bat_max_chg_current_today'] = $post_data['bat_max_chg_current_today'];
        $add_data['bat_max_discharge_current_today'] = $post_data['bat_max_discharge_current_today'];
        $add_data['bat_max_charge_power_today'] = $post_data['bat_max_charge_power_today'];
        $add_data['bat_max_discharge_power_today'] = $post_data['bat_max_discharge_power_today'];
        $add_data['bat_charge_ah_today'] = $post_data['bat_charge_ah_today'];
        $add_data['bat_discharge_ah_today'] = $post_data['bat_discharge_ah_today'];
        $add_data['generat_energy_today'] = $post_data['generat_energy_today'];
        $add_data['used_energy_today'] = $post_data['used_energy_today'];
        $add_data['load_status'] = $post_data['load_status'];
        $add_data['brightness'] = $post_data['brightness'];
        $add_data['charge_status'] = $post_data['charge_status'];
        $add_data['fault_info'] = $post_data['fault_info'];
        $add_data['bat_highest_temper'] = $post_data['bat_highest_temper'];
        $add_data['bat_lowest_temper'] = $post_data['bat_lowest_temper'];
        $add_data['led_sensor_on_time'] = $post_data['led_sensor_on_time'];
        $add_data['led_sensor_off_time'] = $post_data['led_sensor_off_time'];
        $add_data['led_light_on_index'] = $post_data['led_light_on_index'];
        $add_data['power_save_index'] = $post_data['power_save_index'];
        $add_data['sys_health_index'] = $post_data['sys_health_index'];
        $add_data['bat_charge_time'] = $post_data['bat_charge_time'];
        $add_data['night_length'] = $post_data['night_length'];
        $add_data['modify_time'] = date('Y-m-d H:i:s');

        if($info){

            foreach($add_data as $k=>$v){

                // 新传输的值和系统存在的值做比较
                if(!in_array($k,['modify_time','load_status','brightness','charge_status','fault_info','sys_health_index'])){
                    if($add_data[$k] < $info[$k]){
                        unset($add_data[$k]);
                    }
                }

            }
            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$info['id']]);
        }else{

            $add_data['device_id'] = $device_info['id'];
            $add_data['customer_id'] = $device_info['customer_id'];
            $add_data['project_id'] = $device_info['project_id'];
            $add_data['company_id'] = $device_info['company_id'];
            $add_data['category_id'] = $device_info['category_id'];
            $add_data['parent_id'] = $device_info['parent_id'];
            $add_data['device_no'] = $device_info['qr_code'];
            $add_data['date'] = date("Y-m-d");
            $add_data['date_timestamp'] = strtotime(date("Y-m-d"));
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';

            $this->baseInsert(self::tableName(),$add_data);
        }

        return true ;
    }

    /**
     * 获取客户当日总使用电量
     * @param $company_id
     * @param $project_id
     * @param $date
     * @return mixed
     */
    public function getCustomerTodayTotalUsedEnergy($company_id,$project_id,$date=''){
        $date = $date ? $date : date("Y-m-d") ;
        $params['cond'] = 'company_id=:company_id AND date=:date AND is_deleted=:is_deleted' ;
        $params['args'] = [':company_id'=>$company_id,':date'=>$date,':is_deleted'=>'N'];
        if($project_id){
            $params['cond'] = 'project_id=:project_id AND company_id=:company_id AND date=:date AND is_deleted=:is_deleted' ;
            $params['args'] = [':project_id'=>$project_id,':company_id'=>$company_id,':date'=>$date,':is_deleted'=>'N'];
        }
        $params['fields'] = ' sum(used_energy_today) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }

    /**
     * 获取客户当日总使用电量
     * @param $company_id
     * @param $project_id
     * @param $date
     * @return mixed
     */
    public function getCustomerTodayTotalGenerateEnergy($company_id,$project_id,$date=''){
        $date = $date ? $date : date("Y-m-d") ;
        $params['cond'] = 'company_id=:company_id AND date=:date AND is_deleted=:is_deleted' ;
        $params['args'] = [':company_id'=>$company_id,':date'=>$date,':is_deleted'=>'N'];
        if($project_id){
            $params['cond'] = 'project_id=:project_id AND company_id=:company_id AND date=:date AND is_deleted=:is_deleted' ;
            $params['args'] = [':project_id'=>$project_id,':company_id'=>$company_id,':date'=>$date,':is_deleted'=>'N'];
        }
        $params['fields'] = ' sum(generat_energy_today) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }

    /**
     * @param $company_id
     * @param $project_id
     * @param string $date
     * @return int|mixed
     */
    public function getCustomerTotalTotalUsedEnergy($company_id,$project_id,$date=''){
        $date = $date ? $date : date("Y-m-d") ;
        $params['cond'] = 'company_id=:company_id AND date <=:date AND is_deleted=:is_deleted' ;
        $params['args'] = [':company_id'=>$company_id,':date'=>$date,':is_deleted'=>'N'];
        if($project_id){
            $params['cond'] = 'project_id=:project_id AND company_id=:company_id AND date <=:date AND is_deleted=:is_deleted' ;
            $params['args'] = [':project_id'=>$project_id,':company_id'=>$company_id,':date'=>$date,':is_deleted'=>'N'];
        }
        $params['fields'] = ' sum(used_energy_today) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }

    /**
     * @param $company_id
     * @param $project_id
     * @param string $date
     * @return int|mixed
     */
    public function getCustomerTotalTotalGenerateEnergy($company_id,$project_id,$date=''){
        $date = $date ? $date : date("Y-m-d") ;
        $params['cond'] = 'company_id=:company_id AND date <=:date AND is_deleted=:is_deleted' ;
        $params['args'] = [':company_id'=>$company_id,':date'=>$date,':is_deleted'=>'N'];
        if($project_id){
            $params['cond'] = 'project_id=:project_id AND company_id=:company_id AND date <=:date AND is_deleted=:is_deleted' ;
            $params['args'] = [':project_id'=>$project_id,':company_id'=>$company_id,':date'=>$date,':is_deleted'=>'N'];
        }
        $params['fields'] = ' sum(generat_energy_today) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }


    /**
     * 获取总的充电时长
     * @param $device_id
     * @return mixed
     */
    public function getTotalBatChargeTime($device_id){
        $params['cond'] = 'device_id=:device_id AND is_deleted=:is_deleted' ;
        $params['args'] = [':device_id'=>$device_id,':is_deleted'=>'N'];
        $params['fields'] = ' sum(bat_charge_time) as total_bat_charge_time';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_bat_charge_time']) ? $info['total_bat_charge_time'] : 0 ;
    }
}
