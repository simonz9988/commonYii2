<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminApiKey;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminTotalApiKey;
use backend\models\CoinAddressValue;
use backend\models\OkexLedger;
use backend\models\OkexTotalOrder;
use backend\models\PlatformTotalOrder;
use common\components\CoinBalance;
use common\components\PlatformTradeCommonV4;
use common\models\Coin;
use common\models\Member;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

/**
 * System
 */
class CashShowController extends \common\controllers\BaseController
{
    public function actionIndex(){

        $this->layout = 'empty';
        $renderData = [];

        return $this->render('index',$renderData) ;
    }



}
