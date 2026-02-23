<?php
/**
 * 运费
 */
namespace backend\components;

use common\models\DeliveryPrice;
use common\models\Order;

class Freight
{

    /**
     * 获取运费信息
     *
     * @param  float $order_amount   订单金额
     * @param  int   $order_type     订单类型
     * @param  array $order_goods    订单商品
     * @param  string $area_id    收货地址信息
     * @return float
     */
    public function calculateFreightAmount($order_amount, $order_type, $order_goods, $area_id){
        // 运费金额
        $freight_amount = 0.00;

        // 免运费额度
        $delivery_price_obj = new DeliveryPrice();
        $free_freight_amount = $delivery_price_obj->getFreeFreightAmount();
    
        // 是否属于免运费
        $order_obj = new Order();
        if($order_obj->checkFreeFreightOrderType($order_type) == true){
            return $freight_amount;
        }

        // 是否只有虚拟商品
        $is_virtual_goods = true;
        foreach($order_goods as $row){
            if($row['goods_type'] != 5){
                $is_virtual_goods = false;
            }
        }
    
        // 虚拟商品免运费
        if($is_virtual_goods == true){
            return $freight_amount;
        }

        // 订单金额满足要求免运费
        if($order_amount >= $free_freight_amount){
            return $freight_amount;
        }

        // 根据地区id 获取运费
        $freight_amount = $delivery_price_obj->getDeliveryPrice($area_id);
        return $freight_amount;
    }
}