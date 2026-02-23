<?php
namespace backend\components\cps;
use common\models\CpsOrder;
use common\models\CpsOrderLog;
use common\models\OrderGoods;

/**
 * 多麦CPS
 * */
class CpsLinktech extends CpsBase
{
    private $union_id = "linktech";
    private $mid  ="covacs";
    private $send_url  = "http://service.linktech.cn/purchase_cps.php";
    private $cookie_expire_day = 7;
    private $white_list = array("127.0.0.1",
        "10.88.98.221",
        "59.151.113.167",
        "59.151.113.168",
        "59.151.113.169",
        "59.151.113.170",
        "59.151.113.171",
        "59.151.113.172",
        "59.151.113.173",
        "59.151.113.174",
        "59.151.113.175",
        "59.151.113.176",
        "59.151.113.177",
        "59.151.113.178",
        "59.151.113.179",
        "59.151.113.180",
        "59.151.113.181",
        "59.151.113.182",
        "59.151.113.183",
        "59.151.113.184",
        "59.151.113.185",
        "59.151.113.186",
        "59.151.113.187",
        "59.151.113.188",
        "59.151.113.189",
        "59.151.113.190",
        "101.231.53.62",
        "119.253.42.34",
        "119.253.42.35"
    );





    /**
     * 异步发送订单数据
     * @param $cpsOrder
     * @param $orderData
     * @param $goodsData
     */
    public function asynsend($cpsOrder,$orderData,$goodsData)
    {
        $sendData = array();

        $refer_args = json_decode($cpsOrder['refer_args'], true);
        $sendData['a_id'] = isset($refer_args['a_id']) ? $refer_args['a_id']: '';
        $sendData['m_id'] = $this->mid;
        $sendData['mbr_id'] = 1;
        $sendData['o_cd'] = $orderData['order_no'];

        if($goodsData){
            $goods_id_arr = $goods_name_arr = $goods_price_arr = $goods_ta_arr = $goods_cate_arr = array();
            $order_goods_model = new OrderGoods();
            foreach($goodsData as $g){
                $goods_id_arr[] = $g['goods_id'];
                $goods_array = json_decode($g['goods_array'], true);
                $goods_name_arr[] = $goods_array['name'];
                $cost_price =$order_goods_model->getGoodsCostPriceByGoodsId($orderData,$goodsData,$g['goods_id']);
                $goods_price_arr[] = $cost_price['price'];
                $goods_ta_arr[] = $g['goods_nums'];
                $goods_cate_arr[] = 1;
            }
        }

        $sendData['p_cd'] = implode("||", $goods_id_arr);
        $sendData['price'] = implode("||", $goods_price_arr);
        $sendData['it_cnt'] = implode("||", $goods_ta_arr);
        $sendData['c_cd'] = implode("||", $goods_cate_arr);

        //记录推送订单时候的商品数据
        $refer_args['postinfo'] = http_build_query($sendData);
        $cps_order_model = new CpsOrder();
        $cps_order_model->baseUpdate($cps_order_model->tableName(),array('refer_args'=> json_encode($refer_args)),'order_id =:order_id',array(":order_id"=>$cpsOrder['order_id']));

        $result = $this->request($this->send_url,$sendData);
        //记录日志
        $cps_order_log_model = new CpsOrderLog();
        $logData = array('cps_order_id'=> $cpsOrder['id'], 'order_id'=> $cpsOrder['order_id'],'send_data'=>json_encode($sendData),'return_data'=> json_encode($result), 'add_time'=> date("Y-m-d H:i:s"));
        $cps_order_log_model->baseInsert($cps_order_log_model->tableName(),$logData);

        $success = true;
        return $success;
    }

}