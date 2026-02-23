<?php
namespace backend\components\cps;

use Yii;
use yii\db\Expression;
/**
 * cps基类
 * */
class CpsBase
{
    protected $cookie_domain = ".ecovacs.cn";

    public function go($params)
    {

    }

    public function query($params)
    {

    }

    public function send($orderData, $goodsData)
    {

    }

    /**
     * 添加订单记录
     * @param $orderData
     */
    public function addCpsOrder($orderData)
    {

    }

    /**
     * 获取cps实例
     * @param $union_id
     * @return mixed
     */
    public static function getCpsClass($union_id)
    {
        $action_type = preg_replace ( '/_/ ',  ' ' ,  $union_id );
        $integralClassName = "Cps".ucwords($action_type);
        $integralClassName = preg_replace ( '/ / ',  '' ,  $integralClassName );
        $namespace = __NAMESPACE__.'\\';
        $integralClassName = $namespace.$integralClassName;//必须带命名空间
        $integralClassName =  class_exists($integralClassName) ? $integralClassName : $namespace.'CpsBase';
        $integralClass = new $integralClassName();
        return $integralClass;
    }

    /**
     * 发送请求
     * @param $url
     * @param null $post
     * @return mixed
     */
    public function request($url, $post = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1); // 启用POST提交
            $data = $this->GetUrlencodedString($post);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $rst = curl_exec($ch);
        curl_close($ch);
        return $rst;
    }
    
    /**
     * 发送请求
     * @param $url
     * @param null $post
     * @return mixed
     */
    public function duomaiRequest($url, $post = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($post) {
            curl_setopt($ch, CURLOPT_POST, 1); // 启用POST提交
            $data = $this->GetUrlencodedString($post);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $rst = curl_exec($ch);
        curl_close($ch);
        return $rst;
    }

    /**
     * @brief 对字符串进行URL编码，遵循rfc1738 urlencode
     * @param $params
     * @return URL编码后的字符串
     */
    function GetUrlencodedString($params)
    {
        $normalized = array();
        foreach ($params as $key => $val) {
            $normalized[] = $key . "=" . rawurlencode($val);
        }

        return implode("&", $normalized);
    }

    /**
     * 整理订单数据
     * @param $orderArr
     * @param $orderGoodsArr
     * @return array
     */
    public function getMergedOrder( $orderArr,$orderGoodsArr)
    {
        $mergedOrderData = array();
        foreach($orderArr as $order){
            $order_id = $order["id"];
            $goods_list = array();
            foreach($orderGoodsArr as $orderGoods){
                if($orderGoods['order_id'] == $order_id){
                    $goods_list[] = $orderGoods;
                }
            }
            $order['goods_list'] = $goods_list;
            $mergedOrderData[$order_id] = $order;
        }

        return $mergedOrderData;
    }

}