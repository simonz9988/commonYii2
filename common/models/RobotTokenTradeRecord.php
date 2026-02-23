<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_robot_token_trade_record".
 *
 * @property int $id
 * @property int $user_id 用户Id
 * @property string $coin 币种
 * @property string $price 单价
 * @property string $amount 数量
 * @property string $type 类型 BUY/SELL
 * @property int $status 交易状态
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class RobotTokenTradeRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_token_trade_record';
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
            [['coin', 'type'], 'string', 'max' => 50],
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
            'coin' => 'Coin',
            'price' => 'Price',
            'amount' => 'Amount',
            'type' => 'Type',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
}
