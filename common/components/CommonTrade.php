<?php
namespace common\components;
use common\models\SymbolMacd;

/**
 * Class GateTrade
 * okex交易
 * @package common\components
 */
class CommonTrade
{

    public $platform_type = '';

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
        if($this->platform_type=='gate'){
            $url = 'https://data.gateio.io/api2/1/candlestick2/' . $curr_a . '_' . $curr_b . '?group_sec=' . $group_sec . '&range_hour=' . $range_hour;

        }

        if($this->platform_type =='okex'){
            $min = $group_sec/60;
            $size = $range_hour*60/$min ;
            $url = 'https://www.okex.com/api/v1/kline.do?symbol='.$curr_a.'_'.$curr_b.'&type='.$min.'min&size='.$size;
        }

        if($this->platform_type =='huobi'){
            $min = $group_sec/60;
            $size = $range_hour*60/$min ;
            $url= 'https://api.huobipro.com/market/history/kline?period='.$min.'min&size='.$size.'&symbol='.$curr_a.$curr_b;
        }
        return $url;
    }

    /**
     * 根据url 返回指定数据
     * @param $url
     * @return array
     */
    public function getDataByUrl($url)
    {
        if($this->platform_type =='gate'){
            $rst = file_get_contents($url);
        }else{
            $rst = curlGo($url);
        }

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
     * 根据合并秒数周期获得分钟的时间
     * @param $group_sec
     * @return integer
     */
    public function getMinuteByGroupSec($group_sec){

        //判断周期标准
        $stad = $group_sec/60;

        //当前分钟数
        $now = date('i');

        $rst = floor($now/$stad) ;

        $rst = $rst*$stad ;

        return $rst ;
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
        $info = $model->findAllByWhere('sdb_symbol_macd',$params,$model::getDb());
        return isset($info[0])?$info[0]:array() ;
    }

    public function getTotalDif($time_str,$curr_a,$curr_b,$platform,$num=8){
        $params['cond'] = ' time_str < :time_str AND curr_a =:curr_a AND curr_b =:curr_b AND platform =:platform';
        $params['args'] = [':time_str'=>$time_str,':curr_a'=>$curr_a,':curr_b'=>$curr_b,':platform'=>$platform];
        $params['orderby'] = 'time_str DESC ' ;
        $params['limit'] = $num ;
        $model = new SymbolMacd();
        $list = $model->findAllByWhere('sdb_symbol_macd',$params,$model::getDb());

        $total = 0 ;
        if($total){
            foreach($list as $v){
                $total += $v['dif'];
            }
        }
        return $total ;
    }

    /**
     * 获取判断交叉需要的必须的数据
     * @param string $curr_a
     * @param string $curr_b
     * @param int $group_second //合并展示时间 15分钟  $group_second = 60*15 ;
     * @param string $platform_type //平台类型 ;
     */
    public function getCrossData($curr_a='', $curr_b = 'usdt',$group_second,$platform_type='gate'){

        $this->platform_type = $platform_type;

        $trade_model = new CommonTrade();
        $minute = $trade_model->getMinuteByGroupSec($group_second);
        $time = date('Y-m-d H:'.$minute.':00');
        $time_str  = strtotime($time);
        $redis_key = "Common::".$platform_type.$curr_a.$curr_b.$group_second.':price:'.$time_str;
        $redis_model = new MyRedis();
        $redis_info =$redis_model->get($redis_key);
        if(!$redis_info){

            //查询总天数数据
            $total_days = 24*11;
            $total_arr = $this->getDataByDay($curr_a,$curr_b,$group_second,$total_days,$platform_type);
            $total_arr = $this->formateData($total_arr);
            $redis_model->set($redis_key,json_encode($total_arr),50);//秒数不能超过一分钟
        }else{
            $total_arr = json_decode($redis_info,true);
        }

        $prev_cross_data = $this->getPrevCrossData($time_str,$curr_a,$curr_b,$platform_type,$group_second);
        if(isset($prev_cross_data['time_str']) ){
            $current_time_str  = (isset($prev_cross_data['time_str'])?$prev_cross_data['time_str']:0) + $group_second;
        }else{
            $current_time_str  = $time_str ;
        }

        //5/15/30分钟均值
        $five_avg = $this->getAvg($total_arr,5,$group_second,$current_time_str);
        $ten_avg = $this->getAvg($total_arr,10,$group_second,$current_time_str);
        $thirty_avg = $this->getAvg($total_arr,30,$group_second,$current_time_str);
        if($platform_type=='huobi'){
            $five_avg = numToStr($five_avg);
            $ten_avg = numToStr($ten_avg);
            $thirty_avg = numToStr($thirty_avg);
        }
        if(!$prev_cross_data) {

            $dif = 0;
            $dea = 0;
            $macd = 0;
            $i = 0 ;
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
            return compact('five_avg','ten_avg','thirty_avg','dif','dea','macd','time_str','curr_a','curr_b','ema_12','ema_26','price') ;
        }

        $time_str = $current_time_str;
        if(isset($total_arr[$current_time_str])){

            $price = $total_arr[$current_time_str];
            $price = numToStr($price);

            //EMA(12)=前一日EMA(12)×11/13＋当日收盘价×2/13
            $ema_12 = $this->getEma(12,$price,$prev_cross_data['ema_12']);
            $ema_26 = $this->getEma(26,$price,$prev_cross_data['ema_26']);

             $dif  = $ema_12 -$ema_26;

            //最近9日的DIF之和/9
            $dea = 0.8*$prev_cross_data['dea'] +0.2*$dif;
            $macd = ($dif-$dea)*2 ;

            return compact('five_avg','ten_avg','thirty_avg','dif','dea','macd','time_str','curr_a','curr_b','ema_12','ema_26','price') ;

        }else{

            return array();

        }

    }

}
