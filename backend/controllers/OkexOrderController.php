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

/**
 * System
 */
class OkexOrderController extends BackendController
{
    public function actionList(){

        $searchArr = array();


        $admin_user_id = $this->getParam('admin_user_id') ;
        if($admin_user_id){
            $params['where_arr']['admin_user_id'] = $admin_user_id;
        }
        $searchArr['admin_user_id'] = $admin_user_id ;

        $type = $this->getParam('type') ;
        if($type){
            $params['where_arr']['type'] = $type;
        }
        $searchArr['type'] = $type ;

        $start_time = $this->getParam('start_time') ;
        if($start_time){
            $params['greater_where_arr']['timestamp'] = $start_time;
        }
        $searchArr['start_time'] = $start_time ;

        $end_time = $this->getParam('end_time') ;
        if($end_time){
            $params['lesser_where_arr']['timestamp'] = $end_time;
        }
        $searchArr['end_time'] = $end_time ;

        $page_num = $this->getParam('page_num','int') ;
        $page_num = $page_num ? $page_num:$this->page_rows ;
        $page_num  = $page_num >3000 ? 3000:$page_num ;
        $searchArr['page_num'] = $page_num ;

        $state = isset($_GET['state']) ? $_GET['state'] : '' ;

        if($state!=''){
            $params['where_arr']['state'] = $state;
        }
        $searchArr['state'] = $state ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' timestamp desc ,id desc ';


        $model = new OkexTotalOrder() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        if($list){
            $api_key_model = new AdminApiKey();
            $key_list_info = [];
            foreach($list as $k=>$v){
                $list[$k]['state_name'] = $model->getStateName($v['state']);
                $list[$k]['type_name'] = $model->getTypeName($v['type']);
                $admin_user_id = $v['admin_user_id'];
                if(isset($key_list_info[$admin_user_id])){
                    $admin_user_info = $key_list_info[$admin_user_id] ;
                }else{
                    $admin_user_info = $api_key_model->getRowByAdminUserId($admin_user_id);
                    $key_list_info[$admin_user_id] = $admin_user_info ;
                }

                if($admin_user_info['base_coin'] =='USDT'){
                    $list[$k]['earn_total']=  $model->calcTotalEarn($v);
                }else{
                    $list[$k]['earn_total']=  $model->calcTotalEarnByCoin($v);
                }

                $list[$k]['admin_user_note']=  $admin_user_info['note'];
            }
        }
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        $renderData['total_state'] = $model->getTotalState();

        $renderData['total_type'] = $model->getTotalType();

        return $this->render('list',$renderData) ;
    }

    public function actionLedgerList(){
        $searchArr = array();


        $admin_user_id = $this->getParam('admin_user_id') ;
        if($admin_user_id){
            $params['where_arr']['admin_user_id'] = $admin_user_id;
        }
        $searchArr['admin_user_id'] = $admin_user_id ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' timestamp desc ,id desc ';


        $model = new OkexLedger() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        if($list){
            foreach($list as $k=>$v){
                $list[$k]['from_name'] = $model->typeName($v['from']);
                $list[$k]['to_name'] = $model->typeName($v['to']);
            }
        }
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;


        return $this->render('ledger_list',$renderData) ;
    }


}
