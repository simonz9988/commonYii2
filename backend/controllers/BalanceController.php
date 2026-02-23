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
use common\models\SiteConfig;

/**
 * System
 */
class BalanceController extends BackendController
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

    public function actionImport(){

        // 读取文件内容
        $adminUserInfo = $this->adminUserInfo ;
        $model = new CoinAddressValue() ;
        $add_data_rst = $model->getImportOrderDataByUser($adminUserInfo['id']);
        $import_list = isset($add_data_rst['data']) ?$add_data_rst['data'] : [];
        $render_data['import_list'] = $import_list ;

        // 获取所有商品列表
        $type_list = $model->getTypeList() ;
        $render_data['type_list'] = $type_list ;

        $goods_id = getParam('goods_id') ;
        $render_data['goods_id'] = $goods_id ;
        $this->loadResource('exchange','actionImport') ;
        return $this->render('import',$render_data);
    }

    public function actionDoImport(){

        $adminUserInfo = $this->adminUserInfo ;
        $model = new CoinAddressValue();
        $add_data_rst = $model->getImportOrderDataByUser($adminUserInfo['id']);
        $import_list = isset($add_data_rst['data']) ?$add_data_rst['data'] : [];

        $type = getParam('type') ;
        $type = strtoupper($type) ;

        $token_detail = $this->getParam('token_detail');
        $token_detail = strtoupper($token_detail) ;
        $batch_num = time();

        $do_list = $model->checkImportBaseInfo($import_list) ;
        $do_import_list = isset($do_list['data']['list']) ? $do_list['data']['list'] : [] ;

        if($do_import_list){

            $component = new CoinBalance() ;
            foreach( $do_import_list as $v){
                if($v['is_allowed']){

                    $add_data['address'] = $v['address'] ;
                    $add_data['type'] = $type;
                    $add_data['value'] = $component->getBalanceByType($v['address'],$type,$token_detail);
                    $add_data['batch_num'] = $batch_num;
                    $add_data['is_open'] = 1;
                    $add_data['create_time'] = date('Y-m-d H:i:s');
                    $add_data['modify_time'] = date('Y-m-d H:i:s');
                    $model->baseInsert($model::tableName(),$add_data);

                }
            }
        }
        $file =  ROOT_PATH.'/upload/import_order/' .$adminUserInfo['id'] . '_import_order.xls';
        //@unlink($file);
        responseJson(['code'=>1 ,'msg'=>getErrorDictMsg(1)]);
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
        $model = new MiningMachineCashOut() ;
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

    public function actionAjaxAuditCashOut(){
        $audit_status = $this->postParam('audit_status');
        $id = $this->postParam('id') ;
        $status = $audit_status =='Y' ? 'DEALED':'CANCEL';
        $update_data['status'] = $status ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        $model = new MiningMachineCashOut();
        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id]);
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
        $model = new MiningMachineUserBalanceRecord() ;
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
