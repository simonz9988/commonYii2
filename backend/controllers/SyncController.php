<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminApiKey;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminTotalApiKey;
use backend\models\OkexTotalOrder;
use backend\models\PlatformTotalOrder;
use common\components\PlatformTradeCommonV4;
use common\models\SiteConfig;

/**
 * System
 */
class SyncController extends \common\controllers\BaseController
{
    public function actionDownloadFromRemote(){

        // 从远程地址同步订单信息
        $post_data = $_POST ;
        if(!$post_data){
            return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
        }

        $host = isset($post_data['host'])?$post_data['host']:'';
        $order_list = isset($post_data['order_list'])?$post_data['order_list']:[];
        if(!$order_list){
            return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
        }

        $platform_total_order_obj = new PlatformTotalOrder();
        $platform_total_order_obj->downloadOrder($host,$order_list);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);

    }

    // 同步账户信息
    public function actionDownloadApiKey(){
        $post_data = $_POST ;
        if(!$post_data){
            return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
        }

        $host = isset($post_data['host'])?$post_data['host']:'';
        $api_key_list = isset($post_data['api_key_list'])?$post_data['api_key_list']:[];
        if(!$api_key_list){
            return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
        }

        $obj = new AdminTotalApiKey();
        $obj->downloadApiKey($host,$api_key_list);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 远程修复订单
    public function actionFixOrder(){

        $id = $this->getParam('id') ;
        $type = $this->getParam('type') ;
        $total_model = new OkexTotalOrder();
        $total_model->fixOrderByAdminApiKey($id,$type);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 远程新增标记信息
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

        $res = $model->baseInsert($table_name,$buy_add_data);
        if($res){
            return $this->returnJson(['code'=>1,'msg'=>'Success']);
        }else{
            return $this->returnJson(['code'=>'-1','msg'=>'失败']);
        }
    }


}
