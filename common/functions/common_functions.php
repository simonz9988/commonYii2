<?php

function debug($arr, $exit = false){
    header("Content-type:text/html;charset=utf-8");
    echo  '<pre>';
    print_r($arr) ;
    if($exit){
        exit();
    }
}

function getClientIP(){
    $ip_address = '0.0.0.0';
    if(!empty($_SERVER['HTTP_CDN_SRC_IP'])){
        $ip_address = $_SERVER['HTTP_CDN_SRC_IP'];
    }
    elseif (isset($_SERVER['REMOTE_ADDR']) AND isset($_SERVER['HTTP_CLIENT_IP']))
    {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (isset($_SERVER['REMOTE_ADDR']))
    {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }
    elseif (isset($_SERVER['HTTP_CLIENT_IP']))
    {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if ($ip_address === 'Unknown')
    {
        $ip_address = '0.0.0.0';
        return $ip_address;
    }
    if (strpos($ip_address, ',') !== 'Unknown')
    {
        $x = explode(',', $ip_address);
        $ip_address = trim(end($x));
    }
    return $ip_address;
}

/**
 * 获取设备类型
 * @return  string  pc/mobile
 */
function getClienType(){
    $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $mobileList = array('Android','iPhone','phone');
    foreach($mobileList as $val)
    {
        if(stripos($agent,$val) !== false)
        {
            return 'mobile' ;
        }
    }
    return 'pc';

}

/**
 * @brief 获取客户手机类型
 * @return string pc,mobile
 */
function getMobileDetail()
{
    $device = getClienType();
    if($device =='mobile'){

        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        if(strpos($userAgent,"iPhone") || strpos($userAgent,"iPad") || strpos($userAgent,"iPod")){
            $agent ='IOS';
        }else if(strpos($userAgent,"Android")){
            $agent ='Android';
        }else{
            $agent ='otherMobile';
        }

    }else{
        $agent = '';
    }

    return $agent ;
}

//取用户的IP地址的长整型
function getLongIp()
{
    if (($ip = getClientIP()) != '0.0.0.0') {
        return sprintf("%u",ip2long($ip));
    } else {
        return 0;
    }
}

/**
 * 获取当前的控制器名和方法名组成的url
 * @return string eg:language/index
 */
function getCurrURI(){
    $parameter = '';
    $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$parameter;
    $params = '';
    $parse = parse_url($url);

    $url = $parse['path'] ;


    return $url ;
}

/**
 * 多维数组按照某项键值排序
 */
function array_sort($arr, $keys, $type = 'asc') {

    $keysvalue = $new_array = array();

    foreach ($arr as $k => $v) {

        $keysvalue[$k] = $v[$keys];
    }

    if ($type == 'asc') {

        asort($keysvalue);
    } else {

        arsort($keysvalue);
    }

    reset($keysvalue);

    foreach ($keysvalue as $k => $v) {

        $new_array[] = $arr[$k];
    }

    return $new_array;
}


/**
 * 将数组转化为key value形式字符串
 * @param $arr
 * @return string
 */
function arrayToKvString($arr){
    $str = '';
    if($arr){
        foreach($arr as $k=>$v){
            if(is_array($v)){
                $str.= $this->arrayToKvString($v);
            } else {
                $str.= $k.':'.$v.' ';
            }
        }
    }
    return $str;
}

/**
 * 获取错误码描述
 * @param $code
 * @return string
 */
function getErrorDictMsg($code){
    return Yii::$app->EcoErrCode->get($code);
}

/**
 * 获取数组指定键值
 */
function getArrayKey($data, $field)
{
    $key_arr = array();
    if(!empty($data)){
        foreach($data as $row){
            $key_arr[] = $row[$field];
        }
    }
    return $key_arr;
}


/**
 * 发送请求
 * @FIXED 将 post 数据的 form-data 和 raw 类型进行了区分，满足不同需求，同时增加 header 请求的参数
 * @param string $url 请求地址
 * @param mixed $post 待发送的数据
 * @param bool $ignore_error 是否忽略错误
 * @param mixed $header 待发送的请求头
 * @return mixed
 */
function curlGo($url, $post = null, $ignore_error = false, $header = null) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

    // post 方式提交
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1); // 启用POST提交

        // 判断数据类型，如果是数组则通过 form-data 的形式发送数据，否则是 raw
        if (is_array($post)) {
            $post = http_build_query($post);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }

    // header 请求头
    if ($header) {
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $header);
    }

    // 是否需要屏蔽错误
    if ($ignore_error) {
        $rst = @curl_exec($ch);
    } else {
        $rst = curl_exec($ch);
    }

    curl_close($ch);

    return $rst;
}

/**
 * @brief 对字符串进行URL编码，遵循rfc1738 urlencode
 * @param $params
 * @return URL编码后的字符串
 */
function getUrlencodedString($params)
{
    $normalized = array();
    foreach ($params as $key => $val) {
        $normalized[] = $key . "=" . rawurlencode($val);
    }

    return implode("&", $normalized);
}

/**
 * 递归创建文件
 * @param  string $path 文件名
 * @return boolean
 */
function createFolder($path)
{
    if (!file_exists($path))
    {
        createFolder(dirname($path));
        mkdir($path, 0777);
    }
    return true;
}

/**
 * 生成一个字母和数字组成的随机数据
 * @input  $length int 最终结果的长度
 * @return string
 */
function createRndNum($length=10){
    //随机因子
    $rnd_num_charset = 'abcdefghkmnprstuvwxyz23456789';
    $_len = strlen($rnd_num_charset)-1;
    $rst ='';
    for ($i=0;$i<$length;$i++)
    {
        $rst .= $rnd_num_charset[mt_rand(0,$_len)];
    }
    return $rst;
}

/**
 * 格式化 JSON
 *
 * @param string $json The original JSON string to process
 *        When the input is not a string it is assumed the input is RAW
 *        and should be converted to JSON first of all.
 * @return string Indented version of the original JSON string
 */
function jsonFormat($json)
{
    $result = '';
    $level = 0;
    $in_quotes = false;
    $in_escape = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if ( $in_escape ) {
            $in_escape = false;
        } else if( $char === '"' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                $level--;
                $ends_line_level = NULL;
                $new_line_level = $level;
                break;
                case '{': case '[':
                $level++;
                case ',':
                    $ends_line_level = $level;
                    break;
                case ':':
                    $post = " ";
                    break;
                case " ": case "\t": case "\n": case "\r":
                $char = "";
                $ends_line_level = $new_line_level;
                $new_line_level = NULL;
                break;
            }
        } else if ( $char === '\\' ) {
            $in_escape = true;
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "\t", $new_line_level );
        }
        $result .= $char.$post;
    }

    return $result;
}

/**
 * 解码 Unicode
 * @input  string $str
 * @return string
 */
function decodeUnicode($str)
{
    return preg_replace_callback('/\\\\u([0-9a-f]{4})/i',
        create_function(
            '$matches',
            'return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");'
        ),
        $str);
}

/**
 * 商品价格格式化
 * @param $price float 商品价
 * @return float 格式化后的价格
 */
function priceFormat($price)
{
    return round($price,2);
}

/**
 * 过滤提交数据的html 标签
 * @param array $post_data 提交数据
 * @return array
 */
function filterHtmlTag($post_data)
{
    if (!$post_data) {
        return [];
    }

    //过滤html标签
    foreach ($post_data as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $ke => $va) {
                $post_data[$k][$ke] = strip_tags($va);
            }
        } else {
            $post_data[$k] = strip_tags($v);
        }

        return $post_data;
    }
}

//获取IP
function getIp()
{
    $realip = NULL;
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $ipArray = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach($ipArray as $rs)
        {
            $rs = trim($rs);
            if($rs != 'unknown')
            {
                $realip = $rs;
                break;
            }
        }
    }
    else if(isset($_SERVER['HTTP_CLIENT_IP']))
    {
        $realip = $_SERVER['HTTP_CLIENT_IP'];
    }
    else
    {
        $realip = $_SERVER['REMOTE_ADDR'];
    }

    preg_match("/[\d\.]{7,15}/", $realip, $match);
    $realip = !empty($match[0]) ? $match[0] : '0.0.0.0';
    return $realip;
}

/**
 * @brief 去除数字后面的0 eg:1480.00 转换为1480 而1480.01则不改变
 * @param
 * @return int 订单优惠后总价格
 */

function formatNumZero($num){
    $tmp_num = (int)$num ;
    if($tmp_num !=$num){
        $num10 = $num*10 ;
        if((int)$num10 == $num10) {
            //小数点第二位为0
            return floatval(number_format($num,1,'.', ''));
        }else{
            $num10 = $num*10 ;
            if((int)$num10 == $num10) {
                //小数点第二位为0
                return floatval(number_format($num,1));
            }else{
                return floatval($num) ;
            }
        }
    }else{
        return $tmp_num;
    }

}
/**
 * 解析商品规格
 * @param $specJson
 * @return array
 */
function show_spec($specJson)
{
    $specArray = json_decode($specJson);
    $spec      = array();

    if($specArray){

        foreach($specArray as $val)
        {
            if($val['type'] == 1)
            {
                $spec[$val['name']] = $val['value'];
            }
            else
            {
                $spec[$val['name']] = '<img src="'.$val['value'].'" class="img_border" style="width:15px;height:15px;" />';
            }
        }

    }
    return $spec;
}

/**
 * @param 计算两个时间段是否有交集（边界重叠不算）
 * @param string $beginTime1 开始时间1
 * @param string $endTime1 结束时间1
 * @param string $beginTime2 开始时间2
 * @param string $endTime2 结束时间2
 * @return boolean
 */
function timeIsCross($beginTime1 = '', $endTime1 = '', $beginTime2 = '', $endTime2 = '')
{
    $beginTime1=strtotime($beginTime1);
    $endTime1=strtotime($endTime1);
    $beginTime2=strtotime($beginTime2);
    $endTime2=strtotime($endTime2);
    $status = $beginTime2 - $beginTime1;
    if ($status > 0)
    {
        $status2 = $beginTime2 - $endTime1;
        if ($status2 >= 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }else{
        $status2 = $endTime2 - $beginTime1;
        if ($status2 > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

/**
 * 创建文件名称
 * @param $url
 */
function createFilePathAndName($url,$http_host){

    $host_str = base64_encode($http_host);
    $path_str = 'newpath';
    //php上传的相对路径
    $type = 'ecoimages';
    $file_dir = "upload/".$path_str.'/'.$type."/".date("Y")."/".date("m")."/" ;
    $ext = getExtByUrl($url);
    $file_name = time().mt_rand(10000,99999).'.'.$ext;
    return $file_dir.$file_name;

}

/**
 * 根据远程链接获取文件后缀名称
 * @param $url
 */
function getExtByUrl($url){
    $arr = explode('.',$url);
    if($arr){
        $length = count($arr);
        $length = $length -1 ;
        $ext = $arr[$length];
        return $ext ;
    }else{
        return '';
    }

}

function qiniu_base64_urlSafeEncode($data)
{
    $find = array('+', '/');
    $replace = array('-', '_');
    return str_replace($find, $replace, base64_encode($data));
}

/**
 * accessDevices()
 * 获取访问设备类型
 * 与 isMobile 命名冲突，但该方法在老框架中得到使用
 * 且其判断条件较全，故在个人中心处使用该方法
 *
 * @return string
 */
function accessDevices() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset ($_SERVER['HTTP_X_WAP_PROFILE']))
    {
        return 'wap';
    }
    // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset ($_SERVER['HTTP_VIA']))
    {
        // 找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? 'wap' : 'pc';
    }
    // 脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset ($_SERVER['HTTP_USER_AGENT']))
    {
        $clientkeywords = array ('nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile'
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT'])))
        {
            return 'wap';
        }

        //return $this->source();
    }
    // 协议法，因为有可能不准确，放到最后判断
    if (isset ($_SERVER['HTTP_ACCEPT']))
    {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
        {
            return 'wap';
        }
    }
    return 'pc';
}

/**
 * 百度mip内容标准替换方法
 *
 * @author yunbinbai@foxmail.com for http://www.soyiyuan.com/city/
 * @createtime 2017-1-11
 * @modifytime
 * @param string $content 待转换的内容正文
 * @return string
 */
function mip_replace($content = ''){
    header("Content-type:text/html;charset=utf-8");
    $pattern1 = "#<img.*?src=['\"](.*?)['\"].*?>#ims";
    //debug($content);
    preg_match_all($pattern1,$content,$img);
    $imgcontent = $img[0];
    $imgurl = $img[1];
    //debug($content,1);
    foreach($imgcontent as $imgk=>$imgv)
    {
        $url = $imgurl[$imgk];
        $mipimg = '<mip-img src="'.$url.'"></mip-img>';
        //debug($imgv);exit;
        debug($mipimg);
        debug($imgv);
        $content = str_replace($imgv,$mipimg,$content);
        debug($content,1);
    }


    //$content =preg_replace("/<a /si","<a target=\"_blank\" ",$content);
    $content =preg_replace("/style=\".*?\"/si","",$content);
    debug($content,1);
    return mip_utf8($content);

}

function mip_utf8($string = '') {

    $fileType = mb_detect_encoding($string , array('UTF-8','GBK','LATIN1','BIG5'));
    if( $fileType != 'UTF-8'){
        $string = mb_convert_encoding($string ,'utf-8' , $fileType);
    }
    return $string;
}

/**
 * 对象转数组
 * @param $obj
 * @return array|void
 */
function object_to_array($obj) {
    $obj = (array)$obj;
    foreach ($obj as $k => $v) {
        if (gettype($v) == 'resource') {
            return;
        }
        if (gettype($v) == 'object' || gettype($v) == 'array') {
            $obj[$k] = (array)object_to_array($v);
        }
    }

    return $obj;
}

function curl_get_https($url){
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
    $tmpInfo = curl_exec($curl);     //返回api的json对象
    //关闭URL请求
    curl_close($curl);
    return $tmpInfo;    //返回json对象
}

/* PHP CURL HTTPS POST */
function curl_post_https($url,$data,$aHeader=[]){ // 模拟提交数据函数
    $curl = curl_init(); // 启动一个CURL会话
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
    //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    if($data){
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    }
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    if($aHeader){
        curl_setopt($curl, CURLOPT_HTTPHEADER, $aHeader);
    }
    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
        //echo 'Errno'.curl_error($curl);//捕抓异常
        Yii::$app->CommonLogger->logError("请求返回失败：".json_encode(curl_error($curl),JSON_UNESCAPED_UNICODE));
    }
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据，json格式
}


//发送钉钉消息
function send_dingding_sms($message,$type='okex'){

    if($type=='gate'|| $type=='gate_notice'){
        $webhook = "https://oapi.dingtalk.com/robot/send?access_token=96da84eaa563a32bf9b28d88baa85369b9df2c67192c45a674d65e4af60dedd7";
    }else{
        if($type=='okex'){
            $webhook = "https://oapi.dingtalk.com/robot/send?access_token=3542e7c7beac7edde2d1fa0bcb7b21f24d83864ddfcc92df06d085fbb63b4944";
        }

        if($type =='huobi'){
            $webhook = "https://oapi.dingtalk.com/robot/send?access_token=ab99e9b0a3fd7fda266b7a52bc5d81eda0b4582d4e92fcaed797e4d93a903b1a";
        }

    }
    $data = array ('msgtype' => 'text','text' => array ('content' => $message));
    $data_string = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);

    echo $data;
}

//发送钉钉消息
function send_dingding_sms_by_webhook($message,$webhook){

    $message = '业务报警:'.$message;
    $data = array ('msgtype' => 'text','text' => array ('content' => $message));
    $data_string = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $webhook);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);

    echo $data;
}

function do_send_dingding_by_time($message,$num=30){
    set_time_limit(0);
    for($i=1;$i<$num;$i++){
        send_dingding_sms($message);
        sleep(3);
    }

}

function numToStr($num,$double=12){
    $num = strtolower($num);
    if(false !== stripos($num, "e")){
        $a = explode("e",strtolower($num));
        return bcmul($a[0], bcpow(10, $a[1], $double), $double);
    }

    return $num ;
}


function object_array($array)
{
    if (is_object($array)) {
        $array = (array)$array;
    }
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $array[$key] = object_array($value);
        }
    }
    return $array;

}

function int_to_hex($num){
    return '';
    return '0x'.bin2hex($num);
}

/**
 * 创建自增原子redis
 * @param $key
 * @param $value
 * @param $expire
 * @return mixed
 */
function createIncrementRedis($key,$value=1,$expire=0){
    return MyRedis::getInstance()->incrBy($key, $value, $expire);
}

/**
 * 返回允许的最大值
 * @param $num
 * @return mixed
 */
function checkMaxEarn($num,$user_id,$date,$balance){


    // 获取用户已经获得的收益
    $redis_key = 'DayShouyi:'.$user_id.':'.$date;
    $redis_model = new \common\components\MyRedis() ;
    $today_get_total = $redis_model->get($redis_key);

    $max_earn_num = DAY_MAX_ERAN_NUM > $balance/2 ?  $balance/2 : DAY_MAX_ERAN_NUM ;

    if($today_get_total >= DAY_MAX_ERAN_NUM){

        // 已经超出当日最大的收益
        return 0 ;

    }else{

        $max_earn_num = DAY_MAX_ERAN_NUM > $balance/2 ?  $balance/2 : DAY_MAX_ERAN_NUM ;
        // 剩余可以的金额
        $left = $max_earn_num - $today_get_total  ;

        // 判断收益值和剩余值
        return $left > $num ? $num : $left ;
    }
}

/**
 * 后台域名展示
 * @param $str
 * @return string
 */
function url($str){
    return $str ;
}


/**
 * 返回get请求参数
 * @param string $key
 * @param string $type
 * @return array|mixed
 */
function getParam($key = '',$type = ''){
    $request = Yii::$app->request;
    if($key){
        $value = $request->get($key);
        $value = $type == 'int' ? intval($value) : $value;
    } else{
        $value = $request->get();
    }

    if(!$value){
        $value = postParam($key,$type) ;
    }
    return $value;
}

/**
 * 返回post请求参数
 * @param string $key
 * @param string $type
 * @return array|mixed
 */
function postParam($key = '',$type = ''){
    $request = Yii::$app->request;
    if($key){
        $value = $request->post($key);
        $value = $type == 'int' ? intval($value) : $value;
    } else{
        $value = $request->post();
    }
    return $value;
}

/**
 * 文件上传限制属性
 */
function uploadFileProp($type){

    $all_arr = array(
        'images' => array(
            'type_name'=>array('image/jpeg','image/png','image/gif'),//PHP所识别的文件
            'allow'=> array('jpg,gif,png,xml,cgm,jpeg,svg,svga'),
            'file_limit'=>'',
        ),
        'zip'   => array(
            'type_name'=>array('application/x-zip-compressed','application/octet-stream'),
            'allow'=> array('zip'),
            'file_limit'=>''
        ),
        'rar'   =>  array(
            'type_name'=>array('application/x-zip-compressed'),
            'allow'=> array('rar'),
            'file_limit'=>''
        ),
        'pdf'   => array(
            'type_name'=>array('application/pdf'),
            'allow'=> array('pdf'),
            'file_limit'=>''
        )

    );
    switch ($type) {
        case 'all':
            unset($all_arr['zip']);
            unset($all_arr['rar']);
            $r =  $all_arr ;
            break;
        case 'images':
            $r = $all_arr['images'] ;
            break ;
        case 'zip':
            $r =  $all_arr['zip'];
            break ;
        case 'rar':
            $r =  $all_arr['rar'];
            break ;
        case 'pdf':
            $r =  $all_arr['pdf'];
            break ;
        case 'goods_model':

            $r['images'] = $all_arr['images'];

            break ;
        case 'instructions_upload':
            $r['pdf'] = $all_arr['pdf'];
            break ;
        default:
            $r['images'] = $all_arr['images'];

            break;
    }

    //最终返回结果
    return $r ;

}


/**
 * 根据不同的文件类型，设置不同的上传文件大小
 * @param  string $field 返回数组中指定key值的信息
 * @param  string $type  上传的文件类型 对应到相关的上传目录
 * Note:可以设置最大文件上传大小 也可以设置最小文件上传大小
 *      目前只设置了最大的文件上传大小
 */
function uploadFileSizeProp($field='maxsize',$type='default'){

    $return_arr = array(
        'maxsize'=>array(
            'js'=>'100m',
            'php'=>100*1024*1024,//php是以字节数为计算标准
        ),
    ) ;

    switch ($type) {
        case 'goods_model':
            $return_arr['maxsize']['php'] = 5*1024*1024 ;
            break;
        case 'ad':
            //广告新增
            $return_arr['maxsize']['php'] = 5*1024*1024 ;
            break ;
        case 'selling_point':
            //广告新增
            $return_arr['maxsize']['php'] = 5*1024*1024 ;
            break ;
        case 'default':
            $return_arr['maxsize']['php'] = 5*1024*1024 ;
            break;
        case 'instructions_upload':
            $return_arr['maxsize']['php'] = 50*1024*1024 ;
            break;
        default :
            $return_arr['maxsize']['php'] = 5*1024*1024 ;
            break ;

    }

    return isset($return_arr[$field])?$return_arr[$field]:array() ;
}

/**
 * 返回值的格式，jsonp的,还是纯json的，是否加上script
 * @param  array  $arr_ret
 * @param  boolean  $is_log
 * @return
 * Note:returninfo
 *     code   1-成功 0-失败
 *     msg    简单描述 succ/empty/err  etc
 *     detail 具体的内容
 */
function responseJson($arr_ret = array(), $is_log = false)
{
    if(!isset($arr_ret['data'])){
        $arr_ret['data'] = array();
    }
    $sCallback = json_encode($arr_ret);
    echo $sCallback;

    die();
}

/**
 * 预览图片样式
 */
function getPreviewStyle($tag ='default'){
    $str = '';

    if($tag =='default'){
        $str = '  height="150" style="max-width:370px;"  ';
    }

    return $str  ;
}

/**
 *
 * @param $xml
 * @param array $options
 * @return array
 */
function xmlToArray($source, $options = array()) {
    if(is_file($source)){ //传的是文件，还是xml的string的判断
        $xml_array=simplexml_load_file($source);

    }else{

        $xml_array=simplexml_load_string($source);
    }

    return $xml_array ;
}

/**
 * 隐藏手机号中间4位
 * @param string $mobile 手机号
 * @param string $start
 * @param string $length
 * @return string
 */
function formatHiddenString($mobile,$start=3,$length=4)
{
    return $mobile?substr_replace($mobile, '****', $start, $length):'';
}

/**
 * 判断用户是否有该权限
 * @param $uri
 * @return mixed
 */
function checkAdminPrivilege($uri){
    $adminUserInfo = Yii::$app->session->get('login_user');
    if(!$adminUserInfo){
        return false ;
    }
    $admin_username = $adminUserInfo['username'];
    if($admin_username == 'admin'){
        return true ;
    }

    $uri = trim($uri,'/');
    $url_arr = explode('/',$uri);
    $controller_name = $url_arr[0] ;
    $function_name = $url_arr[1] ;

    $admin_role_model = new \backend\models\AdminRole() ;
    $check_privilege_rst = $admin_role_model->checkRolePrivilege($adminUserInfo,$controller_name,$function_name);
    if(!$check_privilege_rst){
        return false ;
    }

    return true ;
}

/**
 * 获取请求的源头服务器地址
 * @return string protocal://hostname:port
 */
function getRequestOrigin() {
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

    return $origin;
}

/**
 * 判断请求的域名是否是安全的
 * 只允许本服务的一级域名相同的请求授权通过
 * @param string $origin 请求的 源头服务器地址
 * @param string $top_domain 本服务的一级域名
 * @return bool
 */
function isSafeRequest($origin, $top_domain) {
    if (!$origin || !$top_domain) {
        return false;
    }

    $last_port = strrchr($origin, ":");

    if (isset($last_port[1]) && is_numeric($last_port[1])) {
        // 如果第一个字符是数字，则认为是端口部分
        // 去除端口部分的字符串
        $origin = substr($origin, 0, strpos($origin, $last_port));
    }

    if ($origin && substr_compare($origin, $top_domain, (mb_strlen($top_domain) * -1)) === 0) {
        return true;
    }

    return false;
}

// 格式化数字精度
function numberSprintf ($total,$jin_du=6){

    $total = sprintf("%.".$jin_du."f",substr(sprintf("%.".($jin_du+1)."f", $total), 0, -1));
    //$total = sprintf('%.'.$jin_du.'f', (float)$total);
    return $total ;
}

/**
 * 隐藏手机号中间4位
 * @param string $mobile 手机号
 * @return string
 */
function formatPhone($mobile,$start=3,$length=4)
{
    return $mobile?substr_replace($mobile, '****', $start, $length):'';
}

/**
 *   将数组转换为xml
 *    @param array $data    要转换的数组
 *   @param bool $root     是否要根节点
 *   @return string         xml字符串
 *    @author Dragondean
 *    @url    http://www.cnblogs.com/dragondean
 */
function arr2xml($data, $root = true){
    $str="";
    if($root)$str .= "<xml>";
    foreach($data as $key => $val){
        if(is_array($val)){
            $child = arr2xml($val, false);
            $str .= "<$key>$child</$key>";
        }else{
            $str.= "<$key><![CDATA[$val]]></$key>";
        }
    }
    if($root)$str .= "</xml>";
    return $str;
}

/**
 * 根据创建时间返回创建日期
 * @param $create_time
 * @return mixed
 */
function returnCreateDate($create_time){
    $create_time = strtotime($create_time);
    return date("Y-m-d",$create_time);
}

/**
 * 根据数据列表，格式化返回创建日期
 * @param $list
 * @return mixed
 */
function returnCreateDateByDataList($list){
    if($list){
        foreach($list as $k=>$v){
            if(isset($v['create_time'])){
                $list[$k]['create_time'] = returnCreateDate($v['create_time']);
            }
        }
    }
    return $list ;
}

/**
 * 检测地址是否正确
 * @param $address
 * @return mixed
 */
function checkEthAddress($address){

    $address = strtoupper($address);
    if(substr($address,0,2) != '0X' || strlen($address) !=42){
        return false ;
    }
    return true ;
}

/**
 * 科学计数法转换
 * @param $num
 * @param int $double
 * @return int
 */
function scToNum($num, $double = 6)
{
    $num = strtoupper($num);
    if (false !== stripos((string)$num, "E")) {
        $a = explode("e", strtolower((string)$num));
        $b=bcmul($a[0], bcpow((string)10, (string)$a[1], $double), $double);
        $b=$b?$b:0;
    }else{
        $b=0;
    }
    return $b;
}

/**
 * 获取交易识别符
 * @param $coin
 * @param $legal_coin
 * @return mixed
 */
function returnInstrumentId($coin,$legal_coin){
    $str = $coin.'-'.$legal_coin;
    return strtoupper($str);
}

/**
 * 返回交易币种的交易对
 * @param $coin
 * @param $legal_coin
 * @return mixed
 */
function getTradeSymbol($coin,$legal_coin){

    $str = $coin.'/'.$legal_coin;
    return strtoupper($str) ;
}

function checkPrivilege($controller,$function){

    $adminUserInfo = Yii::$app->session->get('login_user');
    if($adminUserInfo['username']=='admin'){
        return true ;
    }

    $role_id = $adminUserInfo['role_id'];
    $privilege_model = new \backend\models\AdminPrivilege();
    $privilege_info = $privilege_model->checkExists($controller,$function);
    if(!$privilege_info){
        return false ;
    }

    $params['cond'] = 'role_id =:role_id AND privilege_id=:privilege_id ';
    $params['args'] = [':role_id'=>$role_id,':privilege_id'=>$privilege_info['id']];
    $info = $privilege_model->findOneByWhere('sea_admin_role_privilege',$params,$privilege_model::getDb()) ;
    if(!$info){
        return false ;
    }

    return true;
}

function _getFloatLength($num) {
    $count = 0;

    $temp = explode ( '.', $num );

    if (sizeof ( $temp ) > 1) {
        $decimal = end ( $temp );
        $count = strlen ( $decimal );
    }

    return $count;
}