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
use common\models\PushTask;
use common\models\RobotCashOut;
use common\models\RobotUserBalanceRecord;
use common\models\SiteConfig;

/**
 * System
 */
class RobotBalanceController extends BackendController
{
    public function actionList(){

        $searchArr = array();

        $address= $this->getParam('address');
        if($address){
            $params['like_arr']['address'] = $address ;
        }
        $searchArr['address'] = $address ;

        $batch_num= $this->getParam('batch_num');
        if($batch_num){
            $params['where_arr']['batch_num'] = $batch_num ;
        }
        $searchArr['batch_num'] = $batch_num ;

        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' modify_time desc ';
        $params['where_arr']['is_open'] = 1 ;
        $model = new CoinAddressValue() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

        $renderData['total_value'] = $model->getTotal($batch_num);

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params,  $model::getDb());

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return $this->render('list',$renderData) ;
    }

    public function actionCashOut(){

        $searchArr = array();

        $address= $this->getParam('address');
        if($address){
            $params['like_arr']['address'] = $address ;
        }
        $searchArr['address'] = $address ;


        $status= $this->getParam('status');
        if($status){
            $params['where_arr']['status'] = $status ;
        }
        $searchArr['status'] = $status ;


        $mobile= $this->getParam('mobile');
        $member_obj = new Member();
        if($mobile){

            $user_ids = $member_obj->getUserIdsByMobile($mobile);
            if($user_ids){
                $params['in_where_arr']['user_id'] = $user_ids ;
            }else{
                $params['where_arr']['user_id'] = 0 ;
            }

        }
        $searchArr['mobile'] = $mobile ;

        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id desc ';
        $params['where_arr']['is_deleted'] = 'N' ;
        $model = new RobotCashOut() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        if($list){
            foreach($list as $k=>$v){
                $list[$k]['status_show'] = $model->getStatusName($v['status']);
                $mobile = $member_obj->getMobileByUserId($v['user_id']) ;
                $list[$k]['mobile'] =$mobile;
            }
        }
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params,  $model::getDb());

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;


        $renderData['status_list'] = $model->getStatusList();

        return $this->render('cash-out',$renderData) ;
    }

    // 异步审核通过
    public function actionAjaxAuditCashOut(){
        $audit_status = $this->postParam('audit_status');
        $id = $this->postParam('id') ;
        $status = $audit_status =='Y' ? 'DEALED':'CANCEL';
        $update_data['status'] = $status ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        $model = new RobotCashOut();
        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id]);

        $info = $model->getInfoById($id);
        if($status =='DEALED' && $info['coin'] =='USDT'){
            $push_task_obj = new PushTask();
            $push_task_obj->addRecord($id,'USDT_CASH_OUT',time());

        }
        responseJson(['code'=>1 ,'msg'=>getErrorDictMsg(1)]);
    }

    // 资金操作列表
    public function actionCashRecord(){

        $searchArr = array();

        $type= $this->getParam('type');
        if($type){
            $params['where_arr']['type'] = $type ;
        }
        $searchArr['type'] = $type ;


        $mobile= $this->getParam('mobile');
        $member_obj = new Member();
        if($mobile){

            $user_ids = $member_obj->getUserIdsByMobile($mobile);
            if($user_ids){
                $params['in_where_arr']['user_id'] = $user_ids ;
            }else{
                $params['where_arr']['user_id'] = 0 ;
            }

        }
        $searchArr['mobile'] = $mobile ;

        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id desc ';
        $params['where_arr']['is_deleted'] = 'N' ;
        $model = new RobotUserBalanceRecord() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        if($list){
            foreach($list as $k=>$v){
                $list[$k]['type_show'] = $model->getTypeName($v['type']);
                $mobile = $member_obj->getMobileByUserId($v['user_id']) ;
                $list[$k]['mobile'] =$mobile;
            }
        }
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params,  $model::getDb());

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;


        $renderData['type_list'] = $model->getTotalType();

        return $this->render('cash-record',$renderData) ;
    }

    public function actionEarnList(){

        $searchArr = array();




        $mobile= $this->getParam('mobile');
        $member_obj = new Member();
        if($mobile){

            $user_ids = $member_obj->getUserIdsByMobile($mobile);
            if($user_ids){
                $params['in_where_arr']['user_id'] = $user_ids ;
            }else{
                $params['where_arr']['user_id'] = 0 ;
            }

        }
        $searchArr['mobile'] = $mobile ;

        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id desc ';
        $params['where_arr']['is_deleted'] = 'N' ;
        $cash_out_model = new MiningMachineCashOut() ;
        $model = new MiningMachineEarn();
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $record_obj = new MiningMachineUserBalanceRecord();
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['type_show'] = $record_obj->getTypeName($v['type']);
                $mobile = $member_obj->getMobileByUserId($v['user_id']) ;
                $list[$k]['mobile'] =$mobile;
            }
        }
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params,  $model::getDb());

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;




        return $this->render('earn-list',$renderData) ;
    }
}
