<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminRolePrivilege;
use backend\models\AdminTotalApiKey;
use backend\models\AdminUserApiKey;
use common\components\ExportFile;
use common\components\GoogleAuthenticator;
use common\components\PHPGangsta_GoogleAuthenticator;
use common\models\Ad;
use common\models\AdPosition;
use common\models\Areas;
use common\models\Article;
use common\models\CashIn;
use common\models\CashOut;
use common\models\EmailCode;
use common\models\MiningMachine;
use common\models\MiningMachineActivity;
use common\models\MiningMachineActivityLog;
use common\models\MiningMachineEarn;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;



/**
 * Cash
 */
class RobotController extends BackendController
{

    // 设置内容
    public function actionSetting(){

        $site_config = new SiteConfig();
        // 奖励 可用比例
        // 奖励 冻结比例
        $robot_register_send_integral = $site_config->getByKey('robot_register_send_integral');
        $robot_usdt_integral_percent = $site_config->getByKey('robot_usdt_integral_percent');
        $renderData['robot_register_send_integral'] = $robot_register_send_integral ;
        $renderData['robot_usdt_integral_percent'] = $robot_usdt_integral_percent ;
        $this->loadResource('robot','actionSetting') ;
        $renderData['info'] = [];
        return $this->render('setting',$renderData);
    }

    // 保存设置信息
    public function actionSettingSave(){

        $site_config = new SiteConfig();
        $site_config->saveRobotSettingBatch($_POST);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }



}
