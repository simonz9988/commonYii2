<?php

namespace common\models;

use TencentCloud\Iot\V20180123\Models\Product;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "sea_robot_energy_block_order".
 *
 * @property int $id
 * @property int $product_id 产品ID
 * @property int $parent_product_id 拆分的父类的产品ID
 * @property string $product_root_path 对应的产品ID路径
 * @property int $level 级别
 * @property string $token_price 代币价格(当前为SCA价格)
 * @property string $usdt_price 对应的USDT价格
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class RobotEnergyBlockOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_energy_block_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id', 'parent_product_id', 'level'], 'integer'],
            [['product_root_path'], 'string'],
            [['token_price', 'usdt_price'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
            'parent_product_id' => 'Parent Product ID',
            'product_root_path' => 'Product Root Path',
            'level' => 'Level',
            'token_price' => 'Token Price',
            'usdt_price' => 'Usdt Price',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 获取当前SCA的价格
     * @param $earn_percent
     * @return mixed
     */
    public function returnCurrentScaPrice($earn_percent){
        $site_config_obj = new SiteConfig();
        $sca_start_date = $site_config_obj->getByKey('sca_start_date');
        $ext_days = (strtotime(date('Y-m-d')) - strtotime($sca_start_date))/86400 ;
        $ext_days = $ext_days + 1 ;

        $earn_percent = $earn_percent/100;
        $res = pow((1+$earn_percent),$ext_days);
        return numberSprintf($res,6);
    }

    /**
     * 根据USDT获取SCA需要的数量
     * @param $num
     * @param $earn_percent
     * @return string
     */
    public function returnScaNumByUsdtNum($num,$earn_percent){

        $sca_price = $this->returnCurrentScaPrice($earn_percent);
        $res = $num /$sca_price;
        return numberSprintf($res,6);
    }

    /**
     * 根据价格和商品信息获取指定的手续费
     * @param $usdt_price
     * @param $product_info
     * @return mixed
     */
    public function returnHandlingFee($usdt_price,$product_info){

        $handling_fee = $product_info ? $product_info['handling_fee'] : 0 ;
        $handling_fee = $handling_fee/100;
        $usdt_price = $usdt_price*$handling_fee ;
        return numberSprintf($usdt_price,6);
    }

    /**
     * 根据USDT实际的价值
     * @param $usdt_price
     * @param $order_info
     * @param $earn_percent
     * @return mixed
     */
    public function returnRealUsdtPrice($usdt_price,$order_info,$earn_percent){

        // 计算订单开始时间
        $create_start_time = date("Y-m-d 00:00:00",strtotime($order_info['create_time'])) ;
        $create_start_time = strtotime($create_start_time);
        $ext_days = (strtotime(date('Y-m-d')) - $create_start_time)/86400 ;

        $earn_percent = $earn_percent/100;
        $res = pow((1+$earn_percent),$ext_days);
        $res = $res * $usdt_price ;
        return numberSprintf($res,6) ;
    }

    /**
     * 计算前一天的世纪总价值
     * @param $usdt_price
     * @param $order_info
     * @param $earn_percent
     * @return string
     */
    public function returnPrevDayRealUsdtPrice($usdt_price,$order_info,$earn_percent){
        // 计算订单开始时间
        $create_start_time = date("Y-m-d 00:00:00",strtotime($order_info['create_time'])) ;
        $create_start_time = strtotime($create_start_time);
        $ext_days = (strtotime(date('Y-m-d')) - $create_start_time - 86400)/86400 ;

        $ext_days = $ext_days;

        $earn_percent = $earn_percent/100;
        $res = pow((1+$earn_percent),$ext_days);
        $res = $res * $usdt_price ;
        return numberSprintf($res,6) ;
    }

    /**
     * 获取昨天USDT的真实价值
     * @param $usdt_price
     * @param $order_info
     * @param $earn_percent
     * @return mixed
     */
    public function returnRealEarnUsdt($usdt_price,$order_info,$earn_percent){

        $prev_day_time = date("Y-m-d 00:00:00",time()-86400);
        $create_start_time = date("Y-m-d 00:00:00",strtotime($order_info['create_time'])) ;
        if($prev_day_time < $create_start_time){
            // 代表是当天买入当天卖出
            return  0 ;
        }

        // 计算订单开始时间
        $ext_days = (strtotime(date('Y-m-d')) - strtotime($order_info['create_time']))/86400 ;
        $ext_days = $ext_days + 1 ;

        $earn_percent = $earn_percent/100;
        $res = pow((1+$earn_percent),$ext_days);
        $res = $res * $usdt_price ;
        return numberSprintf($res,6) ;
    }




    // 购买原始能量块
    public function doBuyByOriginal($product_id,$user_id,$cash_password){

        // 判断是否已经购买过矿机
        $robot_orders_obj = new RobotOrders();
        $order_num = $robot_orders_obj->getTotalNumByUserId($user_id);
        if($order_num <=0){
            $this->setError('200063');
            return false ;
        }

        // 查询 余额
        $robot_balance_obj = new RobotUserBalance();
        $balance_info = $robot_balance_obj->getInfoByCoin($user_id,'SCA');

        // 查询产品信息
        $product_obj = new RobotEnergyBlockProduct();
        $product_info = $product_obj->getInfoById($product_id);
        if(!$product_info){
            $this->setError('200043');
            return false ;
        }

        $member_obj = new Member();
        $user_info = $member_obj->getUserInfoById($user_id);
        if($user_info['cash_password'] != md5($cash_password)){
            $this->setError('200051');
            return false ;
        }

        $balance = $balance_info ? $balance_info['total'] : 0 ;

        $usdt_price = $product_info['usdt_price'];

        $sca_price = $this->returnScaNumByUsdtNum($usdt_price,$product_info['contract_income_ratio']);

        if($balance < $sca_price){
            // 余额不足
            $this->setError('200044');
            return false ;
        }

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            // 扣减余额
            $balance_update_data['total'] = new Expression('total -'.$sca_price);
            $balance_update_data['modify_time'] = date('Y-m-d H:i:s');
            $balance_cond = 'id=:id AND total -'.$sca_price.' > 0' ;
            $robot_balance_obj->baseUpdate($robot_balance_obj::tableName(),$balance_update_data,$balance_cond,[':id'=>$balance_info['id']]);

            // 扣减流水
            $record_obj = new RobotUserBalanceRecord();
            $record_obj->addByBuyOriginalBlock($user_id,'SCA',$sca_price);

            // 增加订单
            $add_data['product_id'] = $product_info['id'];
            $add_data['user_id'] = $user_id;
            $add_data['parent_product_id'] = 0;
            $add_data['product_root_path'] = '--0--';
            $add_data['level'] = $product_info['level'];
            $add_data['token_price'] = $sca_price;
            $add_data['usdt_price'] = $product_info['usdt_price'];
            $add_data['original_usdt_price'] = $product_info['usdt_price'];
            $add_data['status'] = 'SELLING';// 自动上架
            $add_data['type'] = 'ORIGINAL';
            $add_data['max_usdt'] = $product_info['max_usdt'];
            $add_data['buffer_days'] = $product_info['buffer_days'];
            $add_data['contract_income_ratio'] = $product_info['contract_income_ratio'];
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');

            $transaction_time = $product_info['transaction_time'];
            $transaction_time = explode('-',$transaction_time);
            $add_data['start_time'] = $transaction_time[0];
            $add_data['end_time'] = $transaction_time[1];
            $this->baseInsert(self::tableName(),$add_data);

            $transaction->commit();
            return true ;
        } catch (\Exception $e) {
            $this->setError('200045');
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 获取总数目
     * @param $user_id
     * @param $is_all
     * @return mixed
     */
    public function getSellingTotalNum($user_id,$is_all=true){
        $params['cond'] = ' user_id !=:user_id AND status in ("SELLING","BIDED") ';
        $params['args'] = [':user_id'=>$user_id];
        //$params = $this->returnBaseSearch($user_id,$is_all);
        $params['fields'] = "count(1) as total";
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ?$info['total']: 0;
    }

    /**
     * 获取出售中的列表
     * @param $user_id
     * @param $page
     * @param $page_num
     * @param $order_field
     * @return mixed
     */
    public function getSellingBlockList($user_id,$page,$page_num,$order_field){

        $params['cond'] = ' user_id !=:user_id AND status in ("SELLING","BIDED")  ';
        $params['args'] = [':user_id'=>$user_id];
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;
        $params['orderby'] = $order_field;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if(!$list){
            return [] ;
        }

        $res = [] ;
        $product_list = [] ;
        foreach($list as $v){

            $product_info = isset($product_list[$v['product_id']]) ? $product_list[$v['product_id']] : [];
            if(!$product_info){
                $product_obj = new RobotEnergyBlockProduct();
                $product_info = $product_obj->getInfoById($v['product_id']);
                $product_list[$v['product_id']] = $product_info ;
            }

            //获取usdt的实际价值 每日有增量
            $v['usdt_price'] = $this->returnRealUsdtPrice($v['usdt_price'],$v,$v['contract_income_ratio']);

            $sca_price = $this->returnScaNumByUsdtNum($v['usdt_price'],$v['contract_income_ratio']) ;

            // 判断是是否为直接购买
            $now = date("H:i");
            $is_selling = false ;
            if($now >=$v['start_time'] && $now <=$v['end_time'] ){

                // 判断是否
                $is_selling = true ;
            }

            // 判断是否可以预约
            $item = [
                'transaction_time' => $product_info['transaction_time'],
                'id' => $v['id'],
                'level' => $product_info['level'],
                'sca_price' => $sca_price,
                'usdt_price' => $v['usdt_price'],
                'contract_income_ratio' => numberSprintf($v['contract_income_ratio'],0).'%',
                'is_reserve'    => $v['buy_user_id'] >0?false:true,
                'contract_days' => $product_info['contract_days'],
                'handling_fee'  =>$this->returnHandlingFee($v['usdt_price'],$product_info),
                'is_selling'  => $is_selling,
            ];

            $res[] = $item ;
        }

        return $res ;
    }

    /**
     * 根据ID获取指定信息
     * @param $id
     * @param string $fields
     * @return array|bool
     */
    public function getInfoById($id,$fields="*"){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields']= $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 预约能量块
     * @param $id
     * @param $cash_password
     * @param $buy_user_id
     * @return mixed
     */
    public function doBid($id,$cash_password,$buy_user_id){

        // 判断是否已经购买过矿机
        $robot_orders_obj = new RobotOrders();
        $order_num = $robot_orders_obj->getTotalNumByUserId($buy_user_id);
        if($order_num <=0){
            $this->setError('200063');
            return false ;
        }

        $member_obj = new Member();
        $user_info = $member_obj->getUserInfoById($buy_user_id);
        if($user_info['cash_password'] != md5($cash_password)){
            $this->setError('200051');
            return false ;
        }

        // 判断判断是否为自己的
        $order_info = $this->getInfoById($id);
        if(!$order_info){
            $this->setError('200046');
            return false ;
        }

        // 判断是否为出售中的
        if($order_info['status'] != "SELLING"){
            $this->setError('200047');
            return false ;
        }

        if($order_info['user_id'] == $buy_user_id){
            $this->setError('200049');
            return false ;
        }

        // 判断是否只购买level one的矿机
        $robot_order_obj = new RobotOrders();
        $only_level_one = $robot_order_obj->checkIsOnlyBuyLevelOne($buy_user_id);
        if($only_level_one){
            if($order_info['original_usdt_price'] > 100){

                $this->setError('200093');
                return false ;
            }
        }

        // 判断是否在预约的时间
        if(date("H:i") >= $order_info['start_time'] &&date("H:i") <=$order_info['end_time']  ){
            //直接购买
            return $this->doByDirect($order_info,$buy_user_id);

        }else{
            //预约购买
            return $this->doByBid($order_info,$buy_user_id);
        }

    }

    /**
     * 是
     * @param $order_info
     * @return mixed
     */
    public function getRealEarn($order_info){

    }
    /**
     * 直接购买
     * @param $order_info
     * @param $buy_user_id
     * @return bool
     * @throws \yii\db\Exception
     */
    private function doByDirect($order_info,$buy_user_id){
        $product_obj = new RobotEnergyBlockProduct();
        $product_info = $product_obj->getInfoById($order_info['product_id']);
        // 判断余额是否可以支付定金
        $balance_obj = new RobotUserBalance();
        $balance_info = $balance_obj->getInfoByCoin($buy_user_id,'USDT');
        $usdt_balance = $balance_info ? $balance_info['total'] : 0;

        // 实际需要支付的能量块的价格
        $total_amount = $order_info['usdt_price'] ;
        $total_amount = $this->returnRealUsdtPrice($total_amount,$order_info,$product_info['contract_income_ratio']);
        // 前一天的
        $prev_day_total_amount = $this->returnPrevDayRealUsdtPrice($order_info['usdt_price'],$order_info,$product_info['contract_income_ratio']);

        // 获取得到
        if($usdt_balance < $total_amount){
            $this->setError('200050');
            return false ;
        }

        // 实际卖家的用户ID
        $sell_user_id = $order_info['user_id'];

        // 判断是否为当前买当天卖
        $is_today = false ;
        $create_time = $order_info['create_time'];
        if(date("Y-m-d") == date("Y-m-d",strtotime($create_time))){
            $is_today = true ;
        }


        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            $now = date('Y-m-d H:i:s');
            // 进行更新
            $order_update_data['buy_user_id'] = $buy_user_id ;
            $order_update_data['status'] = 'SELLED' ;
            $order_update_data['bid_time'] = $now;
            $order_update_data['modify_time'] = $now;
            $order_cond = "id=:id AND status=:status";
            $order_args =[':id'=>$order_info['id'],":status"=>'SELLING'];
            $this->baseUpdate(self::tableName(),$order_update_data,$order_cond,$order_args);

            $user_update_data['balance'] = new Expression('balance - '.$total_amount);
            $user_update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate('sea_user',$user_update_data,'id=:id AND balance - '.$total_amount.' >=0',[':id'=>$buy_user_id]);

            $balance_update_data['total'] = new Expression('total - '.$total_amount);
            $balance_update_data['modify_time'] = date('Y-m-d H:i:s');
            $balance_obj->baseUpdate($balance_obj::tableName(),$balance_update_data,'id=:id AND total - '.$total_amount.' >=0',[':id'=>$balance_info['id']]);

            // 插入资金操作快照
            $balance_record_obj = new RobotUserBalanceRecord();
            $balance_record_obj->addByDirectBuyBlock($buy_user_id,'USDT',$total_amount);

            // 同时新增买入记录
            $add_data['product_id'] = $product_info['id'] ;
            $add_data['user_id'] = $buy_user_id ;
            $add_data['buy_user_id'] = 0 ;
            $add_data['parent_product_id'] = $order_info['id'] ;
            $add_data['product_root_path'] = $order_info['product_root_path'].$order_info['id'].'--' ;
            $add_data['level'] = $order_info['level'] ;
            $add_data['token_price'] = $order_info['token_price'] ;
            $add_data['usdt_price'] = $total_amount;
            $add_data['original_usdt_price'] = $order_info['original_usdt_price'] ;
            $add_data['status'] = 'PENDING' ;
            $add_data['type'] = 'PRODUCT' ;// 换购得来
            $add_data['max_usdt'] = $order_info['max_usdt'] ;
            $add_data['buffer_days'] = $order_info['buffer_days'] ;
            $add_data['contract_income_ratio'] = $order_info['contract_income_ratio'] ;
            $add_data['start_time'] = $order_info['start_time'] ;
            $add_data['end_time'] = $order_info['end_time'] ;
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseInsert(self::tableName(),$add_data);


            // 计算用户入金
            // 获取前一天的价格
            if($is_today){
                $earn_total = $total_amount ;
            }else{
                $site_config_obj = new SiteConfig();
                $sca_sell_earn_percent = $site_config_obj->getByKey('sca_sell_earn_percent');
                $sca_sell_earn_percent = $sca_sell_earn_percent/100;
                $earn_total = $prev_day_total_amount * (1+$sca_sell_earn_percent);
                $earn_total = numberSprintf($earn_total,6);
            }

            $sell_user_update_data['balance'] = new Expression('balance +' . $earn_total);
            $sell_user_update_data['modify_time'] = date('Y-m-d H:i:s');

            $this->baseUpdate('sea_user',$sell_user_update_data,'id=:id',[':id'=>$sell_user_id]);

            // 插入资金流水
            $balance_record_obj->addBySellBlock($sell_user_id,'USDT',$earn_total);

            // 增加余额表记录值
            $balance_obj = new RobotUserBalance();
            $balance_obj->addByCashIn($sell_user_id,$earn_total);

            # 团队总额 当天原始能量块卖出是不产生收益的
            if(!$is_today){
                $sca_team_earn_percent = $site_config_obj->getByKey('sca_team_earn_percent');
                $sca_team_earn_percent = $sca_team_earn_percent/100;
                $team_earn_total =  $prev_day_total_amount * $sca_team_earn_percent;
                $team_earn_total = numberSprintf($team_earn_total,6);

                $team_obj = new RobotTeamTotal();
                $team_obj->addSnapshot($sell_user_id,$team_earn_total,$total_amount);

            }

            $transaction->commit();
            return true ;
        } catch (\Exception $e) {

            $this->setError('200041');
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 预约购买
     * @param $order_info
     * @param $buy_user_id
     * @return bool
     * @throws \yii\db\Exception
     */
    private function doByBid($order_info,$buy_user_id){
        $product_obj = new RobotEnergyBlockProduct();
        $product_info = $product_obj->getInfoById($order_info['product_id']);

        // 计算手续费
        $handling_fee = $this->returnHandlingFee($order_info['usdt_price'],$product_info);

        // 判断余额是否可以支付定金
        $balance_obj = new RobotUserBalance();
        $balance_info = $balance_obj->getInfoByCoin($buy_user_id,'USDT');
        $usdt_balance = $balance_info ? $balance_info['total'] : 0;
        if($usdt_balance < $handling_fee){
            $this->setError('200050');
            return false ;
        }


        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            $now = date('Y-m-d H:i:s');
            // 进行更新
            $order_update_data['buy_user_id'] = $buy_user_id ;
            $order_update_data['status'] = 'BIDED' ;
            $order_update_data['bid_time'] = $now;
            $order_update_data['modify_time'] = $now;
            $order_cond = "id=:id AND status=:status";
            $order_args =[':id'=>$order_info['id'],":status"=>'SELLING'];
            $this->baseUpdate(self::tableName(),$order_update_data,$order_cond,$order_args);

            $user_update_data['balance'] = new Expression('balance - '.$handling_fee);
            $user_update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate('sea_user',$user_update_data,'id=:id AND balance - '.$handling_fee.' >=0',[':id'=>$buy_user_id]);

            $balance_update_data['total'] = new Expression('total - '.$handling_fee);
            $balance_update_data['modify_time'] = date('Y-m-d H:i:s');
            $balance_obj->baseUpdate($balance_obj::tableName(),$balance_update_data,'id=:id AND total - '.$handling_fee.' >=0',[':id'=>$balance_info['id']]);

            // 插入资金操作快照
            $balance_record_obj = new RobotUserBalanceRecord();
            $balance_record_obj->addByBidBlock($buy_user_id,'USDT',$handling_fee);
            $transaction->commit();
            return true ;
        } catch (\Exception $e) {

            $this->setError('200041');
            $transaction->rollBack();
            return false;
        }
    }

    /**
     * 获取查询列表基本查询条件
     * @param $user_id
     * @param $is_all
     * @return  mixed
     */
    private function returnBaseSearch($user_id,$is_all){

        $params['cond'] = "user_id =:user_id AND status != :status AND status !=:status2 ";
        $params['args'] = [':user_id'=>$user_id,':status'=>'EXPLODE',':status2'=>'SELLED'];

        if($is_all){
            $params['cond'] = " ( user_id =:user_id or buy_user_id=:user_id ) AND status != :status";
            $params['args'] = [':user_id'=>$user_id,':status'=>'EXPLODE'];
        }
        $params['orderby'] = 'id desc';
        return $params ;
    }

    /**
     * 获取预估价值
     * @param $usdt_price
     * @param $order_info
     */
    public function returnYuguUsdt($order_info,$sca_earn_percent){

        // 先计算到前一天的价值
        $order_date = date("Y-m-d",strtotime($order_info['create_time']));
        if($order_date == date("Y-m-d")){
            $usdt_price = $order_info['usdt_price'];
            //$usdt_price = $usdt_price*(1+$sca_earn_percent/100);
            return numberSprintf($usdt_price,6);
        }


        // 计算到昨天的价格
        $create_start_time = date("Y-m-d 00:00:00",strtotime($order_info['create_time'])) ;
        $create_start_time = strtotime($create_start_time);
        $ext_days = (strtotime(date('Y-m-d')) - $create_start_time -86400 )/86400 ;

        $usdt_price = $order_info['usdt_price'];
        $earn_percent = $order_info['contract_income_ratio'];
        $earn_percent = $earn_percent/100;
        $res = pow((1+$earn_percent),$ext_days);

        $res = $res * $usdt_price ;

        //预估明天的价值
        $res =  $res*(1+$sca_earn_percent/100);

        $res = numberSprintf($res,6);
        return $res > $order_info['max_usdt'] ? $order_info['max_usdt'] : $res ;
    }

    /**
     * @param $user_id
     * @param $is_all
     * @return mixed
     */
    public function getUserOrderTotalNum($user_id,$is_all){
        $params = $this->returnBaseSearch($user_id,$is_all);
        $params['fields'] = "count(1) as total";
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ?$info['total']: 0;
    }

    /**
     * 获取用户所有的订单列表
     * @param $user_id
     * @param $page
     * @param $page_num
     * @param $is_all
     */
    public function getUserOrderList($user_id,$page,$page_num,$is_all =true){
        $product_obj = new RobotEnergyBlockProduct();

        $res = [] ;
        if(!$is_all && $page == 1){
            // 需要把拆解的统一展示进行拼装
            // 固定在第一页展示
            $implode_obj = new  RobotImplodeRecord();
            $implode_list = $implode_obj->getListByUserId($user_id);
            if($implode_list){
                foreach($implode_list as $v){

                    $product_id = $v['product_id'];
                    $product_info = $product_obj->getInfoById($product_id);

                    $item['id'] =  "implode_".$v['id'] ;
                    $item['type'] = "SELL" ;
                    $item['level'] = $product_info['level'];
                    $item['usdt_price'] = $v['balance'];
                    $sca_price = $this->returnScaNumByUsdtNum($item['usdt_price'],$product_info['contract_income_ratio']) ;
                    $item['sca_price'] = $sca_price;
                    $item['max_usdt_price'] = $v['max_usdt'];// 获取预估收益
                    $item['order_date'] = date("Y-m-d",strtotime($v['create_time']));
                    $item['contract_days'] = $product_info ? $product_info['contract_days'] :'';
                    $item['contract_income_ratio'] = intval($product_info['contract_income_ratio']).'%';
                    $item['status'] = "NOTHING";

                    $left_seconds=0 ;
                    $item['left_seconds'] = $left_seconds ;
                    $item['is_implode'] = true ;
                    $res[] = $item ;
                }
            }
        }

        $params = $this->returnBaseSearch($user_id,$is_all);
        $params['page']['curr_page']= $page ;
        $params['page']['page_num']= $page_num ;

        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return $list ;
        }

        $site_config_obj = new SiteConfig();
        $sca_sell_earn_percent = $site_config_obj->getByKey('sca_sell_earn_percent');


        $product_list = [];
        foreach($list as $v){

            $type = $v['user_id'] == $user_id ?'SELL':"BUY";
            $item['id'] = $v['id'] ;
            $item['type'] = $type ;
            $item['level'] = $v['level'];
            $item['usdt_price'] = $this->returnRealUsdtPrice($v['usdt_price'],$v,$v['contract_income_ratio']);
            $sca_price = $this->returnScaNumByUsdtNum($item['usdt_price'],$v['contract_income_ratio']) ;
            $item['sca_price'] = $sca_price;
            $item['max_usdt_price'] = $this->returnYuguUsdt($v,$sca_sell_earn_percent);// 获取预估收益
            $item['order_date'] = date("Y-m-d",strtotime($v['create_time']));

            $product_id = $v['product_id'];
            $product_info = isset($product_list[$product_id]) ? $product_list[$product_id] : [];
            if(!$product_info){
                $product_info = $product_obj->getInfoById($product_id);
                $product_list[$product_id] = $product_info ;
            }


            $item['contract_days'] = $product_info ? $product_info['contract_days'] :'';
            $item['contract_income_ratio'] = intval($v['contract_income_ratio']).'%';
            $item['status'] = $this->getOrderStatus($v,$type);

            $left_seconds=0 ;
            $status = $item['status'];
            if($status=="TO_PAY"){

                $start_time = $v['start_time'];
                $end_time = $v['start_time'];
                $now = date("H:i");
                if($now >=$end_time ){
                    $left_seconds = strtotime(date("Y-m-d ".$start_time.':0'))+86400 -time();
                }else{
                    $left_seconds =strtotime(date("Y-m-d ".$start_time.':0')) -time();
                }
            }

            $item['left_seconds'] = $left_seconds ;
            $item['is_implode'] = false ;
            $res[] = $item ;
        }


        return $res ;
    }

    /**
     * 判断是不是当天预约
     * @param $order_info
     * @return mixed
     */
    public function checkIsTodayBidByOrderInfo($order_info){

        $order_date = date('Y-m-d',strtotime($order_info['bid_time']));
        if($order_date == date("Y-m-d")){
            return true ;
        }else{
            return false ;
        }
    }

    /**
     * 判断是否当天创建
     * @param $order_info
     * @return bool
     */
    public function checkIsTodayCreateByOrderInfo($order_info){

        $order_date = date('Y-m-d',strtotime($order_info['create_time']));
        if($order_date == date("Y-m-d")){
            return true ;
        }else{
            return false ;
        }
    }

    /**
     * 判断是否当天创建
     * @param $order_info
     * @return bool
     */
    public function checkIsPrevDayCreateByOrderInfo($order_info){

        $create_time = strtotime($order_info['create_time']) ;
        $prev_start_time = strtotime(date("Y-m-d 00:00:00",time()-86400));
        $prev_end_time = strtotime(date("Y-m-d 23:59:59",time()-86400));
        if($create_time >=$prev_start_time && $create_time <= $prev_end_time){
            return true ;
        }else{
            return false ;
        }
    }

    /**
     * 获取订单状态
     * @param $order_info
     * @param $type
     * @return mixed
     */
    public function getOrderStatus($order_info,$type){

        /*TO_PAY-预约成功  OUT_TIME-付款超时(买家状态) CONFIRM-超时投诉(卖家状态)
        PAYING-去支付 WAITING_PAYING-等待支付(卖家状态)
         SELLING-预约中 SELLED-已出售(卖家) BUYED-购买成功（买家）
        FAILED-流拍（卖家） NOTHING-没有任何操作*/

        $start_time = $order_info['start_time'] ;
        $end_time = $order_info['end_time'] ;

        $now_minute = date("H:i") ;

        if($order_info['status'] =='BIDED'){
            $bid_time = $order_info['bid_time'];

            //判断时间是是不是当天
            $is_today_bid = $this->checkIsTodayBidByOrderInfo($order_info);

            $status = "NOTHING" ;
            if($is_today_bid){
                // 判断判断是否在活动时间之内
                if($now_minute < $start_time){
                    $status =   $type=="BUY"?"TO_PAY":"NOTHING";
                }else if($now_minute>=$start_time && $now_minute <=$end_time){
                    $status =  "PAYING";
                }else if($now_minute > $end_time){

                    // 区分情况  判断预约是否为结束时间之后去预约的
                    if(date("H:i",strtotime($bid_time)) > $end_time){
                        $status =  "TO_PAY";
                    }else{
                        $status = "OUT_TIME";
                    }

                }


            }else{

                // 区分情况  判断预约是否为结束时间之后去预约的
                if($now_minute < $start_time){
                    $status =  "TO_PAY";
                }else if($now_minute>=$start_time && $now_minute <=$end_time){
                    $status =  "PAYING";
                }else if($now_minute > $end_time){
                    $status = "OUT_TIME";
                }

            }

            if($status =="TO_PAY"){
                return  "TO_PAY" ;
            }else if($status =="PAYING"){
                return  $type =="BUY"?"PAYING":"WAITING_PAYING";
            }else if($status =="OUT_TIME"){
                return  $type == "BUY" ? "OUT_TIME":"CONFIRM";
            }

            return "NOTHING";

        }else if($order_info['status'] =='SELLING'){

            $is_today_create = $this->checkIsTodayCreateByOrderInfo($order_info);
            if($is_today_create){
                if(date("H:i") > $order_info['end_time']){
                    if($order_info['buy_user_id']>0){
                        return  "FAILED" ;
                    }else{
                        return  'SELLING';
                    }

                }else{
                    return "SELLING";
                }
            }else{

                if($order_info['buy_user_id']>0){
                    return  "FAILED" ;
                }else{

                    $is_prev_day = $this->checkIsPrevDayCreateByOrderInfo($order_info);
                    if($is_prev_day && date("H:i") < $order_info['start_time']){
                        return 'SELLING';
                    }else{
                        return  'LIUPAI';
                    }

                }
            }



        }else if($order_info['status'] =='SELLED'){

            return $type =="SELL" ? "SELLED":"BUYED";
        }

    }

    /**
     * 支付尾款
     * @param $order_id
     * @param $buy_user_id
     * @return mixed
     */
    public function doPayBalance($order_id,$buy_user_id,$cash_password){

        $order_info = $this->getInfoById($order_id);
        if(!$order_info){
            $this->setError('200053');
            return false ;
        }

        $order_status = $order_info['status'];
        if($order_status != "BIDED"){
            $this->setError('200054');
            return false ;
        }

        $type = $order_info['user_id'] == $buy_user_id ?'SELL':"BUY";
        if($type == 'SELL'){
            $this->setError('200052');
            return false ;
        }

        if($order_info['buy_user_id']!= $buy_user_id){
            $this->setError('200055');
            return false ;
        }

        // 获取状态时就已经知道是否可以支付
        $status = $this->getOrderStatus($order_info,$type);
        if($status != "PAYING"){
            $this->setError('200056');
            return false ;
        }

        // 判断资金支付密码是否正确
        $member_obj = new Member();
        $user_info = $member_obj->getUserInfoById($buy_user_id);
        if($user_info['cash_password'] != md5($cash_password)){
            $this->setError('200051');
            return false ;
        }


        // 实际需要支付的能量块的价格
        $total_amount = $order_info['usdt_price'] ;
        $product_obj = new RobotEnergyBlockProduct();
        $product_info = $product_obj->getInfoById($order_info['product_id']);
        $total_amount = $this->returnRealUsdtPrice($total_amount,$order_info,$product_info['contract_income_ratio']);

        $user_balance_obj = new RobotUserBalance();
        $balance_info = $user_balance_obj->getInfoByCoin($buy_user_id,'USDT');
        if($balance_info['total'] <$total_amount){
            $this->setError('200057');
            return false ;
        }

        // 判断是否为当前买当天卖
        $is_today = false ;
        $create_time = $order_info['create_time'];
        if(date("Y-m-d") == date("Y-m-d",strtotime($create_time))){
            $is_today = true ;
        }

        // 前一天的总和
        $prev_day_total_amount = $this->returnPrevDayRealUsdtPrice($order_info['usdt_price'],$order_info,$product_info['contract_income_ratio']);


        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            $now = date('Y-m-d H:i:s');
            // 进行更新
            $order_update_data['buy_user_id'] = $buy_user_id ;
            $order_update_data['status'] = 'SELLED' ;
            $order_update_data['modify_time'] = $now;
            $order_cond = "id=:id AND status=:status";
            $order_args =[':id'=>$order_info['id'],":status"=>'BIDED'];
            $this->baseUpdate(self::tableName(),$order_update_data,$order_cond,$order_args);

            $user_update_data['balance'] = new Expression('balance - '.$total_amount);
            $user_update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate('sea_user',$user_update_data,'id=:id AND balance - '.$total_amount.' >=0',[':id'=>$buy_user_id]);

            $balance_update_data['total'] = new Expression('total - '.$total_amount);
            $balance_update_data['modify_time'] = date('Y-m-d H:i:s');
            $user_balance_obj->baseUpdate($user_balance_obj::tableName(),$balance_update_data,'id=:id AND total - '.$total_amount.' >=0',[':id'=>$balance_info['id']]);

            // 插入资金操作快照
            $balance_record_obj = new RobotUserBalanceRecord();
            $balance_record_obj->addByDirectBuyBlock($buy_user_id,'USDT',$total_amount);

            // 同时新增买入记录
            $add_data['product_id'] = $product_info['id'] ;
            $add_data['user_id'] = $buy_user_id ;
            $add_data['buy_user_id'] = 0 ;
            $add_data['parent_product_id'] = $order_info['id'] ;
            $add_data['product_root_path'] = $order_info['product_root_path'].$order_info['id'].'--' ;
            $add_data['level'] = $order_info['level'] ;
            $add_data['token_price'] = $order_info['token_price'] ;
            $add_data['usdt_price'] = $total_amount;
            $add_data['original_usdt_price'] = $order_info['original_usdt_price'] ;
            $add_data['status'] = 'PENDING' ;
            $add_data['type'] = 'PRODUCT' ;// 换购得来
            $add_data['max_usdt'] = $order_info['max_usdt'] ;
            $add_data['buffer_days'] = $order_info['buffer_days'] ;
            $add_data['contract_income_ratio'] = $order_info['contract_income_ratio'] ;
            $add_data['start_time'] = $order_info['start_time'] ;
            $add_data['end_time'] = $order_info['end_time'] ;
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseInsert(self::tableName(),$add_data);


            // 计算用户入金
            // 获取前一天的价格
            if($is_today){
                $earn_total = $total_amount ;
            }else{
                $site_config_obj = new SiteConfig();
                $sca_sell_earn_percent = $site_config_obj->getByKey('sca_sell_earn_percent');
                $sca_sell_earn_percent = $sca_sell_earn_percent/100;
                $earn_total = $prev_day_total_amount * (1+$sca_sell_earn_percent);
                $earn_total = numberSprintf($earn_total,6);
            }

            $sell_user_id = $order_info['user_id'];
            $sell_user_update_data['balance'] = new Expression('balance + ' . $earn_total);
            $sell_user_update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate('sea_user',$sell_user_update_data,'id=:id',[':id'=>$sell_user_id]);


            // 插入资金流水
            $balance_record_obj->addBySellBlock($sell_user_id,'USDT',$earn_total);

            $balance_obj = new RobotUserBalance();
            $balance_obj->addByCashIn($sell_user_id,$earn_total);

            # 团队总额 当天原始能量块卖出是不产生收益的
            if(!$is_today){
                $site_config_obj = new SiteConfig();
                $sca_team_earn_percent = $site_config_obj->getByKey('sca_team_earn_percent');
                $sca_team_earn_percent = $sca_team_earn_percent/100;

                $team_earn_total =  $prev_day_total_amount * $sca_team_earn_percent;
                $team_earn_total = numberSprintf($team_earn_total,6);
                $team_obj = new RobotTeamTotal();
                $team_obj->addSnapshot($sell_user_id,$team_earn_total,$total_amount);
            }

            $transaction->commit();
            return true ;
        } catch (\Exception $e) {

            $this->setError('200058');
            $transaction->rollBack();
            return false;
        }

    }

    /**
     * 投诉用户
     * @param $order_id
     * @param $user_id
     * @return mixed
     */
    public function doConfirm($order_id,$user_id){

        $order_info = $this->getInfoById($order_id);
        if(!$order_info){
            $this->setError('200053');
            return false ;
        }

        $order_status = $order_info['status'];
        if($order_status != "BIDED"){
            $this->setError('200059');
            return false ;
        }

        $type = $order_info['user_id'] == $user_id ?'SELL':"BUY";
        if($type != 'SELL'){
            $this->setError('200060');
            return false ;
        }
        $status = $this->getOrderStatus($order_info,$type);
        if($status != "CONFIRM"){
            $this->setError('200061');
            return false ;
        }


        //恢复订单状态
        $update_order_data['status'] = "SELLING";
        $update_order_data['buy_user_id'] = "0";
        $update_order_data['bid_time'] = null;
        $update_order_data['modify_time'] = date('Y-m-d H:i:s');
        $this->baseUpdate(self::tableName(),$update_order_data,'id=:id',[':id'=>$order_id]);

        // 目前只做
        $user_update_data['aution_time'] = date('Y-m-d H:i:s');
        $user_update_data['auction_status'] = 'DEALING';
        $user_update_data['modify_time'] = date('Y-m-d H:i:s');
        $this->baseUpdate('sea_user',$user_update_data,'id=:id',[':id'=>$order_info['buy_user_id']]);
        return true;
    }

    public function getAuctionTotalInfo($user_id){
        $product_obj = new RobotEnergyBlockProduct();
        $product_info = $product_obj->getInfoById(1);
        $earn_percent = $product_info['contract_income_ratio'];
        $sca_value = $this->returnCurrentScaPrice($earn_percent);
        $res =  [
            'current_auction_people'    => $this->getCurrentAuctionPeople($user_id),
            'today_reserve_number'      => $this->getTodayReserveNumber(),
            'auction_countdown'         => $this->getCountDown($product_info),
            'sca_value'                 =>$sca_value
        ];


        return $res ;
    }

    /**
     * 获取当前参与竞拍人数
     * @param $user_id
     * @return mixed
     */
    private function getCurrentAuctionPeople($user_id){

        $team_total_obj = new RobotTeamTotal();
        $info = $team_total_obj->checkTodayInfo($user_id);
        return $info  ? $info['team_amount'] : 0 ;
    }

    /**
     * 获取当前参与竞拍人数
     * @return mixed
     */
    private function getTodayReserveNumber(){
        $params['cond'] = ' bid_time >=:start_time ';
        $params['args'] = [':start_time'=>date('Y-m-d 00:00:00')] ;
        $params['fields'] = 'id';
        $params['group_by'] = 'buy_user_id';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return count($list);
    }

    public function getCountDown($product_info){

        $transaction_time = $product_info['transaction_time'];
        $transaction_time = explode('-',$transaction_time);
        $start_time = $transaction_time[0];
        $end_time = $transaction_time[1];
        if(date("H:i") >= $start_time && date("H:i")  <=$end_time){
            return  0 ;
        }else{
            if(date("H:i") < $start_time){
                $ext = strtotime(date("Y-m-d ".$start_time.":00"))-time();
                return $ext > 0 ? $ext : 0 ;
            }else if(date("H:i") > $end_time){
                $ext = strtotime(date("Y-m-d ".$start_time.":00"))-time() +86400;
                return $ext > 0 ? $ext : 0 ;
            }
        }


        return 0 ;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function updateBySaveProductInfo($id){

        $product_obj = new RobotEnergyBlockProduct() ;
        $product_info = $product_obj->getInfoById($id);

        $update_data['contract_income_ratio'] = $product_info['contract_income_ratio'];
        $update_data['original_usdt_price'] = $product_info['usdt_price'];
        $transaction_time = explode('-',$product_info['transaction_time']);
        $update_data['start_time'] = $transaction_time[0];
        $update_data['end_time'] = $transaction_time[1];
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        return $this->baseUpdate(self::tableName(),$update_data,'product_id=:product_id AND status=:status',[':product_id'=>$id,':status'=>'SELLING']);

    }

    /**
     * 获取状态列表
     */
    public function getStatusList(){
        return ['SELLING'=>'拍卖中','BIDED'=>'已预约','SELLED'=>'已售卖','IMPLODE'=>'已拆解'];
    }


    public function getTypeList(){
        //ORIGINAL-原始 PRODUCT-产品 USDT-拆解
        return ['ORIGINAL'=>'原始','PRODUCT'=>'拍卖','USDT'=>'拆解'];
    }
}
