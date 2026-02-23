<?php

namespace common\models;

use common\components\CommonTrade;
use common\components\HuobiLib;
use Yii;
require_once (dirname(__FILE__) . '/../components/OKCoin/OKCoin.php');
use common\components\OkexTrade;

/**
 * This is the model class for table "sdb_order".
 *
 * @property string $id
 * @property string $apiKey
 * @property string $secretKey
 * @property string $post_params 推送数据
 * @property string $response_data 响应数据
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class Order extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_order';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_okex');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_params', 'response_data'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['apiKey', 'secretKey'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apiKey' => 'Api Key',
            'secretKey' => 'Secret Key',
            'post_params' => 'Post Params',
            'response_data' => 'Response Data',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }



    /**
     * 判断是否可以补仓两次
     * @param $from_trade_info 最后一次交易信息
     */
    public function checkIsAllowedAddTwo($from_trade_info){
        $trade_info = $this->getRowInfoById($from_trade_info['id']);
        $symbol = $trade_info['symbol'];
        $apiKey = $trade_info['apiKey'];
        $secretKey = $trade_info['secretKey'];
        $id = $trade_info['id'];
        $num  =2 ;

        $params['cond'] = 'symbol = :symbol AND id < :id AND apiKey =:apiKey AND secretKey = :secretKey';
        $params['args'] = [':symbol'=>$symbol,':id'=>$id,':secretKey'=>$secretKey,':apiKey'=>$apiKey];
        $params['orderby'] = ' id desc ';
        $params['limit'] = $num;
        $list = $this->findAllByWhere('sdb_order', $params, self::getDb());

        $now_type = $trade_info['type'];
        //前一次交易信息
        $prev_info = $list[0];
        $prev_type = $prev_info['type'];

        //前前次交易信息
        $prev_prev_info = $list[1];
        $prev_prev_type = $prev_prev_info['type'];

        if( $now_type == 'BUY'){
            if($prev_type =='SELL'){
                return array('result'=>true,'type'=>'FIRST') ;
            }

            if($prev_type =='BUY' && $prev_prev_type=='SELL'){
                return array('result'=>true,'type'=>'SECOND') ;
            }
        }

        return array('result'=>false,'type'=>'') ;
    }

    /**
     * 判断补仓
     * @param $from_trade_info 最后一次交易信息
     * @param $last_price  最新价格
     * @return array
     */
    public function checkIsAllowedAdd($from_trade_info,$last_price){
        $trade_info = $this->getRowInfoById($from_trade_info['id']);
        $symbol = $trade_info['symbol'];
        $apiKey = $trade_info['apiKey'];
        $secretKey = $trade_info['secretKey'];
        $user_symbol_id = $trade_info['user_symbol_id'];
        $id = $trade_info['id'];
        $num  =1 ;

        $params['cond'] = 'user_symbol_id=:user_symbol_id AND symbol = :symbol AND id < :id AND apiKey =:apiKey AND secretKey = :secretKey';
        $params['args'] = [':user_symbol_id'=>$user_symbol_id,':symbol'=>$symbol,':id'=>$id,':secretKey'=>$secretKey,':apiKey'=>$apiKey];
        $params['orderby'] = ' id desc ';
        $params['limit'] = $num;
        $list = $this->findAllByWhere('sdb_order', $params, self::getDb());

        $now_type = $trade_info['type'];
        //前一次交易信息
        $prev_info = $list[0];
        $prev_type = $prev_info['type'];


        if( $now_type == 'BUY'){
            if($prev_type =='SELL'){
                $price = $trade_info['price'];
                $percent = $price/$last_price ;
                if($price>$last_price && $percent >=1.06 ){
                    return array('result'=>true,'type'=>'FIRST') ;
                }

            }

        }

        return array('result'=>false,'type'=>'') ;
    }

    public function getRowInfoById($id){
        $order_model = new Order();
        $params['cond'] = 'id = :id';
        $params['args'] = [':id'=>$id];
        $row = $order_model->findOneByWhere('sdb_order', $params, $order_model::getDb());
        return $row;
    }

    /**
     * 获取之前所有购买的记录信息
     * @param $prev_trade_info
     * @return array
     */
    public function getPrevBuyList($prev_trade_info){
        if($prev_trade_info && $prev_trade_info['type']=='BUY'){
            $user_symbol_id = $prev_trade_info['user_symbol_id'];
            //查询之前一次卖的时间
            $prev_sell_params['cond'] = 'create_time <:create_time AND user_symbol_id =:user_symbol_id and type=:type AND status=:status ';
            $prev_sell_params['args'] = [":create_time"=>$prev_trade_info['create_time'],":user_symbol_id"=>$user_symbol_id,':type'=>'SELL',':status'=>2];
            $prev_sell_params['orderby'] = 'create_time desc' ;
            $prev_sell_info = $this->findOneByWhere('sdb_order',$prev_sell_params,self::getDb());
            $prev_sell_create_time   = isset($prev_sell_info['create_time'])?$prev_sell_info['create_time']:0;

            $params['cond'] = 'create_time >:create_time AND user_symbol_id =:user_symbol_id AND type=:type AND status=:status ' ;
            $params['args'] = [':create_time'=>$prev_sell_create_time,':user_symbol_id'=>$user_symbol_id,':type'=>'BUY',':status'=>2];
            $params['orderby'] = 'create_time asc';
            $list = $this->findAllByWhere('sdb_order',$params,self::getDb());

            return $list ;

        }else{
            return array();
        }
    }

    /**
     * 获取之前交易的平均值 目前代码可选， 有可能是一次补仓，有可能是2次补仓
     * @param $prev_trade_info 最后一次交易的信息
     * @return float|int
     */
    public function  getPrevAvgPrice($prev_trade_info){


        $total = 0 ;
        $total_amount = 0 ;
        $list = $this->getPrevBuyList($prev_trade_info);
        if($list){
            foreach($list as $v){
                $total += $v['price']*$v['amount'];
                $total_amount += $v['amount'];
            }

            return $total/$total_amount ;
        }
        return  0 ;

    }

    /**
     * 获取第一次购买的数量
     * @param $prev_trade_info
     */
    public function getFirstBuyAmount($prev_trade_info){

    }

    /**
     *获取得到之前的购买总次数
     */
    public function getTotalBuyTimes($prev_trade_info){
        $list = $this->getPrevBuyList($prev_trade_info);
        return count($list);
    }

    //判断确认是否取消订单
    public function checkSellOrder(){
        //获取允许的交易类型
        $symbol_model = new Symbol();
        $symbol_params['cond'] = 'is_open =:is_open';
        $symbol_params['args'] = ['is_open'=>1] ;
        $symbol_list = $symbol_model->findAllByWhere('sdb_symbol',$symbol_params,$symbol_model::getDb());

        //获取所有用户
        $member_model = new Member();
        $member_params['cond'] = 'status =:status AND  is_open =:is_open ';
        $member_params['args'] = [':status'=>'enabled',':is_open'=>1];
        $member_list = $member_model->findAllByWhere('sdb_member',$member_params,$symbol_model::getDb());


        $order_model = new Order();
        foreach($symbol_list as $v){
            $symbol = $v['key'];
            foreach($member_list as $member){
                $apiKey    = $member['apiKey'];
                $secretKey = $member['secretKey'];
                $order_params['cond'] = 'apiKey =:apiKey AND secretKey = :secretKey AND symbol =:symbol AND type=:type';
                $order_params['args'] = [':apiKey'=>$apiKey,':secretKey'=>$secretKey,':symbol'=>$symbol,':type'=>'SELL'] ;
                $order_params['orderby'] ='id desc';
                $order_row = $order_model->findOneByWhere('sdb_order',$order_params,$order_model::getDb());

                $this->checkSell($order_row);
            }

        }
    }

    /**
     * 最终确认是否销售 目前系统中每十分钟执行一次
     * @param $order_row
     */
    public function checkSell($order_row){

        $apiKey = $order_row['apiKey'];
        $secretKey = $order_row['secretKey'];
        $symbol = $order_row['symbol'];
        $order_id = $order_row['order_id'];

        $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
        $params = array('api_key' => $apiKey, 'symbol' => $symbol.'t', 'type' => 1, 'order_id' => $order_id);
        $query_result = $client->ordersInfoApi($params);
        $rst = object_to_array($query_result);

        $result = isset($rst['result']) ? $rst['result'] : 0;
        if (!$result) {
            return false;
        }

        $orders = isset($rst['orders']) ? $rst['orders'] : '-1';
        $status = isset($orders[0]['status'])?$orders[0]['status']:0;
        if($status !=2 ){

            //撤销订单
            $cancel_params = array('api_key' => $apiKey, 'symbol' => $symbol.'t', 'order_id' => $order_id);
            $cancel_rst = $client -> cancelOrderApi($cancel_params);
            //删除记录
            $this->baseDelete('sdb_order','id=:id',[':id'=>$order_row['id']],'db_okex');
            ///新增删除记录
            $add_data['symbol'] = $symbol ;
            $add_data['apiKey'] = $apiKey;
            $add_data['secretKey'] = $secretKey ;
            $add_data['order_data'] = json_encode($order_row) ;
            $add_data['response_data'] = json_encode($cancel_rst) ;
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $this->baseInsert('sdb_cancel_order_log',$add_data,'db_okex');

            //同时取消用户的禁用操作
           // $cancel_symbol_task_model = new  CancelSymbolTask();
            //$cancel_symbol_task_model->doAfterCancelSellByCancel($symbol,$apiKey,$secretKey,$order_row['user_symbol_id'],$order_id);


        }else{

            //操作用户取消币种的相关数据
           // $user_symbol_model = new CancelSymbolTask();
            //user_symbol_model->dealSellSuccess($order_id);
        }
        return true ;
    }

    /**
     * 查找当前用户，当前币种最后一次交易类型
     * @param $user_symbol_id
     * @param $symbol
     * @param $apiKey
     * @param $secretKey
     */
    public function getLastTradeType($user_symbol_id,$symbol,$apiKey,$secretKey){

        $params['cond'] = 'user_symbol_id =:user_symbol_id AND symbol =:symbol AND apiKey =:apiKey AND secretKey =:secretKey' ;
        $params['args'] = array(
            ':user_symbol_id'=>$user_symbol_id,
            ':symbol'=>$symbol,
            ':apiKey'=>$apiKey,
            ':secretKey'=>$secretKey,

        );
        $params['orderby'] = 'id DESC';

        $row = $this->findOneByWhere($this->tableName(),$params,self::getDb());
        return $row?$row['type']:'';
    }

    /**
     * 查找当前用户，当前币种最后一次交易的顶大ID
     * @param $user_symbol_id
     * @param $symbol
     * @param $apiKey
     * @param $secretKey
     */
    public function getLastTradeOrderId($user_symbol_id,$symbol,$apiKey,$secretKey){

        $params['cond'] = 'user_symbol_id =:user_symbol_id AND symbol =:symbol AND apiKey =:apiKey AND secretKey =:secretKey' ;
        $params['args'] = array(
            ':user_symbol_id'=>$user_symbol_id,
            ':symbol'=>$symbol,
            ':apiKey'=>$apiKey,
            ':secretKey'=>$secretKey,

        );
        $params['orderby'] = 'id DESC';

        $row = $this->findOneByWhere($this->tableName(),$params,self::getDb());
        return $row?$row['order_id']:'';
    }

    /**
     * 根据订单ID 获取得到订单表信息
     * @param $order_id
     * @return array|bool
     */
    public function getRowInfoByOrderId($order_id){
        $order_model = new Order();
        $params['cond'] = 'order_id = :order_id';
        $params['args'] = [':order_id'=>$order_id];
        $row = $order_model->findOneByWhere('sdb_order', $params, $order_model::getDb());
        return $row;
    }

    //当前交易的收益值
    public function getIncomeByOrderRow($order_row){
        $id = $order_row['id'] ;
        $apiKey = $order_row['apiKey'];
        $secretKey = $order_row['secretKey'];
        $user_symbol_id = $order_row['user_symbol_id'];
        $parsms['cond'] ='id<:id AND type =:type AND apiKey =:apiKey AND secretKey =:secretKey AND user_symbol_id =:user_symbol_id';
        $parsms['args'] = [':id'=>$id,':type'=>'BUY',':apiKey'=>$apiKey,':secretKey'=>$secretKey,':user_symbol_id'=>$user_symbol_id];
        $parsms['orderby'] = 'id desc';
        $prev_row = $this->findOneByWhere('sdb_order',$parsms,self::getDb());

        if($prev_row){
            $income = $order_row['price']*$order_row['amount'] - $prev_row['price']*$prev_row['amount'] ;

        }else{
            $income = $order_row['price']*$order_row['amount'] ;
        }
        return $income ;
    }


    public function addIncomeInfo($order_row){

        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$order_row['user_symbol_id']];
        $user_symbol_row = $this->findOneByWhere('sdb_user_symbol',$params,self::getDb());
        $user_id = $user_symbol_row['user_id'];

        $income = $this->getIncomeByOrderRow($order_row);
        $add_data['symbol'] = $order_row['symbol'];
        $add_data['order_create_time'] = $order_row['create_time'];
        $add_data['user_symbol_id'] = $order_row['user_symbol_id'];
        $add_data['user_id'] = $user_id;
        $add_data['price'] = $income;
        $add_data['apiKey'] = $order_row['apiKey'];
        $add_data['secretKey'] = $order_row['secretKey'];
        $add_data['create_time'] = date('Y-m-d H:i:s');

        $this->baseInsert('sdb_user_income',$add_data,'db_okex');

    }

    public function getTodayIncome($symbol,$apiKey,$secretKey,$user_symbol_id){

        $params['cond'] = 'order_create_time >= :order_create_time1 AND order_create_time <=:order_create_time2 AND symbol =:symbol AND apiKey =:apiKey AND secretKey = :secretKey AND user_symbol_id =:user_symbol_id';
        $params['args'][':order_create_time1'] = date('Y-m-d 00:00:00') ;
        $params['args'][':order_create_time2'] = date('Y-m-d 23:59:59') ;
        $params['args'][':symbol'] = $symbol ;
        $params['args'][':apiKey'] = $apiKey ;
        $params['args'][':secretKey'] = $secretKey ;
        $params['args'][':user_symbol_id'] = $user_symbol_id ;

        $params['fields'] = 'SUM(price) as total';
        $row = $this->findOneByWhere('sdb_user_income',$params,self::getDb());

        return $row&&isset($row['total'])&&$row['total']?$row['total']:0;

    }

    /**
     * 通过当前币种的收入情况来判断是否允许购买
     * @param $symbol
     * @param $apiKey
     * @param $secretKey
     * @param $user_symbol_id
     * @return bool
     */
    public function checkIsAllowedBuyFromIncome($symbol,$apiKey,$secretKey,$user_symbol_id){


        //用户当前总数目
        $trade_obj = new OkexTrade() ;
        $user_usdt = $trade_obj->getUserUsdt($apiKey,$secretKey);

        $symbol_model = new UserSymbol();
        $params['cond'] = 'status =:status and  is_open =:is_open';
        $params['args'] = [':status'=>'enabled',':is_open'=>1] ;
        $user_symbol_list = $symbol_model->findAllByWhere('sdb_user_symbol',$params,$symbol_model::getDb());
        $user_symbol_num = count($user_symbol_list);
        if($user_symbol_num){
            $symbol_usdt = $user_usdt/$user_symbol_num;

            //获取当前币种的当天营收
            $total_income = $this->getTodayIncome($symbol,$apiKey,$secretKey,$user_symbol_id);
            $upper_limit = $symbol_usdt*0.05;

            if($total_income > $upper_limit){
                return false ;
            }

        }

        return true ;
    }

    /**
     * 获取用户订单信息
     * @param $user_symbol_row
     */
    public function getOrderListByUserSymbol($user_symbol_row){
        $platform = $user_symbol_row['platform'];
        if($platform=='OKEX'){
            $apiKey = $user_symbol_row['apiKey'];
            $secretKey = $user_symbol_row['secretKey'];
            $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
            $params['api_key'] = $apiKey;
            $params['symbol'] = $user_symbol_row['symbol_key'];
            $params['status'] = 1 ;
            $params['current_page'] = 1 ;
            $params['page_length'] = 200 ;
            $rst  = $client->orderHistoryApi($params);
            $rst = (array)$rst;

            $orders = isset($rst['orders'])?$rst['orders']:array();
            return $orders ;
        }

        if($platform =='HUOBI'){
            //define('ACCOUNT_ID', $user_symbol_row['uid']); // 你的账户ID
            //define('ACCESS_KEY',$user_symbol_row['apiKey']); // 你的ACCESS_KEY
            //define('SECRET_KEY',$user_symbol_row['secretKey']); // 你的SECRET_KEY
            $model = new HuobiLib();
            $model->ACCOUNT_ID = $user_symbol_row['uid'];
            $model->ACCESS_KEY = $user_symbol_row['apiKey'];
            $model->SECRET_KEY = $user_symbol_row['secretKey'];
            $symbol_key = $user_symbol_row['symbol_key'];
            $symbol_key_arr = explode('_',$symbol_key);
            $rst = $model->get_orders_matchresults($symbol_key_arr[0].$symbol_key_arr[1]);
            $rst = (array)$rst;
            $orders = isset($rst['data'])?$rst['data']:array();

            return $orders ;
        }
    }

    /**
     * 获取用户失败的订单信息
     * @param $user_symbol_row
     */
    public function getFailedOrderListByUserSymbol($user_symbol_row){
        $platform = $user_symbol_row['platform'];
        if($platform=='OKEX'){
            $apiKey = $user_symbol_row['apiKey'];
            $secretKey = $user_symbol_row['secretKey'];
            $client = new \OKCoin(new \OKCoin_ApiKeyAuthentication($apiKey, $secretKey));
            $params['api_key'] = $apiKey;
            $params['symbol'] = $user_symbol_row['symbol_key'];
            $params['status'] = 0 ;
            $params['current_page'] = 1 ;
            $params['page_length'] = 200 ;
            $rst  = $client->orderHistoryApi($params);
            $rst = (array)$rst;

            $orders = isset($rst['orders'])?$rst['orders']:array();
            return $orders ;
        }

        if($platform =='HUOBI'){
            //define('ACCOUNT_ID', $user_symbol_row['uid']); // 你的账户ID
            //define('ACCESS_KEY',$user_symbol_row['apiKey']); // 你的ACCESS_KEY
            //define('SECRET_KEY',$user_symbol_row['secretKey']); // 你的SECRET_KEY
            $model = new HuobiLib();
            $model->ACCOUNT_ID = $user_symbol_row['uid'];
            $model->ACCESS_KEY = $user_symbol_row['apiKey'];
            $model->SECRET_KEY = $user_symbol_row['secretKey'];
            $symbol_key = $user_symbol_row['symbol_key'];
            $symbol_key_arr = explode('_',$symbol_key);
            $rst = $model->get_orders_matchresults($symbol_key_arr[0].$symbol_key_arr[1]);
            $rst = (array)$rst;
            $orders = isset($rst['data'])?$rst['data']:array();

            return $orders ;
        }
    }

    /**
     * 判断是否已经入库
     * @param $order_id
     * @return array|bool
     */
    public function checkExists($order_id){

        $params['cond'] = 'order_id =:order_id';
        $params['args'] = [':order_id'=>$order_id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 同步订单入库
     * @param $order_list
     * @param $user_symbol_row
     */
    public function addSyncOrder($order_list,$user_symbol_row){
        if($order_list){
            foreach($order_list as $v){
                if($user_symbol_row['platform'] =='OKEX'){
                    $v = (array)$v;
                    $order_id = $v['order_id'];
                }

                if($user_symbol_row['platform'] =='HUOBI'){
                    $v = (array)$v;
                    $order_id = $v['order-id'];
                }

                $check_exists = $this->checkExists($order_id);
                if(!$check_exists){
                    if($user_symbol_row['platform'] =='OKEX'){

                        $addData['order_id'] = $order_id ;
                        $addData['price'] = $v['avg_price'] ;
                        $addData['avg_price'] = $v['avg_price'] ;
                        $addData['amount'] = $v['deal_amount'] ;
                        $addData['is_add_extra'] = 0 ;
                        $addData['symbol'] = $user_symbol_row['symbol_key'] ;
                        $addData['type'] = $v['type'] =='buy'|| $v['type'] =='buy_market'?'BUY':'SELL' ;
                        $addData['status'] = $v['status'] ;
                        $addData['create_time'] = date('Y-m-d H:i:s',$v['create_date']/1000) ;
                        $addData['update_time'] = date('Y-m-d H:i:s',$v['create_date']/1000) ;
                        $addData['apiKey'] = $user_symbol_row['apiKey'] ;
                        $addData['secretKey'] = $user_symbol_row['secretKey'] ;
                        $addData['user_symbol_id'] = $user_symbol_row['id'] ;
                        $this->baseInsert(self::tableName(),$addData,'db_okex');
                    }
                    $symbol_key = $user_symbol_row['symbol_key'] ;
                    $symbol_key_arr = explode('_',$symbol_key);
                    $symbol_key_str = $symbol_key_arr[0].$symbol_key_arr[1] ;
                    if($user_symbol_row['platform'] =='HUOBI' && $symbol_key_str==$v['symbol']){
                        $addData['order_id'] = $v['order-id'] ;
                        $addData['price'] = $v['price'] ;
                        $addData['avg_price'] = $v['price'] ;
                        $addData['amount'] = $v['filled-amount'] ;
                        $addData['is_add_extra'] = 0 ;
                        $addData['symbol'] = $user_symbol_row['symbol_key'] ;
                        $addData['type'] = ($v['type'] =='buy-market' || $v['type'] =='buy-limit' ||$v['type'] =='buy-ioc')?'BUY':'SELL' ;
                        $addData['status'] = 2 ;
                        $addData['create_time'] = date('Y-m-d H:i:s',$v['created-at']/1000) ;
                        $addData['update_time'] = date('Y-m-d H:i:s',$v['created-at']/1000) ;
                        $addData['apiKey'] = $user_symbol_row['apiKey'] ;
                        $addData['secretKey'] = $user_symbol_row['secretKey'] ;
                        $addData['user_symbol_id'] = $user_symbol_row['id'] ;
                        $this->baseInsert(self::tableName(),$addData,'db_okex');
                    }

                }
            }
        }
    }


    /**
     * 取消用户订单
     * @param $order_list
     * @param $user_symbol_row
     */
    public function cancelUserOrder($order_list,$user_symbol_row){
        if($order_list){
            foreach($order_list as $v){

                if($user_symbol_row['platform'] =='OKEX'){
                    $v = (array)$v;
                    $order_id = $v['order_id'];
                }

                if($user_symbol_row['platform'] =='HUOBI'){
                    $v = (array)$v;
                    $order_id = $v['order-id'];
                }

                //更新订单状态
                if($user_symbol_row['platform'] =='OKEX') {

                    if ($v['status'] != 2) {

                        //超过3分钟 执行撤单请求
                        $create_date = $v['create_date'] / 1000;
                        $ext = time() - $create_date;
                        if ($ext > 120 && $ext < 210) {
                            $okex_trade = new OkexTrade();
                            $apiKey = $user_symbol_row['apiKey'];
                            $secretKey = $user_symbol_row['secretKey'];
                            $symbol = $user_symbol_row['symbol_key'];
                            $rst = $okex_trade->doCancel($apiKey, $secretKey, $symbol, $order_id);
                            if ($rst) {
                                //删除交易记录
                                $this->baseDelete(self::tableName(), 'order_id=:order_id', [':order_id' => $order_id], 'db_okex');
                            }
                        }
                    }

                }
            }
        }
    }

    /**
     * 同步用户订单信息
     * @param $user_id
     */
    public function syncUserOrder($user_id){

        //获取用户拥有币种
        $symbol_model = new UserSymbol();
        $user_symbol_list = $symbol_model->getUserSymbolListById($user_id);

        if($user_symbol_list){
            foreach($user_symbol_list as $v){
                //同步添加用户订单
                $order_list = $this->getOrderListByUserSymbol($v);
                //if($v['symbol_key'] =='okb_usd')
                $this->addSyncOrder($order_list,$v);
			}
        }
    }


    /**
     * 同步取消订单信息
     * @param $user_id
     */
    public function syncCancelUserOrder($user_id){

        //获取用户拥有币种
        $symbol_model = new UserSymbol();
        $user_symbol_list = $symbol_model->getUserSymbolListById($user_id);

        if($user_symbol_list){
            foreach($user_symbol_list as $v){

                //取消用户订单
                $cancel_order_list = $this->getFailedOrderListByUserSymbol($v);
                $this->cancelUserOrder($cancel_order_list,$v);
            }
        }
    }



    /**
     * 提示用户交易信息
     * @param $user_symbol_list
     */
    public function noticeOrderByUserSymbolList($user_symbol_list){
        if($user_symbol_list){
            foreach($user_symbol_list as $v){

                //step1获取上次交易的类型
                $params['cond'] = 'user_symbol_id =:user_symbol_id AND status !=:status';
                $params['args'] = [':user_symbol_id'=>$v['id'],':status'=>-1];
                $params['orderby'] = 'create_time desc';
                $trade_info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

                if($trade_info['type']=='BUY'){
                    //step2 获取当前币种的价格
                    $common_trade_model = new CommonTrade();
                    $symbol = $trade_info['symbol'];
                    $symbol_arr = explode('_',$symbol);
                    $curr_a = $symbol_arr[0];
                    $common_trade_model->platform_type ='okex';
                    $total_arr = $common_trade_model->getDataByDay($curr_a,'usdt',60,1,'okex');
                    $total_arr = $common_trade_model->formateData($total_arr);

                    $minute = $common_trade_model->getMinuteByGroupSec(60);
                    $time = date('Y-m-d H:'.$minute.':00');
                    $str  = strtotime($time);
                    $price = $total_arr[$str];

                    $order_price = $trade_info['price'];

                    if($price/$order_price >1.02){
                        $percent = ($price/$order_price -1)*100;
                        $message = '售出提醒:币种:'.$symbol.';';
                        $message .= '购入价格:'.$order_price.';';
                        $message .= '当前价格:'.$price.';';
                        $message .="上涨幅度：".$percent.'%';
                        send_dingding_sms($message,'okex');
                    }

                }


            }
        }
    }

}
