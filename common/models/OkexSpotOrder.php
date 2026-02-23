<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_okex_spot_order".
 *
 * @property int $id
 * @property int $user_id 后台管理员用户ID
 * @property string $client_oid
 * @property string $created_at
 * @property string $fee
 * @property string $fee_currency
 * @property string $filled_notional
 * @property string $filled_size
 * @property string $funds
 * @property string $instrument_id
 * @property string $notional
 * @property string $order_id
 * @property string $order_type
 * @property string $price
 * @property string $price_avg
 * @property string $product_id
 * @property string $rebate
 * @property string $rebate_currency
 * @property string $side
 * @property string $size
 * @property string $state
 * @property string $status
 * @property string $timestamp
 * @property string $type
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class OkexSpotOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_okex_spot_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'client_oid', 'created_at', 'fee', 'fee_currency', 'filled_notional', 'filled_size', 'funds', 'instrument_id', 'notional', 'order_id', 'order_type', 'price', 'price_avg', 'product_id', 'rebate', 'rebate_currency', 'side', 'size', 'state', 'status', 'timestamp', 'type'], 'required'],
            [['user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['client_oid', 'created_at', 'fee', 'fee_currency', 'filled_notional', 'filled_size', 'funds', 'instrument_id', 'notional', 'order_id', 'order_type', 'price', 'price_avg', 'product_id', 'rebate', 'rebate_currency', 'side', 'size', 'state', 'status', 'timestamp', 'type'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'client_oid' => 'Client Oid',
            'created_at' => 'Created At',
            'fee' => 'Fee',
            'fee_currency' => 'Fee Currency',
            'filled_notional' => 'Filled Notional',
            'filled_size' => 'Filled Size',
            'funds' => 'Funds',
            'instrument_id' => 'Instrument ID',
            'notional' => 'Notional',
            'order_id' => 'Order ID',
            'order_type' => 'Order Type',
            'price' => 'Price',
            'price_avg' => 'Price Avg',
            'product_id' => 'Product ID',
            'rebate' => 'Rebate',
            'rebate_currency' => 'Rebate Currency',
            'side' => 'Side',
            'size' => 'Size',
            'state' => 'State',
            'status' => 'Status',
            'timestamp' => 'Timestamp',
            'type' => 'Type',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 判断订单是否已经存在
     * @param $order_id
     * @return mixed
     */
    public function checkExistsByOrderId($order_id){
        $params['cond'] = 'order_id =:order_id';
        $params['args'] = [':order_id'=>$order_id];
        $params['fields'] ='id';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 同步用户订单
     * @param $user_id
     * @param $user_platform_id
     * @param $list
     * @return bool
     */
    public function downloadOrder($user_id,$user_platform_id,$list){

        if(!$list){
            return true ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');

        // 对列表进行排序
        $list = array_sort($list , 'created_at','asc');
        foreach($list as $v){
            $order_id = $v['order_id'] ;
            $exists_order = $this->checkExistsByOrderId($order_id);
            if($exists_order){
                continue ;
            }

            $add_data['user_id'] = $user_id ;
            $add_data['user_platform_id'] = $user_platform_id ;
            $add_data['client_oid'] = $v['client_oid'] ;
            $add_data['created_at'] = $v['created_at'] ;
            $add_data['fee'] = $v['fee'] ;

            $usdt_fee = $v['fee_currency'] =='USDT' ?  $v['fee'] : $v['fee'] *  $v['filled_notional'] ;
            $add_data['usdt_fee'] = $usdt_fee ;
            $add_data['fee_currency'] = $v['fee_currency'] ;
            $add_data['filled_notional'] = $v['filled_notional'] ;
            $add_data['filled_size'] = $v['filled_size'] ;
            $add_data['funds'] = $v['funds'] ;
            $add_data['instrument_id'] = $v['instrument_id'] ;
            $add_data['notional'] = $v['notional'] ;
            $add_data['order_id'] = $v['order_id'] ;
            $add_data['order_type'] = $v['order_type'] ;
            $add_data['price'] = $v['price'] ;
            $add_data['price_avg'] = $v['price_avg'] ;
            $add_data['product_id'] = $v['product_id'] ;
            $add_data['rebate'] = $v['rebate'] ;
            $add_data['rebate_currency'] = $v['rebate_currency'] ;
            $add_data['side'] = $v['side'] ;
            $add_data['size'] = $v['size'] ;
            $add_data['state'] = $v['state'] ;
            $add_data['status'] = $v['status'] ;
            $add_data['timestamp'] = strtotime($v['timestamp']) ;
            $add_data['type'] = $v['type'] ;
            $add_data['create_time'] =$now ;
            $add_data['modify_time'] = $now ;

            $this->baseInsert(self::tableName(),$add_data);

        }
    }

    /**
     * 根据快照信息返回最新的一条信息
     * @param $snapshot_id
     * @return mixed
     */
    public function getInfoBySnapshotId($snapshot_id){

        $params['cond'] = 'trade_batch_snapshot_id=:trade_batch_snapshot_id ';
        $params['args'] = [':trade_batch_snapshot_id'=>$snapshot_id];
        $params['orderby'] = 'timestamp desc';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据平台信息同步订单列表
     * @param $user_platform
     * @param $list
     * @return bool
     */
    public function downloadByUserPlatformAndList($user_platform,$list){

        if(!$list){
            return true ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');

        // 对列表进行排序
        $list = array_sort($list , 'created_at','asc');
        foreach($list as $v){
            $order_id = $v['order_id'] ;
            $exists_order = $this->checkExistsByOrderId($order_id);
            $client_oid_arr = explode('A',$v['client_oid']);
            $trade_batch_snapshot_id = isset($client_oid_arr[1]) ? $client_oid_arr[1] : 0 ;
            if(!$trade_batch_snapshot_id){
                // 脏数据废弃
                continue ;
            }
            $add_data['user_id'] = $user_platform['user_id'] ;
            $add_data['user_platform_id'] = $user_platform['id'] ;

            $add_data['trade_batch_snapshot_id'] = $trade_batch_snapshot_id;
            $add_data['client_oid'] = $v['client_oid'] ;
            $add_data['created_at'] = $v['created_at'] ;
            $add_data['fee'] = $v['fee'] ;

            $usdt_fee = $v['fee_currency'] =='USDT' ?  $v['fee'] : $v['fee'] *  $v['filled_notional'] ;
            $add_data['usdt_fee'] = $usdt_fee ;
            $add_data['fee_currency'] = $v['fee_currency'] ;
            $add_data['filled_notional'] = $v['filled_notional'] ;
            $add_data['filled_size'] = $v['filled_size'] ;
            $add_data['funds'] = $v['funds'] ;
            $add_data['instrument_id'] = $v['instrument_id'] ;
            $add_data['notional'] = $v['notional'] ;
            $add_data['order_id'] = $v['order_id'] ;
            $add_data['order_type'] = $v['order_type'] ;
            $add_data['price'] = $v['price'] ;
            $add_data['price_avg'] = $v['price_avg'] ;
            $add_data['product_id'] = $v['product_id'] ;
            $add_data['rebate'] = $v['rebate'] ;
            $add_data['rebate_currency'] = $v['rebate_currency'] ;
            $add_data['side'] = $v['side'] ;
            $add_data['size'] = $v['size'] ;
            $add_data['state'] = $v['state'] ;
            $add_data['status'] = $v['status'] ;
            $add_data['timestamp'] = strtotime($v['timestamp']) ;
            $add_data['type'] = $v['type'] ;
            $add_data['modify_time'] = $now ;

            if($exists_order){
                $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$exists_order['id']]);
            }else{
                $add_data['create_time'] =$now ;
                $this->baseInsert(self::tableName(),$add_data);
            }


        }
    }

    /**
     * 查询是否指定的卖出单
     * @param $buy_client_oid
     * @return mixed
     */
    public function getInfoByBuyClientOid($buy_client_oid){

        $params['cond'] = 'mapping_buy_client_oid=:mapping_buy_client_oid';
        $params['args'] = [':mapping_buy_client_oid'=>$buy_client_oid];
        $params['orderby'] = 'timestamp desc';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }
}
