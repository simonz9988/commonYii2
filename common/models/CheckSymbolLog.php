<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_check_symbol_log".
 *
 * @property string $id
 * @property string $symbol
 * @property string $apiKey
 * @property string $secretKey
 * @property string $response_data 响应信息
 * @property string $create_time 创建时间
 */
class CheckSymbolLog extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_check_symbol_log';
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
            [['create_time'], 'safe'],
            [['symbol', 'apiKey', 'secretKey', 'response_data'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'symbol' => 'Symbol',
            'apiKey' => 'Api Key',
            'secretKey' => 'Secret Key',
            'response_data' => 'Response Data',
            'create_time' => 'Create Time',
        ];
    }
}
