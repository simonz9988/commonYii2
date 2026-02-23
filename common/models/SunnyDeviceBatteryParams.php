<?php

namespace common\models;

use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sea_sunny_device_battery_params".
 *
 * @property int $id
 * @property int $device_id 设备ID
 * @property string $bat_over_volt 超压电压
 * @property string $bat_charge_limit_volt 充电限制电压
 * @property string $bat_const_charge_volt 均衡充电电压
 * @property string $bat_improve_charge_volt 提升充电电压
 * @property string $bat_float_charge_volt 浮充充电电压
 * @property string $bat_improve_charge_back_volt 充电返回电压
 * @property string $bat_over_discharge_back_volt 过放返回电压
 * @property string $bat_under_volt 欠压警告电压
 * @property string $bat_over_discharge_volt 过放电压
 * @property string $charge_max_temper 充电上限温度
 * @property string $charge_min_temper 充电下限温度
 * @property string $discharge_max_temper 放电下限温度
 * @property string $discharge_min_temper 放电下限温度
 * @property string $light_control_volt 光控电压
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceBatteryParams extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_battery_params';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['bat_over_volt', 'bat_charge_limit_volt', 'bat_const_charge_volt', 'bat_improve_charge_volt', 'bat_float_charge_volt', 'bat_improve_charge_back_volt', 'bat_over_discharge_back_volt', 'bat_under_volt', 'bat_over_discharge_volt', 'charge_max_temper', 'charge_min_temper', 'discharge_max_temper', 'discharge_min_temper', 'light_control_volt'], 'string', 'max' => 255],
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
            'bat_over_volt' => 'Bat Over Volt',
            'bat_charge_limit_volt' => 'Bat Charge Limit Volt',
            'bat_const_charge_volt' => 'Bat Const Charge Volt',
            'bat_improve_charge_volt' => 'Bat Improve Charge Volt',
            'bat_float_charge_volt' => 'Bat Float Charge Volt',
            'bat_improve_charge_back_volt' => 'Bat Improve Charge Back Volt',
            'bat_over_discharge_back_volt' => 'Bat Over Discharge Back Volt',
            'bat_under_volt' => 'Bat Under Volt',
            'bat_over_discharge_volt' => 'Bat Over Discharge Volt',
            'charge_max_temper' => 'Charge Max Temper',
            'charge_min_temper' => 'Charge Min Temper',
            'discharge_max_temper' => 'Discharge Max Temper',
            'discharge_min_temper' => 'Discharge Min Temper',
            'light_control_volt' => 'Light Control Volt',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @param $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){

        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据ID返回指定信息
     * @param $device_id
     * @param $fields
     * @return mixed
     */
    public function getInfoByDeviceId($device_id,$fields='*'){

        // 查询设备基本信息
        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($device_id);

        // 查询电池参数信息
        $params['cond'] = 'device_id=:device_id AND is_deleted=:is_deleted';
        $params['args'] = [':device_id'=>$device_id,':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            $info['battery_type'] = $device_info['battery_type'];
        }
        return $info ;
    }


    /**
     *
     * @param $ids
     * @param $post_data
     * @return mixed
     */
    public function savePostData($ids,$post_data){

        $is_save_template = $post_data['is_save_template'] ;
        $template_id = $post_data['template_id'] ;
        $save_template_name = $post_data['save_template_name'] ;
        if($is_save_template){
            unset($post_data['is_save_template']) ;
            unset($post_data['template_id']) ;
            unset($post_data['save_template_name']) ;

            $template_obj = new SunnyDeviceTemplate();
            if(!$template_id){
                // 新增模板
                $template_add_data['name'] =$save_template_name;
                $template_add_data['type'] ='BATTERY';
                $template_add_data['content'] =json_encode($post_data);
                $template_add_data['create_time'] = date('Y-m-d H:i:s');
                $template_add_data['modify_time'] = date('Y-m-d H:i:s');
                $template_id = $this->baseInsert($template_obj::tableName(),$template_add_data);
            }else{
                // 更新模板
                $template_update_data['content'] = json_encode($post_data);
                $template_update_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate($template_obj::tableName(),$template_update_data,'id=:id',[":id"=>$template_id]);
            }

            return true ;
        }

        // 批量更新
        $temp_ids = explode('_',$ids);
        $ids_arr = [];
        foreach($temp_ids as $v){
            if($v){
                $ids_arr[] = $v ;
            }
        }

        $device_obj = new  SunnyDevice();
        $task_obj = new SunnyDeviceSyncTask();
        if($ids_arr){
            foreach($ids_arr as $v){

                if(!$v){
                    continue ;
                }
                $task_obj->addBatteryTaskByDeviceID($v);

                // 更新蓄电池类型
                $device_update_data['battery_rate_volt'] = isset($post_data['battery_rate_volt'])?$post_data['battery_rate_volt']:12;
                $device_update_data['battery_type'] = $post_data['battery_type'];
                $device_update_data['modify_time'] = date('Y-m-d H:i:s');

                $this->baseUpdate($device_obj::tableName(),$device_update_data,'id=:id',[':id'=>$v]);

                $delete_data['is_deleted'] = 'Y';
                $delete_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate(self::tableName(),$delete_data,'device_id=:device_id',[':device_id'=>$v]);

                $add_data['device_id'] = $v;
                $add_data['template_id'] = $template_id;
                $add_data['bat_over_volt'] = $post_data['bat_over_volt'];
                $add_data['li_type'] = $post_data['li_type'];
                $add_data['bat_charge_limit_volt'] = $post_data['bat_charge_limit_volt'];
                $add_data['bat_const_charge_volt'] = $post_data['bat_const_charge_volt'];
                $add_data['bat_improve_charge_volt'] = $post_data['bat_improve_charge_volt'];
                $add_data['bat_float_charge_volt'] = $post_data['bat_float_charge_volt'];
                $add_data['bat_improve_charge_back_volt'] = $post_data['bat_improve_charge_back_volt'];
                $add_data['bat_over_discharge_back_volt'] = $post_data['bat_over_discharge_back_volt'];
                $add_data['bat_under_volt'] = $post_data['bat_under_volt'];
                $add_data['bat_over_discharge_volt'] = $post_data['bat_over_discharge_volt'];
                $add_data['charge_max_temper'] = $post_data['charge_max_temper'];
                $add_data['charge_min_temper'] = $post_data['charge_min_temper'];
                $add_data['discharge_max_temper'] = $post_data['discharge_max_temper'];
                $add_data['discharge_min_temper'] = $post_data['discharge_min_temper'];
                $add_data['light_control_volt'] = $post_data['light_control_volt'];
                $add_data['is_deleted'] = 'N';
                $add_data['create_time'] = date('Y-m-d H:i:s');
                $add_data['modify_time'] = date('Y-m-d H:i:s');

                $add_data['bat_discharge_limit_volt'] = $post_data['bat_discharge_limit_volt'];
                $add_data['bat_stop_soc'] = $post_data['bat_stop_soc'];
                $add_data['bat_over_discharge_delay'] = $post_data['bat_over_discharge_delay'];
                $add_data['bat_const_charge_time'] = $post_data['bat_const_charge_time'];
                $add_data['bat_improve_charge_time'] = $post_data['bat_improve_charge_time'];
                $add_data['bat_const_charge_gap_time'] = $post_data['bat_const_charge_gap_time'];
                $add_data['coeff_temper_compen'] = $post_data['coeff_temper_compen'];
                $add_data['heat_bat_start_temper'] = $post_data['heat_bat_start_temper'];
                $add_data['heat_bat_stop_temper'] = $post_data['heat_bat_stop_temper'];
                $add_data['bat_switch_dc_volt'] = $post_data['bat_switch_dc_volt'];
                $add_data['stop_charge_current_set'] = $post_data['stop_charge_current_set'];
                $add_data['dc_load_mode'] = $post_data['dc_load_mode'];
                $add_data['light_control_delay_time'] = $post_data['light_control_delay_time'];

                $this->baseInsert(self::tableName(),$add_data);
            }
        }


        return true ;
    }

    /**
     * @param $device_info
     * @param $post_data
     * @return mixed
     */
    public function saveByProject($device_info,$post_data){

        $battery_type = $post_data['battery_type'];
        $device_update_data['battery_type'] = $battery_type;
        $device_update_data['modify_time'] = date('Y-m-d H:i:s');
        $device_obj = new SunnyDevice();
        $this->baseUpdate($device_obj::tableName(),$device_update_data,'id=:id',[':id'=>$device_info['id']]);

        //$add_data['']
        $info = $this->getInfoByDeviceId($device_info['id']);
        if(!$info){

        }else{
            //$add_data[]
        }
    }

    public function updateDataByUpload($device_no,$e003,$e00f,$e01f){

        // 获取设备信息
        $device_obj= new SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($device_no);
        if(!$device_info){
            return true ;
        }

        // 设备ID
        $device_id = $device_info['id'];
        // $eoo3
        //000c EOO3
        //0000 EOO4
        //009b E005
        //008c EOO6
        //0092 EOO7
        //0090 EOO8
        //008a EOO9
        //0084 EOOA
        //007d EOOB
        //0078 EOOC
        //0072 EOOD
        $device_update_data = [];
        $update_data = [] ;
        if($e003){
            // 系统电压设置
            $device_update_data['battery_rate_volt'] = $this->getValueByRegister($e003,2,0);
            //蓄电池类型
            $device_update_data['battery_type'] = $this->getValueByRegister($e003,6,4);
            //超压电压
            $update_data['bat_over_volt'] = $this->getValueByRegister($e003,10,8);
            $update_data['bat_over_volt'] = $update_data['bat_over_volt']*0.1 ;
            //充电限制电压
            $update_data['bat_charge_limit_volt'] = $this->getValueByRegister($e003,14,12);
            $update_data['bat_charge_limit_volt'] = $update_data['bat_charge_limit_volt']*0.1 ;
            //均衡充电电压
            $update_data['bat_const_charge_volt'] = $this->getValueByRegister($e003,18,16);
            $update_data['bat_const_charge_volt'] = $update_data['bat_const_charge_volt']*0.1 ;
            //提升充电电压/ 锂电池过充电压
            $update_data['bat_improve_charge_volt'] = $this->getValueByRegister($e003,22,20);
            $update_data['bat_improve_charge_volt'] = $update_data['bat_improve_charge_volt']*0.1 ;
            //浮充充电电压/ 锂电过充返回电压
            $update_data['bat_float_charge_volt'] = $this->getValueByRegister($e003,26,24);
            $update_data['bat_float_charge_volt'] = $update_data['bat_float_charge_volt']*0.1 ;
            //提升充电返回电压
            $update_data['bat_improve_charge_back_volt'] = $this->getValueByRegister($e003,30,28);
            $update_data['bat_improve_charge_back_volt'] = $update_data['bat_improve_charge_back_volt']*0.1 ;
            // 过放返回电压
            $update_data['bat_over_discharge_back_volt'] = $this->getValueByRegister($e003,34,32);
            $update_data['bat_over_discharge_back_volt'] = $update_data['bat_over_discharge_back_volt']*0.1 ;
            //欠压警告电压
            $update_data['bat_under_volt'] = $this->getValueByRegister($e003,38,36);
            $update_data['bat_under_volt'] = $update_data['bat_under_volt']*0.1 ;
            //过放电压
            $update_data['bat_over_discharge_volt'] = $this->getValueByRegister($e003,42,40);
            $update_data['bat_over_discharge_volt'] = $update_data['bat_over_discharge_volt']*0.1 ;
        }

        if($device_update_data){
            $device_update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate($device_obj::tableName(),$device_update_data,'id=:id',[':id'=>$device_id]);
        }

        // $e00f  0000 003A 0078 0078 001E 0005 0041 00A3 0041 00A3 0000 0000 0073 0000 A165
        //  0000 003a 0078 0078 0078 0005 0041 00a3 0041 00a3 0080 0080 0073 0000 a165
        //0000 EOOF 0000 bat_stop_soc
        //003A EO10 003a bat_over_discharge_delay
        //0078 E011 0078 bat_const_charge_time
        //0078 E012 0078 bat_improve_charge_time
        //001E E013 0078 bat_const_charge_gap_time --
        //0005 E014 0005 coeff_temper_compen
        //0041 E015 0041 charge_max_temper
        //00A3 E016 00a3 charge_min_temper
        //0041 E017 0041 discharge_max_temper
        //00A3 E018 00a3 discharge_min_temper
        //0000 E019 0080 heat_bat_start_temper  --
        //0000 EO1A 0080 heat_bat_stop_temper  --
        //0073 E01B 0073 bat_switch_dc_volt
        //0000 E01C 0000 stop_charge_current_set
        //A165 E01D a165 dc_load_mode

        if($e00f){
            // 充电截止 SOC,放电截 止 SOC
            $update_data['bat_stop_soc'] = $this->getValueByRegister($e00f,2,0);
            //过放延时时间
            $update_data['bat_over_discharge_delay'] = $this->getValueByRegister($e00f,6,4);
            //均衡充电时间
            $update_data['bat_const_charge_time'] = $this->getValueByRegister($e00f,10,8);
            //提升充电时间
            $update_data['bat_improve_charge_time'] = $this->getValueByRegister($e00f,14,12);
            //均衡充电间隔 --
            $update_data['bat_const_charge_gap_time'] = $this->getValueByRegister($e00f,18,16);
            //温度补偿系数
            $update_data['coeff_temper_compen'] = $this->getValueByRegister($e00f,22,20,2);

            //电池充电上限温度
            $update_data['charge_max_temper'] = $this->getValueByRegister($e00f,26,24,2,true);

            //电池充电下限温度
            $update_data['charge_min_temper'] = $this->getValueByRegister($e00f,30,28,2,true);
            //电池放电上限温度
            $update_data['discharge_max_temper'] = $this->getValueByRegister($e00f,34,32,2,true);
            //电池放电下限温度
            $update_data['discharge_min_temper'] = $this->getValueByRegister($e00f,38,36,2,true);
            //(锂电)加热启动电池温 度  --
            $update_data['heat_bat_start_temper'] = $this->getValueByRegister($e00f,42,40,2,true);
            //(锂电)加热停止电池温 度  --
            $update_data['heat_bat_stop_temper'] = $this->getValueByRegister($e00f,46,44,2,true);
            // 市电切换电压
            $update_data['bat_switch_dc_volt'] = $this->getValueByRegister($e00f,50,48);
            $update_data['bat_switch_dc_volt'] = $update_data['bat_switch_dc_volt']*0.1 ;

            // (锂电) 停止充电电流
            $update_data['stop_charge_current_set'] = $this->getValueByRegister($e00f,54,52);
            $update_data['stop_charge_current_set'] = $update_data['stop_charge_current_set']*0.01 ;

            // 直流负载工作模式
            $update_data['dc_load_mode'] = $this->getValueByRegister($e00f,58,56);
        }

        // $e01f
        // 0005
        if($e01f){
            // 光控电压
            $update_data['light_control_volt'] = $this->getValueByRegister($e01f,2,0);
        }

        //var_dump($update_data);exit;
        if($update_data){

            //将任务完成
            $push_task_obj = new PushTask();
            $push_task_obj->deleteTask('BATTERY_PARAMS',$device_id);

            $update_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseUpdate(self::tableName(),$update_data,'device_id=:device_id AND is_deleted=:is_deleted',[':device_id'=>$device_id,':is_deleted'=>'N']);
        }
    }

    /**
     * 根据寄存器的位置返回指定的值 实际上高八位和低八位没有调换
     * @param $string
     * @param $end_start
     * @param $high_start
     * @param $length
     * @param $is_temperature
     * @param $is_tag
     * @return float|int
     */
    private function getValueByRegister($string,$end_start,$high_start,$length=2,$is_temperature=false,$is_tag=false){
        //重新拼接
        $high = substr($string,$high_start,$length);
        $low = substr($string,$end_start,$length);
        $code = $high.$low ;
        if($is_tag){
            var_dump($code);
        }

        // 转换为10进制
        $res = hexdec($code);
        if($is_temperature){
            $res = $this->getTemperature($this->dealToDec($code));
        }
        return $res ;
    }

    /**
     * 将16进制转换成2进制 同时补齐位数
     * @param $string
     * @param int $num
     */
    private function dealToDec($string,$num=8){

        $binNumeric = hexdec($string);

        $binString = decbin($binNumeric); // = "11111"
        return  str_pad($binString, $num, "0", STR_PAD_LEFT);
    }

    public function getTemperature($code){
        //高八位
        //b7:符号位(0表示+，1表示 - )
        //b0-b6:温度值
        // 首字符
        $first = substr($code,0,1);
        $value = substr($code,1,7);
        $res = $first == 1?"-":"";
        if($value == 0){
            $res = 0 ;
        }else{
            $res = $res.bindec($value);
        }

        return $res ;
    }

    /**
     * 更新P07区
     * @param $device_no
     * @param $e08d
     * @return bool
     */
    public function updateP07DataByUpload($device_no,$e08d){

        // 获取设备信息
        $device_obj= new SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($device_no);
        if(!$device_info){
            return true ;
        }

        // 设备ID
        $device_id = $device_info['id'];
        // $e08d
        //000c E08D 负载电流设置
        //0000 E08E 智能功率 6 4
        //009b E08F 光控延时时间（S） 10 8
        //008c EO90 感应延时  14 12
        //0092 EO91 感应距离  18 16
        //0090 EO92 第1段亮灯时间 22 20
        //008a E093 第1段有人功率 26 24
        //0084 E094 第1段无人功率 30 28
        //007d EOOB
        //0078 EOOC
        //0072 EOOD
        $device_update_data = [];

        if($e08d){
            //智能功率
            $device_update_data['led_current_set'] = $this->getValueByRegister($e08d,2,0);
            $device_update_data['led_current_set'] = $device_update_data['led_current_set']*0.01 ;
            $device_update_data['auto_power_set'] = $this->getValueByRegister($e08d,6,4);

            $num1 = 22 ;
            $num2 = 20 ;
            $num3 = 26 ;
            $num4 = 24 ;
            $num5 = 30 ;
            $num6 = 28 ;

            $update_data['project_id']=$device_info['project_id'];
            $update_data['device_id']=$device_info['id'];
            $update_data['company_id']=$device_info['company_id'];
            $update_data['category_id']=$device_info['category_id'];
            $update_data['parent_id']=$device_info['parent_id'];
            $update_data['device_no']=$device_info['device_no'];
            $update_data['modify_time']=date('Y-m-d H:i:s');

            $load_obj = new SunnyDeviceLoadTime();
            for($i = 0;$i<10 ; $i++){

                $f_num1 = $num1 + 12*$i ;
                $f_num2 = $num2 + 12*$i ;
                $f_num3 = $num3 + 12*$i ;
                $f_num4 = $num4 + 12*$i ;
                $f_num5 = $num5 + 12*$i ;
                $f_num6 = $num6 + 12*$i ;
                $minute = $this->getValueByRegister($e08d,$f_num1,$f_num2);
                $load_sensor_on_power = $this->getValueByRegister($e08d,$f_num3,$f_num4);
                $load_sensor_off_power = $this->getValueByRegister($e08d,$f_num5,$f_num6);

                $update_data['time_end']=$i+1;
                $update_data['minutes']= ceil($minute/60);
                $update_data['load_sensor_on_power']= $load_sensor_on_power;
                $update_data['load_sensor_off_power']= $load_sensor_off_power;

                // 判断当前时段的是否存在
                $info = $load_obj->getInfoByDeviceIdAndTimeEnd($device_info['id'],$i+1);
                if($info){
                    $load_obj->baseUpdate($load_obj::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
                }else{
                    $insert_data = $update_data ;
                    $insert_data['create_time'] = date('Y-m-d H:i:s') ;
                    $load_obj->baseInsert($load_obj::tableName(),$insert_data);
                }
            }

        }

        if($device_update_data){

            //将任务完成
            $push_task_obj = new PushTask();
            $push_task_obj->deleteTask('LOAD_PARAMS',$device_id);

            $device_update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate($device_obj::tableName(),$device_update_data,'id=:id',[':id'=>$device_id]);
        }

        // 取最后几个字符串写缓存
        $cache_string =  substr($e08d,140);
        $redis_key = "BatteryBindString:".$device_info['id'];
        $redis_obj = new MyRedis();
        $redis_obj->set($redis_key,$cache_string,864000);

        $cache_string =  substr($e08d,8,12);
        $redis_key = "BatteryBindString1:".$device_info['id'];
        $redis_obj->set($redis_key,$cache_string,864000);
    }
}
