<?php

namespace common\components;

use common\models\OkexOrder;

class Utils
{
    static $apiKey = '';
    static $apiSecret = '';
    static $passphrase = '';

    static $textToSign = '';

    const FUTURE_API_URL = 'https://www.okexcn.com';
    const SERVER_TIMESTAMP_URL = '/api/general/v3/time';

    public function __construct($configs)
    {
        // 设置参数
        self::setParams($configs);
    }

    public  static  function request($requestPath, $params, $method, $cursor = false)
    {

        if (strtoupper($method) == 'GET') {
            $requestPath .= $params ? '?'.http_build_query($params) : '';
            $params = [];


        }

        $url = self::FUTURE_API_URL.$requestPath;
        $ch= curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        $body = $params ? json_encode($params, JSON_UNESCAPED_SLASHES) : '';
        $timestamp = self::getTimestamp();

        $sign = self::signature($timestamp, $method, $requestPath, $body, self::$apiSecret);
        $headers = self::getHeader(self::$apiKey, $sign, $timestamp, self::$passphrase, self::$textToSign);


        if($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        // 设置超时时间
//        curl_setopt($ch, CURLOPT_TIMEOUT_MS,60);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT,true);



        // 头信息
        curl_setopt($ch, CURLOPT_HEADER, true);
//        curl_setopt($ch, CURLOPT_NOBODY,true);


        $return = curl_exec($ch);

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerTotal = strlen($return);
        $bodySize = $headerTotal - $headerSize;

        $body = substr($return, $headerSize, $bodySize);


        if(!curl_errno($ch))
        {
            $info = curl_getinfo($ch,CURLINFO_HEADER_OUT);

            /*
            print_r(substr($return, 0, $headerSize-2));
            print_r("TIMESTAMP: ".self::getTimestamp());
            print_r("\n\n");

            print_r($body);
            print_r("\n\n");*/

        }

//        $body = substr($sContent, $headerSize, $bodySize);


//        print_r("headerSize:".$headerSize."\n");
//        print_r("headerTotal:".$headerTotal."\n");
//        print_r("bodySize:".$bodySize."\n");

        $body = json_decode($body,true);

        $log_data['url'] = $url ;
        $log_data['params'] = $params;
        $log_data['body'] = $body ;

        //self::addLog('common',time(),$log_data);
        return $body;
    }

    /**
     * 新增日志
     * @param $type
     * @param $unique_id
     * @param $content
     * @return string
     */
    public static function addLog($type,$unique_id,$content){
        $model = new OkexOrder();
        $content = json_encode($content);
        $create_time = date('Y-m-d H:i:s');
        $add_data = compact('type','content','create_time','unique_id');

        // 新增日志
        $log_components = new CommonLogger();
        $log_components->logError("Type:".$type.'========='.json_encode($add_data));


        return $model->baseInsert('sea_common_log',$add_data);
    }

    public static function getHeader($apiKey, $sign, $timestamp, $passphrase, $textToSign)
    {
        $headers = array();

        $headers[] = "Content-Type: application/json";
        $headers[] = "OK-ACCESS-KEY: $apiKey";
        $headers[] = "OK-ACCESS-SIGN: $sign";
        $headers[] = "OK-ACCESS-TIMESTAMP: $timestamp";
        $headers[] = "OK-ACCESS-PASSPHRASE: $passphrase";
        $headers[] = "OK-TEXT-TO-SIGN: $textToSign";

        return $headers;
    }

    // 获取IOS格式时间戳
    public static function getTimestamp()
    {
        ini_set("date.timezone","UTC");
        //设置时区为
        date_default_timezone_set("Etc/GMT");
        $res =  date("Y-m-d\TH:i:s"). substr((string)microtime(), 1, 4) . 'Z';
        date_default_timezone_set("Asia/Shanghai");
        return $res ;
    }

    // IOS格式时间戳转毫秒级时间
    function dateToTimestamp($isoTime)
    {
        @list($usec, $sec) = explode(".", $isoTime);
        $date = strtotime($usec);
        $return_data = str_pad($date.$sec,13,"0",STR_PAD_RIGHT); //不足13位。右边补0
        return substr($return_data, 0, -1);

    }

    public static function getServerTimestamp(){
        try{
            $response = file_get_contents(self::FUTURE_API_URL.self::SERVER_TIMESTAMP_URL);
            $response = json_decode($response,true);

            return $response['iso'];
        }catch (Exception $e){
            return '';
        }
    }

    public static function signature($timestamp, $method, $requestPath, $body, $secretKey)
    {
        $message = (string) $timestamp . strtoupper($method) . $requestPath . (string) $body;

        self::$textToSign = $message;

        return base64_encode(hash_hmac('sha256', $message, $secretKey, true));
    }

    public static function wsSignature($timestamp, $method, $requestPath, $body, $secretKey)
    {
        $message = (string) $timestamp . strtoupper($method) . $requestPath . (string) $body;

        $ntime = self::getTimestamp();
        print_r($ntime." TEXT-TO-SIGN:$message\n");



        return base64_encode(hash_hmac('sha256', $message, $secretKey, true));
    }

    /*
     * microsecond 微秒     millisecond 毫秒
     *返回时间戳的毫秒数部分
     */
    public static function get_millisecond()
    {
        $time = microtime(true);
        $msec=round($time*1000);

        return $msec/1000;
    }

    // 设置密钥相关参数
    public static function setParams($configs){
        self::$apiKey=$configs["apiKey"];
        self::$apiSecret=$configs["apiSecret"];
        self::$passphrase=$configs["passphrase"];
    }
}
