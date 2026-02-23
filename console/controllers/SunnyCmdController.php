<?php
namespace console\controllers;
use common\components\CommonLogger;
use common\components\EthScan;
use common\components\MyRedis;
use common\components\SpotApi;
use common\models\CashIn;
use common\models\OkexCoinTicker;
use common\models\OkexSpotOrder;
use common\models\PushTask;
use common\models\SiteConfig;
use common\models\SunnyDevice;
use common\models\SunnyDeviceBatteryParams;
use common\models\SunnyDeviceStatusInfo;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceSyncTask;
use common\models\TxList;
use common\models\UserPlatform;
use common\models\UserPlatformEarn;
use common\models\UserPointRecord;
use yii\db\Expression;
use yii\web\User;


/**
 * CommonCmd controller
 */
class SunnyCmdController extends CmdBaseController
{
    public function init()
    {


    }

    // 测试入口
    public function actionTest(){

        //{"Data":[{"DEV":"HN2021030500009","LGT":"2423424","0xFD":"B200004D00000027007C00BD151500F200CD0031009100AE0000"}]}
        // {"Data":[{"DEV":"HN2021030500009","LGT":"2423424","0xFD":"010362800000000000003A00820000151600BD0000000000F300"}]}
        // {"Data":[{"DEV":"HN2021030500006 ","0xFD":"010362000400000000006400900032161600000000000000F200"}]}
        $obj = new SunnyDeviceStatusRecord();
        //{"Data":[{"DEV":"HN2021030500009","LGT":"111.3424","LTT":"23.432442","0x10B":"0079007900D0000000180000000000000000000000000003000000000000000000000000000000000000000200000000003D0000000013A10000000000000000000000040000EBD2 "}]}
        //HN2021022600090
        $device_no = "HN2021052000002";
        $returnData = $obj->getReturnData($device_no);
        //{"Data":[{"DEV":"HN2021052000002","0x10B":"0074007A002100000003003A0000000000030007000100000000000000020000000100000000000000000002000000000043003E00000BED0404000000000000000010C80000"}]}
        $string = "0074007A002100000003003A0000000000030007000100000000000000020000000100000000000000000002000000000043003E00000BED0404000000000000000010C80000";
        $res = $obj->syncHistoryRecordFromRegister($device_no,$string);
        $task_obj = new SunnyDeviceSyncTask();
        //$need_sync = $task_obj->dealNeedSync($device_no,$res);
        $fault = 62000400;
        //$fault = 62000000;
        $string = "0103".$fault."000000006400900032161600000000000000F200";
        //$string = '0000000100000000005400001616000000000000000200000000';

        $res = $obj->syncRecordFromRegister($device_no,$string);

    }

    public function actionTest2(){
        $deviceNo  = 'HN2021030100001';
        $task_obj = new SunnyDeviceSyncTask();
        $need_sync = $task_obj->dealNeedSync($deviceNo);
        $status_record_obj = new SunnyDeviceStatusRecord();
        $returnData = $status_record_obj->getReturnData($deviceNo,$need_sync);
        var_dump($need_sync,$returnData);exit;
    }

    // 更新地址信息
    public function actionUpdatePosition(){

        $task_obj = new PushTask();
        $task_list = $task_obj->getListByType('UPDATE_POSITION','NOPUSH');
        if(!$task_list){
            echo  'empty';exit;
        }

        $device_obj = new SunnyDevice();
        foreach($task_list as $v){

            $device_obj->resetPosition($v['business_id']);
            $update_data['status'] ='PUSHED';
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $task_obj->baseUpdate($task_obj::tableName(),$update_data,'id=:id',[':id'=>$v['id']]);
        }

        echo 'success';exit;
    }

    public function actionUpdateOffline(){

        $redis_key = "SystemUpdateOffline";
        $redis_obj = new MyRedis() ;
        $start_id = $redis_obj->get($redis_key);
        $start_id = $start_id ? $start_id : 0 ;
        $start_id = 0 ;

        $params['cond'] = 'id > :id';
        $params['args'] = [':id'=>$start_id];

        $device_obj = new SunnyDevice();

        $params['fields'] = 'count(1) as total';
        $total_info = $device_obj->findOneByWhere($device_obj::tableName(),$params,$device_obj::getDb());
        $total_num = $total_info && !is_null($total_info['total']) ? $total_info['total'] : 0 ;
        if(!$total_num){
            return  0 ;
        }

        $limit = 100;
        $total_page = ceil($total_num/$limit);


        $record_obj = new SunnyDeviceStatusRecord();

        for($i=0;$i<$total_page;$i++){
            $params['fields'] = '*';
            $params['page']['curr_page'] = $i ;
            $params['page']['page_num'] = $limit ;
            $list =  $device_obj->findAllByWhere($device_obj::tableName(),$params,$device_obj::getDb());

            $update_data['is_offline'] = 'Y';
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            if($list){
                foreach($list as $v){

                    // 判断是否离线
                    $record_info = $record_obj->getLastedInfoByDeviceId($v['id']) ;

                    if(!$record_info){
                        $update_data['is_offline'] = 'Y';
                        $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$v['id']]);
                    }else{

                        $ext = time() - strtotime($record_info['create_time']) ;

                        if($ext > 60){
                            $update_data['is_offline'] = 'Y';
                            $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$v['id']]);
                        }else{
                            $update_data['is_offline'] = 'N';
                            $device_obj->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$v['id']]);
                        }
                    }
                    $redis_obj->set($redis_key,$v['id'],50);
                }
            }
        }


    }

    // 测试同步信息
    public function actionTest3(){

        $status_record_obj = new SunnyDeviceStatusRecord();
        $deviceNo  = 'HN2021052000002';
        $res = $status_record_obj->syncRecordFromRegister($deviceNo,'E40000000000003600810000202401CF00B00051000300000000');
        $task_obj = new SunnyDeviceSyncTask();
        $need_sync = $task_obj->dealNeedSync($deviceNo,$res);
        var_dump($need_sync);exit;

    }

}
