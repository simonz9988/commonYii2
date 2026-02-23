<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_future_setting".
 *
 * @property string $id
 * @property string $symbol
 * @property string $type
 * @property string $is_open 是否有效
 */
class FutureSetting extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_future_setting';
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
            [['type', 'is_open'], 'string'],
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
            'symbol' => 'Symbol',
            'type' => 'Type',
            'is_open' => 'Is Open',
        ];
    }

    public function getSymbolList(){

        $params['group_by'] = 'symbol';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
}
