<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_status_total".
 *
 * @property int $id
 * @property int $device_id 设备ID
 * @property int $company_id 公司ID
 * @property int $category_id 分类ID
 * @property int $parent_id 父级ID
 * @property string $device_no 设备编号
 * @property string $work_days_total 总运行天数
 * @property string $bat_over_discharge_time 蓄电池总过放次数
 * @property string $bat_over_charge_time 蓄电池总充满次数
 * @property string $bat_charge_an_total 蓄电池总充电安时数
 * @property string $bat_discharge_an_total 蓄电池总放电安时数
 * @property string $generat_energy_total 累计发电量
 * @property string $used_energy_total 累计用电量
 * @property string $load_status 负载状态
 * @property string $brightness 亮度
 * @property string $charge_status 充电状态
 * @property string $fault_info 错误信息
 * @property string $load_total_work_time 负载总工作（累计亮 灯）时间
 * @property string $led_light_on_index 亮灯指数
 * @property string $power_save_index 能耗指数
 * @property string $sys_health_index 健康指数
 * @property string $night_length 夜晚长度
 * @property string $is_deleted
 * @property string $create_time 创建时间
 * @property string $modify_time
 */
class SunnyDeviceStatusTotal extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_status_total';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id', 'company_id', 'category_id', 'parent_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['device_no', 'work_days_total', 'bat_over_discharge_time', 'bat_over_charge_time', 'bat_charge_an_total', 'bat_discharge_an_total', 'generat_energy_total', 'used_energy_total', 'load_status', 'brightness', 'charge_status'], 'string', 'max' => 50],
            [['fault_info', 'load_total_work_time', 'led_light_on_index', 'power_save_index', 'sys_health_index', 'night_length'], 'string', 'max' => 255],
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
            'work_days_total' => 'Work Days Total',
            'bat_over_discharge_time' => 'Bat Over Discharge Time',
            'bat_over_charge_time' => 'Bat Over Charge Time',
            'bat_charge_an_total' => 'Bat Charge An Total',
            'bat_discharge_an_total' => 'Bat Discharge An Total',
            'generat_energy_total' => 'Generat Energy Total',
            'used_energy_total' => 'Used Energy Total',
            'load_status' => 'Load Status',
            'brightness' => 'Brightness',
            'charge_status' => 'Charge Status',
            'fault_info' => 'Fault Info',
            'load_total_work_time' => 'Load Total Work Time',
            'led_light_on_index' => 'Led Light On Index',
            'power_save_index' => 'Power Save Index',
            'sys_health_index' => 'Sys Health Index',
            'night_length' => 'Night Length',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    public function checkExistsByDeviceId($id){
        $params['cond'] = "device_id=:device_id AND is_deleted=:is_deleted";
        $params['args'] = [':device_id'=>$id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 添加设备快照信息
     * @param $device_info
     * @param $post_data
     * @return mixed
     */
    public function addRecord($device_info,$post_data){

        $info = $this->checkExistsByDeviceId($device_info['id']) ;

        $add_data['work_days_total'] = $post_data['work_days_total'];
        $add_data['bat_over_discharge_time'] = $post_data['bat_over_discharge_time'];
        $add_data['bat_over_charge_time'] = $post_data['bat_over_charge_time'];
        $add_data['bat_charge_an_total'] = $post_data['bat_charge_an_total'];
        $add_data['bat_discharge_an_total'] = $post_data['bat_discharge_an_total'];
        $add_data['generat_energy_total'] = $post_data['generat_energy_total'];
        $add_data['used_energy_total'] = $post_data['used_energy_total'];
        $add_data['load_status'] = $post_data['load_status'];
        $add_data['brightness'] = $post_data['brightness'];
        $add_data['charge_status'] = $post_data['charge_status'];
        $add_data['fault_info'] = $post_data['fault_info'];
        $add_data['load_total_work_time'] = $post_data['load_total_work_time'];
        $add_data['led_light_on_index'] = $post_data['led_light_on_index'];
        $add_data['power_save_index'] = $post_data['power_save_index'];
        $add_data['sys_health_index'] = $post_data['sys_health_index'];
        $add_data['night_length'] = $post_data['night_length'];
        $add_data['modify_time'] = date('Y-m-d H:i:s');

        if($info) {

            foreach($add_data as $k=>$v){

                // 新传输的值和系统存在的值做比较
                if(!in_array($k,['modify_time','load_status','brightness','charge_status','fault_info','sys_health_index'])){
                    if($add_data[$k] < $info[$k]){
                        unset($add_data[$k]);
                    }
                }

            }
            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$info['id']]) ;
        }else{

            $add_data['device_id'] = $device_info['id'];
            $add_data['project_id'] = $device_info['project_id'];
            $add_data['customer_id'] = $device_info['customer_id'];
            $add_data['company_id'] = $device_info['company_id'];
            $add_data['category_id'] = $device_info['category_id'];
            $add_data['parent_id'] = $device_info['parent_id'];
            $add_data['device_no'] = $device_info['device_no'];

            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            $this->baseInsert(self::tableName(),$add_data);
        }

        return true ;

    }

    /**
     * 获取客户总用电量
     * @param $company_id
     * @param $project_id
     * @return mixed
     */
    public function getCustomerTotalUsedEnergy($company_id,$project_id){
        $params['cond'] = 'company_id=:company_id  AND is_deleted=:is_deleted' ;
        $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N'];
        if($project_id){
            $params['cond'] = 'company_id=:company_id  AND is_deleted=:is_deleted AND project_id=:project_id' ;
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':project_id'=>$project_id];
        }
        $params['fields'] = ' sum(used_energy_total) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }


    /**
     * 获取用户总发电量
     * @param $company_id
     * @param $project_id
     * @return int
     */
    public function getCustomerTotalGenerateEnergy($company_id,$project_id){
        $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted ' ;
        $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N'];
        if($project_id){
            $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted AND project_id=:project_id ' ;
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':project_id'=>$project_id];
        }
        $params['fields'] = ' sum(generat_energy_total) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }

    /**
     * 获取指定公司总电量
     * @param $company_ids
     * @return int
     */
    public function getTotalGenerateEnergyByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted' ;
        $params['args'] = [':is_deleted'=>'N'];
        $params['fields'] = ' sum(generat_energy_total) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }

    /**
     * 获取指定公司总用电量
     * @param $company_ids
     * @return int
     */
    public function getTotalUsedEnergyByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted' ;
        $params['args'] = [':is_deleted'=>'N'];
        $params['fields'] = ' sum(used_energy_total) as total_used_energy_today';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_used_energy_today']) ? $info['total_used_energy_today'] : 0 ;
    }

}
