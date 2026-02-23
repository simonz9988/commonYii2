<?php
namespace common\components;

use common\models\CancelSymbolTask;
use common\models\Order;
use common\models\Symbol;
use common\models\SymbolMacd;
use common\models\SymbolPriceUpperLog;
use common\models\UserSymbol;
use yii\web\User;

require_once (dirname(__FILE__) . '/OKCoin/OKCoin.php');

/**
 * Class OkexTrade
 * okex交易
 * @package backend\components
 */
class OkexTrade {

    //当前最新价格
    private $last_price = 0 ;

    //购买增加的基础概率
    private  $upper_percent = 1.00197;

    private  $one_day_down_percent = 1.025 ;

    //是否需要补仓
    private  $is_need_add_extra = 0;

    //补仓需要购买的数量
    private $add_extra_amount = 0 ;
    /**
     * @delete
     */
    public function __construct() {

    }

    /**
     * 获取用户信息
     * @param $apiKey
     * @param $secretKey
     * @return array
     */
    public function getUserInfo($apiKey,$secretKey){
        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $params = array('api_key' => $apiKey);
        $result = $client -> userinfoApi($params);
        $result = object_to_array($result) ;
        return $result ;
    }


    /**
     * 获取用户的usdt
     * @param $apiKey
     * @param $secretKey
     * @return int
     */
    public function getUserUsdt($apiKey,$secretKey){
        $model = new  MyRedis();
        $redis_key = md5($apiKey.$secretKey);
        $redis_info = $model->get($redis_key);
        if($redis_info){
            $usdt = $redis_info ;
        }else{
            $usdt = $this->getUserAccountByType('usdt',$apiKey,$secretKey);
            $model->set($redis_key,$usdt,120);
        }

        return $usdt ;
    }

    /**
     * 获取用户指定币种的账户余额
     * @param $type
     * @param $apiKey
     * @param $secretKey
     * @return int
     */
    public function getUserAccountByType($type,$apiKey,$secretKey){
        $rst = $this->getUserInfo($apiKey,$secretKey);
        $result = isset($rst['result'])&&$rst['result'] ==1?true:false;
        if(!$result){
            return  0 ;
        }
        $usdt = isset($rst['info']['funds']['free'][$type])?$rst['info']['funds']['free'][$type]:0 ;
        return $usdt ;
    }
    /**
     * 获取最新成交价
     */
    public function getLastPrice($symbol,$platform='OKEX'){
        $platform = strtolower($platform);
        $symbol_price_model = new SymbolMacd();
        $params['cond'] = 'symbol =:symbol and platform = :platform';
        $params['args'] = [':symbol'=>$symbol,':platform'=>$platform];
        $params['orderby'] = 'symbol_time desc';
        $row = $symbol_price_model->findOneByWhere('sdb_symbol_price',$params,$symbol_price_model::getDb());
        return  isset($row['price'])?$row['price']:0 ;

    }

    /**
     * 返回上一次交易的信息
     * @param $apiKey
     * @param $secretKey
     * @param $symbol
     * @return array
     */
    public function getLastTradeRowInfo($apiKey,$secretKey,$symbol,$user_symbol_id){

        $model = new Order();
        $params['cond'] = 'user_symbol_id =:user_symbol_id AND apiKey = :apiKey AND secretKey =:secretKey AND symbol =:symbol AND  status =:status  ' ;
        $params['args']= ['user_symbol_id'=>$user_symbol_id,':apiKey'=>$apiKey,':secretKey'=>$secretKey,':symbol'=>$symbol,':status'=>2];
        $params['orderby'] = 'create_time desc' ;
        $row = $model->findOneByWhere($model::tableName(),$params,$model::getDb());

        return $row ;
    }

    /**
     * 执行交易
     * @param $symbol
     * @param $apiKey
     * @param $secretKey
     * @param $usdt_num  交易的USDT的数量
     * @param $user_symbol_id  交易的USDT的数量
     */
    public function doTrade($symbol,$apiKey,$secretKey,$usdt_num,$user_symbol_id=0,$platform='OKEX'){

        $trade_type = $this->returnTradeTypeV1($symbol,$apiKey,$secretKey,$user_symbol_id,$platform);

        if($trade_type){

            if($trade_type =='BUY' || $trade_type =='ADD_BUY'){

                //判断5分钟的线是否可以买
                if($trade_type =='BUY'){
                    $macs_model = new SymbolMacd();
                    $group_second= 15*60 ;
                    $symbol_arr =explode('_',$symbol);
                    $curr_a = $symbol_arr[0];
                    $curr_b = $symbol_arr[1];
                    $rst = $macs_model->checkAllowedBuy($curr_a,$curr_b,$platform,$group_second);
                    if(!$rst){
                        return false ;
                    }
                }
                //通过收入来判断是否允许购买
                $last_price = $this->last_price ;
                $this->doBuy($last_price,$apiKey,$secretKey,$symbol,$usdt_num,$user_symbol_id);


            }

            if($trade_type =='SELL'){
                //$last_price =$this->getLastPrice($symbol,$apiKey,$secretKey) ;
                $last_price = $this->last_price ;
                $this->doSell($apiKey,$secretKey,$symbol,$last_price,$user_symbol_id);
            }

        }

    }

    /**
     * 判断交易类型
     * @param $symbol
     * @param $apiKey
     * @param $secretKey
     * @param $user_symbol_id
     */
    public function returnTradeTypeV1($symbol,$apiKey,$secretKey,$user_symbol_id,$platform='OKEX'){

        //step1 查询上一次成交的信息
        $prev_trade_info  = $this->getLastTradeRowInfo($apiKey,$secretKey,$symbol,$user_symbol_id);

        //step2 获取市场当前价
        $last_price =$this->getLastPrice($symbol,$platform) ;

        $this->last_price = $last_price ;


        $last_price = floatval($last_price);

        //该币种之前并没有购买过
        if(!$prev_trade_info){

            //判断有没有连续上涨 且涨幅超过千分之二
            $check_lianxu_up_rst = $this->checkLianxuUp($symbol,3,$last_price,$platform);
            if($check_lianxu_up_rst ){
                return 'BUY';
            }else{
                return '';
            }
        }

        $prev_trade_type = $prev_trade_info['type'];
        if($prev_trade_type =='SELL'){

            //判断有没有连续上涨 且涨幅超过千分之一
            $check_lianxu_up_rst = $this->checkLianxuUp($symbol,3,$last_price,$platform);
            if($check_lianxu_up_rst ){
                return 'BUY';
            }else{
                return '';
            }

        }

        $order_model = new Order();
        $prev_price = $order_model->getPrevAvgPrice($prev_trade_info);

        //获取用户币种信息
        $user_symbol_model = new UserSymbol();
        $user_symbol_info = $user_symbol_model->getRowInfoById($user_symbol_id);
        $bucang_times = $user_symbol_info['bucang_times'];

        if($last_price < $prev_price){

            //判断是否需要补仓
            $down_percent = $prev_price/$last_price;

            //判断补仓次数
            $total_buy_nums = $order_model->getTotalBuyTimes($prev_trade_info);

            if($total_buy_nums < ($bucang_times+1)){
                $bucang_down_percent = $user_symbol_info['bucang_down_percent'];
                if( ($down_percent*100-100) > $bucang_down_percent ){
                    return 'ADD_BUY';
                }
            }else{
                //提示跌幅过大需要卖
            }
            return '';
        }else{
            //判断是否追涨
            $percent = $last_price/$prev_price;
            //获取区间的最高
            $top_price = $this->getBetweenTopPrice($prev_trade_info,$platform);
            //当前价格高于最高价格，是不卖的
            if($last_price >=$top_price){
                return  '';
            }

            if($last_price >= $prev_price  && $percent >=1.005){
                return 'SELL';
            }
        }
        //不做任何处理
        return '';
    }

    /**
     * 获取距离上次买的区间的最高价
     */
    public function getBetweenTopPrice($prev_trade_info,$platform){

        $create_time = $prev_trade_info['create_time'];
        $symbol = $prev_trade_info['symbol'];
        $order_model = new Order();
        $params['cond'] = 'symbol = :symbol and symbol_time >:symbol_time and platform=:platform';
        $params['args'] = [':symbol'=>$symbol,':symbol_time'=>$create_time,':platform'=>$platform];
        $params['orderby'] = ' price desc ';
        $params['limit'] = 1;
        $row = $order_model->findOneByWhere('sdb_symbol_price', $params, $order_model::getDb());
        $top_price = $row['price'];

        return $top_price ;
    }
    /**
     * 判断是否连续上涨
     * @param $symbol
     * @param $num
     * @param $lastest_price 最新价格
     * @param $is_check_having_up 是否检查连续上涨
     */
    public function checkLianxuUp($symbol,$num,$lastest_price,$platform){
        return true ;
        $order_model = new Order();
        $params['cond'] = 'symbol = :symbol AND platform =:platform';
        $params['args'] = [':symbol'=>$symbol,':platform'=>strtoupper($platform)];
        $params['orderby'] = ' symbol_time desc ';
        $params['limit'] = $num;
        $list = $order_model->findAllByWhere('sdb_symbol_price', $params, $order_model::getDb());

        if(!$list){
            return false ;
        }

        $all_num = count($list);

        //值越来越小
        for($i=1;$i<$all_num;$i++){
            $prev_key = $i-1 ;
            $now = $list[$i]['price'];
            $prev = $list[$prev_key]['price'];
            if($now >= $prev){
                return  false ;
            }
        }

        //当前最新价格和数据库中的最新价格进行比较
        $first_price = $list[0]['price'];
        if($lastest_price < $first_price){
            return false ;
        }

        $last_key = $all_num-1 ;
        $last_price = $list[$last_key]['price'];
        $percent = $first_price/$last_price;

        if($percent >=1.003){
            return true ;
        }else{
            return false;
        }

    }

    /**
     * 判断是否连续下跌
     * @param $symbol
     * @param $num
     * @param $lastest_price 传入的最新价格
     */
    public function checkLianxuDown($symbol,$num,$lastest_price,$apiKey,$secretKey){

        $order_model = new Order();
        $params['cond'] = 'symbol = :symbol';
        $params['args'] = [':symbol'=>$symbol];
        $params['orderby'] = ' id desc ';
        $params['limit'] = $num;
        $list = $order_model->findAllByWhere('sdb_symbol_price', $params, $order_model::getDb());

        if(!$list){
            return false ;
        }
        //值越来越大
        $all_num = count($list);
        for($i=1;$i<$all_num;$i++){
            $prev_key = $i-1 ;
            $prev = $list[$prev_key]['price'];
            $now = $list[$i]['price'];
            if($now < $prev){
                return  false ;
            }
        }

        $first_price = $list[0]['price'] ;
        if($lastest_price > $first_price){
            return false ;
        }

        $last_key = $all_num-1 ;
        $last_price = $list[$last_key]['price'];
        $percent = $last_price/$first_price;//1.012658
        $down_percent = $this->getDownPercent($symbol,$apiKey,$secretKey);

        if($percent >=$down_percent){
            return true;
        }else{
            return false ;
        }

    }

    //获取下跌的指数
    public function getDownPercent($symbol,$apiKey,$secretKey){

        $order_model = new Order();
        $params['cond'] = ' symbol_key = :symbol_key AND apiKey =:apiKey AND secretKey = :secretKey';
        $params['args'] = [':symbol_key'=>$symbol,':apiKey'=>$apiKey,':secretKey'=>$secretKey];
        $params['orderby'] = ' id desc ';
        $params['limit'] = 1;
        $list = $order_model->findAllByWhere('sdb_user_symbol', $params, $order_model::getDb());

        $down_percent =  $list[0]['down_percent'];
        $down_percent = $down_percent/100;
        $down_percent = 1+floatval($down_percent) ;

        return $down_percent ;
    }

    /**
     * 按照指定价格进行卖
     * @param $price
     * @param $apiKey
     * @param $secretKey
     * @param $symbol
     * @param $price
     * @param $user_symbol_id
     */
    public function doSell($apiKey,$secretKey,$symbol,$price=0,$user_symbol_id){

        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $symbol_arr =explode('_',$symbol);
        $coin_type =$symbol_arr[0];
        //当前账户 拥有当前币种 的数目
        $amount = $this->getUserAccountByType($coin_type,$apiKey,$secretKey);

        $params = array('api_key' => $apiKey, 'symbol' => $symbol, 'type' => 'sell','price'=>$price,'amount' => $amount);
        $rst = $client -> tradeApi($params);
        $rst = (array)$rst ;
        $order_id = isset($rst['order_id'])?$rst['order_id']:0;

        $order_model = new Order();
        $log_data['symbol'] = $symbol ;
        $log_data['type'] = 'SELL' ;
        $log_data['response_data'] = json_encode($rst);
        $log_data['create_time'] = date('Y-m-d H:i:s');
        $order_model->baseInsert('sdb_trade_log',$log_data,'db_okex');

        if($order_id){
            $add_data['apiKey'] = $apiKey;
            $add_data['order_id'] = $order_id;
            $add_data['secretKey'] = $secretKey;
            $add_data['price'] = $price;
            $add_data['amount'] = $amount;
            $add_data['symbol'] = $symbol;
            $add_data['status'] = 2;
            $add_data['type'] = 'SELL';
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['update_time'] = date('Y-m-d H:i:s');
            $add_data['user_symbol_id'] = $user_symbol_id;
            
            $order_model = new Order();
            $id = $order_model->baseInsert($order_model::tableName(),$add_data,'db_okex');

            //新增营收信息
            //$add_data['id'] = $id ;
            //$order_model->addIncomeInfo($add_data);
        }

        return $order_id ;
    }

    /**
     * 执行买的价格
     * @param $price
     * @param $apiKey
     * @param $secretKey
     * @param $symbol
     * @param $usdt_num 交易数量
     * @param $user_symbol_id 用户币种ID
     */
    private function doBuy($price,$apiKey,$secretKey,$symbol,$usdt_num,$user_symbol_id){

        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $price = $price*$this->upper_percent;//模拟市价单销售
        if($this->is_need_add_extra){
            $amount = $this->add_extra_amount;
        }else{
            $amount = $usdt_num/$price ;
        }

        //$params = array('api_key' => $apiKey, 'symbol' => $symbol.'t', 'type' => 'buy', 'price' => $price, 'amount' => $amount);
        $params = array('api_key' => $apiKey, 'symbol' => $symbol, 'type' => 'buy', 'price' => $price, 'amount' => $amount);
        //$params = array('api_key' => $apiKey, 'symbol' => $symbol, 'type' => 'buy_market', 'amount' => $amount);

        $rst = $client -> tradeApi($params);
        $rst = (array)$rst;

        $order_model = new Order();
        $log_data['symbol'] = $symbol ;
        $log_data['type'] = 'BUY' ;
        $log_data['response_data'] = json_encode($rst);
        $log_data['create_time'] = date('Y-m-d H:i:s');
        $order_model->baseInsert('sdb_trade_log',$log_data,'db_okex');
        //记录数据库
        $order_id = isset($rst['result'])?$rst['order_id']:0;
        if($order_id){
            $add_data['apiKey'] = $apiKey;
            $add_data['order_id'] = $order_id;
            $add_data['secretKey'] = $secretKey;
            $add_data['price'] = $price;
            $add_data['amount'] = $amount;
            $add_data['symbol'] = $symbol;
            $add_data['status'] = 2;//设置默认状态为成交 要不然会影响均值判断
            $add_data['type'] = 'BUY';
            $add_data['is_add_extra'] = 0;//是否为补仓购买操作
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['update_time'] = date('Y-m-d H:i:s');
            $user_symbol_model = new UserSymbol();
            $add_data['user_symbol_id'] = $user_symbol_id;

            $order_model->baseInsert($order_model::tableName(),$add_data,'db_okex');
        }
    }


    /**
     * 取消订单
     * @param $price
     * @param $apiKey
     * @param $secretKey
     * @param $symbol
     * @param $order_id
     * @return boolean
     */
    public function doCancel($apiKey,$secretKey,$symbol,$order_id){

        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));

        $params = array('api_key' => $apiKey, 'symbol' => $symbol, 'order_id' => $order_id);
        $rst = $client -> cancelOrderApi($params);
        $rst = (array)$rst ;
        $result = isset($rst['result'])?$rst['result']:false;

        $order_model = new Order();
        $log_data['symbol'] = $symbol ;
        $log_data['type'] = 'CANCEL' ;
        $log_data['response_data'] = json_encode($rst);
        $log_data['create_time'] = date('Y-m-d H:i:s');
        $order_model->baseInsert('sdb_trade_log',$log_data,'db_okex');

        return $result ;
    }

    /**
     * 判断一分钟之内的下跌幅度
     * @param $symbol
     * @param $last_price
     * @param $apiKey
     * @param $secretKey
     */
    public function checkOneMinuteDown($symbol,$last_price,$apiKey,$secretKey,$platform){

        $model = new Symbol();

        $params['cond'] = 'symbol =:symbol AND platform=:platform';
        $params['args'] =[':symbol'=>$symbol,':platform'=>$platform];
        $params['limit'] = 3 ;
        $params['orderby'] = 'symbol_time desc';
        $list =$model->findAllByWhere('sdb_symbol_price',$params,$model::getDb());

        if(!$list){
            return false;
        }

        $user_symbol_params['cond'] = 'apiKey =:apiKey AND secretKey =:secretKey AND symbol_key =:symbol_key';
        $user_symbol_params['args'] = [':apiKey'=>$apiKey,':secretKey'=>$secretKey,':symbol_key'=>$symbol];
        $user_symbol_row = $model->findOneByWhere('sdb_user_symbol',$user_symbol_params,$model::getDb());

        $baodie_zhisun_percent = $user_symbol_row['baodie_zhisun_percent'];

        if($baodie_zhisun_percent ==0){
            return false ;
        }

        $baodie_zhisun_percent = $baodie_zhisun_percent/100;
        $baodie_zhisun_percent = $baodie_zhisun_percent+1;

        //价格下跌
        if($list[0]['price'] > $last_price){
            $percent =  $list[0]['price']/$last_price ;
            if($percent >=$baodie_zhisun_percent){
                return true ;
            }
        }

        if($list[1]['price'] > $last_price){
            $percent =  $list[1]['price']/$last_price ;
            if($percent >=$baodie_zhisun_percent){
                return true ;
            }
        }

        if($list[2]['price'] > $last_price){
            $percent =  $list[2]['price']/$last_price ;
            if($percent >=$baodie_zhisun_percent){
                return true ;
            }
        }


        return false;
    }

    /**
     * 获取基础购买的数量
     * @param $baocang_buy_times
     * @param $bucang_buy_beishu
     * @param $total
     * @param $prev_trade_info
     * @return float|int|string
     */
    public function getBaseBuyNum($bucang_times,$bucang_buy_beishu,$total,$prev_trade_info){

        if($prev_trade_info&& $prev_trade_info['type'] =='BUY'){
            $rst = $prev_trade_info['amount']*$prev_trade_info['price'];

        }else{

            $total_time = $bucang_times+1 ;

            $temp_total = 1 ;
            $base  =1 ;
            for($i=1;$i<$total_time;$i++){
                $temp_total = $temp_total + $base*$bucang_buy_beishu ;
                $base = $base*$bucang_buy_beishu;
            }

            $rst = $total/$temp_total;

        }

        $rst = sprintf('%.2f', $rst);
        return $rst ;

    }


}
?>