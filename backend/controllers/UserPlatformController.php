<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\CoinAddressValue;
use common\components\CoinBalance;
use common\models\Member;
use common\models\MiningMachineCashOut;
use common\models\MiningMachineEarn;
use common\models\MiningMachineUserBalanceRecord;
use common\models\RobotCashOut;
use common\models\RobotUserBalanceRecord;
use common\models\SiteConfig;
use common\models\UserPlatform;
use common\models\UserPlatformKey;

/**
 * System
 */
class UserPlatformController extends BackendController
{
    public function actionList(){

        $searchArr = array();

        $api_key= $this->getParam('api_key');
        if($api_key){
            $params['api_key']['api_key'] = $api_key;
        }
        $searchArr['api_key'] = $api_key ;

        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' modify_time desc ';
        $params['where_arr']['is_deleted'] = 'N' ;
        $model = new UserPlatformKey() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params,  $model::getDb());

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return $this->render('list',$renderData) ;
    }


}
