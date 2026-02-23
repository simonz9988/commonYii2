<?php
namespace sunny\controllers;

use common\components\Ecosession;
use common\components\Filter;
use common\components\MyRedis;
use common\models\Ad;
use common\models\Article;
use common\models\Coin;
use common\models\EmailCode;
use common\models\Language;
use common\models\LanguagePacket;
use common\models\Member;
use common\models\MiningMachine;
use common\models\MiningMachineUserBalance;
use common\models\PushTask;
use common\models\RobotUserBalance;
use common\models\SiteConfig;
use common\models\SmsLog;
use common\models\SunnyCompany;
use common\models\SunnyDevice;
use common\models\SunnyDeviceBatteryParams;
use common\models\SunnyDeviceCategory;
use common\models\SunnyDeviceCategoryDetail;
use common\models\SunnyDeviceFault;
use common\models\SunnyDeviceLoadTime;
use common\models\SunnyDevicePostitionDetail;
use common\models\SunnyDeviceStatusInfo;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceStatusToday;
use common\models\SunnyDeviceStatusTotal;
use common\models\SunnyDeviceSyncTask;
use common\models\SunnyDeviceTemplate;
use common\models\SunnyDeviceTotal;
use common\models\SunnyLog;
use common\models\SunnyManager;
use common\models\SunnyProject;
use common\models\SunnyRoad;
use common\models\UserPlatformKey;
use Yii;
use yii\redis\Session;

class ProjectController extends \sunny\controllers\BaseController {


    // 新增
    public function actionDoAdd(){

        // 用户ID
        $customer_id = $this->getLoginUserId();
        $name = $this->postParam('name');
        $id = $this->postParam('id');
        $unique_code = $this->postParam('unique_code');
        $time_zone = $this->postParam('time_zone');
        $country_code = $this->postParam('country_code');
        $province = $this->postParam('province');
        $city = $this->postParam('city');
        $area = $this->postParam('area');
        $address = $this->postParam('address');
        $longitude = $this->postParam('longitude');
        $latitude = $this->postParam('latitude');
        $map_name = $this->postParam('map_name');
        $img_url = $this->postParam('img_url');
        $note = $this->postParam('note');

        $add_data = compact('note','img_url','customer_id','name','unique_code','time_zone','country_code','province','city','area','address','longitude','latitude','map_name');

        $obj = new SunnyProject();
        $res= $obj->savePostData($id,$add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }
        $data = [];
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 获取项目列表
    public function actionList(){

        $user_id = $this->getLoginUserId();
        $page = $this->getParam('page','int');
        $name = $this->getParam('name');

        $page = $page > 0 ? $page :1 ;

        $data['page_num'] = $this->page_num ;

        $obj = new SunnyProject();
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);
        $total_num = $obj->getTotalNumByCompanyId($company_id,$name) ;
        $total_page = ceil($total_num/$this->page_num);
        $data['total_page'] = $total_page ;

        $list = $obj->getListByCustomerAndPage($company_id,$page,$this->page_num,$name) ;
        $data['list'] = $list ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 首页汇总
    public function actionIndexTotal(){

        $project_id = $this->getParam('project_id','int');
        $user_id = $this->getLoginUserId() ;
        $device_obj = new SunnyDevice();

        $date = $this->getParam('date');
        $date = $date ? $date:date('Y-m-d');
        $start_time = date('Y-m-d 00:00:00',strtotime($date));
        $end_time = date('Y-m-d 23:59:59',strtotime($date));

        // 公司 ID
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        // 用户设备总数目
        $total_num = $device_obj->getTotalNumByCustomerId($company_id,$project_id);
        $data['total_num'] = $total_num ;

        // 所有有故障的数目
        $fault_num = $device_obj->getTotalFaultNumByCustomerId($company_id,$project_id);
        $data['fault_num'] = $fault_num ;

        // 离线数目
        $offline_num = 0 ;
        $data['offline_num'] = $device_obj->getTotalNumByCustomerIdAndIsOffline($company_id,'Y',$project_id); ;

        // 负载开数目  通过是否离线和开关灯状态进行展示
        $switch_on_num = $device_obj->getTotalNumByCustomerIdAndSwitchStatus($company_id,'Y',$project_id);
        $data['switch_on_num'] = $switch_on_num;

        // 负载关数目
        $switch_off_num = $device_obj->getTotalNumByCustomerIdAndSwitchStatus($company_id,'N',$project_id);
        $data['switch_off_num'] = $switch_off_num;

        // 累计发电量
        $total_obj = new SunnyDeviceTotal();
        $cumulative_charge_list = $total_obj->getCumulativeListByTimeTypeAndNum($company_id,'HALF_HOUR',$project_id,$date);
        $data['cumulative_charge_list'] = $cumulative_charge_list;

        //今日用电量
        $today_obj = new SunnyDeviceStatusToday();
        $today_used_energy = $today_obj->getCustomerTodayTotalUsedEnergy($company_id,$project_id,$date);
        $data['today_used_energy'] = $today_used_energy;

        //今日发电量
        $today_obj = new SunnyDeviceStatusToday();
        $today_generate_energy = $today_obj->getCustomerTodayTotalGenerateEnergy($company_id,$project_id,$date);
        $data['today_generate_energy'] = $today_generate_energy;

        // 总发电量
        $total_obj = new SunnyDeviceStatusTotal();
        //$total_generate_energy = $total_obj->getCustomerTotalGenerateEnergy($company_id,$project_id);
        $total_generate_energy = $today_obj->getCustomerTotalTotalGenerateEnergy($company_id,$project_id,$date);
        $data['total_generate_energy'] = $total_generate_energy;

        // 总用电量
        //$total_used_energy = $total_obj->getCustomerTotalUsedEnergy($company_id,$project_id);
        $total_used_energy = $today_obj->getCustomerTotalTotalUsedEnergy($company_id,$project_id,$date);
        $data['total_used_energy'] = $total_used_energy;

        // 总发电功率 发电电压*发电电流
        $status_info_obj  = new SunnyDeviceStatusInfo();
        #TODO
        $data['total_generating_power'] = 99999;

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 设备列表
     */
    public function actionDeviceList(){

        $project_id = $this->postParam('project_id') ;
        $road_id = $this->postParam('road_id') ;
        $page = $this->postParam('page','int') ;
        $page = $page > 0 ? $page :1 ;
        $user_id = $this->getLoginUserId();

        // 公司 ID
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        $project_obj = new  SunnyProject();
        $project_info = $project_obj->getInfoByCustomerIdAndId($company_id,$project_id);
        if(!$project_info){
            return $this->returnJson(['code'=>100089,'msg'=>getErrorDictMsg(100089),'data'=>[]]);
        }

        $sort = $this->getParam('sort');
        $sort = $sort ? $sort :'common';

        $filter_type = $this->getParam('filter_type') ;

        // 项目的全部数目
        $device_obj = new SunnyDevice();
        $total_num = $device_obj->getTotalNumByProjectId($project_id,$road_id,$filter_type);
        $data['total_num'] = $total_num;
        // 告警数目
        $data['fault_num'] = $device_obj->getTotalFaultNumByProjectId($project_id,$road_id);
        // 灭灯数目
        $data['switch_off_num'] = $device_obj->getTotalSwitchOffNumByProjectId($project_id,$road_id);
        // 离线数目
        $data['offline_num'] = $device_obj->getTotalOfflineNumByProjectId($project_id) ;
        // 每页展示数目
        $page_rows = $this->page_rows ;
        $data['page_rows'] = $page_rows;

        // 总页码
        $total_page = ceil($total_num/$page_rows);
        $data['total_page'] = $total_page ;

        $data['list'] = $device_obj->getListByProjectId($project_id,$page,$page_rows,$sort,$road_id,$filter_type);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);

    }

    // 设备详情
    public function actionDeviceDetail(){
        $device_id = $this->postParam('device_id');
        $user_id = $this->getLoginUserId();

        // 公司 ID
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoByIdAndCustomerId($device_id,$company_id);
        if(!$device_info){
            // 设备信息不存在
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        $data = $device_obj->getDetailFromProject($device_info);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 获取设备枚举值
    public function actionEnumList(){
        $record_obj = new SunnyDeviceStatusRecord();

        //智能功率列表
        $data['auto_power_set_list'] = $record_obj->returnAutoPowerSetList();

        // 负载电流列表 0.15  7  step 0.01
        $data['charging_min'] = 0.15 ;
        $data['charging_max'] = 7 ;
        $data['charging_step'] = 0.01 ;

        // 蓄电池类型
        $data['battery_type_list'] = $record_obj->returnBatteryTypeList();
        $data['li_battery_type_list'] = $record_obj->returnLiBatteryTypeList();

        // 系统功率列表
        $data['bat_rate_volt_list'] = $record_obj->returnBatRateVoltList();

        // 光控电压 light_control_volt
        $data['light_control_volt_min'] =3 ;
        $data['light_control_volt_max'] = 11 ;
        $data['light_control_volt_step'] = 1 ;
        $data['light_control_volt_default'] = 5 ;

        // 通用电压最小值
        $data['common_volt_min'] = 7.5 ;
        // 通用电压最大值
        $data['common_volt_max'] = 17 ;
        // 通用电压步长
        $data['common_volt_step'] = 0.1 ;

        // 超压电压
        $data['bat_over_volt_default'] = 15.5 ;
        // 充电限制电压
        $data['bat_charge_limit_volt_default'] = 15 ;
        // 均衡充电电压
        $data['bat_const_charge_volt_default'] = 14.6 ;
        // 提升充电电压
        $data['bat_improve_charge_volt_default'] = 14.4 ;
        // 浮充充电电压
        $data['bat_float_charge_volt_default'] = 13.8 ;
        // 提升充电返回电压
        $data['bat_improve_charge_back_volt_default'] = 13.2 ;
        // 过放返回电压
        $data['bat_over_discharge_back_volt_default'] = 12.6 ;
        // 欠压警告
        $data['bat_under_volt_default'] = 12.0 ;
        // 过放电压
        $data['bat_over_discharge_volt_default'] = 11.0 ;


        // 温度上限 40~90
        $data['temper_step'] = 1 ;
        $data['top_temper_min'] = 40 ;
        $data['top_temper_max'] = 90 ;
        // 温度下限 0~40
        $data['down_temper_min'] = -40 ;
        $data['down_temper_max'] = 0 ;


        $data['bat_discharge_limit_volt'] = 10.6 ;//放电限制电压
        $data['bat_stop_soc'] = 25610 ;//充电截止 SOC,放电截 止 SOC
        $data['bat_over_discharge_delay'] = 5 ;//过放延时时间
        $data['bat_const_charge_time'] = 120 ;//均衡充电时间

        $data['bat_improve_charge_time'] = 120 ;//提升充电时间
        $data['bat_const_charge_gap_time'] = 30 ;//均衡充电间隔
        $data['coeff_temper_compen'] = 5 ;//温度补偿系数
        $data['heat_bat_start_temper'] = -10 ;//(锂电)加热启动电池温 度

        $data['heat_bat_stop_temper'] = -5 ;//锂电)加热停止电池温
        $data['bat_switch_dc_volt'] = 11.5 ;//市电切换电压
        $data['stop_charge_current_set'] = 0 ;//((锂电) 停止充电电流
        $data['dc_load_mode'] = 0 ;//直流负载工作模式
        $data['light_control_delay_time'] = 0 ;//光控延时时间 （家用：分钟）

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 保存负载参数
     */
    public function actionSaveLoadParams(){

        $device_id = $this->postParam('device_id') ;
        $device_obj = new SunnyDevice();
        $user_id = $this->getLoginUserId();

        // 检测任务是否完成 没有完成给出反馈
        $push_task_obj = new PushTask();
        $push_task_info = $push_task_obj->checkDoneByTypeAndBusinessId($device_id,'LOAD_PARAMS');
        if(!$push_task_info){
            return $this->returnJson(['code'=>100095,'msg'=>getErrorDictMsg(100095),'data'=>[]]);
        }

        // 公司 ID
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        // 判断设备信息是否存在
        $device_info = $device_obj->getInfoByIdAndCustomerId($device_id,$company_id);
        if(!$device_info){
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        // 负载电流
        $led_current_set = $this->postParam('led_current_set');
        $led_current_set = floatval($led_current_set);
        //$battery_rate_volt = $this->postParam('battery_rate_volt');
        // 智能功率
        $auto_power_set = $this->postParam('auto_power_set');
        $auto_power_set = intval($auto_power_set);
        $update_data['auto_power_set'] = $auto_power_set ;
        //$update_data['battery_rate_volt'] = $battery_rate_volt ;
        $update_data['led_current_set'] = $led_current_set ;
        $update_data['modify_time']=  date('Y-m-d H:i:s');
        $res = $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);
        if(!$res){
            return $this->returnJson(['code'=>100091,'msg'=>getErrorDictMsg(100091),'data'=>[]]);
        }

        // 亮灯时段
        $minutes = isset($_POST['minutes'])?$_POST['minutes']:[];
        $load_sensor_on_power = isset($_POST['load_sensor_on_power'])?$_POST['load_sensor_on_power']:[];

        $total_minutes = 0  ;
        if($minutes){
            foreach($minutes as $v){
                if($v > 15*60){
                    return $this->returnJson(['code'=>'1','msg'=>getErrorDictMsg(100087)]);
                }
                $total_minutes = $total_minutes + $v ;
            }
        }
        if($total_minutes > 1440){

        }

        $obj = new SunnyDeviceLoadTime();
        $obj->saveByDeviceId($device_id,$minutes,$load_sensor_on_power);

        // 新增同步任务
        $task_obj = new SunnyDeviceSyncTask();
        $task_obj->addBatteryTask($device_info['qr_code']);

        $push_task_obj->addLoadParams($device_id);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 保存电池参数
    public function actionSaveBatteryParams(){
        $device_id = $this->postParam('device_id') ;
        $device_obj = new SunnyDevice();
        $user_id = $this->getLoginUserId();

        // 检测任务是否完成 没有完成给出反馈
        $push_task_obj = new PushTask();
        $push_task_info = $push_task_obj->checkDoneByTypeAndBusinessId($device_id,'BATTERY_PARAMS');
        if(!$push_task_info){
            return $this->returnJson(['code'=>100095,'msg'=>getErrorDictMsg(100095),'data'=>[]]);
        }

        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        $device_info = $device_obj->getInfoByIdAndCustomerId($device_id,$company_id);
        if(!$device_info){
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        $battery_type = $this->postParam('battery_type');
        $battery_rate_volt = $this->postParam('battery_rate_volt');
        $update_data['battery_type'] = $battery_type ;
        $update_data['battery_rate_volt'] = $battery_rate_volt ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$device_info['id']]);

        $obj = new SunnyDeviceBatteryParams();
        $params_info = $obj->getInfoByDeviceId($device_info['id']);
        // 光控电压
        $li_type = $this->postParam('li_type');
        //(LiFePO4-磷酸铁锂 NCM- 三元锂)
        if($battery_type == 11){
            $params_update['li_type'] = $li_type =='NCM'? 'NCM':'LiFePO4';
        }
        $params_update['light_control_volt'] = $this->postParam('light_control_volt');
        $params_update['bat_over_volt'] = $this->postParam('bat_over_volt');// 超压电压
        $params_update['bat_charge_limit_volt'] = $this->postParam('bat_charge_limit_volt');//充电限制电压
        $params_update['bat_const_charge_volt'] = $this->postParam('bat_const_charge_volt');//均衡充电电压
        $params_update['bat_improve_charge_volt'] = $this->postParam('bat_improve_charge_volt');//提升充电电压
        $params_update['bat_float_charge_volt'] = $this->postParam('bat_float_charge_volt');//浮充充电电压
        $params_update['bat_improve_charge_back_volt'] = $this->postParam('bat_improve_charge_back_volt');//充电返回电压
        $params_update['bat_over_discharge_back_volt'] = $this->postParam('bat_over_discharge_back_volt');//过放返回电压
        $params_update['bat_under_volt'] = $this->postParam('bat_under_volt');//欠压警告电压
        $params_update['bat_over_discharge_volt'] = $this->postParam('bat_over_discharge_volt');//过放电压
        $params_update['charge_max_temper'] = $this->postParam('charge_max_temper');//充电上限温度
        $params_update['charge_min_temper'] = $this->postParam('charge_min_temper');//充电下限温度
        $params_update['discharge_max_temper'] = $this->postParam('discharge_max_temper');//放电上限温度
        $params_update['discharge_min_temper'] = $this->postParam('discharge_min_temper');//放电下限温度
        $params_update['modify_time'] = date('Y-m-d H:i:s');

        $params_update['bat_discharge_limit_volt'] = $this->postParam('bat_discharge_limit_volt');//放电限制电压
        $params_update['bat_stop_soc'] = $this->postParam('bat_stop_soc');//充电截止 SOC,放电截 止 SOC
        $params_update['bat_over_discharge_delay'] = $this->postParam('bat_over_discharge_delay') ;//过放延时时间
        $params_update['bat_const_charge_time'] = $this->postParam('bat_const_charge_time');//均衡充电时间
        $params_update['bat_improve_charge_time'] = $this->postParam('bat_improve_charge_time') ;//提升充电时间
        $params_update['bat_const_charge_gap_time'] = $this->postParam('bat_const_charge_gap_time') ;//均衡充电间隔
        $params_update['coeff_temper_compen'] = $this->postParam('coeff_temper_compen') ;//温度补偿系数
        $params_update['heat_bat_start_temper'] = $this->postParam('heat_bat_start_temper') ;//(锂电)加热启动电池温 度
        $params_update['heat_bat_stop_temper'] = $this->postParam('heat_bat_stop_temper');//锂电)加热停止电池温
        $params_update['bat_switch_dc_volt'] = $this->postParam('bat_switch_dc_volt');//市电切换电压
        $params_update['stop_charge_current_set'] = $this->postParam('stop_charge_current_set') ;//((锂电) 停止充电电流
        $params_update['dc_load_mode'] = $this->postParam('dc_load_mode') ;//直流负载工作模式
        $params_update['light_control_delay_time'] = $this->postParam('light_control_delay_time') ;//光控延时时间 （家用：分钟）


        if($params_info){
            $obj->baseUpdate($obj::tableName(),$params_update,'id=:id',[':id'=>$params_info['id']]);
        }else{
            $params_update['device_id'] = $device_info['id'];
            $params_update['project_id'] = $device_info['project_id'];
            $params_update['is_deleted'] = 'N';
            $params_update['create_time'] = date('Y-m-d H:i:s');
            $obj->baseInsert($obj::tableName(),$params_update);
        }

        // 新增同步任务
        $task_obj = new SunnyDeviceSyncTask();
        $task_obj->addBatteryTask($device_info['qr_code']);

        $push_task_obj->addBatteryParams($device_id);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 保存路段洗洗
    public function actionSaveRoad(){
        $id = $this->postParam('id') ;
        $project_id = $this->postParam('project_id') ;
        $name = $this->postParam('name') ;
        $note = $this->postParam('note') ;
        $user_id = $this->getLoginUserId();

        // 公司 ID
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        $project_obj = new  SunnyProject();
        $project_info = $project_obj->getInfoByCustomerIdAndId($company_id,$project_id);
        if(!$project_info){
            return $this->returnJson(['code'=>100089,'msg'=>getErrorDictMsg(100089),'data'=>[]]);
        }

        $road_obj = new SunnyRoad();

        $company_id = $project_info['company_id'];
        $add_data = compact('project_id','name','note','company_id');
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        $res = $road_obj->saveData($id,$add_data);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 删除路段信息
    public function actionDelRoad(){
        $id = $this->postParam('id') ;

        $road_obj = new SunnyRoad();
        $update_data['is_deleted'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $road_obj->baseUpdate($road_obj::tableName(),$update_data,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 获取路段列表
    public function actionRoadList(){
        $project_id = $this->getParam('project_id') ;
        $page = $this->getParam('page');
        $page = $page > 0 ? $page : 1 ;
        $road_obj = new SunnyRoad();
        $total_num = $road_obj->getTotalNumByProjectId($project_id);
        $page_rows = $this->page_rows;
        $total_page = ceil($total_num/$page_rows);
        $data['total_page'] = $total_page ;
        $data['page_rows'] = $page_rows ;
        $list = $road_obj->getListByProjectId($project_id,$page,$page_rows,'id,name,note');
        $data['list'] = $list ;

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 错误列表
    public function     actionFaultList(){

        $status_record_obj = new SunnyDeviceStatusRecord();
        $data['fault_type_list'] = $status_record_obj->getFaultNameList();

        $status = $this->postParam('status') ;
        $start_date = $this->postParam('start_date');
        $start_date = $start_date ? $start_date:date('Y-m-d');
        $end_date = $this->postParam('end_date');
        $end_date = $end_date ? $end_date:date('Y-m-d');
        $fault_id = $this->postParam('fault_id');
        $page = $this->postParam('page');
        $page = $page > 0 ? $page : 1 ;

        $obj = new SunnyDeviceFault();
        $page_num = $this->page_rows;
        $data['page_num'] = $page_num ;
        $user_id = $this->getLoginUserId() ;

        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        $total_num = $obj->getTotalNum($company_id,$status,$fault_id,$start_date,$end_date);
        $total_page = ceil($total_num/$page_num);
        $data['total_page'] = $total_page;

        //名称 项目 路段 原因 发生原因 状态
        $data['list'] = $obj->getListByPage($company_id,$status,$fault_id,$start_date,$end_date,$page,$page_num);

        // 返回所有处理人
        $user_id = $this->getLoginUserId();
        $manager_obj = new SunnyManager();
        $manager_info = $manager_obj->getInfoById($user_id);
        $company_id = $manager_info? $manager_info['company_id']:0;

        $manager_list = $manager_obj->getListByCompanyId($company_id,'id,email,username') ;
        if($manager_list){
            foreach($manager_list as $k=>$v){
                $manager_list[$k]['email'] = $v['username']? $v['email'].'['.$v['username'].']':$v['email'];
            }
        }
        $data['manager_list'] = $manager_list;

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 设备删除
    public function actionDeviceDel(){

        $device_id = $this->postParam('device_id');
        $device_obj = new SunnyDevice() ;
        $update_data['is_deleted'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');

        $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 项目删除
    public function actionProjectDel(){
        $project_id = $this->postParam('project_id');
        $project_obj = new SunnyProject() ;
        $update_data['is_deleted'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');

        $project_obj->baseUpdate($project_obj::tableName(),$update_data,'id=:id',[':id'=>$project_id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    public function actionDeviceSave(){
        $device_id = $this->postParam('device_id');
        $qr_code = $this->postParam('qr_code');
        $device_name = $this->postParam('device_name');
        $road_id = $this->postParam('road_id');

        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($device_id);
        if(!$device_info){
            //  当前设备信息不存在
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        if($device_info['qr_code'] !=$qr_code){
            // 判断是否重复
            $qr_code_info = $device_obj->getInfoByQrcode($qr_code);
            if($qr_code_info){
                return $this->returnJson(['code'=>100092,'msg'=>getErrorDictMsg(100092),'data'=>[]]);
            }
        }

        $update_data['qr_code'] = $qr_code ;
        $update_data['device_name'] = $device_name ;
        $update_data['road_id'] = $road_id ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    /**
     * 蓄电池工矿
     */
    public function actionBatteryWorking(){
        $device_id = $this->getParam('device_id');
        $start_date = $this->getParam('date');
        $start_date = $start_date ? $start_date:date('Y-m-d');
        $date = $start_date ;
        $start_time = date('Y-m-d 00:00:00',strtotime($start_date));
        // 结束日期
        $end_date = $this->getParam('end_date');
        $end_date = $end_date ? $end_date:$date;
        $end_time  = $end_date== date('Y-m-d') ? date('Y-m-d H:i:s') :date('Y-m-d 23:59:59',strtotime($end_date));

        $time_type = $this->getParam('time_type') ;
        $time_type = strtoupper($time_type);
        $time_type = $time_type ? $time_type :'HALF_HOUR';
        // MINUTE-分钟 FIVE_MINUTE-五分钟 TEN_MINUTE-10分钟 HALF_HOUR-半小时 HOUR-小时
        $total_obj = new SunnyDeviceTotal();
        $params['cond'] = 'device_id =:device_id AND time_type =:time_type AND  timestamp >=:start_time AND timestamp <=:end_time' ;
        $params['args'] =[':device_id'=>$device_id,':time_type'=>$time_type,':start_time'=>strtotime($start_time),':end_time'=>strtotime($end_time)];
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

        $step_second = 1800 ;
        //// MINUTE-分钟 FIVE_MINUTE-五分钟 TEN_MINUTE-10分钟 HALF_HOUR-半小时 HOUR-小时
        $step_second_arr = ['MINUTE'=>60,'FIVE_MINUTE'=>300,'TEN_MINUTE'=>600,'HALF_HOUR'=>1800,'HOUR'=>3600];
        $step_second = isset($step_second_arr[$time_type]) ?$step_second_arr[$time_type] : $step_second ;

        $is_empty = true ;
        $prev_info = [] ;
        for($i=$start_time_timestamp;$i<=$end_time_timestamp;$i = $i+$step_second){
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
                $is_empty = false ;

                $time_list[] = date('H:i',$i);
                $prev_info  = $v ;

            }else{
                if(!$is_empty && $prev_info){
                    // 保证延续性
                    $load_dc_power[] = is_null($prev_info['load_dc_power'])?'0':$prev_info['load_dc_power'];
                    $charging_current[] = is_null($prev_info['charging_current'])?'0':$prev_info['charging_current'];
                    $cumulative_charge[] = is_null($prev_info['cumulative_charge'])?'0':$prev_info['cumulative_charge'];
                    $brightness[] = is_null($prev_info['brightness'])?'0':$prev_info['brightness'];

                    $battery_charging_current[] = is_null($prev_info['battery_charging_current'])?'0':$prev_info['battery_charging_current'];

                    $battery_voltage[] = is_null($prev_info['battery_voltage'])?'0':$prev_info['battery_voltage'];
                    $battery_temperature[] = is_null($prev_info['battery_temperature'])?'0':$prev_info['battery_temperature'];

                    $battery_panel_charging_voltage[] = is_null($prev_info['battery_panel_charging_voltage'])?'0':$prev_info['battery_panel_charging_voltage'];
                    $battery_panel_charging_current[] = is_null($prev_info['battery_panel_charging_current'])?'0':$prev_info['battery_panel_charging_current'];
                    $charging_power[] = is_null($prev_info['charging_power'])?'0':$prev_info['charging_power'];
                    $time_list[] = date('H:i',$i);
                }

            }

        }

        $battery_list['battery_voltage'] = $battery_voltage ;
        $battery_list['battery_charging_current'] = $battery_charging_current ;
        $battery_list['battery_temperature'] = $battery_temperature ;
        $data['battery_list'] = $battery_list ;

        $light_list['load_dc_power'] = $load_dc_power ;
        $light_list['charging_current'] = $charging_current ;
        $light_list['cumulative_charge'] = $cumulative_charge ;
        $light_list['brightness'] = $brightness ;
        $data['light_list'] = $light_list ;


        $diangliang_list['charging_current'] = $charging_current ;
        $diangliang_list['battery_charging_current'] = $battery_charging_current ;
        $data['diangliang_list'] = $diangliang_list ;

        $panel_list['battery_panel_charging_voltage'] = $battery_panel_charging_voltage ;
        $panel_list['battery_panel_charging_current'] = $battery_panel_charging_current ;
        $panel_list['charging_power'] = $charging_power ;
        $data['panel_list'] = $panel_list ;

        $data['time_list'] = $time_list ;

        // 最大电压
        $pos = array_search(max($battery_voltage), $battery_voltage);
        $battery_max_voltage =  $battery_voltage[$pos];

        // 最小电压
        $pos = array_search(min($battery_voltage), $battery_voltage);
        $battery_min_voltage =  $battery_voltage[$pos];
        // 最大充电电流
        $pos = array_search(max($battery_charging_current), $battery_charging_current);
        $battery_max_charging_current =  $battery_charging_current[$pos];
        // 最高温度
        $pos = array_search(max($battery_temperature), $battery_temperature);
        $battery_max_temperature =  $battery_temperature[$pos];
        // 最低温度
        $pos = array_search(min($battery_temperature), $battery_temperature);
        $battery_min_temperature =  $battery_temperature[$pos];

        $today_obj  = new SunnyDeviceStatusToday();
        $today_info = $today_obj->getInfoByDateAndDeviceId($device_id,$date);

        $data['battery_max_voltage'] = $battery_max_voltage ;
        $data['battery_min_voltage'] = $battery_min_voltage ;
        $data['battery_max_charging_current'] = $battery_max_charging_current ;
        $data['battery_max_temperature'] = $battery_max_temperature ;
        $data['battery_min_temperature'] = $battery_min_temperature ;


        $data['battery_max_voltage'] = $today_info?$today_info['bat_max_volt_today']:0 ;
        $data['battery_min_voltage'] =$today_info?$today_info['bat_min_volt_today']:0 ;
        $data['battery_max_temperature'] = $today_info?$today_info['bat_highest_temper']:0 ;
        $data['battery_min_temperature'] = $today_info?$today_info['bat_lowest_temper']:0 ;

        // 电池板 最大电压 最大电流 最大功率
        $pos = array_search(max($battery_panel_charging_voltage), $battery_panel_charging_voltage);
        $battery_panel_max_voltage =  $battery_panel_charging_voltage[$pos];
        $pos = array_search(max($battery_panel_charging_current), $battery_panel_charging_current);
        $battery_panel_max_charging_current =  $battery_panel_charging_current[$pos];
        $pos = array_search(max($charging_power), $charging_power);
        $battery_panel_max_charging_power =  $charging_power[$pos];
        $data['battery_panel_max_voltage']= $battery_panel_max_voltage ;
        $data['battery_panel_max_charging_current']= $battery_panel_max_charging_current ;
        $data['battery_panel_max_charging_power']= $battery_panel_max_charging_power ;


        // 路灯 最大电压 最大电流 最大功率 亮度
        // $load_dc_power ; $charging_current $cumulative_charge $brightness
        $pos = array_search(max($load_dc_power), $load_dc_power);
        $light_max_voltage =  $load_dc_power[$pos];
        $pos = array_search(max($charging_current), $charging_current);
        $light_max_charging_current =  $charging_current[$pos];
        $pos = array_search(max($cumulative_charge), $cumulative_charge);
        $light_max_cumulative_charge =  $cumulative_charge[$pos];
        $pos = array_search(max($brightness), $brightness);
        $light_max_brightness =  $brightness[$pos];
        $data['light_max_voltage']= $light_max_voltage ;
        $data['light_max_charging_current']= $light_max_charging_current ;
        $data['light_max_cumulative_charge']= $light_max_cumulative_charge ;
        $data['light_max_brightness']= $light_max_brightness ;


        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 获取模板列表
     */
    public function actionGetTemplateList(){
        $type = $this->getParam('type');
        $type = $type ? $type :'BATTERY';
        $params['cond'] = 'type=:type AND is_deleted=:is_deleted';
        $params['args'] = [':type'=>$type,':is_deleted'=>'N'];
        $obj = new SunnyDeviceTemplate();
        $params['fields'] = 'id,name';
        $list= $obj->findAllByWhere($obj::tableName(),$params,$obj::getDb());
        $data['list'] = $list ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 获取蓄电池参数模板信息
    public function actionGetBatteryTemplateDetail(){

        $id = $this->getParam('id') ;
        $obj = new SunnyDeviceTemplate();
        $info = $obj->getDetailInfoById($id);
        $data['info'] = $info ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 获取负载模板信息
    public function actionGetLoadTemplateDetail(){

        $id = $this->getParam('id') ;
        $obj = new SunnyDeviceTemplate();
        $info = $obj->getDetailInfoById($id);
        $data['info'] = $info ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 获取具体设备蓄电池参数信息
    public function actionGetDeviceBatteryParams(){
        $device_id = $this->getParam('device_id');
        $device_obj = new SunnyDevice();
        $device_info  = $device_obj->getInfoById($device_id);
        if(!$device_info){
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        $params_obj = new  SunnyDeviceBatteryParams();
        $params_info = $params_obj->getInfoByDeviceId($device_id) ;
        if(!$params_info){
            return  $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        $info = $params_info;
        $info['battery_type'] = $device_info['battery_type'];
        $data['info'] = $info ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);

    }

    // 获取设别负载
    public function actionGetDeviceLoad(){
        $device_id = $this->getParam('device_id');
        $device_obj = new SunnyDevice();
        $device_info  = $device_obj->getInfoById($device_id);
        if(!$device_info){
            return $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        $params_obj = new  SunnyDeviceBatteryParams();
        $params_info = $params_obj->getInfoByDeviceId($device_id) ;
        if(!$params_info){
            return  $this->returnJson(['code'=>100075,'msg'=>getErrorDictMsg(100075),'data'=>[]]);
        }

        $info = $params_info;
        $info['battery_type'] = $device_info['battery_type'];
        $info['battery_rate_volt'] = $device_info['battery_rate_volt'];
        $info['led_current_set'] = $device_info['led_current_set'];
        $info['auto_power_set'] = $device_info['auto_power_set'];
        $data['info'] = $info ;


        $obj = new SunnyDeviceLoadTime();
        $time_list = $obj->getListByDeviceId($device_id);
        //minutes
        $minutes = [];
        $load_sensor_on_power = [] ;
        if($time_list){
            foreach($time_list as $v){
                $minutes[$v['time_end']] = $v['minutes'] ;
                $load_sensor_on_power[$v['time_end']] = $v['load_sensor_on_power'] ;
            }
        }
        $data['minutes'] =  $minutes ;
        $data['load_sensor_on_power'] =  $load_sensor_on_power ;
        //load_sensor_on_power
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    public function actionWorkOrderList(){

        $status = $this->postParam('status') ;
        $page = $this->postParam('page');
        $page = $page > 0 ? $page : 1 ;

        $obj = new SunnyDeviceFault();
        $page_num = $this->page_rows;
        $data['page_num'] = $page_num ;
        $user_id = $this->getLoginUserId() ;

        // 公司ID
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($user_id);

        $total_num = $obj->getTotalNum($company_id,$status,0,'','',true);
        $total_page = ceil($total_num/$page_num);
        $data['total_page'] = $total_page;

        //名称 项目 路段 原因 发生原因 状态
        $data['list'] = $obj->getListByPage($company_id,$status,'','','',$page,$page_num,true);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 创建工单
    public function actionCreateWorkOrder(){
        $id = $this->postParam('id');
        $note = $this->postParam('note');
        $manager_id = $this->postParam('manager_id');
        $future_deal_date= $this->postParam('future_deal_date');
        $image_url = $_POST['image_url'];
        $image_url = serialize($image_url);
$sunny_log = new SunnyLog();
        $log = $_POST;
        $log['type'] = 'actionCreateWorkOrder';
        $sunny_log->addLog(json_encode($log));

        $obj = new SunnyDeviceFault();
        $update_data['is_work_order'] = 'Y';
        $update_data['status'] = 'DEALING';
        $update_data['image_url'] = $image_url;
        $update_data['note'] = $note;
        $update_data['manager_id'] = $manager_id;
        $update_data['future_deal_date'] = $future_deal_date;
        $update_data['deal_date'] = '';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 结单
    public function actionDealWorkOrder(){
        $id = $this->postParam('id');
        $note = $this->postParam('note');
        $image_url = $_POST['image_url'];
        $image_url = serialize($image_url);
        $user_id = $this->getLoginUserId();

 $sunny_log = new SunnyLog();
        $log = $_POST;
        $log['type'] = 'actionDealWorkOrder';
        $sunny_log->addLog(json_encode($log));

        $obj = new SunnyDeviceFault();
        $update_data['status'] = 'DEALED';
        $update_data['success_img_url'] = $image_url;
        $update_data['success_note'] = $note;
        $update_data['deal_manager_id'] = $user_id;
        $update_data['deal_date'] = date('Y-m-d H:i:s');
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 获取用户信息
    public function actionGetUserInfo(){
        $user_id = $this->getLoginUserId() ;
        $manager_obj = new SunnyManager();
        $info = $manager_obj->getInfoById($user_id);
        $data['email'] = $info ? $info['email'] : '';
        $data['username'] = $info ? $info['username']:'';
        $data['id'] = $info ? $info['id']:'';
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 设置用户名
    public function actionSetUsername(){
        $user_id = $this->getLoginUserId();
        $username = $this->postParam('username');
        if(!$username){
            return $this->returnJson(['code'=>100093,'msg'=>getErrorDictMsg(100093),'data'=>[]]);
        }

        $manager_obj = new SunnyManager();
        $info = $manager_obj->getInfoById($user_id);
        if($username != $info['username']){
            // 判断用户昵称是否重复
            $exits_info = $manager_obj->getInfoByUsername($username);
            if($exits_info){
                return $this->returnJson(['code'=>100094,'msg'=>getErrorDictMsg(100094),'data'=>[]]);
            }
        }

        $update_data['username'] = $username ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        $manager_obj->baseUpdate($manager_obj::tableName(),$update_data,'id=:id',[':id'=>$user_id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    public function actionSettingOtherSave(){
        $ids = $this->postParam('id');
        $load_obj = new SunnyDeviceLoadTime();
        $res = $load_obj->savePostData($ids,$_POST);
        if(!$res){
            return $this->returnJson($load_obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg('1')]);
    }

    public function actionSettingBatteryParamsSave(){

        $ids = $this->postParam('id');

        $load_obj = new SunnyDeviceBatteryParams();
        $res = $load_obj->savePostData($ids,$_POST);
        if(!$res){
            return $this->returnJson($load_obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg('1')]);
    }

    /**
     * @param string $JSON 数据信息
     * @return string
     * @soap   #最新同步接口
     */
    public function actionGetNsr(){
        $JSON = $this->postParam('json');
        $log_obj = new SunnyLog();
        $log_obj->addLog($JSON);
        $data = json_decode($JSON,true);
        $data = isset($data['Data'][0]) ?$data['Data'][0] : [];
        if(!$data){
            $data = isset($data['DATA'][0]) ?$data['DATA'][0] : [];
        }
        if(!$data){
            $returnData['MSG'] = "E" ;
            $return['DATA'][] = $returnData ;
            return json_encode($return);
        }

        // 获取设备编号
        $deviceNo = $data['DEV'];

        $status_record_obj = new SunnyDeviceStatusRecord();
        $need_sync = false ;
        if(isset($data['0xFD'])){
            // 控制器实时数据
            $res = $status_record_obj->syncRecordFromRegister($deviceNo,$data['0xFD']);

            if(!$res){
                $returnData['MSG'] = "E" ;
                $return['DATA'][] = $returnData ;
                return json_encode($status_record_obj->error_data);
            }

            // 判断是否需要同步
            $task_obj = new SunnyDeviceSyncTask();
            $need_sync = $task_obj->dealNeedSync($deviceNo,$res);
        }else if(isset($data['0x10B'])){

            // 历史数据
            //{"Data":[{"DEV":"HN2021030100002","LGT":"111.3424","LTT":"23.432442","0x10B":"0079007900D0000000180000000000000000000000000003000000000000000000000000000000000000000200000000003D0000000013A10000000000000000000000040000EBD2 "}]}
            $res = $status_record_obj->syncHistoryRecordFromRegister($deviceNo,$data['0x10B']);
            if(!$res){
                $returnData['MSG'] = "E" ;
            }
            $task_obj = new SunnyDeviceSyncTask();
            $need_sync = $task_obj->dealNeedSync($deviceNo,$res);

        }

        if(isset($data['BPS'])){
            $position_arr = explode(',',$data['BPS']);
            $longitude = isset($position_arr[1]) ? $position_arr[1] : '';
            $latitude = isset($position_arr[0]) ? $position_arr[0] : '';
            $longitude = ltrim($longitude,'0');
            $latitude = ltrim($latitude,'0');
            $device_obj = new SunnyDevice();
            $device_obj->updatePosition($deviceNo,$longitude,$latitude);
        }

        // 需要先更新电池相关的
        if(isset($data['0xE003']) || isset($data['0xE00F']) || isset($data['0xE01F'])){
            $battery_params_obj = new SunnyDeviceBatteryParams();
            $e003 = isset($data['0xE003']) ? $data['0xE003'] :'' ;
            $e00f = isset($data['0xE00F']) ? $data['0xE00F'] :'' ;
            $eo1f = isset($data['0xE01F']) ? $data['0xE01F'] :'' ;
            $battery_params_obj->updateDataByUpload($deviceNo,$e003,$e00f,$eo1f);
        }

        $returnData = $status_record_obj->getReturnData($deviceNo,$need_sync);
        $returnData['MSG'] = "S" ;

        $site_config_obj = new SiteConfig();
        $returnData['HOP'] = $site_config_obj->getByKey('init_hop') ;
        $returnData['HIS'] = $site_config_obj->getByKey('init_his') ;

        $return['DATA'][] = $returnData ;

        $log_obj->addLog(json_encode($return));
        return json_encode($return);
    }


}
