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
use common\models\Areas;
use common\models\CashIn;
use common\models\CashOut;
use common\models\EmailCode;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class CashController extends BackendController
{
    public function actionIndex(){

    }

    // 入金列表
    public function actionIn(){
        $searchArr = array();

        $order_no = $this->getParam('order_no') ;
        if($order_no){
            $params['like_arr']['order_no'] = $order_no;
        }
        $searchArr['order_no'] = $order_no ;

        $pay_start_time = $this->getParam('pay_start_time');
        if($pay_start_time){
            $params['greater_where_arr']['pay_time'] = $pay_start_time;
        }
        $searchArr['pay_start_time'] = $pay_start_time ;

        $pay_end_time = $this->getParam('pay_end_time');
        if($pay_end_time){
            $params['lesser_where_arr']['pay_time'] = $pay_end_time;
        }
        $searchArr['pay_end_time'] = $pay_end_time ;

        $renderData['searchArr'] =$searchArr ;

        // 查询分页信息
        $page_num = $this->page_rows ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = 'id desc ';
        $params['where_arr']['is_deleted'] = 'N';

        $adminUserInfo = $this->adminUserInfo;
        if($adminUserInfo['username'] != 'admin'){
            $params['where_arr']['admin_user_id'] = $adminUserInfo['id'] ;
        }

        $model = new CashIn() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            $site_config_obj = new SiteConfig();
            $cash_in_fee_percent = $site_config_obj->getByKey('cash_in_fee_percent') /100 ;
            foreach($list as $k=>$v){
                $list[$k]['pay_type'] = $model->getPayTypeName($v['pay_type']);
                $list[$k]['pay_status'] = $model->getPayStatusName($v['pay_status']);
                $list[$k]['fee'] = round($v['amount']*$cash_in_fee_percent,2);
            }
        }

        $is_export = $this->getParam('is_export');
        $is_export = $is_export> 0? $is_export :0 ;
        if($is_export){
            return $this->doExport($list);
        }
        $renderData['list'] = $list ;

        unset($params['page']) ;
        $params['return_field']='id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;
        $site_config = new SiteConfig();
        $renderData['cash_in_min_amount'] = $site_config->getByKey('cash_in_min_amount') ;
        $renderData['is_admin'] = $adminUserInfo['username'] == 'admin' ? true :false ;
        $this->loadResource('cash','actionAddIn') ;
        return $this->render('in',$renderData);
    }

    // 执行导出
    private function doExport($list){

        $export_data[] = [
            '交易单号',
            '名称',
            '创建时间',
            '支付时间',
            '支付人姓名',
            '交易金额',
            '币种',
            '支付方式',
            '支付状态',
            '客户端',
            '备注',
            '手续费',
        ];

        if ($list) {

            foreach ($list as $v) {

                $data[] = $v['order_no'];//申请时间
                $data[] = $v['name'];//活动名称
                $data[] = $v['create_time'];//手机号码
                $data[] = $v['pay_time'];//商品名称
                $data[] = $v['pay_name'];//商品名称
                $data[] = $v['amount'];//商品名称
                $data[] = $v['coin_type'];//商品名称
                $data[] = $v['pay_type'];//商品名称
                $data[] = $v['pay_status'];//商品名称
                $data[] = $v['source'];//商品名称
                $data[] = $v['note'];//商品名称
                $data[] = $v['fee'];//商品名称

                $export_data[] = $data;

                unset($data);
            }
        }

        $export_obj = new ExportFile();
        $file_name = '入金账单-'.date('Y-m-d').'.csv';
        $export_obj->download($export_data,$file_name);

    }

    // 新增编辑入金记录
    public function actionAddIn(){
        $info = array();
        $id = $this->getParam('id');

        $cash_in_model = new CashIn() ;

        if($id) {
            $params['where_arr']['id'] = $id;
            $params['return_type'] = 'row';
            $info = $cash_in_model->findByWhere($cash_in_model::tableName(), $params);
        }
        $renderData['info'] = $info ;

        $site_config = new SiteConfig();
        $cash_in_name = $site_config->getByKey('cash_in_name');
        $name_list = json_decode($cash_in_name,true);
        $renderData['name_list'] = $name_list ;

        $cash_in_coin_type = $site_config->getByKey('cash_in_coin_type');
        $coin_type_list = json_decode($cash_in_coin_type,true);
        $renderData['coin_type_list'] = $coin_type_list ;

        $cash_pay_type = $site_config->getByKey('cash_pay_type');
        $pay_type_list = json_decode($cash_pay_type,true);
        $renderData['pay_type_list'] = $pay_type_list ;

        $cash_pay_status = $site_config->getByKey('cash_pay_status');
        $pay_status_list = json_decode($cash_pay_status,true);
        $renderData['pay_status_list'] = $pay_status_list ;
        $renderData['cash_in_min_amount'] = $site_config->getByKey('cash_in_min_amount') ;

        $this->loadResource('cash','actionAddIn') ;

        return $this->render('add-in',$renderData);
    }

    /**
     * 保存入账记录
     */
    public function actionSaveIn(){

        $id = $this->postParam('id');
        $order_no = $this->postParam('order_no');
        $name = $this->postParam('name');
        $pay_time = $this->postParam('pay_time');
        $pay_name = $this->postParam('pay_name');
        $amount = $this->postParam('amount');
        $coin_type = $this->postParam('coin_type');
        $pay_type = $this->postParam('pay_type');
        $pay_status = $this->postParam('pay_status');
        $note = $this->postParam('note');

        $add_data = compact('order_no','name','pay_time','pay_name','amount','coin_type','pay_type','note','pay_status');

        $cash_in_model = new CashIn();
        if($id){
            $info = $cash_in_model->getInfoById($id);
            if($info && $info['order_no']!=$order_no){
                $exits_info = $cash_in_model->getInfoByOrderNo($order_no);
                if($exits_info){
                    $this->showerror(['code'=>'200001','msg'=>'订单号已重复']) ;
                }
            }

            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $cash_in_model->baseUpdate($cash_in_model::tableName(),$add_data,'id=:id',[':id'=>$id]);

        }else{
            $adminUserInfo = $this->adminUserInfo ;
            $add_data['admin_user_id'] = $adminUserInfo['id'];
            $add_data['source'] = 'ADMIN';
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            $cash_in_model->baseInsert($cash_in_model::tableName(),$add_data);
        }

        return $this->redirect('/cash/in') ;
    }

    // 删除入金记录
    public function actionDelIn(){
        $id = $this->getParam('id') ;
        $model = new CashIn();
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $update_data['is_deleted'] = 'Y';
        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id] ) ;
        return $this->returnJson(['code'=>1]) ;
    }

    // 出金记录
    public function actionOut(){

        $searchArr = array();

        $order_no = $this->getParam('order_no') ;
        if($order_no){
            $params['like_arr']['order_no'] = $order_no;
        }
        $searchArr['order_no'] = $order_no ;

        $start_time = $this->getParam('start_time');
        if($start_time){
            $params['greater_where_arr']['apply_time'] = $start_time;
        }
        $searchArr['start_time'] = $start_time ;

        $end_time = $this->getParam('end_time');
        if($end_time){
            $params['lesser_where_arr']['apply_time'] = $end_time;
        }
        $searchArr['end_time'] = $end_time ;

        $renderData['searchArr'] =$searchArr ;

        // 查询分页信息
        $page_num = $this->page_rows ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = 'id desc ';
        $params['where_arr']['is_deleted'] = 'N';

        $adminUserInfo = $this->adminUserInfo;
        if($adminUserInfo['username'] != 'admin'){
            $params['where_arr']['admin_user_id'] = $adminUserInfo['id'];
        }

        $model = new CashOut() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            $site_config_obj = new SiteConfig();
            $cash_in_fee_percent = $site_config_obj->getByKey('cash_out_fee_percent') /100 ;
            foreach($list as $k=>$v){
                $list[$k]['status'] = $v['status'] =='TO_PAY' ? '待支付':'已支付';
                //$list[$k]['fee'] = round($v['amount']*$cash_in_fee_percent,2);
                $list[$k]['fee'] =3 ;
            }
        }


        $renderData['list'] = $list ;

        unset($params['page']) ;
        $params['return_field']='id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        $site_config = new SiteConfig();
        $renderData['cash_in_min_amount'] = $site_config->getByKey('cash_in_min_amount') ;

        $this->loadResource('cash','actionAddIn') ;
        return $this->render('out',$renderData);
    }

    // 新增单条出金记录
    public function actionAddOut(){
        $this->loadResource('cash','actionAddOut') ;
        $site_config = new SiteConfig();
        $cash_in_coin_type = $site_config->getByKey('cash_in_coin_type');
        $coin_type_list = json_decode($cash_in_coin_type,true);
        $renderData['coin_type_list'] = $coin_type_list ;
        $area_obj = new Areas();
        $renderData['province_list'] = $area_obj->getListByParentId(0);

        $renderData['city_list'] = $area_obj->getListByParentId($renderData['province_list'][0]['area_id']);
        $renderData['info'] = [] ;
        return $this->render('add-out',$renderData);
    }

    // 获取城市列表信息
    public function actionGetCityList(){
        $province_id = $this->postParam('province_id') ;
        $area_obj = new Areas();
        $city_list = $area_obj->getListByParentId($province_id);
        $str = '';
        if($city_list){
            foreach($city_list as $v){
                $str.= '<option value="'.$v['area_id'].'">'.$v['area_name'].'</option>';
            }
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$str]);
    }

    // 发送邮箱验证码
    public function actionSendCode(){
        $type = strtoupper($this->postParam('type')) ;
        $adminUserInfo = $this->adminUserInfo ;
        $admin_user_id = $adminUserInfo['id'];
        $admin_user_obj = new Admin();
        $email = $admin_user_obj->getEmailByUserId($admin_user_id);
        if(!$email){
            return $this->returnJson(['code'=>'200002','msg'=>getErrorDictMsg(200002)]);
        }

        $email_obj = new EmailCode();
        $res = $email_obj->sendByEmail($email,$type);
        if($res){
            return $this->returnJson(['code'=>1]);
        }else{
            return $this->returnJson(['code'=>200003,'msg'=>getErrorDictMsg(200003)]);
        }

    }

    //  保存单条出金信息
    public function actionSaveOut(){

        $code = $this->postParam('code');
        $email_obj = new EmailCode();

        // 获取管理员的邮箱信息
        $admin_user_obj = new Admin();
        $adminUserInfo = $this->adminUserInfo ;
        $admin_user_id = $adminUserInfo['id'];
        $email = $admin_user_obj->getEmailByUserId($admin_user_id);

        // 验证邮箱验证码是否正确
        $type = 'CASH_OUT';
        $check_res = $email_obj->checkCode($email,$code,$type);
        if(!$check_res){
            //return  $this->returnJson(['code'=>200004,'msg'=>200004]);
        }

        // 更新验证码信息
        $update_res = $email_obj->updateCode($check_res['id'],$email,$type);
        if(!$update_res){
            //return  $this->returnJson(['code'=>200005,'msg'=>200005]);
        }

        $cash_out_model = new CashOut();
        $add_data['order_no'] = $cash_out_model->createOrderNo();
        $add_data['amount'] = $this->postParam('amount');
        $add_data['coin_type'] = 'CNY';
        $add_data['bank_name'] = $this->postParam('bank_name');
        $add_data['bank_account'] = $this->postParam('bank_account');
        $add_data['bank_detail'] = $this->postParam('bank_detail');
        $add_data['bank_no'] = $this->postParam('bank_no');
        $add_data['province'] = $this->postParam('province');
        $add_data['city'] = $this->postParam('city');
        $add_data['status'] = 'TO_PAY' ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['admin_user_id'] = $admin_user_id ;

        // 当前时间
        $now = date('Y-m-d H:i:s');
        $add_data['pay_time'] = NULL ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        $id = $cash_out_model->baseInsert($cash_out_model::tableName(),$add_data);
        if(!$id){
            return $this->returnJson(['code'=>200007,'msg'=>getErrorDictMsg(200007)]);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    /**
     * 导入批量出金记录
     */
    public function actionAddBatchOut(){
        $this->loadResource('cash','actionAddBatchOut') ;
        $site_config = new SiteConfig();
        $cash_in_coin_type = $site_config->getByKey('cash_in_coin_type');
        $coin_type_list = json_decode($cash_in_coin_type,true);
        $renderData['coin_type_list'] = $coin_type_list ;
        $site_config_obj = new SiteConfig();
        $renderData['cash_out_min_amount'] = $site_config_obj->getByKey('cash_out_min_amount');
        $renderData['cash_out_max_amount'] = $site_config_obj->getByKey('cash_out_max_amount');
        $renderData['info'] = [] ;
        return $this->render('add-batch-out',$renderData);
    }

    /**
     * 批量保存
     */
    public function actionSaveBatchOut(){

        $file_name = $this->postParam('file_name');
        $code = $this->postParam('code') ;

        // 获取管理员的邮箱信息
        $admin_user_obj = new Admin();
        $adminUserInfo = $this->adminUserInfo ;
        $admin_user_id = $adminUserInfo['id'];
        $email = $admin_user_obj->getEmailByUserId($admin_user_id);
        // 验证邮箱验证码是否正确
        $email_obj = new EmailCode();
        $type = 'CASH_BATCH_OUT';
        $check_email_res = $email_obj->checkCode($email,$code,$type);
        if(!$check_email_res){
            //return  $this->returnJson(['code'=>200004,'msg'=>200004]);
        }

        $file_path = ROOT_PATH.$file_name ;
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($file_path)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($file_path)){
                return  $this->returnJson(['code'=>200008,'msg'=>200008]);
            }
        }

        $PHPExcel = $PHPReader->load($file_path);

        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        $allColumn = 'G';

        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();

        //循环读取每个单元格的内容。注意行从1开始，列从A开始
        $insert_data = [];
        for($rowIndex=4;$rowIndex<=$allRow;$rowIndex++){
            $add_data = [] ;
            for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
                $addr = $colIndex.$rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if($cell instanceof PHPExcel_RichText)     //富文本转换字符串
                    $cell = $cell->__toString();

                $add_data[] = $cell ;

            }

            $insert_data[] = $add_data ;


        }

        if(!$insert_data){
            return  $this->returnJson(['code'=>200009,'msg'=>getErrorDictMsg(200009)]);
        }

        $cash_out_obj = new CashOut();

        $batch_insert_data = [] ;
        foreach($insert_data as $k=>$v){
            $check_data['amount'] = trim($v[0]);
            $check_data['bank_account'] = trim($v[1]);
            $check_data['bank_no'] = trim($v[2]);
            $check_data['province_name'] = trim($v[3]);
            $check_data['city_name'] = trim($v[4]);
            $check_data['bank_detail'] = trim($v[5]);
            $check_data['bank_name'] = trim($v[6]);

            $check_res = $cash_out_obj->checkAddData($check_data);
            if($check_res['code'] != 1){
                return $this->returnJson(['code'=>$check_res['code'],'msg'=>'第'.($k+4).'行'.$check_res['code']]);
            }

            $check_data['province'] = $check_res['data']['province'];
            $check_data['city'] = $check_res['data']['city'];
            $check_data['bank_no'] = $check_res['data']['bank_no'];
            $batch_insert_data[] = $check_data ;

        }

        $cash_out_obj->batchInsertData($batch_insert_data,$admin_user_id);

        // 更新验证码信息
        $email_obj->updateCode($check_email_res['id'],$email,$type);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除入金记录
    public function actionDelOut(){
        $id = $this->getParam('id') ;
        $model = new CashOut();
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $update_data['is_deleted'] = 'Y';
        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id] ) ;
        return $this->returnJson(['code'=>1]) ;
    }

    /**
     * 今日汇总信息
     */
    public function actionTotal(){

        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d H:i:s');

        $cash_in_obj = new CashIn();
        $today_in_total = $cash_in_obj->getTotal($this->adminUserInfo,$start_time,$end_time);
        $renderData['today_in_total'] = $today_in_total ;

        $cash_out_obj = new CashOut() ;
        $today_out_total = $cash_out_obj->getTotal($this->adminUserInfo,$start_time,$end_time);
        $renderData['today_out_total'] = $today_out_total ;

        $total_in = $cash_in_obj->getTotal($this->adminUserInfo,'','');

        $total_out = $cash_out_obj->getTotal($this->adminUserInfo,'','');
        $total_left = $total_in - $total_out;
        $renderData['total_left'] = $total_left ;

        return $this->render('total',$renderData);
    }

    public function actionConfirmIn(){
        $id = $this->postParam('id') ;
        $model = new CashIn();
        $update_data['is_confirm'] = 'Y';
        $update_data['pay_status'] = 'PAYED';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id]) ;

        $task_data['business_id'] = $id ;
        $task_data['business_type'] = 'CASH_PAY_SUCCESS' ;
        $task_data['business_time'] = time() ;
        $task_data['business_timestamp'] = date('Y-m-d H:i:s');
        $task_data['to_address'] = '';
        $task_data['tx_hash'] = '';
        $task_data['nonce'] = '';
        $task_data['admin_allowed'] = '';
        $task_data['status'] = 'NOPUSH';
        $task_data['push_url'] = '';
        $task_data['create_time'] = date('Y-m-d H:i:s');
        $task_data['modify_time'] = date('Y-m-d H:i:s');
        $model->baseInsert('sea_push_task',$task_data);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    public function actionConfirmOut(){
        $id = $this->postParam('id') ;
        $model = new CashOut();
        $update_data['is_confirm'] = 'Y';
        $update_data['status'] = 'PAYED';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id]) ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }
}
