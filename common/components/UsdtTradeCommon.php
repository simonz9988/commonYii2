<?php
namespace common\components;


use common\models\OkexOrder;
use Yii;

// 交易通用类
class UsdtTradeCommon {

    public static $base_coin = 'USDT';

    public static $base_buy_num = 5; //最开始购买数量
    // 盈利比例
    public static $earn_percent = 0.002;
    // 止损比例
    public static $stop_percent = 0.04;

    // 最大的补仓层级
    public static $max_level = 6 ;

    // 补仓间隔
    public static $add_distance = 0.006;

    //最大补仓间隔
    public static $max_add_distance = 0.02;

    //购买类型
    public $buy_num_type = 'FEIBO';

    /**
     * 返回账户信息
     * @return array
     */
    private function returnConfig(){
        $config=[
            "apiKey"=>"6a5eac0d-7d7a-4931-bc4b-cdbaeef1d8e9",
            "apiSecret"=>"94F53D603D5274FB9B98430AAD5D1706",
            "passphrase"=>"a12345678",
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
        $instrumentId = $coin."-".self::$base_coin."-SWAP";
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
     * 返回取消订单的最低判断时间
     * @return int
     *
     */
    public function returnCancelOrderSeconds(){
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
            $insert_data['group_id'] = $this->createGroupId($table_name,$buy_order_id);
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
     * 插入单行信息
     * @param $coin
     * @param $add_data
     * @param $buy_order_id
     * @return mixed
     */
    public function addTotalRowData($coin,$add_data,$buy_order_id=''){

        $now = date('Y-m-d H:i:s');

        $table_name = 'sea_okex_total_order' ;

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
        $insert_data['trigger_price'] = isset($add_data['trigger_price']) ? $add_data['trigger_price'] : 0 ;
        $insert_data['type'] = $add_data['type'] ;
        $insert_data['modify_time'] = $now ;

        if($buy_order_id){
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
     * 获取指定币种的最新一条信息
     * @param $table_name
     * @param $coin
     * @return mixed
     */
    public function getLastInfo($table_name,$coin){
        // 获取最新的一条信息
        $model = new OkexOrder();
        $params['cond'] ='coin=:coin' ;
        $params['args'] = [':coin'=>$coin];
        $params['orderby'] = 'id DESC';
        $info = $model->findOneByWhere($table_name,$params);
        return $info ;
    }

    /**
     * 获取购买记录表的信息
     * @param $buy_table_name
     * @param $order_id
     * @return mixed
     */
    public function getBuyOrderInfoByOrderId($buy_table_name,$order_id){

        $params['cond'] = 'order_id=:order_id';
        $params['args'] = [':order_id'=>$order_id];
        $model = new OkexOrder();
        return $model->findOneByWhere($buy_table_name,$params);
    }

    /**
     * 获取第三方的订单信息
     * @param $order_id
     * @param $coin
     * @return mixed
     */
    public function getInfoByOrderIdFromService($order_id,$coin){

        $obj = new SwapApi($this->returnConfig());
        $instrumentId = $this->returnInstrumentId($coin) ;
        $res = $obj->getOrderInfo("$order_id",$instrumentId);
        $this->addLog('QUERY_ORDER_BUY_ID',$order_id,$res);
        return $res ;
    }

    /**
     * 查询是否已经卖出过
     * @param $sell_table_name
     * @param $order_id
     * @return mixed
     */
    public function checkIsSell($sell_table_name,$order_id){
        $model = new OkexOrder();

        //0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
        $status_arr = [0,1,2,3,4];
        $params['cond'] = 'buy_id=:buy_id AND ( state in('.implode(',',$status_arr).') OR ( state="-1" AND is_cancel="Y" ) )';
        $params['args'] = [':buy_id'=>$order_id];
        return $model->findOneByWhere($sell_table_name,$params);
    }

    /**
     * 根据购买类型执行卖空还是卖空
     * @param $coin
     * @param $buy_type
     * @param $buy_order_info
     */
    public function doBuy($coin,$buy_type,$size,$buy_order_info=[]){

        $otype = $buy_type == 'up' ? 1:2;
        $order_type = 0; //普通委托
        $match_price = 1 ;
        $obj = new SwapApi($this->returnConfig());
        $client_oid = 0 ;
        $instrumentId = $this->returnInstrumentId($coin);
        $leverage = 50;// 杠杆倍数
        $price = 0 ;
        $res = $obj->takeOrder($client_oid, $instrumentId, $otype, $price, $size, $match_price, $leverage, $order_type);

        $this->addLog('BUY_'.strtoupper($buy_type),0,$res);

        $error_code = isset($res['error_code']) ? $res['error_code'] : '-1';
        if(!$error_code){
            $order_id = $res['order_id'] ;

            //  查询订单状态信息
            $order_info = $this->getInfoByOrderIdFromService($order_id,$coin);

            $this->addLog('QUERY_ORDER_'.strtoupper($buy_type),$order_id,$order_info);

            // 新增记录
            $buy_order_id = $buy_order_info ? $buy_order_info['order_id'] : 0 ;
            return $this->addRowData($coin,$order_info,$buy_order_id);

        }
    }

    /**
     * 执行售出操作
     * @param string $coin 币种
     * @param $sell_type 卖出类型
     * @param $buy_order_info 对应购买订单信息
     * @param string $buy_table_name 购买记录表表名
     * @param string $sell_table_name 卖出记录表表名
     * @return mixed
     */
    public function doSell($coin,$sell_type,$buy_order_info,$buy_table_name,$sell_table_name){

        // 判断是否需要补仓
        $check_add_extra = $this->checkAddExtra($buy_order_info,$buy_table_name);

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
                $this->addLog('DO_SELL_SELL_ORDER2',$buy_order_id,$res);

            }else{
                $obj = new SwapApi($this->returnConfig());

                $res = $obj->takeOrder($client_oid, $instrumentId, $otype, $sell_price, $size, $match_price, $leverage, $order_type);

                $this->addLog('DO_SELL_SELL_ORDER1',$buy_order_id,$res);

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
     * 执行交易操作
     * @param $type
     * @param $coin
     * @return mixed
     */
    public function doTrade($type,$coin){

        // 基础购买数量
        $size = $this->returnBuyNumByCoin($coin);

        $all_table_name = $this->returnAllTableName($coin) ;

        if($type =='up'){
            $buy_table_name = $all_table_name[1] ;
            $sell_table_name = $all_table_name[3] ;
        }else{
            $buy_table_name = $all_table_name[2] ;
            $sell_table_name = $all_table_name[4] ;
        }

        //查询最新购买记录
        $last_info = $this->getLastInfo($buy_table_name,$coin) ;
        if(!$last_info || !$last_info['order_id']){
            // 新增购买记录

            return  $this->doBuy($coin,$type,$size);
        }

        $last_state = $last_info['state'];
        //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
        if($last_state == 2){
            // 查询是否有卖单

            $sell_order_info = $this->checkIsSell($sell_table_name,$last_info['order_id']) ;

            if(!$sell_order_info){

                //此处需要加强判断，判断是否已经强平
                $check_empty_res = $this->dealCheckEmpty($last_info,$sell_table_name);

                if($check_empty_res){
                    return true ;
                }

                return $this->doSell($coin,$type,$last_info,$buy_table_name,$sell_table_name);

            }else{
                return $this->dealSellTradeBySuccessBuyAndSellOrderInfo($type,$last_info,$sell_order_info,$buy_table_name,$sell_table_name);

            }

        }

        if(in_array($last_state,[-2,-1])){
            // 执行购买操作
            //上次取消了，直接再按照之前的数量重新购买
            $size = $last_info['size'];
            // 删除当条记录
            $model = new OkexOrder();
            $this->addLog('DELETE_RECORD_'.$buy_table_name,$last_info['order_id'],$last_info);
            return $model->baseDelete($buy_table_name,'id=:id',[':id'=>$last_info['id']]) ;
            //return  $this->doBuy($coin,$type,$size,$last_info);
        }

        // 实时读取服务对应的订单状态
        $service_buy_order_info =  $this->getInfoByOrderIdFromService($last_info['order_id'],$last_info['coin']) ;
        if(in_array($service_buy_order_info['state'],[-2,-1,1,2,3,4])){
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
                $this->doBuyCancel($last_info['order_id'],$last_info['coin']) ;
            }

            if($service_buy_order_info['state']== -1 && $last_info['state'] == 1){

                #TODO
                #取消交易需要添加时间限制  同时level 层级更新不正确
                // 由部分执行直接
                $filled_qty = $service_buy_order_info['filled_qty'] ;
                if($filled_qty > 0 ){
                    //查询已经购买数量 大于0 直接变更状态为成功
                    //同时判断是否确认数量大于二分之一 ，大于的话
                    $update_data['state'] = 2;

                    $level = $last_info['level'] - 1 > 0 ? ($last_info['level'] - 1):1 ;
                    $update_data['level'] = $level -1 ;


                }
            }

            return $model->baseUpdate($buy_table_name,$update_data,'id=:id',[':id'=>$last_info['id']]);

        }

        if($service_buy_order_info['state'] == 0){

            // 给与90秒的缓冲期，看是否超过，超过了再进行取消操作
            $ext = time() - strtotime($last_info['create_time']) ;
            $min_cancel_seconds = $this->returnCancelOrderSeconds();
            if($ext > $min_cancel_seconds){
                // 时间久还没有下单成功 直接撤销订单
                return $this->doBuyCancel($last_info['order_id'],$last_info['coin']) ;
            }

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
     * 订单平单撤销操作
     * @param $sell_order_id
     * @param $buy_order_id
     * @param $coin
     * @param $buy_table_name
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
     * 处理在购买成功情况下卖出的操作
     * @param $type
     * @param $buy_order_info
     * @param $sell_order_info
     * @param $buy_table_name
     * @param $sell_table_name
     * @return mixed
     */
    public function dealSellTradeBySuccessBuyAndSellOrderInfo($type,$buy_order_info,$sell_order_info,$buy_table_name,$sell_table_name){

        //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中

        // 此方法体内只会处理buy_order_info['state'] == 2 的情况

        // 如何卖单已经成功不做任何处理

        if($sell_order_info['state'] == 2){

            // 查询买入表的最后一条是不是order_id为0 的记录，不是则新增一条
            $last_buy_order = $this->getLastInfo($buy_table_name,$sell_order_info['coin']);

            if(!$last_buy_order || $last_buy_order['order_id']){
                $model = new OkexOrder();
                $add_data['order_id'] = 0 ;
                $add_data['coin'] = $sell_order_info['coin'] ;
                $add_data['create_time'] = date('Y-m-d H:i:s');
                $add_data['modify_time'] = date('Y-m-d H:i:s');
                $model->baseInsert($buy_table_name,$add_data) ;

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
                return $model->baseDelete($sell_table_name,'id=:id',[':id'=>$sell_order_info['id']]);
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
     * 返回group_id
     * @param $table_name
     * @param $buy_order_id
     * @return mixed
     * Note:用户后期补仓使用
     */
    public function createGroupId($table_name,$buy_order_id=''){


        $model = new OkexOrder();
        if($buy_order_id){
            $params['cond'] = 'order_id=:order_id';
            $params['args'] = [':order_id'=>$buy_order_id];
            $info = $model->findOneByWhere($table_name,$params);
            if($info && $info['group_id']){
                return $info['group_id'] ;
            }
        }

        $group_id = time().mt_rand(10000,99999);
        $params['cond'] = 'group_id=:group_id AND `level`=:level';
        $params['args'] = [':group_id'=>$group_id,':level'=>1];
        $info = $model->findOneByWhere($table_name,$params);
        if(!$info){
            return $group_id ;
        }

        return $this->createGroupId($table_name,$buy_order_id);
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
     * 判断是否需要补仓
     * @param $buy_order_info
     * @param $buy_table_name
     * @return mixed
     */
    public function checkAddExtra($buy_order_info,$buy_table_name){

        $group_id = $buy_order_info['group_id'];
        $coin = $buy_order_info['coin'] ;
        $params['cond'] = 'group_id=:group_id AND coin=:coin AND state =2 ';
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

        //查询当前币种的实时价格
        $mark_price = $this->getCoinMarkPriceFromService($coin);

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
     * 根据买的订单信息获取得到需要卖出的商品数量
     * @param $buy_order_info
     * @param $buy_table_name
     * @return mixed
     */
    public function getFilledQtyByBuyOrderInfo($buy_order_info,$buy_table_name){

        $model = new OkexOrder();
        $group_id = $buy_order_info['group_id'];

        $params['cond'] = 'group_id=:group_id AND state=:state';
        $params['args'] = [':group_id'=>$group_id,':state'=>2];
        $list = $model->findAllByWhere($buy_table_name,$params);
        $total = 0 ;
        if($list){
            foreach($list as $v){
                $total += $v['filled_qty'];
            }
        }

        return $total ;
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
     * 获取单个币种合约账户信息
     * @param $coin
     * @return mixed
     */
    public function getBalanceByCoin($coin){
        $instrumentId = $this->returnInstrumentId($coin);
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getCoinAccounts($instrumentId);
        return isset($res['info']) ? $res['info'] : [] ;
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
        $params['cond'] = 'group_id=:group_id AND coin=:coin AND state =2 ';
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
        $params['cond'] = 'group_id=:group_id AND coin=:coin AND state =2 ';
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
     * 根据制定制定类型返回最新的一条强平的单子
     * @param $coin
     * @param $type
     * @return bool
     */
    public function getLastedEmptyOrder($coin,$type){
        $res = $this->queryOrderList($coin,2,10);
        $list = array_sort($res,'timestamp') ;
        if($list){
            foreach($list as $v){
                if($v['type'] == $type && $v['trigger_price']){
                    //说明已经触发了强平
                    return $v ;
                }
            }
        }

        return false ;

    }

    /**
     * 判断是否已经强制平仓
     * @param $buy_order_info
     * @param $sell_table_name
     * @return bool
     */
    public function dealCheckEmpty($buy_order_info,$sell_table_name){

        // 查询最新一条平仓记录
        $empty_order_type = $buy_order_info['type'] + 2 ;
        $empty_order = $this->getLastedEmptyOrder($buy_order_info['coin'],$empty_order_type);
        if(!$empty_order){
            return  false ;
        }

        // 判断是否已经入库
        $params['cond'] = 'order_id =:order_id ';
        $params['args'] = [':order_id'=>$empty_order['order_id']] ;
        $model = new OkexOrder();
        $sell_info = $model->findOneByWhere($sell_table_name,$params);
        if($sell_info){
            return true ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');
        $insert_data['coin'] = $buy_order_info['coin'];
        $insert_data['buy_id'] = $buy_order_info['order_id'];
        $insert_data['order_id'] = $empty_order['order_id'];
        $insert_data['instrument_id'] = $empty_order['instrument_id'] ;
        $insert_data['contract_val'] = $empty_order['contract_val'] ;
        $insert_data['fee'] = $empty_order['fee'] ;
        $insert_data['filled_qty'] = $empty_order['filled_qty'] ;
        $insert_data['order_type'] = $empty_order['order_type'] ;
        $insert_data['price'] = $empty_order['price'] ;
        $insert_data['price_avg'] = $empty_order['price_avg'] ;
        $insert_data['size'] = $empty_order['size'] ;
        $insert_data['state'] = $empty_order['state'] ;
        $insert_data['timestamp'] = $empty_order['timestamp'] ;
        $insert_data['trigger_price'] = $empty_order['trigger_price'] ;
        $insert_data['type'] = $empty_order['type'] ;
        $insert_data['create_time'] = $now ;
        $insert_data['modify_time'] = $now ;

        return $model->baseInsert($sell_table_name,$insert_data);
    }

    public function getHoldingFromService(){}

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



}