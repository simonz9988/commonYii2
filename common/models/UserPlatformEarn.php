<?php

namespace common\models;

use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sea_user_platform_earn".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $platform 平台信息(OKEX/HUOBI)
 * @property string $instrument_id 所选币种(XRP-USDT)
 * @property string $order_id 对应平台订单表ID
 * @property string $total 交易费率
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class UserPlatformEarn extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_user_platform_earn';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['total'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['platform', 'instrument_id', 'order_id'], 'string', 'max' => 100],
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
            'platform' => 'Platform',
            'instrument_id' => 'Instrument ID',
            'order_id' => 'Order ID',
            'total' => 'Total',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据币种来结算盈利信息
     * @param $platform
     * @param $user_id
     * @param $coin
     * @param $legal_coin
     * @return mixed
     */
    public function dealEarn($platform,$user_id,$coin='',$legal_coin=''){

        // 先处理同步指定用户的订单信息
        $platform = strtoupper($platform);

        if($platform =='OKEX'){
            // step1  同步订单
            $order_obj = new RobotOkexSpotOrder();
            $order_obj->downloadOrderByUserPlatform($user_id,$platform,$coin,$legal_coin);

            // 处理收益
            $this->dealOkexEarn($platform,$user_id,$coin,$legal_coin);
        }
    }

    /**
     * 处理盈利信息
     * @param $platform
     * @param $user_id
     * @param $coin
     * @param $legal_coin
     * @return bool
     */
    public function dealOkexEarn($platform,$user_id,$coin,$legal_coin){

        $params['cond'] = 'is_deal =:is_deal AND user_id=:user_id';
        $params['args'] = [':is_deal'=>'N',':user_id'=>$user_id];

        if($coin && $legal_coin){
            $params['cond'] .= ' AND instrument_id =:instrument_id ';
            $params['args'][':instrument_id'] = strtoupper($coin.'-'.$legal_coin);
        }
        $params['orderby'] = 'created_at ASC';
        $params['limit'] = 100;
        if($platform =='OKEX'){
            $order_table_name = 'sea_robot_okex_spot_order';
        }else{
            #TODO 需要新增火币相关联表格
            $order_table_name = 'sea_robot_okex_spot_order';
        }

        $balance_table_name = 'sea_user_platform_balance';
        $earn_table_name = 'sea_user_platform_earn';
        $list = $this->findAllByWhere($order_table_name,$params,self::getDb()) ;

        if(!$list){
            return false ;
        }

        $now = date('Y-m-d H:i:s');

        $balance_obj = new UserPlatformBalance();

        foreach($list as $v){

            // 查询有没有指定的balance信息
            $user_id = $v['user_id'];
            $instrument_id = $v['instrument_id'];
            $balance_info = $balance_obj->checkExistsByUserAndPlatform($user_id,$platform,$instrument_id);

            if($v['side'] =='buy'){
                if(!$balance_info){
                    // insert
                    $add_data['user_id'] = $user_id ;
                    $add_data['platform'] = $platform ;
                    $add_data['instrument_id'] = $instrument_id ;
                    $add_data['avg_price'] = ($v['filled_notional']*$v['filled_size'] + $v['usdt_fee'])/$v['filled_size'] ;
                    $add_data['left_num'] = $v['filled_size'] ;
                    $add_data['create_time'] = $now ;
                    $add_data['modify_time'] = $now ;
                    $this->baseInsert($balance_table_name,$add_data) ;
                }else{

                    $left_num = $balance_info['left_num']+$v['filled_size'] ;
                    $avg_price = ($balance_info['avg_price'] * $balance_info['left_num'] + $v['filled_notional']*$v['filled_size'] + $v['usdt_fee'] ) / $left_num;
                    $buy_update_data['avg_price'] = $avg_price ;
                    $buy_update_data['left_num'] = $left_num ;
                    $buy_update_data['modify_time'] = $now ;
                    $this->baseUpdate($balance_table_name,$buy_update_data,'id=:id',[':id'=>$balance_info['id']]);
                }

            }else{
                if($balance_info){

                    // 当前剩余总额
                    $total =  $balance_info['left_num'] * $balance_info['avg_price'] ;
                    // 需要扣减卖出 再扣减掉手续费
                    $total = $total -  $v['filled_notional']*$v['filled_size'] + $v['usdt_fee'] ;
                    $left_num = $balance_info['left_num'] - $v['filled_size'] ;
                    $avg_price = $total / $left_num ;
                    $sell_update_data['avg_price'] = $avg_price ;
                    $sell_update_data['left_num'] = $left_num ;
                    $sell_update_data['modify_time'] = $now ;
                    $this->baseUpdate($balance_table_name,$sell_update_data,'id=:id',[':id'=>$balance_info['id']]) ;

                    $earn = ($v['filled_notional'] - $balance_info['avg_price']) *  $v['filled_size'] ;
                    $earn_add_data['total'] = $earn ;
                    $earn_add_data['user_id'] = $user_id ;
                    $earn_add_data['platform'] = $platform ;
                    $earn_add_data['instrument_id'] = $instrument_id ;
                    $earn_add_data['order_id'] = $v['order_id'] ;
                    $earn_add_data['timestamp'] = $v['timestamp'] ;
                    $earn_add_data['date'] = date('Y-m-d',$v['timestamp']) ;
                    $earn_add_data['create_time'] = $now ;
                    $earn_add_data['modify_time'] = $now ;
                    $this->baseInsert($earn_table_name,$earn_add_data) ;
                }
            }

            // 直接处理为已完成
            $order_update_data['is_deal'] = 'Y';
            $order_update_data['modify_time'] = $now ;
            $this->baseUpdate($order_table_name,$order_update_data,'id=:id',[':id'=>$v['id']]);

        }

        return true ;
    }

    /**
     * 获取指定时间的总收益
     * @param $user_id
     * @param string $start_time
     * @param string $end_time
     * @return int
     */
    public function getTotalByTime($user_id,$start_time='',$end_time=''){

        $cond[] = 'user_id=:user_id' ;
        $params['args'][':user_id'] = $user_id ;
        if($start_time){
            $cond[] = 'create_time >=:start_time';
            $params['args'][':start_time'] = $start_time ;
        }

        if($end_time){
            $cond[] = 'create_time <=:end_time';
            $params['args'][':end_time'] = $end_time ;
        }

        $params['cond'] = implode(' AND ',$cond) ;
        $params['fields']= 'sum(total) as total_earn';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_earn']) ? $info['total_earn']:0;
    }
}
