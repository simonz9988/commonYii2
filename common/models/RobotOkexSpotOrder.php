<?php

namespace common\models;

use common\components\MyRedis;
use common\components\SpotApi;
use Yii;

/**
 * This is the model class for table "sea_robot_okex_spot_order".
 *
 * @property int $id
 * @property int $user_id 后台管理员用户ID
 * @property int $user_platform_id
 * @property string $client_oid
 * @property string $created_at
 * @property string $fee
 * @property string $usdt_fee
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
 * @property string $is_deal 是否已经处理过结算
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class RobotOkexSpotOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_okex_spot_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_platform_id', 'client_oid', 'created_at', 'fee', 'usdt_fee', 'fee_currency', 'filled_notional', 'filled_size', 'funds', 'instrument_id', 'notional', 'order_id', 'order_type', 'price', 'price_avg', 'product_id', 'rebate', 'rebate_currency', 'side', 'size', 'state', 'status', 'timestamp', 'type'], 'required'],
            [['user_id', 'user_platform_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['client_oid', 'created_at', 'fee', 'usdt_fee', 'fee_currency', 'filled_notional', 'filled_size', 'funds', 'instrument_id', 'notional', 'order_id', 'order_type', 'price', 'price_avg', 'product_id', 'rebate', 'rebate_currency', 'side', 'size', 'state', 'status', 'timestamp', 'type'], 'string', 'max' => 50],
            [['is_deal'], 'string', 'max' => 1],
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
            'user_platform_id' => 'User Platform ID',
            'client_oid' => 'Client Oid',
            'created_at' => 'Created At',
            'fee' => 'Fee',
            'usdt_fee' => 'Usdt Fee',
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
            'is_deal' => 'Is Deal',
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
     * 根据用户ID返回
     * @param $user_id
     * @param $platform
     * @param $coin
     * @param $legal_coin
     * @return mixed
     */
    public function downloadOrderByUserPlatform($user_id,$platform,$coin,$legal_coin){

        $redis_key = "SyncOrder:".$platform.$user_id.$coin.$legal_coin;
        $redis_obj = new MyRedis();
        if(!$redis_obj->checkKeyExists($redis_key)  ){

            $key_obj = new UserPlatformKey();
            $key_info = $key_obj->getInfoByUserIdAndPlatform($user_id,$platform);
            if(!$key_info){
                return false ;
            }

            $config['apiKey'] = $key_info['api_key'] ;
            $config['apiSecret'] = $key_info['api_secret'] ;
            $config['passphrase'] = $key_info['passphrase'] ;
            $instrumentId = strtoupper($coin.'-'.$legal_coin) ;
            $obj = new SpotApi($config);
            $res = $obj->getOrdersList($instrumentId, 2);
            $this->downloadOrder($user_id,$res);

            $redis_obj->set($redis_key,1,120);

        }

    }

    /**
     * 同步用户订单
     * @param $user_id
     * @param $list
     * @return bool
     */
    public function downloadOrder($user_id,$list){

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

            $add_data['user_id'] = $user_id ;
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
                // 更新信息
                $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$exists_order['id']]) ;
            }else{
                $add_data['create_time'] =$now ;
                $this->baseInsert(self::tableName(),$add_data);
            }

        }
    }

    /**
     * 获取指定指定的列表信息
     * @param $user_id
     * @param $coin
     * @param $legal_coin
     * @param $is_deal
     * @return mixed
     */
    public function getListByUser($user_id,$coin,$legal_coin,$is_deal){

        $params['cond'] = 'user_id =:user_id AND instrument_id=:instrument_id AND state=:state';
        $instrument_id = returnInstrumentId($coin,$legal_coin);
        $is_deal = $is_deal ? 2:0 ;
        $params['args'] = [':user_id'=>$user_id,':instrument_id'=>$instrument_id,':state'=>$is_deal];
        $params['orderby'] = ' created_at desc ';
        $params['limit'] = 20 ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return  [] ;
        }

        $res=[];

        foreach($list as $v){
            $res[] = [
                'created_at'=>date('Y-m-d H:i:s',strtotime($v['created_at'])),
                'usdt_fee'=>$v['usdt_fee'],
                'price_avg'=>$v['price_avg'],
                'side'=>$v['side'],
            ];
        }
        return $res ;
    }
}
