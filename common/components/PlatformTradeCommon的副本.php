<?php
namespace common\components;


use common\models\OkexOrder;
use Yii;

// 所有平台交易类的
class PlatformTradeCommon的副本 {

    // 交易不中 USD币本位 USDT 美元本位
    public  $base_coin = 'USDT';

    public static $base_buy_num = 5; //最开始购买数量
    // 盈利比例
    public static $earn_percent = 0.002;
    // 止损比例
    public static $stop_percent = 0.04;

    // 最大的补仓层级
    public static $max_level = 6 ;

    // 补仓间隔
    public static $add_distance = 0.002;

    //最大补仓间隔
    public static $max_add_distance = 0.02;

    //购买类型
    public $buy_num_type = 'FEIBO';

    //购买币种
    public $buy_coin = 'eos' ;

    // 配置交易账户的基础密钥信息
    public $apiKey = '';
    public $apiSecret = '';
    public $passphrase = '';

    // 返回后台管理员用户的ID
    public $admin_user_id = 0 ;

    // 当前的杠杆倍数
    public $leverage = 50 ;

    // 双边判断开始层级
    public $start_twice_level = 4 ;

    /**
     * 返回账户信息
     * @return array
     */
    private function returnConfig(){
        $config=[
            //"apiKey"=>"6a5eac0d-7d7a-4931-bc4b-cdbaeef1d8e9",
            "apiKey"=>$this->apiKey,
            //"apiSecret"=>"94F53D603D5274FB9B98430AAD5D1706",
            "apiSecret"=>$this->apiSecret,
            //"passphrase"=>"a12345678",
            "passphrase"=>$this->passphrase,
        ];

        return $config ;
    }

    /**
     * 返回钉钉机器人推送消息地址
     * @return string
     */
    public function returnDingDingWebhook(){
        return 'https://oapi.dingtalk.com/robot/send?access_token=692e9146bdb392869d7f4aad6b0dba39deaff653b7fac541a431c29a36b99633';
    }

    /**
     * 返回状态说明
     * @return mixed
     */
    public function returnStatusArr(){

        $arr['-2'] = '失败';
        $arr['-1'] = '撤单成功';
        $arr['0'] = '等待成交';
        $arr['1'] = '部分成交';
        $arr['2'] = '完全成交';
        $arr['3'] = '下单中';
        $arr['4'] = '撤单中';
        $arr['6'] = '未完成（等待成交+部分成交）';
        $arr['7'] = '已完成（撤单成功+完全成交）';

        return $arr ;
    }

    /**
     * 返回已完成状态
     * @return array
     */
    public function returnFinalStateArr(){
        //-2:失败 -1:撤单成功 0:等待成交 1:部分成交 2:完全成交 3:下单中 4:撤单中
        $final_state = [-2,-1,2];
        return $final_state ;
    }

    /**
     * 根据币种返回购买数量
     * @param $coin
     * @return int
     */
    public function returnBuyNumByCoin($coin){
        if($coin =='ltc' || $coin =='etc'){
            return  1;
        }else{
            return self::$base_buy_num ;
        }
    }

    /**
     * 返回币种字符串
     * @param $coin
     * @return string
     */
    public function returnInstrumentId($coin){
        $coin = strtoupper($coin);
        $base_coin = $this->base_coin ;
        $instrumentId = $coin."-".$base_coin."-SWAP";
        return $instrumentId ;
    }

    /**
     * 根据币种返回所有表名
     * @param $coin
     * @return array
     */
    public function returnAllTableName($coin =''){

        $all_table_name = ['','sea_usdt_okex_buy_up_order','sea_usdt_okex_buy_down_order','sea_usdt_okex_sell_up_order','sea_usdt_okex_sell_down_order'];

        if($coin=='xrp') {
            $all_table_name = ['', 'sea_usdt_okex_buy_up_order_xrp', 'sea_usdt_okex_buy_down_order_xrp', 'sea_usdt_okex_sell_up_order_xrp', 'sea_usdt_okex_sell_down_order_xrp'];
        }else if($coin =='btc'){
            $all_table_name = ['','sea_usdt_okex_buy_up_order_btc','sea_usdt_okex_buy_down_order_btc','sea_usdt_okex_sell_up_order_btc','sea_usdt_okex_sell_down_order_btc'];
        }else if($coin =='eth'){
            $all_table_name = ['','sea_usdt_okex_buy_up_order_eth','sea_usdt_okex_buy_down_order_eth','sea_usdt_okex_sell_up_order_eth','sea_usdt_okex_sell_down_order_eth'];
        }else if($coin =='link'){
            $all_table_name = ['','sea_usdt_okex_buy_up_order_link','sea_usdt_okex_buy_down_order_link','sea_usdt_okex_sell_up_order_link','sea_usdt_okex_sell_down_order_link'];
        }else if($coin =='ltc'){
            $all_table_name = ['','sea_usdt_okex_buy_up_order_ltc','sea_usdt_okex_buy_down_order_ltc','sea_usdt_okex_sell_up_order_ltc','sea_usdt_okex_sell_down_order_ltc'];
        }else if($coin =='etc'){
            $all_table_name = ['','sea_usdt_okex_buy_up_order_etc','sea_usdt_okex_buy_down_order_etc','sea_usdt_okex_sell_up_order_etc','sea_usdt_okex_sell_down_order_etc'];
        }



        return $all_table_name ;
    }

    /**
     * 返回取消购买订单的最低判断时间
     * @return int
     * Note: 主要是针对部分购买的情况由于手数过大，会造成不能完全买入的情况
     */
    public function returnCancelBuyOrderSeconds(){
        return 90 ;
    }

    /**
     * 判断最低的删除订单的时间限制
     * @return int
     */
    public function returnMinDeleteOrderSeconds(){
        return 300 ;
    }

    /**
     * 返回取消订单的最低判断时间
     * @return int
     *
     */
    public function returnCancelOrderSeconds(){
        return 90 ;
    }


    /**
     * 设置类中基础配置信息
     * @param $admin_user_id
     * @param $api_key_info
     * @param $coin
     */
    public function setConfigInfo($admin_user_id,$api_key_info,$coin){

        // 设置管理员信息
        $this->admin_user_id = $admin_user_id ;

        // 配置交易币种信息
        $this->base_coin = strtoupper($api_key_info['type']) ;

        // 配置交易账户的基础密钥信息
        $this->apiKey = $api_key_info['api_key'];
        $this->apiSecret = $api_key_info['api_secret'];
        $this->passphrase = $api_key_info['passphrase'];

        // 配置默认杠杆倍数
        $this->leverage = intval($api_key_info['leverage']) ;

        $this->buy_coin = $coin ;

    }

    /**
     * 获取账单流水
     * @param $coin
     * @param $limit
     * @return mixed
     * Note:查询频率 40次/2次 目前看来意义不大，不能产生实际的作用
     */
    public function queryLedger($coin,$limit =100) {
        // 永续合约
        $coin = strtoupper($coin);
        $instrumentId = $this->returnInstrumentId($coin);
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getLedger($instrumentId,$limit);

        return $res ;
    }


    /**
     * 根据状态获取订单列表
     * @param $coin
     * @param $state
     * @param $limit
     * @return mixed
     * Note:查询频率 10次/2s 目前正好对应9个状态所以不会产生限速的限定
     */
    public function queryOrderList($coin,$state=2,$limit =100) {
        // 永续合约
        $coin = strtoupper($coin);
        $instrumentId = $this->returnInstrumentId($coin) ;
        $obj = new SwapApi($this->returnConfig());

        $res = $obj->getOrderList($state,$instrumentId);

        return $res ;
    }

    /**
     * 返回所有状态的
     * @param $coin
     * @param int $limit
     * @return array
     */
    public function queryAllStatusOrderList($coin,$limit =10){
        $all_status = $this->returnStatusArr() ;
        $res = [];
        foreach($all_status as $status=>$v){
            $temp_res = $this->queryOrderList($coin,$status) ;
            $order_list = isset($temp_res['order_info']) ? $temp_res['order_info'] : [] ;
            if($order_list){
                foreach($order_list as $order_info){
                    $res[] = $order_info ;
                }
            }
        }

        return $res ;
    }

    /**
     * 查询对应币种的订单列表信息
     * @param $coin
     * @param $order_id
     * @return mixed
     */
    public function getOrderInfo($coin,$order_id){

        $obj = new SwapApi($this->returnConfig());
        $instrumentId = $this->returnInstrumentId($coin);
        $order_info = $obj->getOrderInfo($order_id,$instrumentId);
        return $order_info ;
    }

    /**
     * 根据订单类型返回指定的表名
     * @param $type
     * @param $coin
     * @return string
     */
    public function getTableNameByType($type,$coin){
        //1:开多2:开空3:平多4:平空
        $all_table_name = $this->returnAllTableName($coin) ;
        $table_name = isset($all_table_name[$type]) ? $all_table_name[$type] : '';
        return $table_name ;
    }

    /**
     * 执行币种的双边交易
     * @param $type
     * @param $coin
     * @param $api_key_info
     * @param $admin_user_id
     * @return mixed
     */
    public function doTrade($type,$coin,$api_key_info,$admin_user_id){

        // 查询
        $this->setConfigInfo($admin_user_id,$api_key_info,$coin) ;

        $all_table_name = $this->returnAllTableName($coin) ;

        // step1 判断是否允许继续购买
        $check_buy_info = $this->getBuyType($all_table_name);
        $check_buy = $check_buy_info['type'] ;
        if($check_buy =='ALL'){
            return $this->buyAllType($all_table_name);
        }

        $group_id = isset($check_buy_info['group_id']) ?$check_buy_info['group_id'] : 0 ;


        // 判断是否卖出操作
        if($check_buy =='BUY_UP'){
            $scenario = 'BUY_UP';
            // 基础购买数量
            $size = $this->returnBuyNumByCoin($coin);
            $this->doBuyForService('up',$size,$group_id,$scenario);
        }else if($check_buy =='BUY_DOWN'){
            $scenario = 'BUY_DOWN';
            // 基础购买数量
            $size = $this->returnBuyNumByCoin($coin);
            $this->doBuyForService('down',$size,$group_id,$scenario);
        }else if($check_buy =='WAITING'){
            //不要进行任何操作
        }else if($check_buy =='SELL_UP_AND_DOWN'){
            //执行买和卖的策略
            $last_up_order_info = $check_buy_info['last_up_order_info'] ;
            $last_down_order_info = $check_buy_info['last_down_order_info'] ;
            $mark_price = $check_buy_info['mark_price'] ;
            $this->doSellByType('up',$coin,$last_up_order_info,$all_table_name[1],$all_table_name[3],$mark_price);
            $this->doSellByType('down',$coin,$last_down_order_info,$all_table_name[2],$all_table_name[4],$mark_price);
        }

    }

    /**
     * 执行交易操作
     * @param $type
     * @param $coin
     * @param $last_info
     * @param $buy_table_name
     * @param $sell_table_name
     * @param $mark_price 当前市场价
     * @return mixed
     */
    public function doSellByType($type,$coin,$buy_order_info,$buy_table_name,$sell_table_name,$mark_price){

        $sell_order_info = $this->checkIsSell($sell_table_name,$buy_order_info['order_id']) ;
        if($sell_order_info){
            // 查询是否有卖的信息
            return $this->doSell($coin,$type,$buy_order_info,$buy_table_name,$mark_price);
        }

        //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中

        // 此方法体内只会处理buy_order_info['state'] == 2 的情况

        // 如何卖单已经成功不做任何处理

        if($sell_order_info['state'] == 2){

            // 查询买入表的最后一条是不是order_id为0 的记录，不是则新增一条
            $last_buy_order = $this->getLastInfo($buy_table_name,$sell_order_info['coin']);

            if(!$last_buy_order || $last_buy_order['order_id']){
                $model = new OkexOrder();
                $add_data['order_id'] = 0 ;
                $add_data['admin_user_id'] = $this->admin_user_id ;
                $add_data['coin'] = $sell_order_info['coin'] ;
                $add_data['create_time'] = date('Y-m-d H:i:s');
                $add_data['modify_time'] = date('Y-m-d H:i:s');
                $model->baseInsert($buy_table_name,$add_data) ;

                // 通知将购买记录表变更为已删除
                $del_data['is_deleted'] = 'Y';
                $del_data['modify_time'] = date('Y-m-d H:i:s');
                $model->baseUpdate($buy_table_name,$del_data,'group_id=:group_id',[':group_id'=>$buy_order_info['group_id']]) ;

            }

            return true;
        }

        // 实时查询得到当前订单状态
        $service_sell_order_info = $this->getInfoByOrderIdFromService($sell_order_info['order_id'],$sell_order_info['coin']);

        $error_code = isset($service_sell_order_info['error_code'])?$service_sell_order_info['error_code']:0;
        if($error_code && $error_code==35029){

            // 添加日志
            $this->addLog('CANNOT_FIND_ORDER',$buy_order_info['order_id'],$sell_order_info);
            send_dingding_sms_by_webhook('订单信息查询不到，订单号为:'.$sell_order_info['order_id'].'--'.$buy_order_info['order_id'],$this->returnDingDingWebhook());

            //当前取消订单时间过长导致无法进行查询订单状态
            $ext = time()-strtotime($sell_order_info['create_time']) ;
            $min_delete_order_seconds = $this->returnMinDeleteOrderSeconds();
            if($ext > $min_delete_order_seconds){
                $model = new OkexOrder();
                $del_data = ['is_deleted'=>'Y','modify_time'=>date('Y-m-d H:i:s')];
                return $model->baseUpdate($sell_table_name,$del_data,'id=:id',[':id'=>$sell_order_info['id']]);
            }
        }
        $service_sell_order_id = isset($service_sell_order_info['order_id']) ? $service_sell_order_info['order_id'] : 0 ;
        if(!$service_sell_order_id){
            return true ;
        }

        $service_sell_order_state = $service_sell_order_info['state'];
        if(in_array($service_sell_order_state,[-2,-1])){
            //失败和撤单成功的情况下都直接在执行下单操作
            $model = new OkexOrder();
            $sell_update_data['state'] = $service_sell_order_state ;
            $sell_update_data['modify_time'] = date('Y-m-d H:i:s') ;
            $res = $model->baseUpdate($sell_table_name,$sell_update_data,'id=:id',[':id'=>$sell_order_info['id']]) ;
            if(!$res){
                return false ;
            }

            if($service_sell_order_state == -1 && $sell_order_info['is_cancel'] =='Y'){
                // 暂时不需要更新订单状态
                return $this->sellByPart($buy_table_name,$buy_order_info);
            }

            // 再次执行卖出操作
            $coin = $buy_order_info['coin'] ;
            $sell_type = $type;

            return $this->doSell($coin,$sell_type,$buy_order_info,$buy_table_name,$sell_table_name);
        }

        if(in_array($service_sell_order_state,[2,3,4])){
            ////-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
            $model = new OkexOrder();
            $sell_update_data['state'] = $service_sell_order_state ;
            $sell_update_data['modify_time'] = date('Y-m-d H:i:s') ;
            return $model->baseUpdate($sell_table_name,$sell_update_data,'id=:id',[':id'=>$sell_order_info['id']]) ;

        }

        if($service_sell_order_state ==1){
            //执行撤单操作
            //$service_sell_order_id
            $coin = $buy_order_info['coin'] ;
            $this->doBuyCancel($service_sell_order_id,$coin);

            $model = new OkexOrder();
            $sell_update_data['state'] = $service_sell_order_state ;
            $sell_update_data['is_cancel'] = 'Y' ;
            $sell_update_data['modify_time'] = date('Y-m-d H:i:s') ;
            return $model->baseUpdate($sell_table_name,$sell_update_data,'id=:id',[':id'=>$sell_order_info['id']]) ;
        }

        if($service_sell_order_state == 0){

            $ext = time()-strtotime($sell_order_info['create_time']) ;
            $min_cancel_seconds = $this->returnCancelOrderSeconds();
            if($ext >= $min_cancel_seconds){
                //执行撤单操作
                return $this->doSellCancel($sell_order_info['order_id'],$buy_order_info['order_id'],$sell_order_info['coin'],$buy_table_name,$buy_order_info);
            }
        }
    }

    /**
     * 获取购买类型
     * @param $coin
     * @param $all_table_name
     * @return mixed
     */
    public function getBuyType($all_table_name){

        $buy_up_table_name = $all_table_name[1] ;
        $buy_down_table_name = $all_table_name[3];

        // 获取最新的group_id
        $admin_user_id = $this->admin_user_id ;
        $okex_order_model = new OkexOrder() ;
        $group_id =$okex_order_model->getLastedGroupId($admin_user_id,$buy_up_table_name,$buy_down_table_name);
        if(!$group_id){
            return ['type'=>'ALL','group_id'=>0];
        }

        $mark_price = $this->getCoinMarkPriceFromService($this->buy_coin);
        if(!$mark_price){
            return ['type'=>'WAITING','group_id'=>0];
        }
        // 查询两个表的购买数目
        $buy_up_order_list = $okex_order_model->getOrderListByGroupId($admin_user_id,$all_table_name[1],$group_id);
        $buy_down_order_list = $okex_order_model->getOrderListByGroupId($admin_user_id,$all_table_name[3],$group_id);

        $buy_up_avg_price = $this->getAvgPriceByList($buy_up_order_list) ;
        $buy_down_avg_price = $this->getAvgPriceByList($buy_down_order_list) ;
        if(!$buy_up_order_list){

            if($buy_down_avg_price <=$mark_price){
                return ['type'=>'WAITING','group_id'=>$group_id];
            }else{
                return ['type'=>'BUY_UP','group_id'=>$group_id];
            }
        }

        if(!$buy_down_order_list){

            if($mark_price >= $buy_up_avg_price){
                return ['type'=>'WAITING','group_id'=>$group_id];
            }else{
                return ['type'=>'BUY_DOWN','group_id'=>$group_id];
            }

        }

        // 查询最新的订单状态是否是完结
        $last_up_order_info = $buy_up_order_list[0];
        $last_down_order_info = $buy_down_order_list[0];

        $sell_up_table_name = $all_table_name[2] ;
        $sell_down_table_name = $all_table_name[4] ;

        // 根据在线查询状态 ，更新系统状态信息
        $this->updateOrderInfoFromService($last_up_order_info ,$buy_up_table_name) ;
        $this->updateOrderInfoFromService($last_down_order_info ,$buy_down_table_name) ;

        $last_up_order_info = $okex_order_model->getOrderInfoByOrderId($last_up_order_info['order_id'],$buy_up_table_name);
        $last_down_order_info = $okex_order_model->getOrderInfoByOrderId($last_down_order_info['order_id'],$buy_down_table_name);

        // 判断是否有等待中的情况
        //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
        if(in_array($last_up_order_info['state'],[1,3,4]) || in_array($last_down_order_info['state'],[1,3,4])){
            return ['type'=>'WAITING','group_id'=>$group_id];
        }

        $buy_up_level = $last_up_order_info['level'];
        $buy_down_level = $last_down_order_info['level'];

        $start_twice_level = $this->start_twice_level ;
        if($buy_up_level <$start_twice_level && $buy_down_level <$start_twice_level){
            $type = 'SELL_UP_AND_DOWN';
            return compact('type','last_up_order_info','last_down_order_info','mark_price');
        }

        // 获取当前持仓的总收益
        $total_earn_info = $this->getTotalTempEarn($group_id,$buy_up_table_name,$buy_down_table_name,$mark_price);
        $up_total_earn = $total_earn_info['up_total_earn'];
        $down_total_earn = $total_earn_info['down_total_earn'];

        // 增加日志信息
        $this->addLog('GET_TOTAL_EARN_INFO',$group_id,$total_earn_info) ;



        $up_price_avg = $total_earn_info['up_price_avg'];
        $down_price_avg = $total_earn_info['down_price_avg'];
        $up_filled_qty_total = $total_earn_info['up_filled_qty_total'];
        $down_filled_qty_total = $total_earn_info['down_filled_qty_total'];
        if($buy_up_level >=$start_twice_level || $buy_down_level >=$start_twice_level ){

            if($up_total_earn + $down_total_earn  >= 0){
                $type = 'WAITING';
                return compact('type','last_up_order_info','last_down_order_info','mark_price');
            }

            //判断做空是否卖掉
            if($buy_down_order_list){
                $sell_down_order_info = $this->checkIsSell($sell_down_table_name,$buy_down_order_list[0]['order_id']) ;
                if($sell_down_order_info && $sell_down_order_info['is_notice']=='N'){
                    $this->cancelByOrderId($sell_down_order_info['order_id']);
                    $type = 'WAITING';
                    return compact('type','last_up_order_info','last_down_order_info','mark_price');
                }
            }

            //判断做多是否卖掉
            if($buy_up_order_list){
                $sell_up_order_info = $this->checkIsSell($sell_up_table_name,$buy_up_order_list[0]['order_id']) ;
                if($sell_up_order_info && $sell_up_order_info['is_notice']=='N'){
                    $this->cancelByOrderId($sell_up_order_info['order_id']);
                    $type = 'WAITING';
                    return compact('type','last_up_order_info','last_down_order_info','mark_price');
                }
            }


            // 做多
            if($up_price_avg > $mark_price){
                //


            }else{


            }



        }else{


            if($up_total_earn + $down_total_earn  >= 0){
                $type = 'WAITING';
                return compact('type','last_up_order_info','last_down_order_info','mark_price');
            }
        }




    }

    /**
     * 购买双边类型的订单
     * @param $all_table_name
     * @return mixed
     */
    public function buyAllType($all_table_name){

        //创建group_id
        $model = new OkexOrder() ;
        $group_id = $model->createGroupId($all_table_name[1],0);
        $size = $this->returnBuyNumByCoin($this->buy_coin) ;

        // 购买场景
        $scenario = 'BUY_ALL';
        $this->doBuyForService('up',$size,$group_id,$scenario);
        $this->doBuyForService('down',$size,$group_id,$scenario);
        return true ;
    }



    /**
     * @param $coin
     * @param $buy_type
     * @param $size
     * @param $buy_table_name
     * @param $group_id
     * @return mixed
     */
    public function doBuyForService($buy_type,$size,$group_id,$scenario ){

        $coin = $this->buy_coin ;

        $otype = $buy_type == 'up' ? 1:2;
        $order_type = 0; //普通委托
        $match_price = 1 ;
        $obj = new SwapApi($this->returnConfig());
        $client_oid = 0 ;
        $instrumentId = $this->returnInstrumentId($coin);
        $leverage = 50;// 杠杆倍数
        $price = 0 ;
        $res = $obj->takeOrder($client_oid, $instrumentId, $otype, $price, $size, $match_price, $leverage, $order_type);

        $this->addLog($scenario.'_'.strtoupper($buy_type),$group_id,$res);

        $error_code = isset($res['error_code']) ? $res['error_code'] : '-1';
        if(!$error_code){
            $order_id = $res['order_id'] ;

            //  查询订单状态信息
            $order_info = $this->getInfoByOrderIdFromService($order_id,$coin);

            $this->addLog('QUERY_ORDER_'.$scenario.'_'.strtoupper($buy_type),$order_id,$order_info);

            // 新增记录
            $buy_order_id = 0 ;
            return $this->addRowData($coin,$order_info,$buy_order_id,$group_id);

        }
    }

    /**
     * 增加2个新增表的标志位信息
     * @param $coin
     * @return mixed
     */
    public function addMarkRecord($coin){

        // 当前时间
        $now = date('Y-m-d H:i:s');

        $all_table_name = $this->returnAllTableName($coin) ;
        $up_table_name = $all_table_name[1] ;
        $down_table_name = $all_table_name[2] ;

        $model = new OkexOrder();
        // 查询买入和卖出的两张表
        $params['cond'] = 'coin=:coin';
        $params['args'] = [':coin'=>$coin];
        $params['orderby'] = 'id desc';
        $buy_info = $model->findOneByWhere($up_table_name,$params);

        if(!$buy_info || $buy_info['order_id'] !=0){
            // 新增
            $buy_add_data['order_id'] =  0 ;
            $buy_add_data['coin'] =  $coin ;
            $buy_add_data['create_time'] = $now ;
            $buy_add_data['modify_time'] = $now ;
            $model->baseInsert($up_table_name,$buy_add_data);
        }

        $sell_info = $model->findOneByWhere($down_table_name,$params) ;
        if(!$sell_info || $sell_info['order_id'] !=0){
            // 新增
            $sell_add_data['order_id'] =  0 ;
            $sell_add_data['coin'] =  $coin ;
            $sell_add_data['create_time'] = $now ;
            $sell_add_data['modify_time'] = $now ;
            $model->baseInsert($down_table_name,$sell_add_data);
        }
    }

    /**
     * 新增日志
     * @param $type
     * @param $unique_id
     * @param $content
     * @return string
     */
    public function addLog($type,$unique_id,$content){
        $model = new OkexOrder();
        $content = json_encode($content);
        $create_time = date('Y-m-d H:i:s');
        $add_data = compact('type','content','create_time','unique_id');

        // 新增日志
        $log_components = new CommonLogger();
        $log_components->logError("Type:".$type.'========='.json_encode($add_data));


        return $model->baseInsert('sea_okex_log',$add_data);
    }

    /**
     * 获取第三方的订单信息
     * @param $order_id
     * @param $coin
     * @param $table_name
     * @return mixed
     */
    public function getInfoByOrderIdFromService($order_id,$coin){

        $obj = new SwapApi($this->returnConfig());
        $instrumentId = $this->returnInstrumentId($coin) ;
        $res = $obj->getOrderInfo("$order_id",$instrumentId);
        return $res ;
    }


    /**
     * 插入单行信息
     * @param $coin
     * @param $add_data
     * @param $buy_order_id
     * @param $group_id 假如group_id大于就说明是要补仓
     * @return mixed
     */
    public function addRowData($coin,$add_data,$buy_order_id='',$group_id=0){

        $now = date('Y-m-d H:i:s');

        $table_name = $this->getTableNameByType($add_data['type'],$coin) ;

        if($add_data['type'] ==1 || $add_data['type'] == 2){
            $insert_data['group_id'] = $group_id >0 ? $group_id : $this->createGroupId($table_name,$buy_order_id);
            $insert_data['level'] = $this->createLevel($insert_data['group_id'],$table_name);
        }

        //判断是否存在
        $order_id = isset($add_data['order_id']) ? $add_data['order_id'] : 0 ;
        if(!$order_id){
            return true ;
        }

        $model = new  OkexOrder();
        $exists_params['cond'] = 'order_id =:order_id';
        $exists_params['args'] = [':order_id'=>$order_id];
        $exists_info = $model->findOneByWhere($table_name,$exists_params);

        $insert_data['coin'] = $coin ;
        $insert_data['admin_user_id'] = $this->admin_user_id ;
        $insert_data['order_id'] = $add_data['order_id'] ;
        $insert_data['instrument_id'] = $add_data['instrument_id'] ;
        $insert_data['contract_val'] = $add_data['contract_val'] ;
        $insert_data['fee'] = $add_data['fee'] ;
        $insert_data['filled_qty'] = $add_data['filled_qty'] ;
        $insert_data['order_type'] = $add_data['order_type'] ;
        $insert_data['price'] = $add_data['price'] ;
        $insert_data['price_avg'] = $add_data['price_avg'] ;
        $insert_data['size'] = $add_data['size'] ;
        $insert_data['state'] = $add_data['state'] ;
        $insert_data['timestamp'] = $add_data['timestamp'] ;
        $insert_data['trigger_price'] = 0.00 ;
        $insert_data['type'] = $add_data['type'] ;
        $insert_data['modify_time'] = $now ;

        // 必须是买入表才会操作
        if(in_array($add_data['type'],[3,4]) && $buy_order_id ){
            $insert_data['buy_id'] = $buy_order_id ;
        }

        if($exists_info){

            // 判断状态是否已经最终完结 不是最终完结 查询当前状态
            //-2:失败 -1:撤单成功 0:等待成交 1:部分成交 2:完全成交 3:下单中 4:撤单中
            $final_state = $this->returnFinalStateArr();
            if($insert_data['state'] != $exists_info['state'] && !in_array($insert_data['state'],$final_state)){
                //查询状态更新状态
                $model->baseUpdate($table_name,$insert_data,'id=:id',[':id'=>$exists_info['id']]);
            }

        }else{
            $insert_data['create_time'] = $now ;

            $model->baseInsert($table_name,$insert_data);
        }


    }


    /**
     * 返回group_id
     * @param $table_name
     * @param $buy_order_id
     * @return mixed
     * Note:用户后期补仓使用
     */
    public function createGroupId($table_name,$buy_order_id=''){

        $model = new OkexOrder();
        return $model->createGroupId($table_name,$buy_order_id) ;
    }

    /**
     * 创建建单层级
     * @param $group_id
     * @param $table_name
     * @return mixed
     */
    public function createLevel($group_id,$table_name){
        if(!$group_id){
            return  1;
        }
        $model = new OkexOrder();
        $params['cond'] = 'group_id=:group_id AND `state`=:state';
        $params['args'] = [':group_id'=>$group_id,':state'=>2];
        $params['orderby'] = 'create_time desc';
        $info = $model->findOneByWhere($table_name,$params);

        return $info ? $info['level'] + 1 : 1 ;

    }

    /**
     * 通过系统状态返回更新购买表信息
     * @param $buy_order_info
     * @param $buy_table_name
     * @return mixed
     */
    public function updateOrderInfoFromService($buy_order_info ,$buy_table_name){
        //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
        $state = $buy_order_info['state'] ;
        if($state == 2){
            return true ;
        }


        if(in_array($state,[-2,-1])){

            $this->addLog('DELETE_RECORD_'.$buy_table_name,$buy_order_info['order_id'],$buy_order_info);

            // 删除当条记录
            $model = new OkexOrder();
            $update_data['state'] = $state ;
            $update_data['is_deleted'] = 'Y' ;
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            return $model->baseUpdate($buy_table_name,$update_data,'id=:id',[':id'=>$buy_order_info['id']]) ;
        }

        //读取服务端订单的实时状态
        $order_id = $buy_order_info['order_id'] ;
        $coin = $buy_order_info['coin'];
        $service_buy_order_info = $this->getInfoByOrderIdFromService($order_id,$coin);

        if(in_array($service_buy_order_info['state'],[-2,-1,1,2,3,4])){
            //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
            //更新订单表的状态信息
            $model = new OkexOrder();
            $update_data['state']=$service_buy_order_info['state'] ;
            $update_data['price']=$service_buy_order_info['price'] ;
            $update_data['filled_qty']=$service_buy_order_info['filled_qty'] ;//填充购买数量
            $update_data['size']=$service_buy_order_info['filled_qty'] ;//填充购买数量
            $update_data['price_avg']=$service_buy_order_info['price_avg'] ;
            $update_data['modify_time']=date('Y-m-d H:i:s');

            if($service_buy_order_info['state'] == 1){
                // 部分成交 直接执行取消操作
                $ext = time()-strtotime($buy_order_info['create_time']) ;
                if($ext >=$this->returnCancelBuyOrderSeconds()){
                    $this->doBuyCancel($buy_order_info['order_id'],$buy_order_info['coin']) ;
                }

            }

            if($service_buy_order_info['state']== -1 && $buy_order_info['state'] == 1){

                // 由部分执行直接
                $filled_qty = $service_buy_order_info['filled_qty'] ;
                if($filled_qty > 0 ){
                    //查询已经购买数量 大于0 直接变更状态为成功
                    //同时判断是否确认数量大于二分之一 ，大于的话
                    $update_data['state'] = 2;

                    $level = $buy_order_info['level'] - 1 > 0 ? ($buy_order_info['level'] - 1):1 ;
                    $update_data['level'] = $level -1 ;

                }
            }

            return $model->baseUpdate($buy_table_name,$update_data,'id=:id',[':id'=>$buy_order_info['id']]);

        }


    }


    /**
     * 订单下单撤销操作
     * @param $order_id
     * @param $coin
     * @return mixed
     */
    public function doBuyCancel($order_id,$coin){

        $obj = new SwapApi($this->returnConfig());
        $coin = strtoupper($coin);
        $instrumentId = $this->returnInstrumentId($coin);
        return $obj->revokeOrder($instrumentId,$order_id);
    }

    /**
     * 查询是否已经卖出过
     * @param $sell_table_name
     * @param $buy_id
     * @return mixed
     */
    public function checkIsSell($sell_table_name,$buy_id){
        $model = new OkexOrder();

        //0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
        $status_arr = [0,1,2,3,4];
        $params['cond'] = 'buy_id=:buy_id AND is_deleted="N" AND ( state in('.implode(',',$status_arr).') OR ( state="-1" AND is_cancel="Y" ) )';
        $params['args'] = [':buy_id'=>$buy_id];
        return $model->findOneByWhere($sell_table_name,$params);
    }

    /**
     * 获取指定币种的信息
     * @param $coin
     * @return mixed
     */
    public function getCoinMarkPriceFromService($coin){
        $instrumentId = $this->returnInstrumentId($coin) ;
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getMarkPrice($instrumentId);
        return isset($res['mark_price']) ? $res['mark_price'] : 0 ;
    }

    /**
     * 根据购买列表返回平均价格
     * @param $list
     * @return float|int
     */
    public function getAvgPriceByList($list){

        if(!$list){
            return  0  ;
        }

        $total_price = 0 ;
        $total_qty = 0 ;
        foreach($list as $v){
            $total_price += $v['price_avg']*$v['filled_qty'];
            $total_qty +=$v['filled_qty'];
        }

        $price_avg = $total_price/$total_qty ;

        return $price_avg ;
    }

    /**
     * 执行售出操作
     * @param string $coin 币种
     * @param $sell_type 卖出类型
     * @param $buy_order_info 对应购买订单信息
     * @param string $buy_table_name 购买记录表表名
     * @param string $mark_price 卖出记录表表名
     * @return mixed
     */
    public function doSell($coin,$sell_type,$buy_order_info,$buy_table_name,$mark_price){

        // 判断是否需要补仓
        $check_add_extra = $this->checkAddExtra($buy_order_info,$buy_table_name,$mark_price);

        if($check_add_extra){
            // 直接以对手价购买
            $obj = new SwapApi($this->returnConfig());
            $instrumentId = $this->returnInstrumentId($coin);
            $buy_order_id = $buy_order_info['order_id'];
            $otype = $sell_type == 'up' ? 1 : 2 ;
            $client_oid = 0;
            $match_price = 1 ;//以对手价成交
            $order_type = 0 ;

            $sell_price = 0 ;//卖出的价格
            $leverage = 50 ;
            $size = $this->getBuySizeByBuyOrderInfo($buy_order_info);
            $res = $obj->takeOrder($client_oid, $instrumentId, $otype, $sell_price, $size, $match_price, $leverage, $order_type);

            $this->addLog('DO_BUY_EXTRA_ORDER',$buy_order_info['order_id'],$res);
        }else{

            $otype = $sell_type == 'up' ? 3 : 4 ;
            $client_oid = 0 ;
            $instrumentId = $this->returnInstrumentId($coin) ;
            $order_type = 0 ; // 普通委托
            $leverage = 50 ;
            $match_price = 0 ; //非对手价成交

            $buy_order_id = $buy_order_info['order_id'];

            $buy_price = $this->getPriceAvgFromDb($buy_order_info,$buy_table_name);

            if($sell_type == 'up'){
                $sell_price = $buy_price*(1+self::$earn_percent);
            }else{
                $sell_price = $buy_price*(1-self::$earn_percent);
            }

            // 卖出数量
            #TODO是否需要进行补仓操作
            //$size = $buy_order_info['filled_qty'];
            //返回之前购买数量的总和
            $size = $this->getHoldingFromDb($buy_order_info,$buy_table_name);

            $check_stop = $this->checkStop($buy_order_id,$buy_table_name) ;

            if($check_stop){

                //直接止损，直接以对手价进行卖出
                $obj = new SwapApi($this->returnConfig());
                $match_price = 1 ;//以对手价成交
                $order_type = 0 ;
                $res = $obj->takeOrder($client_oid, $instrumentId, $otype, 0, $size, $match_price, $leverage, $order_type);
                $this->addLog('DO_SELL_SELL_ORDER_STOP',$buy_order_id,$res);

            }else{
                $obj = new SwapApi($this->returnConfig());

                $res = $obj->takeOrder($client_oid, $instrumentId, $otype, $sell_price, $size, $match_price, $leverage, $order_type);

                $this->addLog('DO_SELL_SELL_ORDER_NORMAL',$buy_order_id,$res);

            }

        }

        $error_code = isset($res['error_code']) ? $res['error_code'] : '-1';
        if(!$error_code){
            $order_id = $res['order_id'] ;

            //  查询订单状态信息
            $order_info = $this->getInfoByOrderIdFromService($order_id,$coin);

            // 新增记录
            return $this->addRowData($coin,$order_info,$buy_order_id);

        }
    }


    /**
     * 判断是否需要补仓
     * @param $buy_order_info
     * @param $buy_table_name
     * @param $mark_price
     * @return mixed
     */
    public function checkAddExtra($buy_order_info,$buy_table_name,$mark_price=0){

        $group_id = $buy_order_info['group_id'];
        $coin = $buy_order_info['coin'] ;
        $params['cond'] = 'group_id=:group_id AND coin=:coin AND state =2 AND is_deleted="N" ';
        $params['args'] = [':group_id'=>$group_id,':coin'=>$coin];
        $params['orderby'] = 'id DESC';
        $params['fields'] = '*';
        $model = new OkexOrder();
        $list  = $model->findAllByWhere($buy_table_name,$params) ;

        if($list && $list[0]['level'] >= self::$max_level){
            return false ;
        }

        $total_price = 0;
        $total_qty = 0 ;
        foreach($list as $v){
            $total_price += $v['price_avg']*$v['filled_qty'];
            $total_qty +=$v['filled_qty'];
        }

        $price_avg = $total_price/$total_qty ;

        if(!$mark_price){
            //查询当前币种的实时价格
            $mark_price = $this->getCoinMarkPriceFromService($coin);
        }


        // 假如没有没有得到服务器的价格，则不允许进行加仓
        if($mark_price <=0){
            return false ;
        }

        if($buy_order_info['type'] ==1){
            if($mark_price >= $price_avg){
                return false ;
            }else{
                $percent = $price_avg/$mark_price - 1 ;
            }
        }else{

            if($mark_price <= $price_avg){
                return false ;
            }else{
                $percent =  $mark_price/$price_avg - 1;
            }
        }

        if(!$percent){
            return false ;
        }

        $real_add_distance = $this->returnAddDistance($buy_order_info) ;
        if($percent >=$real_add_distance){

            //返回总的购买数量
            return true;
        }

        return false ;

    }

    /**
     * 返回补仓的间隔
     * @param $buy_order_info
     * @return mixed
     */
    private function returnAddDistance($buy_order_info){

        //默认补仓间隔
        $default_distance = self::$add_distance ;
        $level = $buy_order_info['level'];
        if($level <=4){
            //return $default_distance +  0.002*$level ;
            return $default_distance  ;
        }else{
            return self::$max_add_distance ;
        }
    }

    /**
     * 返回购买数量
     * @param $buy_order_info
     * @return mixed
     */
    public function getBuySizeByBuyOrderInfo($buy_order_info){
        $level = $buy_order_info['level'] ;

        $base_buy_num = $this->returnBuyNumByCoin($buy_order_info['coin']);

        return $this->getBuySizeBuyLevel($level,$base_buy_num) ;

    }

    /**
     * 返回最终需要的购买数目
     * @param $level
     * @param $base_buy_num
     * @return float|int
     */
    public function getBuySizeBuyLevel($level,$base_buy_num){
        $type= $this->buy_num_type ;

        if($type =='FEIBO'){
            if($level ==1){
                return 2*$base_buy_num ;
            }else if($level ==2){
                return 3*$base_buy_num ;
            }else if($level ==3){
                return 5*$base_buy_num ;
            }else if($level ==4){
                return 8*$base_buy_num ;
            }else if($level ==5){
                return 13*$base_buy_num ;
            }else if($level ==6){
                return 21*$base_buy_num ;
            }else if($level ==7){
                return 34*$base_buy_num ;
            }else if($level ==8){
                return 55*$base_buy_num ;
            }else if($level ==9){
                return 89*$base_buy_num ;
            }else{
                return 2*$base_buy_num ;
            }
        }

        if($type=='BEISHU'){
            return pow($base_buy_num,$level);
        }
    }


    /**
     * 根据数据查询得到购买的平均价格
     * @param $buy_order_info
     * @param $buy_table_name
     * @return bool|float|
     */
    public function getPriceAvgFromDb($buy_order_info,$buy_table_name){
        $group_id = $buy_order_info['group_id'];
        $coin = $buy_order_info['coin'] ;
        $params['cond'] = 'group_id=:group_id AND coin=:coin AND state =2 AND is_deleted="N"';
        $params['args'] = [':group_id'=>$group_id,':coin'=>$coin];
        $params['fields'] = '*';
        $model = new OkexOrder();
        $list  = $model->findAllByWhere($buy_table_name,$params) ;

        $total_price = 0;
        $total_qty = 0 ;
        foreach($list as $v){
            $total_price += $v['price_avg']*$v['filled_qty'];
            $total_qty +=$v['filled_qty'];
        }

        $price_avg = $total_price/$total_qty ;
        return $price_avg ;
    }

    /**
     * 从数据库中读取已经购买的数量
     * @param $buy_order_info
     * @param $buy_table_name
     * @return int
     */
    public function getHoldingFromDb($buy_order_info,$buy_table_name){

        $group_id = $buy_order_info['group_id'];
        $coin = $buy_order_info['coin'] ;
        $params['cond'] = 'group_id=:group_id AND coin=:coin AND state =2 AND is_deleted="N"';
        $params['args'] = [':group_id'=>$group_id,':coin'=>$coin];
        $params['fields'] = '*';
        $model = new OkexOrder();
        $list  = $model->findAllByWhere($buy_table_name,$params) ;

        $total_price = 0;
        $total_qty = 0 ;
        foreach($list as $v){
            $total_price += $v['price_avg']*$v['filled_qty'];
            $total_qty +=$v['filled_qty'];
        }

        return $total_qty ;
    }

    /**
     * 判断是否已经达到止损或者盈利达到上限的条件
     * @param $buy_order_id
     * @param $buy_table_name
     * @param $sell_table_name
     * @return mixed
     */
    public function checkStop($buy_order_id,$buy_table_name){

        //停止止损
        return false;

        // 查询卖单信息
        $model = new OkexOrder();
        $buy_params['cond'] =  'order_id =:order_id';
        $buy_params['args'] =  [':order_id'=>$buy_order_id];
        $buy_order_info = $model->findOneByWhere($buy_table_name,$buy_params);
        $buy_price = $this->getPriceAvgFromDb($buy_order_info,$buy_table_name) ;

        $coin = $buy_order_info['coin'];
        //查询当前币种的实时价格
        $mark_price = $this->getCoinMarkPriceFromService($coin);
        if(!$mark_price){
            return false ;
        }
        $buy_type = $buy_order_info['type'] ;
        // 先判断是UP /down
        if($buy_type ==1){
            // 当前价格小于mark_price

            if($mark_price >= $buy_price){
                //判断是否超出盈利的理想点位
                $ext_percent = $mark_price/$buy_price -1 ;

                if($ext_percent>=self::$earn_percent){
                    return true ;
                }
                return false ;
            }

            $ext_percent = $buy_price/$mark_price ;

        }else{

            // 当前价格 大于mark_price
            if($mark_price <= $buy_price){

                $ext_percent = $buy_price/$mark_price -1 ;
                if($ext_percent >= self::$earn_percent){
                    return true;
                }
                return false ;
            }

            $ext_percent  = $mark_price/$buy_price ;
        }

        $ext_percent = $ext_percent -1 ;

        if($ext_percent >= self::$stop_percent){
            return true ;
        }

        return false ;
    }


    /**
     * 获取指定币种的最新一条信息
     * @param $table_name
     * @param $coin
     * @return mixed
     */
    public function getLastInfo($table_name,$coin){
        // 获取最新的一条信息
        $model = new OkexOrder();
        $params['cond'] ='coin=:coin AND is_deleted="N" AND admin_user_id=:admin_user_id' ;
        $params['args'] = [':coin'=>$coin,':admin_user_id'=>$this->admin_user_id];
        $params['orderby'] = 'id DESC';
        $info = $model->findOneByWhere($table_name,$params);
        return $info ;
    }


    /**
     * 挂单售出 部分信息
     * @param $buy_table_name
     * @param $buy_order_info
     * @return mixed
     */
    public function sellByPart($buy_table_name,$buy_order_info){

        $res = $this->getPositionFromService($buy_order_info['coin'],$buy_order_info['type']);
        if(!$res){
            return false ;
        }
        $now = date('Y-m-d H:i:s');

        // 模拟插入的过程
        $add_data['coin'] = $buy_order_info['coin'] ;
        $add_data['group_id'] = $buy_order_info['group_id'] + 1;
        $add_data['level'] = $buy_order_info['level'] ;
        $add_data['order_id'] = $buy_order_info['order_id'] +99 ;
        $add_data['instrument_id'] = $buy_order_info['instrument_id'] ;
        $add_data['filled_qty'] = $res['avail_position'] ;
        $add_data['price_avg'] = $res['avg_cost'] ;
        $add_data['contract_val'] = $buy_order_info['contract_val'] ;
        $add_data['fee'] = $buy_order_info['fee'] ;
        $add_data['order_type'] = $buy_order_info['order_type'] ;
        $add_data['price'] = $buy_order_info['price'] ;
        $add_data['size'] = $res['avail_position'] ;
        $add_data['state'] = $buy_order_info['state'] ;
        $add_data['status'] = $buy_order_info['status'] ;
        $add_data['timestamp'] = $buy_order_info['timestamp'] ;
        $add_data['trigger_price'] = $buy_order_info['trigger_price'] ;
        $add_data['type'] = $buy_order_info['type'] ;
        $add_data['create_time'] =$now;
        $add_data['modify_time'] =$now;

        $model = new OkexOrder();
        return $model->baseInsert($buy_table_name,$add_data);

    }

    /**
     * 获取当前币种的持仓信息
     * @param $coin
     * @param $type
     * @return mixed
     */
    public function getPositionFromService($coin,$type){

        $instrumentId = $this->returnInstrumentId($coin) ;
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getSpecificPosition($instrumentId);
        $list = isset($res['holding']) ?$res['holding'] :[];
        if(!$list){
            return [] ;
        }
        $type = $type ==1?'long':'short' ;
        $res = [];
        foreach($list as $v){
            if($v['side'] ==$type){
                $res = $v ;
            }
        }
        return $res ;
    }

    /**
     * 订单平单撤销操作
     * @param $sell_order_id
     * @param $buy_order_id
     * @param $coin
     * @param $buy_table_name
     * @param $buy_order_info
     * @return mixed
     */
    public function doSellCancel($sell_order_id,$buy_order_id,$coin,$buy_table_name,$buy_order_info){

        // 判断是否需要止损
        $check_stop = $this->checkStop($buy_order_id,$buy_table_name) ;

        // 判断是否要加仓
        $check_need_add_extra = $this->checkAddExtra($buy_order_info,$buy_table_name) ;

        if($check_stop || $check_need_add_extra){


            $obj = new SwapApi($this->returnConfig());
            $coin = strtoupper($coin);
            $instrumentId = $this->returnInstrumentId($coin);
            return $obj->revokeOrder($instrumentId,$sell_order_id);
        }

        return true ;
    }

    /**
     * 获取当前盈利信息
     * @param $group_id
     * @param $buy_up_table_name
     * @param $buy_down_table_name
     * @param $mark_price
     * @return mixed
     */
    public function getTotalTempEarn($group_id,$buy_up_table_name,$buy_down_table_name,$mark_price){
        $okex_order_model  = new OkexOrder() ;
        $admin_user_id = $this->admin_user_id ;
        $buy_up_list = $okex_order_model->getOrderListByGroupId($admin_user_id,$buy_up_table_name,$group_id) ;
        $up_total_earn = 0 ;

        $up_price_total = 0 ;
        $up_filled_qty_total = 0 ;
        if($buy_up_list){
            foreach($buy_up_list as $v){
                $price_avg = $v['price_avg'];
                $filled_qty = $v['filled_qty'] ;
                if($price_avg >= $mark_price){
                    $temp_earn = ($price_avg - $mark_price)*$this->leverage*$filled_qty ;

                }else{
                    $temp_earn = ($mark_price - $price_avg)*$this->leverage*$filled_qty ;
                }
                $up_total_earn += $temp_earn ;

                $up_price_total += $price_avg*$filled_qty;
                $up_filled_qty_total += $v['filled_qty'] ;

            }
        }

        $up_price_avg = $up_price_total/$up_filled_qty_total ;

        $buy_down_list= $okex_order_model->getOrderListByGroupId($admin_user_id,$buy_down_table_name,$group_id) ;
        $down_total_earn = 0 ;
        $down_price_total = 0 ;
        $down_filled_qty_total = 0 ;
        foreach($buy_down_list as $v){
            $price_avg = $v['price_avg'];
            $filled_qty = $v['filled_qty'] ;
            if($mark_price >= $price_avg){
                $temp_earn = ($mark_price - $mark_price)*$this->leverage*$filled_qty ;

            }else{
                $temp_earn = ($price_avg - $mark_price)*$this->leverage*$filled_qty ;
            }
            $down_total_earn += $temp_earn ;
            $down_price_total += $price_avg*$filled_qty;
            $down_filled_qty_total += $v['filled_qty'] ;

        }

        $down_price_avg = $down_price_total/$down_filled_qty_total ;

        return compact('up_total_earn','down_total_earn','up_price_avg','down_price_avg','up_filled_qty_total','down_filled_qty_total');
    }

    /**
     * 撤销指定的ID订单
     * @param $sell_order_id
     * @return bool|mixed|string
     */
    public function cancelByOrderId($sell_order_id){
        $obj = new SwapApi($this->returnConfig());
        $coin = strtoupper($this->base_coin);
        $instrumentId = $this->returnInstrumentId($coin);
        return $obj->revokeOrder($instrumentId,$sell_order_id);
    }

}