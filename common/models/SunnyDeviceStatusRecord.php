<?php

namespace common\models;

use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sea_sunny_device_status_record".
 *
 * @property int $id
 * @property int $device_id 设备ID
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
class SunnyDeviceStatusRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_status_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id'], 'integer'],
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
     * 根据设备ID返回指定的信息
     * @param $id
     * @return mixed
     */
    public function getLastedInfoByDeviceId($id){

        $params['cond'] = 'device_id=:device_id';
        $params['args'] = [':device_id'=>$id];
        $params['orderby'] = 'create_time desc';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 同步记录信息
     * @param $deviceNo
     * @param $post_data
     * @return mixed
     */
    public function syncRecord($deviceNo,$post_data){

        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($deviceNo);
        if(!$device_info){
            $this->setError('100075');
            return false ;
        }

        // 落盘快照信息 需要在更新记录之前
        $total_obj = new SunnyDeviceTotal();
        $total_obj->addRecordByPostData($device_info,$post_data);

        //原则上五分钟同步一次
        #TODO
        $add_data['device_id'] = $device_info['id'];
        $add_data['project_id'] = $device_info['project_id'];
        $add_data['company_id'] = $device_info['company_id'];
        $add_data['category_id'] = $device_info['category_id'];
        $add_data['parent_id'] = $device_info['parent_id'];
        $add_data['device_no'] = $deviceNo;
        $add_data['battery_volume'] = $post_data['batteryVolume'];
        $add_data['battery_voltage'] = $post_data['batteryVoltage'];
        $add_data['battery_charging_current'] = $post_data['batteryChargingCurrent'];
        $add_data['ambient_temperature'] = $post_data['ambientTemperature'];
        $add_data['battery_panel_charging_voltage'] = $post_data['batteryPanelChargingVoltage'];
        $add_data['charging_current'] = $post_data['chargingCurrent'];
        $add_data['charging_power'] = $post_data['chargingPower'];
        $add_data['cumulative_charge'] = $post_data['cumulativeCharge'];
        $add_data['load_status'] = $post_data['loadStatus'];
        $add_data['brightness'] = $post_data['brightness'];
        $add_data['switch_status'] = $post_data['switchStatus'];
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $this->baseInsert(self::tableName(),$add_data);

        return true ;
    }

    /**
     * 通过寄存器同步记录
     * @param $deviceNo
     * @param $data
     * @return mixed
     */
    public function syncRecordFromRegister($deviceNo,$data){

        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($deviceNo);
        if(!$device_info){
            $this->setError('100075');
            return false ;
        }

        // 没有绑定不能进行行为上报
        if($device_info['is_bind'] != 'Y'){
            $this->setError('100075');
            return false ;
        }

        $post_data = $this->transPostDataFromData($data);

        if(!$post_data){
            return false ;
        }

        // 落盘快照信息 需要在更新记录之前
        $total_obj = new SunnyDeviceTotal();
        $total_obj->addRecordByPostData($device_info,$post_data);

        $is_fault = $post_data['fault_info'] && !is_null($post_data['fault_info']) ? 'Y':"N";
        //原则上五分钟同步一次
        $add_data = $post_data ;
        $add_data['project_id'] = $device_info['project_id'];
        $add_data['device_id'] = $device_info['id'];
        $add_data['company_id'] = $device_info['company_id'];
        $add_data['category_id'] = $device_info['category_id'];
        $add_data['parent_id'] = $device_info['parent_id'];
        $add_data['device_no'] = $deviceNo;
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['is_fault'] =$is_fault;
        $this->baseInsert(self::tableName(),$add_data);

        $status_info_obj = new SunnyDeviceStatusInfo();
        $status_info_obj->syncByAddData($add_data);

        // 同时更新设备故障信息
        $device_update_data['is_fault'] = $is_fault ;
        $device_update_data['modify_time'] = date('Y-m-d H:i:s');
        $this->baseUpdate($device_obj::tableName(),$device_update_data,'id=:id',[':id'=>$device_info['id']]);

        // 处理错误信息
        $fault_obj = new SunnyDeviceFault();
        $fault_info = $post_data['fault_info'] ? explode(',',$post_data['fault_info']) :[];
        $fault_obj->dealFault($fault_info,$device_info);
        return $post_data ;
    }

    /**
     * 通过传递的字符串转换成需要同步的记录
     * @param $data
     * @return mixed
     */
    public function transPostDataFromData($data){

        $data = strtoupper($data);
        // 取四位
        // FD 0-3  负载状态和 充电状态
        $load_status = $this->getLoadStatus($data);
        $res['load_status'] = $load_status ;
        $light_percent = $this->getLightPercent($data);
        $res['brightness'] = $light_percent ;
        // 充电状态
        $res['charge_status'] = $this->getChargeStatus($data);

        // 控制器故障 告警信息   占8位

        $fault_list = $this->getFaultList(substr($data,4,8)) ;
        $res['fault_info'] = $fault_list?implode(',',$fault_list):'' ;

        // 蓄电池电量
        $res['battery_volume'] = $this->getValueByRegister($data,14,12);
        $res['battery_volume'] = $res['battery_volume']*0.1 ;
        // 蓄电池电压 battery_voltage
        $res['battery_voltage'] = $this->getValueByRegister($data,18,16);
        $res['battery_voltage'] = $res['battery_voltage'] * 0.1 ;
        // 充电电流
        $res['battery_charging_current'] = $this->getValueByRegister($data,22,20);
        $res['battery_charging_current'] = $res['battery_charging_current'] *0.01 ;
        // 控制器温度
        $res['ambient_temperature'] = $this->getControllerTemperature($data);//26
        //蓄电池温度
        $res['battery_temperature'] = $this->getBatteryTemperature($data);//24

        // 直流负载电压
        $res['load_dc_power'] = $this->getValueByRegister($data,30,28);
        $res['load_dc_power'] = $res['load_dc_power']*0.1;
        // 直流负载电流
        $res['charging_current'] = $this->getValueByRegister($data,34,32);
        $res['charging_current'] = $res['charging_current']*0.01 ;
        //  直流负载功率
        $res['cumulative_charge'] = $this->getValueByRegister($data,38,36);
        //太阳能板电压
        $res['battery_panel_charging_voltage'] =  $this->getValueByRegister($data,42,40);
        $res['battery_panel_charging_voltage'] = $res['battery_panel_charging_voltage'] *0.1 ;
        //太阳能板电流
        $res['battery_panel_charging_current'] = $this->getValueByRegister($data,46,44);
        $res['battery_panel_charging_current'] = $res['battery_panel_charging_current'] *0.01 ;
        //太阳能板充电功率
        $res['charging_power'] = $this->getValueByRegister($data,50,48);
        // 开关状态 ---暂定
        $res['switch_status'] = $load_status ;
        return $res ;

    }

    /**
     * 获取负载状态 取低八位
     * @param $string
     * @param $offset
     * @return mixed
     * Note：之前的高八位和低八位是调换的
     */
    public function getLoadStatus($string,$offset=0){
        // 3-4
        $info = substr($string,$offset,2);
        $info  = $this->dealToDec($info);

        // 将16进制转换为2进制
        // 取最高位是
        $info = substr($info,0,1);
        return $info == 1 ? "Y":"N";
    }

    // 获取亮度等级
    public function getLightPercent($string,$offset=0){
        // 3-4
        $info = substr($string,$offset,2);
        // 将16进制转换为2进制
        $info  = $this->dealToDec($info);
        // 取后七位
        $info = substr($info,1,7);
        // 将二进制转换为10进制
        return bindec($info);
    }

    // 获取充电状态
    public function getChargeStatus($string,$offset=2){
        // 1-2
        $code = substr($string,$offset,2);
        // 将16进制转换为10进制
        $code  = hexdec($code);
        return $code ;
        //$res = ["0"=>'未开启','1'=>''];
    }



    public function getBatteryTemperature($string){
        //高八位
        //b7:符号位(0表示+，1表示 - )
        //b0-b6:温度值
        $low = substr($string,24,2);
        // 16转换为2进制
        $code = $this->dealToDec($low);

        // 首字符
        $first = substr($code,0,1);
        $value = substr($code,1,7);
        $res = $first == 1?"-":"+";
        $res = $res.bindec($value);
        return $res ;
    }

    // 获取控制器温度
    public function getControllerTemperature($string){
        //高八位
        //b7:符号位(0表示+，1表示 - )
        //b0-b6:温度值
        $high = substr($string,26,2);

        // 16转换为2进制
        $code = $this->dealToDec($high);

        // 首字符
        $first = substr($code,0,1);
        $value = substr($code,1,7);
        $res = $first == 1?"-":"+";
        $res = $res.bindec($value);
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

    /**
     * 根据寄存器的位置返回指定的值 实际上高八位和低八位没有调换
     * @param $string
     * @param $end_start
     * @param $high_start
     * @param $length
     * @param $is_tag
     * @return float|int
     */
    private function getValueByRegister($string,$end_start,$high_start,$length=2,$is_tag=false){
        //重新拼接
        $high = substr($string,$high_start,$length);
        $low = substr($string,$end_start,$length);
        $code = $high.$low ;
        if($is_tag){
            var_dump($code);
        }

        // 转换为10进制
        return hexdec($code);
    }

    /**
     * 返回数据
     * @param $deviceNo
     * @param $need_sync
     * @return mixed
     */
    public function getReturnData($deviceNo,$need_sync=true){

        // 查询设备基本信息状态
        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($deviceNo);
        if(!$device_info){
            return [] ;
        }

        $record_info = $this->getLastedInfoByDeviceId($device_info['id']);

        // 开关状态
        $switch_status = $device_info['switch_status'];
        $minute = intval($device_info['minute']) ;

        $seconds = $minute*60;
        $seconds = $this->formatZero($seconds,4);
        $seconds = strtoupper($seconds);

        if($switch_status == "Y"){

            // 再取亮度
            //亮度
            $brightness = intval($device_info['brightness']);
            // 转为4位的16进制
            $switch_string = dechex($brightness);
            $seconds = $seconds!='0000'?$seconds:'D2D0';//默认15小时
            $switch_string = str_pad($switch_string, 4, "0", STR_PAD_LEFT);
            $switch_string = $switch_string.$seconds;// 持续开灯15个小时
        }else{

            $seconds = $seconds!='0000'?$seconds:'0008';//默认15小时
            $switch_string = "0000".$seconds;
        }

        if(!$need_sync){
            $switch_string = "00000000";
        }
        //负载开关 亮度  0xDF0A 0xDF0B
        $returnData['0xDF0A'] = $switch_string;

        // 系统电压0xE003   蓄电池类型E004
        //"0xE0003":"000C000A"   设置蓄电池电压和类型
        //蓄电池类型 10=自定义（铅酸电池） 11=自定义（锂电池）
        $battery_type = $device_info['battery_type'] ? $device_info['battery_type'] :10;
        $battery_type = $battery_type ? str_pad(dechex($battery_type), 4, "0", STR_PAD_LEFT):"000A";

        $battery_rate_volt = $device_info['battery_rate_volt'] ? $device_info['battery_rate_volt'] :12;
        $battery_rate_volt = dechex($battery_rate_volt);
        $battery_rate_volt = str_pad($battery_rate_volt, 4, "0", STR_PAD_LEFT);
        //设置蓄电池电压和类型
        $returnData['0xE003'] = $battery_rate_volt.$battery_type ;

        #TODO START
        $battery_params_obj = new SunnyDeviceBatteryParams();
        $battery_params_info = $battery_params_obj->getInfoByDeviceId($device_info['id']);



        // 超压电压 Bat Over Volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_over_volt']:15.5;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);
        // 充电限制电压  Bat Charge Limit Volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_charge_limit_volt']:15;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);
        //均衡充电电压 Bat Const Charge Volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_const_charge_volt']:14.6;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);
        // 提升充电电压 bat improve  charge volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_improve_charge_volt']:14.4;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);
        //浮充充电电压   bat float  charge volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_float_charge_volt']:13.8;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);
        //充电返回电压 Bat Improve Charge Back Volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_improve_charge_back_volt']:13.2;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);
        //过放返回电压 Bat Over-discharge Back Volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_over_discharge_back_volt']:12.6;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);
        //欠压警告电压 Bat Under Volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_under_volt']:12.0;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);
        //过放电压  Bat Over Discharge Volt
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_over_discharge_volt']:11.0;
        $returnData['0xE003'] .= $this->formatZero($bat_over_volt*10);

        // 充电截止SOC,放电截 止SOC
        //$bat_over_volt = $battery_params_info ? $battery_params_info['bat_stop_soc']:0;
        $bat_over_volt = 0;//固定为0
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt);
        // 过放延时时间
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_over_discharge_delay']:5;
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt);
        // 均衡充电时间
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_const_charge_time']:120;
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt);
        // 提升充电时间
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_improve_charge_time']:120;
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt);
        // 均衡充电间隔
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_const_charge_gap_time']:30;
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt);
        // 温度补偿系数
        $bat_over_volt = $battery_params_info ? $battery_params_info['coeff_temper_compen']:5;
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt);

        //充电上限温度   Charge Max Temper 60  高8位未用 低8位：温度值 b7:符号位(0表示+，1表示 - ) b0-b6:温度值
        $bat_over_volt = $battery_params_info ? $battery_params_info['charge_max_temper']:65;
        $returnData['0xE00F'] .= $this->trans10To16AddZero($bat_over_volt);
        //充电下限温度   Charge Min Temper -30
        $bat_over_volt = $battery_params_info ? $battery_params_info['charge_min_temper']:-30;
        $returnData['0xE00F'] .= $this->trans10To16AddZero($bat_over_volt);
        //放电上限温度   Discharge Max Temper 60
        $bat_over_volt = $battery_params_info ? $battery_params_info['discharge_max_temper']:60;
        $returnData['0xE00F'] .= $this->trans10To16AddZero($bat_over_volt);
        //放电下限温度  Discharge Max Temper 60 -30
        $bat_over_volt = $battery_params_info ? $battery_params_info['discharge_min_temper']:-30;
        $returnData['0xE00F'] .= $this->trans10To16AddZero($bat_over_volt);

        //(锂电)加热启动电池温度
        $bat_over_volt = $battery_params_info ? $battery_params_info['heat_bat_start_temper']:-10;
        $returnData['0xE00F'] .= $this->trans10To16AddZero($bat_over_volt);
        //(锂电)加热停止电池温度
        $bat_over_volt = $battery_params_info ? $battery_params_info['heat_bat_stop_temper']:-5;
        $returnData['0xE00F'] .= $this->trans10To16AddZero($bat_over_volt);
        // 市电切换电压
        $bat_over_volt = $battery_params_info ? $battery_params_info['bat_switch_dc_volt']:11.5;
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt*10);
        // (锂电) 停止充电电流
        $bat_over_volt = $battery_params_info ? $battery_params_info['stop_charge_current_set']:0;
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt*100);
        // 直流负载工作模式
        $bat_over_volt = $battery_params_info ? $battery_params_info['dc_load_mode']:0;
        $returnData['0xE00F'] .= $this->formatZero($bat_over_volt);

        // 光控电压 Light Control Volt 5
        $light_control_volt = $battery_params_info ? $battery_params_info['light_control_volt']:5;
        $returnData['0xE01F'] = $this->formatZero($light_control_volt);
        #TODO END


        // 10进制转成16进制，4位
        //负载电流设置0xE08D Led Current Set 0.15~10  // 默认0.9  单位0.01A
        $led_current_set = $device_info['led_current_set'] ? $device_info['led_current_set'] :0.9;
        $led_current_set = $led_current_set*100;
        $led_current_set = dechex($led_current_set);
        $led_current_set = str_pad($led_current_set, 4, "0", STR_PAD_LEFT);

        // 智能功率 0xE08E Auto Power Set  关闭/低/中/高/自动（控制器自动设置） /user1（用户自己设定 0/1/2/3/4
        $auto_power_set =  $device_info ? $device_info['auto_power_set'] :2;
        $auto_power_set = dechex($auto_power_set);
        $auto_power_set = str_pad($auto_power_set, 4, "0", STR_PAD_LEFT);
        $returnData['0xE08D'] = $led_current_set.$auto_power_set ;


        // 光控延时设置  0xE08F
        $redis_obj = new MyRedis();
        $redis_key = "BatteryBindString1:".$device_info['id'];
        //$returnData['0xE08D'] .= '003C00000000';
        $redis_info = $redis_obj->get($redis_key) ;
        $redis_info = $redis_info ? $redis_info : '003C00000000';// 初次上传查询不到，填充默认值
        $returnData['0xE08D']  .= $redis_info;

        // 亮灯 时段设置0xE092 - E0AC  最多是设置9条记录
        //LoadTime1  最大 54000S 最小 0
        //LoadSensorOnPower1  有人功率   默认值50%  0~100
        //LoadSensorOffPower1 无人功率   默认值20%  0~100
        $load_time_obj = new SunnyDeviceLoadTime();
        $str = $load_time_obj->getFrontString($device_info);

        $returnData['0xE08D'] .= $load_time_obj->getFrontString($device_info);

        //$returnData['0xE08D'] .= '005a005a00010001';
        $redis_key = "BatteryBindString:".$device_info['id'];
        $redis_info = $redis_obj->get($redis_key) ;
        $redis_info = $redis_info ? $redis_info : '007E007400640005';// 初次上传查询不到，填充默认值
        $returnData['0xE08D'] .= $redis_info;
        //
        $bat_over_volt = $battery_params_info ? $battery_params_info['heat_bat_stop_temper']:-5;
        //$returnData['0xE00F'] .= $this->formatZero($bat_over_volt*100);
        //$returnData['0xE00F'] .= $this->formatZero($bat_over_volt*100);
        //$returnData['0xE00F'] .= $this->formatZero($bat_over_volt*100);
        //$returnData['0xE00F'] .= $this->formatZero($bat_over_volt*100);

        $returnData['0xE08D'] .= "0000000000000000";


        return $returnData ;
    }

    /**
     * 获取错误信息列表
     * @param $fault_info
     * @return mixed
     */
    public function getFaultList($fault_info){

        // 将16进制转为2进制
        $binNumeric = hexdec($fault_info);
        $binString = decbin($binNumeric);

        // 转换为数组

        $arr = [] ;
        for($i=0;$i<strlen($binString);$i++){
            $arr[] = substr($binString,$i,1);
        }

        // 反转
        $arr = array_reverse($arr);

        // key 转换
        $err_list = [] ;

        $fault_list_name = $this->getFaultNameList();
        $fault_keys = array_keys($fault_list_name);
        foreach($arr as $k=>$v){
            $code_key = 31-$k ;
            if($v &&  in_array($code_key,$fault_keys)){
                $err_list[] = $code_key;
            }
        }

        return $err_list ;

    }

    /**
     * 获取对应的错误信息
     * @return array
     */
    public function getFaultNameList(){

        $arr[31] = "负载开路(路灯)";
        $arr[30] = "感应探头损坏(路灯)";
        $arr[29] = "电容超压 （保留）";
        //$arr[28] = "蓄电池反接";
        $arr[28] = "负载短路";
        $arr[27] = "电池低温保护（温度低于充电下限）停 止充电";
        $arr[26] = "过充保护，停止充电";
        $arr[25] = "电池低温保护（温度低于放电下限）禁止 放电";
        $arr[24] = "电池高温保护（温度高于放电上限）禁止 放电";
        $arr[23] = "未检测到电池（铅酸）";
        //$arr[22] = "供电状态（0 蓄电池供电，1 市电供电）"; // 暂时保留

        $arr[12] = "太阳能板反接";
        $arr[11] = "太阳能板工作点超压";
        $arr[10] = "太阳能板反流 （保留）";
        $arr[9] = "光伏输入端超压";
        $arr[8] = "光伏输入端短路 （保留）";
        $arr[7] = "光伏输入功率过大";
        $arr[6] = "电池高温保护（温度高于充电上限）禁止 充电";
        $arr[5] = "控制器温度过高";
        $arr[4] = "负载功率过大或负载过流";
        //$arr[3] = "负载短路";
        $arr[3] = "太阳能板正负极反接";
        $arr[2] = "欠压警告";
        $arr[1] = "蓄电池超压";
        $arr[0] = "蓄电池过放";
        return $arr ;
    }


    /**
     * 通过寄存器同步历史记录
     * @param $deviceNo
     * @param $data
     * @return mixed
     */
    public function syncHistoryRecordFromRegister($deviceNo,$data){

        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($deviceNo);
        if(!$device_info){
            $this->setError('100075');
            return false ;
        }

        // 没有绑定不能进行行为上报
        if($device_info['is_bind'] != 'Y'){
            $this->setError('100075');
            return false ;
        }

        $post_data = $this->transHistoryPostDataFromData($data);
        if(!$post_data){
            return false ;
        }

        // 落盘快照信息 需要在更新记录之前
        $this->addHistoryData($device_info,$post_data);

        return $post_data ;
    }

    /**
     * 通过传递的字符串转换成需要同步的历史记录
     * @param $data
     * @return mixed
     */
    public function transHistoryPostDataFromData($data){

        $data = strtoupper($data);

        //控制器历史数据  0x10B - 0x12D
        //  蓄电池当天最低电 压 Bat Min Volt Today
        $res['bat_min_volt_today'] = $this->getValueByRegister($data,2,0);
        $res['bat_min_volt_today'] = $res['bat_min_volt_today']*0.1;
        // 蓄电池当天最高电 压 Bat Max Volt Today
        $res['bat_max_volt_today'] = $this->getValueByRegister($data,6,4);
        $res['bat_max_volt_today'] = $res['bat_max_volt_today']*0.1 ;
        // 当天充电最大电流 Bat Max Chg Current Today
        $res['bat_max_chg_current_today'] = $this->getValueByRegister($data,10,8,2);
        $res['bat_max_chg_current_today'] = $res['bat_max_chg_current_today']*0.01 ;
        //当天放电最大电流 Bat Max Discharge Current Today
        $res['bat_max_discharge_current_today'] = $this->getValueByRegister($data,14,12);
        $res['bat_max_discharge_current_today'] = $res['bat_max_discharge_current_today']*0.01;
        // 当天充电最大功率 Bat Max Charge Power Today
        $res['bat_max_charge_power_today'] = $this->getValueByRegister($data,18,16);
        // 当天放电最大功率  Bat Max Discharge Power Today
        $res['bat_max_discharge_power_today'] = $this->getValueByRegister($data,22,20);
        // 当天充电安时数 Bat Charge AH Today
        $res['bat_charge_ah_today'] = $this->getValueByRegister($data,26,24);
        // 当天放电安时数 Bat Discharge AH Today
        $res['bat_discharge_ah_today'] = $this->getValueByRegister($data,30,28);
        //当天发电量 Generat Energy Today
        $res['generat_energy_today'] = $this->getValueByRegister($data,34,32);
        //当天用电量 Used Energy Today
        $res['used_energy_today'] = $this->getValueByRegister($data,38,36);
        //总运行天数 Work Days Total
        $res['work_days_total'] = $this->getValueByRegister($data,42,40);
        //蓄电池总过放次数 Bat Over-Discharge Times
        $res['bat_over_discharge_time'] = $this->getValueByRegister($data,46,44);
        //蓄电池总充满次数 Bat Over-Charge Times
        $res['bat_over_charge_time'] = $this->getValueByRegister($data,50,48);
        //蓄电池总充电安时数 Bat Charge AH Total // 2个字节
        $res['bat_charge_an_total'] = $this->getValueByRegister($data,56,52,4);
        //蓄电池总放电安时数 Bat Discharge AH Total // 2个字节
        $res['bat_discharge_an_total'] = $this->getValueByRegister($data,64,60,4);
        //累计发电量 Generat Energy Total // 2个字节
        $res['generat_energy_total'] = $this->getValueByRegister($data,72,68,4);
        //累计用电量 Used Energy Total // 2个字节
        $res['used_energy_total'] = $this->getValueByRegister($data,80,76,4);
        // 负载状态和 充电状态 LoadAndChgState1
        $load_status = $this->getLoadStatus($data,84);
        $res['load_status'] = $load_status ;
        $light_percent = $this->getLightPercent($data,84);
        $res['brightness'] = $light_percent ;
        // 充电状态
        $res['charge_status'] = $this->getChargeStatus($data,86);
        // 控制器故障 告警信息   占8位
        $fault_list = $this->getFaultList(substr($data,88,8)) ;
        $res['fault_info'] = $fault_list? implode(',',$fault_list):'' ;
        // 当天电池最高温度 Bat Highest Temper
        $res['bat_highest_temper'] = $this->getValueByRegister($data,98,96);
        // 当天电池最低温度 Bat Lowest Temper
        $res['bat_lowest_temper'] = $this->getValueByRegister($data,102,100);
        // 负载总工作（累计亮 灯）时间 Load Total Work Time // 2个字节
        $res['load_total_work_time'] = $this->getValueByRegister($data,108,104,4);
        // 当天亮灯时间 （有人）Led Sensor On Time
        $res['led_sensor_on_time'] = $this->getValueByRegister($data,114,112);
        // 当天亮灯时间 （无人）当天亮灯时间 （无人） Led Sensor Off Time
        $res['led_sensor_off_time'] = $this->getValueByRegister($data,118,116);
        // 亮灯指数） Led Light On Index
        $res['led_light_on_index'] = $this->getValueByRegister($data,122,120);
        // 能耗指数 Power Save Index
        $res['power_save_index'] = $this->getValueByRegister($data,126,124);
        // 健康指数 Sys Health Index
        $res['sys_health_index'] = $this->getValueByRegister($data,130,128);
        // 当天充电时间 Bat Charge Time
        $res['bat_charge_time'] = $this->getValueByRegister($data,134,132);
        // 夜晚长度 Night Length
        $res['night_length'] = $this->getValueByRegister($data,138,136);

        // 开关状态 ---暂定
        $res['switch_status'] = $load_status ;

        return $res ;

    }

    /**
     * 新增历史记录
     * @param $device_info
     * @param $data
     * @return mixed
     */
    public function addHistoryData($device_info,$data){

        $today_obj = new SunnyDeviceStatusToday();
        $today_obj->addRecord($device_info,$data);
        $total_obj = new SunnyDeviceStatusTotal();
        $total_obj->addRecord($device_info,$data);
    }

    /**
     * 智能功率列表
     * @return array
     */
    public function returnAutoPowerSetList(){
        return  ['关闭','高','中','低','自动','自定义'];
    }

    /**
     * 获取电池类型列表
     * @return array
     */
    public function returnBatteryTypeList(){
        return  [10=>'铅酸电池',11=>'锂电池'];
    }

    public function returnLiBatteryTypeList(){
        //(LiFePO4-磷酸铁锂 NCM- 三元锂)
        return  ['LiFePO4'=>'磷酸铁锂','NCM'=>'三元锂'] ;
    }

    /**
     * 获取充电状态列表
     * @return mixed
     */
    public function returnChargeStatusList(){
        return  ['未开启充电','启动充电模式','mppt 充电模式','均衡充电模式','提升充电模式','浮充充电模式','限流(超功率)'];
    }

    /**
     * 系统电压列表
     * @return array
     */
    public function returnBatRateVoltList(){
        return [3=>'3',6=>'6',12=>'12',24=>'24',36=>'36',48=>'48'] ;
    }

    /**
     * 获取电池类型名称
     * @param $battery_type
     * @return mixed
     */
    public function getBatteryType($battery_type){

        $list = $this->returnBatteryTypeList();
        return isset($list[$battery_type]) ? $list[$battery_type] : '' ;
    }

    /**
     * 获取智能功率
     * @param $auto_power_set
     * @return mixed
     */
    public function getAutoPowerSet($auto_power_set){
        $list = $this->returnAutoPowerSetList();
        return isset($list[$auto_power_set]) ? $list[$auto_power_set] : '';
    }

    /**
     * 获取错误前台展示的列表信息
     * @param $device_id
     * @return mixed
     */
    public function getFaultShowNameList($device_id){

        $params['cond'] = 'device_id=:device_id AND is_deleted=:is_deleted AND is_fault=:is_fault';
        $params['args'] = [':device_id'=>$device_id,':is_deleted'=>'N',':is_fault'=>'Y'];
        $params['orderby'] = 'id DESC';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if(!$info || is_null($info['fault_info'])){
            return  '';
        }

        $fault_info = $info['fault_info'];
        $fault_info = explode(',',$fault_info);

        $list = $this->getFaultNameList();
        $res = [] ;
        foreach($fault_info as $v){
            if(isset($list[$v])){
                $res[]= "B".$v.$list[$v];
            }
        }
        return $res ? implode(',',$res):'';
    }

    /**
     * 将十进制转换为16进制 并添加左导0
     * @param $num
     * @param int $float
     * @return string
     */
    public function formatZero($num,$float=4){
        $num = $num ? str_pad(dechex($num), $float, "0", STR_PAD_LEFT):"0000";
        return $num ;
    }

    /**
     * 将十进制转换为16进制，并添加左导0，区分正负数
     * @param $num
     * @param int $float
     * @return mixed
     */
    public function trans10To16AddZero($num,$float=7){

        // 判断是否为正数
        if($num >= 0){

            //转换为2进制
            $num = decbin($num);
            $num = str_pad($num, $float, "0", STR_PAD_LEFT);
            $num = "0".$num ;
        }else{
            $num = decbin(abs($num));
            $num = str_pad($num, $float, "0", STR_PAD_LEFT);
            $num = "1".$num ;
        }

        $res = ( dechex(bindec($num)));
        return  str_pad($res, 4, "0", STR_PAD_LEFT);
    }

    /**
     * 获取字段最大或者最小字段
     * @param $device_id
     * @param $field
     * @param $sort_type
     * @return mixed
     */
    public function getMaxFieldByDeviceId($device_id,$field,$sort_type){

        $order_by = $field.'  '.$sort_type.' , id desc ';
        $params['cond'] ='device_id=:device_id AND is_deleted=:is_deleted';
        $params['args'] = [':device_id'=>$device_id,':is_deleted'=>'N'];
        $params['orderby'] = $order_by ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info[$field] : 0 ;
    }

}
