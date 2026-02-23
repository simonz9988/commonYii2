<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Web3\Eth;
use Yii;

/**
 * This is the model class for table "sea_tx_list".
*/
class OkexBuyDownOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_okex_buy_down_order';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [] ;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [] ;
    }



}
