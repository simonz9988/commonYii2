<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminApiKey;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminTotalApiKey;
use backend\models\AdminUserApiKey;
use backend\models\CoinAddressValue;
use backend\models\OkexLedger;
use backend\models\OkexTotalOrder;
use common\components\CoinBalance;
use common\components\PlatformTradeCommonV4;
use common\models\SiteConfig;

/**
 * System
 */
class AllApiKeyController extends BackendController
{
    public function actionList(){

        $searchArr = array();
        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id desc ';
        $admin_user_id = $this->adminUserInfo['id'];

        // 查询当前用于允许查看账户ID
        $admin_user_api_key_obj = new AdminUserApiKey() ;
        $total_api_key_ids = $admin_user_api_key_obj->getKeyIdsByAdminUserId($admin_user_id);

        $model = new AdminTotalApiKey() ;

        if($total_api_key_ids){
            $params['in_where_arr']['id'] = $total_api_key_ids ;

            $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        }else{
            $list = [];
        }

        $renderData['list'] =$list;

        // 返回总记录记录数
        if($total_api_key_ids){
            unset($params['page']);
            $params['return_field'] = 'id';
            $total_list = $admin_user_api_key_obj->findByWhere($model::tableName(),$params,$model::getDb());
            $total = count($total_list);
        }else{
            $total = 0 ;
        }


        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return $this->render('list',$renderData) ;
    }

    // 账户余额列表
    public function actionBalanceList(){

        $this->page_num = 40 ;
        $this->page_rows = 40 ;
        $searchArr = array();
        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id asc ';

        // 查询当前用于允许查看账户ID
        $admin_user_id = $this->adminUserInfo['id'];
        $admin_user_api_key_obj = new AdminUserApiKey() ;
        $total_api_key_ids = $admin_user_api_key_obj->getKeyIdsByAdminUserId($admin_user_id);

        $model = new AdminTotalApiKey() ;
        if($total_api_key_ids){
            $params['in_where_arr']['id'] = $total_api_key_ids ;
            $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        }else{
            $list = [];
        }

        $mark_price['eth'] =  0 ;
        $mark_price['eos'] =  0 ;
        $mark_price['btc'] =  0 ;

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
                $list[$k]['down_buy_extra_num'] = intval($list[$k]['down_buy_extra_num']) ;

                $up_position = $v['order_info']['up_order']?$v['order_info']['up_order']['position']:0;
                $up_avg_price =$v['order_info']['up_order']?$v['order_info']['up_order']['avg_cost']: 0 ;
                $list[$k]['up_buy_extra_num'] = $up_position ?  ( $up_avg_price-$current_price*1.002 )*$up_position/($current_price*0.002) : 0 ;
                $list[$k]['up_buy_extra_num'] = intval($list[$k]['up_buy_extra_num']) ;

                $list[$k]['distance_percent'] = $distance_percent*100 ;

            }
        }
        $renderData['list'] =$list;
        $renderData['mark_price'] =$mark_price;

        // 返回总记录记录数
        $params['return_field'] = 'id';
        unset($params['page']);
        $total_list = $model->findByWhere($model::tableName(),$params,$model::getDb());
        $total = count($total_list);

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;



        return $this->render('balance-list',$renderData) ;
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

        return $this->returnJson(['code'=>1,'msg'=>'success']);
    }

    /**
     * 修正远程系统订单
     */
    public function actionAjaxFixOrder(){
        $id = $this->getParam('id') ;
        $type = $this->getParam('type') ;
        $all_api_key_obj  = new  AdminTotalApiKey();
        $res = $all_api_key_obj->fixOrderByIdAndType($id,$type);

        if($res){
            return $this->returnJson(['code'=>1,'msg'=>'success']);
        }else{
            return $this->returnJson(['code'=>-1,'msg'=>'修正失败']);
        }
    }

    // 远程新增标记位
    public function actionAjaxAddMark(){
        $id = $this->getParam('id') ;
        $type = $this->getParam('type') ;
        $all_api_key_obj  = new  AdminTotalApiKey();
        $res = $all_api_key_obj->addMark($id,$type);
        if($res){
            return $this->returnJson(['code'=>1,'msg'=>'success']);
        }else{
            return $this->returnJson(['code'=>-1,'msg'=>'新增失败']);
        }
    }




}
