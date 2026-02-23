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
use common\models\PushTask;
use common\models\SiteConfig;
use common\models\UserPlatform;
use common\models\UserPlatformEarn;
use common\models\UserPointRecord;
use TencentCloud\Cws\V20180312\Models\Site;

/**
 * System
 */
class CoinUserController extends BackendController
{
    public function actionList(){

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

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new UserPlatform() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        return $this->render('list',$renderData) ;
    }

    // 编辑内容
    public function actionEdit(){
        $id = $this->getParam('id');
        $api_key_obj = new UserPlatform() ;
        $info = $api_key_obj->getInfoById($id);
        $render_data['info'] = $info ;
        $this->loadResource('coinUser','actionEdit') ;

        // 查询所有平台
        $site_config = new SiteConfig();
        $bit_all_platform = $site_config->getByKey('bit_all_platform','json');
        $render_data['bit_all_platform'] = $bit_all_platform ;

        $coin_obj = new Coin();
        $coin_list = $coin_obj->getTotalList();
        $render_data['coin_list'] = $coin_list ;
        return $this->render('edit',$render_data);
    }

    public function actionSave(){

        $id = $this->postParam('id');

        $platform = $this->postParam('platform');
        $instrument_id = $this->postParam('instrument_id');
        $api_key = $this->postParam('api_key');
        $api_secret = $this->postParam('api_secret');
        $passphrase = $this->postParam('passphrase');
        $coin = $this->postParam('coin');
        $legal_coin = $this->postParam('legal_coin');
        $buying_points = $this->postParam('buying_points');
        $max_buying_points = $this->postParam('max_buying_points');
        $total_amount = $this->postParam('total_amount');
        $grid_levels = $this->postParam('grid_levels');
        $gap_percent = $this->postParam('gap_percent');

        $add_data = compact('platform','instrument_id','api_key','api_secret','passphrase','coin','legal_coin','buying_points','max_buying_points','total_amount','grid_levels','gap_percent');
        $add_data['modify_time'] = date('Y-m-d H:i:s');

        $model = new UserPlatform();
        if($id){
            $model->baseUpdate($model::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{

            $mobile = $this->postParam('mobile');
            // 通过手机号码查询用户信息
            $member_obj = new  Member();
            $user_info = $member_obj->getInfoByMobile($mobile);
            if(!$user_info){
                return $this->returnJson(['code'=>200024,'msg'=>getErrorDictMsg(200024)]);
            }

            $add_data['user_id'] = $user_info['id'];
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $id = $model->baseInsert($model::tableName(),$add_data);
        }

        $push_task_obj = new PushTask();
        $push_task_obj->addRecord($id,'UPDATE_STRATEGY',time());

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);

    }

    // 执行删除操作
    public function actionDel(){
        $id = $this->getParam('id');
        $model = new UserPlatform();
        $update_data['is_deleted'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 盈利列表
    public function actionEarnList(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '' ;

        if($user_id){
            $params['where_arr']['user_id'] = $user_id;
        }
        $searchArr['user_id'] = $user_id ;

        $start_time = $this->getParam('start_time') ;
        if($start_time){
            $params['greater_where_arr']['create_time'] = $start_time;
        }
        $searchArr['start_time'] = $start_time ;

        $end_time = $this->getParam('end_time') ;
        if($end_time){
            $params['lesser_where_arr']['create_time'] = $end_time;
        }
        $searchArr['end_time'] = $end_time ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new UserPlatformEarn() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['return_field'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        //计算盈利总和
        $params['return_type'] = 'row';
        $params['return_field'] = 'sum(total) as sum_total';
        $earn_info = $model->findByWhere($model::tableName(),$params,$model::getDb());
        $renderData['earn_info'] = $earn_info;

        $this->loadResource('exchange','actionAddGoods');
        return $this->render('earn_list',$renderData) ;

    }

    // 充值列表
    public function actionChargeList(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $hash = isset($_GET['hash']) ? $_GET['hash'] : '' ;

        if($hash){
            $params['like_arr']['hash'] = $hash;
        }
        $searchArr['hash'] = $hash ;

        $start_time = $this->getParam('start_time') ;
        if($start_time){
            $params['greater_where_arr']['timestamp'] = strtotime($start_time);
        }
        $searchArr['start_time'] = $start_time ;

        $end_time = $this->getParam('end_time') ;
        if($end_time){
            $params['lesser_where_arr']['timestamp'] = strtotime($end_time);
        }
        $searchArr['end_time'] = $end_time ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new UserPointRecord() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['return_field'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        $this->loadResource('exchange','actionAddGoods');
        return $this->render('charge_list',$renderData) ;
    }

}
