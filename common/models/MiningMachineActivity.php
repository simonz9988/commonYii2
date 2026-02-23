<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "sea_mining_machine_activity".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property int $total 总算力
 * @property int $useful_percent 有效总算力比例
 * @property int $daily_add 每天释放算力
 * @property int $frozen 冻结期数
 * @property string $unit_earn 每T产生的收益
 * @property string $status 状态 ENABLED-有效 DISABLED-无效
 * @property string $is_deleted 是否已经删除
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class MiningMachineActivity extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'create_time', 'modify_time'], 'safe'],
            [['total', 'useful_percent', 'daily_add', 'frozen'], 'integer'],
            [['unit_earn'], 'number'],
            [['name'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
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
            'name' => 'Name',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'total' => 'Total',
            'useful_percent' => 'Useful Percent',
            'daily_add' => 'Daily Add',
            'frozen' => 'Frozen',
            'unit_earn' => 'Unit Earn',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回信息
     * @param $id
     * @param $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取所有有效的
     * @return mixed
     */
    public function getAll(){
        $params['cond'] = 'status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':status'=>'ENABLED',':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据ID返回活动名称
     * @param $id
     * @return mixed
     */
    public function getNameById($id){

        $info = $this->getInfoById($id,'name');
        return $info ? $info['name']:'未选择';
    }

    /**
     * 根据 时间 获取记录
     * @param string $time
     * @return array
     */
    public function getInfoByTime($id, $time)
    {
        $db = new Query();
        return $db->from($this->tableName())->where("id!=:id AND status='ENABLED' AND is_deleted='N' AND start_time <= :start_time AND end_time >= :end_time", [":id" => $id, ":start_time" => $time, ":end_time" => $time])->one(self::getDb());
    }

    /**
     * 获取截止时间之前的总获取量
     * @param $activity_info
     * @param $end_date_timestamp
     * @return mixed
     */
    public function getTotalByEndDate($activity_info , $end_date_timestamp){

        //直接返回总量
        return $activity_info['total'];
        $activity_id = $activity_info['id'];
        $user_power_obj = new MiningMachineUserPower();
        $params['cond'] = ' activity_id=:activity_id AND is_deleted=:is_deleted AND date_timestamp <= :date_timestamp ';
        $params['args'] = [':activity_id'=>$activity_id,':is_deleted'=>'N',':date_timestamp'=>$end_date_timestamp];
        $params['fields'] = ' sum(real_total) as sum_total';
        $power_info = $user_power_obj->findOneByWhere($user_power_obj::tableName(),$params,self::getDb());
        $total_get_power = $power_info && !is_null($power_info['sum_total']) ? $power_info['sum_total'] : 0 ;
        return $total_get_power ;
    }

    /**
     * 获取当前活动所有有效算力
     * @param $activity_info
     * @param $end_date_timestamp
     * @param $total_get_power
     * @return mixed
     */
    public function getTotalUsefulByEndDate($activity_info , $end_date_timestamp,$total_get_power){

        // 理论上最大的有效算力
        $max_useful_power =  $activity_info['total']*$activity_info['useful_percent']/100 ;

        // 查询每天释放的算力
        $ext_day  = $end_date_timestamp - strtotime(date('Y-m-d 00:00:00',strtotime($activity_info['start_time']) )) ;
        $ext_day = $ext_day/86400 ;
        //$daily_add_total_power = $activity_info['daily_add']*$ext_day ;
        // 修改为直接去数据库的总供应量
        // 修改为查询每日增量总和
        //$daily_add_obj = new MiningMachineActivityDailyAdd();
        //$daily_add_total_power = $daily_add_obj->getTotalByActivityId($activity_info['id'],$end_date_timestamp);
        $daily_add_total_power = $activity_info['total_supply'] ;
        $total_get_power = $total_get_power > $daily_add_total_power ? $daily_add_total_power : $total_get_power ;

        // 最终释放的有效算力
        $final_power = $total_get_power > $max_useful_power  ? $max_useful_power : $total_get_power;
        return $final_power ;
    }

    /**
     * 获取当前所有有效时间的活动
     * @return mixed
     */
    public function getTotalUsefulList(){
        //当前时间
        $now  = date('Y-m-d H:i:s');
        $params['cond'] = ' :now >= start_time AND :now <= end_time AND status = :status AND is_deleted=:is_deleted ';
        $params['args'] = [':now'=>$now,':status'=>'ENABLED',':is_deleted'=>'N' ];
        $params['fields'] = '*';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list  ;

    }


}
