<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminApiKey;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\CoinAddressValue;
use backend\models\OkexLedger;
use backend\models\OkexTotalOrder;
use common\components\CoinBalance;
use common\components\PlatformTradeCommonV4;
use common\models\SiteConfig;
use common\models\SmsLog;
use TencentCloud\Cws\V20180312\Models\Site;

/**
 * System
 */
class SmsController extends BackendController
{

    public function actionIndex(){

        $site_config = new SiteConfig();
        $renderData['tencent_sms_secret_id'] = $site_config->getByKey('tencent_sms_secret_id');
        $renderData['tencent_sms_secret_key'] = $site_config->getByKey('tencent_sms_secret_key');
        $renderData['tencent_sms_app_id'] = $site_config->getByKey('tencent_sms_app_id');
        $renderData['tencent_sms_app_sign'] = $site_config->getByKey('tencent_sms_app_sign');
        $renderData['tencent_sms_template_id'] = $site_config->getByKey('tencent_sms_template_id');

        return $this->render('index',$renderData) ;
    }

    public function actionSave(){

        $tencent_sms_secret_id = $this->postParam('tencent_sms_secret_id');
        $tencent_sms_secret_key = $this->postParam('tencent_sms_secret_key');
        $tencent_sms_app_id = $this->postParam('tencent_sms_app_id');
        $tencent_sms_app_sign = $this->postParam('tencent_sms_app_sign');
        $tencent_sms_template_id = $this->postParam('tencent_sms_template_id');

        $site_config = new SiteConfig();
        $site_config->saveByKey('tencent_sms_secret_id',$tencent_sms_secret_id);
        $site_config->saveByKey('tencent_sms_secret_key',$tencent_sms_secret_key);
        $site_config->saveByKey('tencent_sms_app_id',$tencent_sms_app_id);
        $site_config->saveByKey('tencent_sms_app_sign',$tencent_sms_app_sign);
        $site_config->saveByKey('tencent_sms_template_id',$tencent_sms_template_id);
        $url = '/sms/index';
        return $this->redirect($url);
    }

    // 短信发送记录列表
    public function actionRecordList(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $mobile = isset($_GET['mobile']) ? $_GET['mobile'] : '' ;

        if($mobile){
            $params['like_arr']['mobile'] = $mobile;
        }
        $searchArr['mobile'] = $mobile ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['status'] = 'SEND';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new SmsLog() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;


        return $this->render('record-list',$renderData) ;
    }




}
