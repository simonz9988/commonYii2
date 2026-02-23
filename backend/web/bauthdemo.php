<?php
$article_cate  = [
    'FIL'=>['key'=>'FIL','name'=>'FilCoin'],
    'USDT'=>['key'=>'USDT','name'=>'USDT'],
] ;

//echo json_encode($article_cate);exit;

$micro_time = getMicrotime();
$data = array(
	'appKey' => 'jpay_7777777',
	'timestamp' => $micro_time,
	'orderNo' => 'UPWPM5f9679c93c37f',
	'payName' => 'Test+Order',
	'amount' => '999.99',
	'callbackUrl' => 'http://www.test.com/callback/notice',
	'coinType' => 'CNY',
	'payType' => 'CARD',

	//'ucUid' => '20190307140035_de29122a3f80c2814cb52757b47bb4e7',
	//'order_id' => '18735126',
	//'goods_num' => '2',
	//'ucUid' => '184651',
	//'ucUid' => '523046',
	//'goodsId' => '3668',
	//'order_no' => '120082529250',
	//'payment_id' => 32,
	//'contact_user' => 'Dasdasdas',
	//'goods_id' => '3466',
	//'after_market_type' => '2',
	//'refund_price' => '0.02',
	//'request_content' => 'tste',
	//'goods_num' => '1',
	//'img_url' => '',
	//'after_market_type' => '2',
	//'payment_id' => '9',
	//'mobile' => '13988887777',
    //'activity_id' => '4',
    //'goods_id' => '4',
	//'goods_id' => '3342',
    //'goods_data' => json_encode([['goods_id'=>1345,'goods_num'=>1]]),
    //'order_type' => 'normal',
    //'address_id' => '27780',
    //'goods_data' =>'[{"goods_id":"3381","goods_num":"1"},{"goods_id":"3381","goods_num":"1"}]'
    //'code' => '40LOSHWV',
    //'order_type' => 'normal',
    //'pageIndex' => '1',
    //'pageSize' => '20',
    //'goods_data' =>json_encode([['goods_id'=>1396, 'goods_num'=>1]])
    // 'id'=>1527,
    //'status'=>'EXPIRED',
    //'pageSize'=>'6',
    //'trade_in_activity_id'=>3,
);

/*
// 保存用户信息
$userDetailInfoStr = json_encode(
	array(
		'name' => '测试4',
		'gender' => NULL,
		'birthday' => '1998-08-23',
	//	'provinceId' => '',
	//	'cityId' => '',
	//	'areaId' => '',
		//'familyArea' => '90-120',
	//	'houseType' => '花园洋房式',
		//'familyMembers' => '',
		//'familyMembers' => '老人,小孩,配偶,宠物,保姆',
		//'hobbies' => '科技,时尚',
		'otherHobbies' => '玩什么呢',
	)
);
$data['userDetailInfoStr'] = $userDetailInfoStr;
*/
/*
// 保存用户收货地址信息
$GlobalUserReceiveInfoRequest = json_encode(
	array(
		//'receiveId' => '54861',
		'receiverName' => '测试人',
		'provinceId' => '370000',
		'cityId' => '371400',
		'areaId' => '371428',
		'address' => '友翔路18号',
		'receiverMobile' => '13402576084',
		'isDefault' => '1',
	)
);
$data['globalUserReceiveInfoRequest'] = $GlobalUserReceiveInfoRequest;
*/
/*
// 获取用户收货地址信息
$data['pageIndex'] = 1;
$data['pageSize'] = 5;
*/

/*
// 删除用户收货地址信息
$data['receiveId'] = 55095;
*/

/*
// 给Global 调用的用于同步Global注册用户信息
$data['registerChannel'] = 'globalapp';
$data['system'] = null;
*/

$data = paraFilter($data);
$sign_string = 'jpay_7777777'.arrayToString($data).'jpay888999' ;
$data['sign_string'] = $sign_string;

$sign = md5($sign_string);

$data['sign'] = $sign;
echo "<pre>";
var_dump($data);

	
	/**
     * 获取一个毫秒级别的时间戳
     * @return int
     */
    function getMicrotime() {
        $time = floor(microtime(true) * 1000);

        return $time;
    }
    /**
     * 数字转字符串
     * @param array $params 需要拼接的数组
     * @return string
     */
    function arrayToString($params)
    {
        $arg = "";
        foreach($params as $key => $val){
            $arg.=$key."=".$val;
        }
        
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){
            $arg = stripslashes($arg);
        }
        
        return $arg;
    }
    
    /**
     * 除去数组中的空值和签名参数
     * @param  array $params 签名参数组
     * @return array
     */
    function paraFilter($params)
    {
        // 正序排序
        ksort($params);
        
        $para_filter = array();
        foreach($params as $key => $val)
        {
            if($key == "sign"){
                continue;
            }else{
                $para_filter[$key] = $val;
            }
        }
        return $para_filter;
    }