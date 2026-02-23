<?php

namespace common\models;

use common\components\HuobiLib;
use common\components\SpotApi;
use common\components\Websocket;
use Yii;

/**
 * This is the model class for table "sea_robot_coin_platform_info".
 *
 * @property int $id
 * @property string $platform 所属平台
 * @property int $date_timestamp 时间戳
 * @property string $all_info 全部信息
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class RobotCoinPlatformInfo extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_coin_platform_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['date_timestamp'], 'integer'],
            [['all_info'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
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
            'platform' => 'Platform',
            'date_timestamp' => 'Date Timestamp',
            'all_info' => 'All Info',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 同步平台所属订单信息
     * @param $platform
     * @return mixed
     */
    public function syncPrice($platform){

         if($platform=='OKEX'){
             $this->syncOkexPrice();
         }else if($platform =='HUOBI'){
             $this->syncHuobiPrice();
         }
    }

    public function delPrice($platform){
        $args =[':platform'=>$platform ,':create_time' => date('Y-m-d H:i:s',time()-90000)];
        $this->baseDelete(self::tableName(),'platform=:platform AND create_time <:create_time',$args,'db');
    }

    /**
     * 同步OK的价格
     * @return bool|string
     */
    private function syncOkexPrice(){

        // 配置无用，随意设置
        $config=[
            "apiKey"=>"xxx",
            "apiSecret"=>"yyy",
            "passphrase"=>"zzz",

        ];

        $obj = new SpotApi($config);
        $res = $obj->getTicker();

        $date_timestamp = time() ;
        $params['cond'] = 'platform=:platform AND date_timestamp = :date_timestamp';
        $params['args'] = [':platform'=>'OKEX',':date_timestamp'=>$date_timestamp];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            return true;
        }

        $add_data['platform'] = 'OKEX';
        $add_data['date_timestamp'] = $date_timestamp;
        $add_data['all_info'] = json_encode($res);
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 同步火币价格
     * @return mixed
     */
    private function syncHuobiPrice(){

        $date_timestamp = time() ;
        $params['cond'] = 'platform=:platform AND date_timestamp = :date_timestamp';
        $params['args'] = [':platform'=>'HUOBI',':date_timestamp'=>$date_timestamp];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            return true;
        }

        $res = curlGo('https://api.huobi.pro/market/tickers');
        $res = json_decode($res,true);
        $list = isset($res['data']) ? $res['data'] : [];
        if(!$list){
            return false ;
        }
        $add_data['platform'] = 'HUOBI';
        $add_data['date_timestamp'] = $date_timestamp;
        $add_data['all_info'] = json_encode($list);
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        return $this->baseInsert(self::tableName(),$add_data);

    }

    /**
     * 获取最近一个币种信息
     * @param $platform
     * @return mixed
     */
    public function getLastedInfoByPlatform($platform){

        $params['cond'] = 'platform=:platform';
        $params['args'] = [':platform'=>$platform];
        $params['orderby'] = ' date_timestamp DESC ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        return $info ? json_decode($info['all_info'],true): false ;
    }

    /**
     * 根据平台返回最近的一条信息
     * @param $platform
     * @param $fields
     * @return mixed
     */
    public function getRowByPlatform($platform,$fields='*'){

        $params['cond'] = 'platform=:platform';
        $params['args'] = [':platform'=>$platform];
        $params['fields'] = $fields;
        $params['orderby'] = ' date_timestamp DESC ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /*
     * 同步所有平台信息
     */
    public function syncAllPlatformInfo(){
        // 判断判断半分钟内是否有重复请求
        $info = $this->getRowByPlatform('OKEX','date_timestamp');
        $ext = $info ? time() - $info['date_timestamp'] :0 ;
        if($ext > 30 ){
            $this->syncPrice('OKEX');
        }

        $info = $this->getRowByPlatform('HUOBI','date_timestamp');
        $ext = $info ? time() - $info['date_timestamp'] :0 ;
        if($ext > 30 ){
            $this->syncPrice('HUOBI');
        }
    }

}
