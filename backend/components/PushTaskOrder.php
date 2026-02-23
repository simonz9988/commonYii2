<?php
/**
 * 推送订单
 */
namespace backend\components;

use common\models\Areas;
use common\models\OrderSalesclerkStore;
use Yii ;
use yii\base\Exception;
use common\models\PushTask;
use common\models\Repair;
use common\models\OrderGoods;
use common\models\Member;
use common\models\OrderGoodsSingle;
use common\models\OrderMapping;
use common\models\OrderInvoice;
use common\models\PaymentDiscountRecord;
use common\models\CollectionDoc;
use common\models\Order ;



class PushTaskOrder extends PushTaskCommon
{
    /**
     * 推送订单信息
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('ORDER', $status, 500);
        if(!$push_task_list){
            return false;
        }
        
        // 数据处理 追加 push_task_id,push_url 字段 (不用动)
        $order_push_task_list = [];
        foreach($push_task_list as $row){
            $order_push_task_list[$row['business_id']]['push_task_id'] = $row['id'];
            $order_push_task_list[$row['business_id']]['order_id'] = $row['business_id'];
            $order_push_task_list[$row['business_id']]['push_url'] = $this->getPushOrderUrl();
        }

        // 循环推送订单中心
        $pushed_order_ids = [];
        foreach($order_push_task_list as $row){

            if(!in_array($row['order_id'],$pushed_order_ids)){

                $push_rst = $this->pushDataToEc($row);

                if($push_rst){

                    $push_task_id = $row['push_task_id'];

                    $pushed_order_ids[] = $row['order_id'];

                    $update_data = ['status'=>'CLOSED','modify_time'=>date('Y-m-d H:i:s')];
                    $update_where_str = 'id < :id AND business_id=:business_id AND business_type =:business_type AND status != :status1 AND  status != :status2 ';
                    $update_where_arr[':business_type'] = 'ORDER';
                    $update_where_arr[':business_id'] = $row['order_id'];
                    $update_where_arr[':status1'] = 'PUSHED';
                    $update_where_arr[':status2'] = 'CLOSED';
                    $update_where_arr[':id'] = $push_task_id;
                    $push_task_model->baseUpdate('sdb_push_task',$update_data,$update_where_str,$update_where_arr);
                }
            }else{
                continue ;
            }
        }

    }

    /**
     * 发送售后单到订单中心
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToEc($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];

        $push_task_model = new PushTask();

        $now_time = date("Y-m-d H:i:s");

        // 推送相关数据处理
        $order_id = $request_data['order_id'];
        $order_data = $this->prepareOrder($order_id);
        $params = json_encode($order_data,JSON_UNESCAPED_UNICODE);
        $push_data['detailData'] = $params ;
        // 发送售后信息到中台
        $response_data = $this->pushDataToEcCommon($request_data, $push_data);
        if(!$response_data){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新任务为已推送
            $push_url = $request_data['push_url'];
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED', 'push_url'=>$push_url,'modify_time' => $now_time]);

            // 提交事务
            $transaction->commit();

            return true;
        }catch (Exception $e) {

            //回滚事务
            $transaction->rollback();

            return false;
        }
    }

    /**
     * 准备订单数据
     * @param $order_id
     * @return array
     */
    private function prepareOrder($order_id){
        $data = [];

        $order_obj = new Order();
        $order_data = $order_obj->findOneByWhere('sdb_order',['cond'=>'id = :id','args'=>[':id'=>$order_id]]);

        $order_data['delivery_callback_url'] = scheme_url(SHOP_ADMIN_URL).'/ec-callback/send-delivery';
        $order_data['einvoice_callback_url'] = scheme_url(SHOP_ADMIN_URL).'/ec-callback/einvoice';
        $order_data['order_cancel_callback_url'] = scheme_url(SHOP_ADMIN_URL).'/ec-callback/order-cancel-for-new';
        $order_data['shop_name'] = 'MALL';
        $area_obj = new Areas();
        $order_data['province_name'] = $area_obj->getAreaName($order_data['province']);
        $order_data['city_name'] = $area_obj->getAreaName($order_data['city']);
        $order_data['area_name'] = $area_obj->getAreaName($order_data['area']);
        $data['orderBasicInfo'] = $order_data;

        $order_goods_obj = new OrderGoods();
        $order_goods_data = $order_goods_obj->getEcNormalOrderInfo($order_id);
        $data['orderGoodsInfo'] = $order_goods_data;

        //$order_goods_single_obj = new OrderGoodsSingle();
        //$order_goods_single_data = $order_goods_single_obj->findAllByWhere('sdb_order_goods_single',['cond'=>'order_id = :order_id','args'=>[':order_id'=>$order_id],'fields'=>'id,order_id,goods_id,product_id,order_goods_id,serial_no,send_status,after_market_status,amd_id,send_time']);
        // TODO:EC 不需要 order_goods_single_info
        $data['orderGoodsSingleInfo'] = [];

        $order_mapping_obj = new OrderMapping();
        $order_mapping_data = $order_mapping_obj->findAllByWhere('sdb_order_mapping',['cond'=>'order_id = :order_id','args'=>[':order_id'=>$order_id]]);
        $data['orderMapping'] = $order_mapping_data;

        $member_obj = new Member();
        $member_data = $member_obj->getUserInfoById($order_data['user_id']);
        unset($member_data['custom']);
        unset($member_data['password']);
        $data['userInfo'] = $member_data;

        // 以旧换新部分订单发票数据错误
        $data['orderInvoice'] = null;
        if($order_data['invoice'] !=0){
            $order_invoice_obj = new OrderInvoice();
            $order_invoice_data = $order_invoice_obj->findOneByWhere('sdb_order_invoice',['cond'=>'order_id = :order_id','args'=>[':order_id'=>$order_id]]);
            $data['orderInvoice'] = $order_invoice_data ? $order_invoice_data : null;
            if($order_invoice_data){
                $data['orderInvoice']['invoice_content'] = $order_invoice_obj->getContentShow($data['orderInvoice']['invoice_content']);
            }
        }


        //默认快递
        $data['orderBasicInfo']['default_express'] = $order_goods_obj->getDefaultExpress($order_goods_data);

        //实付金额
        $payment_discount_record_obj = new PaymentDiscountRecord();
        $payment_discount_amount = $payment_discount_record_obj->getDiscountAmount($order_id);

        $collection_doc_obj = new CollectionDoc();
        $data['orderBasicInfo']['payed_amount'] = $collection_doc_obj->getOrderPayedAmount($order_id,$payment_discount_amount);

        //导购信息
        $data['orderSalesclerkStore'] = null;
        if($order_data['order_belong_to'] == 'STORE'){
            $salesclerk_store_obj = new OrderSalesclerkStore();
            $data['orderSalesclerkStore'] = $salesclerk_store_obj->getInfoByOrderId($order_id);
        }
        
        return $data;
    }

}
