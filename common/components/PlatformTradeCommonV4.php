<?php
namespace common\components;


use backend\models\AdminApiKey;
use common\models\OkexOrder;
use common\models\SiteConfig;
use Yii;

// 所有平台交易类的
class PlatformTradeCommonV4
{

    // 交易不中 USD币本位 USDT 美元本位
    public $base_coin = 'USD';

    // 获取当前交易类型 up or downz
    public $type = 'up';
    //是否开始购买
    public $is_start_buy_up = 'N';
    //是否允许交易
    public $is_start_trade_up = 'N';
    public $is_start_buy_down = 'N';
    public $is_start_trade_down = 'N';

    // 判断是否允许开始交易
    public $is_start = true ;

    //是否对冲
    public $is_duichong = false;

    // 所有表名
    public $total_table_info = [];
    public $buy_table_name = '';
    public $sell_table_name = '';
    public $buy_double_table_name = '';
    public $sell_double_table_name = '';

    public $base_buy_num = 1; //最开始购买数量
    public $admin_note = ''; //管理员备注
    // 盈利比例
    public  $earn_percent = 0.002;
    // 止损比例
    public  $stop_percent = 0.2;

    // 最大的补仓层级
    public $max_level = 6;

    // 反向买单的起始点
    public $double_start_level = 2;

    // 补仓间隔
    public $add_distance = 0.003;

    //购买类型
    public $buy_num_type = 'FEIBO';

    //购买币种
    public $buy_coin = 'eos';

    // 配置交易账户的基础密钥信息
    public $apiKey = '';
    public $apiSecret = '';
    public $passphrase = '';

    // 返回后台管理员用户的ID
    public $admin_user_id = 0;

    // 当前的杠杆倍数
    public $leverage = 50;

    // 双边判断开始层级
    public $start_twice_level = 4;

    // 当前实价
    public $mark_price = 0;

    // 强平比例
    public $qiangpin_percent = 0;

    // 将查询的订单进行反向
    public $buy_order_list = [] ;
    public $sell_order_list = [] ;
    public $buy_double_order_list = [] ;
    public $sell_double_order_list = [] ;


    // 实际的订单信息
    public $up_order = [] ;
    public $down_order = [] ;

    /**
     * 返回账户信息
     * @return array
     */
    private function returnConfig()
    {
        $config = [
            //"apiKey"=>"6a5eac0d-7d7a-4931-bc4b-cdbaeef1d8e9",
            "apiKey" => $this->apiKey,
            //"apiSecret"=>"94F53D603D5274FB9B98430AAD5D1706",
            "apiSecret" => $this->apiSecret,
            //"passphrase"=>"a12345678",
            "passphrase" => $this->passphrase,
        ];

        return $config;
    }

    /**
     * 返回钉钉机器人推送消息地址
     * @return string
     */
    public function returnDingDingWebhook()
    {
        return 'https://oapi.dingtalk.com/robot/send?access_token=692e9146bdb392869d7f4aad6b0dba39deaff653b7fac541a431c29a36b99633';
    }

    /**
     * 返回状态说明
     * @return mixed
     */
    public function returnStatusArr()
    {

        $arr['-2'] = '失败';
        $arr['-1'] = '撤单成功';
        $arr['0'] = '等待成交';
        $arr['1'] = '部分成交';
        $arr['2'] = '完全成交';
        $arr['3'] = '下单中';
        $arr['4'] = '撤单中';
        $arr['6'] = '未完成（等待成交+部分成交）';
        $arr['7'] = '已完成（撤单成功+完全成交）';

        return $arr;
    }

    /**
     * 返回已完成状态
     * @return array
     */
    public function returnFinalStateArr()
    {
        //-2:失败 -1:撤单成功 0:等待成交 1:部分成交 2:完全成交 3:下单中 4:撤单中
        $final_state = [-2, -1, 2];
        return $final_state;
    }

    /**
     * 根据币种返回购买数量
     * @param $coin
     * @return int
     */
    public function returnBuyNumByCoin($coin)
    {
        return $this->base_buy_num;

    }

    /**
     * 返回币种字符串
     * @param $coin
     * @return string
     */
    public function returnInstrumentId($coin)
    {
        $coin = strtoupper($coin);
        $base_coin = $this->base_coin;
        $instrumentId = $coin . "-" . $base_coin . "-SWAP";
        return $instrumentId;
    }

    /**
     * 根据币种返回所有表名
     * @param $coin
     * @return array
     */
    public function returnAllTableName($coin = '')
    {
        if($coin=='eos'){
            if($this->base_coin =='USD'){
                $all_table_name = ['', 'sea_okex_buy_up_order_'.$this->admin_user_id, 'sea_okex_buy_down_order_'.$this->admin_user_id, 'sea_okex_sell_up_order_'.$this->admin_user_id, 'sea_okex_sell_down_order_'.$this->admin_user_id, 'sea_okex_buy_up_order_double_'.$this->admin_user_id, 'sea_okex_buy_down_order_double_'.$this->admin_user_id, 'sea_okex_sell_up_order_double_'.$this->admin_user_id, 'sea_okex_sell_down_order_double_'.$this->admin_user_id];
            }else{
                $all_table_name = ['', 'sea_okex_usdt_buy_up_order_'.$this->admin_user_id, 'sea_okex_usdt_buy_down_order_'.$this->admin_user_id, 'sea_okex_usdt_sell_up_order_'.$this->admin_user_id, 'sea_okex_usdt_sell_down_order_'.$this->admin_user_id, 'sea_okex_usdt_buy_up_order_double_'.$this->admin_user_id, 'sea_okex_usdt_buy_down_order_double_'.$this->admin_user_id, 'sea_okex_usdt_sell_up_order_double_'.$this->admin_user_id, 'sea_okex_usdt_sell_down_order_double_'.$this->admin_user_id];
            }
        }else{

            if($this->base_coin =='USD'){
                $all_table_name = ['', 'sea_okex_'.$coin.'_buy_up_order_'.$this->admin_user_id, 'sea_okex_'.$coin.'_buy_down_order_'.$this->admin_user_id, 'sea_okex_'.$coin.'_sell_up_order_'.$this->admin_user_id, 'sea_okex_'.$coin.'_sell_down_order_'.$this->admin_user_id, 'sea_okex_'.$coin.'_buy_up_order_double_'.$this->admin_user_id, 'sea_okex_'.$coin.'_buy_down_order_double_'.$this->admin_user_id, 'sea_okex_'.$coin.'_sell_up_order_double_'.$this->admin_user_id, 'sea_okex_'.$coin.'_sell_down_order_double_'.$this->admin_user_id];
            }else{
                $all_table_name = ['', 'sea_okex_'.$coin.'_usdt_buy_up_order_'.$this->admin_user_id, 'sea_okex_'.$coin.'_usdt_buy_down_order_'.$this->admin_user_id, 'sea_okex_'.$coin.'_usdt_sell_up_order_'.$this->admin_user_id, 'sea_okex_'.$coin.'_usdt_sell_down_order_'.$this->admin_user_id, 'sea_okex_'.$coin.'_usdt_buy_up_order_double_'.$this->admin_user_id, 'sea_okex_'.$coin.'_usdt_buy_down_order_double_'.$this->admin_user_id, 'sea_okex_'.$coin.'_usdt_sell_up_order_double_'.$this->admin_user_id, 'sea_okex_'.$coin.'_usdt_sell_down_order_double_'.$this->admin_user_id];
            }

        }



        return $all_table_name;
    }

    /**
     * 返回取消购买订单的最低判断时间
     * @return int
     * Note: 主要是针对部分购买的情况由于手数过大，会造成不能完全买入的情况
     */
    public function returnCancelBuyOrderSeconds()
    {
        return 20;
    }

    /**
     * 判断最低的删除订单的时间限制
     * @return int
     */
    public function returnMinDeleteOrderSeconds()
    {
        return 120;
    }

    /**
     * 返回取消订单的最低判断时间
     * @return int
     *
     */
    public function returnCancelOrderSeconds()
    {
        return 120;
    }

    /**
     * 根据购买类型返回所有涉及
     * @param $type
     * @return array
     */
    public function returnBuySellTableName($type)
    {
        $coin = $this->buy_coin;
        $all_table_name = $this->returnAllTableName($coin);

        if ($type == 'up') {
            $buy_table_name = $all_table_name[1];
            $buy_double_table_name = $all_table_name[6];
            $sell_table_name = $all_table_name[3];
            $sell_double_table_name = $all_table_name[8];
        } else {
            $buy_table_name = $all_table_name[2];
            $buy_double_table_name = $all_table_name[5];
            $sell_table_name = $all_table_name[4];
            $sell_double_table_name = $all_table_name[7];
        }

        return compact('buy_table_name', 'buy_double_table_name', 'sell_table_name', 'sell_double_table_name');
    }


    /**
     * 设置类中基础配置信息
     * @param $admin_user_id
     * @param $api_key_info
     * @param $coin
     * @param $type
     */
    public function setConfigInfo($admin_user_id, $api_key_info, $coin ,$type)
    {

        // 设置管理员信息
        $this->admin_user_id = $admin_user_id;
        $this->admin_note = $api_key_info['note'];

        // 配置交易币种信息
        $this->base_coin = strtoupper($api_key_info['base_coin']) ;

        // 基础购买数量
        $this->base_buy_num = $api_key_info['base_buy_num'] ;

        // 配置交易账户的基础密钥信息
        $this->apiKey = $api_key_info['api_key'];
        $this->apiSecret = $api_key_info['api_secret'];
        $this->passphrase = $api_key_info['passphrase'];

        // 配置默认杠杆倍数
        $this->leverage = intval($api_key_info['leverage']);

        // 获取当前操作类型
        $this->type = $type ;
        $this->buy_coin = $coin;

        $this->is_start_buy_up = $api_key_info['is_start_buy_up'];
        $this->is_start_trade_up = $api_key_info['is_start_trade_up'];
        $this->is_start_buy_down = $api_key_info['is_start_buy_down'];
        $this->is_start_trade_down = $api_key_info['is_start_trade_down'];
        $this->qiangpin_percent = $api_key_info['qiangpin_percent'];


    }

    /**
     * 获取账单流水
     * @param $coin
     * @param $limit
     * @return mixed
     * Note:查询频率 40次/2次 目前看来意义不大，不能产生实际的作用
     */
    public function queryLedger($coin, $limit = 100)
    {
        // 永续合约
        $coin = strtoupper($coin);
        $instrumentId = $this->returnInstrumentId($coin);
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getLedger($instrumentId, $limit);

        return $res;
    }


    /**
     * 根据状态获取订单列表
     * @param $coin
     * @param $state
     * @param $limit
     * @return mixed
     * Note:查询频率 10次/2s 目前正好对应9个状态所以不会产生限速的限定
     */
    public function queryOrderList($coin, $state = 2, $limit = 100)
    {
        // 永续合约
        $coin = strtoupper($coin);
        $instrumentId = $this->returnInstrumentId($coin);
        $obj = new SwapApi($this->returnConfig());

        $res = $obj->getOrderList($state, $instrumentId);

        return $res;
    }


    /**
     * 根据状态获取订单列表
     * @param $coin
     * @param $type
     * @param $limit
     * @return mixed
     * Note:查询频率 10次/2s 目前正好对应9个状态所以不会产生限速的限定
     */
    public function queryLedgerByType($coin, $type = 22, $limit = 100)
    {
        // 永续合约
        $coin = strtoupper($coin);
        $instrumentId = $this->returnInstrumentId($coin);
        $obj = new SwapApi($this->returnConfig());

        $res = $obj->getLedgerByType($instrumentId, $type,$limit);

        return $res;
    }

    /**
     * 返回所有状态的
     * @param $coin
     * @param int $limit
     * @return array
     */
    public function queryAllStatusOrderList($coin, $limit = 10)
    {
        $all_status = $this->returnStatusArr();
        $res = [];
        foreach ($all_status as $status => $v) {
            $temp_res = $this->queryOrderList($coin, $status);
            $order_list = isset($temp_res['order_info']) ? $temp_res['order_info'] : [];
            if ($order_list) {
                foreach ($order_list as $order_info) {
                    $res[] = $order_info;
                }
            }
        }

        return $res;
    }

    /**
     * 查询对应币种的订单列表信息
     * @param $coin
     * @param $order_id
     * @return mixed
     */
    public function getOrderInfo($coin, $order_id)
    {

        $obj = new SwapApi($this->returnConfig());
        $instrumentId = $this->returnInstrumentId($coin);
        $order_info = $obj->getOrderInfo($order_id, $instrumentId);
        return $order_info;
    }

    /**
     * 根据订单类型返回指定的表名
     * @param $type
     * @param $coin
     * @return string
     */
    public function getTableNameByType($type, $coin)
    {
        //1:开多2:开空3:平多4:平空
        $all_table_name = $this->returnAllTableName($coin);
        $table_name = isset($all_table_name[$type]) ? $all_table_name[$type] : '';
        return $table_name;
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
     * 返回最终需要的购买数目
     * @param $level
     * @return float|int
     */
    public function getBuySizeBuyLevel($level){

        $level = intval($level) ;

        $type= $this->buy_num_type ;
        $base_buy_num = $this->base_buy_num ;
        if($type =='FEIBO'){
            if($level ==0){
                return $base_buy_num ;
            }elseif($level ==1){
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
        }else if($type=='beishu'){
            //return pow($base_buy_num*2,$level+1) ;
            return $base_buy_num*pow(2,$level) ;
        }
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

        $leverage = $this->leverage;// 杠杆倍数
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
     * 插入单行信息
     * @param $coin
     * @param $add_data
     * @param $buy_order_id
     * @param $group_id 假如group_id大于就说明是要补仓
     * @param $level 假如group_id大于就说明是要补仓
     * @param $buy_double_table_name
     * @param $sell_double_table_name
     * @param $orginal_group_id
     * @return mixed
     */
    public function addRowData($coin,$add_data,$buy_order_id='',$group_id=0,$level=0,$buy_double_table_name='',$sell_double_table_name='',$orginal_group_id=0){

        $now = date('Y-m-d H:i:s');

        if($buy_double_table_name){
            $table_name = $buy_double_table_name ;
        }elseif($sell_double_table_name){
            $table_name = $sell_double_table_name ;
        }else{
            $table_name = $this->getTableNameByType($add_data['type'],$coin) ;
        }

        if($add_data['type'] ==1 || $add_data['type'] == 2){
            $insert_data['group_id'] = $group_id >0 ? $group_id : $this->createGroupId($table_name,$buy_order_id);
            $insert_data['level'] = $level > 0 ? $level : $this->createLevel($insert_data['group_id'],$table_name);
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

        if($orginal_group_id && $sell_double_table_name){
            $insert_data['orginal_group_id'] = $orginal_group_id ;
        }

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
     * 依据订单列表返回最新的一条信息
     * @param $order_list
     * @return array
     */
    public function getLastedOrderByOrderList($order_list){
        return isset($order_list[0]) ? $order_list[0] : [] ;
    }

    /**
     * 依据订单列表返回最新的一条已完成信息
     * @param $order_list
     * @return array
     */
    public function getLastedCompleteOrderByOrderList($order_list){

        if($order_list){
            foreach($order_list as $v){
                if($v['state'] ==2){
                    return $v ;
                }
            }
        }

        return [] ;
    }

    /**
     * 从订单列表返回均价
     * @param $list
     * @return bool|float|
     * Note:必须是已购买成功的才能加入均价
     */
    public function getPriceAvgFromList($list){

        $total_price = 0;
        $total_qty = 0 ;
        foreach($list as $v){

            if($v['state'] ==2 && $v['is_deleted'] =='N'){
                $total_price += $v['price_avg']*$v['filled_qty'];
                $total_qty +=$v['filled_qty'];
            }

        }

        $price_avg = $total_price/$total_qty ;
        return $price_avg ;
    }

    /**
     * 从订单列表返回均价
     * @param $list
     * @return bool|float|
     * Note:必须是已购买成功的才能加入均价
     */
    public function getTotalNumFromList($list){

        $total_qty = 0 ;
        foreach($list as $v){

            if($v['state'] ==2 && $v['is_deleted'] =='N'){
                $total_qty +=$v['filled_qty'];
            }

        }

        return $total_qty ;
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
     * 返回当前账户信息
     * @return array
     */
    public function getAccountsInfo(){
        $coin = $this->buy_coin ;
        $instrumentId = $this->returnInstrumentId($coin) ;
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getCoinAccounts($instrumentId);
        return isset($res['info']) ? $res['info'] :[] ;
    }

    /**
     * 获取K线数据
     * @return array
     */
    public function getKlineData(){
        $coin = $this->buy_coin ;
        $instrumentId = $this->returnInstrumentId($coin) ;
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getKline($instrumentId,60);
        return $res ;
    }

    /**
     * 根据订单状态返回订单列表信息
     * @param $state
     * @param string $limit
     * @param string $after
     * @param string $before
     * @return bool|false|mixed|string
     */
    public function getOrderListByState($state,$limit='',$after='',$before=''){
        $coin = $this->buy_coin ;
        $instrumentId = $this->returnInstrumentId($coin) ;
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getOrderList($state, $instrumentId,$after, $before, $limit);
        return isset($res['order_info'])? $res['order_info']:[];
    }

    /**
     * 获取单个合约持仓信息
     * @return array
     */
    public function getAllHoldingInfo(){
        $coin = $this->buy_coin ;
        $instrumentId = $this->returnInstrumentId($coin) ;
        $obj = new SwapApi($this->returnConfig());
        $res = $obj->getSpecificPosition($instrumentId);
        $holding = isset($res['holding']) ? $res['holding'] :[] ;
        $up_order = [] ;
        $down_order = [] ;
        if($holding){
            foreach($holding as $v){
                if($v['side'] =='short'){
                    $down_order = $v;
                }else{
                    $up_order = $v ;
                }
            }
        }

        $this->up_order = $up_order ;
        $this->down_order = $down_order ;

        return compact('up_order','down_order');
    }

    /**
     * 执行交易入口
     * @param $type
     * @param $api_key_info
     * @return bool
     */
    public function doTrade($type,$api_key_info){

        $coin = $api_key_info['coin'];

        $admin_user_id = $api_key_info['admin_user_id'] ;
        // 查询
        $this->setConfigInfo($admin_user_id,$api_key_info,$coin,$type) ;

        if($type=='up'){
            if($this->is_start_trade_up =='N'){
                echo  'PAUSE TRADE';
                return false ;
            }
        }else{
            if($this->is_start_trade_down =='N'){
                echo  'PAUSE TRADE';
                return false ;
            }
        }

        // 对冲操作
        $this->addDoubleOrder();

        if($this->is_duichong){
            echo 'DUICHONG';
            return false;
        }

        // 返回所有的标明信息
        $total_table_info = $this->returnBuySellTableName($type);
        $this->total_table_info = $total_table_info ;

        $buy_table_name = $total_table_info['buy_table_name'];
        $sell_table_name = $total_table_info['sell_table_name'];
        $buy_double_table_name = $total_table_info['buy_double_table_name'];
        $sell_double_table_name = $total_table_info['sell_double_table_name'];
        $this->buy_table_name = $buy_table_name ;
        $this->sell_table_name = $sell_table_name ;
        $this->buy_double_table_name = $buy_double_table_name ;
        $this->sell_double_table_name = $sell_double_table_name ;

        $size = $this->returnBuyNumByCoin($coin);

        $admin_user_id = $this->admin_user_id ;

        $okex_model = new OkexOrder();

        //查询最新购买记录
        $last_info = $okex_model->getLastInfo($admin_user_id,$buy_table_name,$coin) ;

        if(!$last_info || !$last_info['order_id']){
            // 新增购买记录

            if($this->type =='up'){
                if($this->is_start_buy_up=='Y'){
                    return  $this->doBuy($coin,$type,$size);

                }else{
                    return false ; //没有开启不做任何操作
                }
            }else{
                if($this->is_start_buy_down=='Y'){
                    return  $this->doBuy($coin,$type,$size);

                }else{
                    return false ; //没有开启不做任何操作
                }
            }

        }
        $group_id = $last_info['group_id'] ;

        //step1 赋值属性 防止重复查询
        $buy_order_list = $okex_model->getOrderListByGroupId($admin_user_id,$buy_table_name,$group_id) ;
        $sell_order_list = $okex_model->getSellOrderListByOrderId($admin_user_id,$last_info['order_id'],$sell_table_name) ;

        $this->buy_order_list = $buy_order_list ;
        $this->sell_order_list = $sell_order_list ;

        //step2 更新订单状态 有存在购买没有

        $this->updateOrderListLastedInfo($buy_table_name,$buy_order_list) ;
        $this->updateOrderListLastedInfo($sell_table_name,$sell_order_list) ;

        // step3 判断是否有取消中的订单 有取消中的不进行任何操作
        $cancelling_rst = $this->checkIsDealing();

        if($cancelling_rst){
            return false ;
        }

        // step4 判断2边的卖是否完成
        $check_sell_success = $this->checkSellSuccess();
        if($check_sell_success){
            return $this->addNewOrderTag() ;
        }

        //step4 执行挂单操作
        $this->doSellOrBuy();

    }

    /**
     * 跟新订单列表第一条订单的信息
     * @param $table_name
     * @param $order_list
     * @return mixed
     * //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
     */
    public function updateOrderListLastedInfo($table_name,$order_list){

        if(!$order_list){
            return true;
        }

        $order_info = $order_list[0] ;
        $order_state = $order_info['state'];

        // 最终完结状态不做任何操作
        if( in_array($order_state,[-2,-1,2]) ){
            return true ;
        }

        $order_id = $order_info['order_id'] ;
        $coin = $order_info['coin'] ;

        // API获取的订单信息
        $service_order_info = $this->getInfoByOrderIdFromService($order_id,$coin) ;

        $error_code = isset($service_order_info['error_code'])?$service_order_info['error_code']:0;
        if($error_code && $error_code==35029){

            // 添加日志
            $this->addLog('CANNOT_FIND_ORDER',$order_id,$order_info);
            //send_dingding_sms_by_webhook('订单信息查询不到，订单号为:'.$sell_order_info['order_id'].'--'.$buy_order_info['order_id'],$this->returnDingDingWebhook());

            //当前取消订单时间过长导致无法进行查询订单状态
            $ext = time()-strtotime($order_info['create_time']) ;
            $min_delete_order_seconds = $this->returnMinDeleteOrderSeconds();
            if($ext > $min_delete_order_seconds){
                $model = new OkexOrder();
                $update_data['is_deleted'] = 'Y';
                $update_data['modify_time'] = date('Y-m-d H:i:s');
                return $model->baseUpdate($table_name,$update_data,'id=:id',[':id'=>$order_info['id']]);
            }
        }


        $now = date('Y-m-d H:i:s');

        // 长时间委托不成功 直接取消
        if($service_order_info['state'] == 0){

            // 给与90秒的缓冲期，看是否超过，超过了再进行取消操作
            $ext = time() - strtotime($order_info['create_time']) ;
            $min_cancel_seconds = $this->returnCancelOrderSeconds();
            if($ext > $min_cancel_seconds){
                // 时间久还没有下单成功 直接撤销订单
                return $this->doBuyCancel($order_info['order_id'],$order_info['coin']) ;
            }

        }

        // 长时间部分成功 直接成功
        if($service_order_info['state'] == 1){

            // 给与90秒的缓冲期，看是否超过，超过了再进行取消操作
            $ext = time() - strtotime($order_info['create_time']) ;
            $min_cancel_seconds = $this->returnCancelOrderSeconds();
            if($ext > $min_cancel_seconds){
                // 时间久还没有下单成功 直接撤销订单
                return $this->doBuyCancel($order_info['order_id'],$order_info['coin']) ;
            }

        }

        $update_data['state']=$service_order_info['state'] ;
        $update_data['price']=$service_order_info['price'] ;
        $update_data['filled_qty']=$service_order_info['filled_qty'] ;//填充购买数量
        $update_data['size']=$service_order_info['filled_qty'] ;//填充购买数量
        $update_data['price_avg']=$service_order_info['price_avg'] ;
        $update_data['modify_time']=$now ;


        if($service_order_info['state']== -1 && $order_info['state'] == 1 ){

            if($order_info['type'] <=2){
                #TODO
                #取消交易需要添加时间限制  同时level 层级更新不正确
                // 由部分执行直接
                $filled_qty = $service_order_info['filled_qty'] ;
                if($filled_qty > 0 ){
                    //查询已经购买数量 大于0 直接变更状态为成功
                    //同时判断是否确认数量大于二分之一 ，大于的话
                    $update_data['state'] = 2;

                    $level = $order_info['level'] - 1 > 0 ? ($order_info['level'] - 1):1 ;
                    $update_data['level'] = $level ;


                }
            }

        }else{
            if(in_array($service_order_info['state'],[-2,-1])){
                $update_data['is_deleted'] = 'Y';
            }
        }
        $okex_model = new OkexOrder();
        return $okex_model->baseUpdate($table_name,$update_data,'id=:id',[':id'=>$order_info['id']]);

    }

    /**
     * 判断是否有取消中的状态
     * @return mixed
     * //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
     */
    public function checkIsDealing(){

        // 1 3 4 未最终完结状态都是处理中的状态
        $dealing_status_arr = [1,3,4] ;

        $buy_order_info = $this->getLastedOrderByOrderList($this->buy_order_list);
        if($buy_order_info && in_array($buy_order_info['state'],$dealing_status_arr)){
            return true ;
        }

        $sell_order_info = $this->getLastedOrderByOrderList($this->sell_order_list);
        if($sell_order_info && in_array($sell_order_info['state'],$dealing_status_arr)){
            return true ;
        }
        $buy_double_order_info = $this->getLastedOrderByOrderList($this->buy_double_order_list);
        if($buy_double_order_info && in_array($buy_double_order_info['state'],$dealing_status_arr)){
            return true ;
        }

        $sell_double_order_info = $this->getLastedOrderByOrderList($this->sell_double_order_list);
        if($sell_double_order_info && in_array($sell_double_order_info['state'],$dealing_status_arr)){
            return true ;
        }

        return false ;
    }

    /**
     * 通过购买订单信息委托价格进行售出
     * @param $coin
     * @param $buy_order_info
     * @return mixed
     */
    public function doSellByBuyOrderInfo($coin,$buy_order_info){

        $sell_type = $this->type ;

        $otype = $sell_type == 'up' ? 3 : 4 ;
        $client_oid = 0 ;
        $instrumentId = $this->returnInstrumentId($coin) ;
        $order_type = 0 ; // 普通委托
        $leverage = $this->leverage ;
        $match_price = 0 ; //非对手价成交

        $buy_order_id = $buy_order_info['order_id'];


        // 均价
        $buy_price = $this->getPriceAvgFromList($this->buy_order_list);

        // 购买成功的总数量
        $size = $this->getTotalNumFromList($this->buy_order_list) ;

        // 挂单盈利幅度
        $earn_percent = $this->earn_percent ;
        if($sell_type == 'up'){
            $sell_price = $buy_price*(1+$earn_percent);
        }else{
            $sell_price = $buy_price*(1-$earn_percent);
        }

        $obj = new SwapApi($this->returnConfig());

        $res = $obj->takeOrder($client_oid, $instrumentId, $otype, $sell_price, $size, $match_price, $leverage, $order_type);

        $this->addLog('DO_SELL_doSellByBuyOrderInfo',$buy_order_id,$res);

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
     * 根据订单和币种取消订单
     * @param $order_id
     * @param $coin
     * @return bool|false|mixed|string
     */
    public function cancelByOrderId($order_id,$coin){
        $obj = new SwapApi($this->returnConfig());
        $coin = strtoupper($coin);
        $instrumentId = $this->returnInstrumentId($coin);
        return $obj->revokeOrder($instrumentId,$order_id);
    }

    /**
     * 批量取消订单ID
     * @param $order_ids
     * @param $coin
     * @return mixed
     */
    public function cancelOrderIds($order_ids,$coin){
        if(!$order_ids){
            return true ;
        }

        foreach($order_ids as $v){

            $this->cancelByOrderId($v,$coin);
        }
        return true ;
    }

    /**
     * 执行购买和卖还有只有止损的操作
     * @return mixed
     * * //-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
     */
    public function doSellOrBuy(){

        // step1 判断买入第一层是否成功
        $buy_order_info = $this->getLastedOrderByOrderList($this->buy_order_list) ;

        if($buy_order_info['level'] == 1  && $buy_order_info['state']!=2){
            return false ;
        }

        $coin = $buy_order_info['coin'];


        // step2 查询当前实时价格
        $mark_price = $this->getCoinMarkPriceFromService($coin);
        if(!$mark_price){
            return false ;
        }
        $this->mark_price = $mark_price ;

        // step3 查询当前趋势
        $qushi = $this->getBuyQushi();

        if($qushi !='NONE') {
            // step5 确认是否需要取消订单
            $cancel_order_ids = $this->getTotalPendingOrderIds();

            if ($cancel_order_ids) {
                return $this->cancelOrderIds($cancel_order_ids, $coin);
            }

            if($qushi =='STOP'){
                // step6 对手价进行卖出
                $this->doSellTotalOrderByStop();
            }else{
                // step7 根据趋势进行对手价买入
                return $this->doBuyByQushi($qushi,$coin);
            }


        }

        // step4 重来还没卖出过 需要直接执行卖出操作
        /*
        $sell_order_info = $this->getLastedOrderByOrderList($this->sell_order_list);
        if(!$sell_order_info){
            return $this->doSellByBuyOrderInfo($coin,$buy_order_info);
        }*/


        // step6 判断是否已经全部卖出
        $check_need_sell = $this->checkNeedSell();

        if($check_need_sell){
            return $this->doSellByOrderList();
        }

        return true ;
    }

    /**
     * 获取当前趋势 包括购买趋势和止损趋势
     * @return string
     * Note: BUY_UP/BUY_DOWN/STOP/NONE
     */
    public function getBuyQushi(){

        $buy_order_list = $this->buy_order_list ;

        $buy_order_info = $this->getLastedCompleteOrderByOrderList($buy_order_list);
        $buy_order_price_avg = $buy_order_info['price_avg'] ;
        $buy_order_level = $buy_order_info ? $buy_order_info['level'] : 0;

        // 已购买订单的均价
        $buy_price_avg = $this->getPriceAvgFromList($buy_order_list) ;
        $mark_price = $this->mark_price ;


        if($buy_order_level < $this->max_level ) {

            if($this->type =='down'){
                //if($this->mark_price >$buy_order_info['price_avg']){
                if($this->mark_price >$buy_order_price_avg){

                    //$add_distance = $buy_order_level >=3 ? $this->add_distance*3:$this->add_distance ;
                    $add_distance = $this->add_distance ;
                    if( ($this->mark_price/$buy_order_price_avg-1 ) >=$add_distance){
                        return 'BUY_DOWN';
                    }
                }

            }else{

                // up
                //if($this->mark_price <$buy_order_info['price_avg']){

                if($this->mark_price <$buy_order_price_avg){

                    //$add_distance = $buy_order_level >=3 ? $this->add_distance*3:$this->add_distance ;
                    $add_distance = $this->add_distance ;
                    if( ($buy_order_price_avg/$this->mark_price-1 ) >=$add_distance){
                        return 'BUY_UP';
                    }
                }

            }

        }else{

            // 出发止损
            if($this->type =='down'){
                //if($this->mark_price >$buy_order_info['price_avg']){
                if($this->mark_price >$buy_price_avg){

                    if( ($this->mark_price/$buy_price_avg-1 ) >=$this->stop_percent){
                        return 'STOP';
                    }
                }

            }else{

                // up
                //if($this->mark_price <$buy_order_info['price_avg']){
                if($this->mark_price < $buy_price_avg){

                    if( ($buy_price_avg/$this->mark_price-1 ) >=$this->stop_percent){
                        return 'STOP';
                    }
                }

            }
        }

        return 'NONE';
    }

    /**
     * 返回所有处理中的订单ID集合
     * @return array
     * Note://-2:失败-1:撤单成功0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
     */
    public function getTotalPendingOrderIds(){

        $res = [] ;

        $pending_status = [0,1,3,4];
        if($this->buy_order_list){
            foreach($this->buy_order_list as $v){
                if(in_array($v['state'],$pending_status)){
                    $res[] = $v['order_id'] ;
                }
            }
        }

        if($this->sell_order_list){
            foreach($this->sell_order_list as $v){
                if(in_array($v['state'],$pending_status)){
                    $res[] = $v['order_id'] ;
                }
            }
        }

        if($this->buy_double_order_list){
            foreach($this->buy_double_order_list as $v){
                if(in_array($v['state'],$pending_status)){
                    $res[] = $v['order_id'] ;
                }
            }
        }

        if($this->sell_double_order_list){
            foreach($this->sell_double_order_list as $v){
                if(in_array($v['state'],$pending_status)){
                    $res[] = $v['order_id'] ;
                }
            }
        }


        return $res ;
    }

    /**
     * 返回返回所有的购买订单是否已经购买成功
     * @return boolean
     */
    public function checkAllBuyOrderSuccess(){
        $buy_order_list = $this->buy_order_list ;
        $buy_order_info = $this->getLastedOrderByOrderList($buy_order_list) ;
        if($buy_order_info && $buy_order_info['state'] !=2){
            return false ;
        }

        $buy_double_order_list = $this->buy_double_order_list ;
        $buy_double_order_info = $this->getLastedOrderByOrderList($buy_double_order_list) ;
        if($buy_double_order_info && $buy_double_order_info['state'] !=2){
            return false ;
        }

        return true ;
    }

    /**
     * 根据趋势对手价购买
     * @param $qushi
     * @param $coin
     * @return mixed
     */
    public function doBuyByQushi($qushi,$coin){

        if($qushi =='BUY_UP'){
            $buy_type = 'up' ;
        }else{
            $buy_type = 'down' ;
        }

        $buy_double_table_name = '';

        $buy_order_info = $this->getLastedOrderByOrderList($this->buy_order_list);
        $group_id = $buy_order_info['group_id'];


        //反向订单购买订单是由高到低进行购买
        $buy_level = $buy_order_info['level'] ;
        $size = $this->getBuySizeBuyLevel($buy_level);
        $otype = $buy_type == 'up' ? 1:2;
        $order_type = 0; //普通委托
        $match_price = 1 ;

        $obj = new SwapApi($this->returnConfig());
        $client_oid = 0 ;
        $instrumentId = $this->returnInstrumentId($coin);

        $leverage = $this->leverage;// 杠杆倍数
        $price = 0 ;
        $res = $obj->takeOrder($client_oid, $instrumentId, $otype, $price, $size, $match_price, $leverage, $order_type);

        $this->addLog('BUY_1144_'.strtoupper($buy_type),0,$res);

        $error_code = isset($res['error_code']) ? $res['error_code'] : '-1';

        if(!$error_code){
            $order_id = $res['order_id'] ;

            //  查询订单状态信息
            $order_info = $this->getInfoByOrderIdFromService($order_id,$coin);

            $this->addLog('QUERY_ORDER_'.strtoupper($buy_type),$order_id,$order_info);

            // 新增记录

            $buy_order_id = $buy_order_info ? $buy_order_info['order_id'] : 0 ;
            return $this->addRowData($coin,$order_info,$buy_order_id,$group_id,0,$buy_double_table_name);

        }

        return false ;
    }

    /**
     * 根据对手价卖出指定的订单信息
     * @return mixed
     */
    public function doSellTotalOrderByStop(){

        // 先更新数据库不能执行交易
        $site_config = new SiteConfig();
        $update_data['config_value'] = 'N';
        $site_config->baseUpdate($site_config::tableName(),$update_data,'config_key=:config_key',[':config_key'=>'is_okex_start']);

        $coin = $this->buy_coin ;
        $client_oid = 0 ;
        $instrumentId = $this->returnInstrumentId($coin) ;
        $order_type = 0 ; // 普通委托
        $leverage = $this->leverage ;
        $match_price = 0 ; //非对手价成交

        $type = $this->type ;

        $total_buy_num = $this->getTotalNumFromList($this->buy_order_list) ;

        if($total_buy_num >0){
            $otype = $type =='up' ? 3:4 ;
            //直接止损，直接以对手价进行卖出
            $obj = new SwapApi($this->returnConfig());
            $res = $obj->takeOrder($client_oid, $instrumentId, $otype, 0, $total_buy_num, $match_price, $leverage, $order_type);
            $this->addLog('DO_SELL_STOP_1289',0,$res);

            $error_code = isset($res['error_code']) ? $res['error_code'] : '-1';
            if(!$error_code){
                $order_id = $res['order_id'] ;

                //  查询订单状态信息
                $order_info = $this->getInfoByOrderIdFromService($order_id,$coin);

                // 新增记录
                $buy_order_info = $this->getLastedCompleteOrderByOrderList($this->buy_order_list);
                $buy_order_id = $buy_order_info ? $buy_order_info['order_id']: 0;
                return $this->addRowData($coin,$order_info,$buy_order_id);

            }
        }

    }

    /**
     * 查询是否已经卖出过
     * @param $sell_table_name
     * @param $buy_id
     * @return mixed
     */
    public function checkIsSell($sell_table_name,$buy_id,$c=false){
        $model = new OkexOrder();

        //0:等待成交1:部分成交2:完全成交3:下单中4:撤单中
        $status_arr = [0,1,2,3,4];
        $params['cond'] = 'buy_id=:buy_id AND is_deleted="N" AND ( state in('.implode(',',$status_arr).') OR ( state="-1" AND is_cancel="Y" ) )';
        $params['args'] = [':buy_id'=>$buy_id];

        $info =  $model->findOneByWhere($sell_table_name,$params);
        return $info ;
    }

    /**
     * 判断是否需要挂单
     * @return boolean
     */
    public function checkNeedSell(){

        $buy_order_info = $this->getLastedOrderByOrderList($this->buy_order_list) ;

        if(!$this->checkIsSell($this->sell_table_name,$buy_order_info['order_id'])){
            return true ;
        }

        $buy_double_order_info = $this->getLastedOrderByOrderList($this->buy_double_order_list);

        if(!$this->checkIsSell($this->sell_double_table_name,$buy_double_order_info['order_id'])){
            return true ;
        }
        return false ;
    }

    /**
     * 根据购买订单进行
     * @return mixed
     */
    public function doSellByOrderList(){

        $buy_order_info = $this->getLastedOrderByOrderList($this->buy_order_list) ;

        $buy_price_avg = $this->getPriceAvgFromList($this->buy_order_list);

        if(!$buy_price_avg){
            return false ;
        }

        // 读取当前币价
        $mark_price = $this->mark_price;
        $coin = $this->buy_coin ;
        if(!$mark_price){
            $mark_price = $this->getCoinMarkPriceFromService($coin);
            $this->mark_price = $mark_price;
        }

        if(!$this->mark_price){
            return false ;
        }

        if($this->type =='up'){
            $sell_price = $buy_price_avg*(1+$this->earn_percent);
            $sell_price = $sell_price > $this->mark_price ? $sell_price : $this->mark_price;
        }else{
            $sell_price = $buy_price_avg*(1-$this->earn_percent);
            $sell_price = $this->mark_price < $sell_price ? $this->mark_price : $sell_price ;
        }

        if(!$sell_price){
            return false ;
        }
        $coin  = $this->buy_coin ;

        $client_oid = 0 ;
        $instrumentId = $this->returnInstrumentId($coin) ;
        $order_type = 0 ; // 普通委托
        //$order_type = 1 ; // maker
        $leverage = $this->leverage ;
        $match_price = 0 ; //非对手价成交

        $obj = new SwapApi($this->returnConfig());
        $buy_num = $this->getTotalNumFromList($this->buy_order_list) ;
        $otype = $this->type == 'up' ? 3 : 4 ;

        $res = $obj->takeOrder($client_oid, $instrumentId, $otype, $sell_price, $buy_num, $match_price, $leverage, $order_type);
        $error_code = isset($res['error_code']) ? $res['error_code'] : '-1';

        if(!$error_code){
            $order_id = $res['order_id'] ;

            //  查询订单状态信息
            $order_info = $this->getInfoByOrderIdFromService($order_id,$coin);

            // 新增记录
            $this->addRowData($coin,$order_info,$buy_order_info['order_id'],0,0,'','','');

        }

        return true ;
    }

    /**
     * 判断是否完全售出
     * @return bool
     */
    public function checkSellSuccess(){

        $sell_order_info = $this->getLastedOrderByOrderList($this->sell_order_list);

        if($sell_order_info['state'] ==2){

            $sell_double_order_info = $this->getLastedOrderByOrderList($this->sell_double_order_list);
            if(!$sell_double_order_info)
            {
                return true ;
            }else{
                if($sell_double_order_info['state'] ==2){
                    return true ;
                }
            }

        }

        return false ;
    }

    /**
     * 新增订单完成标记为
     * @return bool|string
     */
    public function addNewOrderTag(){

        $model = new OkexOrder();
        // 查询买入表的最后一条是不是order_id为0 的记录，不是则新增一条
        $sell_order_info = $this->getLastedOrderByOrderList($this->sell_order_list);
        $buy_table_name = $this->buy_table_name ;
        $last_buy_order = $model->getLastInfo($this->admin_user_id,$buy_table_name,$sell_order_info['coin']);

        if(!$last_buy_order || $last_buy_order['order_id']){
            $model = new OkexOrder();
            $add_data['order_id'] = 0 ;
            $add_data['admin_user_id'] = $this->admin_user_id ;
            $add_data['coin'] = $sell_order_info['coin'] ;
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            return $model->baseInsert($buy_table_name,$add_data) ;

        }

        return false ;
    }

    /**
     * 添加对冲单
     * @return bool
     */
    public function addDoubleOrder(){



        $site_config = new SiteConfig();
        $qiangpin_start =$site_config->getByKey('qiangpin_start');
        if($qiangpin_start =='N'){
            return false ;
        }

        $qiangpin_percent = $this->qiangpin_percent ;
        if($qiangpin_percent <= 0){
            $qiangpin_percent = $site_config->getByKey('qiangpin_percent');
            $qiangpin_percent = $qiangpin_percent > 0 ? $qiangpin_percent :0.12 ;
        }

        $mark_price = $this->mark_price;
        $coin = $this->buy_coin ;
        if(!$mark_price){
            $mark_price = $this->getCoinMarkPriceFromService($coin);
            $this->mark_price = $mark_price;
        }

        if(!$this->mark_price){
            return false ;
        }

        // 查询当前持有
        $all_holding = $this->getAllHoldingInfo();

        // 获取预估强评价
        $up_order = $all_holding['up_order'] ;
        $liquidation_price = isset($up_order['liquidation_price']) ? $up_order['liquidation_price'] : 0;

        // 多单数量
        $up_position = isset($up_order['position']) ? $up_order['position'] : 0;
        $down_order = $all_holding['down_order'] ;

        // 空单数量
        $down_position = isset($down_order['position']) ? $down_order['position'] : 0;

        if($up_position == $down_position){
            return true ;
        }

        if(!$liquidation_price){
            $liquidation_price = isset($down_order['liquidation_price']) ? $down_order['liquidation_price'] : 0;
        }

        if($liquidation_price <=0){
            return true ;
        }

        // 获取当前的爆仓价 比例
        if($mark_price > $liquidation_price){
            $percent = $mark_price/$liquidation_price - 1 ;
        }else{
            $percent = $liquidation_price/$mark_price - 1 ;
        }

        if($percent >= $qiangpin_percent){
            return false ;
        }

        // 读取redis 保证五秒之内
        // 设置redis 当前有效期为10秒
        $set_redis_rst = $this->setHedgingRedis();
        if(!$set_redis_rst){
            return false ;
        }

        $model = new AdminApiKey();
        $update_data['is_start_trade_up'] = 'N';
        $update_data['is_start_trade_down'] = 'N';
        $update_data['is_start_trade_up'] = 'N';
        $update_data['is_start_trade_down'] = 'N';
        $update_data['is_sync'] = 'N';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $model->baseUpdate($model::tableName(),$update_data,'admin_user_id=:admin_user_id',[':admin_user_id'=>$this->admin_user_id]);

        // 获取所有等待成交的订单
        $pending_order_list = $this->getOrderListByState(0);
        if($pending_order_list){
            foreach($pending_order_list as $v){
                $this->cancelByOrderId($v['order_id'],$coin);
            }
        }

        if($up_position > $down_position){
            //买空单
            $buy_num = $up_position - $down_position ;
            $this->doBuy($coin,'down',$buy_num) ;
        }else{
            $buy_num = $down_position - $up_position ;
            $this->doBuy($coin,'up',$buy_num) ;
        }
        $message = '对冲账户--'.$this->admin_note;
        send_dingding_sms_by_webhook($message,$this->returnDingDingWebhook());
        $this->is_duichong = true;
        return true ;


    }

    /**
     * 设置队中redis
     * @return mixed
     * Note:设置有效期为20秒
     */
    public function setHedgingRedis(){

        $admin_user_id = $this->admin_user_id ;
        $redis = new MyRedis();
        $redis_key =  "Hedging:limit:".$admin_user_id;
        $redis_info = $redis->get($redis_key);
        $id = intval($redis_info);
        if($id > 0){
            return false ;
        }
        $id = $redis->incrBy($redis_key,1,20);
        if($id > 1){
            return false ;
        }else{
            return true ;
        }
    }


}

