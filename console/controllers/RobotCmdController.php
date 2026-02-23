<?php
namespace console\controllers;
use common\components\CommonLogger;
use common\components\EthScan;
use common\components\SpotApi;
use common\models\Coin;
use common\models\Member;
use common\models\MiningMachineEarn;
use common\models\MiningMachineOrder;
use common\models\OkexCoinTicker;
use common\models\OkexSpotOrder;
use common\models\PushTask;
use common\models\RobotCoinPlatformInfo;
use common\models\RobotOkexSpotOrder;
use common\models\RobotTradeBatch;
use common\models\SiteConfig;
use common\models\TxList;
use common\models\UserPlatform;
use common\models\UserPlatformEarn;
use common\models\UserPointRecord;
use common\models\UserWallet;
use yii\web\User;


/**
 * CommonCmd controller
 */
class RobotCmdController extends CmdBaseController
{

    public function init()
    {}

    // 同步okex所有币种价格信息
    public function actionSyncPriceOkex(){
        $action = $this->getShellAction();
        $this->checkShell($action) ;
        $obj = new RobotCoinPlatformInfo();
        $obj->syncPrice('OKEX');
    }

    // 同步火币所有币种价格信息
    public function actionSyncPriceHuobi(){
        $action = $this->getShellAction();
        $this->checkShell($action) ;

        $obj = new RobotCoinPlatformInfo();
        $obj->syncPrice('HUOBI');

    }

    // 同步所使用的地址对应的账户的余额信息 每分钟同步一次
    public function actionSyncBalance(){

        $wallet_obj = new UserWallet();
        $wallet_obj->syncBalanceFromRobot();
        echo 'success';
    }


    // 同步Okex订单信息 每小时同步一次
    public function actionSyncOkexOrder(){

        $user_platform_obj = new UserPlatform();
        $list = $user_platform_obj->getListByPlatform('OKEX');

        $order_obj = new RobotOkexSpotOrder();

        foreach($list as $v){
            $config['apiKey'] = $v['api_key'] ;
            $config['apiSecret'] = $v['api_secret'] ;
            $config['passphrase'] = $v['passphrase'] ;
            $instrumentId = strtoupper($v['instrument_id']) ;
            $obj = new SpotApi($config);
            $res = $obj->getOrdersList($instrumentId, 2);
            if($res){
                $order_obj->downloadOrder($v['user_id'],$res);
            }
            sleep(3);
        }

    }

    /**
     * 发送自动创建的策略
     */
    public function actionSendAutoStrategy(){

        $push_task_obj = new PushTask();
        $task_list = $push_task_obj->getListByType('UPDATE_STRATEGY','NOPUSH');
        if(!$task_list){
            echo  'Empty';
            exit;
        }

        $user_platform_obj = new UserPlatform();
        foreach($task_list as $v){
            $business_id = $v['business_id'];
            $info = $user_platform_obj->getInfoById($business_id);
            $res = $user_platform_obj->sendByPushTask($info);
            if($res){
                $update_data['status'] = 'PUSHED';
                $update_data['modify_time'] = date('Y-m-d H:i:s');
                $push_task_obj->baseUpdate($push_task_obj::tableName(),$update_data,'id=:id',[':id'=>$v['id']]);
            }
        }
    }


    /**
     * 运行机器人
     */
    public function actionRunRobot(){

        $batch_obj = new RobotTradeBatch();
        $batch_obj->doRunRobot();
    }



}
