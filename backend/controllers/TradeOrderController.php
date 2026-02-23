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
use common\models\Member;
use common\models\MiningMachineEarn;
use common\models\MiningMachineFrozenEarn;
use common\models\MiningMachineUserBalance;
use common\models\RobotOrders;
use common\models\SiteConfig;
use common\models\UserWallet;

/**
 * System
 */
class TradeOrderController extends BackendController
{
    public function actionList(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $productname = isset($_GET['productname']) ? $_GET['productname'] : '' ;

        if($productname){
            $params['like_arr']['productname'] = $productname;
        }
        $searchArr['productname'] = $productname ;



        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' createtime desc ';


        $model = new RobotOrders() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $user_list = [] ;
        $member_obj = new Member() ;
        if($list){

            foreach($list as $k=>$v){
                $user_id = $v['id'];
                if(isset($user_list[$user_id])){
                    $user_info = $user_list[$user_id] ;
                }else{
                    $member_obj = new Member() ;
                    $user_info = $member_obj->getUserInfoById($user_id);
                    $user_list[$user_id] = $user_info ;
                }
                $list[$k]['username'] = $user_info['username'] ;
                $list[$k]['mobile'] = $user_info['mobile'] ;
                $list[$k]['email'] = $user_info['email'] ;
                $list[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $list[$k]['paytime'] = date('Y-m-d H:i:s',$v['paytime']);
            }


        }

        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['return_field'] = 'orderid';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        return $this->render('list',$renderData) ;
    }


}
