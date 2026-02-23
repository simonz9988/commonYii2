<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "sea_mining_machine_user_balance".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $total 资产
 * @property string $frozen_total 冻结
 * @property string $coin 所属币种
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class RobotUserBalance extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_user_balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['total', 'frozen_total'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['coin'], 'string', 'max' => 50],
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
            'total' => 'Total',
            'frozen_total' => 'Frozen Total',
            'coin' => 'Coin',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据充值更新用户资产信息
     * @param $user_id
     * @param $total
     * @return mixed
     */
    public function addByCashIn($user_id,$total){

        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>'USDT',':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            $update_data['total'] = new Expression(" total + ".$total);
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }
        $now = date('Y-m-d H:i:s');
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['frozen_total'] = 0 ;
        $add_data['coin'] = 'USDT' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 根据购买机器更新用户资产余额
     * @param $user_id
     * @param $total
     * @return mixed
     */
    public function updateByBuyMachine($user_id,$total){

        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>'USDT',':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            $update_data['total'] = new Expression(" total  - ".$total);
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }

        return false ;
    }

    /**
     * 根据用户ID返回指定的列表信息
     * @param $user_id
     * @param $is_other
     * @return mixed
     */
    public function getList($user_id,$is_other=true){

        $params['cond'] = 'user_id=:user_id AND is_deleted=:is_deleted AND coin !=:coin' ;
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N',':coin'=>'DIG_SCA'];
        $params['fields'] = 'total,frozen_total,coin';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        // 判断是否2个之中是否都存在
        if(!$list){
            $list[] = ['total'=>0,'frozen_total'=>0,'coin'=>'USDT'];
            if($is_other){
                $list[] = ['total'=>0,'frozen_total'=>0,'coin'=>'OTHER'];//代表积分
            }

        }else{

            $coin_list = [];
            foreach($list as $v){
                $coin_list[] = $v['coin'];
            }

            if(!in_array('USDT',$coin_list)){
                $list[] = ['total'=>0,'frozen_total'=>0,'coin'=>'USDT'];
            }

            if($is_other) {
                if (!in_array('OTHER', $coin_list)) {
                    $list[] = ['total' => 0, 'frozen_total' => 0, 'coin' => 'OTHER'];
                }
            }
        }
        return $list ;
    }

    /**
     * 根据
     * @param $user_id
     * @param $get_value
     * @return mixed
     */
    public function addByShare($user_id,$get_value){

        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>'USDT',':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            $update_data['total'] = new Expression(" total + ".$get_value);
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }
        $now = date('Y-m-d H:i:s');
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $get_value ;
        $add_data['frozen_total'] = 0 ;
        $add_data['coin'] = 'USDT' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data);

    }

    /**
     * 根据机器盈利添加记录
     * @param $user_id
     * @param $coin
     * @param $total
     * @param $type
     */
    public function addByMachineEarn($user_id,$coin,$total,$type){

        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            $update_data['total'] = new Expression(" total + ".$total);
            if($type =='SHIFANG'){
                // 扣减冻结总额
                $update_data['frozen_total'] = new Expression(" frozen_total - ".$total);
            }
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }
        $now = date('Y-m-d H:i:s');
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['frozen_total'] = 0 ;
        $add_data['coin'] = $coin ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 增加冻结资产
     * @param $user_id
     * @param $total
     * @param $coin
     * @return mixed
     */
    public function addFrozen($user_id,$total,$coin){

        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            $update_data['frozen_total'] = new Expression(" frozen_total + ".$total);
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }
        $now = date('Y-m-d H:i:s');
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = 0 ;
        $add_data['frozen_total'] = $total ;
        $add_data['coin'] = $coin ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 根据用户 ID和币种返回信息
     * @param $user_id
     * @param $coin
     * @return mixed
     */
    public function getInfoByUserIdAndCoin($user_id,$coin){
        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据取现更新记录
     * @param $user_id
     * @param $balance_info
     * @param $total
     * @param $address
     * @param $cash_out_fee
     * @return mixed
     */
    public function updateByCashOut($user_id,$balance_info,$total,$address,$cash_out_fee){

        $coin = $balance_info['coin'];

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            // 当前时间
            $now = date('Y-m-d H:i:s');
            // 更新
            if($coin == 'USDT'){
                $member_obj = new Member();
                $member_update_data['balance'] = new Expression('balance -'.($total+$cash_out_fee));
                $member_update_data['modify_time'] = $now;
                $member_cond = ' id=:id AND balance-'.($total+$cash_out_fee).' >= 0';
                $member_args[':id'] = $user_id;
                $member_obj->baseUpdate('sea_user',$member_update_data,$member_cond,$member_args);
            }

            // 扣减记录
            $balance_update_data['total'] =  new Expression('total -'.($total+$cash_out_fee));
            $balance_cond = ' id=:id AND total -'.($total+$cash_out_fee).' >= 0';
            $balance_args[':id'] = $balance_info['id'];
            $this->baseUpdate(self::tableName(),$balance_update_data,$balance_cond,$balance_args);


            // 新增资金提现记录
            $balance_record_obj = new RobotUserBalanceRecord();
            $balance_record_obj->addByCashOut($user_id,$coin,$total);

            // 新增提现手续费
            $balance_record_obj->addByCashOutFee($user_id,$coin,$cash_out_fee);

            // 新增提现申请表
            $cash_out_obj = new RobotCashOut();
            $out_data['user_id'] = $user_id;
            $out_data['total'] = $total;
            $out_data['fee'] = $cash_out_fee;
            $out_data['coin'] = $coin;
            $out_data['address'] = $address;
            $out_data['status'] = 'UNDEAL';
            $out_data['is_deleted'] = 'N';
            $out_data['create_time'] = $now;
            $out_data['modify_time'] = $now;
            $cash_out_obj->baseInsert($cash_out_obj::tableName(),$out_data);

            $transaction->commit();
            return true ;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }
    /**
     * 根据用户注册返回注册积分
     * @param $user_id
     * @return mixed
     */
    public function addRegisterIntegral($user_id){

        $site_config = new SiteConfig() ;
        $robot_register_send_integral = $site_config->getByKey('robot_register_send_integral');
        if(!$robot_register_send_integral){
            return false ;
        }

        // 判断是否已经存在
        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>'REGISTER_INTEGRAL',':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            $update_data['total'] = new Expression('total +'.$robot_register_send_integral);
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate(self::tableName(),$update_data,'id=:id',[":id"=>$info['id']]);
        }else{

            // 增加余额
            $add_data['user_id'] = $user_id ;
            $add_data['total'] = $robot_register_send_integral ;
            $add_data['frozen_total'] = 0 ;
            $add_data['coin'] = 'REGISTER_INTEGRAL';
            $add_data['is_deleted'] = 'N';
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseInsert(self::tableName(),$add_data);
        }

        // 增加操作记录
        $record_obj  = new RobotUserBalanceRecord();
        $record_obj->addRecordByRegister($user_id,$robot_register_send_integral);
    }

    /**
     * 根据兑换新增积分记录
     * @param $user_id
     * @param $total
     * @return mixed
     */
    public function addIntegralByExchange($user_id,$total){
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['frozen_total'] = 0 ;
        $add_data['coin'] = 'INTEGRAL';
        $add_data['is_deleted'] = 'N';
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 根据用户返回指定币种余额信息
     * @param $user_id
     * @param $coin
     * @return mixed
     */
    public function getInfoByCoin($user_id,$coin){
        $params['cond'] = 'user_id=:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 用户转换积分
     * @param $user_id
     * @param $total
     * @param $cash_password
     * @return mixed
     */
    public function exchangePoint($user_id,$total,$cash_password){

        // 查询用户余额
        $coin_info = $this->getInfoByCoin($user_id,'USDT');
        if(!$coin_info){
            $this->setError('100080');
            return false ;
        }

        if($coin_info['total']<$total){
            $this->setError('100080');
            return false ;
        }

        // 验证自己密码是否正确
        $member_obj = new Member();
        $user_info = $member_obj->getUserInfoById($user_id);
        if(md5($cash_password) != $user_info['cash_password']){
            $this->setError('100081');
            return false ;
        }

        return $this->doExchangePoint($user_info,$total,$coin_info);

    }

    /**
     * 执行余额和积分转换 前后台可复用
     * @param $user_info
     * @param $total
     * @param $usdt_coin_info
     * @return mixed
     */
    public function doExchangePoint($user_info,$total,$usdt_coin_info){


        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            // 用户ID
            $user_id = $user_info['id'];

            // 当前时间
            $now = date('Y-m-d H:i:s');
            // 扣减用户表的余额
            // 账户余额更新
            $user_update_data['balance'] = new Expression('balance - ' . $total);
            $user_update_data['modify_time'] = $now;
            $user_cond = 'id=:id AND balance - ' . $total . ' > 0';
            $user_args = [':id' => $user_id];
            $this->baseUpdate('sea_user', $user_update_data, $user_cond, $user_args);

            // 扣减balance表的余额
            $balance_update_data['total'] = new Expression('total - ' . $total);
            $balance_update_data['modify_time'] = $now;
            $balance_cond = 'id=:id AND total - ' . $total . ' > 0';
            $balance_args = [':id' => $usdt_coin_info['id']];
            $this->baseUpdate(self::tableName(), $balance_update_data, $balance_cond, $balance_args);

            // 新增积分记录
            $site_config = new SiteConfig();
            $robot_usdt_integral_percent = $site_config->getByKey('robot_usdt_integral_percent');
            $total_integral = $robot_usdt_integral_percent * $total;

            // 增加积分记录
            $integral_info = $this->getInfoByCoin($user_id,'INTEGRAL');
            if($integral_info){
                $integral_update_info['total'] = new Expression('total +  ' . $total_integral);
                $this->baseUpdate(self::tableName(),$integral_update_info,'id=:id',[':id'=>$integral_info['id']]);
            }else{
                $this->addIntegralByExchange($user_id,$total_integral);
            }

            // 新增资金变更 2条
            $record_obj = new RobotUserBalanceRecord();
            $record_obj->addByExchange($user_id,$total,$total_integral);

            $transaction->commit();
            return true;
        } catch (\Exception $e) {

            $transaction->rollBack();
            $this->setError(100081);
            return false;
        }
    }

    /**
     * 获取指定币种的余额信息
     * @param $user_id
     * @param $coin
     * @return mixed
     */
    public function getTotalBalanceByCoin($user_id,$coin){
        $params['cond'] = ' user_id=:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['total'] : 0 ;
    }

    public function getTotalFrozenBalanceByCoin($user_id,$coin){
        $params['cond'] = ' user_id=:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['frozen_total'] : 0 ;
    }

    /**
     * 执行购买数量
     * @param $user_id
     * @param $amount 购买数量
     * @param $coin_info
     * @return mixed
     */
    public function doBuyCoin($user_id,$amount,$coin_info){

        $member_obj = new Member() ;
        $user_info = $member_obj->getUserInfoById($user_id);

        // 计算消耗的金额
        $coin_price_obj = new CoinPrice();
        $coin_price = $coin_price_obj->getCoinPrice($coin_info['unique_key']);
        $total = $coin_price * $amount ;
        if($total > $user_info['balance']){
            $this->setError('200008');
            return false ;
        }

        $usdt_coin_info = $this->getInfoByCoin($user_id,'USDT');
        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {

            // 用户ID
            $user_id = $user_info['id'];

            // 当前时间
            $now = date('Y-m-d H:i:s');
            // 扣减用户表的余额
            // 账户余额更新
            $user_update_data['balance'] = new Expression('balance - ' . $total);
            $user_update_data['modify_time'] = $now;
            $user_cond = 'id=:id AND balance - ' . $total . ' > 0';
            $user_args = [':id' => $user_id];
            $this->baseUpdate('sea_user', $user_update_data, $user_cond, $user_args);

            // 扣减balance表的余额
            $balance_update_data['total'] = new Expression('total - ' . $total);
            $balance_update_data['modify_time'] = $now;
            $balance_cond = 'id=:id AND total - ' . $total . ' > 0';
            $balance_args = [':id' => $usdt_coin_info['id']];
            $this->baseUpdate(self::tableName(), $balance_update_data, $balance_cond, $balance_args);

            // 增加两条记录 一条消耗金额，一条购买获得币种
            $record_obj = new RobotUserBalanceRecord();
            $record_obj->addByBuyCoin($user_id,$amount,$total,$coin_info['unique_key'],$coin_price);

            $transaction->commit();
            return true;
        } catch (\Exception $e) {

            $transaction->rollBack();
            $this->setError(200009);
            return false;
        }
    }

    /**
     * 后台管理员扣减币种余额
     * @param $user_id
     * @param $coin
     * @param $total
     * @return mixed
     */
    public function reduceCoinBalanceByAdmin($user_id,$coin,$total){

        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if(!$info){
            return false ;
        }

        // 当前时间
        $now  = date('Y-m-d H:i:s');

        $update_data['total'] = new Expression("total - " . $total);
        $update_data['modify_time'] = $now ;
        $cond = 'id=:id AND total- '.$total.' > 0';
        $args = [':id'=>$info['id']];
        $res = $this->baseUpdate(self::tableName(),$update_data,$cond,$args);
        if(!$res){
            return false ;
        }

        // 新增快照记录
        $record_obj = new RobotUserBalanceRecord();
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = $coin ;
        $add_data['type'] = 'REDUCE_COIN_BY_ADMIN' ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert($record_obj::tableName(),$add_data);
    }

    /**
     * 根据币种类型进行操作
     * @param $user_id
     * @param $coin
     * @param $type
     * @param $total
     * @return mixed
     */
    public function opByCoinAndType($user_id,$coin,$type,$total){

        // 当前时间
        $now  = date('Y-m-d H:i:s');

        // 查询信息是否存在
        $params['cond'] = 'user_id=:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($type == 'add'){

            if($info){
                // update_data
                $update_data['total'] = new Expression("total + " . $total);
                $update_data['modify_time'] = $now ;
                $cond = 'id=:id ';
                $args = [':id'=>$info['id']];
                $res = $this->baseUpdate(self::tableName(),$update_data,$cond,$args);

            }else{
                // insert
                $add_data['user_id'] = $user_id ;
                $add_data['total'] = $total ;
                $add_data['frozen_total'] = 0 ;
                $add_data['coin'] = $coin;
                $add_data['create_time'] = $now;
                $add_data['modify_time'] = $now;
                $res = $this->baseInsert(self::tableName(),$add_data);
            }
        }else{

            if(!$info){
                return false ;
            }else{
                $reduce_update_data['total'] = new Expression("total - " . $total);
                $reduce_update_data['modify_time'] = $now ;

                $reduce_cond = 'id=:id AND total- '.$total.' > 0';
                $reduce_args = [':id'=>$info['id']];
                $res = $this->baseUpdate(self::tableName(),$reduce_update_data,$reduce_cond,$reduce_args);
            }
        }

        if(!$res){
            return false ;
        }

        // 插入日志信息
        $record_obj = new RobotUserBalanceRecord();
        $record_add_data['user_id'] = $user_id ;
        $record_add_data['total'] = $total ;
        $record_add_data['coin'] = $coin ;
        $record_add_data['type'] = $type=='add'?'ADD_TOKEN_BY_ADMIN':'REDUCE_TOKEN_BY_ADMIN'  ;
        $record_add_data['op_type'] = $type=='add'?'ADD':'REDUCE' ;
        $record_add_data['is_deleted'] = 'N' ;
        $record_add_data['create_time'] = $now ;
        $record_add_data['modify_time'] = $now ;
        return $this->baseInsert($record_obj::tableName(),$record_add_data);
    }

    /**
     * @param $user_id
     * @param $type
     * @param $total
     * @return mixed
     */
    public function opFrozenUsdt($user_id,$type,$total){

        $params['cond'] = 'user_id=:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>'USDT',':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        //当前时间
        $now = date('Y-m-d H:i:s');

        if($type =='add'){
            // 增加冻结
            if($info){
                // update_data
                $update_data['frozen_total'] = new Expression("frozen_total + " . $total);
                $update_data['modify_time'] = $now ;
                $cond = 'id=:id ';
                $args = [':id'=>$info['id']];
                $res = $this->baseUpdate(self::tableName(),$update_data,$cond,$args);

            }else{
                // insert
                $add_data['user_id'] = $user_id ;
                $add_data['total'] = 0 ;
                $add_data['frozen_total'] = $total ;
                $add_data['coin'] = 'USDT';
                $add_data['create_time'] = $now;
                $add_data['modify_time'] = $now;
                $res = $this->baseInsert(self::tableName(),$add_data);
            }
        }else{

            if(!$info){
                return false ;
            }
            // 释放冻结
            $update_data['frozen_total'] = new Expression("frozen_total - " . $total);
            $update_data['total'] = new Expression("total + " . $total);
            $update_data['modify_time'] = $now ;
            $cond = 'id=:id AND frozen_total - '.$total.' > 0 ';
            $args = [':id'=>$info['id']];
            $res = $this->baseUpdate(self::tableName(),$update_data,$cond,$args);
            if(!$res){
                return false ;
            }

            // 更新账户余额
            $user_update_data['balance'] = new Expression("balance + " . $total);
            $user_update_data['modify_time'] = $now ;
            $res = $this->baseUpdate('sea_user',$user_update_data,'id=:id',[':id'=>$user_id]);
        }

        if(!$res){
            return false ;
        }


        $record_obj = new RobotUserBalanceRecord();
        $record_add_data['user_id'] = $user_id ;
        $record_add_data['total'] = $total ;
        $record_add_data['coin'] = 'USDT' ;
        // 增加冻结
        $record_add_data['type'] = $type=='add'?'ADD_FROZEN_USDT':'RELEASE_FROZEN_USDT'  ;
        $record_add_data['op_type'] = 'ADD' ;
        $record_add_data['is_deleted'] = 'N' ;
        $record_add_data['create_time'] = $now ;
        $record_add_data['modify_time'] = $now ;
        return $this->baseInsert($record_obj::tableName(),$record_add_data);
    }

    /**
     * 执行赠送操作
     * @param $from_user_id
     * @param $to_user_id
     * @param $coin
     * @param $total
     * @return mixed
     */
    public function send($from_user_id,$to_user_id,$coin,$total){

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {


            $from_balance_info = $this->getInfoByUserIdAndCoin($from_user_id,$coin);

            $to_balance_info =  $this->getInfoByUserIdAndCoin($to_user_id,$coin);

            if($to_balance_info){
                $to_update_data['total'] = new Expression(" total + ".$total);
                $to_update_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate(self::tableName(),$to_update_data,'id=:id',[':id'=>$to_balance_info['id']]);
            }else{

                //插入操作
                $to_add_data['user_id'] = $to_user_id ;
                $to_add_data['total'] = $total ;
                $to_add_data['frozen_total'] = 0 ;
                $to_add_data['coin'] = $coin ;
                $to_add_data['is_deleted'] = 'N' ;
                $to_add_data['create_time'] = date('Y-m-d H:i:s') ;
                $to_add_data['modify_time'] = date('Y-m-d H:i:s') ;
                $this->baseInsert(self::tableName(),$to_add_data);
            }


            $from_update_data['total'] = new Expression(" total - ".$total);
            $from_update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate(self::tableName(),$from_update_data,'id=:id AND total -'.$total.' >0',[':id'=>$from_balance_info['id']]);


            // 增加快照信息
            $record_obj = new RobotUserBalanceRecord();
            $record_obj->addBySend($from_user_id,$to_user_id,$coin,$total);

            $transaction->commit();
            return true ;
        } catch (\Exception $e) {
            var_dump($e);exit;
            $transaction->rollBack();
            return false;
        }
    }

}
