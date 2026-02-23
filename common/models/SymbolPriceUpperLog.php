<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_symbol_price_upper_log".
 *
 * @property string $id
 * @property string $symbol 币种
 * @property string $upper_nums 上升次数
 * @property string $create_time 创建时间
 */
class SymbolPriceUpperLog extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_symbol_price_upper_log';
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
            [['symbol', 'upper_nums'], 'string', 'max' => 255],
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
            'upper_nums' => 'Upper Nums',
            'create_time' => 'Create Time',
        ];
    }

    /**检查只等分钟数内是否已经上涨过
     * @param $symbol
     * @param $percent
     * @param $num
     * @param $minute 默认45分钟
     */
    public function checkHavingUp($symbol,$percent,$num,$minute =45,$is_add_record){

        //step1 查询是否有记录
        $now = time();
        $prev_time = $now - $minute*60 - 2;
        $prev_time = date('Y-m-d H:i:s',$prev_time);
        $params['cond'] = ' symbol =:symbol AND create_time >= :create_time ';
        $params['args'] = [":symbol"=>$symbol ,':create_time'=>$prev_time];
        $info = $this->findOneByWhere('sdb_symbol_price_upper_log',$params,self::getDb());

        //step2  新增记录
        if($is_add_record){

            $add_data['symbol'] = $symbol ;
            $add_data['total_percent'] = $percent ;
            $add_data['upper_nums'] = $num ;
            $add_data['minute'] = $minute ;
            $add_data['create_time'] = date('Y-m-d H:i:s');

            $this->baseInsert('sdb_symbol_price_upper_log',$add_data,'db_okex');

        }

        if($info){
            return true ;
        }else{
            return false;
        }
    }

}
