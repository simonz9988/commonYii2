<?php
namespace sunny\controllers;



use common\models\SiteConfig;
use common\models\SunnyDevice;
use common\models\SunnyDeviceBatteryParams;
use common\models\SunnyDeviceDetail;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceSyncTask;
use common\models\SunnyLog;
use common\models\SunnyProject;
use TencentCloud\Cms\V20190321\Models\Device;

class ApiController extends \common\controllers\BaseController {

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'sr'   => 'mongosoft\soapserver\Action',
            'nsr' => 'mongosoft\soapserver\Action',

        ];
    }


    /**
     * @param string $JSON 数据信息
     * @return string
     * @soap   #最新同步接口
     */
    public function getNsr($JSON){

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
            $need_sync = $task_obj->dealNeedSync($deviceNo,$res,true);
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
        $device_obj = new SunnyDevice();
        if(isset($data['BPS'])){
            $position_arr = explode(',',$data['BPS']);
            $longitude = isset($position_arr[1]) ? $position_arr[1] : '';
            $latitude = isset($position_arr[0]) ? $position_arr[0] : '';
            $longitude = ltrim($longitude,'0');
            $latitude = ltrim($latitude,'0');

            $device_obj->updatePosition($deviceNo,$longitude,$latitude);
        }

        // 返回是否需要下载配置
        $task_obj = new SunnyDeviceSyncTask();
        $res = $task_obj->checkBatteryTaskExists($deviceNo);

        if(isset($data['0xE003']) || isset($data['0xE00F']) || isset($data['0xE01F'])){
            $battery_params_obj = new SunnyDeviceBatteryParams();
            $e003 = isset($data['0xE003']) ? $data['0xE003'] :'' ;
            $e00f = isset($data['0xE00F']) ? $data['0xE00F'] :'' ;
            $eo1f = isset($data['0xE01F']) ? $data['0xE01F'] :'' ;
            $battery_params_obj->updateDataByUpload($deviceNo,$e003,$e00f,$eo1f);

            // 直接更新掉对应的任务
            $task_obj->deleteBatteryTask($deviceNo);
        }

        // p07区{"Data":[{"DEV":"HN2021060300003","0xE08D":"000F0002001400000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000005A005A00010001
        if(isset($data['0xE08D'])){
            $battery_params_obj = new SunnyDeviceBatteryParams();
            $e08d  = $data['0xE08D'] ;
            $battery_params_obj->updateP07DataByUpload($deviceNo,$e08d);
            // 直接更新掉对应的任务
            $task_obj->deleteBatteryTask($deviceNo);
        }

        $returnData = $status_record_obj->getReturnData($deviceNo,$need_sync);
        $returnData['MSG'] = "S" ;

        $project_obj = new SunnyProject();
        $device_info = $device_obj->getInfoByQrcode($deviceNo);
        $project_id = $device_info ? $device_info['project_id']:0;
        $project_info = $project_obj->getInfoById($project_id);
        $project_init_hop = $project_info && !is_null($project_info['init_hop']) ? $project_info['init_hop']:'';
        $project_init_his = $project_info && !is_null($project_info['init_his']) ? $project_info['init_his']:'';
        $site_config_obj = new SiteConfig();
        $returnData['HOP'] = $project_init_hop ? $project_init_hop: $site_config_obj->getByKey('init_hop') ;
        $returnData['HIS'] = $project_init_his ? $project_init_his :$site_config_obj->getByKey('init_his') ;
        $returnData['SYNC'] = $res ? "1" :"0"  ;
        $return['DATA'][] = $returnData ;

        // 直接更新掉对应的任务 ---只要响应就算任务完成
        $task_obj->deleteBatteryTask($deviceNo);

        $log_obj->addLog(json_encode($return));
        return json_encode($return);
    }


    /**
     * @param string $JSON 数据信息
     * @return string
     * @soap   #最新同步接口
     */
    public function getSr($JSON){

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
            $need_sync = $task_obj->dealNeedSync($deviceNo,$res,true);
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
        //{"Data":[{"DEV":"HN2021052000002",
        //"0xE003":"000C000A009B008C00920090008A0084007D00780072",
        //"0xE00F":"0000000A009B008C008C000A000800080007000700000000007300005E8C",
        //"0xE01F":"000C"}]}

        // 返回是否需要下载配置
        $task_obj = new SunnyDeviceSyncTask();
        $res = $task_obj->checkBatteryTaskExists($deviceNo);

        if(isset($data['0xE003']) || isset($data['0xE00F']) || isset($data['0xE01F'])){
            $battery_params_obj = new SunnyDeviceBatteryParams();
            $e003 = isset($data['0xE003']) ? $data['0xE003'] :'' ;
            $e00f = isset($data['0xE00F']) ? $data['0xE00F'] :'' ;
            $eo1f = isset($data['0xE01F']) ? $data['0xE01F'] :'' ;
            $battery_params_obj->updateDataByUpload($deviceNo,$e003,$e00f,$eo1f);

            // 直接更新掉对应的任务
            $task_obj->deleteBatteryTask($deviceNo);
        }

        // p07区{"Data":[{"DEV":"HN2021060300003","0xE08D":"000F0002001400000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000005A005A00010001
        if(isset($data['0xE08D'])){
            $battery_params_obj = new SunnyDeviceBatteryParams();
            $e08d  = $data['0xE08D'] ;
            $battery_params_obj->updateP07DataByUpload($deviceNo,$e08d);
            // 直接更新掉对应的任务
            $task_obj->deleteBatteryTask($deviceNo);
        }

        $returnData = $status_record_obj->getReturnData($deviceNo,$need_sync);
        $returnData['MSG'] = "S" ;

        $site_config_obj = new SiteConfig();
        $returnData['HOP'] = $site_config_obj->getByKey('init_hop') ;
        $returnData['HIS'] = $site_config_obj->getByKey('init_his') ;
        $returnData['SYNC'] = $res ? "1" :"0"  ;
        $return['DATA'][] = $returnData ;

        $log_obj->addLog(json_encode($return));
        return json_encode($return);
    }

}