<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mining_machine_user_balance_record".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $total 金额
 * @property string $coin 所属币种
 * @property string $type 类型
 * @property string $op_type ADD-新增/REDUCE-扣减
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class RobotUserBalanceRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_user_balance_record';
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
            [['coin', 'type', 'op_type'], 'string', 'max' => 50],
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
            'coin' => 'Coin',
            'type' => 'Type',
            'op_type' => 'Op Type',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 返回所有类型
     * @return array
     */
    public function getTotalType(){
        $type_arr = [
            'CASH_IN' =>'充值',
            'ADMIN_CASH_IN' =>'充值',
            'CASH_OUT' =>'提现',
            'CASH_OUT_FEE' =>'提现手续费',
            'REGISTER_INTEGRAL' =>'充值送积分',
            'EXCHANGE_INTEGRAL' =>'兑换现金的积分',
            'EXCHANGE_USDT' =>'兑换积分扣减现金',
            'BUY_OUT_USDT'=>'购买节点矿机',
            'BUY_OUT_INTEGRAL'=>'购买商品扣减积分',
            'BUY_IN_FROZEN_INTEGRAL'=>'购买商品增加冻结积分',
            'RETURN_BUY_OUT_FROZEN_INTEGRAL'=>'返还购买商品减少冻结积分',
            'RETURN_BUY_IN_INTEGRAL'=>'返还购买商品积分',
            'RECOMMENDER_IN_USDT'=>'第一节点收益',				//直接推荐奖励
            'RECOMMENDER_PARENT_IN_USDT'=>'第二节点收益',		//间接推荐奖励
            'NOTE_OTHER_RELEASE_IN_INTEGRAL'=>'逐层挖矿奖励',		//其它节点释放积分
            'NOTE_SELF_RELEASE_IN_INTEGRAL'=>'本层挖矿奖励',			//本层节点释放积分
            'TEAM_IN_USDT'=>'领域奖励',
            'DISTRIBUTE_OTHER_IN_USDT'=>'云端大派送现金奖励',
            'DISTRIBUTE_LEVEL_IN_USDT'=>'云端大派送现金奖励（最后一层用户）',
            'DISTRIBUTE_LAST_IN_USDT'=>'云端大派送现金奖励（最后一位用户）',
            'BUY_COIN'=>'购买币种',
            'BUY_COIN_USDT'=>'购买币种消耗',
			'CASH_IN_FROM_DIG_INTEGRAL'=>'SCA提币',
			'REDUCE_COIN_BY_ADMIN'=>'扣减余额',//后台扣减
			'ADD_TOKEN_BY_ADMIN'=>'新增余额',//后台新增TOKEN
			'REDUCE_TOKEN_BY_ADMIN'=>'扣减余额',//后台扣减TOKEN
			'ADD_FROZEN_USDT'=>'增加冻结',//
			'RELEASE_FROZEN_USDT'=>'释放冻结',//
			'SEND_TO_USER'=>'赠送给用户',//
			'GET_BY_SEND'=>'他人赠送',//
        ];
        return $type_arr ;
    }

    /**
     * 根据key返回指定类型的中文显示名
     * @param $type
     * @return mixed
     */
    public function getTypeName($type){

        $type_arr = $this->getTotalType();
        return isset($type_arr[$type]) ? $type_arr[$type] :'';
    }

    /**
     * 返回挖矿收益类型
     * @return array
     */
    public function getTokenEarnType(){
        return ['GUDING','SHIFANG'] ;
    }

    /**
     * 新增记录
     * @param $user_id
     * @param $total
     * @param $coin
     * @param $type
     * @param $op_type
     * @return string
     */
    public function addRecordByCashIn($user_id,$total,$is_admin=false){

        // 当前时间
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'USDT' ;
        $add_data['type'] = !$is_admin ? 'CASH_IN':'ADMIN_CASH_IN' ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 根据购买机器添加快照
     * @param $user_id
     * @param $total
     * @return string
     */
    public function addRecordByOrder($user_id,$total){

        // 当前时间
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'USDT' ;
        $add_data['type'] = 'PAY_MACHINE' ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 根据币种获取总数目
     * @param $user_id
     * @param $coin
     * @return int
     */
    public function getTotalByCoin($user_id,$coin){
        $params['cond'] = 'coin=:coin AND user_id=:user_id';
        $params['args'] = [':coin'=>$coin,':user_id'=>$user_id];
        $params['fields'] = 'count(1) as total_num';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_num']) ? $info['total_num'] : 0 ;
    }

    /**
     * 获取列表信息
     * @param $user_id
     * @param $coin
     * @param $page
     * @param $page_num
     * @return mixed
     */
    public function getListByPage($user_id,$coin,$page,$page_num){
        $params['cond'] = 'coin=:coin AND user_id=:user_id';
        $params['args'] = [':coin'=>$coin,':user_id'=>$user_id];
        $params['fields'] = 'total,type,op_type,create_time';
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['create_time'] = date('Y-m-d',strtotime($v['create_time']));
                $list[$k]['type_name'] =$this->getTypeName($v['type']);
            }
        }
        return $list ;
    }

    /**
     * 根据分享获得
     * @param $user_id
     * @param $get_value
     * @param $type
     * @return string
     */
    public function addByShare($user_id,$get_value,$type){
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $get_value ;
        $add_data['coin'] = 'USDT' ;
        $add_data['type'] = $type ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 增加团队收益
     * @param $user_id
     * @param $earn_total
     * @return string
     */
    public function addByShareTeam($user_id,$earn_total){
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $earn_total ;
        $add_data['coin'] = 'USDT' ;
        $add_data['type'] = 'TEAM_EARN' ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 根据机器盈利添加记录
     * @param $user_id
     * @param $coin
     * @param $total
     * @param $type
     * @return string
     */
    public function addByMachineEarn($user_id,$coin,$total,$type){
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = $coin ;
        $add_data['type'] = $type ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * @param $user_id
     * @param $coin
     * @param $total
     * @return string
     */
    public function addByCashOut($user_id,$coin,$total){
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = $coin ;
        $add_data['type'] = 'CASH_OUT' ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 提现手续费
     * @param $user_id
     * @param $coin
     * @param $cash_out_fee
     * @return mixed
     */
    public function addByCashOutFee($user_id,$coin,$cash_out_fee){
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $cash_out_fee ;
        $add_data['coin'] = $coin ;
        $add_data['type'] = 'CASH_OUT_FEE' ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 获取指定类型列表信息
     * @param $user_id
     * @param $type
     * @param $page
     * @param $page_num
     * @return mixed
     */
    public function getListByPageAndType($user_id,$type,$page,$page_num){
        if($type =='ALL'){
            $params['cond'] = ' user_id=:user_id';
            $params['args'] = [':user_id'=>$user_id];
        }else{
            $params['cond'] = 'type=:type AND user_id=:user_id';
            $params['args'] = [':type'=>$type,':user_id'=>$user_id];
        }
        $params['fields'] = 'total,type,op_type,create_time,coin';
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['create_time'] = date('Y-m-d',strtotime($v['create_time']));
                $list[$k]['type_name'] =$this->getTypeName($v['type']);
            }
        }
        return $list ;
    }

    /**
     * 获取指定类型列表信息
     * @param $user_id
     * @param $type_list
     * @param $page
     * @param $page_num
     * @return mixed
     */
    public function getListByPageAndTypeList($user_id,$type_list,$page,$page_num){

        $params['cond'] = 'type like :type AND user_id=:user_id';
        $params['args'] = [':type'=>'%'.$type_list.'%',':user_id'=>$user_id];
        $params['fields'] = 'total,type,op_type,create_time';
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['create_time'] = date('Y-m-d',strtotime($v['create_time']));
                $list[$k]['type_name'] =$this->getTypeName($v['type']);
            }
        }
        return $list ;
    }


    /**
     * 获取用户指定类型的总数目
     * @param $user_id
     * @param $type
     * @return int
     */
    public function getTotalByPageAndType($user_id,$type){

        if($type =='ALL'){
            $params['cond'] = ' user_id=:user_id';
            $params['args'] = [':user_id'=>$user_id];
        }else{
            $params['cond'] = 'type=:type AND user_id=:user_id';
            $params['args'] = [':type'=>$type,':user_id'=>$user_id];

        }
        $params['fields'] = 'count(1) as total_num';

        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_num']) ? $info['total_num'] : 0 ;
    }

    /**
     * 根据注册新增记录
     * @param $user_id
     * @param $total
     * @return mixed
     */
    public function addRecordByRegister($user_id,$total){

        //REGISTER_INTEGRAL
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'REGISTER_INTEGRAL' ;
        $add_data['type'] = 'REGISTER_INTEGRAL' ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * @param $user_id
     * @param $total
     * @param $total_integral
     * @return mixed
     */
    public function addByExchange($user_id,$total,$total_integral){

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total_integral ;
        $add_data['coin'] = 'INTEGRAL' ;
        $add_data['type'] = 'EXCHANGE_INTEGRAL' ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        $this->baseInsert(self::tableName(),$add_data);

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'USDT' ;
        $add_data['type'] = 'EXCHANGE_USDT' ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        $id = $this->baseInsert(self::tableName(),$add_data);

        if($id){
            // 增加计算团队收益的
        }
        return true ;
    }

    /**
     * 根据币种和类型返回合计金额
     * @param $user_id
     * @param $coin
     * @param $type
     * @return mixed
     */
    public function getTotalByCoinAndType($user_id,$coin,$type){
        $params['cond'] = ' user_id=:user_id AND coin=:coin AND type=:type';
        $params['args'] = [":user_id"=>$user_id,':coin'=>$coin,':type'=>$type];
        $params['fields'] = ' sum(total) as sum_total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['sum_total']) ? $info['sum_total'] : 0 ;

    }


    /**
     * @param $user_id
     * @param $amount
     * @param $total
     * @param $coin
     * @param $price
     * @return mixed
     */
    public function addByBuyCoin($user_id,$amount,$total,$coin,$price){

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $amount ;
        $add_data['coin'] = $coin ;
        $add_data['type'] = 'BUY_COIN' ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        $this->baseInsert(self::tableName(),$add_data);

        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'USDT' ;
        $add_data['type'] = 'BUY_COIN_USDT' ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        $id = $this->baseInsert(self::tableName(),$add_data);

        $record_obj = new RobotTokenTradeRecord();
        $record_add_data['user_id'] = $user_id ;
        $record_add_data['coin'] = $coin ;
        $record_add_data['price'] = $price ;
        $record_add_data['amount'] = $amount ;
        $record_add_data['type'] = 'BUY' ;
        $record_add_data['is_deleted'] = 'N' ;
        $record_add_data['create_time'] = date('Y-m-d H:i:s') ;
        $record_add_data['modify_time'] = date('Y-m-d H:i:s') ;
        $this->baseInsert($record_obj::tableName(),$record_add_data);

        if($id){
            // 增加计算团队收益的
        }
        return true ;
    }

    /**
     * 获取指定返回的总额
     * @param $user_id
     * @param $coin
     * @param $type
     * @param $start_time
     * @param $end_time
     * @return mixed
     */
    public function getTotalByCoinAndTypeAndRangeTime($coin,$type,$start_time,$end_time){
        $params['cond'] = ' coin=:coin AND type=:type AND create_time >=:start_time AND create_time <= :end_time';
        $params['args'] = [':coin'=>$coin,':type'=>$type,':start_time'=>$start_time,':end_time'=>$end_time];
        $params['fields'] = ' sum(total) as sum_total' ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['sum_total']) ?$info['sum_total'] : 0 ;
    }


    /**
     * 增加现金
     * @param $user_id
     * @param $total
     * @prams $types
     * @return mixed
     */
    public function addLogByInCash($user_id,$total,$types){
        $now = date('Y-m-d H:i:s');
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'USDT' ;
        $add_data['type'] = $types ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 减少现金
     * @param $user_id
     * @param $total
     * @prams $types
     * @return mixed
     */
    public function addLogByOutCash($user_id,$total,$types){
        $now = date('Y-m-d H:i:s');
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'USDT' ;
        $add_data['type'] = $types ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 增加积分
     * @param $user_id
     * @param $total
     * @prams $types
     * @return mixed
     */
    public function addLogByInIntegral($user_id,$total,$types){
        $now = date('Y-m-d H:i:s');
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'SCA' ;
        $add_data['type'] = $types ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * 减少积分
     * @param $user_id
     * @param $total
     * @prams $types
     * @return mixed
     */
    public function addLogByOutIntegral($user_id,$total,$types){
        $now = date('Y-m-d H:i:s');
        $add_data['user_id'] = $user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = 'SCA' ;
        $add_data['type'] = $types ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }

    /**
     * @param $user_id
     * @param $coin
     * @param $total
     * @return string
     */
    public function addBySend($send_user_id,$get_user_id,$coin,$total){
        $now = date('Y-m-d H:i:s');

        $add_data['user_id'] = $send_user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = $coin ;
        $add_data['type'] = 'SEND_TO_USER' ;
        $add_data['op_type'] = 'REDUCE' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        $this->baseInsert(self::tableName(),$add_data) ;

        $add_data['user_id'] = $get_user_id ;
        $add_data['total'] = $total ;
        $add_data['coin'] = $coin ;
        $add_data['type'] = 'GET_BY_SEND' ;
        $add_data['op_type'] = 'ADD' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data) ;
    }
}
