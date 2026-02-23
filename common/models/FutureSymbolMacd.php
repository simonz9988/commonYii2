<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_future_symbol_macd".
 *
 * @property string $id
 * @property string $key
 * @property string $curr_a
 * @property string $curr_b
 * @property int $time_str
 * @property string $platform
 * @property string $price
 * @property string $dif
 * @property string $dea
 * @property string $macd
 * @property string $five_avg
 * @property string $ten_avg
 * @property string $thirty_avg
 * @property string $ema_12
 * @property string $ema_26
 * @property int $group_second
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class FutureSymbolMacd extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_future_symbol_macd';
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
            [['time_str', 'group_second'], 'integer'],
            [['price', 'dif', 'dea', 'macd', 'five_avg', 'ten_avg', 'thirty_avg', 'ema_12', 'ema_26'], 'number'],
            [['create_time', 'update_time'], 'safe'],
            [['key', 'curr_a', 'curr_b'], 'string', 'max' => 255],
            [['platform'], 'string', 'max' => 50],
            [['curr_a', 'curr_b', 'time_str', 'platform', 'group_second'], 'unique', 'targetAttribute' => ['curr_a', 'curr_b', 'time_str', 'platform', 'group_second']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'curr_a' => 'Curr A',
            'curr_b' => 'Curr B',
            'time_str' => 'Time Str',
            'platform' => 'Platform',
            'price' => 'Price',
            'dif' => 'Dif',
            'dea' => 'Dea',
            'macd' => 'Macd',
            'five_avg' => 'Five Avg',
            'ten_avg' => 'Ten Avg',
            'thirty_avg' => 'Thirty Avg',
            'ema_12' => 'Ema 12',
            'ema_26' => 'Ema 26',
            'group_second' => 'Group Second',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public function doAdd($addData,$platform,$group_second,$time_str){

        $params['cond'] = " time_str=:time_str AND platform=:platform AND group_second=:group_second AND curr_a=:curr_a AND curr_b=:curr_b ";
        $params['args'][':time_str'] = $time_str;
        $params['args'][':platform'] = $platform;
        $params['args'][':group_second'] = $group_second;
        $params['args'][':curr_a'] = $addData['curr_a'];
        $params['args'][':curr_b'] = $addData['curr_b'];

        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        if(!$info){
            $insert_data['key'] = $addData['curr_a'].'_'.$addData['curr_b'];
            $insert_data['curr_a'] = $addData['curr_a'];
            $insert_data['curr_b'] = $addData['curr_b'];
            $insert_data['platform'] = $platform;
            $insert_data['group_second'] = $group_second;
            $insert_data['time_str'] = $time_str;
            $insert_data['price'] = $addData['price'];
            $insert_data['top_price'] = $addData['top_price'];
            $insert_data['low_price'] = $addData['low_price'];
            $insert_data['five_avg'] = $addData['five_avg'];
            $insert_data['ten_avg'] = $addData['ten_avg'];
            $insert_data['thirty_avg'] = $addData['thirty_avg'];
            $insert_data['dif'] = $addData['dif'];
            $insert_data['dea'] = $addData['dea'];
            $insert_data['macd'] = $addData['macd'];
            $insert_data['ema_12'] = $addData['ema_12'];
            $insert_data['ema_26'] = $addData['ema_26'];
            $insert_data['create_time'] = date('Y-m-d H:i:s');
            $insert_data['update_time'] = date('Y-m-d H:i:s');

            $this->baseInsert(self::tableName(),$insert_data,'db_okex');

        }
    }
}
