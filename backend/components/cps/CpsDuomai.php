<?php
namespace backend\components\cps;
use common\models\CpsOrderLog;
use common\models\OrderGoods;

/**
 * 多麦CPS
 * */
class CpsDuomai extends CpsBase
{
    private $union_id = "duomai";
    private $mid  ="4911";
    private $send_url  = "https://www.duomai.com/api/order.php";
    private $hash_code = "9de23805a3609a3496783bdb8e1e5839";
    private $cookie_expire_day = 30;
    private $rate = 0.06;
    private $white_list = array("127.0.0.1","10.88.98.221","115.236.163.195","60.191.0.168","115.236.76.138");


    /**
     * 异步发送订单数据
     * @param $cpsOrder
     * @param $orderData
     * @param $goodsData
     */
    public function asynsend($cpsOrder,$orderData,$goodsData)
    {
        $sendData = array();
        $sendData['hash'] = $this->hash_code;

        $refer_args = json_decode($cpsOrder['refer_args'], true);
        $sendData['euid'] = isset($refer_args['refer_euid']) ? $refer_args['refer_euid']: '';
        $sendData['order_sn'] = $orderData['order_no'];
        $sendData['order_time'] = $cpsOrder['order_create_time'];
        $sendData['click_time'] = $cpsOrder['refer_time'];
        $sendData['orders_price'] = $orderData['order_amount'];
        $sendData['discount_amount'] = 0;
        $sendData['is_new_custom'] = 0;
        $sendData['order_status'] = 0;
        $sendData['referer'] = $cpsOrder['refer_url'];

        if($goodsData){
            $goods_id_arr = $goods_name_arr = $goods_price_arr = $goods_ta_arr = $goods_cate_arr = $totalPrice_arr = $commission_arr = array();
            $order_goods_model = new OrderGoods();
            foreach($goodsData as $g){
                $goods_id_arr[] = $g['goods_id'];
                $goods_array = json_decode($g['goods_array'], true);
                $goods_name_arr[] = $goods_array['name'];
                $cost_price = $order_goods_model->getGoodsCostPriceByGoodsId($orderData,$goodsData,$g['goods_id']);

                $goods_price_arr[] = $cost_price['price'];
                $goods_ta_arr[] = $g['goods_nums'];
                $goods_cate_arr[] = 1;
                $totalPrice_arr[] = $cost_price['totalAmount'];
                $commission_arr[] = $cost_price['totalAmount'] * $this->rate;
            }
        }

        $sendData['goods_id'] = implode("|", $goods_id_arr);
        $sendData['goods_name'] = implode("|", $goods_name_arr);
        $sendData['goods_price'] = implode("|", $goods_price_arr);
        $sendData['goods_ta'] = implode("|", $goods_ta_arr);
        $sendData['goods_cate'] = implode("|", $goods_cate_arr);
        $sendData['totalPrice'] = implode("|", $totalPrice_arr);
        $sendData['commission'] = implode("|", $commission_arr);
        $result = $this->duomaiRequest($this->send_url,$sendData);
        //记录日志
        $logData = array('cps_order_id'=> $cpsOrder['id'], 'order_id'=> $cpsOrder['order_id'],'send_data'=>json_encode($sendData),'return_data'=> json_encode($result), 'add_time'=> date("Y-m-d H:i:s"));
        $cps_order_log_model = new CpsOrderLog();
        $cps_order_log_model->baseInsert($cps_order_log_model->tableName(),$logData);

        $success = $result == "推送成功" ? true : false;
        return $success;
    }

}