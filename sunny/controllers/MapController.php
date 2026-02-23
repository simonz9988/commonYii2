<?php
namespace sunny\controllers;
use common\models\AdminMappingCompany;
use common\models\SunnyDevice;
use common\models\SunnyDeviceStatusInfo;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceStatusTotal;
use common\models\SunnyDeviceSyncTask;
use common\models\SunnyManager;
use common\models\SunnyProject;
use common\models\SunnyRoad;
use Yii;

// 地图需要返回的数据信息
class MapController extends \common\controllers\BaseController {


    // 返回所有的项目汇总信息
    public function actionProjectTotal(){

        // 管理员的用户ID
        $admin_user_id = $this->getParam('admin_user_id');
        $page = $this->getParam('page') ;
        $page = $page > 0 ? $page:1 ;
        // 获取所有的公司列表
        $company_obj = new AdminMappingCompany();
        $company_ids = $company_obj->getCompanyIdsByAdminId($admin_user_id);

        $project_obj = new  SunnyProject() ;
        // 每页数目
        $page_num = 20 ;
        $data['page_num'] = $page_num ;
        // 项目总数目
        $total_num = $project_obj->getTotalNumByCompanyIds($company_ids);

        $data['total_num'] = $total_num ;
        // 项目总页码
        $total_page = ceil($total_num/$page_num);
        $data['total_page'] = $total_page ;
        $list = $project_obj->getListByCompanyIds($company_ids,$page,$page_num);
        $data['list'] = $list ;

        // 查询汇总信息 路段总数、总安装数量、在线数、总发电量、总用电量
        $road_obj = new SunnyRoad();
        $data['total_road_num'] = $road_obj->getTotalNumByCompanyIds($company_ids);
        // 总安装数
        $device_obj = new SunnyDevice();
        $data['total_device_num'] = $device_obj->getTotalNumByCompanyIds($company_ids);
        // 在线数
        $data['total_online_num'] = $device_obj->getTotalOnlineNumByCompanyIds($company_ids);
        $total_obj = new SunnyDeviceStatusTotal();
        // 总发电量
        $data['total_generate_energy'] = $total_obj->getTotalGenerateEnergyByCompanyIds($company_ids);
        // 总用电量
        $data['total_used_energy'] = $total_obj->getTotalUsedEnergyByCompanyIds($company_ids);

        //设备总数、负载开数量、负载关数量、告警数量、离线数量
        $data['total_device_num'] = $device_obj->getTotalNumByCompanyIds($company_ids);
        $data['total_switch_on_num'] = $device_obj->getTotalSwitchOnNumByCompanyIds($company_ids);
        $data['total_switch_off_num'] = $device_obj->getTotalSwitchOffNumByCompanyIds($company_ids);
        $data['total_fault_num'] = $device_obj->getTotalFaultNumByCompanyIds($company_ids);
        $data['total_offline_num'] = $device_obj->getTotalOfflineNumByCompanyIds($company_ids);

        //社会贡献
        //节约标准煤、减少CO₂、减少SO₂
        $data['save_coal'] = 0 ;
        $data['save_co2'] = 0 ;
        $data['save_so2'] = 0 ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 获取项目列表
    public function actionProjectList(){
        // 管理员的用户ID
        $admin_user_id = $this->getParam('admin_user_id');
        $name = $this->getParam('name');
        $page = $this->getParam('page') ;
        $page = $page > 0 ? $page:1 ;
        // 获取所有的公司列表
        $company_obj = new AdminMappingCompany();
        $company_ids = $company_obj->getCompanyIdsByAdminId($admin_user_id);

        $project_obj = new  SunnyProject() ;
        // 每页数目
        $page_num = 20 ;
        $data['page_num'] = $page_num ;
        // 项目总数目
        $total_num = $project_obj->getTotalNumByCompanyIds($company_ids,$name);

        $data['total_num'] = $total_num ;
        // 项目总页码
        $total_page = ceil($total_num/$page_num);
        $data['total_page'] = $total_page ;
        $list = $project_obj->getListByCompanyIds($company_ids,$page,$page_num,$name);
        $data['list'] = $list ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 获取项目具体信息
     */
    public function actionGetInfoByProject(){

        $project_id = $this->getParam('project_id');

        $project_obj = new SunnyProject();
        $project_info = $project_obj->getInfoById($project_id);
        if(!$project_info){
            return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
        }

        // 客户 ID
        $customer_id = $project_info['customer_id'];

        // 公司ID
        $manager_obj = new SunnyManager();
        $company_id = $manager_obj->getCompanyIdById($customer_id);

        //项目时区、项目地址、创建时间、设备总数量、在线数、总发电量、总用电量
        $data['name'] = $project_info['name'] ;
        $data['time_zone'] = $project_info['time_zone'] ;
        $data['country'] = $project_info['country'] ;
        $data['province'] = $project_info['province'] ;
        $data['city'] = $project_info['city'] ;
        $data['area'] = $project_info['area'] ;
        $data['address'] = $project_info['address'] ;
        $data['longitude'] = $project_info['longitude'] ;
        $data['latitude'] = $project_info['latitude'] ;
        $data['create_time'] = $project_info['create_time'] ;

        $sunny_device_obj = new SunnyDevice();
        $total_obj = new SunnyDeviceStatusTotal();
        $data['total_device_num'] = $sunny_device_obj->getTotalNumByProjectId($project_id);
        $data['total_online_num'] = $sunny_device_obj->getTotalOnlineNumByProjectId($project_id);
        $data['total_generate_energy'] = $total_obj->getCustomerTotalGenerateEnergy($company_id,$project_id);
        $data['total_used_energy'] = $total_obj->getCustomerTotalUsedEnergy($company_id,$project_id);

        //亮灯数量、灭灯数量、告警数量、离线数量
        $data['total_switch_on_num'] = $sunny_device_obj->getTotalSwitchOnNumByProjectId($project_id);
        $data['total_switch_off_num'] = $sunny_device_obj->getTotalSwitchOffNumByProjectId($project_id);
        $data['total_fault_num'] = $sunny_device_obj->getTotalFaultNumByProjectId($project_id);
        $data['total_offline_num'] = $sunny_device_obj->getTotalOfflineNumByProjectId($project_id);

        //节约标准煤、减少CO₂、减少SO₂
        $data['save_coal'] = 0 ;
        $data['save_co2'] = 0 ;
        $data['save_so2'] = 0 ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 设备列表
     */
    public function actionDeviceList(){

        // 亮灯/灭灯/离线/警告
        $project_id = $this->getParam('project_id');
        $status = $this->getParam('status');
        $device_obj = new SunnyDevice();
        $total_num =$device_obj->getTotalNumByProjectIdAndFilterStatus($project_id,$status);
        $page_num = 20 ;
        $data['total_page'] = ceil($total_num/$page_num);

        $page= $this->getParam('page');
        $page = $page > 0 ? $page :1 ;
        $data['list'] = $device_obj->getListByProjectIdAndFilterStatus($project_id,$status,$page,$page_num);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    public function actionDeviceInfo(){
        $device_id = $this->getParam('device_id');
        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($device_id);
        if(!$device_info){
            return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
        }

        //联网方式、项目名称、所属路段、PN、经纬度、
        $data['network_type'] = '';
        $project_obj = new SunnyProject();
        $data['project_name'] = $project_obj->getNameById($device_info['project_id']);
        $road_obj = new SunnyRoad();
        $data['road_name'] = $road_obj->getNameById($device_info['road_id']);
        $data['mark_no'] = $device_info['mark_no'];
        $data['device_name'] = $device_info['device_name'];
        $data['qr_code'] = $device_info['qr_code'];
        $data['longitude'] = $device_info['longitude'];
        $data['latitude'] = $device_info['latitude'];

        //太阳能板数据(电压、电流、功率)、
        $device_info_obj = new SunnyDeviceStatusInfo();
        $status_info = $device_info_obj->getInfoByDeviceId($device_id);
        $data['battery_panel_charging_voltage'] = $status_info['battery_panel_charging_voltage'];
        $data['battery_panel_charging_current'] = $status_info['battery_panel_charging_current'];
        $data['charging_power'] = $status_info['charging_power'];


        //蓄电池数据(电压、温度、充电状态)、
        $data['battery_voltage'] = $status_info['battery_voltage'];
        $data['battery_temperature'] = $status_info['battery_temperature'];
        $record_obj = new SunnyDeviceStatusRecord();
        $data['charge_status'] = $record_obj->getChargeStatus($status_info['charge_status']);

        //路灯(电压、电流、功率、亮度)、
        $data['load_dc_power'] = $status_info['load_dc_power'];
        $data['charging_current'] = $status_info['charging_current'];
        $data['cumulative_charge'] = $status_info['cumulative_charge'];
        $data['brightness'] = $device_info['brightness'];
        //更新时间
        $data['modify_time'] = $device_info['modify_time'];
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);

    }

    // 设置开关状态
    public function actionSetSwitchStatus(){

        $data = [];

        $device_id = $this->postParam('device_id');
        $value = $this->postParam('value');
        $brightness = $this->postParam('value_light');

        $obj = new SunnyDevice() ;

        $switch_status  = $value=="ON"?"Y":"N" ;
        $update_data['switch_status'] = $switch_status ;
        if($switch_status =='Y'){

            $brightness = intval($brightness);
            if($brightness >100 ||$brightness <1){
                return $this->returnJson(['code'=>100083,'msg'=>getErrorDictMsg(100083),'data'=>[]]);
            }
            $update_data['brightness'] = $brightness ;
        }else{
            $update_data['brightness'] = 0 ;
        }

        $update_data['modify_time']=  date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$device_id]);

        // 增加同步任务
        $info = $obj->getInfoById($device_id);
        $task_obj = new SunnyDeviceSyncTask();
        $task_obj->addTask($info) ;

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }
}