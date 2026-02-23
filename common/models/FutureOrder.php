<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_future_order".
 *
 * @property string $id
 * @property string $order_id 交易ID
 * @property int $pici 批次
 * @property string $type 订单类型 1：开多 2：开空 3：平多 4： 平空
 * @property string $status 是否交易完成-1：已撤销  0：未成交 1：部分成交 2：完全成交 4:撤单处理中
 * @property string $trade_pici 交易批次
 * @property string $contract_name 合约名称
 * @property string $create_date 委托时间
 * @property string $create_date_str
 * @property string $amount 下单购买数量
 * @property string $deal_amount 成交数量
 * @property string $fee 手续费
 * @property string $price 订单价格
 * @property string $price_avg 平均价格
 * @property string $symbol 购买的币种
 * @property int $is_add_extra 是否进行补仓订单
 * @property string $unit_amount 合约面值
 * @property string $lever_rate 杠杆倍数  value:10\20  默认10 
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 * @property string $apiKey
 * @property string $secretKey
 */
class FutureOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_future_order';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_okex');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'apiKey'], 'required'],
            [['pici', 'create_date', 'is_add_extra'], 'integer'],
            [['create_date_str', 'create_time', 'update_time'], 'safe'],
            [['amount', 'deal_amount', 'fee', 'price', 'price_avg'], 'number'],
            [['order_id', 'trade_pici', 'contract_name'], 'string', 'max' => 50],
            [['type', 'status', 'symbol', 'unit_amount', 'lever_rate', 'apiKey', 'secretKey'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'pici' => 'Pici',
            'type' => 'Type',
            'status' => 'Status',
            'trade_pici' => 'Trade Pici',
            'contract_name' => 'Contract Name',
            'create_date' => 'Create Date',
            'create_date_str' => 'Create Date Str',
            'amount' => 'Amount',
            'deal_amount' => 'Deal Amount',
            'fee' => 'Fee',
            'price' => 'Price',
            'price_avg' => 'Price Avg',
            'symbol' => 'Symbol',
            'is_add_extra' => 'Is Add Extra',
            'unit_amount' => 'Unit Amount',
            'lever_rate' => 'Lever Rate',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'apiKey' => 'Api Key',
            'secretKey' => 'Secret Key',
        ];
    }
}
