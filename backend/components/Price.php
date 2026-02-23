<?php
namespace backend\components;
use common\models\Goods;
use common\models\Member;
use common\models\PromotionPrice;

class Price
{
    /**
     * 计算普通订单最终价格
     * @param $params
     * @return string
     */
    public function calcNomralOrder($params)
    {
        $final = array();
        $order_amount = 0;
        $goods_amount = 0;

        $user_id = $params['user_id'];
        $post_goods = $params['goods'];
        $discount = $params['discount'];

        $memberDb = new Member();
        $memberInfo = $memberDb->getUserInfoById($user_id);

        $group_id = isset($memberInfo['group_id']) ? $memberInfo['group_id'] : 0;

        //整理数据
        $goodsDb = new Goods();
        if($post_goods){
            foreach($post_goods as $d){
                $goods_id = $d[0];
                $goods_num = $d[1];
                $goods_info = $goodsDb->getInfoById($goods_id);
                $goods_price = $this->getGoodsRealPrice($group_id,$goods_id);//$goods_info['sell_price'];
                $goods_amount = bcadd($goods_amount,$goods_num*$goods_price,2);
            }
        }

        $order_amount = $goods_amount + $discount;
        $final['order_amount'] = $order_amount;
        $final['discount'] = $discount;
        $final['promotion'] = 0;

		 return $final;
    }

    /**
     * 获取商品真实价格
     * @param $group_id
     * @param $goods_id
     * @return int
     * 促销会员>>促销价>>会员价>>商品本身价格
     */
    public function getGoodsRealPrice($group_id,$goods_id){
        $real_price = 0;

        $pro_prcie_obj = new PromotionPrice();
        $pro_price_arr = $pro_prcie_obj->getProPriceArr($goods_id,$group_id);
        //debug($pro_price_arr,1);
        if($pro_price_arr){
            $real_price = $pro_price_arr['price'];
        }else{

        }

        return $real_price;
    }

}
