<?php
namespace sunny\controllers;



use common\models\SiteConfig;
use common\models\SunnyDevice;
use common\models\SunnyDeviceDetail;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceSyncTask;
use common\models\SunnyLog;
use TencentCloud\Cms\V20190321\Models\Device;

class ApiController extends \common\controllers\BaseController {

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'sr1'   => 'mongosoft\soapserver\Action',
            'sr'   => 'mongosoft\soapserver\Action',
            'sp' => 'mongosoft\soapserver\Action',
            'nsr' => 'mongosoft\soapserver\Action',

        ];
    }



    /**
     * @param string $JSON 数据信息
     * @return string
     * @soap   #同步经纬度信息
     */

    public function getSr1($JSON){



        //{"Data":[{"DEV":"HN2021022800001","SOC":"50","bVL":"12","bCT":"20","TMP":"25","cVL":"20","cCT":"20","cPW":"20","SUM":"20","LST":"20","SST":"20","BRT":"20"}]}
        $data = json_decode($JSON,true);
        $data = isset($data['Data'][0]) ?$data['Data'][0] : [];
        if(!$data){
            $returnData['MSG'] = "E" ;
            $return['DATA'][] = $returnData ;
            return json_encode($return);
        }

        //{"Data":[{"DEV":"1234","SOC":"50","bVL":"12","bCT":"20","TMP":"25","cVL":"20","cCT":"20","cPW":"20","SUM":"20","LST":"20","SST":"Y","BRT":"20"}]}
        $data = json_decode($JSON,true);
        $data = isset($data['Data'][0]) ?$data['Data'][0] : [];
        if(!$data){
            return json_encode(['MSG'=>'E']);
        }

        $deviceNo = $data['DEV'];
        $batteryVolume = $data['SOC'];
        $batteryVoltage = $data['bVL'];
        $batteryChargingCurrent = $data['bCT'];
        $ambientTemperature = $data['TMP'];
        $batteryPanelChargingVoltage = $data['cVL'];
        $chargingCurrent = $data['cCT'];
        $chargingPower = $data['cPW'];
        $cumulativeCharge = $data['SUM'];
        $loadStatus = $data['LST'];
        $switchStatus = $data['SST'];
        $brightness = $data['BRT'];
        $add_data['batteryVolume'] = $batteryVolume ;
        $add_data['batteryVoltage'] = $batteryVoltage ;
        $add_data['batteryChargingCurrent'] = $batteryChargingCurrent ;
        $add_data['ambientTemperature'] = $ambientTemperature ;
        $add_data['batteryPanelChargingVoltage'] = $batteryPanelChargingVoltage ;
        $add_data['chargingCurrent'] = $chargingCurrent ;
        $add_data['chargingPower'] = $chargingPower ;
        $add_data['cumulativeCharge'] = $cumulativeCharge ;
        $add_data['loadStatus'] = $loadStatus ;
        $add_data['switchStatus'] = $switchStatus ;
        $add_data['brightness'] = $brightness ;

        $status_record_obj = new SunnyDeviceStatusRecord();
        $status_record_obj->syncRecord($deviceNo,$add_data);

        $returnData['MSG'] = "S" ;
        $device_detail_obj = new SunnyDevice();
        $returnData["LST"] = $device_detail_obj->getSwitchStatus($deviceNo) ; //负载状态
        $level = $device_detail_obj->getLightLevel($deviceNo);
        $returnData["LVL"] = (string)($level) ; //亮度
        $returnData["HOP"] = "30" ; //心跳间隔
        //{"Data":[{"MSG":"S","LST":"1","BRT":"50","HOP":"30"}]}
        /*
         负载状态       LST
        亮度           LVL
        心跳间隔       HOP
         */
        $return['DATA'][] = $returnData ;
        return json_encode($return);
    }

    /**
     * @param string $JSON
     * @return string
     * @soap   #同步经纬度信息
     */
    public function getSp($JSON){


        $data = json_decode($JSON,true);
        $data = isset($data['Data'][0]) ?$data['Data'][0] : [];
        if(!$data){
            $returnData['MSG'] = "E" ;
            $return['DATA'][] = $returnData ;
            return json_encode($return);
        }

        //{"Data":[{"DEV":"1234","LGT":"2423424","LTT":"23432442"}]}
        $device_detail_obj = new SunnyDevice();
        $deviceNo = $data['DEV'];
        $longitude = $data['LGT'];
        $latitude = $data['LTT'];

        #TODO
        $device_info = $device_detail_obj->getInfoByQrcode($deviceNo);
        $device_detail_obj->setPosition($device_info,$longitude,$latitude);


        $loadStatus = $device_info ? $device_info['switch_status']:'';
        $returnData['MSG'] = "S" ;
        $returnData["LST"] = $loadStatus? $loadStatus:"N"; //负载状态

        $level = $device_detail_obj->getLightLevel($deviceNo);
        $returnData["LVL"] = (string)($level) ; //亮度
        $returnData["HOP"] = "30" ; //心跳间隔
        return json_encode($returnData);
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
            $returnData['MSG'] = "E" ;
            $return['DATA'][] = $returnData ;
            return json_encode($return);
        }

        // 获取设备编号
        $deviceNo = $data['DEV'];

        $status_record_obj = new SunnyDeviceStatusRecord();
        $need_sync = true ;
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

        $returnData = $status_record_obj->getReturnData($deviceNo,$need_sync);
        $returnData['MSG'] = "S" ;

        $site_config_obj = new SiteConfig();
        $returnData['HOP'] = $site_config_obj->getByKey('init_hop') ;
        $returnData['HIS'] = $site_config_obj->getByKey('init_his') ;

        $return['DATA'][] = $returnData ;

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
            $returnData['MSG'] = "E" ;
            $return['DATA'][] = $returnData ;
            return json_encode($return);
        }

        // 获取设备编号
        $deviceNo = $data['DEV'];

        $status_record_obj = new SunnyDeviceStatusRecord();
        $need_sync = true ;
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