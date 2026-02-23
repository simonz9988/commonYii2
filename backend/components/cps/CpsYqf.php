<?php
namespace backend\components\cps;
/**
 * 多麦CPS
 * */
class CpsYqf extends CpsBase
{
    private $union_id = "yqf";
    private $mid  ="";
    private  $interId = '566549467c5cd0b77aa20844';
    private  $src     = 'emar';
    private  $channel = 'cps';
    private  $cid     = '18436';
    private  $wi      = ''; //该值为官方随机给出非固定值
    private $send_url  = "http://o.yiqifa.com/servlet/handleCpsInterIn2222";
    private $cookie_expire_day = 30;
    private $rate = 0.06;

    private $error_detail = array(
        '0'  =>'表示发送成功',
        '1'  =>'表示缺少必要的参数',
        '2'  =>'表示参数格式错误',
        '3'  =>'表示链接超时',
        '4'  =>'表示URL格式错误',
        '5'  =>'表示IO异常',
        '-1' =>'表示发送失败',
    );

    //限定查询IP  等到正式对接的时候再进行填写
    private $limit_ip = array(
        "127.0.0.1",
        "10.88.98.221",
        '221.122.127.193',
        '119.40.53.2',
        '119.253.32.53',
    );

    private  $query_error_detail = array(
        '0'  =>array('return_str'=>'ip is limited!','return_detail'=>'IP验证不通过'),
        '1'  =>array('return_str'=>'sign is error!','return_detail'=>'签名验证不通过'),
        '2'  =>array('return_str'=>'no data!','return_detail'=>'查询不到数据'),
        '3'  =>array('return_str'=>'paramter is not the numeric!','return_detail'=>'参数不是整数'),
        '4'  =>array('return_str'=>'request time out !','return_detail'=>'请求超时'),
    );

    /**
     * 根据错误代码获取指定的错误反馈提示
     * @param  [int] $error_code  指定的错误代码 由我们提供
     * @return [string]           返回展现给亿起发的code
     */
    private function get_query_error($error_code){
        $query_error_detail = $this->query_error_detail ;
        if(isset($query_error_detail[$error_code])){
            return $query_error_detail[$error_code]['return_str'] ;
        }else{
            return 'unknown error!';
        }
    }

    /**
     * 添加订单记录
     * @param $orderData
     */
    public function addCpsOrder($orderData)
    {

    }

    public function send($orderData, $goodsData){

    }

    /**
     * 异步发送订单数据
     * @param $cpsOrder
     * @param $orderData
     * @param $goodsData
     */
    public function asynsend($cpsOrder,$orderData,$goodsData)
    {

    }


}