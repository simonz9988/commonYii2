<?php
namespace console\controllers;
use common\components\CommonLogger;
use common\components\EthScan;
use common\components\SpotApi;
use common\models\Member;
use common\models\MiningMachineEarn;
use common\models\MiningMachineOrder;
use common\models\OkexCoinTicker;
use common\models\OkexSpotOrder;
use common\models\PushTask;
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
class MachineCmdController extends CmdBaseController
{

    public function init()
    {}

    /**
     *自动取消定的那
     */
    public function actionAutoCancelOrder(){
        $obj = new MiningMachineOrder();
        $obj->autoCancelOrder();
        echo 'success';
    }

    // 创建收益
    public function actionCreateEarn(){
        $earn_obj = new MiningMachineEarn();
        $earn_obj->addByCronTab();
        echo 'success';
    }

    // 同步所使用的地址对应的账户的余额信息 每分钟同步一次
    public function actionSyncBalance(){

        $wallet_obj = new UserWallet();
        $wallet_obj->syncBalance();
        echo 'success';
    }

    public function actionTest(){
        $member_obj = new Member();
        for($user_id=16188;$user_id <=16200;$user_id++){
            $value = mt_rand(50,100) ;
            $blockHash = time().mt_rand(100000,999999) ;
            $res = $member_obj->addMiningMachineCashInRecord($user_id,$value,$blockHash);
            var_dump($res);
        }

    }

    public function actionTest2(){
        $user_id =  16192;
        $member_obj = new Member();
        $value = mt_rand(50,100) ;
        $blockHash = time().mt_rand(100000,999999) ;
        $res = $member_obj->addMiningMachineCashInRecord($user_id,$value,$blockHash);
        var_dump($res);


    }

}
