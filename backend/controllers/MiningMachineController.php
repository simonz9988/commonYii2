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

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class MiningMachineController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $coin = isset($_GET['coin']) ? $_GET['coin'] : '' ;

        if($coin){
            $params['where_arr']['coin'] = $coin;
        }
        $searchArr['coin'] = $coin ;

        $activity_id = isset($_GET['activity_id']) ? $_GET['activity_id'] : '' ;

        if($activity_id){
            $params['where_arr']['activity_id'] = $activity_id;
        }
        $searchArr['activity_id'] = $activity_id ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';

        $site_config_obj = new SiteConfig();
        $mining_coin_list = $site_config_obj->getByKey('mining_coin_list','json') ;

        $model = new MiningMachine() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $activity_obj = new MiningMachineActivity() ;
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['coin_name'] = isset($mining_coin_list[$v['coin']]) ? $mining_coin_list[$v['coin']]['name'] : '';
                $list[$k]['activity_name'] = $activity_obj->getNameById($v['activity_id']);
            }
        }
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        $renderData['mining_coin_list'] = $mining_coin_list ;


        $activity_list = $activity_obj->getAll() ;
        $renderData['activity_list'] = $activity_list ;

        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new MiningMachine();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        // 分类列表
        $site_config_obj = new SiteConfig();
        $mining_coin_list = $site_config_obj->getByKey('mining_coin_list','json') ;
        $renderData['mining_coin_list'] = $mining_coin_list ;

        // 获取活动列表
        $activity_obj = new MiningMachineActivity();
        $activity_list = $activity_obj->getAll();
        $renderData['activity_list'] = $activity_list ;

        $this->loadResource('machine','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){

        $id = $this->postParam('id');
        $coin = $this->postParam('coin');
        $title = $this->postParam('title');
        $sub_title = $this->postParam('sub_title');
        $price = $this->postParam('price');
        $activity_id = $this->postParam('activity_id');
        // 库存功能已作废
        //$store_num = $this->postParam('store_num');
        $period = $this->postParam('period');
        $limit_day = $this->postParam('limit_day');
        $is_pre_sell = $this->postParam('is_pre_sell');
        $fee = $this->postParam('fee');
        $cover_img_url = $this->postParam('cover_img_url');
        $content = $this->postParam('content1');
        $seo_title = $this->postParam('seo_title');
        $seo_keywords = $this->postParam('seo_keywords');
        $seo_description = $this->postParam('seo_description');
        $sort = $this->postParam('sort');
        $status = $this->postParam('status');
        $calc_power = $this->postParam('calc_power');
        $content = $this->postParam('content');
        $modify_time =date('Y-m-d H:i:s');
        $add_data = compact('content','activity_id','coin','title','sub_title','price','period','limit_day','is_pre_sell','fee','cover_img_url','content');
        $add_data['seo_title'] = $seo_title ;
        $add_data['seo_keywords'] = $seo_keywords ;
        $add_data['seo_description'] = $seo_description ;
        $add_data['sort'] = $sort ;
        $add_data['status'] = $status ;
        $add_data['modify_time'] = $modify_time ;
        $add_data['calc_power'] = $calc_power ;

        $obj = new MiningMachine();
        if($id){
            $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);
        }else{
            $add_data['create_time']  = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            $obj->baseInsert($obj::tableName(),$add_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MiningMachine();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 活动列表
    public function actionActivityList(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $name = isset($_GET['name']) ? $_GET['name'] : '' ;

        if($name){
            $params['like_arr']['name'] = $name;
        }
        $searchArr['name'] = $name ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';

        $model = new MiningMachineActivity() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        return $this->render('activity_list',$renderData) ;
    }

    // 活动编辑
    public function actionActivityEdit(){
        $id= $this->getParam('id');
        $obj = new MiningMachineActivity();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $this->loadResource('machine','actionActivityEdit');
        return $this->render('activity_edit',$renderData) ;
    }

    // 活动保存
    public function actionActivitySave(){

        $id = $this->postParam('id');
        $name = $this->postParam('name');
        $start_time = $this->postParam('start_time');
        $end_time = $this->postParam('end_time');
        $total = $this->postParam('total');
        $useful_percent = $this->postParam('useful_percent');
        $daily_add = $this->postParam('daily_add');
        $frozen = $this->postParam('frozen');
        $unit_earn = $this->postParam('unit_earn');
        $status = $this->postParam('status');
        $modify_time = date('Y-m-d H:i:s');
        $add_data = compact('name','start_time','end_time','total','useful_percent','daily_add','frozen','unit_earn','status','modify_time');

        $obj = new MiningMachineActivity();

        // 判断当前时间范围内是否有已存在的
        // 允许多个返回内的
        if($obj->getInfoByTime($id,$start_time) || $obj->getInfoByTime($id,$end_time)){
            //return $this->returnJson(['code'=>'200029','msg'=>getErrorDictMsg(200029)]);
        }

        if($id){
            $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['left_total'] = $add_data['total'];
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $id = $obj->baseInsert($obj::tableName(),$add_data);
        }

        // 添加矿力的新增编辑记录
        $log_obj = new MiningMachineActivityLog();
        $log_obj->addLog($id,$add_data);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 活动操作日志列表
    public function actionLogList(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $activity_id = isset($_GET['id']) ? $_GET['id'] : '' ;

        if($activity_id){
            $params['where_arr']['activity_id'] = $activity_id;
        }
        $searchArr['activity_id'] = $activity_id ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';

        $model = new MiningMachineActivityLog() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        return $this->render('log_list',$renderData) ;
    }

    // 活动删除
    public function actionActivityDel(){
        $id = $this->getParam('id');
        $obj = new MiningMachineActivity();
        $del_data['is_deleted'] = 'Y';
        $del_data['modify_time'] = date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$del_data,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 设置内容
    public function actionSetting(){

        $site_config = new SiteConfig();
        // 奖励 可用比例
        // 奖励 冻结比例
        $jiangli_keyong_bili = $site_config->getByKey('jiangli_keyong_bili');
        $jiangli_dongjie_bili = $site_config->getByKey('jiangli_dongjie_bili');
        $renderData['jiangli_keyong_bili'] = $jiangli_keyong_bili ;
        $renderData['jiangli_dongjie_bili'] = $jiangli_dongjie_bili ;

        // 分享奖励
        // 1T  一维度  二维度
        $fenxiang1_total = $site_config->getByKey('fenxiang1_total');
        $fenxiang1_level1 = $site_config->getByKey('fenxiang1_level1');
        $fenxiang1_level2 = $site_config->getByKey('fenxiang1_level2');
        $renderData['fenxiang1_total'] = $fenxiang1_total ;
        $renderData['fenxiang1_level1'] = $fenxiang1_level1 ;
        $renderData['fenxiang1_level2'] = $fenxiang1_level2 ;

        // 4T  一维度  二维度
        $fenxiang2_total = $site_config->getByKey('fenxiang2_total');
        $fenxiang2_level1 = $site_config->getByKey('fenxiang2_level1');
        $fenxiang2_level2 = $site_config->getByKey('fenxiang2_level2');
        $renderData['fenxiang2_total'] = $fenxiang2_total ;
        $renderData['fenxiang2_level1'] = $fenxiang2_level1 ;
        $renderData['fenxiang2_level2'] = $fenxiang2_level2 ;

        //P1 团队业绩100T，新增业绩3%，除去最大市场外，其它市场总业绩需达40T
        //P2 团队业绩1000T，新增业绩5%，除去最大市场外，其它市场总业绩需达400T
        //P3 团队业绩2500T，新增业绩8%，除去最大市场外，其它市场总业绩需达1000T
        $p1_tuandui_yeji = $site_config->getByKey('p1_tuandui_yeji');
        $p1_xinzeng_yeji_bili = $site_config->getByKey('p1_xinzeng_yeji_bili');
        $p1_qita_yeji = $site_config->getByKey('p1_qita_yeji');
        $renderData['p1_tuandui_yeji'] = $p1_tuandui_yeji ;
        $renderData['p1_xinzeng_yeji_bili'] = $p1_xinzeng_yeji_bili ;
        $renderData['p1_qita_yeji'] = $p1_qita_yeji ;

        $p2_tuandui_yeji = $site_config->getByKey('p2_tuandui_yeji');
        $p2_xinzeng_yeji_bili = $site_config->getByKey('p2_xinzeng_yeji_bili');
        $p2_qita_yeji = $site_config->getByKey('p2_qita_yeji');
        $renderData['p2_tuandui_yeji'] = $p2_tuandui_yeji ;
        $renderData['p2_xinzeng_yeji_bili'] = $p2_xinzeng_yeji_bili ;
        $renderData['p2_qita_yeji'] = $p2_qita_yeji ;

        $p3_tuandui_yeji = $site_config->getByKey('p3_tuandui_yeji');
        $p3_xinzeng_yeji_bili = $site_config->getByKey('p3_xinzeng_yeji_bili');
        $p3_qita_yeji = $site_config->getByKey('p3_qita_yeji');
        $renderData['p3_tuandui_yeji'] = $p3_tuandui_yeji ;
        $renderData['p3_xinzeng_yeji_bili'] = $p3_xinzeng_yeji_bili ;
        $renderData['p3_qita_yeji'] = $p3_qita_yeji ;


        $usdt_cash_out_fee = $site_config->getByKey('usdt_cash_out_fee');
        $fil_cash_out_fee = $site_config->getByKey('fil_cash_out_fee');
        $renderData['usdt_cash_out_fee'] = $usdt_cash_out_fee ;
        $renderData['fil_cash_out_fee'] = $fil_cash_out_fee ;

        $this->loadResource('machine','actionSetting') ;
        $renderData['info'] = [];
        return $this->render('setting',$renderData);
    }

    // 保存设置信息
    public function actionSettingSave(){

        $site_config = new SiteConfig();
        $site_config->saveMachineSettingBatch($_POST);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


    /**
     * 发放收益
     */
    public function actionDoSendEarn(){

        $activity_id = $this->postParam('activity_id');
        $unit_earn = $this->postParam('unit_data');
        $daily_add = $this->postParam('daily_add');
        if(!is_numeric($unit_earn)){
            return $this->returnJson(['code'=>'200033','msg'=>getErrorDictMsg(200033)]);
        }

        if(!is_numeric($daily_add)){
            return $this->returnJson(['code'=>'200038','msg'=>getErrorDictMsg(200038)]);
        }

        $now = date('Y-m-d H:i:s');

        // 查询活动信息
        $activity_obj = new MiningMachineActivity();
        $activity_info = $activity_obj->getInfoById($activity_id);

        $useful_total = $activity_info['total']*$activity_info['useful_percent']*0.01;
        if($daily_add + $activity_info['total_supply'] > $useful_total){
            return $this->returnJson(['code'=>'200039','msg'=>getErrorDictMsg(200039)]);
        }

        // 当日已发放
        if($activity_info['send_earn_date'] == date("Ymd")){
            //return $this->returnJson(['code'=>'200036','msg'=>getErrorDictMsg(200036)]);
        }

        $start_time =$activity_info['start_time'] ;
        $end_time =$activity_info['end_time'] ;
        $end_time =date("Y-m-d 23:59:59" ,strtotime($end_time)+86400);

        if($now < $start_time || $now > $end_time){
            return $this->returnJson(['code'=>'200034','msg'=>getErrorDictMsg(200034)]);
        }

        if($activity_info['status'] !='ENABLED'){
            return $this->returnJson(['code'=>'200035','msg'=>getErrorDictMsg(200035)]);
        }

        $activity_info['unit_earn'] = $unit_earn ;
        $activity_info['daily_add'] = $daily_add ;
        $log_obj = new MiningMachineActivityLog();
        $log_obj->addLog($activity_id,$activity_info);

        $earn_obj = new MiningMachineEarn();
        $res =$earn_obj->addByAdmin($unit_earn,$daily_add,$activity_id);
        //if(!$res){
            //return $this->returnJson(['code'=>'200037','msg'=>getErrorDictMsg(200037)]);
        //}
        return $this->returnJson(['code'=>'1','msg'=>getErrorDictMsg(1)]);
    }
}
