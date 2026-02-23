<?php

namespace common\models;

use common\components\CommonTrade;
use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sdb_symbol_macd".
 *
 * @property string $id
 * @property string $key
 * @property string $curr_a
 * @property string $curr_b
 * @property string $platform
 * @property int $group_second
 * @property int $time_str
 * @property string $five_avg
 * @property string $ten_avg
 * @property string $thirty_avg
 * @property string $dif
 * @property string $dea
 * @property string $macd
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class SymbolMacd extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_symbol_macd';
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
            [['group_second', 'time_str'], 'integer'],
            [['five_avg', 'ten_avg', 'thirty_avg', 'dif', 'dea', 'macd'], 'number'],
            [['create_time', 'update_time'], 'safe'],
            [['key', 'curr_a', 'curr_b'], 'string', 'max' => 255],
            [['platform'], 'string', 'max' => 50],
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
            'platform' => 'Platform',
            'group_second' => 'Group Second',
            'time_str' => 'Time Str',
            'five_avg' => 'Five Avg',
            'ten_avg' => 'Ten Avg',
            'thirty_avg' => 'Thirty Avg',
            'dif' => 'Dif',
            'dea' => 'Dea',
            'macd' => 'Macd',
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

    public function checkPlatformNotice($minute=15,$platform ='OKEX'){

        $platform = strtoupper($platform);
        $model = new UserSymbol();
        $list = $model->getListByPlatformAndMinute($platform,$minute);

        if($list){

            foreach($list as $v){

                $group_second = 60*$minute ;
                $symbol_key = $v['symbol_key'];
                $symbol_key_arr = explode('_',$symbol_key);
                $this->checkNotice($symbol_key_arr[0],$symbol_key_arr[1],$platform,$group_second);
            }
        }
    }

    /**
     *价格提醒
     */
    public function checkNotice($curr_a,$curr_b,$platform,$group_second){

        $params['cond'] = "  platform=:platform AND group_second=:group_second AND curr_a=:curr_a AND curr_b=:curr_b ";
        $params['args'][':platform'] = $platform;
        $params['args'][':group_second'] = $group_second;
        $params['args'][':curr_a'] = $curr_a;
        $params['args'][':curr_b'] = $curr_b;

        $params['limit'] =2;
        $params['orderby'] ='id desc';
        $r = date('i')%15;
        if($r>5){
            return false ;
        }
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());


        if( ($list[0]['dif'] >=$list[0]['dea']) && ($list[1]['dif'] < $list[1]['dea'] )){

            if($list[0]['five_avg'] >= $list[0]['ten_avg'] ){
            $minute = $group_second/60;
                $message =$minute."分钟交易提醒--当前时间".date('Y-m-d H:i:s')."---平台:".$platform."----可交易币种:".$curr_a.'_'.$curr_b.';';
                if($platform=='gate'){
                    send_dingding_sms($message,'gate_notice');
                }else {
                    $platform = strtolower($platform);
                    send_dingding_sms($message,$platform);
                }
                $notice_add_data['platform'] = $platform;
                $notice_add_data['symbol'] = $curr_a.'_'.$curr_b;
                $notice_add_data['curr_a'] = $curr_a;
                $notice_add_data['curr_b'] = $curr_b;
                $notice_add_data['notice_str'] = $message;
                $notice_add_data['group_second'] = $group_second;
                $notice_add_data['create_time'] = date('Y-m-d H:i:s');
                $notice_add_data['update_time'] = date('Y-m-d H:i:s');
                $this->baseInsert('sdb_symbol_notice',$notice_add_data,'db_okex');
            }
        }

        /*
        if( ($list[0]['dif'] >=$list[0]['dea']) &&  $list[0]['macd'] > 0){

            if($list[0]['five_avg'] >= $list[0]['ten_avg'] ){
                $minute = $group_second/60;
                $message =$minute."分钟交易提醒-可交易币种:".$curr_a.'_'.$curr_b.';';
                if($platform=='gate'){
                    send_dingding_sms($message,'gate_notice');
                }else{
                    send_dingding_sms($message,'okex');
                }
            }
        }*/

        return true ;

    }

    /**
     * 通过MACD判断是否允许购买
     * @param $curr_a
     * @param $curr_b
     * @param $platform
     * @param $group_second
     * @return bool
     */
    public function checkAllowedBuy($curr_a,$curr_b,$platform,$group_second){
        $params['cond'] = "  platform=:platform AND group_second=:group_second AND curr_a=:curr_a AND curr_b=:curr_b ";
        $params['args'][':platform'] = $platform;
        $params['args'][':group_second'] = $group_second;
        $params['args'][':curr_a'] = $curr_a;
        $params['args'][':curr_b'] = $curr_b;

        $params['limit'] =2;
        $params['orderby'] ='id desc';

        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if( ($list[0]['dif'] >=$list[0]['dea']) &&  $list[0]['macd'] > 0){

            if($list[0]['five_avg'] >= $list[0]['ten_avg'] ){
                return true ;
            }
        }

        return false;
    }
}
