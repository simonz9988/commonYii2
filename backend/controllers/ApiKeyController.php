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
class ApiKeyController extends BackendController
{
    public function actionIndex(){

        $searchArr = array();
        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id desc ';
        $model = new AdminApiKey() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

        // 返回总记录记录数
        $total = $model->getTotal();

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return $this->render('list',$renderData) ;
    }

    public function actionEdit(){

        $id = $this->getParam('id');
        $api_key_obj = new AdminApiKey() ;
        $info = $api_key_obj->getInfoById($id);
        $render_data['info'] = $info ;
        return $this->render('edit',$render_data);
    }

    public function actionSave(){
        $admin_user_id =  $this->getParam('admin_user_id');
        $is_start_buy_up   =  $this->getParam('is_start_buy_up');
        $is_start_trade_up  =  $this->getParam('is_start_trade_up');
        $is_start_buy_down       =  $this->getParam('is_start_buy_down');
        $is_start_trade_down       =  $this->getParam('is_start_trade_down');
        $platform       = 'OKEX';
        $api_key       =    $this->getParam('api_key');
        $api_secret       =    $this->getParam('api_secret');
        $passphrase       =    $this->getParam('passphrase');
        $leverage       =    $this->getParam('leverage');
        $sort       =    $this->getParam('sort');
        $status       =    $this->getParam('status');
        $note       =    $this->getParam('note');
        $coin       =    strtolower(trim($this->getParam('coin')));
        $base_coin       =    $this->getParam('base_coin');
        $base_buy_num       =    $this->getParam('base_buy_num','int');
        $is_deleted       =    'N';
        $modify_time = date('Y-m-d H:i:s') ;
        $add_data = compact('admin_user_id','is_start_buy_down','is_start_buy_up','is_start_trade_up','is_start_trade_down');
        $add_data['platform'] = $platform ;
        $add_data['api_key'] = $api_key ;
        $add_data['api_secret'] = $api_secret ;
        $add_data['passphrase'] = $passphrase ;
        $add_data['leverage'] = $leverage ;
        $add_data['sort'] = $sort ;
        $add_data['status'] = $status ;
        $add_data['note'] = $note ;
        $add_data['is_deleted'] = $is_deleted ;
        $add_data['modify_time'] = $modify_time ;
        $add_data['coin'] = $coin ;
        $add_data['base_coin'] = $base_coin ;
        $add_data['base_buy_num'] = $base_buy_num ;

        $earn_percent       =    $this->getParam('earn_percent');
        $add_distance       =    $this->getParam('add_distance');
        $buy_num_type       =    $this->getParam('buy_num_type');
        $max_level       =    $this->getParam('max_level');

        $add_data['earn_percent'] = $earn_percent ;
        $add_data['add_distance'] = $add_distance ;
        $add_data['buy_num_type'] = $buy_num_type ;
        $add_data['max_level'] = $max_level ;
        $add_data['is_sync'] = 'N';
        $add_data['qiangpin_percent'] = $this->getParam('qiangpin_percent');


        $id = $this->getParam('id') ;
        $model = new AdminApiKey() ;
        if($id){

            $rst = $model->baseUpdate($model::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = date('Y-m-d H:i:s') ;
            $rst = $model->baseInsert($model::tableName(),$add_data) ;
        }

        $url = '/api-key/index';
        return $this->redirect($url);
    }

    // 添加标记
    public function actionAddMark(){
        $id = $this->getParam('id') ;
        $type = $this->getParam('type') ;
        $api_key_obj = new AdminApiKey();
        $api_key_info = $api_key_obj->getInfoById($id);
        $admin_user_id = $api_key_info['admin_user_id'] ;

        $trade_obj = new PlatformTradeCommonV4();
        $coin = $api_key_info['coin'];
        $trade_obj->setConfigInfo($admin_user_id,$api_key_info,$coin,$type) ;

        $all_table_name = $trade_obj->returnAllTableName($coin);
        if ($type == 'up') {
            $table_name = $all_table_name[1];

        } else {
            $table_name = $all_table_name[2];
        }

        $model = new AdminApiKey();
        $now = date('Y-m-d H:i:s');


        $buy_add_data['order_id'] =  0 ;
        $buy_add_data['admin_user_id'] =  $admin_user_id ;
        $buy_add_data['coin'] =  $coin ;
        $buy_add_data['create_time'] = $now ;
        $buy_add_data['modify_time'] = $now ;

        $model->baseInsert($table_name,$buy_add_data);
        $url = '/api-key/index';
        return $this->redirect($url);
    }

    // 账户余额列表
    public function actionBalanceList(){

        $searchArr = array();
        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id asc ';
        $mark_price['eth'] =  0 ;
        $mark_price['eos'] =  0 ;
        $mark_price['btc'] =  0 ;
        $model = new AdminApiKey() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $kline_data['eth'] = [] ;
        $kline_data['eos'] = [] ;
        $kline_data['btc'] = [] ;
        if($list){
            foreach($list as $k=>$v){
                $trade_obj = new PlatformTradeCommonV4();
                $trade_obj->setConfigInfo($v['admin_user_id'],$v,$v['coin'],'up') ;
                $account_info = $trade_obj->getAccountsInfo();
                $list[$k]['account_info'] = $account_info ;
                $list[$k]['order_info'] = $trade_obj->getAllHoldingInfo();
                if(!$mark_price[$v['coin']]){
                    $mark_price[$v['coin']] = $trade_obj->getCoinMarkPriceFromService($v['coin']);
                }

                if(!$kline_data[$v['coin']]){
                    // key值为4的为收盘价
                    $kline_data[$v['coin']] = $trade_obj->getKlineData();
                    $kline_data[$v['coin']] = array_sort($kline_data[$v['coin']],0);
                }
            }

            foreach($list as $k=>$v){
                $current_price = $mark_price[$v['coin']];
                $liquidation_price = isset($v['order_info']['down_order']['liquidation_price'])?$v['order_info']['down_order']['liquidation_price']:0;
                if($liquidation_price > $current_price){
                    $distance_percent = $current_price >0? round($liquidation_price/$current_price,4) - 1 : 0 ;
                }else{
                    $distance_percent = $liquidation_price >0? round($current_price/$liquidation_price,4) - 1 : 0 ;
                }

                // 空单张数

                $down_position = $v['order_info']['down_order']?$v['order_info']['down_order']['position']:0;
                $down_avg_price =$v['order_info']['down_order']?$v['order_info']['down_order']['avg_cost']: 0 ;
                $list[$k]['down_buy_extra_num'] = $down_position ?  ($current_price*0.998 - $down_avg_price )*$down_position /($current_price*0.002) : 0 ;
                $list[$k]['down_buy_extra_num'] =intval($list[$k]['down_buy_extra_num']);

                $up_position = $v['order_info']['up_order']?$v['order_info']['up_order']['position']:0;
                $up_avg_price =$v['order_info']['up_order']?$v['order_info']['up_order']['avg_cost']: 0 ;
                $list[$k]['up_buy_extra_num'] = $up_position ?  ( $up_avg_price-$current_price*1.002 )*$up_position/($current_price*0.002) : 0 ;
                $list[$k]['up_buy_extra_num'] =intval($list[$k]['up_buy_extra_num']);


                $list[$k]['distance_percent'] = $distance_percent*100 ;
            }
        }
        $renderData['list'] =$list;
        $renderData['mark_price'] =$mark_price;

        // 返回总记录记录数
        $total = $model->getTotal();

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        $renderData['kline_data'] = $kline_data;

        $time_list['eos'] = [];
        $time_list['eth'] = [];
        $time_list['btc'] = [];
        $data_list['eos'] = [];
        $data_list['eth'] = [];
        $data_list['btc'] = [];
        foreach($kline_data as $k=>$v_v){

            if($v_v){
                foreach($v_v as $v){
                    $time_list[$k][] = $v[0];
                    $data_list[$k][] = $v[4] ;
                }
            }

        }
        $eos_format_line_data = [
            'labels' => json_encode($time_list['eos'],JSON_UNESCAPED_UNICODE),
            'datasets' => json_encode([
                [
                    'label' => '汇总数据',
                    'backgroundColor' => "rgba(152,137,193,0.3)", // 背景色
                    'borderColor' => "rgb(152,137,193)", // 线
                    'data' => $data_list['eos']
                ],

            ],JSON_UNESCAPED_UNICODE),
            'title' => '价格曲线'
        ];

        $eth_format_line_data = [
            'labels' => json_encode($time_list['eth'],JSON_UNESCAPED_UNICODE),
            'datasets' => json_encode([
                [
                    'label' => '汇总数据',
                    'backgroundColor' => "rgba(152,137,193,0.3)", // 背景色
                    'borderColor' => "rgb(152,137,193)", // 线
                    'data' => $data_list['eth']
                ],

            ],JSON_UNESCAPED_UNICODE),
            'title' => '价格曲线'
        ];

        $btc_format_line_data = [
            'labels' => json_encode($time_list['btc'],JSON_UNESCAPED_UNICODE),
            'datasets' => json_encode([
                [
                    'label' => '汇总数据',
                    'backgroundColor' => "rgba(152,137,193,0.3)", // 背景色
                    'borderColor' => "rgb(152,137,193)", // 线
                    'data' => $data_list['btc']
                ],

            ],JSON_UNESCAPED_UNICODE),
            'title' => '价格曲线'
        ];

        $renderData['eos_format_line_data'] = $eos_format_line_data;
        $renderData['eth_format_line_data'] = $eth_format_line_data;
        $renderData['btc_format_line_data'] = $btc_format_line_data;
        return $this->render('balance-list',$renderData) ;
    }

    // 关闭所有
    public function actionAjaxCloseAll(){
        //
        $type= $this->getParam('type') ;
        $type = strtolower($type);
        if(!$type){
            $type = 'all';
        }

        $params['order_by'] = ' id asc ';
        $model = new AdminApiKey() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            foreach($list as $k=>$v){

                if($type =='all'){
                    $update_data['is_start_trade_up'] = 'N';
                    $update_data['is_start_trade_down'] = 'N';
                }elseif($type =='up'){
                    $update_data['is_start_trade_up'] = 'N';
                }elseif($type=='down'){
                    $update_data['is_start_trade_down'] = 'N';
                }

                $update_data['modify_time'] = date('Y-m-d H:i:s');
                $model->baseUpdate($model::tableName(),$update_data);
            }
        }
        echo json_encode(['code'=>1,'msg'=>'success']);exit ;
    }

    // 异步同步订单
    public function actionAjaxSyncOrder(){
        $id = $this->getParam('admin_user_id');
        $model = new AdminApiKey();
        $admin_api_key_info = $model->getInfoById($id);
        $obj = new OkexTotalOrder();
        $order_id = '';
        for($i =0 ;$i<6;$i++){
            $order_id = $obj->syncOrderByAdminApiInfo($admin_api_key_info,$order_id);
        }

        echo json_encode(['code'=>1,'msg'=>'success']);exit ;
    }

    // 提示信息设置
    public function actionNotice(){

        $site_config_obj = new SiteConfig();
        //https://oapi.dingtalk.com/robot/send?access_token=692e9146bdb392869d7f4aad6b0dba39deaff653b7fac541a431c29a36b99633
        $notice_webhook = $site_config_obj->getByKey('notice_webhook');
        $render_data['notice_webhook'] = $notice_webhook ;

        $is_start_notice_webhook = $site_config_obj->getByKey('is_start_notice_webhook');
        $render_data['is_start_notice_webhook'] = $is_start_notice_webhook ;

        $up_notice_price = $site_config_obj->getByKey('up_notice_price');
        $render_data['up_notice_price'] = $up_notice_price ;

        $down_notice_price = $site_config_obj->getByKey('down_notice_price');
        $render_data['down_notice_price'] = $down_notice_price ;

        return $this->render('notice',$render_data);
    }

    public function actionSaveNotice(){

        $site_config_obj = new SiteConfig();
        //https://oapi.dingtalk.com/robot/send?access_token=692e9146bdb392869d7f4aad6b0dba39deaff653b7fac541a431c29a36b99633
        $notice_webhook = $this->getParam('notice_webhook');
        $site_config_obj->saveByKey('notice_webhook',$notice_webhook);

        $is_start_notice_webhook = $this->getParam('is_start_notice_webhook');
        $site_config_obj->saveByKey('is_start_notice_webhook',$is_start_notice_webhook);

        $up_notice_price = $this->getParam('up_notice_price');
        $site_config_obj->saveByKey('up_notice_price',$up_notice_price);

        $down_notice_price = $this->getParam('down_notice_price');
        $site_config_obj->saveByKey('down_notice_price',$down_notice_price);

        return $this->redirect('/api-key/notice');
    }

    /**
     * 修正订单
     */
    public function actionFixOrder(){
        $id = $this->getParam('id') ;
        $type = $this->getParam('type') ;
        $total_model = new OkexTotalOrder();
        $total_model->fixOrderByAdminApiKey($id,$type);

    }



    // 同步流水
    public function actionAjaxSyncLedger(){

        $id = $this->getParam('admin_user_id') ;
        $model = new OkexLedger();
        $model->syncOrder($id);
        echo json_encode(['code'=>1,'msg'=>'success']);exit ;

    }


    // 异步同步订单
    public function actionAjaxSyncAllOrder(){
        $obj = new OkexTotalOrder();
        $obj->syncAllOrder();
        echo json_encode(['code'=>1,'msg'=>'success']);exit ;
    }

}
