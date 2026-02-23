<?php

namespace common\models;

use common\components\SpotApi;
use Yii;

/**
 * This is the model class for table "sea_okex_coin_ticker".
 *
 * @property int $id
 * @property string $best_ask 后台管理员用户ID
 * @property string $best_bid
 * @property string $instrument_id
 * @property string $product_id
 * @property string $last
 * @property string $last_qty
 * @property string $ask
 * @property string $best_ask_size
 * @property string $bid
 * @property string $best_bid_size
 * @property string $open_24h
 * @property string $high_24h
 * @property string $low_24h
 * @property string $base_volume_24h
 * @property string $timestamp
 * @property string $quote_volume_24h
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class OkexCoinTicker extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_okex_coin_ticker';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['instrument_id', 'product_id', 'last', 'last_qty', 'ask', 'best_ask_size', 'bid', 'best_bid_size', 'open_24h', 'high_24h', 'low_24h', 'base_volume_24h', 'timestamp', 'quote_volume_24h'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['best_ask', 'best_bid', 'instrument_id', 'product_id', 'last', 'last_qty', 'ask', 'best_ask_size', 'bid', 'best_bid_size', 'open_24h', 'high_24h', 'low_24h', 'base_volume_24h', 'timestamp', 'quote_volume_24h'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'best_ask' => 'Best Ask',
            'best_bid' => 'Best Bid',
            'instrument_id' => 'Instrument ID',
            'product_id' => 'Product ID',
            'last' => 'Last',
            'last_qty' => 'Last Qty',
            'ask' => 'Ask',
            'best_ask_size' => 'Best Ask Size',
            'bid' => 'Bid',
            'best_bid_size' => 'Best Bid Size',
            'open_24h' => 'Open 24h',
            'high_24h' => 'High 24h',
            'low_24h' => 'Low 24h',
            'base_volume_24h' => 'Base Volume 24h',
            'timestamp' => 'Timestamp',
            'quote_volume_24h' => 'Quote Volume 24h',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 同步所有加个信息
     */
    public function syncPrice(){
        $config['apiKey'] = '' ;
        $config['apiSecret'] = '' ;
        $config['passphrase'] = '' ;

        $obj = new SpotApi($config);
        $res = $obj->getTicker();

        if(!$res){
            return false ;
        }

        foreach($res as $v){

            $params['cond'] = 'instrument_id=:instrument_id';
            $params['args'] = [':instrument_id'=>$v['instrument_id']];
            $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());


            if(!$info){
                //insert
                $add_data = $v ;
                $add_data['create_time'] = date('Y-m-d H:i:s');
                $add_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseInsert(self::tableName(),$add_data) ;
            }else{
                // update
                $update_data = $v ;
                $update_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
            }
        }

        return true ;

    }

    /**
     * 根据币种信息获取最新报价
     * @param $instrument_id
     * @return mixed
     */
    public function getLastByInstrumentId($instrument_id){

        $params['cond'] = 'instrument_id=:instrument_id';
        $params['args'] = [':instrument_id'=>$instrument_id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? floatval($info['last']): 0 ;
    }
}
