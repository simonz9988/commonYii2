<?php
namespace sunny\controllers;

use common\components\MyRedis;
use common\models\Language;
use common\models\PushTask;
use common\models\SiteConfig;
use common\models\SunnyDevice;
use common\models\SunnyDeviceBatteryParams;
use common\models\SunnyDeviceCategory;
use common\models\SunnyDeviceCategoryDetail;
use common\models\SunnyDeviceDetail;
use common\models\SunnyDeviceFault;
use common\models\SunnyDeviceLoadTime;
use common\models\SunnyDevicePostitionDetail;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceStatusToday;
use common\models\SunnyDeviceStatusTotal;
use common\models\SunnyDeviceSyncTask;
use common\models\SunnyDeviceTotal;
use common\models\SunnyManager;
use common\models\SunnyProject;
use Yii;

class DeviceController extends \sunny\controllers\BaseController {


    // 获取设备信息
    public function actionInfoByCode(){

        $qr_code = $this->postParam('qr_code');

        $device_obj = new  SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($qr_code);
        if(!$device_info){
            //  当前设备信息不存在
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        if($device_info['is_bind'] =='Y'){
            return $this->returnJson(['code'=>100076,'msg'=>getErrorDictMsg(100076),'data'=>[]]);
        }

        $category_obj = new SunnyDeviceCategory();
        $category_info = $category_obj->getInfoById($device_info['category_id']);

        //获取指定的SOP文档
        $lang_obj = new Language();
        $lang_id = $lang_obj->getUserDefaultLangId();

        $category_detail_obj = new SunnyDeviceCategoryDetail();
        $sop_url1 = $category_detail_obj->getSopUrlByIdAndLangId($category_info['id'],$lang_id);
        $site_config_obj = new SiteConfig();
        $static_url = $site_config_obj->getByKey('static_url');
        $sop_url = $static_url.$sop_url1 ;
        $data['device_info'] = [
            'id'=>$device_info['id'] ,
            'qr_code'=>$device_info['qr_code'] ,
            'category_name'=>$category_info? $category_info['name']:'',
            'sop_url' =>$static_url.'/device/preview?f='.$sop_url1
        ];

        $record_obj = new SunnyDeviceStatusRecord();
        // 智能功率列表
        $data['auto_power_set_list'] = $record_obj->returnAutoPowerSetList();
        // 蓄电池类型
        $data['battery_type_list'] = $record_obj->returnBatteryTypeList();
        // 系统功率列表
        $data['bat_rate_volt_list'] = $record_obj->returnBatRateVoltList();

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    public  function actionDoBind(){

        // 用户ID
        $user_id = $this->getLoginUserId();

        $project_id = $this->postParam('project_id');
        $project_obj = new SunnyProject();

        // 公司 ID
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        $project_info = $project_obj->getInfoByCustomerIdAndId($company_id,$project_id);
        if(!$project_info){
            return $this->returnJson(['code'=>100089,'msg'=>getErrorDictMsg(100089),'data'=>[]]);
        }

        $qr_code = $this->postParam('qr_code');

        $device_obj = new  SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($qr_code);
        if(!$device_info){
            //  当前设备信息不存在
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        if($device_info['is_bind'] =='Y'){
            //return $this->returnJson(['code'=>100076,'msg'=>getErrorDictMsg(100076),'data'=>[]]);
        }

        // 可以进行换绑


        $longitude = $this->postParam('longitude');
        $device_name = $this->postParam('device_name');
        $latitude = $this->postParam('latitude');
        $bind_image_url = $this->postParam('bind_image_url');
        $note = $this->postParam('note');
        $address = $this->postParam('address');
        $road_id = $this->postParam('road_id');
        $mark_no = $this->postParam('mark_no');

        if(!$longitude || !$latitude){
            return $this->returnJson(['code'=>100077,'msg'=>getErrorDictMsg(100077),'data'=>[]]);
        }

        if(!$device_name ){
            return $this->returnJson(['code'=>100078,'msg'=>getErrorDictMsg(100078),'data'=>[]]);
        }

        $user_model = new SunnyManager();
        $user_info = $user_model->getInfoById($user_id);


        $update_data['project_id'] = $project_id;
        $update_data['mark_no'] = $mark_no;
        $update_data['road_id'] = $road_id;
        $update_data['is_bind'] = 'Y';
        $update_data['longitude'] = $longitude;
        $update_data['device_name'] = $device_name;
        $update_data['latitude'] = $latitude;
        $update_data['bind_image_url'] = $bind_image_url;
        $update_data['note'] = $note;
        $update_data['address'] = $address;
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $update_data['customer_id'] = $user_info['id'];
        $update_data['company_id'] = $user_info['company_id'];
        if($device_info['is_bind'] =='Y'){
            // 在设备已绑定的情况下恢复删除状态
            $update_data['is_deleted'] = 'N';
        }

        $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$device_info['id']]);



        // 更新当前所有关联表的数据
        $cond = 'device_id=:device_id';
        $args = [':device_id'=>$device_info['id']];

        $battery_params_obj = new SunnyDeviceBatteryParams();
        $battery_update_data['project_id'] = $project_id;
        $battery_update_data['modify_time'] = date('Y-m-d H:i:s');
        $battery_params_obj->baseUpdate($battery_params_obj::tableName(),$battery_update_data,$cond,$args);

        $detail_obj = new SunnyDeviceDetail();
        $battery_update_data['project_id'] = $project_id;
        $battery_update_data['modify_time'] = date('Y-m-d H:i:s');
        $battery_params_obj->baseUpdate($detail_obj::tableName(),$battery_update_data,$cond,$args);

        $device_fault_obj = new SunnyDeviceFault();
        $fault_update_data['project_id'] = $project_id ;
        $fault_update_data['customer_id'] = $user_id ;
        $fault_update_data['company_id'] = $company_id ;
        $device_fault_obj->baseUpdate($device_fault_obj::tableName(),$fault_update_data,$cond,$args);

        $device_load_time = new SunnyDeviceLoadTime();
        $load_update_data['project_id'] = $project_id ;
        $load_update_data['company_id'] = $company_id ;
        $device_load_time->baseUpdate($device_load_time::tableName(),$load_update_data,$cond,$args);

        $position_detail_obj = new SunnyDevicePostitionDetail();
        $position_detail_update_data['project_id'] = $project_id;
        $position_detail_update_data['customer_id'] = $user_id;
        $position_detail_update_data['company_id'] = $company_id;
        $position_detail_obj->baseUpdate($position_detail_obj::tableName(),$position_detail_update_data,$cond,$args);

        $status_record_obj = new SunnyDeviceStatusRecord();
        $status_record_update_data['project_id'] = $project_id ;
        $status_record_update_data['company_id'] = $company_id ;
        $status_record_obj->baseUpdate($status_record_obj::tableName(),$status_record_update_data,$cond,$args);

        $today_obj  = new SunnyDeviceStatusToday();
        $today_update_data['project_id'] = $project_id ;
        $today_update_data['company_id'] = $company_id ;
        $today_update_data['customer_id'] = $user_id ;
        $today_obj->baseUpdate($today_obj::tableName(),$today_update_data,$cond,$args);

        $status_total_obj = new SunnyDeviceStatusTotal();
        $status_total_obj->baseUpdate($status_total_obj::tableName(),$today_update_data,$cond,$args);

        $total_obj = new SunnyDeviceTotal();
        $total_obj->baseUpdate($total_obj::tableName(),$today_update_data,$cond,$args);

        // 在未绑定的情况下进行数据的更新
        if($device_info['is_bind'] !='Y'){
            // 更新坐标具体信息
            $position_detail_obj = new SunnyDevicePostitionDetail();
            $position_detail_obj->updateDataByDeviceId($device_info);

            // 获取需要变更的redis key 值信息
            $redis_key_arr = $device_obj->returnPositionRedisKey($user_id,$user_info['company_id'],$project_id);

            // 设置对应的redis key 值信息
            $device_obj->addPositionRedis($redis_key_arr,$longitude,$latitude,$device_info['id']);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 获取状态
    public function actionGetStatus(){
        $qr_code = $this->postParam('qr_code');

        $device_obj = new  SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($qr_code);
        if(!$device_info){
            //  当前设备信息不存在
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        $status_record_obj = new SunnyDeviceStatusRecord();
        $info = $status_record_obj->getLastedInfoByDeviceId($device_info['id']);
        $data['detail'] = $info ;
        return  $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 获取周围信息
    public function actionGetNearby(){
        $longitude = $this->postParam('longitude');
        $latitude = $this->postParam('latitude');

        $user_id = $this->getLoginUserId();
        $device_obj = new SunnyDevice();
        $redis_key_arr = $device_obj->returnPositionRedisKey($user_id);
        $redis_obj = new MyRedis();
        $redis_key = $redis_key_arr['company'];
        $res = $redis_obj->georadius($redis_key,$longitude,$latitude,500,'km');

        $device_list = [] ;
        if($res){

            foreach($res as $id){
                $device_info = $device_obj->getInfoById($id);

                $device_list[] = [
                    'id'=>$device_info['id'],
                    'qr_code'=>$device_info['qr_code'],
                    'device_name'=>$device_info['device_name'],
                    'longitude'=>$device_info['longitude'],
                    'latitude'=>$device_info['latitude'],
                ];
            }
        }

        $data['device_list'] = $device_list ;
        return  $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 根据ID获取具体信息
     */
    public function actionGetDetailById(){
        $id = $this->postParam('id');

        $device_obj = new SunnyDevice();
        $info  = $device_obj->getInfoById($id);
        if(!$info){
            return  $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075)]);
        }
        $customer_id = $info['customer_id'];
        $user_id = $this->getLoginUserId() ;
        if($customer_id!=$user_id){
            // 去除判断
            //return  $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075)]);
        }

        $record_obj = new  SunnyDeviceStatusRecord();
        $record_info = $record_obj->getLastedInfoByDeviceId($id) ;
        $data['battery_charging_current'] = $record_info ? $record_info['battery_charging_current']: '';
        $data['battery_charging_unit'] = "A";
        $data['charging_power'] = $record_info ? $record_info['battery_panel_charging_voltage']: '';
        $data['charging_power_unit'] = "V";
        $data['battery_volume'] = $record_info ? $record_info['battery_volume']: '';
        $data['battery_volume_unit'] = "mh";
        $data['longitude'] = $info ? $info['longitude']: '';
        $data['latitude'] = $info ? $info['latitude']: '';
        $data['switch_status'] = $info && $info['switch_status']=='Y'?'ON': 'OFF';
        $data['brightness'] = $info? intval($info['brightness']): 0;
        $data['name'] = $info? $info['name']: '';
        $data['qr_code'] = $info? $info['qr_code']: '';

        $data['battery_type'] = $info ? $info['battery_type']: '11';
        $data['battery_type_list'] = $record_obj->returnBatteryTypeList();
        $data['battery_rate_volt'] = $info ? $info['battery_rate_volt']: '12';
        $data['battery_rate_volt_list'] = $record_obj->returnBatRateVoltList();
        $data['battery_rate_volt_unit'] = "V";
        $data['led_current_set'] = $info ? $info['led_current_set']: '0.9';
        $data['led_current_set_unit'] = 'A';
        $data['auto_power_set'] = $info ? $info['auto_power_set']: '2';
        $data['auto_power_set_list'] = $record_obj->returnAutoPowerSetList();;

        return  $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 获取设备列表
     */
    public function actionDeviceList(){
        $page = $this->postParam('page');
        $user_id = $this->getLoginUserId();
        $params['cond'] = 'customer_id=:customer_id AND is_deleted=:is_deleted AND status=:status ';
        $params['args'] = [':customer_id'=>$user_id,':is_deleted'=>'N',':status'=>'ENABLED'];
        $page_rows = 20 ;
        $page = $page > 0 ? $page : 0 ;
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_rows ;
        $obj = new SunnyDevice();
        $params['fields'] = 'id,device_name';
        $list = $obj->findAllByWhere($obj::tableName(),$params,$obj::getDb());
        $params['fields'] = 'count(1) as total';
        $total_info = $obj->findOneByWhere($obj::tableName(),$params,$obj::getDb());
        $total_num = $total_info && !is_null($total_info['total']) ? $total_info['total']:0;
        $total_page = ceil($total_num/$page_rows);

        $data['list'] = $list ;
        $data['total_page'] = $total_page ;
        return  $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    public function actionTotal(){
        $user_id = $this->getLoginUserId();

        $id = $this->postParam('id');
        $cond[] = 'customer_id=:customer_id';
        $args[':customer_id'] = $user_id;
        if($id){
            $cond[] = "device_id=:device_id";
            $args[':device_id'] = $id;
        }
        $range_type = strtoupper($this->postParam('range_type'));

        if($range_type && !in_array($range_type,['YEAR','MONTH','DAY'])){
            return  $this->returnJson(['code'=>100079,'msg'=>getErrorDictMsg(100079)]);
        }

        if(!$range_type){
            $range_type ="YEAR";
            $year = date("Y");
        }

        if($range_type=='YEAR'){
            $year = $year? $year : $this->postParam('year');

            if(!$year  ){
                return  $this->returnJson(['code'=>100082,'msg'=>getErrorDictMsg(100082)]);
            }
            $start_time = date($year."-01-01 00:00:00");
        }else if($range_type =='MONTH'){
            $year = $this->postParam('year');
            $month = $this->postParam('month');

            if(!$year || !$month ){
                return  $this->returnJson(['code'=>100081,'msg'=>getErrorDictMsg(100081)]);
            }

            $start_time = date($year."-".$month."-01 00:00:00");
        }else if($range_type =='DAY'){
            $year = $this->postParam('year');
            $month = $this->postParam('month');
            $day = $this->postParam('day');

            if(!$year || !$month || !$day){
                return  $this->returnJson(['code'=>100080,'msg'=>getErrorDictMsg(100080)]);
            }
            $day =  str_pad($day,2 , "0", STR_PAD_LEFT);
            $start_time = date($year."-".$month."-".$day." 00:00:00");
        }

        if(!$range_type){
            $range_type = 'DAY';
            $year = date("Y");
            $month = date("m");
            $day = date("d");
            $start_time = date($year."-".$month."-".$day." 00:00:00");
        }

        if($range_type){
            $cond[] = 'time_type=:time_type AND timestamp=:timestamp';
            $args[':time_type'] = $range_type;
            $args[':timestamp'] = strtotime($start_time);
        }


        $params['cond'] = implode(' AND ',$cond);
        $params['args'] = $args;
        $fields = 'sum(cumulative_charge) as total_cumulative_charge';
        $params['fields'] = $fields;
        $obj = new SunnyDeviceTotal();
        $info = $obj->findOneByWhere($obj::tableName(),$params,$obj::getDb());
        $data['total_cumulative_charge'] = $info && !is_null($info['total_cumulative_charge'])?$info['total_cumulative_charge']:0;
        $data['max_show_cumulative_charge'] = 999;

        $fields = 'sum(lighting_time) as total_lighting_time';
        $params['fields'] = $fields;
        $info = $obj->findOneByWhere($obj::tableName(),$params,$obj::getDb());
        $data['total_lighting_time'] = $info && !is_null($info['total_lighting_time'])?$info['total_lighting_time']:0;
        $data['total_lighting_time'] = ceil($data['total_lighting_time']/3600);
        $data['max_show_lighting_time'] = 400;//最大展示小时数

        // 查询最近7天的发电数
        $temp_list = $obj->getListRangByDay($user_id,$id);

        $recent_list = [] ;
        if($temp_list){
            foreach($temp_list as $v){
                $recent_list[] = ['total'=>$v['total'],'date'=>date('Y-m-d',$v['timestamp'])];
            }
        }
        $data['recent_list'] = $recent_list;
        return  $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 设置亮度
    public function actionSetBrightness(){

        $data = [] ;
        $device_id = $this->postParam('device_id');
        $brightness = $this->postParam('value');
        $brightness = intval($brightness);
        if($brightness >100 ||$brightness <1){
            echo   json_encode(['code'=>100083,'msg'=>getErrorDictMsg(100083),'data'=>$data]);exit;
        }

        $obj = new SunnyDevice() ;
        $update_data['brightness'] = $brightness ;
        $update_data['modify_time']=  date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

        // 增加同步任务
        $info = $obj->getInfoById($device_id);
        $task_obj = new SunnyDeviceSyncTask();
        $task_obj->addTask($info) ;
        echo   json_encode(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);exit;
    }


    // 设置开关状态
    public function actionSetSwitchStatus(){

        $data = [];

        $device_id = $this->postParam('device_id');
        $value = $this->postParam('value');
        $brightness = $this->postParam('value_light');
        $minute = $this->postParam('minute');
        $minute = intval($minute);
        $device_obj = new SunnyDevice();
        $info  = $device_obj->getInfoById($device_id);
        if(!$info){
            return  $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075)]);
        }

        $obj = new SunnyDevice() ;

        $switch_status  = $value=="ON"?"Y":"N" ;
        $update_data['switch_status'] = $switch_status ;
        if($switch_status =='Y'){

            $brightness = intval($brightness);

            if($brightness >100 ||$brightness <1){
                echo   json_encode(['code'=>100083,'msg'=>getErrorDictMsg(100083),'data'=>$data]);exit;
            }
            $update_data['minute'] = $minute ;
            $update_data['brightness'] = $brightness ;
        }else{
            $update_data['brightness'] = 0 ;
        }
        $update_data['minute'] = $minute ;
        $update_data['modify_time']=  date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

        // 增加同步任务
        $info = $obj->getInfoById($device_id);
        $task_obj = new SunnyDeviceSyncTask();
        $task_obj->addTask($info) ;

        echo   json_encode(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);exit;
    }

    public function actionPreview(){

        $f = getParam('f');
        $base_path = Yii::$app->basePath ;
        $file = $base_path.'/web/'.$f ;
        var_dump($file);exit;
        $r = file_exists($file);
        var_dump($file);exit;
        header("Content-type:application/octet-stream");
        $filename = iconv("UTF-8", "GB2312", basename($file));
        header("Content-Disposition:attachment;filename=" . $filename);
        header("Accept-ranges:bytes");
        header("Accept-length:".filesize($file));
        readfile($file);

    }

    // 负载电流设置
    public function actionSetLedCurrent(){

        $device_id = $this->postParam('device_id');
        $led_current_set = $this->postParam('value');

        $device_obj = new SunnyDevice();
        $info  = $device_obj->getInfoById($device_id);
        if(!$info){
            return  $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075)]);
        }

        $led_current_set = floatval($led_current_set);

        if($led_current_set >=0.15 && $led_current_set <=10){
            $obj = new SunnyDevice() ;

            $update_data['led_current_set'] = $led_current_set ;
            $update_data['modify_time']=  date('Y-m-d H:i:s');

            $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

            echo   json_encode(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);exit;
        }else{
            echo   json_encode(['code'=>100083,'msg'=>getErrorDictMsg(100083),'data'=>[]]);exit;
        }
    }

    // 设置智能功率
    public function actionSetAutoPower(){
        $device_id = $this->postParam('device_id');
        $auto_power_set = $this->postParam('value');

        $auto_power_set = intval($auto_power_set);

        if(in_array($auto_power_set,[0,1,2,3,4])){
            $obj = new SunnyDevice() ;

            $update_data['auto_power_set'] = $auto_power_set ;
            $update_data['modify_time']=  date('Y-m-d H:i:s');
            $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

            echo   json_encode(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);exit;
        }else{
            echo   json_encode(['code'=>100084,'msg'=>getErrorDictMsg(100084),'data'=>[]]);exit;
        }
    }

    // 设置蓄电池类型
    public function actionSetBatteryType(){
        $device_id = $this->postParam('device_id');
        $battery_type = $this->postParam('value');

        $battery_type = intval($battery_type);

        if(in_array($battery_type,[10,11])){
            $obj = new SunnyDevice() ;

            $update_data['battery_type'] = $battery_type ;
            $update_data['modify_time']=  date('Y-m-d H:i:s');
            $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

            echo   json_encode(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);exit;
        }else{
            echo   json_encode(['code'=>100085,'msg'=>getErrorDictMsg(100085),'data'=>[]]);exit;
        }
    }

    // 设置系统电压
    public  function actionSetBatteryRateVolt(){
        $device_id = $this->postParam('device_id');
        $battery_rate_volt = $this->postParam('value');

        $battery_rate_volt = intval($battery_rate_volt);

        if(in_array($battery_rate_volt,[3,6,12,24,36,48])){
            $obj = new SunnyDevice() ;

            $update_data['battery_rate_volt'] = $battery_rate_volt ;
            $update_data['modify_time']=  date('Y-m-d H:i:s');
            $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

            echo   json_encode(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);exit;
        }else{
            echo   json_encode(['code'=>100086,'msg'=>getErrorDictMsg(100086),'data'=>[]]);exit;
        }
    }

    public function actionSetDevice(){
        $data = [] ;

        $obj = new SunnyDevice() ;
        $device_id = $this->postParam('device_id');
        // 判断设备信息是否存在
        $device_info = $obj->getInfoById($device_id);
        if(!$device_info){
            echo   json_encode(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>$data]);exit;
        }

        // 亮度
        $brightness = $this->postParam('brightness');
        $brightness = intval($brightness);
        if($brightness >100 ||$brightness <1){
            echo   json_encode(['code'=>100083,'msg'=>getErrorDictMsg(100083),'data'=>$data]);exit;
        }

        // 开关状态
        $switch_status = $this->postParam('switch_status');
        $switch_status  = $switch_status=="ON"?"Y":"N" ;

        //负载电流设置
        $led_current_set = $this->postParam('led_current_set');
        $led_current_set = floatval($led_current_set);
        if($led_current_set < 0.15 || $led_current_set > 10){
            echo  json_encode(['code'=>100083,'msg'=>getErrorDictMsg(100083),'data'=>[]]);exit;
        }

        //设置智能功率
        $auto_power_set = $this->postParam('auto_power_set');
        $auto_power_set = intval($auto_power_set);
        if(!in_array($auto_power_set,[0,1,2,3,4])){
            echo   json_encode(['code'=>100084,'msg'=>getErrorDictMsg(100084),'data'=>[]]);exit;
        }

        //蓄电池类型
        $battery_type = $this->postParam('battery_type');
        $battery_type = intval($battery_type);
        if(!in_array($battery_type,[10,11])){
            echo   json_encode(['code'=>100085,'msg'=>getErrorDictMsg(100085),'data'=>[]]);exit;
        }

        // 蓄电池容量
        $battery_rate_volt = $this->postParam('battery_rate_volt');
        $battery_rate_volt = intval($battery_rate_volt);
        if(!in_array($battery_rate_volt,[3,6,12,24,36,48])){
            echo   json_encode(['code'=>100086,'msg'=>getErrorDictMsg(100086),'data'=>[]]);exit;
        }

        $update_data['brightness'] = $brightness ;
        $update_data['switch_status'] = $switch_status ;
        $update_data['led_current_set'] = $led_current_set ;
        $update_data['auto_power_set'] = $auto_power_set ;
        $update_data['battery_type'] = $battery_type ;
        $update_data['battery_rate_volt'] = $battery_rate_volt ;
        $update_data['modify_time']=  date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

        // 增加同步任务
        $info = $obj->getInfoById($device_id);
        $task_obj = new SunnyDeviceSyncTask();
        $task_obj->addTask($info) ;

        echo   json_encode(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);exit;
    }

    /**
     * 获取错误列表
     */
    public function actionGetFaultList(){

        $user_id = $this->getLoginUserId();
        $device_obj = new SunnyDevice();
        $page = $this->getParam('page') ;
        $is_fault = $this->getParam('is_fault') ;
        $is_fault = $is_fault=="Y" ? "Y" :"N";
        $page = $page > 0 ? $page: 1 ;
        $page_num = $this->page_rows ;
        $data = $device_obj->getFaultList($user_id,$page,$page_num,$is_fault);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 获取初始化信息
     */
    public function actionGetInitInfo(){
        $user_id = $this->getLoginUserId();
        $obj = new SunnyManager();
        $info = $obj->getInfoById($user_id);
        if($info['init_zoom'] && !is_null($info['init_zoom'])){
            $data['init_zoom'] = $info['init_zoom'];
            $data['init_longitude'] = $info['init_longitude'];
            $data['init_latitude'] = $info['init_latitude'];
        }else{
            $site_config = new SiteConfig();
            $data['init_zoom'] = $site_config->getByKey('init_zoom');
            $data['init_longitude'] = $site_config->getByKey('init_longitude');
            $data['init_latitude'] = $site_config->getByKey('init_latitude');
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 获取设备亮灯时段信息
     */
    public function actionTimeList(){
        $device_id = $this->postParam('device_id');
        $load_obj = new SunnyDeviceLoadTime();
        $tmp_list = $load_obj->getListByDeviceId($device_id);

        $list = [] ;

        if($tmp_list){
            foreach($tmp_list as $v){
                $item['time_end'] = $v['time_end'];
                $item['minutes'] = $v['minutes'];
                $item['load_sensor_on_power'] = $v['load_sensor_on_power'];
                $item['load_sensor_off_power'] = $v['load_sensor_off_power'];

                $list[] = $item;
            }
        }else{

            for($i=1;$i<=10;$i++){
                $item['time_end'] = $i;
                $item['minutes'] = 0;
                $item['load_sensor_on_power'] = 0;
                $item['load_sensor_off_power'] = 0;
                $list[] = $item;
            }
        }

        $data['list'] = $list ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    public function actionSaveTimeList(){
        // 设备ID
        $device_id = $this->postParam('device_id');
        $minutes = $_POST['minutes'];
        $load_sensor_on_power = $_POST['load_sensor_on_power'];

        $total_minutes = 0  ;
        if($minutes){
            foreach($minutes as $v){
                if($v > 15*60){
                    return $this->returnJson(['code'=>'100087']);
                }
                $total_minutes = $total_minutes + $v ;
            }
        }
        if($total_minutes > 1440){
            //return $this->returnJson(['code'=>'100087']);
        }

        $obj = new SunnyDeviceLoadTime();
        $obj->saveByDeviceId($device_id,$minutes,$load_sensor_on_power);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    /**
     * 工矿
     */
    public function actionWorking(){
        $device_id = $this->getParam('device_id');
        $start_time = $this->getParam('start_time');
        $end_time = $this->getParam('end_time');
        $start_time = $start_time  ? $start_time : date('Y-m-d 00:00:00',time()-30*86400);
        $end_time  = $end_time?$end_time:date('Y-m-d H:i:s');

        $total_obj = new SunnyDeviceTotal();
        $params['cond'] = 'device_id =:device_id AND time_type =:time_type AND  timestamp >=:start_time AND timestamp <=:end_time' ;
        $params['args'] =[':device_id'=>$device_id,':time_type'=>'DAY',':start_time'=>strtotime($start_time),':end_time'=>strtotime($end_time)];
        $list = $total_obj->findAllByWhere($total_obj::tableName(),$params,$total_obj::getDb());


        $light_list  = [] ;
        $diangliang_list  = [] ;
        $battery_list  = [] ;
        $panel_list  = [] ;
        $time_list  = [] ;

        $load_dc_power = [] ;
        $charging_current = [] ;
        $cumulative_charge = [] ;
        $brightness = [] ;

        $battery_charging_current = [];

        $battery_voltage = [] ;
        $battery_temperature = [] ;

        $battery_panel_charging_voltage = [] ;
        $battery_panel_charging_current = [] ;
        $charging_power = [] ;

        $timestamp_list = [];
        if($list){
            foreach($list as $v){
                $timestamp_list[$v['timestamp']] = $v ;
            }
        }

        $start_time_timestamp = strtotime($start_time);
        $end_time_timestamp = strtotime($end_time);
        for($i=$start_time_timestamp;$i<=$end_time_timestamp;$i = $i+86400){
            if(isset($timestamp_list[$i])){
                $v = $timestamp_list[$i];
                $load_dc_power[] = is_null($v['load_dc_power'])?'0':$v['load_dc_power'];
                $charging_current[] = is_null($v['charging_current'])?'0':$v['charging_current'];
                $cumulative_charge[] = is_null($v['cumulative_charge'])?'0':$v['cumulative_charge'];
                $brightness[] = is_null($v['brightness'])?'0':$v['brightness'];

                $battery_charging_current[] = is_null($v['battery_charging_current'])?'0':$v['battery_charging_current'];

                $battery_voltage[] = is_null($v['battery_voltage'])?'0':$v['battery_voltage'];
                $battery_temperature[] = is_null($v['battery_temperature'])?'0':$v['battery_temperature'];

                $battery_panel_charging_voltage[] = is_null($v['battery_panel_charging_voltage'])?'0':$v['battery_panel_charging_voltage'];
                $battery_panel_charging_current[] = is_null($v['battery_panel_charging_current'])?'0':$v['battery_panel_charging_current'];
                $charging_power[] = is_null($v['charging_power'])?'0':$v['charging_power'];
            }else{

                $load_dc_power[] = 0;
                $charging_current[] = 0;
                $cumulative_charge[] = 0;
                $brightness[] = 0;

                $battery_charging_current[] = 0;

                $battery_voltage[] = 0;
                $battery_temperature[] = 0;

                $battery_panel_charging_voltage[] = 0;
                $battery_panel_charging_current[] = 0;
                $charging_power[] = 0;
            }

            $time_list[] = date('m-d',$i);
        }
        if($list){
            foreach($list as $v){

                $load_dc_power[] = is_null($v['load_dc_power'])?'0':$v['load_dc_power'];
                $charging_current[] = is_null($v['charging_current'])?'0':$v['charging_current'];
                $cumulative_charge[] = is_null($v['cumulative_charge'])?'0':$v['cumulative_charge'];
                $brightness[] = is_null($v['brightness'])?'0':$v['brightness'];

                $battery_charging_current[] = is_null($v['battery_charging_current'])?'0':$v['battery_charging_current'];

                $battery_voltage[] = is_null($v['battery_voltage'])?'0':$v['battery_voltage'];
                $battery_temperature[] = is_null($v['battery_temperature'])?'0':$v['battery_temperature'];

                $battery_panel_charging_voltage[] = is_null($v['battery_panel_charging_voltage'])?'0':$v['battery_panel_charging_voltage'];
                $battery_panel_charging_current[] = is_null($v['battery_panel_charging_current'])?'0':$v['battery_panel_charging_current'];
                $charging_power[] = is_null($v['charging_power'])?'0':$v['charging_power'];

                $time_list[] = date('m-d',$v['timestamp']);
            }
        }

        $light_list['load_dc_power'] = $load_dc_power ;
        $light_list['charging_current'] = $charging_current ;
        $light_list['cumulative_charge'] = $cumulative_charge ;
        $light_list['brightness'] = $brightness ;
        $data['light_list'] = $light_list ;


        $diangliang_list['charging_current'] = $charging_current ;
        $diangliang_list['battery_charging_current'] = $battery_charging_current ;
        $data['diangliang_list'] = $diangliang_list ;

        $battery_list['battery_voltage'] = $battery_voltage ;
        $battery_list['battery_charging_current'] = $battery_charging_current ;
        $battery_list['battery_temperature'] = $battery_temperature ;

        $data['battery_list'] = $battery_list ;

        $panel_list['battery_panel_charging_voltage'] = $battery_panel_charging_voltage ;
        $panel_list['battery_panel_charging_current'] = $battery_panel_charging_current ;
        $panel_list['charging_power'] = $charging_power ;
        $data['panel_list'] = $panel_list ;


        $data['time_list'] = $time_list ;

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 更新位置信息
    public function actionUpdatePosition(){
        $device_id = $this->postParam('device_id') ;
        $longitude = $this->postParam('longitude') ;
        $latitude = $this->postParam('latitude') ;

        $update_data['longitude'] = $longitude ;
        $update_data['latitude'] = $latitude ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        $device_obj = new SunnyDevice();
        $res = $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);
        if(!$res){
            return $this->returnJson(['code'=>100090,'msg'=>getErrorDictMsg(100090),'data'=>[]]);
        }

        // 新增计划任务
        $task_obj = new PushTask();
        $task_obj->addPositionTask($device_id);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

}