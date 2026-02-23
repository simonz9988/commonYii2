<?php
namespace console\controllers;

use backend\models\AdminApiKey;
use backend\models\AdminTotalApiKey;
use backend\models\OkexLedger;
use backend\models\OkexTotalOrder;
use backend\models\PlatformTotalOrder;
use common\components\CommonLogger;
use common\components\MyRedis;
use common\components\PlatformTradeCommon;
use common\components\PlatformTradeCommonV2;
use common\components\PlatformTradeCommonV4;
use common\components\TradeCommon;
use common\components\UsdtTradeCommon;
use common\models\OkexOrder;
use common\models\SiteConfig;
use okv3\SwapApi;
use yii\console\Controller;

/**
 * Cmd controller
 */
class SyncController extends CmdBaseController
{

    // 根据管理员信息进行交易
    public function actionSendOrder(){

        $action = $this->getShellAction();
        $this->checkShell($action) ;

        $obj = new PlatformTotalOrder();
        $obj->sendOrderFromTotalOrder();

        echo 'Success';
    }

    public function actionSendApiKey(){
        $action = $this->getShellAction();
        $this->checkShell($action) ;

        $obj = new AdminTotalApiKey();
        $obj->sendApiKey();
        echo 'Success';
    }


}
