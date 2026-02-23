<?php
namespace backend\components;


use common\models\Category;
use common\models\Goods;
use common\models\Microshop;
use Yii;
use common\models\MsGoods;

class Statistics
{
    /**
     * 综合统计
     * @param $params
     * @return mixed
     */
    public function getIndexSummeryData($params){

        //支付金额
        $params['type'] = 13;

        $return['payed_total_amount'] = $this->getDailyPayedTotalAmount( $params);

        //支付人数
        $return['payed_people_num'] = $this->getDailyPayedPeopleNum( $params);

        //支付订单数
        $return['payed_order_num'] = $this->getDailyPayedOrderNum( $params);

        //支付商品数
        $return['payed_goods_num'] = $this->getDailyPayedGoodsNum( $params);

        //客单价
        $return['avg_order_amount'] = $this->getDailyPayedGoodsPrice( $params);

        //下单金额
        $return['checkout_total_amount'] = $this->getDailyCheckoutTotalAmount( $params);

        //下单人数
        $return['checkout_people_num'] = $this->getDailyCheckoutPeopleNum( $params);


        //debug($return,1);
        return $return;
    }


    /**
     * 每日统计支付总额
     * @param $params
     * @return array
     */
    private function getDailyPayedTotalAmount($params){
        $sql = "select sum(order_amount) as payed_total_amount from sdb_order
                  where pay_status = 1 and type = {$params['type']} and pay_time >= '{$params['start_time']}' and pay_time <= '{$params['end_time']}'";
       if($params['order_from']){
           $sql .= " and order_from = '{$params['order_from']}'";
       }

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryOne();

        $payed_total_amount = isset($result['payed_total_amount']) ? $result['payed_total_amount'] : 0;
        return $payed_total_amount;
    }

    /**
     * 综合统计获取已支付人数
     * @param $params
     * @return array
     */
    private function getDailyPayedPeopleNum($params){
        $sql = "select DISTINCT user_id  from sdb_order
                  where pay_status = 1 and type = {$params['type']} and pay_time >= '{$params['start_time']}' and pay_time <= '{$params['end_time']}'";

        if($params['order_from']){
            $sql .= " and order_from = '{$params['order_from']}'";
        }

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();

        $payed_people_num = $result ? count($result) : 0;

        return $payed_people_num;
    }

    /**
     * 每日统计支付订单数
     * @param $params
     * @return array
     */
    private function getDailyPayedOrderNum($params){
        $sql = "select count(1) as payed_order_num from sdb_order
                  where pay_status = 1 and type = {$params['type']} and pay_time >= '{$params['start_time']}' and pay_time <= '{$params['end_time']}'";

        if($params['order_from']){
            $sql .= " and order_from = '{$params['order_from']}'";
        }

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryOne();

        $payed_order_num = isset($result['payed_order_num']) ? $result['payed_order_num'] : 0;
        return $payed_order_num;
    }


    /**
     * 综合统计支付台数
     * @param $params
     * @return int
     */
    private function getDailyPayedGoodsNum($params){
        $sql = "select sum(goods_nums) as payed_goods_num from sdb_order_goods
                  where  order_id in (select id from sdb_order where pay_status = 1 and type = {$params['type']} and pay_time >= '{$params['start_time']}' and pay_time <= '{$params['end_time']}' ";

        if($params['order_from']){
            $sql .= " and order_from = '{$params['order_from']}'";
        }

        $sql .= ")";

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryOne();
        $payed_goods_num = isset($result['payed_goods_num']) ? $result['payed_goods_num'] : 0;
        return $payed_goods_num;
    }

    /**
     * 综合统计客单价
     * @param $params
     * @return int
     */
    private function getDailyPayedGoodsPrice($params){
        $sql = "select avg(order_amount) as avg_order_amount from sdb_order
                  where pay_status = 1 and type = {$params['type']} and pay_time >= '{$params['start_time']}' and pay_time <= '{$params['end_time']}'";

        if($params['order_from']){
            $sql .= " and order_from = '{$params['order_from']}'";
        }

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryOne();
        $avg_order_amount = isset($result['avg_order_amount']) ? $result['avg_order_amount'] : 0;
        $avg_order_amount = number_format($avg_order_amount,2);
        return $avg_order_amount;
    }


    /**
     * 综合统计下单总额
     * @param $params
     * @return int
     */
    private function getDailyCheckoutTotalAmount($params){
        $sql = "select sum(order_amount) as checkout_total_amount from sdb_order
                  where type = {$params['type']}  and create_time >= '{$params['start_time']}' and create_time <= '{$params['end_time']}'";

        if($params['order_from']){
            $sql .= " and order_from = '{$params['order_from']}'";
        }

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryOne();
        $checkout_total_amount = isset($result['checkout_total_amount']) ? $result['checkout_total_amount'] : 0;
        return $checkout_total_amount;
    }


    /**
     * 综合统计下单人数
     * @param $params
     * @return array
     */
    private function getDailyCheckoutPeopleNum($params){
        $sql = "select DISTINCT user_id  from sdb_order
                  where type = {$params['type']}  and  create_time >= '{$params['start_time']}' and create_time <= '{$params['end_time']}'";

        if($params['order_from']){
            $sql .= " and order_from = '{$params['order_from']}'";
        }

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();

        $checkout_people_num = $result ? count($result) : 0;
        return $checkout_people_num;
    }



    /**
     * 单品统计
     * @param $params
     * @return mixed
     */
    public function getIndexGoodsData($search, $page_params){
        //获取每页的分销商ID
        $goods_obj = new Goods();
        $goods_list = $goods_obj->getList($search, $page_params,"g.id,g.name");

        $goods_ids = [];
        if($goods_list){
            foreach($goods_list as $d){
                $goods_ids[] = $d['id'];
            }
        }

        //批量获取数据
        $goods_list = [];
        if($goods_ids) {
            $goods_list = $goods_obj->getBaseInfoByIdList($goods_ids, "id,name");
            $payed_goods_num = $this->getGoodsPayedGoodsNum($goods_ids, $search['start_time'], $search['end_time']);
            $payed_order_num = $this->getGoodsPayedOrderNum($goods_ids, $search['start_time'], $search['end_time']);
            $payed_total_amount = $this->getGoodsPayedTotalAmount($goods_ids, $search['start_time'], $search['end_time']);
            $payed_people_num = $this->getGoodsPayedPeopleNum($goods_ids, $search['start_time'], $search['end_time']);
        }

        //整理数据
        $goods_data = [];
        if($goods_list){
            foreach($goods_list as $item){
                $tmp = [];
                $tmp['goods_id'] = $item['id'];
                $tmp['goods_name'] = $item['name'];
                $tmp['payed_goods_num'] = isset($payed_goods_num[$item['id']]) ? $payed_goods_num[$item['id']] : 0;
                $tmp['payed_order_num'] = isset($payed_order_num[$item['id']]) ? $payed_order_num[$item['id']] : 0;
                $tmp['payed_total_amount'] = isset($payed_total_amount[$item['id']]) ? $payed_total_amount[$item['id']] : 0;
                $tmp['payed_people_num'] = isset($payed_people_num[$item['id']]) ? $payed_people_num[$item['id']] : 0;

                $goods_data[] = $tmp;
            }
        }

        return $goods_data;
    }


    /**
     * 单品统计支付商品数
     * @param $goods_ids
     * @param $start_time
     * @param $end_time
     * @param $username
     * @return array
     */
    private function getGoodsPayedGoodsNum($goods_ids,$start_time,$end_time){
        $sql = "select sum(goods_nums) as payed_goods_num,og.goods_id from sdb_order_goods og, sdb_order o
                  where o.type = 13 and o.pay_status = 1 and o.pay_time >= '{$start_time}' and o.pay_time <= '{$end_time}' and og.goods_id in (".implode(",",$goods_ids).")
                    and og.order_id = o.id group by og.goods_id";

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();

        $payed_goods_num = [];
        foreach($goods_ids as $id) {
            $payed_goods_num[$id] = 0;
            if($result) {
                foreach ($result as $d) {
                    if ($id == $d['goods_id']) {
                        $payed_goods_num[$d['goods_id']] = $d['payed_goods_num'];
                        break;
                    }
                }
            }
        }

        return $payed_goods_num;
    }

    /**
     * 单品统计支付订单数
     * @param $ms_ids
     * @param $start_time
     * @param $end_time
     * @param $username
     * @return array
     */
    private function getGoodsPayedOrderNum($goods_ids,$start_time,$end_time){
        $sql = "select count(o.id) as payed_order_num,og.goods_id from sdb_order_goods og, sdb_order o
                  where o.type = 13 and o.pay_status = 1 and o.pay_time >= '{$start_time}' and o.pay_time <= '{$end_time}' and og.goods_id in (".implode(",",$goods_ids).")
                    and og.order_id = o.id group by og.goods_id";

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();

        $payed_order_num = [];
        foreach($goods_ids as $id) {
            $payed_order_num[$id] = 0;
            if($result) {
                foreach ($result as $d) {
                    if ($id == $d['goods_id']) {
                        $payed_order_num[$d['goods_id']] = $d['payed_order_num'];
                        break;
                    }
                }
            }
        }

        return $payed_order_num;
    }

    /**
     * 单品支付总金额
     * @param $goods_ids
     * @param $start_time
     * @param $end_time
     * @param $username
     * @return array
     */
    private function getGoodsPayedTotalAmount($goods_ids,$start_time,$end_time){
        $sql = "select sum(goods_nums * real_price) as payed_total_amount, goods_id from sdb_order_goods
                  where goods_id in (".implode(",",$goods_ids).")
                    and order_id in (select id from sdb_order where type = 13 and pay_status = 1 and pay_time >= '{$start_time}' and pay_time <= '{$end_time}') group by goods_id";

        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();

        $payed_total_amount = [];
        foreach($goods_ids as $id) {
            $payed_total_amount[$id] = 0;
            if($result) {
                foreach ($result as $d) {
                    if ($id == $d['goods_id']) {
                        $payed_total_amount[$d['goods_id']] = $d['payed_total_amount'];
                        break;
                    }
                }
            }
        }

        return $payed_total_amount;
    }


    /**
     * 综合统计获取已支付人数
     * @param $params
     * @return array
     */
    private function getGoodsPayedPeopleNum($goods_ids,$start_time,$end_time){
        $sql = "select count(o.user_id) as payed_people_num,og.goods_id from sdb_order_goods og, sdb_order o
                  where o.type = 13 and o.pay_status = 1 and o.pay_time >= '{$start_time}' and o.pay_time <= '{$end_time}' and og.goods_id in (".implode(",",$goods_ids).")
                    and og.order_id = o.id group by og.goods_id";


        $command = Yii::$app->db->createCommand($sql);
        $result = $command->queryAll();

        $payed_people_num = [];
        foreach($goods_ids as $id) {
            $payed_people_num[$id] = 0;
            if($result) {
                foreach ($result as $d) {
                    if ($id == $d['goods_id']) {
                        $payed_people_num[$d['goods_id']] = $d['payed_people_num'];
                        break;
                    }
                }
            }
        }

        return $payed_people_num;
    }



}
