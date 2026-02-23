<?php

namespace backend\models;

use common\components\PlatformTradeCommonV4;
use common\models\OkexOrder;
use Yii;

/**
 * This is the model class for table "sea_okex_total_order".
 *
 * @property int $id
 * @property string $coin
 * @property int $admin_user_id 后台管理员用户ID
 * @property string $group_id
 * @property string $level
 * @property string $order_id
 * @property string $instrument_id
 * @property string $filled_qty
 * @property string $price_avg
 * @property string $contract_val
 * @property string $fee
 * @property string $order_type
 * @property string $price
 * @property string $size
 * @property string $state
 * @property string $status
 * @property string $is_deleted 判断是否删除Y-是 N-否
 * @property string $is_double 是否有双边订单，双边订单group_id保持一直
 * @property string $is_cancel
 * @property string $timestamp
 * @property string $trigger_price
 * @property string $type
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class OkexTotalOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_okex_total_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['coin', 'admin_user_id', 'order_id', 'instrument_id', 'filled_qty', 'price_avg', 'contract_val', 'fee', 'order_type', 'price', 'size', 'state', 'status', 'timestamp', 'trigger_price', 'type'], 'required'],
            [['admin_user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['coin', 'group_id', 'level', 'order_id', 'instrument_id', 'filled_qty', 'price_avg', 'contract_val', 'fee', 'order_type', 'price', 'size', 'state', 'status', 'timestamp', 'trigger_price', 'type'], 'string', 'max' => 50],
            [['is_deleted', 'is_double', 'is_cancel'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coin' => 'Coin',
            'admin_user_id' => 'Admin User ID',
            'group_id' => 'Group ID',
            'level' => 'Level',
            'order_id' => 'Order ID',
            'instrument_id' => 'Instrument ID',
            'filled_qty' => 'Filled Qty',
            'price_avg' => 'Price Avg',
            'contract_val' => 'Contract Val',
            'fee' => 'Fee',
            'order_type' => 'Order Type',
            'price' => 'Price',
            'size' => 'Size',
            'state' => 'State',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'is_double' => 'Is Double',
            'is_cancel' => 'Is Cancel',
            'timestamp' => 'Timestamp',
            'trigger_price' => 'Trigger Price',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 判断订单的指定状态是否存在
     * @param $order_id
     * @param $state
     */
    public function checkExists($order_id,$state){
        $params['cond'] =  'order_id =:order_id AND state=:state';
        $params['args'] = [':order_id'=>$order_id,':state'=>$state];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     *同步订单信息
     * @param $api_key_info
     * @param $limit
     * @param $after
     * @param $before
     * @return mixed
     * Note://-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
     */
    public function syncOrderByAdminApiInfo($api_key_info,$after=''){


        $before='';
        $limit=100 ;
        $trade_obj = new PlatformTradeCommonV4();
        $coin = $api_key_info['coin'];

        $trade_obj->setConfigInfo($api_key_info['admin_user_id'],$api_key_info,$coin,'up') ;
        $res5 = $trade_obj->getOrderListByState(2,$limit,$after,$before);
        //$res1 = $trade_obj->getOrderListByState(-2);

        //$res2 = $trade_obj->getOrderListByState(-1);
        //$res3 = $trade_obj->getOrderListByState(0,$limit,$after,$before);
        //$res4 = $trade_obj->getOrderListByState(1,$limit,$after,$before);

        //$res = array_merge($res1,$res2,$res3,$res4,$res5);
        $res = array_merge($res5);
        $res = array_sort($res,'timestamp');

        $order_id = '' ;
        if($res){
            $i = 0;
            $admin_user_id = $api_key_info['admin_user_id'] ;
            foreach($res as $v){

                if($i ==0){
                    $order_id = $v['order_id'] ;
                }

                $i++ ;

                // 判断是否存在
                if($this->checkExists($v['order_id'],$v['state'])){
                    continue ;
                }
                $add_data['coin'] = $coin ;
                $add_data['admin_user_id'] = $admin_user_id ;
                $add_data['order_id'] = $v['order_id'] ;
                $add_data['instrument_id'] = $v['instrument_id'] ;
                $add_data['filled_qty'] = $v['filled_qty'] ;
                $add_data['price_avg'] = $v['price_avg'] ;
                $add_data['contract_val'] = $v['contract_val'] ;
                $add_data['fee'] = $v['fee'] ;
                $add_data['order_type'] = $v['order_type'] ;
                $add_data['price'] = $v['price'] ;
                $add_data['size'] = $v['size'] ;
                $add_data['state'] = $v['state'] ;
                $add_data['status'] = $v['status'] ;
                $add_data['timestamp'] = $v['timestamp'] ;
                $add_data['trigger_price'] = $v['trigger_price'] ;
                $add_data['type'] = $v['type'] ;
                $add_data['create_time'] = date('Y-m-d H:i:s') ;
                $add_data['modify_time'] = date('Y-m-d H:i:s') ;
                $this->baseInsert(self::tableName(),$add_data);

            }
        }

        return $order_id ;

    }

    /**
     * 返回所有类型
     * @return array
     */
    public function getTotalState(){
        //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
        return  [
            '-2' =>'失败',
            '-1' =>'撤单成功',
            '0' =>'等待成交',
            '1' =>'部分成交',
            '2' =>'完全成交',
            '3' =>'下单中',
            '4' =>'撤单中',
        ];
    }

    public function getStateName($state){
        $arr = $this->getTotalState();
        $str = isset($arr[$state]) ? $arr[$state]:'';
        return $str ;
    }

    /**
     * 返回所有类型
     * @return array
     */
    public function getTotalType(){
        return  [
            '1' =>'买入开多',
            '2' =>'买入开空',
            '3' =>'卖出平多',
            '4' =>'卖出平空',
        ];
    }

    public function getTypeName($type){
        $arr = $this->getTotalType();
        $str = isset($arr[$type]) ? $arr[$type]:'';
        return $str ;
    }

    /**
     * 获取指定状态的订单信息
     * @param $total_pending_list
     * @param $type
     * @return mixed
     */
    public function getPendingListByType($total_pending_list,$type){

        if(!$total_pending_list){
            return [];
        }
        $type = $type=='up' ? 3:4 ;
        $res = [];
        foreach($total_pending_list as $v){
            if($type == $v['type']){
                $res[] = $v ;
            }
        }
        return $res ;
    }

    /**
     * 获取指定订单的总数目
     * @param $pending_list
     * @return int
     */
    public function getTotalNumByPendingList($pending_list){

        $total_num = 0;

        if(!$pending_list){
            return $total_num ;
        }

        foreach($pending_list as $v){
            $total_num += $v['size'] ;
        }

        return $total_num ;
    }

    /**
     *
     * @param $pending_order_list
     * @param $api_key_info
     * @param $type
     * @return mixed
     */
    public function cancelOrderByList($pending_order_list,$api_key_info,$type){
        if(!$pending_order_list){
            return true ;
        }

        $admin_user_id = $api_key_info['admin_user_id'];
        $coin = $api_key_info['coin'];
        $trade_obj = new PlatformTradeCommonV4();
        $type = strtolower($type);
        $trade_obj->setConfigInfo($admin_user_id,$api_key_info,$coin,$type) ;
        foreach($pending_order_list as $v){
            $order_id = $v['order_id'] ;
            $trade_obj->cancelByOrderId($order_id,$coin) ;
        }

        // 查询订单状态
        foreach($pending_order_list as $v){
            $order_id = $v['order_id'] ;
            $service_order_info = $trade_obj->getInfoByOrderIdFromService($order_id,$coin) ;
            if(!in_array($service_order_info['state'],[-2,-1])){
                return $this->cancelOrderByList($pending_order_list,$api_key_info,$type);
            }
        }

        return true ;
    }

    /**
     * 新增标记位
     * @param $admin_user_id
     * @param $coin
     * @param $table_name
     * @return string
     */
    public function addMarkRecord($admin_user_id,$coin,$table_name){
        $model = new AdminApiKey();
        $now = date('Y-m-d H:i:s');


        $buy_add_data['order_id'] =  0 ;
        $buy_add_data['admin_user_id'] =  $admin_user_id ;
        $buy_add_data['coin'] =  $coin ;
        $buy_add_data['create_time'] = $now ;
        $buy_add_data['modify_time'] = $now ;

        return $model->baseInsert($table_name,$buy_add_data);
    }

    /**
     * 修正订单
     * @param $api_key_id
     * @param $type
     * @return mixed
     * Note:-2:失败 -1:撤单成功   0:等待成交 1:部分成交  2:完全成交  3:下单中  4:撤单中  6: 未完成（等待成交+部分成交） 7:已完成（撤单成功+完全成交）
     */
    public function fixOrderByAdminApiKey($api_key_id,$type){

        $api_key_obj =  new AdminApiKey() ;
        $api_key_info = $api_key_obj->getInfoById($api_key_id);
        $admin_user_id = $api_key_info['admin_user_id'];
        $coin = $api_key_info['coin'];
        $trade_obj = new PlatformTradeCommonV4();
        $type = strtolower($type);
        $trade_obj->setConfigInfo($admin_user_id,$api_key_info,$coin,$type) ;

        $total_table_info = $trade_obj->returnBuySellTableName($type);
        $buy_table_name = $total_table_info['buy_table_name'];
        $sell_table_name = $total_table_info['sell_table_name'];

        $okex_model = new OkexOrder();

        //查看当前委托数量和数据库中的是否保持一致
        $last_info = $okex_model->getLastInfo($admin_user_id,$buy_table_name,$coin) ;
        if($last_info && $last_info['order_id']){
            $group_id = $last_info['group_id'] ;
            $buy_order_list = $okex_model->getOrderListByGroupId($admin_user_id,$buy_table_name,$group_id);
        }else{
            $buy_order_list = [] ;
        }

        // 查询所有
        $total_pending_order_list = $trade_obj->getOrderListByState(0);

        // 判断是否有挂单 查询状态为0 的订单信息
        $pending_order_list = $this->getPendingListByType($total_pending_order_list,$type);

        // 查询多单空单信息
        $holding_list = $trade_obj->getAllHoldingInfo();

        // 查看当前持仓
        $holding_info = $type=='up' ? $holding_list['up_order'] : $holding_list['down_order'] ;

        // 执行撤单
        $this->cancelOrderByList($pending_order_list,$api_key_info,$type);


        if(!$holding_info){
            // 新增标记位
            return $this->addMarkRecord($admin_user_id,$coin,$buy_table_name);
        }

        $delete_data['is_deleted'] = 'Y';
        $delete_data['state'] = -2;
        $delete_data['modify_time'] = date('Y-m-d H:i:s') ;
        $buy_order_id = isset($last_info['order_id']) ? $last_info['order_id'] : 0 ;
        $this->baseUpdate($sell_table_name,$delete_data,'buy_id=:buy_id',[':buy_id'=>$buy_order_id]);
        return $this->addBuyRecordByHoldingInfo($holding_info,$last_info,$buy_table_name,$api_key_info);

    }

    /**
     * 新增修正记录
     * @param $holding_info
     * @param $last_info
     * @param $buy_table_name
     * @param $api_key_info
     * @return mixed
     */
    public function addBuyRecordByHoldingInfo($holding_info,$last_info,$buy_table_name,$api_key_info){

        $coin = $api_key_info['coin'];
        $admin_user_id = $api_key_info['admin_user_id'];

        //当前时间
        $now  = date('Y-m-d H:i:s');

        $avg_cost = $holding_info['avg_cost'];
        $filled_qty = $holding_info['position'];

        // 获取指定数量的级别信息
        $level = $this->getLevelByFilledQty($filled_qty,$api_key_info);
        $insert_data['coin'] = $coin ;
        $insert_data['admin_user_id'] = $admin_user_id;
        $insert_data['group_id'] = $last_info['group_id']+1;
        $insert_data['level'] = $level;
        $insert_data['order_id'] = $last_info['order_id'] ;
        $insert_data['instrument_id'] = $last_info['instrument_id'] ;
        $insert_data['contract_val'] = $last_info['contract_val'] ;
        $insert_data['fee'] = $last_info['fee'] ;
        $insert_data['filled_qty'] = $filled_qty ;
        $insert_data['order_type'] = $last_info['order_type'] ;
        $insert_data['price'] = $avg_cost;
        $insert_data['price_avg'] = $avg_cost ;
        $insert_data['size'] = $filled_qty ;
        $insert_data['state'] = $last_info['state'] ;
        $insert_data['status'] = $last_info['status'] ;
        $insert_data['timestamp'] = $last_info['timestamp'] ;
        $insert_data['trigger_price'] = 0.00 ;
        $insert_data['type'] = $last_info['type'] ;
        $insert_data['modify_time'] = $now ;
        $insert_data['create_time'] = $now ;

        return $this->baseInsert($buy_table_name,$insert_data) ;
    }

    /**
     * 计算盈利值不包含书续费
     * @param $order_info
     * @return  mixed
     */
    public function calcTotalEarn($order_info){
        $type = $order_info['type'] ;
        if($type <=2){
            return 0 ;
        }
        $filled_qty = $order_info['filled_qty'] ;
        $contract_val = $order_info['contract_val'] ;
        $price_avg = $order_info['price_avg'];
        $percent = 0.00175;
        if($type ==3){
            $total = $filled_qty * $contract_val *($price_avg - $price_avg/(1+$percent));
        }else{
            $total = $filled_qty * $contract_val *( $price_avg/(1-$percent) - $price_avg);
        }

        $total = sprintf('%0.7f',$total);
        return $total ;
    }

    public function calcTotalEarnByCoin($order_info){

        $type = $order_info['type'] ;
        if($type <=2){
            return 0 ;
        }
        $filled_qty = $order_info['filled_qty'] ;
        $contract_val = $order_info['contract_val'] ;
        $price_avg = $order_info['price_avg'];
        $percent = 0.00175;

        //多仓收益=面值*开仓张数／开仓价格-面值*开仓张数／平仓价格
        // 空仓收益=面值*开仓张数／平仓价格-面值*开仓张数／开仓价格
        if($price_avg > 0){
            if($type ==3){
                $total = $filled_qty * $contract_val /( $price_avg/(1+$percent)) - $filled_qty * $contract_val / $price_avg;
            }else{
                $total = $filled_qty * $contract_val /$price_avg - $filled_qty * $contract_val /( $price_avg/(1-$percent))   ;
            }
        }else{
            $total = 0 ;
        }


        $total = sprintf('%0.7f',$total);
        return $total ;
    }

    /**
     * 同步全部订单
     */
    public function syncAllOrder(){

        $admin_user_obj = new AdminApiKey();
        for ($i=1;$i<=10;$i++){
            $admin_api_key_info = $admin_user_obj->getRowByAdminUserId($i);
            $this->syncOrderByAdminApiInfo($admin_api_key_info);
        }
    }


    /**
     * 获取指定数目的未同步订单
     * @param int $limit
     * @return mixed
     */
    public function getUnSyncOrderList($limit =20){

        $params['cond'] = 'is_sync=:is_sync';
        $params['args'] = [':is_sync'=>'N'];
        $params['limit'] = $limit ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据购买数量返回指定的层级
     * @param $filled_qty
     * @param $api_key_info
     * @return int
     */
    public function getLevelByFilledQty($filled_qty,$api_key_info){

        $arr = [1,2,3,5,8,13];

        // 基础购买顺眼
        $base_buy_num = $api_key_info['base_buy_num'];
        $filled_qty =  ceil($filled_qty/$base_buy_num);

        $total =  0;
        $level = 1 ;
        foreach($arr as $k=>$v){

          $total += $v;
          if($filled_qty >=$total){
              $level = $k+1 ;
          }
        }

        return  $level ;
    }


}
