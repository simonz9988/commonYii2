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
class MiningMachineUserBalance extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_user_balance';
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
     * @return mixed
     */
    public function getList($user_id){

        $params['cond'] = 'user_id=:user_id AND is_deleted=:is_deleted' ;
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N'];
        $params['fields'] = 'total,frozen_total,coin';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        // 判断是否2个之中是否都存在
        if(!$list){
            $list[] = ['total'=>0,'frozen_total'=>0,'coin'=>'USDT'];
            $list[] = ['total'=>0,'frozen_total'=>0,'coin'=>'FIL'];
        }else{

            $coin_list = [];
            foreach($list as $v){
                $coin_list[] = $v['coin'];
            }

            if(!in_array('USDT',$coin_list)){
                $list[] = ['total'=>0,'frozen_total'=>0,'coin'=>'USDT'];
            }

            if(!in_array('FIL',$coin_list)){
                $list[] = ['total'=>0,'frozen_total'=>0,'coin'=>'FIL'];
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
                $member_cond = ' id=:id AND balance-'.($total+$cash_out_fee).' > 0';
                $member_args[':id'] = $user_id;
                $member_obj->baseUpdate('sea_user',$member_update_data,$member_cond,$member_args);
            }

            // 扣减记录
            $balance_update_data['total'] =  new Expression('total -'.($total+$cash_out_fee));
            $balance_cond = ' id=:id AND total -'.($total+$cash_out_fee).' > 0';
            $balance_args[':id'] = $balance_info['id'];
            $this->baseUpdate(self::tableName(),$balance_update_data,$balance_cond,$balance_args);


            // 新增资金提现记录
            $balance_record_obj = new MiningMachineUserBalanceRecord();
            $balance_record_obj->addByCashOut($user_id,$coin,$total);

            // 新增提现手续费
            $balance_record_obj->addByCashOutFee($user_id,$coin,$cash_out_fee);

            // 新增提现申请表
            $cash_out_obj = new MiningMachineCashOut();
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
}
