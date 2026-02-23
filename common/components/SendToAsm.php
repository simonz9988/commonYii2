<?php
/**
 *发送售后内容到售后中心组件
 * author simon.zhang
 */
namespace common\components;

use common\models\AsmTask;
use common\models\AsmTaskLog;
use common\models\AfterMarketDoc;
use common\models\LogAftermarket;
use common\models\Order;
use common\models\OrderGoodsSingle;
use common\models\SendOmsAfterMarketLog;
use common\models\Member;
use common\models\SiteConfig;
use common\models\Areas;
use common\models\OrderGoods;
use common\models\PreSell;
use Yii ;
use yii\base\Exception;
use yii\db\Expression;

use common\models\TradeInAsmTask;
use common\models\TradeInAsmTaskLog;
use common\models\TradeInAfterMarketDoc;
use common\models\TradeIn;
use common\models\TradeInSendOmsAftermarketLog;


class SendToAsm
{
    //中台对应官网的商铺编码
    private static $shopNo = '0003008500';

    //定义需要的表名
    private static $table_after_market_doc = 'after_market_doc';
    private static $table_oms_log = 'oms_log';
    private static $table_send_oms_after_market_log = 'send_oms_after_market_log';
    private static $table_order='order';
    private static $table_order_goods='order_goods';
    private static $table_order_goods_single='order_goods_single';


    //售后类型对应中的String 信息
    private static $type_arr = array(
        0=>'',
        1=>'REFUND',//退款 对订单中单条order_goods表中的记录进行处理  如下如 只能退数量 1或者2个
        //2=>'RECEIVE',//退货
        2=>'RECEIVE_REFUND',
        3=>'EXCHANGE',//换货
        4=>'REPAIR',//维修
        5=>'REFUND',//退差价 通过具体的属性来进行区分 退款和退差价
        6=>'OLD_FOR_NEW',//以旧换新
    );

    //获取售后类型的中文释义
    private static function getAftermarketTypeStr($type,$after_market_info){
        $rst = '';

        if($type =='REFUND'){
            $rst = '退款';
        }

        if($type =='RECEIVE_REFUND'){
            $rst = '退货退款';
        }

        if($type =='EXCHANGE'){
            $rst = '换货';
        }

        if($type =='REPAIR'){
            $rst = '维修';
        }

        if($type =='REFUND' && $after_market_info['after_market_type']==5){
            $rst = '退差价';
        }

        return $rst ;
    }

    /**
     * 获取售后工单的服务域名
     * @return string
     */
    public static function getAsmUrl(){
        $asm_url = ASM_API_URL;
        $rst = $asm_url?$asm_url:'http://qas-asm1.ecovacs.cn/' ;

        return $rst ;
    }

    /**
     * 获取工单回调地址
     * @return string
     */
    public static function getWorkOrderCallbackUrl(){

        $channel_url = SHOP_ADMIN_URL ;
        $callBackNotifyUrl = $channel_url.'/ec-callback/deal-aftermarket';//工单关闭回调地址
        return $callBackNotifyUrl ;
    }

    /**
     * 获取工单回调地址
     * @return string
     */
    public static function getWorkOrderCallbackUrlFromTradeIn(){

        $channel_url = SHOP_ADMIN_URL ;
        $callBackNotifyUrl = $channel_url.'/ec-callback/deal-trade-in-aftermarket';//工单关闭回调地址
        return $callBackNotifyUrl ;
    }

    /**
     * 获取创建工单地址
     * @return string
     */
    public static function getCreateAsmOrderUrl(){

        $asm_url =  self::getAsmUrl();
        $create_asm_order_url = $asm_url.'api/order/createAsmOrder';//创建工单地址
        return $create_asm_order_url;
    }

    /**
     * 获取创建工单地址
     * @return string
     */
    public static function getCancelAsmOrderUrl(){

        $asm_url =  self::getAsmUrl();
        $cancel_asm_order_url = $asm_url.'api/order/cancelAsmOrder';//取消工单地址
        return $cancel_asm_order_url;
    }

    /**
     * //格式化POST信息
     * @param array $post
     * @param int $post_file
     * @return array|string
     */
    private static  function formatCurlPost($post=array(),$post_file=0){
        if(is_array($post))
        {
            $post_data	='';
            $tmp_array	=array();
            foreach($post as $key=>$a_form)
            {
                $tmp_array[]="{$key}={$a_form}";
            }
            if($tmp_array)
            {
                $post_data=$post_file?$tmp_array:implode('&',$tmp_array);
            }
            return $post_data;
        }
        else
        {
            if(!is_string($post))
            {
                return '';
            }
            return ($post_file)?explode('&',$post):$post;
        }
    }

    /**
     * 向中台发送数据 并且记录日志
     * @param  string $url       对接的中台URL
     * @param  array  $post      传送的post数据 假如post数值为空则是认为进行get方式进行传输数据
     * @param  string $data_type json或者为空
     * @return json 中台发送数据返回的结果
     */
    public static function sendDataByCurl($url='',$post=array(), $data_type=''){

        $curl_handle			=curl_init($url);
        curl_setopt($curl_handle,CURLOPT_HEADER,0);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);


        if($post)
        {
            curl_setopt($curl_handle,CURLOPT_POST,1);
            if($data_type == 'json'){
                $post_string = json_encode($post);
                curl_setopt($curl_handle,CURLOPT_POSTFIELDS,$post_string);
                curl_setopt($curl_handle, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Content-Length: ' . strlen($post_string)));
            } else{
                curl_setopt($curl_handle,CURLOPT_POSTFIELDS,self::formatCurlPost($post));
            }

        }

        $tmp_content		=curl_exec($curl_handle);
        $sult['content']	=$tmp_content;
        curl_close($curl_handle);

        //添加推送响应日志
        $log_data['post_data'] = serialize($post);
        $log_data['push_url'] = $url ;
        $log_data['return_data']= serialize($tmp_content) ;
        $log_data['create_time']= date('Y-m-d H:i:s') ;
        //用来当BaseModel来使用
        $obj = new AsmTask();
        $log_id = $obj->baseInsert('task_asm_task_push_response_log', $log_data, 'db_log');
        return $tmp_content;
    }

    /**
     * 获取符合条件的任务信息
     * @return array
     */
    public static function getTasks(){

        $task_info = array();

        $task_obj = new  AsmTask();
        $task_where['status'] = "SENDING";
        $task_where['service_key'] = "send_aftermarket_to_asm";
        $task_return_field = '*';
        $info = $task_obj->getListInfoByWhere($task_where,$task_return_field);

        if($info){
            foreach($info as $v){

                //获取已经发送过的任务信息
                $log_obj = new AsmTaskLog();
                $log_where['task_id'] = $v['id'];
                $log_return_field = ' count(1) as send_num,max(create_time) as create_time ';
                $count_info = $log_obj->getRowInfoByWhere($log_where,$log_return_field);

                //设置不同的发送请求的频率
                $send_num = isset($count_info['send_num'])?$count_info['send_num']:0 ;
                $last_create_time = isset($count_info['create_time'])?$count_info['create_time']: '0000-00-00 00:00:00' ;
                $ext_time = time()-strtotime($last_create_time) ;
                if($send_num>10 && $send_num <=20){
                    if($ext_time < 1800){
                        continue ;
                    }
                }
                if($send_num >20){
                    //if($ext_time < 3600){
                        continue ;
                    //}
                }

                //设置返回结果
                $params = unserialize($v['params']);
                if($params){

                    $after_market_doc_id = isset($v['after_market_doc_id'])?$v['after_market_doc_id']:0;
                    $postData  = isset($params['postData'])?$params['postData']:array();
                    $userInfo  = isset($params['userInfo'])?$params['userInfo']:array();
                    $task_info[] = array(
                        'after_market_doc_id'=>$after_market_doc_id,
                        'postData'=>$postData,
                        'userInfo'=>$userInfo,
                        'id'=>$v['id'],
                    );

                }
            }
        }

        return $task_info ;
    }

    /**
     * 判断传入参数的有效性
     * @param int $after_market_doc_id
     * @param array $postData
     * @param array $userInfo
     * @return mixed
     */
    public static function checkParams($after_market_doc_id=0,$postData=array(),$userInfo=array()){

        $rst['code'] = 1 ;
        $rst['msg'] = '';
        $rst['data'] = '';


        //step1 判断传参
        if(!$after_market_doc_id){
            $rst['code']='400020';
            return $rst ;
        }

        //step2 判断指定的售后单是否存在
        $obj = new AfterMarketDoc();
        $after_market_doc_where['id'] = $after_market_doc_id ;
        $after_market_doc_return_field = '*';
        $data_detail = $obj->getRowInfoByWhere($after_market_doc_where,$after_market_doc_return_field);

        if(!$data_detail){
            $rst['code']='400021';
            return $rst ;
        }

        //step3 判断售后单是否为未审核状态
        if($data_detail['dispose_status'] >0){
            $rst['code']='400004';
            return $rst ;
        }
        if($data_detail['audit_status'] >0){
            $rst['code']='400003';
            return $rst ;
        }

        //step4  判断售后单对应的订单信息是否存在
        $order_id  = $data_detail['order_id'];
        $order_obj = new Order();
        $order_where['id'] = $order_id;
        $order_return_field = '*';
        $order_info = $order_obj->getRowInfoByWhere($order_where,$order_return_field);
        if(!$order_info){
            $rst['code']='400022';
            return $rst ;
        }

        $rst['data']['order_info'] = $order_info ;
        $rst['data']['data_detail'] = $data_detail;
        return $rst ;

    }

    /**
     * 创建 发往中台的唯一标识符
     * @param  integer $after_market_doc_id 官网售后单ID
     * @param  array   $order_info          官网订单信息
     * @return [type]                       [description]
     */
    private static function createValidateCode($after_market_doc_id=0,$order_info=array()){
        $rst = '';
        if($after_market_doc_id && $order_info){
            $rst = md5($after_market_doc_id.$order_info['order_no']);
        }
        return $rst ;
    }


    /**
     * 创建 发往中台的唯一标识符
     * @param  integer $after_market_doc_id 官网售后单ID
     * @param  array   $order_info          官网订单信息
     * @return [type]                       [description]
     */
    private static function createValidateCodeFromTradeIn($after_market_doc_info=array()){
        $rst = '';
        if($after_market_doc_info ){
            $rst = md5($after_market_doc_info['id'].$after_market_doc_info['delivery_code']);
        }
        return $rst ;
    }


    /**
     * 创建售后工单日志信息
     * @param  int   $after_market_doc_id 官网售后单号ID
     * @param  array  $order_info 订单信息
     * @return int 插入的售后的订单日志的ID
     */
    public static function createOmsAfterMarketLog($after_market_doc_id=0,$order_info=array(),$type='send'){

        $rst = 0 ;

        if($after_market_doc_id && $order_info){

            //step1 读取用户售后请求的信息
            $obj =new AfterMarketDoc();
            $after_market_info = $obj->getRowInfoByWhere(array('id'=>$after_market_doc_id),'*');

            $goods_id = $after_market_info['goods_id'];
            $user_id = $after_market_info['user_id'];
            $after_market_type = $after_market_info['after_market_type'];
            //step2 读取售后管理人员信息
            $admin_id		=$after_market_info['admin_id'];

            $addData['order_id'] = $order_info['id'];
            $addData['after_market_doc_id'] = $after_market_doc_id;
            $addData['after_market_type'] = $after_market_type;
            $addData['order_no'] = $order_info['order_no'];
            $addData['goods_id'] = $goods_id;
            $addData['user_id']  = $user_id;
            $addData['admin_id'] = $admin_id;
            $addData['status']   = 'locking';
            $addData['callback_status']   = 'SENDING';
            $addData['create_time'] = date('Y-m-d H:i:s');
            $addData['validateCode'] = self::createValidateCode($after_market_doc_id,$order_info);
            $addData['type'] = $type;

            //step3  插入信息
            $log_obj = new SendOmsAfterMarketLog();
            $rst = $log_obj->baseInsert('sdb_send_oms_after_market_log',$addData);

        }
        return $rst ;
    }


    /**
     * 创建售后工单日志信息
     * @param  int   $after_market_doc_id 官网售后单号ID
     * @param  array  $order_info 订单信息
     * @return int 插入的售后的订单日志的ID
     */
    public static function createOmsAfterMarketLogFromTradeIn($after_market_doc_id=0,$type='send'){

        $rst = 0 ;

        if($after_market_doc_id ){

            //step1 读取用户售后请求的信息

            $obj = new TradeInAfterMarketDoc();
            $params['cond'] = 'id=:id';
            $params['args'] = [":id"=>$after_market_doc_id];
            $doc_info = $obj->findOneByWhere('sdb_trade_in_after_market_doc',$params);

            $addData['after_market_doc_id'] = $after_market_doc_id;
            $addData['status']   = 'locking';
            $addData['callback_status']   = 'SENDING';
            $addData['create_time'] = date('Y-m-d H:i:s');
            $addData['update_time'] = date('Y-m-d H:i:s');
            $addData['validateCode'] = self::createValidateCodeFromTradeIn($doc_info);
            $addData['type'] = $type;
            $addData['asmOrderNo'] = '';

            //step3  插入信息
            $rst = $obj->baseInsert('sdb_trade_in_send_oms_after_market_log',$addData);

        }
        return $rst ;
    }

    /**
     * 获取退款工单 中台所需要的信息
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单信息
     * @return array
     */
    private static function getRefundOmsData($after_market_info=array(),$order_info=array()){

        $refundOrderPropertiesDTO = array();
        if($after_market_info && $order_info){
            $after_market_type = $after_market_info['after_market_type'];

            //step1 售后事由
            if($after_market_type ==1){
                //退款
                $refundOrderPropertiesDTO['orderReason'] = 'REFUND_GOODS';
                // 需要计算均摊值  ===>变更为用户填写 同时需要加上运费
                $refundTotal = $after_market_info['refund_price']+$after_market_info['refund_freight'];

            }else{
                //退差价
                $refundOrderPropertiesDTO['orderReason'] = 'REFUND_DISPARITY';
                $refundTotal = $after_market_info['refund_price'];
            }



            $refundOrderPropertiesDTO['payType'] =$after_market_info['payment_class_name'];//中台支付方式、
            $refundOrderPropertiesDTO['accountUser'] =$after_market_info['cash_user'];//退款账号对应实名姓名
            $refundOrderPropertiesDTO['account'] =$after_market_info['cash_account'];//退款账号
            $refundOrderPropertiesDTO['accountBank'] = $after_market_info['refund_accountBank'];//退款账号
            $refundOrderPropertiesDTO['refundTotal'] =$refundTotal;//退款总金额
            $refundOrderPropertiesDTO['subBank'] = $after_market_info['subBank'];//开户支行
            $refundOrderPropertiesDTO['bankDistrict'] = $after_market_info['bankDistrict'];//开户行地区

        }
        return $refundOrderPropertiesDTO ;
    }

    /**
     * 获取退货工单 中台所需要的信息
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单信息
     * @return array
     * Note:目前只能提供官网的用户名
     */
    private static function getReceiveOmsData($after_market_info=array(),$order_info=array()){

        $rst = array();
        if($after_market_info  && $order_info){
            //step1 获取客户名称
            $user_id = $order_info['user_id'] ;
            $obj = new Member();
            $user_info = $obj->getUserInfoById($user_id);
            $username  = $user_info['username'];
            $rst['consigneeAccount'] = $username ;

        }
        return $rst ;
    }

    /**
     * 获取退货退款工单 中台所需要的信息
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单信息
     * @return array
     * Note:目前只能提供官网的用户名
     */
    private static function getReceiveRefundOmsData($after_market_info=array(),$order_info=array()){

        $rst = array();
        if($after_market_info && $order_info){

            //step1  需要计算均摊值  ===>直接变更为用户填写
            $refundTotal = $after_market_info['refund_price'];

            //step2 退款的方式 银行信息等
            $rst['payType'] =$after_market_info['payment_class_name'];//中台支付方式、

            $rst['accountUser'] =$after_market_info['cash_user'];;//退款账号对应实名姓名
            $rst['account'] =$after_market_info['cash_account'];;//退款账号
            $rst['accountBank'] =$after_market_info['refund_accountBank'];//退款账号
            $rst['refundTotal'] =$refundTotal;//退款总金额

            //获取用户名
            $user_id = $order_info['user_id'] ;
            $obj = new Member();
            $user_info = $obj->getUserInfoById($user_id);
            $username  = $user_info['username'];
            $rst['consigneeAccount'] = $username ;

            $rst['bankDistrict'] = $after_market_info['bankDistrict'];//开户行地区
            $rst['subBank'] = $after_market_info['subBank'];//开户支行

        }
        return $rst ;
    }

    /**
     * 获取换货工单 中台所需要的信息
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单信息
     * @return array
     */
    private static function getExchangeOmsData($after_market_info=array(),$order_info=array()){

        $rst = array();
        if($after_market_info  && $order_info){
            //step1 收货人的联系 手机或者固定电话
            $mobile = isset($order_info['mobile'])?$order_info['mobile']:'';
            $telphone= isset($order_info['telphone'])?$order_info['telphone']:'';
            $link_info  = '';
            if($mobile || $telphone){
                if($mobile){
                    $link_info = $mobile ;
                }else{
                    $link_info = $telphone ;
                }
            }

            //step2 获取订单收获地址省市区的名称
            $province = isset($order_info['province'])?$order_info['province']:'';
            $city = isset($order_info['city'])?$order_info['city']:'';
            $area = isset($order_info['area'])?$order_info['area']:'';
            $area_obj = new Areas();
            $province = $area_obj->getAreaName($province);
            $city = $area_obj->getAreaName($city);
            $area = $area_obj->getAreaName($area);

            $rst['receiverName'] = isset($order_info['accept_name'])?$order_info['accept_name']:'';//收货人姓名
            $rst['receiverMobile'] =$link_info;//收货人手机或者固定电话信息
            $rst['receiverProvince'] = $province;//收货人省
            $rst['receiverCity'] = $city;//收货人市
            $rst['receiverDistrict'] = $area;//收货人区
            $rst['receiverAddress'] = isset($order_info['address'])?$order_info['address']:'';//收货人详细地址
            $rst['consigneeAccount'] = $order_info['user_id'];
        }
        return $rst ;
    }

    /**
     * 获取以旧换新中台所需要的信息
     * @param $after_market_info 售后单信息
     */
    private static function getTradeInOmsData($after_market_info){


        //step2 获取订单收获地址省市区的名称
        $province = isset($after_market_info['province'])?$after_market_info['province']:'';
        $city = isset($after_market_info['city'])?$after_market_info['city']:'';
        $area = isset($after_market_info['area'])?$after_market_info['area']:'';
        $area_obj = new Areas();
        $province = $area_obj->getAreaName($province);
        $city = $area_obj->getAreaName($city);
        $area = $area_obj->getAreaName($area);

        $rst['transportCompany'] = $after_market_info['freight_code'];//快递公司
        $rst['transportNumber'] = $after_market_info['delivery_code'];//快递单号
        $rst['payType'] = 'cash_coupon';//以旧换新的支付方式固定写死
        $rst['consigneeAccount'] = $after_market_info['username'];
        $rst['consigneePhone'] = '';
        $rst['consigneeMobile'] = $after_market_info['mobile'];
        $rst['consigneeName'] = $after_market_info['contact'];
        $rst['consigneeProvince'] = $province;//收货人省
        $rst['consigneeCity'] = $city;//收货人市
        $rst['consigneeDistrict'] = $area;//收货人区
        $rst['consigneeAddress'] = $after_market_info['address'];

        return $rst ;
    }

    /**
     * 申请维修时 返回给中台的数据
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单信息
     * @return array
     * Note: 目前实际实际上是和 getExchangeOmsData是一样的内容 但是考虑到后期考虑还是区分开
     */
    private static function getRepairOmsData($after_market_info=array(),$order_info=array()){

        $rst = self::getExchangeOmsData($after_market_info,$order_info);
        return $rst ;

    }

    /**
     *获取退货的时候详细问题描述
     * @param  array   $after_market_info [description]
     * @param  array   $order_info        [description]
     * @param  string  $orderType 售后类型
     * @return string
     */
    private static function getProblemDesc($after_market_info=array(),$order_info=array(),$orderType){

        $rst = '';
        if($after_market_info && $order_info){

            //step1 查询指定订单商品表信息
            $order_id = $after_market_info['order_id'] ;
            $goods_id = $after_market_info['goods_id'] ;
            $order_goods_obj = new OrderGoods();
            $order_goods_where['order_id'] = $order_id;
            $order_goods_where['goods_id'] = $goods_id;
            $order_goods_return_field = '*';
            $goods_info = $order_goods_obj->getRowInfoByWhere($order_goods_where,$order_goods_return_field);

            //step1.1 获取售后类型中文释义
            $orderTypeStr = self::getAftermarketTypeStr($orderType,$after_market_info);

            //step2 读取文件名和物料号 均是读取快照信息
            $tmp_info    =json_decode($goods_info['goods_array'],true);
            $productName =isset($tmp_info['name'])?$tmp_info['name']:'';
            $materialNo  =isset($tmp_info['ecovacs_goods_no'])?$tmp_info['ecovacs_goods_no']:'';

            //step3获取主品对应的成交价
            $price  ='';
            if($orderType =='RECEIVE_REFUND' || $orderType =='REFUND' ){
                //推送给中台实际的成交价
                $price = $after_market_info['refund_price'];

            }

            //step4.1 存在运费
            if($after_market_info['after_market_type']==1 && $after_market_info['refund_freight']){
                $rst .='【退货款:'.$after_market_info['refund_price'].', 退运费'.$after_market_info['refund_freight'].'】' ;
            }

            //step4 拼接主品的商品信息
            $rst .='【商品编码】'.$materialNo.'，【商品名称】'.$productName.'，【数量】'.$after_market_info['goods_nums'].'，【售后类型】'.$orderTypeStr.'，【金额】'.$price;



            //step5 拼接主品对应赠品信息
            $giveaway_params = ['cond'=>'order_id = :order_id and main_goods_id = :main_goods_id','args'=>[':order_id'=>$order_id,':main_goods_id'=>$goods_id],'fields'=>'*'];
            $giveaway_goods_list = $order_goods_obj->findAllByWhere('sdb_giveaway_activity_order',$giveaway_params);
            if($giveaway_goods_list){
                foreach($giveaway_goods_list as $v){

                    $giveaway_goods_id = $v['giveaway_goods_id'];
                    $giveaway_order_goods_params['cond']='order_id =:order_id and goods_id=:goods_id';
                    $giveaway_order_goods_params['args']=[":order_id"=>$order_id,':goods_id'=>$giveaway_goods_id];
                    $giveaway_order_goods_params['fields']='goods_array,global_no';
                    $giveaway_order_goods = $order_goods_obj->findOneByWhere('sdb_order_goods',$giveaway_order_goods_params);
                    $tmp_info    =json_decode($giveaway_order_goods['goods_array'],true);
                    $productName =isset($tmp_info['name'])?$tmp_info['name']:'';
                    $materialNo  =isset($tmp_info['ecovacs_goods_no'])?$tmp_info['ecovacs_goods_no']:'';
                    $all_giveaway_num = $v['giveaway_num']*$after_market_info['goods_nums'] ;
                    $rst .='【商品编码】'.$materialNo.'，【商品名称】'.$productName.'，【数量】'.$all_giveaway_num.'，【售后类型】'.$orderTypeStr.'，【金额】';

                }
            }

            $order_amount = $order_info['order_amount'];
            $real_freight= $order_info['real_freight'];
            $rst .='【订单支付金额】'.$order_amount.'(含运费'.$real_freight.'元)';

        }

        return $rst ;

    }


    /**
     *获取以旧换新详细问题描述
     * @param  array   $after_market_info [description]
     * @param  array   $order_info        [description]
     * @param  string  $orderType 售后类型
     * @return string
     */
    private static function getProblemDescFromTradeIn($after_market_info=array()){

        $rst = '';
        if($after_market_info ){

            $rst  = '申请机型：'.$after_market_info['apply_goods_name'].'，';
            $rst .= '申请物料号：'.$after_market_info['apply_materiel_no'].'，';
            $rst .= 'SN码：'.$after_market_info['sn'].'，';
            $rst .= '快递公司：'.$after_market_info['freight_code'].'，';
            $rst .= '快递单号：'.$after_market_info['delivery_code'].'，';
            $rst .= '估值：'.$after_market_info['estimated_price'];
        }

        return $rst ;

    }

    /**
     * 获取中台需要的商品列表信息
     * @param  array  $after_market_info [description]
     * @param  array  $order_info        [description]
     * @param  array  $receiveRefundOrderPropertiesDTO 处理结束的退货退款的属性信息
     * @return array
     * Note:报修、退货、退货退款、换货 时都需要添加该对象信息
     */
    private static function getAsmOrderProductItemList($after_market_info=array(),$order_info=array(),$orderType){
        $rst = array();
        if($after_market_info && $order_info){

            //step1 查询指定订单商品表信息
            $order_id = $after_market_info['order_id'] ;
            $goods_id = $after_market_info['goods_id'] ;
            $order_goods_obj = new OrderGoods();
            $order_goods_where['order_id'] = $order_id;
            $order_goods_where['goods_id'] = $goods_id;
            $order_goods_return_field = '*';
            $goods_info = $order_goods_obj->getRowInfoByWhere($order_goods_where,$order_goods_return_field);

            //step2 读取文件名和物料号 均是读取快照信息
            $tmp_info    =json_decode($goods_info['goods_array'],true);
            $productName =isset($tmp_info['name'])?$tmp_info['name']:'';
            $materialNo  =isset($goods_info['global_no'])?$goods_info['global_no']:'';
            $temp_rst= array(
                'productName' =>$productName,
                'materialNo'=>$materialNo,
                'count'=>$after_market_info['goods_nums'],
            );
            if($orderType =='RECEIVE_REFUND'){
                //推送给中台实际的成交价
                $price = self::getSendToAsmPrice($after_market_info);
                $temp_rst['price'] = $price ;
            }
            $rst[] = $temp_rst ;

            //step3 假如存在赠品还需要处理赠品数据
            //退货、换货这2种情况都需要处理
            if($orderType =='RECEIVE' || $orderType =='EXCHANGE' || $orderType =='RECEIVE_REFUND'){

                $gift_info = $order_goods_obj->getSendOmsGiftInfo($after_market_info['order_id']);
                if($gift_info){
                    foreach($gift_info as $v){
                        $tmp_info    =json_decode($v['goods_array'],true);
                        $productName =isset($tmp_info['name'])?$tmp_info['name']:'';
                        $materialNo  =isset($v['global_no'])?$v['global_no']:'';
                        $rst[] = array(
                            'productName'=>$productName,
                            'materialNo'=>$materialNo,
                            'priceMemo'=>'赠品',
                            'count'=>$v['goods_nums'],
                        );

                    }
                }

            }
        }
        return $rst ;
    }


    /**
     * 获取以旧换新中台需要的商品列表信息
     * @param  array  $after_market_info [description]
     * @return array
     */
    private static function getAsmOrderProductItemListFromTradeIn($after_market_info=array()){
        $rst = array();
        if($after_market_info ){



            $trade_in_id = $after_market_info['trade_in_id'];
            $obj = new TradeIn();
            $params['cond'] = 'id=:id';
            $params['args'] = [':id'=>$trade_in_id];
            $info = $obj->findOneByWhere('sdb_trade_in',$params);

            if($info){
                $productName = $info['goods_name'];
                $materialNo = $info['materiel_no'];
                $productSn = $after_market_info['sn'];
                $rst[] = array(
                    'productName'=>$productName,
                    'materialNo'=>$materialNo,
                    'productSn'=>$productSn,
                );
            }



        }
        return $rst ;
    }


    /**
     * 发往售后中心的时候，需要获取售后商品的实际成本价
     * @param  array $after_market_info 官网售后单信息
     * @return number
     */
    public static function getSendToAsmPrice($after_market_info=array()){

        $order_id    = $after_market_info['order_id'];
        $order_obj = new Order();
        $order_info = $order_obj->getRowInfoByWhere(array('id'=>$order_id));
        $rst =  self::getRefundGoodsPrice($after_market_info,$order_info,true);
        return  sprintf('%.2f', $rst);
    }


    /**
     * 客户申请退款时 获取最终需要退款的平摊金额
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单信息
     * @param  boolean$is_send_to_asm          是否发送给中台的时候
     * @return decimal 最终的平摊金额 eg:89.01
     * Note:after_market_type == 1
     */
    public static function getRefundGoodsPrice($after_market_info=array(),$order_info=array(),$is_send_to_asm=false){

        $rst_amount = 0.00 ;


        //step1 判断是否为预售订单 在订单逾期未支付尾款 只能返回定金金额
        $order_type = $order_info['type'];
        if($order_type == 6){
            $presell_order_ids[$order_info['id']]=$order_info['id'];
            $presell_orders[$order_info['id']] =  $order_info;
            //预售信息
            if($presell_order_ids){
                $presell_obj = new PreSell();
                $presell_data_list = $presell_obj->getPresellDataList($presell_order_ids);

                $order_goods_obj = new OrderGoods();
                $order_goods_where['order_id'] = $order_info['id'] ;
                $order_goods_where['goods_id'] = $after_market_info['goods_id'] ;
                $order_goods_return_field = '*';
                $presell_order_goods_info = $order_goods_obj->getRowInfoByWhere($order_goods_where,$order_goods_return_field);

                $all_presll_goods_num = isset($presell_order_goods_info['goods_nums'])?$presell_order_goods_info['goods_nums']:0;
                if(isset($presell_data_list[$order_info['id']]['isovertime']) && $presell_data_list[$order_info['id']]['isovertime']){

                    $rst_amount = ($presell_data_list[$order_info['id']]['deposit']*$after_market_info['goods_nums'] )/$all_presll_goods_num;
                    return $rst_amount ;
                }

            }
        }


        //step2 获取当前售后单订单商品的信息
        $order_id = $after_market_info['order_id'] ;
        $goods_id = $after_market_info['goods_id'] ;

        $goods_nums = $after_market_info['goods_nums'] ;
        $order_goods_obj = new OrderGoods();
        $order_goods_where['order_id'] = $order_id ;
        $order_goods_where['goods_id'] = $goods_id ;
        $goods_info = $order_goods_obj->getRowInfoByWhere($order_goods_where);

        //step3 判断是否该订单的最后一批退款或退货商品 订单类型必须全部商品都申请退款或者退货

        //step3.1 先查询该订单已经退款、退货商品总数目
        $order_goods_single_obj = new OrderGoodsSingle();
        $num1 = $order_goods_single_obj->getOtherGoodsRefundAndReceiveNum($order_id,$goods_id);


        //step3.2 查询订单中非当前商品的数量
        $num2 = $order_goods_single_obj->getOtherGoodsNum($order_id,$goods_id);

        //step3.3 查询当前商品的数量
        $is_last = false ;
        $num3 = $order_goods_single_obj->getCurrentGoodsNum($order_id,$goods_id);

        //除去当前商品已经全部发货
        if($num3 ==$goods_nums && $num2 ==$num1){
            $is_last = true ;
        }


        //step3.4
        if(!$is_last && $num2 ==$num1){
            $num4 = $order_goods_single_obj->getCurrentGoodsRefundAndReceiveNum($order_id,$goods_id);
            $temp_sum = $num4+$goods_nums ;
            if($temp_sum ==$num3){
                $is_last = true;
            }
        }


        //3.2判断得到是最后一批
        if($is_last){

            //3.3 获得最后的金额
            $order_amount = $order_info['order_amount'];
            //查看退款和和退差价总金额
            //由于是其他的商品都是退款所以再分别计算
            $after_market_doc_obj = new AfterMarketDoc();
            $send_aftermarket_info = $after_market_doc_obj->getSendInfoByOrderId($order_id);
            $send_amount = 0 ;
            if($send_aftermarket_info){
                foreach($send_aftermarket_info as $v){
                    $send_amount += $v['refund_price'];
                }
            }
            //运费
            $payable_freight = $order_info['payable_freight'] ;
            return $order_amount - $send_amount - $payable_freight ;

        }

        if($goods_info){

            //step4 计算成本价
            $tmp_info			= json_decode($goods_info['goods_array'],true);
            $goods_info['name']			=isset($tmp_info['name'])?$tmp_info['name']:'';
            $goods_info['value']			=isset($tmp_info['value'])?$tmp_info['value']:'';
            $goods_info['delivery_info']	=NULL;

            //货款= 成本价 - 已申请的退差价 需要排除已取消的售后请求

            $all_order_goods_params['cond'] = 'order_id=:order_id';
            $all_order_goods_params['args'] = [':order_id'=>$order_id];
            $all_order_goods_info = $order_goods_obj->findAllByWhere('sdb_order_goods',$all_order_goods_params);

            $costPrice = $order_goods_obj->getGoodsCostPriceByGoodsId($order_info,$all_order_goods_info,$goods_id);
            $costPrice = isset($costPrice['price']) ? $costPrice['price'] : 0;

            //得到已取消的售后请求
            $doc_obj = new AfterMarketDoc();
            $is_cancel_ids_arr = $doc_obj->getCancelAfterMarketDocId($order_id,$goods_id);

            //发送到售后中心 商品的实际金额需要排除自身
            //差额
            $after_market_doc_obj = new AfterMarketDoc();

            $after_market_doc_id = isset($after_market_info['id'])?$after_market_info['id']:0 ;
            $disparity = $after_market_doc_obj->getDisparity($after_market_doc_id,$order_id,$goods_id,$is_cancel_ids_arr,$is_send_to_asm);

            //获取商品下单总数目
            $allGoodsNum = isset($goods_info['goods_nums'])?$goods_info['goods_nums']:0;
            //剩余的总金额
            $leftAllPrice = $allGoodsNum*$costPrice -$disparity ;
            //剩余允许的商品的售后数量

            $leftGoodsNum = $after_market_doc_obj->returnLeftGoodsNum($after_market_info);
            $rst_amount  = ($leftAllPrice*$after_market_info['goods_nums'])/$leftGoodsNum;

        }

        return $rst_amount ;
    }


    /**
     * 根据售后类型格式化发往中台的额外数据
     * @param  array  $rst               最终返回的数组信息
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单表信息
     * @return array
     */
    public static function formateOmsDataByAftermarketType($rst=array(),$after_market_info=array(),$order_info=array()){

        $orderType = isset($rst['orderType'])?$rst['orderType']:'' ;

        if($orderType){
            if($orderType =='REFUND'){
                //退款 或 退差价
                $rst['refundOrderPropertiesDTO'] = self::getRefundOmsData($after_market_info,$order_info);
            }else if($orderType =='RECEIVE'){
                //退货
                $rst['receiveOrderPropertiesDTO'] = self::getReceiveOmsData($after_market_info,$order_info);
                //$rst['asmOrderProductItemList'] = self::getAsmOrderProductItemList($after_market_info,$order_info,$orderType);
            }else if($orderType =='RECEIVE_REFUND'){
                //退货 相当于中台的退货退款
                $rst['receiveRefundOrderPropertiesDTO'] = self::getReceiveRefundOmsData($after_market_info,$order_info);
                //$rst['asmOrderProductItemList'] = self::getAsmOrderProductItemList($after_market_info,$order_info,$orderType);
            }else if($orderType =='EXCHANGE'){
                //换货
                $rst['exchangeOrderPropertiesDTO'] = self::getExchangeOmsData($after_market_info,$order_info);
                //$rst['asmOrderProductItemList'] = self::getAsmOrderProductItemList($after_market_info,$order_info,$orderType);
            }else if($orderType =='REPAIR'){
                //维修
                $rst['repairOrderPropertiesDTO'] = self::getRepairOmsData($after_market_info,$order_info);
                //$rst['asmOrderProductItemList'] = self::getAsmOrderProductItemList($after_market_info,$order_info,$orderType);
            }else if($orderType =='OLD_FOR_NEW'){
                $rst['oldForNewOrderPropertiesDTO'] = self::getTradeInOmsData($after_market_info);
                $rst['asmOrderProductItemList'] =  self::getAsmOrderProductItemListFromTradeIn($after_market_info);
            }
        }
        return $rst  ;
    }


    /**
     * 返回发送给中台的售后申请数据
     * @param  int   $after_market_doc_id 官网售后单号ID
     * @param  array $order_id 订单信息
     * @return array
     * Note:
     * 	REFUND：退款
     *	RECEIVE_MONEY：收款
     *  REPAIR：报修
     *  RECEIVE：退货
     *  RECEIVE_REFUND：退货退款
     *  EXCHANGE：换货
     */
    public static function returnApplyWorkOrderOmsData($after_market_doc_id=0,$order_info=array()){
        $rst = array() ;

        if($order_info){

            //step1 获取客户名称
            $user_id = $order_info['user_id'] ;
            $member_obj = new Member();
            $user_info = $member_obj->getUserInfoById($user_id);
            $username  = $user_info['username'];

            //step2 获取售后类型
            $after_market_obj = new AfterMarketDoc();
            $after_market_info = $after_market_obj->getRowInfoById($after_market_doc_id,'*');
            $after_market_type = $after_market_info['after_market_type'];
            $type_arr = self::$type_arr ;
            $orderType = $type_arr[$after_market_type];
            $problemDesc = $after_market_info['request_content'];//官网后台输入的问题描述
            $extra_problem_desc = self::getProblemDesc($after_market_info,$order_info,$orderType);//拼接额外的问题描述
            $problemDesc  = $problemDesc.$extra_problem_desc ;
            $rst['shopNo'] = self::$shopNo;
            $rst['originalOrderNo'] = $order_info['order_no'];
            $rst['problemDesc'] = $problemDesc;//问题描述
            $rst['customerNick'] = $username;//客户名称 对应user表的username
            $rst['customerName'] = $after_market_info['contact_user'];//顾客姓名
            $rst['customerPhone'] = $after_market_info['contact'];//顾客手机
            $rst['orderType'] = $orderType;//工单类型
            $work_order_callback_url = self::getWorkOrderCallbackUrl();
            $rst['callBackNotifyUrl'] = $work_order_callback_url;//回调地址 工单完成关闭
            $rst['validateCode'] = self::createValidateCode($after_market_doc_id,$order_info);//发往中台唯一标识符
            $rst['orderSource'] = 'asm_order_source_ecovacs';

            $rst = self::formateOmsDataByAftermarketType($rst,$after_market_info,$order_info) ;
            $rst = self::formateOmsFileInfo($rst,$after_market_info);

        }
        return $rst ;
    }

    /**
     * 返回发送给中台的售后申请数据
     * @param  int   $after_market_doc_id 官网售后单号ID
     * @return array
     * Note:
     * 	OLD_FOR_NEW：以旧换新
     */
    public static function returnApplyWorkOrderOmsDataFromTradeIn($after_market_doc_id=0){
        $rst = array() ;

        if($after_market_doc_id){

            $doc_obj = new TradeInAfterMarketDoc();
            $doc_params['fields'] = '*';
            $doc_params['cond'] = 'id=:id';
            $doc_params['args'] = ["id"=>$after_market_doc_id];
            $after_market_info = $doc_obj->findOneByWhere('sdb_trade_in_after_market_doc',$doc_params);

            //step2 获取客户名称
            $username = $after_market_info['username'] ;
            //step3 获取售后类型 固定类型为6
            $after_market_type = 6;
            $type_arr = self::$type_arr ;
            $orderType = $type_arr[$after_market_type];
            //step  拼接额外的问题描述
            $problemDesc = self::getProblemDescFromTradeIn($after_market_info);

            $rst['shopNo'] = self::$shopNo;
            $rst['originalOrderNo'] = '';//
            $rst['problemDesc'] = $problemDesc;//问题描述
            $rst['customerNick'] = $username;//客户名称 对应user表的username
            $rst['customerName'] = $after_market_info['contact'];//顾客姓名
            $rst['customerPhone'] = $after_market_info['mobile'];//顾客手机
            $rst['orderType'] = $orderType;//工单类型
            $work_order_callback_url = self::getWorkOrderCallbackUrlFromTradeIn();
            $rst['callBackNotifyUrl'] = $work_order_callback_url;//回调地址 工单完成关闭
            $rst['validateCode'] = self::createValidateCodeFromTradeIn($after_market_info);//发往中台唯一标识符
            $rst['orderSource'] = 'asm_order_source_ecovacs';
            $rst['contactInfo'] = self::getTradeInContactInfo($after_market_info);

            $order_info = array();
            $rst = self::formateOmsDataByAftermarketType($rst,$after_market_info,$order_info) ;
            //$rst = self::formateOmsFileInfo($rst,$after_market_info);

        }
        return $rst ;
    }

    /**
     * 获取发送中台的图片的url地址
     * @param  array  $after_market_info 官网售后单信息
     * @return array
     */
    public static function formateOmsFileInfo($rst=array(),$after_market_info=array()){
        if($after_market_info){
            $img_url = $after_market_info['img_url'];
            $info = unserialize($img_url) ;

            if($info){
                $static_url = CDN_URL.'/';
                foreach($info as $img_url){
                    if($img_url){
                        $arr  = explode('/', $img_url);
                        $fileName = $arr[count($arr)-1];

                        $filePath = $static_url.$img_url;
                        $temp_arr = array('fileName'=>$fileName,'filePath'=>$filePath);
                        $rst['fileItemDTOList'][] = $temp_arr ;
                    }
                }
            }

        }


        return $rst ;
    }


    /**
     * 发送售后申请工单数据给手中心
     * @param  int   $after_market_doc_id 官网售后单号ID
     * @param  array  $order_info 订单信息
     * @return array array('code'=>1,'msg'=>'');
     * Note: oms 返回的数据格式
     * {"code":"0000","msg":"操作成功","data":{"asmOrderNo":"701466390703933004006","originalOrderNo":"118143003026"}}
     */
    public static function sendApplyDataToAsm($after_market_doc_id,$order_info){

        $rst['code'] = 1 ;
        $rst['msg'] = '';

        //step1 新增send_oms_after_market_log记录
        $log_id = self::createOmsAfterMarketLog($after_market_doc_id,$order_info,'send');
        if(!$log_id){
            //创建失败
            $rst['code'] = '400023';
            return $rst ;
        }


        //step2 发送给中台的数据
        $send_data = self::returnApplyWorkOrderOmsData($after_market_doc_id,$order_info);
        $create_asm_order_url = self::getCreateAsmOrderUrl();
        $oms_return_info = self::sendDataByCurl($create_asm_order_url,$send_data,'json');

        $oms_return_arr = json_decode($oms_return_info,true);
        $is_return_json = 	json_last_error() == JSON_ERROR_NONE ;
        if(!$is_return_json){

            $rst['code'] = '400024';
            return $rst ;
        }

        //step3 售后中心返回值判断
        $oms_return_code = $oms_return_arr['code'];
        if($oms_return_code !='0000'){
            $rst['code'] = $oms_return_code ;
            $rst['msg'] = $oms_return_arr['msg'];
            return $rst ;
        }

        //step4 更新send_oms_after_market_log 中台回调字段信息
        $oms_data = $oms_return_arr['data'] ;
        $asmOrderNo = $oms_data['asmOrderNo'];//中台创建的工单号
        $send_oms_after_market_log_obj = new SendOmsAfterMarketLog();
        $log_update_data['status'] = 'unlock';
        $log_update_data['asmOrderNo'] = $asmOrderNo;
        $log_where_str = ' id=:id';
        $log_where_arr[":id"] = $log_id;
        $send_oms_after_market_log_obj->baseUpdate('sdb_send_oms_after_market_log',$log_update_data,$log_where_str,$log_where_arr);


        return $rst ;

    }

    /**
     * 处理相关的基础的log数据
     * @param $data_detail
     * @param $post_data
     * @return array
     */
    public static function getInitLogData($data_detail,$post_data){
        $log_data = array(
            'act'=> 'aftermarket_audit',
            'act_name'=>'售后单管理',
            'act_type'=>'审核售后申请单',
            'act_content'=>'审核售后申请单:'.$data_detail['id'],
            'order_id'=>$data_detail['order_id'],
            'order_no'=>$data_detail['order_no'] ,
            'note'=>'',
            'content' =>json_encode($post_data),
            'aftermarket_id'=>$data_detail['id']
        );

        return $log_data;
    }

    /**
     * 执行发往售后中心的操作
     */
    public static function doSend(){

        $task_list = self::getTasks();

        if($task_list){

            $task_log_obj = new AsmTaskLog();


            //统一使用同一个审核状态
            $audit_status		=1;
            $audit_idea			='前台用户申请售后单！';
            foreach($task_list as $v){
                $after_market_doc_id = $v['after_market_doc_id'];
                $postData = $v['postData'];
                $userInfo = $v['userInfo'];

                //step1 判断参数
                $check_rst = self::checkParams($after_market_doc_id,$postData,$userInfo);
                if($check_rst['code']!=1){
                    //添加错误日志
                    $task_log_obj->addSendLog($v['id'],$check_rst);
                    continue;
                }

                //step2 将售后信息发往售后中心 并更新相关表数据
                $order_info = $check_rst['data']['order_info'];
                $send_rst = self::sendApplyDataToAsm($after_market_doc_id,$order_info);

                if($send_rst['code']!=1){
                    //添加错误日志
                    $task_log_obj->addSendLog($v['id'],$send_rst);
                    continue;
                }

                //step3 更新售后表信息和订单商品表相关信息
                $after_market_doc_info = $check_rst['data']['data_detail'];
                $log_data = self::getInitLogData($after_market_doc_info,$postData);
                $after_market_doc_obj = new AfterMarketDoc();
                $aftermarketRst = $after_market_doc_obj->dealPassedAftermarketDoc($after_market_doc_info,$audit_idea,$log_data,$userInfo);
                if($aftermarketRst['code']!=1){
                    $task_log_obj->addSendLog($v['id'],$aftermarketRst);
                    continue;
                }

                //step4更新任务日志信息
                $asm_task_obj = new AsmTask();
                $asm_task_update_data['status'] = 'SUCCESS';
                $asm_task_update_data['update_time'] = date('Y-m-d H:i:s');
                $asm_task_where_str = 'id=:id';
                $asm_task_where_arr[':id'] = $v['id'];

                $asm_task_obj->baseUpdate('sdb_asm_task',$asm_task_update_data,$asm_task_where_str,$asm_task_where_arr);

                //step5 增加任务日志信息
                $ask_task_log_obj = new AsmTaskLog();
                $log_add_data['task_id'] = $v['id'];
                $log_add_data['task_type'] = 'send_aftermarket_to_asm';
                $log_add_data['create_time'] = date('Y-m-d H:i:s');
                $log_add_data['return_message'] = serialize($aftermarketRst);
                $ask_task_log_obj->baseInsert('sdb_asm_task_log',$log_add_data);


            }
        }
    }

    /**
     * 获取符合条件取消的任务信息
     * @return array
     */
    public static function getCancelTasks(){

        $task_info = array();

        $task_obj = new  AsmTask();
        $task_where['status'] = "SENDING";
        $task_where['service_key'] = "send_cancel_aftermarket_to_asm";
        $task_return_field = '*';
        $info = $task_obj->getListInfoByWhere($task_where,$task_return_field);

        $rst = array();
        if($info){
            foreach($info as $v){

                //获取已经发送过的任务信息
                $log_obj = new AsmTaskLog();
                $log_where['task_id'] = $v['id'];
                $log_return_field = ' count(1) as send_num,max(create_time) as create_time ';
                $count_info = $log_obj->getRowInfoByWhere($log_where,$log_return_field);

                //设置不同的发送请求的频率
                $send_num = isset($count_info['send_num'])?$count_info['send_num']:0 ;
                $last_create_time = isset($count_info['create_time'])?$count_info['create_time']: '0000-00-00 00:00:00' ;
                $ext_time = time()-strtotime($last_create_time) ;
                if($send_num>10 && $send_num <=20){
                    if($ext_time < 1800){
                        continue ;
                    }
                }
                if($send_num >20){
                    if($ext_time < 3600){
                        continue ;
                    }
                }

                //设置返回结果
                $rst[] = $v ;
            }
        }

        return $rst ;
    }


    /**
     * 确认退款回调 退款操作失败
     * @param  array  $data_detail     官网售后单ID
     * @param  string $msg             用户取消原因
     * @return boolean
     * 相当于之前中台处理失败  callback/confirmRefund  -> aftermarket::oms_refund_fail
     */
    public static function aftermarket_confirm_fail_by_user($data_detail=array(),$msg='用户申请取消售后'){

        $rst['code'] =1 ;
        $rst['msg'] = '';

        //step1 更新订单发货表信息
        $order_goods_single_db = new OrderGoodsSingle();
        $order_goods_single_update_data = ['after_market_status'=>0];
        $order_goods_single_db->baseUpdate('sdb_order_goods_single',$order_goods_single_update_data,'amd_id =:amd_id',array(":amd_id"=>$data_detail['id']));

        //step2 更新订单商品相关字段
        $goods_nums = $data_detail['goods_nums'];
        $order_goods_update_where_str = ' order_id = :order_id and goods_id = :goods_id ';
        $order_id  = $data_detail['order_id'] ;
        $goods_id  = $data_detail['goods_id'] ;
        $order_goods_update_where_arr = [":order_id"=>$order_id,':goods_id'=>$goods_id];
        $order_goods_update_data = [ 'refund_status'=>0,'refund_nums'=>new Expression('refund_nums + '.$goods_nums) , 'return_goods_nums'=>new Expression('return_goods_nums + '.$goods_nums) ,'exchange_nums'=>new Expression('exchange_nums + '.$goods_nums)  ];
        $order_goods_obj = new OrderGoods();
        $order_goods_obj->baseUpdate('sdb_order_goods',$order_goods_update_data,$order_goods_update_where_str,$order_goods_update_where_arr);

        //step3 更新售后表信息
        $current_time = date('Y-m-d H:i:s');
        $doc_log=@unserialize($data_detail['doc_log']);
        $doc_log[count($doc_log)]	=array('time'=>$current_time,'type'=>'取消售后','operater'=>'用户取消');
        $doc_log = serialize($doc_log);

        $doc_update_data = ['audit_status'=>2,'audit_idea'=>$msg,'dispose_status'=>2,'dispose_time'=>$current_time,'dispose_idea'=>$msg,'doc_log'=>$doc_log];
        $doc_obj = new AfterMarketDoc();
        $doc_obj->baseUpdate('sdb_after_market_doc',$doc_update_data,'id=:id',array(":id"=>$data_detail['id']));

        //step4 新增售后相关日志
        $logdata = array(
            'act'=> 'aftermarket_refund_close',
            'act_name'=>'退款单管理',
            'act_type'=>'关闭退款单',
            'act_content'=>'关闭售后请求 售后单号'.$data_detail['id'],
            'order_id'=>$data_detail['order_id'],
            'order_no'=>$data_detail['order_no'] ,
            'note'=>'关闭理由:'.$msg,
            'content' =>json_encode($data_detail),
            'aftermarket_id'=>$data_detail['id']
        );
        $log_obj = new LogAftermarket();
        $log_obj->baseInsert('sdb_log_aftermarket',$logdata);

        return $rst ;

    }




    /**
     * 执行发往售后中心的操作
     */
    public static function doCancel($after_market_doc_id){

        $cancel_reason = '用户申请取消售后';
        $doc_obj = new AfterMarketDoc();
        $task_obj = new AsmTask();
        $deal_obj = new DealAftermarket();


        //判断是否发送请求到中台
        $params['cond'] = ' after_market_doc_id = :after_market_doc_id and status ="SENDING" ';
        $params['args'] = [':after_market_doc_id'=>$after_market_doc_id];
        $info = $task_obj->findOneByWhere('sdb_asm_task',$params);



        if($info){
            //更新为CANCEL
            $task_obj->baseUpdate('sdb_asm_task',['status'=>'CANCEL'],'id=:id',[':id'=>$info['id']]);
        }

        //step3 向中台发送请求
        $rst = self::sendCancelWorkOrderToOms($after_market_doc_id, $cancel_reason);

        //step4 同时模拟执行回调请求
        if($rst['code']==1 && isset($rst['data'])) {
            $data = isset($rst['data']) ? $rst['data'] : array();
            $data_detail = isset($data['data_detail']) ? $data['data_detail'] : array();
            $asmOrderNo = isset($data['asmOrderNo']) ? $data['asmOrderNo'] : '';
            $originalOrderNo = isset($data['originalOrderNo']) ? $data['originalOrderNo'] : '';
            $rst = $deal_obj->aftermarket_confirm_fail($data_detail, $asmOrderNo, $originalOrderNo, $cancel_reason);

            $data_detail =$doc_obj->getRowInfoByWhere(['id' => $after_market_doc_id]);
            $rst =  self::aftermarket_confirm_fail_by_user($data_detail, $cancel_reason);
        }

        return $rst ;
    }


    /**
     * 返回中台需要的取消工单的数组信息
     * @param  integer $after_market_doc_id 官网售后申请单的ID
     * @param  string  $cancel_reason       取消原因
     * @return array
     */
    public static function returnCancelWorkOrderOmsData($after_market_doc_id=0,$cancel_reason=''){
        //查询最近一次的售后操作行为 并查询售后工单号
        $rst = array();
        if($after_market_doc_id){

            $obj = new SendOmsAfterMarketLog();
            $where_arr = ['after_market_doc_id'=>$after_market_doc_id,'status'=>'unlock'];
            $info =  $obj->getRowInfoByWhere($where_arr);

            if($info){

                $asmOrderNo  = $info['asmOrderNo'] ;
                $originalOrderNo = $info['order_no'];
                $orderMemo = $cancel_reason ;
                $rst = compact('asmOrderNo','originalOrderNo','orderMemo');
            }
        }
        return $rst ;

    }

    /**
     * 创建取消售后工单的相关日志
     * @param int $after_market_doc_id
     * @param array $order_info
     * @param $asmOrderNo
     * @return int
     */
    public static function createCancelAfterMarketLog($after_market_info=0,$order_info=array(),$asmOrderNo){
        $rst = 0 ;
        $addData = array();
        if($after_market_info && $order_info){
            //step1 读取用户售后请求的信息
            $goods_id = $after_market_info['goods_id'];
            $user_id = $after_market_info['user_id'];
            $after_market_type = $after_market_info['after_market_type'];
            //step2 读取售后管理人员信息
            //$admin_info		=CheckRights::getAdmin();
            //$admin_info = unserialize(ISafe::get('admin_info'));
            //$admin_id		=is_array($admin_info)?$admin_info['admin_id']:0;
            $admin_id = 0 ;
            $after_market_doc_id = $after_market_info['id'];
            $addData['order_id'] = $order_info['id'];
            $addData['after_market_doc_id'] = $after_market_doc_id;
            $addData['after_market_type'] = $after_market_type;
            $addData['order_no'] = $order_info['order_no'];
            $addData['goods_id'] = $goods_id;
            $addData['user_id']  = $user_id;
            $addData['admin_id'] = $admin_id;
            $addData['status']   = 'unlock';
            $addData['callback_status']   = 'SENDED';
            $addData['create_time'] = date('Y-m-d H:i:s');
            $addData['asmOrderNo'] = $asmOrderNo ;
            $addData['validateCode'] = self::createValidateCode($after_market_doc_id,$order_info);
            $addData['type'] ='cancel';

            //step3  插入信息
            $obj = new SendOmsAfterMarketLog();
            $rst = $obj->baseInsert('sdb_send_oms_after_market_log',$addData);
        }
        return $rst ;
    }


    /**
     * 发送取消工单请求
     * @param  integer $after_market_doc_id 官网售后申请单号ID
     * @param  string  $cancel_reason       取消售后的原因
     * @return array
     * Note:其实就是截单操作 最终将售后直接关闭
     */
    public static function sendCancelWorkOrderToOms($after_market_doc_id=0,$cancel_reason=''){

        $rst['code'] = 1 ;
        $rst['msg'] = '';

        //step1 创建取消工单的数据
        $send_data = self::returnCancelWorkOrderOmsData($after_market_doc_id,$cancel_reason);
        if($send_data) {
            //step2 向中台发送请求
            $cancel_asm_order_url = self::getCancelAsmOrderUrl();

            $oms_return_info = self::sendDataByCurl($cancel_asm_order_url, $send_data, 'json');

            //step3 判断返回的是否为json字符串
            $oms_return_arr = json_decode($oms_return_info, true);
            $is_return_json = json_last_error() == JSON_ERROR_NONE;
            if (!$is_return_json) {

                $rst['code'] = '400024';
                return $rst;
            }

            //step4 返回
            $doc_obj = new AfterMarketDoc();
            $oms_return_code = $oms_return_arr['code'];
            if ($oms_return_code != '0000') {
                $rst['code'] = $oms_return_code;
                $rst['msg'] = $oms_return_arr['msg'];

                //step4.1 需要去更新售后表信息 专门针对是否已经进行过取消操作
                $doc_obj->baseUpdate('sdb_after_market_doc',['is_user_send_cancel'=>'YES'],'id=:id',[':id'=>$after_market_doc_id]);

                return $rst;
            }

            //step5 官网执行取消售后动作之后的数据库相关操作
            //售后日志

            $doc_where = ['id' => $after_market_doc_id];
            $doc_data = $doc_obj->getRowInfoByWhere($doc_where);

            if ($doc_data['doc_log']) {
                $doc_log = @unserialize($doc_data['doc_log']);
            }
            if (!is_array($doc_log)) {
                $doc_log = array();
            }
            $doc_log[count($doc_log)] = array('time' => date('Y-m-d H:i:s'), 'type' => '后台管理员确认取消售后', 'operater' => '后台');
            $doc_log = serialize($doc_log);
            $doc_update_data = ['doc_log' => $doc_log];
            $doc_obj->baseUpdate('sdb_after_market_doc', $doc_update_data, 'id=:id', [":id" => $after_market_doc_id]);


            //step6获取当前售后对应的售后工单信息
            $log_obj = new SendOmsAfterMarketLog();
            $log_where_arr = ['after_market_doc_id' => $after_market_doc_id, 'status' => 'unlock'];
            $omsLogInfo = $log_obj->getRowInfoByWhere($log_where_arr);

            if (!$omsLogInfo) {
                $rst['code'] = '400033';
                return $rst;
            }

            //step7 直接处理售后完成和关闭2个流程
            $asmOrderNo = $omsLogInfo['asmOrderNo'];
            $originalOrderNo = $omsLogInfo['order_no'];


            //step8 添加取消售后单的信息
            $order_obj = new Order();
            $order_where_arr = ['order_no' => $originalOrderNo];
            $order_info = $order_obj->getRowInfoByWhere($order_where_arr);

            $create_cancel_rst = self::createCancelAfterMarketLog($doc_data, $order_info, $asmOrderNo);
            if (!$create_cancel_rst) {
                $rst['code'] = '400034';
                return $rst;
            }

            //step9 加入售后订单日志
            $log_data = array(
                'act' => 'aftermarket_audit',
                'act_name' => '售后单管理',
                'act_type' => '取消售后单',
                'act_content' => '审核售后申请单:' . $doc_data['id'],
                'order_id' => $doc_data['order_id'],
                'order_no' => $doc_data['order_no'],
                'note' => '',
                'content' => '',
                'aftermarket_id' => $doc_data['id'],
                'note' => '审核结果:审核通过<br/>' . '审核意见:' . $cancel_reason,
            );
            $log_aftermarket_obj = new LogAftermarket();
            $log_aftermarket_obj->baseInsert('sdb_log_aftermarket', $log_data);

            $rst['data'] = array('data_detail' => $doc_data, 'asmOrderNo' => $asmOrderNo, 'originalOrderNo' => $originalOrderNo);
        }else{

            //直接将售后设置为关闭
            $doc_obj = new AfterMarketDoc();
            $doc_obj->baseUpdate('sdb_after_market_doc',['audit_status'=>2,'dispose_status'=>2,'dispose_time'=>date('Y-m-d H:i:s')],'id=:id',[':id'=>$after_market_doc_id]);

            //同时变更order_goods_single表信息
            $doc_obj->baseUpdate('sdb_order_goods_single',['after_market_status'=>0,'amd_id'=>0],'amd_id=:amd_id',[':amd_id'=>$after_market_doc_id]);


        }
        return $rst ;
    }

    /**
     * 获取符合条件的任务信息
     * @return array
     */
    public static function getTasksFromTradeIn(){

        $task_info = array();

        $task_obj = new  TradeInAsmTask();
        $task_where['status'] = "SENDING";
        $task_where['service_key'] = "send_aftermarket_to_asm";
        $task_return_field = '*';
        $info = $task_obj->getListInfoByWhere($task_where,$task_return_field);

        if($info){
            foreach($info as $v){

                //获取已经发送过的任务信息
                $log_obj = new TradeInAsmTaskLog();
                $log_where['task_id'] = $v['id'];
                $log_return_field = ' count(1) as send_num,max(create_time) as create_time ';
                $task_log_params['fields'] =  ' count(1) as send_num,max(create_time) as create_time ';
                $task_log_params['cond'] = 'task_id =:task_id';
                $task_log_params['args'] = [':task_id'=>$v['id']];
                $count_info = $log_obj->findOneByWhere('sdb_trade_in_asm_task_log',$task_log_params);

                //设置不同的发送请求的频率
                $send_num = isset($count_info['send_num'])?$count_info['send_num']:0 ;
                $last_create_time = isset($count_info['create_time'])?$count_info['create_time']: '0000-00-00 00:00:00' ;
                $ext_time = time()-strtotime($last_create_time) ;
                if($send_num>10 && $send_num <=20){
                    if($ext_time < 1800){
                        continue ;
                    }
                }
                if($send_num >20){
                    //if($ext_time < 3600){
                        continue ;
                    //}
                }

                //设置返回结果
                $params = unserialize($v['params']);
                if($params){

                    $after_market_doc_id = isset($v['after_market_doc_id'])?$v['after_market_doc_id']:0;
                    $postData  = isset($params['postData'])?$params['postData']:array();
                    $userInfo  = isset($params['userInfo'])?$params['userInfo']:array();
                    $task_info[] = array(
                        'after_market_doc_id'=>$after_market_doc_id,
                        'postData'=>$postData,
                        'userInfo'=>$userInfo,
                        'id'=>$v['id'],
                    );

                }
            }
        }

        return $task_info ;
    }


    /**
     * 发送售后申请工单数据给手中心
     * @param  int   $after_market_doc_id 官网售后单号ID
     * @return array array('code'=>1,'msg'=>'');
     * Note: oms 返回的数据格式
     * {"code":"0000","msg":"操作成功","data":{"asmOrderNo":"701466390703933004006","originalOrderNo":"118143003026"}}
     */
    public static function sendApplyDataToAsmFromTradeIn($after_market_doc_id){

        $rst['code'] = 1 ;
        $rst['msg'] = '';

        //step1 新增trade_in_send_oms_after_market_log记录
        $log_id = self::createOmsAfterMarketLogFromTradeIn($after_market_doc_id,'send');
        if(!$log_id){
            //创建失败
            $rst['code'] = '400023';
            return $rst ;
        }


        //step2 发送给中台的数据
        $send_data = self::returnApplyWorkOrderOmsDataFromTradeIn($after_market_doc_id);
        $create_asm_order_url = self::getCreateAsmOrderUrl();
        $oms_return_info = self::sendDataByCurl($create_asm_order_url,$send_data,'json');

        $oms_return_arr = json_decode($oms_return_info,true);
        $is_return_json = 	json_last_error() == JSON_ERROR_NONE ;
        if(!$is_return_json){

            $rst['code'] = '400024';
            return $rst ;
        }

        //step3 售后中心返回值判断
        $oms_return_code = $oms_return_arr['code'];
        if($oms_return_code !='0000'){
            $rst['code'] = $oms_return_code ;
            $rst['msg'] = $oms_return_arr['msg'];
            return $rst ;
        }

        //step4 更新send_oms_after_market_log 中台回调字段信息
        $oms_data = $oms_return_arr['data'] ;
        $asmOrderNo = $oms_data['asmOrderNo'];//中台创建的工单号
        $send_oms_after_market_log_obj = new TradeInSendOmsAfterMarketLog();
        $log_update_data['status'] = 'unlock';
        $log_update_data['asmOrderNo'] = $asmOrderNo;
        $log_update_data['update_time'] = date('Y-m-d H:i:s');
        $log_where_str = ' id=:id';
        $log_where_arr[":id"] = $log_id;
        $send_oms_after_market_log_obj->baseUpdate('sdb_trade_in_send_oms_after_market_log',$log_update_data,$log_where_str,$log_where_arr);


        return $rst ;

    }

    /**
     * 发送以旧换新的售后任务
     */
    public  static function doSendFromTradeIn(){

        //获取售后任务信息
        $task_list = self::getTasksFromTradeIn();

        if($task_list){

            $task_log_obj = new TradeInAsmTaskLog();

            $audit_idea			='前台用户申请售后单！';

            foreach($task_list as $v){

                $after_market_doc_id = $v['after_market_doc_id'];

                //step1 将售后信息发往售后中心 并更新相关表数据
                $send_rst = self::sendApplyDataToAsmFromTradeIn($after_market_doc_id);

                $trade_in_doc_params['cond'] = 'id = :id';
                $trade_in_doc_params['args'] = [':id'=>$after_market_doc_id];
                $trade_in_doc_info = $task_log_obj->findOneByWhere('sdb_trade_in_after_market_doc',$trade_in_doc_params);
                $trade_in_apply_id = isset($trade_in_doc_info['trade_in_apply_id'])?$trade_in_doc_info['trade_in_apply_id']:0;

                if($send_rst['code']!=1){

                    //添加错误日志
                    $task_log_obj->addSendLog($v['id'],$send_rst);
                    //更新已经换进申请表信息
                    $admin_note = $send_rst['msg']?$send_rst['msg']:getErrorDictMsg($send_rst['code']);
                    $trade_in_apply_update_data['admin_note'] = $admin_note;
                    $trade_in_apply_update_data['aftermarket_status'] = 'FAIL';
                    $trade_in_apply_where_str = 'id=:id';
                    $trade_in_apply_where_arr = [':id'=>$trade_in_apply_id];
                    $task_log_obj->baseUpdate('sdb_trade_in_apply',$trade_in_apply_update_data,$trade_in_apply_where_str,$trade_in_apply_where_arr);
                    continue;
                }else{

                    $trade_in_apply_update_data['admin_note'] = '';
                    $trade_in_apply_update_data['aftermarket_status'] = 'SUCCESS';
                    $trade_in_apply_where_str = 'id=:id';
                    $trade_in_apply_where_arr = [':id'=>$trade_in_apply_id];
                    $task_log_obj->baseUpdate('sdb_trade_in_apply',$trade_in_apply_update_data,$trade_in_apply_where_str,$trade_in_apply_where_arr);
                }

                //step2 更新售后表信息和订单商品表相关信息
                $after_market_doc_obj = new TradeInAfterMarketDoc();
                $aftermarketRst = $after_market_doc_obj->dealPassedAftermarketDoc($after_market_doc_id,$audit_idea);


                //step3更新任务日志信息
                $asm_task_obj = new AsmTask();
                $asm_task_update_data['status'] = 'SUCCESS';
                $asm_task_update_data['update_time'] = date('Y-m-d H:i:s');
                $asm_task_where_str = 'id=:id';
                $asm_task_where_arr[':id'] = $v['id'];

                $asm_task_obj->baseUpdate('sdb_trade_in_asm_task',$asm_task_update_data,$asm_task_where_str,$asm_task_where_arr);

                //step4 增加任务日志信息
                $ask_task_log_obj = new AsmTaskLog();
                $log_add_data['task_id'] = $v['id'];
                $log_add_data['task_type'] = 'send_aftermarket_to_asm';
                $log_add_data['create_time'] = date('Y-m-d H:i:s');
                $log_add_data['return_message'] = serialize($aftermarketRst);
                $ask_task_log_obj->baseInsert('sdb_trade_in_asm_task_log',$log_add_data);


            }
        }

    }

    /**
     * 返回以旧换新售后类型的联系信息
     * @param   $doc_info  售后单信息
     * @return  string
     */
    private static function getTradeInContactInfo($doc_info){
        $rst = '';

        if($doc_info){

            $contact = isset($doc_info['contact'])?$doc_info['contact']:'';
            $mobile = isset($doc_info['mobile'])?$doc_info['mobile']:'';

            $province = isset($doc_info['province'])?$doc_info['province']:'';
            $city = isset($doc_info['city'])?$doc_info['city']:'';
            $area = isset($doc_info['area'])?$doc_info['area']:'';
            $area_obj = new Areas();
            $province = $area_obj->getAreaName($province);
            $city = $area_obj->getAreaName($city);
            $area = $area_obj->getAreaName($area);

            $address = isset($doc_info['address'])?$doc_info['address']:'';
            $address = $province.$city.$area.$address;

            $rst .= '申请人姓名：'.$contact.'，';
            $rst .= '申请人手机：'.$mobile.'，';
            $rst .= '申请人地址：'.$address;
        }

        return $rst ;
    }


}
