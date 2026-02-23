<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_future_symbol_price".
 *
 * @property string $id
 * @property string $price 当前价格
 * @property string $platform 所属平台
 * @property string $symbol 购买的币种
 * @property string $symbol_time 币种时间
 * @property int $symbol_time_str 币种时间戳
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class FutureSymbolPrice extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_future_symbol_price';
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
            [['price'], 'number'],
            [['symbol_time', 'create_time', 'update_time'], 'safe'],
            [['symbol_time_str'], 'integer'],
            [['platform'], 'string', 'max' => 50],
            [['symbol'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'price' => 'Price',
            'platform' => 'Platform',
            'symbol' => 'Symbol',
            'symbol_time' => 'Symbol Time',
            'symbol_time_str' => 'Symbol Time Str',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
