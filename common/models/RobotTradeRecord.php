<?php

namespace common\models;

use common\components\MatchEngine\OrderBook;
use common\components\MatchEngine\OrderRedis;
use Yii;

/**
 * This is the model class for table "sea_robot_trade_record".
 *
 * @property int $id
 * @property int $user_id 用户Id
 * @property string $order_no 订单号
 * @property string $match_id 匹配的卖单
 * @property string $coin 币种
 * @property string $legal_coin 所属发币
 * @property string $price 单价
 * @property string $amount 数量
 * @property string $type 类型 BUY/SELL
 * @property int $status 交易状态
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class RobotTradeRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_trade_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status'], 'integer'],
            [['price', 'amount'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['order_no', 'coin', 'legal_coin', 'type'], 'string', 'max' => 50],
            [['match_id'], 'string', 'max' => 255],
            [['is_deleted'], 'string', 'max' => 1],
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
            'order_no' => 'Order No',
            'match_id' => 'Match ID',
            'coin' => 'Coin',
            'legal_coin' => 'Legal Coin',
            'price' => 'Price',
            'amount' => 'Amount',
            'type' => 'Type',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    public function getTopPrice($coin,$legal_coin){
        $symbol = getTradeSymbol($coin,$legal_coin);
        $order_book_obj = new OrderBook();
        $list =  $order_book_obj->getHandicap('limit',$symbol);

        $ask_list = isset($list['ask']) ?  $list['ask'] : [] ;
        // 获取币种初始的价格
        $res =  isset($ask_list[0]['price']) ?  $ask_list[0]['price'] : 0 ;
        if(!$res){
            $coin_obj = new Coin();
            $res = $coin_obj->getStartPriceByCoin($coin);

        }

        return $res ;
    }

    /**
     * 执行购买操作 默认只支持usdt
     * @param $user_id
     * @param $coin
     * @param $legal_coin
     * @param $price
     * @param $amount
     * @return bool
     */
    public function doBuy($user_id,$coin,$legal_coin,$price,$amount){

        if($price < 0 || $amount < 0 ){
            $this->setError('200003');
            return false ;
        }

        $coin = strtoupper($coin) ;
        $legal_coin = strtoupper($legal_coin) ;
        // 判断委托价格是否符合浮动范围
        $sell1_price = $this->getTopPrice($coin,$legal_coin);
        if($sell1_price < 0){
            $this->setError('200004');
            return false ;
        }

        // 计算是否超出允许报价的范围
        if($sell1_price >=$price){
            $percent = $sell1_price/$price - 1 ;
        }else{
            $percent = $price/$sell1_price - 1 ;
        }

        if($percent > 0.03){
            $this->setError('200005');
            return false ;
        }


        // 先判断账户余额
        $robot_record_obj = new RobotUserBalance();
        $balance_info = $robot_record_obj->getInfoByCoin($user_id,$legal_coin);
        if(!$balance_info){
            $this->setError('200002');
            return false ;
        }

        $balance_total = $balance_info['total'];
        $buy_total_amount = $price*$amount;
        if($buy_total_amount > $balance_total){
            $this->setError('200002');
            return false ;
        }

        // 创建订单
        $order_no = $this->createOrderNo('BUY');
        $order_book=new OrderBook();
        $create_order_info =[
            'order_id' => $order_no,
            'user_id' => $user_id,
            'market' => getTradeSymbol($coin,$legal_coin),
            'price' => $price,
            'quantity' => $amount,
            'side' => 'buy',
            'type' => 'limit'
        ];
        $info=$order_book->processOrder($create_order_info);
        return $info ;
    }

    /**
     * 创建订单号
     * @param $type
     * @return string
     */
    public function createOrderNo($type){
        $order_no = ($type=='SELL'?'S':'B').date("YmdHis");
        $order_no .= mt_rand(10000000,99999999);

        $params['cond'] = 'order_no =:order_no';
        $params['args'] = [':order_no'=>$order_no];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            return $this->createOrderNo($type);
        }
        return $order_no ;
    }
}
