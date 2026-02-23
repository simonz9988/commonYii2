<?php
namespace console\controllers;
use common\components\CommonLogger;
use common\components\EthScan;
use common\components\SpotApi;
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
class BitCmdController extends CmdBaseController
{

    public $start_date = '';
    public $end_date = '';

    public function init()
    {


    }




    //同步价格信息 5分钟同步一次  php yii /bit-cmd/okex-coin-ticker
    public function actionOkexCoinTicker(){

        $obj = new OkexCoinTicker();
        $res = $obj->syncPrice();
        if($res){
            echo  'success';
        }else{
            echo 'failed';
        }
    }

    // 同步所使用的地址对应的账户的余额信息 每分钟同步一次
    public function actionSyncBalance(){

        $wallet_obj = new UserWallet();
        $wallet_obj->syncBalance();
        echo 'success';
    }

}
