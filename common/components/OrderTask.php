<?php
namespace common\components;


use common\models\CollectionDoc;
use common\models\Member;
use common\models\Order;
use common\models\OrderGoods;
use common\models\OrderGoodsSingle;
use common\models\OrderInvoice;
use common\models\OrderMapping;
use common\models\OrderPushTask;
use common\models\PaymentDiscountRecord;

class OrderTask
{
    /**
     * 添加推送任务
     * @param $order_id
     * @return bool
     */
    public function addPushTask($order_id)
    {
        $order_push_task_obj = new OrderPushTask();
        $data['order_id'] = $order_id;
        $data['order_no'] = '';
        $data['params'] =  '';
        $data['url'] = '';
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['update_time'] = date("Y-m-d H:i:s");
        $data['push_count'] = 0;
        $data['status'] = 'NOTSTART';

        $result = $order_push_task_obj->baseInsert('sdb_order_push_task',$data);
        return $result;
    }

    /**
     * 添加取消发货任务
     * @param $order_id
     * @param $goods_id
     * @return bool
     */
    public function addCancelDeliveryTask($order_id,$goods_id)
    {
        $order_data = $this->prepareCancelDeliveryOrder($order_id,$goods_id);

        $order_push_task_obj = new OrderPushTask();
        $data['order_id'] = $order_id;
        $data['order_no'] = $order_data['orderBasicInfo']['order_no'];
        $data['params'] =  json_encode($order_data);
        $data['url'] = '';
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['update_time'] = date("Y-m-d H:i:s");
        $data['push_count'] = 0;
        $data['status'] = 'NOTSTART';

        $result = $order_push_task_obj->baseInsert('sdb_order_push_task',$data);
        return $result;
    }


    /**
     * 准备订单数据
     * @param $order_id
     * @return array
     */
    private function prepareCancelDeliveryOrder($order_id,$goods_id){
        $data = [];

        $order_obj = new Order();
        $order_data = $order_obj->findOneByWhere('sdb_order',['cond'=>'id = :id','args'=>[':id'=>$order_id]]);
        $order_data['delivery_callback_url'] = SHOP_ADMIN_URL.'/ec-callback/send-delivery';
        $order_data['einvoice_callback_url'] = SHOP_ADMIN_URL.'/ec-callback/einvoice';
        $order_data['order_cancel_callback_url'] = SHOP_ADMIN_URL.'/ec-callback/order-cancel';
        $data['orderBasicInfo'] = $order_data;

        $order_goods_obj = new OrderGoods();
        $order_goods_data = $order_goods_obj->getEcCancelOrderInfo($order_id,$goods_id);
        $data['orderGoodsInfo'] = $order_goods_data;

        $order_goods_single_obj = new OrderGoodsSingle();
        $order_goods_single_data = $order_goods_single_obj->findAllByWhere('sdb_order_goods_single',['cond'=>'order_id = :order_id','args'=>[':order_id'=>$order_id],'fields'=>'id,order_id,goods_id,product_id,order_goods_id,serial_no,send_status,after_market_status,amd_id,send_time']);
        $data['orderGoodsSingleInfo'] = $order_goods_single_data;

        $order_mapping_obj = new OrderMapping();
        $order_mapping_data = $order_mapping_obj->findAllByWhere('sdb_order_mapping',['cond'=>'order_id = :order_id','args'=>[':order_id'=>$order_id]]);
        $data['orderMapping'] = $order_mapping_data;

        $member_obj = new Member();
        $member_data = $member_obj->getUserInfoById($order_data['user_id']);
        unset($member_data['custom']);
        unset($member_data['password']);
        $data['userInfo'] = $member_data;

        $order_invoice_obj = new OrderInvoice();
        $order_invoice_data = $order_invoice_obj->findOneByWhere('sdb_order_invoice',['cond'=>'order_id = :order_id','args'=>[':order_id'=>$order_id]]);
        $data['orderInvoice'] = $order_invoice_data ? $order_invoice_data : null;

        //默认快递
        $data['orderBasicInfo']['default_express'] = $order_goods_obj->getDefaultExpress($order_goods_data);

        //实付金额
        $payment_discount_record_obj = new PaymentDiscountRecord();
        $payment_discount_amount = $payment_discount_record_obj->getDiscountAmount($order_id);

        $collection_doc_obj = new CollectionDoc();
        $data['orderBasicInfo']['payed_amount'] = $collection_doc_obj->getOrderPayedAmount($order_id,$payment_discount_amount);

        return $data;
    }

}
