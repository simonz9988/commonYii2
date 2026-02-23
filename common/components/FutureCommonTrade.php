<?php
namespace common\components;
use common\models\FutureSymbolPrice;
use common\models\SymbolMacd;

/**
 * Class FutureCommonTrade
 * okex交易
 * @package common\components
 */
class FutureCommonTrade
{

    public $platform_type = '';
    public $okex_host = "www.okb.com";

    public function __construct()
    {

    }

    /**
     * @param $curr_a     交易币种
     * @param $curr_b     法币币种
     * @param $group_sec  时间周期(单位秒)
     * @param $range_hour 查询时间范围(单位小时)
     * @param $platform_type 查询时间范围(单位小时)
     * @return string
     */
    public function getTradeUrl($curr_a, $curr_b, $group_sec, $range_hour,$platform)
    {
        $this->platform_type = strtolower($platform);

        if ($this->platform_type == 'okex') {
            $min = $group_sec / 60;
            $size = $range_hour * 60 / $min;
            $url = 'https://'.$this->okex_host.'/api/v1/future_kline.do?symbol=' . $curr_a . '_' . $curr_b . '&type=' . $min . 'min&size=' . $size.'&contract_type=this_week';
        }

        return $url;
    }

    /**
     * 获取指定天数内的交易信息
     * @param string $curr_a
     * @param string $curr_b
     * @param int $group_sec
     * @param int $range_hour
     * @return false|array
     */
    public function getDataByDay($curr_a, $curr_b, $group_sec, $range_hour,$platform)
    {
        $url = $this->getTradeUrl($curr_a, $curr_b , $group_sec, $range_hour,$platform) ;
        $rst = $this->getDataByUrl($url);
        return $rst;
    }

    /**
     * 根据url 返回指定数据
     * @param $url
     * @return array
     */
    public function getDataByUrl($url)
    {
        $rst = file_get_contents($url);

        $arr = json_decode($rst, true);
        if($this->platform_type =='gate'){
            return isset($arr['data']) ? $arr['data'] : array();
        }

        if($this->platform_type =='okex') {
            return $arr;
        }

        if($this->platform_type =='huobi'){
            return isset($arr['data']) ? $arr['data'] : array();
        }
    }

    /**
     * 格式化数据信息
     * @param $data
     */
    public function formateData($data){
        $rst = array();
        if($data){
            foreach($data as $v){

                if($this->platform_type =='gate'){
                    $key = $v[0]/1000;
                    $rst[$key] = $v[2];
                }

                if($this->platform_type =='okex'){
                    $key = $v[0]/1000;
                    $rst[$key] = $v[4];
                }

                if($this->platform_type =='huobi'){
                    $rst[$v['id']] = numToStr($v['close']);
                }

            }
        }
        ksort($rst);
        return $rst ;
    }

    /**
     * 格式化数据信息
     * @param $data
     */
    public function formateTopLowPrice($data){
        $rst = array();
        if($data){
            foreach($data as $v){

                if($this->platform_type =='gate'){
                    $key = $v[0]/1000;
                    $rst[$key] = $v[2];
                }

                if($this->platform_type =='okex'){
                    $key = $v[0]/1000;
                    $rst[$key]['top_price'] = $v[2];
                    $rst[$key]['low_price'] = $v[3];
                }

                if($this->platform_type =='huobi'){
                    $rst[$v['id']] = numToStr($v['close']);
                }

            }
        }
        ksort($rst);
        return $rst ;
    }

    /**
     * @param $symbol
     * 增加币种每分钟的价格
     */
    public function addSymbolPrice($symbol,$platform='OKEX'){
        $this->platform_type = $platform ;
        $group_second = 60 ;
        $total_days = 0.1 ;
        $symbol_arr = explode('_',$symbol);
        $curr_a = $symbol_arr[0];
        $curr_b = $symbol_arr[1];
        $total_arr = $this->getDataByDay($curr_a,$curr_b,$group_second,$total_days,$this->platform_type);

        $list_data = $this->formateData($total_arr);

        if($list_data){
            $model = new FutureSymbolPrice();
            foreach($list_data as $data_k=>$data_v){
                //判断是否已经插入
                $data_params['cond'] = 'platform =:platform AND symbol=:symbol AND symbol_time_str=:symbol_time_str';
                $data_params['args'] = [':platform'=>$platform,':symbol'=>$symbol,':symbol_time_str'=>$data_k];
                $row = $model->findOneByWhere($model::tableName(),$data_params,$model::getDb());
                if(!$row){
                    $add_data['price']=$data_v;
                    $add_data['platform']=$platform;
                    $add_data['symbol']=$symbol;
                    $add_data['symbol_time']=date('Y-m-d H:i:s',$data_k);
                    $add_data['symbol_time_str']=$data_k;
                    $add_data['create_time']=date('Y-m-d H:i:s');
                    $add_data['update_time']=date('Y-m-d H:i:s');
                    $model->baseInsert($model::tableName(),$add_data,'db_okex');
                }
            }
        }

    }

    /**
     * 获取之前的crossData
     * @param $time_str
     * @param $curr_a
     * @param $curr_b
     * @param $platform
     */
    public function getPrevCrossData($time_str,$curr_a,$curr_b,$platform,$group_second){
        $params['cond'] = ' time_str < :time_str AND curr_a =:curr_a AND curr_b =:curr_b AND platform =:platform AND group_second =:group_second';
        $params['args'] = [':time_str'=>$time_str,':curr_a'=>$curr_a,':curr_b'=>$curr_b,':platform'=>$platform,':group_second'=>$group_second];
        $params['orderby'] = 'time_str DESC ' ;
        $params['limit'] = 1 ;
        $model = new SymbolMacd();
        $info = $model->findAllByWhere('sdb_future_symbol_macd',$params,$model::getDb());
        return isset($info[0])?$info[0]:array() ;
    }

    /**
     * 根据总和获取几天内的均值
     * @param $total_arr
     * @param $num 获取区间的数目
     * @param $group_sec 时间区间(单位:秒)
     * @param $str 时间戳信息
     */
    public function getAvg($total_arr,$num,$group_sec,$str){

        $total = 0 ;

        if(!isset($total_arr[$str])){
            $num = $num +1 ;
        }
        for($i=0;$i<$num;$i++){
            if(isset($total_arr[$str])){
                $total = $total+$total_arr[$str];
            }
            $str = $str-$group_sec;
        }
        $avg = $total/$num ;
        return $avg ;
    }

    /**
     * 获取得到EMA的值
     * @param $num
     * @param $price 当前收盘价
     * @param $prev_price 之前收盘价
     * @return mixed
     */
    public function getEma($num,$price,$prev_ema_price){

        return 2*($price-$prev_ema_price)/($num+1) +$prev_ema_price ;
    }

    /**
     * 获取当前时间段
     * @param $prev_cross_data
     * @param $all_data
     * @param $group_second
     * @return mixed
     */
    public function getCurrTimeStr($prev_cross_data,$all_data,$group_second){
        $prev_time_str = $prev_cross_data['time_str'] ;
        $next_k = 0  ;
        $temp_arr = $all_data ;
        $keys = array_keys($temp_arr);
        //var_dump($keys);exit;
        foreach($temp_arr as $k=>$v){
            if($k ==$prev_time_str){
                $current_key = array_search($k, $keys);
                $next_k  = isset($keys[$current_key+1])?$keys[$current_key+1]:0;
            }
        }

        if($next_k){
            $current_time_str = $next_k;
        }else{
            $current_time_str  = $prev_time_str + $group_second;
        }
        return $current_time_str ;
    }


    /**
     * 获取判断交叉需要的必须的数据
     * @param string $curr_a
     * @param string $curr_b
     * @param int $group_second //合并展示时间 15分钟  $group_second = 60*15 ;
     * @param string $platform_type //平台类型 ;
     */
    public function getCrossData($curr_a='', $curr_b = 'usd',$group_second,$platform_type='gate',$total_days =264 ){

        $this->platform_type = $platform_type;

        $trade_model = new CommonTrade();
        $minute = $trade_model->getMinuteByGroupSec($group_second);
        $time = date('Y-m-d H:'.$minute.':00');
        $time_str  = strtotime($time);
        $redis_key = "FutureCommon::".$platform_type.$curr_a.$curr_b.$group_second.':price:'.$time_str;
        $redis_key_price = "FutureCommon::".$platform_type.$curr_a.$curr_b.$group_second.':lowprice:'.$time_str;
        $redis_model = new MyRedis();
        $redis_info =$redis_model->get($redis_key);
        $redis_info_price =$redis_model->get($redis_key_price);
        if(!$redis_info  || !$redis_info_price ){

            //查询总天数数据
            $total_arr = $this->getDataByDay($curr_a,$curr_b,$group_second,$total_days,$platform_type);
            $top_low_price   = $this->formateTopLowPrice($total_arr);
            $total_arr = $this->formateData($total_arr);
            $redis_model->set($redis_key,json_encode($total_arr),50);//秒数不能超过一分钟
            $redis_model->set($redis_key_price,json_encode($top_low_price),50);//秒数不能超过一分钟
        }else{
            $total_arr = json_decode($redis_info,true);
            $top_low_price = json_decode($redis_info_price,true);
        }

        $prev_cross_data = $this->getPrevCrossData($time_str,$curr_a,$curr_b,$platform_type,$group_second);

        if(isset($prev_cross_data['time_str']) ){
            //$current_time_str  = (isset($prev_cross_data['time_str'])?$prev_cross_data['time_str']:0) + $group_second;

            $current_time_str = $this->getCurrTimeStr($prev_cross_data,$total_arr,$group_second) ;

        }else{
            $current_time_str  = $time_str ;
        }

        //5/15/30分钟均值
        $five_avg = $this->getAvg($total_arr,5,$group_second,$current_time_str);
        $ten_avg = $this->getAvg($total_arr,10,$group_second,$current_time_str);
        $thirty_avg = $this->getAvg($total_arr,30,$group_second,$current_time_str);

        if(!$prev_cross_data) {

            $dif = 0;
            $dea = 0;
            $macd = 0;
            $i = 0 ;
            $top_price = 0 ;
            $low_price = 0 ;
            foreach($total_arr as $k=>$v){
                if($i==0){
                    $time_str = $k ;
                    $price= $v ;
                }
                $i++ ;
            }

            $price = numToStr($price);
            $ema_12 = $price;
            $ema_26 = $price;
            return compact('five_avg','ten_avg','thirty_avg','dif','dea','macd','time_str','curr_a','curr_b','ema_12','ema_26','price','top_price','low_price') ;
        }

        $time_str = $current_time_str;
        if(isset($total_arr[$current_time_str])){

            $price = $total_arr[$current_time_str];
            $price = numToStr($price);

            $top_price  = $top_low_price[$current_time_str]['top_price'];
            $top_price = numToStr($top_price);
            $low_price  = $top_low_price[$current_time_str]['low_price'];
            $low_price = numToStr($low_price);

            //EMA(12)=前一日EMA(12)×11/13＋当日收盘价×2/13
            $ema_12 = $this->getEma(12,$price,$prev_cross_data['ema_12']);
            $ema_26 = $this->getEma(26,$price,$prev_cross_data['ema_26']);

            $dif  = $ema_12 -$ema_26;

            //最近9日的DIF之和/9
            $dea = 0.8*$prev_cross_data['dea'] +0.2*$dif;
            $macd = ($dif-$dea)*2 ;

            return compact('five_avg','ten_avg','thirty_avg','dif','dea','macd','time_str','curr_a','curr_b','ema_12','ema_26','price','top_price','low_price') ;

        }else{

            return array();

        }

    }

}
