<?php

namespace common\models;

use common\components\MyRedis;
use common\components\SpotApi;
use Yii;

/**
 * This is the model class for table "sea_coin_price".
 *
 * @property int $id
 * @property string $name 币种名称
 * @property string $unique_key 币种唯一关键字
 * @property string $hour_timestamp 小时时间戳
 * @property string $day_timestamp 天时间戳
 * @property string $year_timestamp 年时间戳
 * @property string $price 初始价格
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class CoinPrice extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_coin_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['name'], 'string', 'max' => 11],
            [['unique_key', 'hour_timestamp', 'day_timestamp', 'year_timestamp'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'unique_key' => 'Unique Key',
            'hour_timestamp' => 'Hour Timestamp',
            'day_timestamp' => 'Day Timestamp',
            'year_timestamp' => 'Year Timestamp',
            'price' => 'Price',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 获取币种价格
     * @param $coin
     * @return mixed
     */
    public function getCoinPrice($coin){
        $params['cond'] = 'unique_key=:unique_key ';
        $params['args'] = [':unique_key'=>$coin];
        $params['orderby'] = 'id desc';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['price'] : 0 ;
    }

    public function getCurrentPrice(){
        $sqlstr = "SELECT IFNULL((0.15 + (position_y-2)*0.02),0.15) AS siax_price,FROM_UNIXTIME(create_time,'%Y-%m-%d %H:%i:%s') AS update_time FROM sea_robot_user_res_position ORDER BY position_y DESC,position_x LIMIT 0,1";
        $command = Yii::$app->db->createCommand($sqlstr);
        $res = $command->queryOne();
        $res =  $res ? $res : ['siax_price'=>0.15,'update_time'=>date("Y-m-d H:i:s")];
        return $res['siax_price'] ;
    }
    /**
     * 插入价格快照信息
     * @param $coin
     * @param $price
     * @return mixed
     */
    public function addPriceRecord($coin,$price){

        $hour = date('Y-m-d H:00:00');
        $hour_timestamp = strtotime($hour);
        $params['cond'] = 'hour_timestamp=:hour_timestamp AND unique_key=:unique_key';
        $params['args'] = [':unique_key'=>$coin,':hour_timestamp'=>$hour_timestamp];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            return true ;
        }

        $add_data['name'] = $coin ;
        $add_data['unique_key'] = $coin ;
        $add_data['hour_timestamp'] = $hour_timestamp ;
        $add_data['day_timestamp'] = strtotime(date("Y-m-d 00:00:00")) ;
        $add_data['month_timestamp'] = strtotime(date("Y-m-01 00:00:00")) ;
        $add_data['year_timestamp'] = strtotime(date("Y-01-01 00:00:00")) ;
        $add_data['price'] = $price ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 获取指定币种的K线图
     * @param $coin_info
     * @param $group_type
     * @param $redis_key
     * @param $legal_coin
     * @return mixed
     */
    public function getKlineByCoinInfo($coin_info,$group_type,$redis_key,$legal_coin='USDT'){

        if($coin_info['is_private'] =='Y'){

            $coin_obj = new Coin() ;
            $return_data['coin_detail_info'] = $coin_obj->getPrivateCoinInfo($coin_info) ;

            if($group_type =='HOUR'){
                $granularity = 'hour_timestamp';
            }else if($group_type =='DAY'){
                $granularity = 'day_timestamp';
            }else if($group_type =='MONTH'){
                $granularity = 'month_timestamp';
            }else{
                $granularity = 'year_timestamp';
            }

            $params['cond'] = 'unique_key=:unique_key';
            $params['args'] = [':unique_key'=>$coin_info['unique_key']];
            $params['group_by'] = $granularity;
            $params['orderby'] = ' id desc';
            $params['limit'] = 200 ;
            $coin_price_obj = new CoinPrice();
            $temp_list = $this->findAllByWhere($coin_price_obj::tableName(),$params,self::getDb());
            $list = [] ;
            if($temp_list){
                foreach ($temp_list as $v){

                    if($group_type =='HOUR'){
                        $reportDate = date("H:i",$v[$granularity]);
                    }else if($group_type =='DAY'){
                        $reportDate = date("Y-m-d",$v[$granularity]);
                    }else if($group_type =='MONTH'){
                        $reportDate = date("m",$v[$granularity]);
                    }else{
                        $reportDate = date("Y",$v[$granularity]);
                    }
                    $list[] = [
                        'name'=>$coin_info['unique_key'],
                        'reportDateTimestamp'=>$v[$granularity]*1000,
                        'reportDate'=>$reportDate,
                        'rate' => $v['price']
                    ];
                }
            }

            $return_data['coin_list'] = $list  ;
        }else{

            // 配置无用，随意设置
            $config=[
                "apiKey"=>"xxx",
                "apiSecret"=>"yyy",
                "passphrase"=>"zzz",

            ];
            $platform_obj = new RobotCoinPlatformInfo();
            $platform_coin_list['OKEX'] = $platform_obj->getLastedInfoByPlatform('OKEX');
            $coin_obj = new Coin();
            $detail_info = $coin_obj->getCoinDetailByUser(0,$coin_info,$platform_coin_list,$legal_coin);
            $return_data['coin_detail_info'] = $detail_info ;

            // 24小时成交量 最高价 最低价 当前价格
            //granularity=86400&start=2019-03-19T16:00:00.000Z&end=2019-03-20T16:00:00.000Z
            $obj = new SpotApi($config);
            $instrument_id = $coin_info['unique_key'].'-'.'USDT';
            $instrument_id = strtoupper($instrument_id);
            if($group_type =='HOUR'){
                $granularity = 3600;
            }else if($group_type =='DAY'){
                $granularity = 86400;
            }else if($group_type =='MONTH'){
                $granularity = 2678400;
            }else{
                $granularity = 86400*365;
            }
            $res = $obj->getKine($instrument_id, $granularity,'2018-01-19T00:00:00.000Z', date("Y-m-d\TH:i:s.000\Z"));

            $list = [];
            foreach($res as $v){

                if($group_type =='HOUR'){
                    $reportDate = date("H:i",strtotime($v[0]));
                }else if($group_type =='DAY'){
                    $reportDate = date("Y-m-d",strtotime($v[0]));
                }else if($group_type =='MONTH'){
                    $reportDate = date("m",strtotime($v[0]));
                }else{
                    $reportDate = date("Y",strtotime($v[0]));
                }

                $list[] = [
                    'name'=>$coin_info['unique_key'],
                    'reportDateTimestamp'=>strtotime($v[0])*1000,
                    'reportDate'=>$reportDate,
                    'rate' => $v[4]
                ];

            }
            $return_data['coin_list'] = $list  ;
        }

        $coin_list = $return_data['coin_list'];
        $i = 0 ;

        $final_coin_list = [];
        foreach($coin_list as $v){
            if($i<20){
                $final_coin_list[] = $v ;
                $i++ ;
            }
        }
        $return_data['coin_list'] = $final_coin_list  ;

        // 设置数据缓存
        $redis_obj = new MyRedis();
        $redis_obj->set($redis_key,serialize($return_data),180);

        return $return_data ;
    }

    /**
     * 获取指定时间最高价
     * @param $coin
     * @param $start_time
     * @param $end_time
     * @param $order_by
     * @return mixed
     */
    public function getPriceByRangeTime($coin,$start_time,$end_time,$order_by ='desc'){

        $params['cond'] = 'create_time >= :start_time AND  create_time<=:end_time AND unique_key=:unique_key ';
        $params['args'] = [':start_time'=>$start_time,':end_time'=>$end_time,':unique_key'=>$coin];
        $params['orderby'] = ' price '.$order_by;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['price'] : 0 ;
    }
}
