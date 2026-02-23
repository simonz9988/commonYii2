<?php

namespace common\models;

use common\components\HuobiLib;
use common\components\SpotApi;
use Yii;

/**
 * This is the model class for table "sea_coin".
 *
 * @property int $id
 * @property string $name 币种名称
 * @property string $platform 平台信息(OKEX/HUOBI)
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 * @property string $alias 币种别名
 * @property string $unique_key 币种唯一关键字
 */
class Coin extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_coin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['name'], 'string', 'max' => 11],
            [['platform'], 'string', 'max' => 100],
            [['alias', 'unique_key'], 'string', 'max' => 255],
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
            'platform' => 'Platform',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'alias' => 'Alias',
            'unique_key' => 'Unique Key',
        ];
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @return mixed
     */
    public function getInfoById($id){
        $params['cond'] = 'id =:id ';
        $params['args'] = [':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     *
     * @return mixed
     */
    public function getTotalList(){

        $params['cond'] = 'is_deleted=:is_deleted';
        $params['args'] = [':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if($list){
            foreach($list as $k=>$v){
                $show_name = strtoupper($v['unique_key']).'-USDT';
                $list[$k]['show_name'] = $show_name ;
            }
        }
        return $list ;
    }

    /**
     * 获取用管理列表信息
     * @param $user_id
     * @param $platform
     * @param $legal_coin
     * @return mixed
     */
    public function getListByUser($user_id,$platform='',$legal_coin='USDT'){

        if($legal_coin){
            $cond[] = 'legal_coin_list like :legal_coin_list' ;
            $args[':legal_coin_list'] = '%'.$legal_coin.'%' ;
        }

        if($platform){
            $cond[] = 'platform = :platform' ;
            $args[':platform'] = $platform ;
        }

        $cond[] = 'is_deleted=:is_deleted' ;
        $args[':is_deleted'] = 'N' ;
        
        $params['cond'] = implode('  AND  ',$cond);
        $params['args'] = $args ;
        $params['orderby'] = 'sort DESC';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if(!$list){
            return false ;
        }

        $res = [];

        $platform_obj = new RobotCoinPlatformInfo();

        $platform_coin_list['OKEX'] = $platform_obj->getLastedInfoByPlatform('OKEX');
        $platform_coin_list['HUOBI'] = $platform_obj->getLastedInfoByPlatform('HUOBI');

        foreach($list as $v){
            $res[] = $this->getCoinDetailByUser($user_id,$v,$platform_coin_list,$legal_coin);
        }

        return $res ;
    }

    /**
     * 根据币种的具体信息
     * @param $user_id
     * @param $info
     * @param $platform_coin_list
     * @param $legal_coin
     * @return mixed
     */
    public function getCoinDetailByUser($user_id,$info,$platform_coin_list,$legal_coin){

        $res['coin_id'] = $info['id'];
        $platform = $info['platform'];

        $coin_list = $platform_coin_list[$platform];

        $res['coin'] = $info['unique_key'] ;
        // 是否已经收藏
        $is_collected = false ;
        $collection_obj = new RobotUserCollection();
        if($user_id){
            $collection_info = $collection_obj->getInfoByUserIdAndCoinId($user_id,$info['id'],$legal_coin);
            $is_collected =  $collection_info ? true :false ;
        }
        $res['is_collected'] = $is_collected ;
        $res['is_private'] = $info['is_private'] ;

        // 法币
        $res['legal_coin'] = $legal_coin ;
        $res['platform'] = $platform ;

        // 私有币种直接返回
        if($info['is_private'] == 'Y') {

            $private_coin_info = $this->getPrivateCoinInfo($info);
            return $private_coin_info ;
        }

        if($platform =='OKEX'){

            $detail = [];
            foreach($coin_list as $v){
                if($v['instrument_id'] == $info['unique_key'].'-'.$legal_coin){
                    $detail = $v ;
                    break ;
                }
            }
            if(!$detail){
                return [] ;
            }

            $price = $detail['best_ask'];
            $res['price'] = $price ;

            // 成交量
            $volume_24h = $detail['base_volume_24h'];
            $res['volume_24h'] = $volume_24h ;
            // 下降或者增加百分比
            $open_24h = $detail['open_24h'];
            $percent = numberSprintf(($price-$open_24h)/$open_24h,6);
            $res['percent'] = $percent ;
        }else if($platform =='HUOBI'){
            $detail = [];
            foreach($coin_list as $v){
                if($v['symbol'] == strtolower($info['unique_key']).strtolower($legal_coin)){
                    $detail = $v ;
                    break ;
                }
            }

            // 价格
            $price = $detail['ask'];
            $res['price'] = $price ;

            // 成交量
            $volume_24h = $detail['vol'];
            $res['volume_24h'] = $volume_24h ;
            // 下降或者增加百分比
            $open_24h = $detail['open'];
            $percent = numberSprintf(($price-$open_24h)/$open_24h,6);
            $res['percent'] = $percent ;

        }

        $base_price = $price ;
        //获取币种价格
        if($legal_coin =='BTC'){
            foreach($coin_list as $v){

                if($platform == 'OKEX' && $v['instrument_id'] == 'BTC-USDT'){
                    $base_price = $v['best_ask']*$base_price;
                    break ;
                }

                if($platform == 'HUOBI' &&$v['symbol'] == 'btcusdt'){
                    $base_price = $v['ask']*$base_price;
                    break ;
                }
            }
        }else if($legal_coin =='ETH'){
            foreach($coin_list as $v) {
                if ($platform == 'OKEX' && $v['instrument_id'] == 'ETH-USDT') {
                    $base_price = $v['best_ask'] * $base_price;
                    break;
                }

                if ($platform == 'HUOBI' && $v['symbol'] == 'ethusdt') {
                    $base_price = $v['ask'] * $base_price;
                    break;
                }
            }
        }


        $usdt_price = $this->getUsdtPrice();
        $cny_price = $base_price*$usdt_price;
        $res['cny_price'] = $cny_price ;
        return $res ;

    }

    /**
     * 获取私有币种价格信息
     * @param $coin_info
     * @return mixed
     */
    public function getPrivateCoinInfo($coin_info){

        $coin_price_obj = new CoinPrice();
        $price = $coin_price_obj->getCoinPrice($coin_info['unique_key']);

        $usdt_price = $this->getUsdtPrice();
        $start_time = date('Y-m-d H:i:s' ,time()-86400);
        $end_time = date('Y-m-d H:i:s');

        $start_price = $coin_price_obj->getPriceByRangeTime($coin_info['unique_key'],$start_time,$end_time,'asc') ;

        $record_obj = new RobotUserBalanceRecord();
        return [
            'coin_id'=>$coin_info['id'] ,
            'coin'=>$coin_info['unique_key'] ,
            'is_collected'=>'N' ,
            'is_private'=>'Y' ,
            'legal_coin'=>'USDT' ,
            'platform'=>'SELF' ,
            'price'=>$price ,
            'volume_24h'=>$record_obj->getTotalByCoinAndTypeAndRangeTime($coin_info['unique_key'],'BUY_COIN',$start_time,$end_time) ,
            'percent'=> $start_price ? numberSprintf(($price-$start_price)/$start_price,6) : 0 ,
            'cny_price'=>$price*$usdt_price ,
        ];
    }

    /**
     * 获取美刀价格
     * @return array|bool|mixed
     */
    public function getUsdtPrice(){
        $site_config = new SiteConfig();
        $price = $site_config->getByKey('usdt_price');
        return $price ;
    }

    /**
     * 获取用户收藏的币种列表信息
     * @param $user_id
     * @return mixed
     */
    public function getCollectionListByUser($user_id){
        $collection_obj = new RobotUserCollection();
        $collection_list = $collection_obj->getListByUserId($user_id);
        if(!$collection_list){
            return false ;
        }

        $platform_obj = new RobotCoinPlatformInfo();

        $platform_coin_list['OKEX'] = $platform_obj->getLastedInfoByPlatform('OKEX');
        $platform_coin_list['HUOBI'] = $platform_obj->getLastedInfoByPlatform('HUOBI');

        $res = [] ;
        foreach($collection_list as $v){
            $legal_coin = $v['legal_coin'] ;
            $coin_id = $v['coin_id'];
            $coin_info = $this->getInfoById($coin_id);
            $res[] = $this->getCoinDetailByUser($user_id,$coin_info,$platform_coin_list,$legal_coin);
        }

        return $res ;
    }

    /**
     * 判断法币类型是否正确
     * @param $legal_coin
     * @return mixed
     */
    public function checkLegalCoinType($legal_coin){
        $arr = ['USDT','ETH','BTC'];
        return in_array($legal_coin,$arr) ? true :false ;
    }

    /**
     * 根据指定关键字获取列表信息
     * @param $user_id
     * @param $search_name
     * @return mixed
     */
    public function getListByKeywords($user_id,$search_name){

        $params['cond'] = 'is_deleted=:is_deleted AND unique_key like :unique_key';
        $params['args'] = [':is_deleted'=>'N',':unique_key'=>'%'.$search_name.'%'];
        $params['orderby'] = ' sort desc';
        $coin_list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$coin_list){
            return false ;
        }

        $platform_obj = new RobotCoinPlatformInfo();

        $platform_coin_list['OKEX'] = $platform_obj->getLastedInfoByPlatform('OKEX');
        $platform_coin_list['HUOBI'] = $platform_obj->getLastedInfoByPlatform('HUOBI');
        $res = [];
        foreach($coin_list as $coin_info){

            $legal_coin_list = explode(',',$coin_info['legal_coin_list']);
            if($legal_coin_list){
                foreach($legal_coin_list as $legal_coin){
                    $res[] = $this->getCoinDetailByUser($user_id,$coin_info,$platform_coin_list,$legal_coin);
                }
            }

        }
        return $res ;
    }

    /**
     * 获取所有列表信息
     * @return mixed
     */
    public function getAll(){

        $params['cond'] = 'is_deleted=:is_deleted ';
        $params['args'] = [':is_deleted'=>'N'];
        $params['orderby'] = ' sort desc';
        $coin_list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if(!$coin_list){
            return false ;
        }

        $platform_obj = new RobotCoinPlatformInfo();

        $platform_coin_list['OKEX'] = $platform_obj->getLastedInfoByPlatform('OKEX');
        $platform_coin_list['HUOBI'] = $platform_obj->getLastedInfoByPlatform('HUOBI');
        $res = [];
        foreach($coin_list as $coin_info){

            $legal_coin_list = explode(',',$coin_info['legal_coin_list']);
            if($legal_coin_list){
                foreach($legal_coin_list as $legal_coin) {
                    $info = $this->getCoinDetailByUser(0, $coin_info, $platform_coin_list, $legal_coin);
                    if ($info) {
                        $res[] = $info;
                    }
                }
            }

        }

        if($res){
            foreach($res as $k=>$v){
                $res[$k]['percent'] = $v['percent']*100 ;
            }
        }



        return $res ;
    }

    /**
     * 获取所有列表信息
     * @param $user_id
     * @param $platform
     * @param $earn_type
     * @return mixed
     */
    public function getAllByPlatformAndType($user_id,$platform,$earn_type){

        $params['cond'] = 'is_deleted=:is_deleted AND platform=:platform AND earn_type=:earn_type ';
        $params['args'] = [':is_deleted'=>'N',':platform'=>$platform,':earn_type'=>$earn_type];
        $params['orderby'] = ' sort desc';
        $coin_list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if(!$coin_list){
            return false ;
        }

        $platform_obj = new RobotCoinPlatformInfo();

        $platform_coin_list[$platform] = $platform_obj->getLastedInfoByPlatform($platform);
        $res = [];
        foreach($coin_list as $coin_info){

            $legal_coin_list = explode(',',$coin_info['legal_coin_list']);
            if($legal_coin_list){
                foreach($legal_coin_list as $legal_coin){
                    $res[] = $this->getCoinDetailByUser($user_id,$coin_info,$platform_coin_list,$legal_coin);
                }
            }

        }


        return $res ;
    }

    /**
     * 根据列表返回上涨top
     * @param $list
     * @param int $limit
     * @return mixed
     */
    public function getTopUp($list,$limit=5){

        if(!$list){
            return  [] ;
        }

        $list= array_sort($list,'percent','desc');
        $res = [];
        $i =  0 ;

        foreach($list as $v){
            if($i<$limit){
                if($v['percent'] > 0 ){
                    $res[] = $v ;
                    $i++ ;
                }
            }
        }

        return $res ;
    }

    /**
     * 根据列表返回上涨top
     * @param $list
     * @param int $limit
     * @return mixed
     */
    public function getTopDown($list,$limit=5){

        if(!$list){
            return  [] ;
        }

        $list= array_sort($list,'percent','asc');
        $res = [];
        $i =  0 ;

        foreach($list as $v){
            if($i<$limit){
                if($v['percent'] <= 0 ){
                    $res[] = $v ;
                    $i++ ;
                }
            }
        }

        return $res ;
    }

    /**
     * 根据币种和平台返回指定币种的信息
     * @param $platform
     * @param $coin
     * @return mixed
     */
    public function getInfoByPlatformAndCoin($platform,$coin){
        $params['cond'] = 'unique_key=:unique_key AND platform=:platform';
        $params['args'] = [':unique_key'=>$coin,':platform'=>$platform];
        return $this->findOneByWhere(self::tableName(),$params,self::getDb());
    }

    /**
     * 根据法币获取购买价格
     * @param $info
     * @param $legal_coin
     * @return mixed
     */
    public function getBuyPointsByInfoAndLegalCoin($info,$legal_coin){

        if(!$info){
            return  0 ;
        }

        if($legal_coin=='USDT'){
            return $info['usdt_buying_points'];
        }elseif($legal_coin=='BTC'){
            return $info['btc_buying_points'];
        }elseif($legal_coin=='ETH'){
            return $info['eth_buying_points'];
        }
        return  0 ;
    }

    public function getMaxBuyPointsByInfoAndLegalCoin($info,$legal_coin){
        if(!$info){
            return  0 ;
        }

        if($legal_coin=='USDT'){
            return $info['usdt_max_buying_points'];
        }elseif($legal_coin=='BTC'){
            return $info['btc_max_buying_points'];
        }elseif($legal_coin=='ETH'){
            return $info['eth_max_buying_points'];
        }
        return  0 ;
    }

    /**
     * 获取平台指定币种的交易深度信息
     * @param $platform
     * @param $coin
     * @param $legal_coin
     * @return mixed
     */
    public function getBookList($platform,$coin,$legal_coin){
        if(!in_array($platform,['OKEX','HUOBI'])){
            return [] ;
        }

        $coin_info = $this->getInfoByPlatformAndCoin($platform,$coin);
        if(!$coin_info){
            return false ;
        }

        $depth_key = strtolower($legal_coin.'_depth');
        $depth = isset($coin_info[$depth_key]) ? $coin_info[$depth_key] :0.01;
        if($platform =='OKEX'){

            $config=[
                "apiKey"=>"xxx",
                "apiSecret"=>"yyy",
                "passphrase"=>"zzz",

            ];

            $obj = new SpotApi($config);
            $instrument_id = strtoupper($coin.'-'.$legal_coin);
            $list = $obj->getDepth($instrument_id, 5, $depth);
            if(!$list){
                return  [] ;
            }
            $temp_asks = isset($list['asks']) ? $list['asks'] :[];
            $asks = [];
            if($temp_asks){
                foreach($temp_asks as $v){
                    $asks[] = [
                        'price'=>$v[0],
                        'amount'=>$v[1],
                        'order'=>$v[2],
                    ];
                }
            }
            $temp_bids = isset($list['bids']) ? $list['bids'] :[];
            $bids = [];
            if($temp_bids){
                foreach($temp_bids as $v){
                    $bids[] = [
                        'price'=>$v[0],
                        'amount'=>$v[1],
                        'order'=>$v[2],
                    ];
                }
            }

            return compact('asks','bids');
        }else{
            $symbol = strtolower($coin.$legal_coin);
            $depth = 5 ;
            $type =  'step1';
            $huobi_lib = new HuobiLib();
            $list = $huobi_lib->get_market_depth($symbol,$type,5);
            $list = object_to_array($list);
            $list = isset($list['tick'])?$list['tick']:[];
            $temp_asks = isset($list['asks']) ? $list['asks'] :[];
            $asks = [];
            if($temp_asks){
                foreach($temp_asks as $v){
                    $asks[] = [
                        'price'=>$v[0],
                        'amount'=>$v[1],
                        'order'=>1,
                    ];
                }
            }
            $temp_bids = isset($list['bids']) ? $list['bids'] :[];
            $bids = [];
            if($temp_bids){
                foreach($temp_bids as $v){
                    $bids[] = [
                        'price'=>$v[0],
                        'amount'=>$v[1],
                        'order'=>1,
                    ];
                }
            }

            return compact('asks','bids');
        }
    }

    /**
     * 获取币种的价格
     * @param $coin
     * @return mixed
     */
    public function getStartPriceByCoin($coin){
        $param['cond'] = 'unique_key=:unique_key AND is_deleted=:is_deleted';
        $param['args'] = [':unique_key'=>strtoupper($coin),':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$param,self::getDb());
        return $info ? $info['start_price']: 0 ;
    }

    /**
     * 获取交易步长
     * @param $coin_info
     * @param $legal_coin
     * @param $max_block
     * @return mixed
     */
    public function getAddStepPercentByCoinInfo($coin_info,$legal_coin,$max_block){

        $min_price_key = strtolower($legal_coin.'_buying_points') ;
        $min_price = $coin_info[$min_price_key] ;

        $max_price_key = strtolower($legal_coin.'_max_buying_points') ;
        $max_price = $coin_info[$max_price_key] ;

        $add_step_percent =  numberSprintf(($max_price-$min_price)/$max_block,2) ;
        return $add_step_percent ;

    }

    /**
     * 获取交易步长
     * @param $coin_info
     * @param $legal_coin
     * @param $max_block
     * @return mixed
     */
    public function getAddStepByCoinInfo($coin_info,$legal_coin,$max_block =0 ){

        $min_price_key = strtolower($legal_coin.'_buying_points') ;
        $min_price = $coin_info[$min_price_key] ;

        $max_price_key = strtolower($legal_coin.'_max_buying_points') ;
        $max_price = $coin_info[$max_price_key] ;

        if(!$max_block){
            $max_block = $coin_info['max_block'] ;
        }

        $add_step_percent =  numberSprintf(($max_price-$min_price)/$max_block,4) ;
        return $add_step_percent ;

    }

    /**
     * 获取的最低的金额
     * @param $price 当前金额
     * @param $step  网格数目
     * @param $add_step_price 每个步长的价格
     * @param $min_buy_amount 最小交易单位
     * @return mixed
     */
    public function getMinBalance($price,$step,$add_step_price,$min_buy_amount){
        $total = 0 ;
        $step =  floor($step/2) ;

        // 上单边需要的最小交易金额
        for($i=1;$i<=$step;$i++){
            $total += $price + $add_step_price*$i ;

        }
        // 下单边需要的交易交易金额
        for($i=1;$i<=$step;$i++){
            $total += ($price - $add_step_price*$i) ;

        }

        return $total*$min_buy_amount ;
    }
}
