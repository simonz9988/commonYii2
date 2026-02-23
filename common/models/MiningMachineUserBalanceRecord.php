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
class MiningMachineUserBalanceRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_user_balance_record';
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
            'ADMIN_CASH_IN' =>'后台充值',
            'PAY_MACHINE' =>'购买',
            'SHARE_1' =>'直接分享',
            'SHARE_2' =>'间接分享',
            'TEAM_EARN' =>'团队收益',
            'GUDING' =>'立即释放收益',
            'SHIFANG' =>'线性释放收益',
            'CASH_OUT' =>'提现',
            'CASH_OUT_FEE' =>'提现手续费',
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
}
