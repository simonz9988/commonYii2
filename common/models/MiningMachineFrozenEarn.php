<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mining_machine_frozen_earn".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $business_id 关联业务ID
 * @property string $total 盈利值
 * @property string $coin 所属币种
 * @property string $date 日期
 * @property int $date_timestamp 日期时间戳
 * @property string $type 类型
 * @property int $user_level 开始时间
 * @property string $user_root_path 结束时间
 * @property string $is_deleted 是否已经删除
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class MiningMachineFrozenEarn extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_frozen_earn';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'date_timestamp', 'user_level'], 'integer'],
            [['total'], 'number'],
            [['user_root_path'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['business_id', 'coin', 'type'], 'string', 'max' => 50],
            [['date'], 'string', 'max' => 8],
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
            'business_id' => 'Business ID',
            'total' => 'Total',
            'coin' => 'Coin',
            'date' => 'Date',
            'date_timestamp' => 'Date Timestamp',
            'type' => 'Type',
            'user_level' => 'User Level',
            'user_root_path' => 'User Root Path',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 插入指定币种的指定类型的盈利信息
     * @param $user_info
     * @param $total
     * @param $type
     * @param $machine_info
     * @param $end_date_timestamp
     * @param $activity_info
     * @param $business_id
     * @return bool|string
     */
    public function addByUserAndType($user_info,$total,$type,$machine_info,$end_date_timestamp,$activity_info,$business_id){
        if($total <= 0 ){
            return false ;
        }

        // 币种
        $coin = $machine_info['coin'] ;
        // 用户ID
        $user_id = $user_info['id'];
        $params['cond'] = 'user_id=:user_id AND business_id=:business_id AND coin=:coin AND date_timestamp=:date_timestamp AND type=:type';
        $params['args'] = [':user_id'=>$user_id,':business_id'=>$business_id,':coin'=>$coin,':date_timestamp'=>$end_date_timestamp,':type'=>$type];
        $check_info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        if($check_info){
            return false ;
        }

        // 判断是否重复新增
        $total = numberSprintf($total,6);

        $add_data['user_id'] = $user_info['id'] ;
        $add_data['business_id'] = $business_id ;
        $add_data['total'] = $total ;

        $add_data['coin'] = $coin ;
        $add_data['date'] = date("Ymd",$end_date_timestamp) ;
        $add_data['date_timestamp'] = $end_date_timestamp ;
        $add_data['type'] = $type ;
        $add_data['user_level'] = $user_info['user_level'] ;
        $add_data['user_root_path'] = $user_info['user_root_path'] ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['activity_frozen_qishu'] = $activity_info['frozen'] ;

        // 当前时间
        $now = date('Y-m-d H:i:s') ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;

        $this->baseInsert(self::tableName(),$add_data);

        // 增加冻结资产
        $balance_obj = new MiningMachineUserBalance();
        $balance_obj->addFrozen($user_info['id'],$total,$coin);
    }

    /**
     * 获取前一天冻结的记录信息
     * @param $end_date_timestamp
     * @param $user_info
     * @param $type
     * @return mixed
     */
    public function getPrevDayEarn($end_date_timestamp,$user_info,$type){

        $user_id = $user_info['id'];
        $date_timestamp = $end_date_timestamp-86400 ;
        $params['cond'] = 'user_id=:user_id AND date_timestamp=:date_timestamp AND type=:type AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':date_timestamp'=>$date_timestamp,':type'=>$type,':is_deleted'=>'N'];
        $params['fields'] = 'total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['total']: 0;

    }

    /**
     * 查询之前所有的
     * @param $end_date_timestamp
     * @param $user_info
     * @param $type
     * @return mixed
     */
    public function getPrevDayEarnList($end_date_timestamp,$user_info,$type){

        $user_id = $user_info['id'];
        $date_timestamp = $end_date_timestamp-86400 ;
        $params['cond'] = 'user_id=:user_id AND date_timestamp <=:date_timestamp AND type=:type AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':date_timestamp'=>$date_timestamp,':type'=>$type,':is_deleted'=>'N'];
        $params['fields'] = '*';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if(!$list){
            return false;
        }

        $res = [];
        foreach($list as $v){

            // 超出冻结期限的排除
            $activity_frozen_qishu = $v['activity_frozen_qishu'];
            $ext =  ($end_date_timestamp - $v['date_timestamp'])/86400;
            if($ext <=$activity_frozen_qishu){
                $v['real_earn'] = $v['total']/$activity_frozen_qishu ;
                $res[] = $v ;
            }
        }

        return $res ;

    }

    /**
     * 判断当前用户冻结的总数量
     * @param $user_id
     * @param $coin
     * @return mixed
     */
    public function getTotalLeftByUserIdAndCoin($user_id,$coin){

        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return  0 ;
        }

        $res = 0 ;
        foreach($list as $v){

            $date_timestamp = $v['date_timestamp'];
            $ext_day = (time()-$date_timestamp)/86400 + 1 ;
            $left_day = $v['activity_frozen_qishu'] - $ext_day;
            if($left_day > 0 ){
                $res +=  ($v['total']*$left_day)/$v['activity_frozen_qishu'] ;
            }
        }

        return numberSprintf($res,6) ;
    }
}
