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
use common\models\EmailCode;
use common\models\SiteConfig;
use common\models\SmsLog;
use TencentCloud\Cws\V20180312\Models\Site;

/**
 * System
 */
class EmailController extends BackendController
{



    // 短信发送记录列表
    public function actionRecordList(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $email = isset($_GET['email']) ? $_GET['email'] : '' ;

        if($email){
            $params['like_arr']['email'] = $email;
        }
        $searchArr['email'] = $email ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['status'] = 'SEND';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new EmailCode() ;
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
