<?php
namespace backend\components\cps;
use common\models\CpsOrderLog;
use common\models\OrderGoods;

/**
 * 返利网CPS
 * */
class CpsFanli extends CpsBase
{
    private $union_id = "fanli";
    private $s_id  ="2303";
    private $shop_key  ="6ec64f6f96b6b5a8";
    private $send_url  = "http://union.fanli.com/dingdan/push/shopid/";
    private $rate = 0.05;

    /**
     * 异步发送订单数据
     * @param $cpsOrder
     * @param $orderData
     * @param $goodsData
     * @return bool
     */
    public function asynsend($cpsOrder,$orderData,$goodsData)
    {
        $sendData = [];
        $refer_args = json_decode($cpsOrder['refer_args'], true);

        $order = array();
        $order['s_id'] = $this->s_id;
        $order['order_id_parent'] = $orderData['order_no'];
        $order['order_id'] = $orderData['order_no'];
        $order['order_time'] = $cpsOrder['order_create_time'];
        $order['uid'] = $refer_args['uid'];
        $order['uname'] = $orderData['user_id'];
        $order['tc'] = $refer_args['tc'];
        $order['pay_time'] = $orderData['pay_time'] && $orderData['pay_time'] != '0000-00-00 00:00:00' ? $orderData['pay_time'] : '';
        $order['status'] = $this->getStatus($orderData['status']);
        $order['lastmod'] = $orderData['update_time'] ? $orderData['update_time'] : $cpsOrder['order_create_time'];
        $order['is_newbuyer'] = 2;
        $order['platform'] = $orderData['order_from'] == 'pc' ? 1 : 2;

        $products = [];
        if($goodsData){
            $order_goods_model = new OrderGoods();
            foreach($goodsData as $j=>$g){
                $product = [];
                $product['pid'] = $g['goods_id'];
                $goods_array = json_decode($g['goods_array'], true);
                $product['title'] = $goods_array['name'];
                $product['category'] = 1;
                $product['category_title'] = '扫地机器人';
                $product['url'] = goods_url($g['goods_id']);
                $product['num'] = $g['goods_nums'];
                $product['price'] = $g['real_price'];
                $cost_price = $order_goods_model->getGoodsCostPriceByGoodsId($orderData,$goodsData,$g['goods_id']);
                $product['real_pay_fee'] = priceFormat($cost_price['price'] * $g['goods_nums']);
                $rate = $this->getCommissionRate($g['goods_id']);
                $product['commission'] = priceFormat($product['real_pay_fee'] * $rate);
                $product['comm_type'] = 'A';
                $products['product_item_'.$j] = $product;
            }
        }

        $order['products'] = $products;
        $sendData['order_item_0'] = $order;
        $content = $this->xml_encode($sendData);
        $post['content'] = $content;

        //记录日志
        $logData = array('cps_order_id'=> $cpsOrder['id'], 'order_id'=> $cpsOrder['order_id'],'send_data'=>json_encode($post),'return_data'=> '', 'add_time'=> date("Y-m-d H:i:s"));
        $cps_order_log_model = new CpsOrderLog();
        $log_id = $cps_order_log_model->baseInsert($cps_order_log_model->tableName(),$logData);
        $result = $this->request($this->send_url.$this->s_id,$post);

        //更新日志
        $logUpdateData = array('return_data'=> json_encode($result));
        $cps_order_log_model->baseUpdate($cps_order_log_model->tableName(),$logUpdateData,"id = :id", array(":id"=> $log_id));

        $result_xml = simplexml_load_string($result);
        $success = $result_xml->error_code == 1 ? true : false;
        return $success;
    }

    /**
     * 映射订单状态到CPS状态
     * @param $order_status  1生成订单,2支付订单,3取消订单(客户触发),4作废订单(管理员触发),5完成订单,6退款,7部分退款,8订单关闭(售后退款完成触发)
     * @return int  //1 已下单；2 已付款；3 已消费；4 已发货；5 已确认收货；6 维权退货
     */
    private function getStatus($order_status){
        $cps_status = 0;
        if(in_array($order_status,[1,3,4])){
            $cps_status = 1;
        }

        if(in_array($order_status,[2,5,7])){
            $cps_status = 2;
        }

        if(in_array($order_status,[6,8])){
            $cps_status = 6;
        }

        return $cps_status;
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $encoding 数据编码
     * @param string $root 根节点名
     * @return string
     */
    private function xml_encode($data, $encoding='utf-8', $root='orders') {
        $xml    = '<?xml version="1.0" encoding="' . $encoding . '"?>';
        $xml   .= '<' . $root . ' version="4.0">';
        $xml   .= $this->data_to_xml($data);
        $xml   .= '</' . $root . '>';
        return $xml;
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    private function data_to_xml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            $key = strpos($key,'order_item_') !== false ? 'order' : $key;
            $key = strpos($key,'product_item_') !== false ? 'product' : $key;
            $xml    .=  "<$key>";

            $xml    .=  ( is_array($val) || is_object($val)) ? $this->data_to_xml($val) : $val;
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }

    /**
     * Notes:获取实际比例
     * @param $goods_id
     * @return float
     */
    private function getCommissionRate($goods_id){
        $mapping['2272'] = 0.6;
        $mapping['2094'] = 0.2;
        $rate = isset($mapping[$goods_id]) ? $mapping[$goods_id] : $this->rate;

        return $rate;
    }
}