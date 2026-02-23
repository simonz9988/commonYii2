<?php

namespace common\models;

use common\components\MyRedis;
use common\components\SpotApi;
use Yii;

/**
 * This is the model class for table "sea_robot_trade_batch".
 *
 * @property int $id
 * @property int $user_id 用户Id
 * @property int $user_platform_id 用户平台配置表ID
 * @property string $cash_balance 支配的现金金额
 * @property string $coin_balance 支配的币种总额
 * @property int $max_level 网格层数
 * @property string $price 单价
 * @property string $ceil_price 价格上限
 * @property string $floor_price 价格下限
 * @property string $min_qty 最小交易颗粒度
 * @property string $step 步长(价格间隔)
 * @property int $status 交易状态
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class RobotTradeBatch extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_trade_batch';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_platform_id', 'max_level', 'status'], 'integer'],
            [['cash_balance', 'coin_balance', 'price', 'ceil_price', 'floor_price', 'min_qty', 'step'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['is_deleted'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user_platform_id' => 'User Platform ID',
            'cash_balance' => 'Cash Balance',
            'coin_balance' => 'Coin Balance',
            'max_level' => 'Max Level',
            'price' => 'Price',
            'ceil_price' => 'Ceil Price',
            'floor_price' => 'Floor Price',
            'min_qty' => 'Min Qty',
            'step' => 'Step',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @return array|bool
     */
    public function getInfoById($id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 执行机器人操作
     */
    public function doRunRobot(){

        $params['cond'] = 'status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':status'=>'BUYING',':is_deleted'=>'N'];
        $params['limit'] = 100 ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return false ;
        }

        foreach($list as $v){

            // 执行操作
            $this->doRunByDetail($v);
        }
    }

    /**
     * 根据具体信息执行操作
     * @param $info
     * @return mixed
     */
    public function doRunByDetail($info){

        $user_platform_obj = new UserPlatform();
        $user_platform_info = $user_platform_obj->getInfoById($info['user_platform_id']);
        if(!$user_platform_info){
            return false ;
        }

        $coin = $user_platform_info['coin'] ;
        $legal_coin = $user_platform_info['legal_coin'] ;
        $platform = $user_platform_info['platform'];

        $coin_obj = new Coin();
        $coin_info = $coin_obj->getInfoByPlatformAndCoin($platform,$coin);

        // 网格数
        $max_level = $info['max_level'];
        // 用户插入的起始加个
        $start_price =  $info['price'];
        $add_step_price = $coin_obj->getAddStepByCoinInfo($coin_info,$legal_coin,$max_level) ;

        //需要的用户最小的资金总额
        $depth_key =  strtolower($legal_coin.'_depth');
        $depth = $coin_info[$depth_key] ;
        $min_total = $coin_obj->getMinBalance($start_price,$max_level,$add_step_price,$depth);

        $coin_balance = $info['cash_balance'] ;
        // 取购买数量倍数
        $beishu =numberSprintf($coin_balance/$min_total,_getFloatLength($depth));

        if($beishu <1){
            return false ;
        }

        // 每笔最低购买数目
        $min_buy_amount = $beishu*$depth;

        // 创建交易快照信息
        $this->createRecord($info,$add_step_price,$max_level,$min_buy_amount);

        // 执行购买操作
        if($user_platform_info['platform'] =='OKEX'){

            //OKEX
            $this->doTradeByBatchIdOkex($info['id']);
        }else{
            //火币
            #TODO 参照上面的方法
        }

    }

    /**
     * 创造快照记录
     * @param $info
     * @param $add_step_price
     * @param $step
     * @param $min_buy_amount
     * @return mixed
     */
    public function createRecord($info,$add_step_price,$step,$min_buy_amount){

        // 判断是否已经插入过
        $user_id = $info['user_id'];
        $user_platform_id = $info['user_platform_id'];


        $params['cond'] = 'user_id=:user_id AND  batch_id=:batch_id';
        $params['args'] = [':user_id'=>$user_id,':batch_id'=>$info['id']];
        $exits_info = $this->findOneByWhere('sea_robot_trade_batch_snapshot',$params,self::getDb());
        if($exits_info){
            return true ;
        }

        $step =  floor($step/2) ;

        $start_price =  $info['price'];

        // 上单边需要的最小交易金额
        for($i=1;$i<=$step;$i++){
            $price = $start_price + $add_step_price*$i ;
            $add_data['user_id'] = $user_id;
            $add_data['user_platform_id'] = $user_platform_id;
            $add_data['batch_id'] = $info['id'];
            $add_data['side'] = 'UP';
            $add_data['price'] = $price;
            $add_data['min_qty'] = $min_buy_amount;
            $add_data['block'] = $i;
            $add_data['status'] = 'BUYING';
            $add_data['is_deleted'] = 'N';
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseInsert('sea_robot_trade_batch_snapshot',$add_data);

        }
        // 下单边需要的交易交易金额
        for($i=1;$i<=$step;$i++){
            $price = ($start_price - $add_step_price*$i) ;
            $add_data['user_id'] = $user_id;
            $add_data['user_platform_id'] = $user_platform_id;
            $add_data['batch_id'] = $info['id'];
            $add_data['side'] = 'DOWN';
            $add_data['price'] = $price;
            $add_data['min_qty'] = $min_buy_amount;
            $add_data['block'] = $i;
            $add_data['status'] = 'BUYING';
            $add_data['is_deleted'] = 'N';
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseInsert('sea_robot_trade_batch_snapshot',$add_data);

        }

    }

    /**
     * 根据批次号ID执行买入和卖出操作
     * @param $batch_id
     * @return mixed
     */
    public function doTradeByBatchIdOkex($batch_id){

        // 查询快照列表
        $snapshot_obj = new RobotTradeBatchSnapshot() ;
        $snapshot_list = $snapshot_obj->getListByBatchId($batch_id);

        if(!$snapshot_list){
            return false ;
        }

        // 获取批次信息
        $batch_info = $this->getInfoById($batch_id);

        // 获取指定平台配置信息
        $user_platform_id = $batch_info['user_platform_id'];
        $user_platform_obj = new UserPlatform() ;
        $user_platform_info = $user_platform_obj->getInfoById($user_platform_id);

        // 将平台订单落盘到本地
        $this->downOkexOrderInfoByPlatformInfo($user_platform_info);

        // 读取当前价格
        $platform_coin_price = $this->getOkexCoinPrice($user_platform_info);

        $spot_obj = new OkexSpotOrder() ;

        foreach($snapshot_list as $v){

            // OKEX暂时不用限速 2s 100个订单
            // step1 查询是否买入成功
            $buy_order_info = $spot_obj->getInfoBySnapshotId($v['id']);

            if(!$buy_order_info){
                // 执行买入操作
                $this->doOkexBuyBySnapshotAndPlatformInfo($v,$user_platform_info,$platform_coin_price);
                continue ;
            }

            //OKEXA81A1612012140399897
            // step2 查询是否已经有对应的卖出订单
            $sell_order_info = $spot_obj->getInfoByBuyClientOid($buy_order_info['client_oid']);
            if(!$sell_order_info){
                $this->doOkexSellByPlatformInfo($user_platform_info,$buy_order_info,$v);
                continue ;
            }

            // step3 查询是否卖出
            if($sell_order_info['state'] ==2){

                #结算收益
                #TODO 根据盈利值来进行计算
                // step4 继续执行买入
                $this->doOkexBuyBySnapshotAndPlatformInfo($v,$user_platform_info,$platform_coin_price);
                continue;
            }


        }
    }

    /**
     * 获取当前币种价格 主要获取卖一价
     * @param $user_platform_info
     * @return mixed
     */
    public function getOkexCoinPrice($user_platform_info){

        $redis_obj = new MyRedis();
        $redis_key = "Price:".$user_platform_info['platform'].$user_platform_info['coin'].$user_platform_info['legal_coin'];
        $redis_info = $redis_obj->get($redis_key);
        if($redis_info){
            return $redis_info ;
        }
        $config['apiKey'] = $user_platform_info['api_key'] ;
        $config['apiSecret'] = $user_platform_info['api_secret'] ;
        $config['passphrase'] = $user_platform_info['passphrase'] ;
        $instrument_id = strtoupper($user_platform_info['instrument_id']) ;
        $obj = new SpotApi($config);
        $info  = $obj->getSpecificTicker($instrument_id);
        $price =  isset($info['best_ask'])?$info['best_ask']: 0 ;
        $redis_obj->set($redis_key,$price,10 );// 设置十秒的缓存
        return $price ;
    }

    /**
     * 根据快照和平台配置信息购买OKex订单
     * @param $snapshot_info
     * @param $user_platform_info
     * @param $platform_coin_price
     * @return mixed
     */
    public function doOkexBuyBySnapshotAndPlatformInfo($snapshot_info,$user_platform_info,$platform_coin_price){

        // 快照请求的价格
        $price = $snapshot_info['price'] ;

        // 由于是撮合交易， 大于的价格暂时不给于挂单
        if($price >$platform_coin_price){
            return false ;
        }

        $config['apiKey'] = $user_platform_info['api_key'] ;
        $config['apiSecret'] = $user_platform_info['api_secret'] ;
        $config['passphrase'] = $user_platform_info['passphrase'] ;
        $instrument_id = strtoupper($user_platform_info['instrument_id']) ;
        $obj = new SpotApi($config);
        $side = 'buy';
        $size = $snapshot_info['min_qty'] ;
        // 平台唯一标识符  平台名称+快照ID
        $client_oid = $user_platform_info['platform'].'A'.$snapshot_info['id'].'A'.time().mt_rand(100000,999999) ;
        $res = $obj->takeOrder($instrument_id, $side, $size, $price, '', $client_oid);

        return $res ;
    }

    /**
     * 根据用户平台信息同步平台订单
     * @param $user_platform_info
     * @return mixed
     */
    public function downOkexOrderInfoByPlatformInfo($user_platform_info){

        // OKEX 平台状态释义
        /*
         * -2:失败
            -1:撤单成功
            0:等待成交
            1:部分成交
            2:完全成交
            3:下单中
            4:撤单中
            6: 未完成（等待成交+部分成交）
            7:已完成（撤单成功+完全成交）
         */

        // 获取未成交订单
        $config['apiKey'] = $user_platform_info['api_key'] ;
        $config['apiSecret'] = $user_platform_info['api_secret'] ;
        $config['passphrase'] = $user_platform_info['passphrase'] ;
        $instrument_id = strtoupper($user_platform_info['instrument_id']) ;
        $obj = new SpotApi($config);
        $order_list = $obj->getOrdersList($instrument_id, 0);
        $order_obj = new OkexSpotOrder();
        $order_obj->downloadByUserPlatformAndList($user_platform_info,$order_list);

        // 获取已成交订单
        $order_list = $obj->getOrdersList($instrument_id, 2);
        $order_obj->downloadByUserPlatformAndList($user_platform_info,$order_list);

        // 获取已成交订单
        $order_list = $obj->getOrdersList($instrument_id, 3);
        $order_obj->downloadByUserPlatformAndList($user_platform_info,$order_list);

    }

    /**
     * 执行OKex的卖出单操作
     * @param $user_platform_info
     * @param $buy_order_info
     * @param $snapshot_info
     * @return mixed
     */
    public function doOkexSellByPlatformInfo($user_platform_info,$buy_order_info,$snapshot_info){

        $config['apiKey'] = $user_platform_info['api_key'] ;
        $config['apiSecret'] = $user_platform_info['api_secret'] ;
        $config['passphrase'] = $user_platform_info['passphrase'] ;
        $instrument_id = strtoupper($user_platform_info['instrument_id']) ;
        $obj = new SpotApi($config);
        $side = 'sell';
        $size = $buy_order_info['size'] ;
        $price_avg = $buy_order_info['price_avg'] ;
        if(!$price_avg){
            return false ;
        }
        // 平台唯一标识符  平台名称+快照ID
        $client_oid = $user_platform_info['platform'].'A'.$snapshot_info['id'].'A'.time().mt_rand(100000,999999).'ASELL' ;

        $price = ($user_platform_info['gap_percent']/100 +1)*$price_avg ;
        $price = numberSprintf($price,_getFloatLength($price_avg));
        $res = $obj->takeOrder($instrument_id, $side, $size, $price, '', $client_oid);
    }
}
