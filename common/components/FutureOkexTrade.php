<?php
namespace common\components;

use common\models\CancelSymbolTask;
use common\models\FutureOrder;
use common\models\FutureSymbolMacd;
use common\models\FutureSymbolPrice;
use common\models\Order;
use common\models\Symbol;
use common\models\SymbolMacd;
use common\models\SymbolPriceUpperLog;
use common\models\UserSymbol;
use yii\web\User;

require_once (dirname(__FILE__) . '/OKCoin/OKCoin.php');

class FutureOkexTrade {

    public $apiKey = '05af4d5b-2eeb-43f1-932f-2f14b2c7cf6d';
    public $secretKey = 'C12EFA962DED5080CBB37EEA4AB9A48F';

    /**
     * @delete
     */
    public function __construct() {

    }

    /**
     * 获取指定指定币种的账户余额
     * @param $symbol
     * @return int
     */
    public function getBalanceBySymbol($symbol){

        $apiKey = $this->apiKey ;
        $secretKey = $this->secretKey ;
        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $params['api_key'] = $apiKey ;
        $result = $client -> fixUserinfoFutureApi($params);
        $result = object_to_array($result) ;

        $balance = isset($result['info'][$symbol]['balance'])?$result['info'][$symbol]['balance']:0 ;
        return $balance ;
    }

    /**
     * 获取指定币种的订单列表
     * @param $symbol_key
     * @return array|mixed|void
     */
    public function getUserOrderList($symbol_key,$order_id){
        $apiKey = $this->apiKey ;
        $secretKey = $this->secretKey ;
        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $params = array('api_key' => $apiKey, 'symbol' =>$symbol_key,'order_id'=>$order_id, 'contract_type' => 'this_week');
        $result = $client->getOrderFutureApi($params);
        $result = object_to_array($result) ;
        $orders = isset($result['orders'])?$result['orders']:array();
        if($orders){
            foreach($orders as $v){
                if($v['order_id'] ==$order_id){
                    return $v ;
                }
            }
        }
        return array() ;
    }

    public function getAllOrderList($symbol_key,$order_id='-1',$status=2){
        $apiKey = $this->apiKey ;
        $secretKey = $this->secretKey ;
        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $params = array('api_key' => $apiKey, 'symbol' =>$symbol_key,'order_id'=>$order_id, 'contract_type' => 'this_week');
        $params['status'] =$status ;
        $params['current_page'] =1 ;
        $params['page_length'] =5 ;
        $result = $client->getOrderFutureApi($params);
        $result = object_to_array($result) ;
        $orders = isset($result['orders'])?$result['orders']:array();
        return $orders ;
    }

    /**
     * 获取当前价格
     * @param $symbol eos_usd
     * @return int
     */
    public function getCurrentPrice($symbol){

        $url = "https://www.okex.com/api/v1/future_kline.do?symbol=".$symbol."&type=1min&contract_type=this_week&size=1";
        $rst= curl_get_https($url);
        $arr = json_decode($rst,true);
        return isset($arr[0][4])?$arr[0][4]:0 ;
    }

    /**
     * 获取已经联系购买的次数
     * @param $list
     * @param $type
     * @return int
     */
    public function getAddTimes($list,$type){
        $times = 0 ;
        if($list){
            foreach($list as $v){

                if($v['type'] ==$type){
                    $times++ ;
                }else{
                    break ;
                }
            }
        }

        return $times;
    }

    /**执行交易
     * @param $symbol
     * @param $type
     * @param $is_direct_deal 是否直接进行交易
     * @return bool
     */
    public function doTrade1($symbol,$type,$is_direct_deal=false){

        $model = new  CancelSymbolTask();
        $params['cond'] = 'symbol=:symbol and (type=:type1 or type=:type2) AND status != :status ';
        $params['args'] = [':symbol'=>$symbol,':type1'=>$type,':type2'=>$type+2,':status'=>-1];
        $params['orderby'] = 'create_date desc ,id desc';
        $params['limit'] = 5 ;
        $list = $model->findAllByWhere('sdb_future_order',$params,$model::getDb());

        if($is_direct_deal){
            //直接进行卖操作 不能通过数据库查询
            $price_info = $this->getFuturePosition($symbol);
            if(!$price_info){
                return false;
            }

            $sell_num = $price_info['buy_available'] ;
            $buy_price_avg = $price_info['buy_price_avg'] ;
            $sell_price = ($type==1 || $type==3 ) ?$buy_price_avg*1.0005:$buy_price_avg*(1-0.0005)  ;
            $this->doBuyOrSell($symbol,$sell_price,$sell_num,$type+2) ;
            return true ;
        }
        $buy_num_arr = array(1,2,4);
        if($list){
            $last_row = $list[0];

            //最后一单交易没有完成不进行下一一笔叫i
            if($last_row['status']!=2){
                return false;
            }

            if($last_row['type'] ==($type+2)){
                $current_price = $this->getCurrentPrice($symbol);
                //买
                $this->doBuyOrSell($symbol,$current_price,1,$type) ;
            }else{

                $current_price = $this->getCurrentPrice($symbol);
                $avg_price = $this->getAvgPriceByList($list,$type) ;
                if($type==1) {

                    if ($current_price > $avg_price) {
                        $percent = $current_price/$avg_price ;
                        if($percent >= 1.001){
                            //卖
                            $sell_num = $this->getTotalSellAmount($list,$type) ;
                            $this->doBuyOrSell($symbol,$current_price,$sell_num,$type+2) ;
                        }
                    }else{
                        $percent = $avg_price/$current_price;
                        if($percent >= 1.035){
                            //止损 卖
                            $sell_num = $this->getTotalSellAmount($list,$type) ;
                            $this->doBuyOrSell($symbol,$current_price,$sell_num,$type+2) ;
                        }else if($percent>=1.01 && $percent <=1.03){
                            //补仓
                            $is_add_extra = $this->getAddTimes($list,$type) ;
                            if($is_add_extra ==3){
                                //不操作
                            }else{
                                //补仓 操作
                                $buy_num  =$buy_num_arr[$is_add_extra];
                                //买
                                $this->doBuyOrSell($symbol,$current_price,$buy_num,$type) ;
                            }
                        }

                    }
                }else{
                    //下跌幅度
                    if ($avg_price > $current_price) {
                        $percent = $avg_price/$current_price ;
                        if($percent >= 1.001){
                            //卖
                            $sell_num = $this->getTotalSellAmount($list,$type) ;
                            $this->doBuyOrSell($symbol,$current_price,$sell_num,$type+2) ;
                        }
                    }else{
                        $percent = $current_price/$avg_price;
                        if($percent >= 1.035){
                            //止损 卖
                            $sell_num = $this->getTotalSellAmount($list,$type) ;
                            $this->doBuyOrSell($symbol,$current_price,$sell_num,$type+2) ;
                        }else if($percent>=1.01 && $percent <=1.03){
                            //补仓
                            $is_add_extra = $this->getAddTimes($list,$type) ;
                            if($is_add_extra ==3){
                                //不操作
                            }else{
                                //补仓 操作
                                $buy_num  =$buy_num_arr[$is_add_extra];
                                //买
                                $this->doBuyOrSell($symbol,$current_price,$buy_num,$type) ;
                            }
                        }

                    }
                }
            }


        }else{
            //直接买
            $current_price = $this->getCurrentPrice($symbol);

            $buy_num = 1 ;
            //买
            $this->doBuyOrSell($symbol,$current_price,$buy_num,$type) ;
        }


    }

    /**执行交易
     * @param $symbol
     * @param $buy_type 允许的购买类型
     * @param $current_price
     * @param $buy_price
     * @return bool
     */
    public function doTrade($symbol,$buy_type,$current_price,$buy_price){

        $model = new  CancelSymbolTask();
        $params['cond'] = 'symbol=:symbol  AND status != :status ';
        $params['args'] = [':symbol'=>$symbol,':status'=>-1];
        $params['orderby'] = 'create_date desc,type desc';
        $params['limit'] = 1 ;
        $list = $model->findAllByWhere('sdb_future_order',$params,$model::getDb());


        $base_buy_num =1 ;
        $ext_percent = 0.002 ;
        $p1_percent = 1+$ext_percent ;
        $p2_percent = 1-$ext_percent ;
        if($list){
            $last_row = $list[0];

            //最后一单交易没有完成不进行下一一笔叫i
            if($last_row['status']!=2){
                return false;
            }

            if($last_row['type'] >2){
                 //买
                $this->doBuyOrSell($symbol,$buy_price,$base_buy_num,$buy_type) ;
            }else{

                $avg_price = $this->getAvgPriceByList($list,$last_row['type']) ;
                if($last_row['type']==1) {

                    $percent = $current_price/$avg_price;
                    //盈利
                    if($percent >= $p1_percent){
                        //卖
                        $sell_num = $this->getTotalSellAmount($list,$last_row['type']) ;
                        $this->doBuyOrSell($symbol,$current_price,$sell_num,$last_row['type']+2) ;
                    }else{
                        $sell_num = $this->getTotalSellAmount($list,$last_row['type']) ;

                        //判断是否亏损
                        $kuisun_percent = $avg_price/$current_price ;
                        if($kuisun_percent >= 1.003){
                            $this->doBuyOrSell($symbol,$current_price,$sell_num,$last_row['type']+2,true) ;
                        }else{
                            //按照千分之一卖
                            $sell_price = $avg_price*$p1_percent ;
                            $this->doBuyOrSell($symbol,$sell_price,$sell_num,$last_row['type']+2) ;
                        }

                    }
                }else{
                    $avg_price = $this->getAvgPriceByList($list,$last_row['type']) ;
                    $percent = $avg_price/$current_price;
                    //盈利
                    if($percent >= $p1_percent){
                        //卖
                        $sell_num = $this->getTotalSellAmount($list,$last_row['type']) ;
                        $this->doBuyOrSell($symbol,$current_price,$sell_num,$last_row['type']+2) ;
                    }else{
                        $kuisun_percent = $current_price/$avg_price ;
                        $sell_num = $this->getTotalSellAmount($list,$last_row['type']) ;
                        if($kuisun_percent>=1.003){
                            $this->doBuyOrSell($symbol,$current_price,$sell_num,$last_row['type']+2,true) ;
                        }else{
                            //按照千分之一卖
                            $sell_price = $avg_price*$p2_percent ;
                            $this->doBuyOrSell($symbol,$sell_price,$sell_num,$last_row['type']+2) ;
                        }
                    }
                }
            }


        }else{
            //买
            $this->doBuyOrSell($symbol,$buy_price,$base_buy_num,$buy_type) ;
        }


    }

    public function doBuyOrSell($symbol,$price,$buy_num,$type,$is_zhisun= false ){
        $apiKey = $this->apiKey ;
        $secretKey = $this->secretKey ;
        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $params = array('api_key' => $apiKey, 'symbol' => $symbol, 'contract_type' => 'this_week', 'price' => $price, 'amount' => $buy_num, 'type' => $type, 'lever_rate' => 20);
        if($type >2 && $is_zhisun){
            //直接使用个对手价
            $params['match_price'] = 1;
        }
        $result = $client -> tradeFutureApi($params);
        $result = object_to_array($result) ;
        if(isset($result['result']) && $result['result']){
            $order_id = $result['order_id'];
            $this->handlePushTask($order_id,$symbol);
            return $order_id ;
        }else{
            return false ;
        }
    }

    /**
     * 获取平均价格
     * @param $list
     * @param $type
     * @return float|int
     */
    public function getAvgPriceByList($list,$type){
        $avg = 0 ;
        if($list){
            $total_amount = 0 ;
            $total_price = 0 ;
            foreach($list as $v){
                if($v['type'] ==$type){
                    $total_amount += $v['deal_amount'];
                    $price = $v['price_avg']?$v['price_avg']:$v['price'];
                    $total_price += $v['deal_amount']*$price;
                }else{
                    break ;
                }
            }
            if($total_amount){
                $avg = $total_price/$total_amount ;
            }
        }
        return $avg ;
    }

    /**
     * 获取平均价格
     * @param $list
     * @param $type
     * @return float|int
     */
    public function getTotalDealAmountByList($list,$type){
        $total_amount = 0 ;
        if($list){

            foreach($list as $v){
                if($v['type'] ==$type){
                    $total_amount += $v['deal_amount'];
                }
            }
        }

        return $total_amount ;
    }

    public function getTotalSellAmount($list,$type){
        $total_amount = 0 ; ;
        if($list){
            $total_amount = 0 ;
            foreach($list as $v){
                if($v['type'] ==$type){
                    $total_amount += $v['deal_amount'];

                }else{
                    break ;
                }
            }

        }
        return $total_amount ;
    }

    public function handlePushTask($order_id,$symbol_key){
        $rst = $this->getUserOrderList($symbol_key,$order_id) ;

        $add_data['order_id']  = $rst['order_id'] ;
        $add_data['contract_name']  = $rst['contract_name'] ;
        $add_data['create_date']  = $rst['create_date'] ;
        $add_data['create_date_str']  = date('Y-m-d H:i:s',$rst['create_date']/1000) ;
        $add_data['amount']  = $rst['amount'] ;
        $add_data['deal_amount']  = $rst['deal_amount'] ;
        $add_data['fee']  = $rst['fee'] ;
        $add_data['price']  = $rst['price'] ;
        $add_data['price_avg']  = $rst['price_avg'] ;
        $add_data['status']  = $rst['status'] ;
        $add_data['symbol']  = $rst['symbol'] ;
        $add_data['type']  = $rst['type'] ;
        $add_data['unit_amount']  = $rst['unit_amount'] ;
        $add_data['lever_rate']  = $rst['lever_rate'] ;
        $add_data['create_time']  = date('Y-m-d H:i:s') ;
        $add_data['update_time']  = date('Y-m-d H:i:s') ;
        $add_data['apiKey']  = $this->apiKey ;
        $add_data['secretKey']  = $this->secretKey ;

        $model = new CancelSymbolTask() ;
        $model->baseInsert('sdb_future_order',$add_data,'db_okex');

        if($rst['type'] <3){
            //直接卖
            $price = $rst['type'] ==1 ?$add_data['price']*1.001:$add_data['price']*(1-0.001) ;
            $this->doBuyOrSell($rst['symbol'],$price,$rst['amount'],$rst['type']+2) ;
        }

    }


    //取消订单
    public function cancelOrder($row){
        $apiKey = $row['apiKey'];
        $secretKey = $row['secretKey'];
        $symbol = $row['symbol'];
        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $params = array('api_key' => $apiKey, 'symbol' => $symbol, 'order_id' => $row['order_id'], 'contract_type' => 'this_week');
        $result = $client -> cancelFutureApi($params);
    }

    /**获取所有的可操作币种
     * @return array
     */
    public function getSymbolList($b = true){

        if($b){
            $params['cond'] = ' is_open =:is_open';
            $params['args'] = [':is_open'=>'Y'];

        }else{
            $params = [];
        }

        $model = new CancelSymbolTask() ;
        $list = $model->findAllByWhere('sdb_future_setting',$params,$model::getDb());
        return $list ;
    }

    /**
     * 获取购买类型
     * @param $symbol
     * @param $current_price
     */
    public function getBuyType($symbol,$current_price){

        //获取macd信息
        $macd_model = new FutureSymbolMacd();
        $macd_params['orderby'] = 'time_str desc';
        $macd_params['cond'] = 'curr_a =:curr_a AND curr_b =:curr_b';
        $symbol_arr = explode('_',$symbol);
        $macd_params['args'] = [':curr_a'=>$symbol_arr[0],':curr_b'=>$symbol_arr[1]];
        $macd_row = $macd_model->findOneByWhere($macd_model::tableName(),$macd_params,$macd_model::getDb());
        if($macd_row['macd']>0){
            $type =1;
        }else{
            $type = 2;
        }

        //获取最高价
        $price_params['cond'] = 'symbol_time_str >=:symbol_time_str and symbol=:symbol';
        $time_str = time()-86400;
        $price_params['args'] = [':symbol_time_str'=>$time_str,':symbol'=>$symbol];
        $price_params['orderby'] = 'price desc';
        $price_model = new FutureSymbolPrice();
        $price_list = $price_model->findAllByWhere($price_model::tableName(),$price_params,$price_model::getDb());
        //获取最低价
        return $type ;
        $top_price = $price_list[0]['price'] ;
        $bottom_price = $price_list[count($price_list)-1]['price'] ;
        $top_price = $top_price*0.9 ;
        $bottom_price = $bottom_price*1.1;
        if($current_price>=$bottom_price && $current_price<=$top_price){
            return $type ;
        }else{
            return 0 ;
        }

    }

    /**
     * 同时执行两种交易
     * @param $symbol
     */
    public function doTwoTrade($symbol){
        $current_price = $this->getCurrentPrice($symbol);
        $this->doBuyBySymbol($symbol,$current_price) ;
        //sleep(3);
        $this->doSellBySymbol($symbol,1,$current_price) ;
        $this->doSellBySymbol($symbol,2,$current_price) ;
    }
    /**
     * 根据币种
     * @param $symbol
     * @param $current_price
     */
    public function doBuyBySymbol($symbol,$current_price){

        //step1 最多同时10比订单同时买
        $order_params['cond'] = 'symbol =:symbol AND  status != :status AND sell_id = :sell_id AND (type =1 or type =2)';
        $order_params['args'] = [':symbol'=>$symbol,':status'=>'-1',':sell_id'=>0];
        $order_params['fields'] = 'id' ;
        $order_model = new FutureOrder();
        $order_list = $order_model->findAllByWhere($order_model::tableName(),$order_params,$order_model::getDb());
        $total_order_num = count($order_list);
        if($total_order_num >=10){
            return false ;
        }

        $buy_type = $this->getBuyType($symbol,$current_price);
        if($buy_type > 0){
            $sell_num = 2  ;
            $this->doBuyOrSell($symbol,$current_price,$sell_num,$buy_type);
        }else{
            return false ;
        }


    }

    public function doSellBySymbol($symbol,$type,$current_price){

        //step1 获取没有卖单操作的
        $order_params['cond'] = 'symbol =:symbol AND  status = :status AND sell_id = :sell_id AND type=:type ';
        $order_params['args'] = [':symbol'=>$symbol,':status'=>2,':sell_id'=>0,':type'=>$type];
        $order_params['fields'] = '*' ;
        $order_model = new FutureOrder();
        $order_list = $order_model->findAllByWhere($order_model::tableName(),$order_params,$order_model::getDb());

        if(!$order_list){
            return false ;
        }

        $price = $this->getAvgPriceByList($order_list,$type) ;
        $sell_num = $this->getTotalSellAmount($order_list,$type);
        if($type==1){
            $price=$price*1.001;
            if($price >=$current_price){
                //卖
                $order_id = $this->doBuyOrSell($symbol,$current_price,$sell_num,3);

            }else{
                //止损
                $percent = $price/$current_price;
                if($percent >= 1.01){
                    $order_id = $this->doBuyOrSell($symbol,$price,$sell_num,3);
                }

            }
        }

        if($type==2){
            $price = $price*(1-0.001) ;
            if($current_price <= $price*(1-0.001)){
                //卖
                $order_id = $this->doBuyOrSell($symbol,$current_price,$sell_num,4);

            }else{
                //止损
                $percent = $current_price/$price ;
                if($percent >= 1.01){
                    $order_id = $this->doBuyOrSell($symbol,$price,$sell_num,4);
                }

            }
        }


    }

    /**
     * 获取最高和最低价
     * @param $symbol
     * @param int $minute
     */
    public function getTopAndLowPrice($symbol,$minute=3){
        $params['cond']  = 'symbol =:symbol and group_second=:group_second';
        $params['args'] = [':symbol'=>$symbol,':group_second'=>$minute*60];
        $params['orderby'] = 'time_str desc' ;
        $model = new Order();
        $row = $model->findOneByWhere('sdb_future_symbol_macd',$params,$model::getDb());
        $top_price = $row['top_price'];
        $low_price = $row['low_price'];

        return compact('top_price','low_price');
    }

    public function getTypeByMacd($symbol,$minute=3){
        $symbol_arr = explode('_',$symbol);
        $curr_a = $symbol_arr[0];
        $curr_b = $symbol_arr[1];
        $params['cond']  = 'curr_a =:curr_a AND curr_b =:curr_b and group_second=:group_second';
        $params['args'] = [':curr_a'=>$curr_a,':curr_b'=>$curr_b,':group_second'=>$minute*60];
        $params['orderby'] = 'time_str desc' ;
        $params['limit'] =2;
        $model = new Order();
        $list = $model->findAllByWhere('sdb_future_symbol_macd',$params,$model::getDb());

        $type = 0 ;
        $price = 0 ;
        if($list[0]['macd'] > $list[1]['macd'] ){
            if($list[0]['dif'] > $list[1]['dif'] && $list[0]['dea'] > $list[1]['dea']){
                $type = 1  ;
                $price = $list[0]['top_price']*(1-0.0002);
            }
        }

        if($list[0]['macd'] < $list[1]['macd'] ){
            if($list[0]['dif'] < $list[1]['dif'] && $list[0]['dea'] < $list[1]['dea']){
                $type = 2  ;
                $price = $list[0]['low_price']*(1+0.0002);

            }
        }

        //判断当前区间段是否已经购买过
        return compact('type','price');
    }

}
