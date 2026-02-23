<?php
/**
 * 售后公用方法
 * author
 */
namespace backend\components;

use common\models\AfterMarketDocInfo;
use Yii ;
use yii\base\Exception;
use yii\db\Expression;

use common\models\Areas;
use common\models\Order;
use common\models\AsmTask;
use common\models\AsmTaskLog;
use common\models\AfterMarketDoc;
use common\models\Member;
use common\models\TradeIn;
use common\models\PushTask;
use common\models\Repair;
use common\models\TaskWorkOrderLog;
use common\models\TaskWorkOrderCallbackLog;



class PushTaskCommon
{
    //在线报修 中台对应官网的商铺编码
    protected $shopNo = '0003008500';
    //普通售后 中台对应官网的商铺编码
    protected $asmShopNo = '0003008500';

    //售后类型对应中的String 信息
    public $type_arr = array(
        0=>'',
        1=>'REFUND',//退款 对订单中单条order_goods表中的记录进行处理  如下如 只能退数量 1或者2个
        //2=>'RECEIVE',//退货
        2=>'RECEIVE_REFUND',
        3=>'EXCHANGE',//换货
        4=>'REPAIR',//维修
        5=>'REFUND',//退差价 通过具体的属性来进行区分 退款和退差价
        6=>'OLD_FOR_NEW_SERVICE',//以旧换新
    );

    //获取售后类型的中文释义
    protected function getAftermarketTypeStr($type,$after_market_info){
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

    //获取售后类型的中文释义
    protected function getAftermarketProblemTypeStr($type,$after_market_info){
        $rst = '';

        if($type =='REFUND'){
            $rst = '订单退款';
        }

        if($type =='RECEIVE_REFUND'){
            $rst = '用户体验';
        }

        if($type =='EXCHANGE'){
            $rst = '故障';
        }

        if($type =='REPAIR'){
            $rst = '故障';
        }

        if($type =='REFUND' && $after_market_info['after_market_type']==5){
            $rst = '订单退款';
        }

        return $rst ;
    }

    /**
     * 获取售后工单的服务域名
     * @return string
     */
    public function getAsmUrl(){
        $asm_url = ASM_API_URL;
        $rst = $asm_url?$asm_url:'http://qas-asm1.ecovacs.cn/' ;

        return $rst ;
    }

    /**
     * 获取工单回调地址
     * @return string
     */
    public function getWorkOrderCallbackUrl(){

        $channel_url = SHOP_ADMIN_URL ;
        $callBackNotifyUrl = $channel_url.'/ec-callback/deal-aftermarket';//工单关闭回调地址
        return $callBackNotifyUrl ;
    }

    /**
     * 获取工单回调地址
     * @return string
     */
    public function getWorkOrderCallbackUrlFromTradeIn(){

        $channel_url = SHOP_ADMIN_URL ;
        $callBackNotifyUrl = $channel_url.'/ec-callback/deal-trade-in-aftermarket';//工单关闭回调地址
        return $callBackNotifyUrl ;
    }
    
    /**
     * 获取维修工单回调地址
     * @return string
     */
    public function getWorkOrderCallbackUrlFromRepair(){
        return SHOP_ADMIN_URL.'/ec-callback/deal-repair-aftermarket';
    }

    /**
     * 获取创建工单地址
     * @return string
     */
    public function getCreateAsmOrderUrl(){

        $asm_url =  self::getAsmUrl();
        $create_asm_order_url = $asm_url.'api/order/createAsmOrder';//创建工单地址
        return $create_asm_order_url;
    }

    /**
     * 获取创建工单地址
     * @return string
     */
    public function getCancelAsmOrderUrl(){

        $asm_url =  self::getAsmUrl();
        $cancel_asm_order_url = $asm_url.'api/order/cancelAsmOrder';//取消工单地址
        return $cancel_asm_order_url;
    }

    /**
     * 获取新版售后回调地址
     * @return string
     */
    public function getWorkOrderCallbackUrlForNew(){
        return SHOP_ADMIN_URL.'/ec-callback/deal-aftermarket';
    }

    /**
     * 推送的地址
     * @return string
     */
    public function getPushOrderUrl(){
        $url = EC_API_URL.'/api/pipe/syncWebsiteOrder';
        return $url;
    }

    /**
     * 推送门店订单的地址
     * @return string
     */
    public function getPushStoreOrderUrl(){
        $url = STORE_API_URL.'/api/order/sync';
        return $url;
    }

    /**
     * 推送门店商品的地址
     * @return string
     */
    public function getPushStoreGoodUrl(){
        $url = STORE_API_URL.'/api/product/sync';
        return $url;
    }

    /**
     * 推送通知需要地址更新的地址
     * @return string
     */
    public function getPushAddressNoticeUpdateUrl(){
        $url = STORE_API_URL.'/api/area/sync';
        return $url;
    }

    /**
     * //格式化POST信息
     * @param array $post
     * @param int $post_file
     * @return array|string
     */
    public function formatCurlPost($post=array(),$post_file=0){
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
     * 维修创建 发往中台的唯一标识符
     * @param  array  $after_market_info 售后信息
     * @return string
     */
    public function createValidateCodeFromRepair($after_market_info){
        return md5($after_market_info['id'].$after_market_info['repair_no']);
    }
    
    /**
     * 创建 以旧换新唯一标识符
     * @param  array   $after_market_doc_info  官网订单信息
     * @return string
     */
    public function createValidateCodeFromTradeIn($after_market_doc_info = array()){
        $rst = '';
        if($after_market_doc_info ){
            $rst = md5($after_market_doc_info['id'].$after_market_doc_info['delivery_code']);
        }
        return $rst ;
    }

    /**
     * 普通售后 发往中台的唯一标识符
     * @param  array  $after_market_info 售后信息
     * @return string
     */
    public function createValidateCode($after_market_info){
        $rst = '';
        if($after_market_info){
            $after_market_doc_id = $after_market_info['id'];
            $order_no = $after_market_info['order_no'];
            $rst = md5($after_market_doc_id.$order_no);
        }
        return $rst ;


    }

    /**
     * 发送售后单到售后中心(公用)
     * @param  array $request_data 请求信息
     * @param  array $push_data    推送数据
     * @return array
     */
    public function pushDataToAsmCommon($request_data, $push_data){
    
        // 推送任务ID（sdb_push_task 的 主键 id）
        $push_task_id = $request_data['push_task_id'];
    
        $now_time = date("Y-m-d H:i:s");
    
        // 推送任务日志
        $task_work_order_data = [
            'push_task_id' => $push_task_id,
            'request_data' => json_encode($push_data),
            'response_data' => '',
            'create_time' => $now_time,
            'modify_time' => $now_time,
        ];
        $task_work_order_log_model = new TaskWorkOrderLog();
        $task_work_order_log_id = $task_work_order_log_model->saveInfo($task_work_order_data);
        if(!$task_work_order_log_id){
            return [];
        }

        // 推送url
        $create_asm_order_url = $request_data['push_url'];

        // 执行推送
        $response_data = curlGo($create_asm_order_url,$push_data,false,null,'json');

        // 保存返回信息
        $task_work_order_log_model->updateInfo($task_work_order_log_id, ['response_data' => $response_data]);
        $response_data = json_decode($response_data,true);
        $is_return_json = 	json_last_error() == JSON_ERROR_NONE ;
        if(!$is_return_json){
            // json 编码错误
            return [];
        }

        // 判断返回值
        $oms_return_code = $response_data['code'];
        if($oms_return_code !='0000'){

            $is_trade_in = isset($request_data['is_trade_in'])?$request_data['is_trade_in']:false;
            
            if(!$is_trade_in){
                // 更新任务为失败
                $push_task_model = new PushTask();
                $push_task_model->updateInfo($push_task_id, ['status' => 'FAILED', 'modify_time' => $now_time]);
            }


            // 售后返回失败
            $log_data['request_data'] = $request_data;
            $log_data['push_data'] = $push_data;
            $log_data['response_data'] = $response_data;
            Yii::$app->CommonLogger->logErrorConsole("售后返回失败：".json_encode($log_data), 'aftermarket');
            return [];
        }
        
        return $response_data;
    }
    
    /**
     * 根据售后类型格式化发往中台的额外数据
     * @param  array  $rst               最终返回的数组信息
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单表信息
     * @param  string $repair_type       维修类型(online-在线保修，common-普通维修) 专门正对REPAIR类型进行判断的
     * @return array
     */
    public function formateOmsDataByAftermarketType($rst=array(),$after_market_info=array(),$order_info=array(),$repair_type=''){
        
        $orderType = isset($rst['orderType'])?$rst['orderType']:'' ;
        
        if($orderType){
            if($orderType =='REFUND'){
                //退款 或 退差价
                $rst['refundOrderPropertiesDTO'] = $this->getRefundOmsData($after_market_info,$order_info);
            }else if($orderType =='RECEIVE'){
                //退货
                $rst['receiveOrderPropertiesDTO'] = $this->getReceiveOmsData($after_market_info,$order_info);
            }else if($orderType =='RECEIVE_REFUND'){
                //退货 相当于中台的退货退款
                $rst['receiveRefundOrderPropertiesDTO'] = $this->getReceiveRefundOmsData($after_market_info,$order_info);
            }else if($orderType =='EXCHANGE'){
                //换货
                $rst['exchangeOrderPropertiesDTO'] = $this->getExchangeOmsData($after_market_info,$order_info);
            }else if($orderType =='REPAIR'){
                //维修
                $rst['repairOrderPropertiesDTO'] = $this->getRepairOmsData($after_market_info,$order_info);
                if($repair_type =='online'){
                    // 商品信息
                    $rst['asmOrderProductItemList'] =  $this->getAsmOrderProductItemListFromRepair($after_market_info);
                }
            }else if($orderType =='OLD_FOR_NEW_SERVICE'){
                $rst['oldForNewOrderPropertiesDTO'] = $this->getTradeInOmsData($after_market_info);
                $rst['asmOrderProductItemList'] =  $this->getAsmOrderProductItemListFromTradeIn($after_market_info);
            }
        }
        return $rst  ;
    }
    
    /**
     * 获取退款工单 中台所需要的信息
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        官网订单信息
     * @return array
     */
    private function getRefundOmsData($after_market_info=array(),$order_info=array()){
        
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
            
            if($after_market_info['original_road_refund']=='ALL'){
                $refundOrderPropertiesDTO['payType'] ='background_refund';//中台支付方式、
            }else{
                $refundOrderPropertiesDTO['payType'] =$after_market_info['payment_class_name'];//中台支付方式、
            }
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
    private function getReceiveOmsData($after_market_info=array(),$order_info=array()){
        
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
    private function getReceiveRefundOmsData($after_market_info=array(),$order_info=array()){
        
        $rst = array();
        if($after_market_info && $order_info){
            
            //step1  需要计算均摊值  ===>直接变更为用户填写
            $refundTotal = $after_market_info['refund_price'];
            
            //step2 退款的方式 银行信息等
            //$rst['payType'] =$after_market_info['payment_class_name'];//中台支付方式、
            if($after_market_info['original_road_refund']=='ALL'){
                $rst['payType'] ='background_refund';//中台支付方式、
            }else{
                $rst['payType'] =$after_market_info['payment_class_name'];//中台支付方式、
            }
            
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
    private function getExchangeOmsData($after_market_info=array(),$order_info=array()){
        
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
    private function getTradeInOmsData($after_market_info){
        
        
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
     * 获取以旧换新中台需要的商品列表信息
     * @param  array  $after_market_info [description]
     * @return array
     */
    private function getAsmOrderProductItemListFromTradeIn($after_market_info=array()){
        $rst = array();
        if($after_market_info ){
    
            $productName = $after_market_info['apply_goods_name'];
            $materialNo  = $after_market_info['apply_materiel_no'];
            $productSn   = $after_market_info['sn'];
            $rst[] = array(
                'productName' => $productName,
                'materialNo'  => $materialNo,
                'productSn'   => $productSn,
            );

        }
        return $rst ;
    }
    
    /**
     * 获取维修需要的商品列表信息
     * @param  array  $after_market_info [description]
     * @return array
     */
    private function getAsmOrderProductItemListFromRepair($after_market_info){
        if(!empty($after_market_info['material_no'])){
            return [['productName' => $after_market_info['product_model'],'materialNo' => $after_market_info['material_no']]];
        }else{
            return [];
        }
        
    }
    
    /**
     * 申请维修时 返回给中台的数据
     * @param  array  $after_market_info 官网售后单信息
     * @param  array  $order_info        订单信息
     * @return array
     *
     */
    private function getRepairOmsData($after_market_info, $order_info = []){
        
        // 省市区信息
        $area_obj = new Areas();
        $province = $area_obj->getAreaName($after_market_info['province']);
        $city     = $area_obj->getAreaName($after_market_info['city']);
        $area     = $area_obj->getAreaName($after_market_info['area']);
        
        $rst['transportCompany'] = '';                                 // 快递公司
        $rst['transportNumber']  = '';                                 // 快递单号
        $rst['consigneeAccount'] = $after_market_info['username'];     // 寄件账号
        $rst['consigneePhone']   = '';                                 // 寄件人电话
        $rst['consigneeMobile']  = '';                                 // 寄件人手机
        $rst['receiverName']     = $after_market_info['buy_realname']; // 收货人姓名
        $rst['receiverPhone']    = '';                                 // 收货人电话
        $rst['receiverMobile']   = $after_market_info['mobile'];       // 收货人手机
        $rst['receiverProvince'] = $province;                          // 收货人省
        $rst['receiverCity']     = $city;                              // 收货人市
        $rst['receiverDistrict'] = $area;                              // 收货人区
        $rst['receiverAddress']  = $after_market_info['address'];      // 收货人详细地址
        $rst['receiverZip'] = '';
        
        return $rst ;
    }

    /**
     * 获取发送中台的图片的url地址
     * @param  array  $after_market_info 官网售后单信息
     * @return array
     */
    public function formateOmsFileInfo($rst=array(),$after_market_info=array()){
        if($after_market_info){
            $img_url = $after_market_info['img_url'];
            $info = unserialize($img_url) ;

            //读取是否有上传物流凭证
            if(in_array($after_market_info['after_market_type'],array(2,3,4))){
                $model = new AfterMarketDocInfo();
                $doc_info_info_params['cond'] = ' after_market_doc_id = :after_market_doc_id';
                $doc_info_info_params['args'] = [':after_market_doc_id'=>$after_market_info['id']];
                $doc_info_info  = $model->findOneByWhere('sdb_after_market_doc_info',$doc_info_info_params);
                if($doc_info_info && $doc_info_info['evidence_url']){
                    $info[] = $doc_info_info['evidence_url'] ;
                }
            }

            // 必须是数组不为空
            if($info && is_array($info) && !empty($info)){
                $static_url = BACKEND_PRIVATE_STATIC_URL . "/";
                foreach($info as $img_url){
                    if($img_url){
                        $arr  = explode('/', $img_url);
                        $fileName = $arr[count($arr)-1];

                        $filePath = $static_url . $img_url . "?user_id=" . $after_market_info["user_id"];
                        $temp_arr = array('fileName'=>$fileName,'filePath'=>$filePath);
                        $rst['fileItemDTOList'][] = $temp_arr ;
                    }
                }
            }

        }


        return $rst ;
    }


    /**
     * 推送数据到Ec(公用)
     * @param  array $request_data 请求信息
     * @param  array $push_data    推送数据
     * @return array
     */
    public function pushDataToEcCommon($request_data, $push_data){

        // 推送任务ID（sdb_push_task 的 主键 id）
        $push_task_id = $request_data['push_task_id'];

        $now_time = date("Y-m-d H:i:s");


        // 推送任务日志
        $task_work_order_data = [
            'push_task_id' => $push_task_id,
            'request_data' => '',
            'response_data' => '',
            'create_time' => $now_time,
            'modify_time' => $now_time,
        ];
        $task_work_order_log_model = new TaskWorkOrderLog();
        $task_work_order_log_id = $task_work_order_log_model->saveInfo($task_work_order_data);
        if(!$task_work_order_log_id){
            return [];
        }

        // 目录
        $dir = '/runtime/console_log/task_work_order_log/'.date("Y/m/d").'/';

        // 文件路径
        $file_path = $dir.$task_work_order_log_id.'.txt';

        // 文件目录
        $file_dir = Yii::getAlias('@console').$dir;
        if(!is_dir($file_dir)){
            // 创建目录
            mkdir($file_dir, 0777, true);
        }

        // 内容写入文件
        file_put_contents(Yii::getAlias('@console').$file_path, json_encode($push_data, JSON_UNESCAPED_UNICODE));


        // 推送url
        $push_url = $request_data['push_url'];
        // 执行推送
        $response_data = curlGo($push_url,$push_data);

        // 保存返回信息
        $task_work_order_log_model->updateInfo($task_work_order_log_id, ['response_data' => $response_data,'request_data'=>$file_path]);

        $response_data = json_decode($response_data,true);
        $is_return_json = 	json_last_error() == JSON_ERROR_NONE ;
        if(!$is_return_json){
            // json 编码错误
            return [];
        }

        // 判断返回值
        $oms_return_code = $response_data['code'];
        if($oms_return_code !='0000'){
            // 更新任务为失败
            $push_task_model = new PushTask();
            $push_task_model->updateInfo($push_task_id, ['status' => 'FAILED','push_url'=>$push_url, 'modify_time' => $now_time]);

            // 订单推送返回失败
            $log_data['request_data'] = $request_data;
            $log_data['push_data'] = $push_data;
            $log_data['response_data'] = $response_data;
            Yii::$app->CommonLogger->logErrorConsole("推送订单信息返回失败：".json_encode($log_data));
            return [];
        }

        return $response_data;
    }


    /**
     * 发送售后单到售后中心(公用)
     * @param  array $request_data 请求信息
     * @param  array $push_data    推送数据
     * @return array
     */
    public function pushDataToNewRetailCommon($request_data, $push_data){

        // 推送任务ID（sdb_push_task 的 主键 id）
        $push_task_id = $request_data['push_task_id'];

        $now_time = date("Y-m-d H:i:s");

        // 推送任务日志
        $task_work_order_data = [
            'push_task_id' => $push_task_id,
            'request_data' => json_encode($push_data),
            'response_data' => '',
            'create_time' => $now_time,
            'modify_time' => $now_time,
        ];
        $task_work_order_log_model = new TaskWorkOrderLog();
        $task_work_order_log_id = $task_work_order_log_model->saveInfo($task_work_order_data);
        if(!$task_work_order_log_id){
            return [];
        }

        // 推送url
        $create_asm_order_url = $request_data['push_url'];

        // 执行推送
        $response_data = curlGo($create_asm_order_url,$push_data,false,null,'json');

        // 保存返回信息
        $task_work_order_log_model->updateInfo($task_work_order_log_id, ['response_data' => $response_data]);
        $response_data = json_decode($response_data,true);
        $is_return_json = 	json_last_error() == JSON_ERROR_NONE ;
        if(!$is_return_json){
            // json 编码错误
            return [];
        }

        // 判断返回值
        $oms_return_code = $response_data['code'];

        if($oms_return_code !='0000'){
            
            // 更新任务为失败
            $push_task_model = new PushTask();
            $push_task_model->updateInfo($push_task_id, ['status' => 'FAILED', 'modify_time' => $now_time]);

            // 同步商品失败
            $log_data['request_data'] = $request_data;
            $log_data['push_data'] = $push_data;
            $log_data['response_data'] = $response_data;
            Yii::$app->CommonLogger->logErrorConsole("新零售店铺商品同步失败：".json_encode($log_data));
            return [];
        }

        return $response_data;
    }


    /**
     * 发送售后单到售后中心(公用)
     * @param  array $request_data 请求信息
     * @param  array $push_data    推送数据
     * @return array
     */
    public function pushDataToNewRetailAreas($request_data, $push_data){

        // 推送任务ID（sdb_push_task 的 主键 id）
        $push_task_id = $request_data['push_task_id'];

        $now_time = date("Y-m-d H:i:s");

        // 推送任务日志
        $task_work_order_data = [
            'push_task_id' => $push_task_id,
            'request_data' => json_encode($push_data),
            'response_data' => '',
            'create_time' => $now_time,
            'modify_time' => $now_time,
        ];
        $task_work_order_log_model = new TaskWorkOrderLog();
        $task_work_order_log_id = $task_work_order_log_model->saveInfo($task_work_order_data);
        if(!$task_work_order_log_id){
            return [];
        }

        // 推送url
        $create_asm_order_url = $request_data['push_url'];

        // 执行推送
        $response_data = curlGo($create_asm_order_url,$push_data,false,null,'json');

        // 保存返回信息
        $task_work_order_log_model->updateInfo($task_work_order_log_id, ['response_data' => $response_data]);
        $response_data = json_decode($response_data,true);
        $is_return_json = 	json_last_error() == JSON_ERROR_NONE ;
        if(!$is_return_json){
            // json 编码错误
            return [];
        }

        // 判断返回值
        $oms_return_code = $response_data['code'];

        if($oms_return_code !='0000'){


            // 更新任务为失败
            $push_task_model = new PushTask();
            $push_task_model->updateInfo($push_task_id, ['status' => 'FAILED', 'modify_time' => $now_time]);

            // 同步商品失败
            $log_data['request_data'] = $request_data;
            $log_data['push_data'] = $push_data;
            $log_data['response_data'] = $response_data;
            Yii::$app->CommonLogger->logErrorConsole("通知店铺更新地址失败：".json_encode($log_data));
            return [];
        }

        return $response_data;
    }


    /**
     * 发送请求到同事圈后台(公用)
     * @param  array $request_data 请求信息
     * @param  array $push_data    推送数据
     * @return array
     */
    public function pushDataToCcCommon($request_data, $push_data){

        // 推送任务ID（sdb_push_task 的 主键 id）
        $push_task_id = $request_data['push_task_id'];

        $now_time = date("Y-m-d H:i:s");

        // 推送任务日志
        $task_work_order_data = [
            'push_task_id' => $push_task_id,
            'request_data' => json_encode($push_data),
            'response_data' => '',
            'create_time' => $now_time,
            'modify_time' => $now_time,
        ];

        $task_work_order_log_model = new TaskWorkOrderLog();
        $task_work_order_log_id = $task_work_order_log_model->saveInfo($task_work_order_data);
        if(!$task_work_order_log_id){
            return [];
        }

        // 推送url
        $create_asm_order_url = $request_data['push_url'];

        // 执行推送
        $response_data = curlGo($create_asm_order_url,$push_data,false,null,'');

        // 保存返回信息
        $task_work_order_log_model->updateInfo($task_work_order_log_id, ['response_data' => $response_data]);
        $response_data = json_decode($response_data,true);
        $is_return_json = 	json_last_error() == JSON_ERROR_NONE ;
        if(!$is_return_json){
            // json 编码错误
            return [];
        }

        // 判断返回值
        $oms_return_code = $response_data['code'];

        if($oms_return_code != 1){

            // 更新任务为失败
            $push_task_model = new PushTask();
            $push_task_model->updateInfo($push_task_id, ['status' => 'FAILED', 'modify_time' => $now_time]);

            // 同步商品失败
            $log_data['request_data'] = $request_data;
            $log_data['push_data'] = $push_data;
            $log_data['response_data'] = $response_data;
            Yii::$app->CommonLogger->logErrorConsole("推送同事圈请求失败：".json_encode($log_data));
            return [];
        }

        return $response_data;
    }
   
}
