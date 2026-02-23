<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "sea_mining_machine_user_power".
 *
 * @property int $id
 * @property string $user_id 标题
 * @property string $date 日期
 * @property int $activity_id 活动ID
 * @property int $total 副标题
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MiningMachineUserPower extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_user_power';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'total'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['user_id'], 'string', 'max' => 255],
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
            'date' => 'Date',
            'activity_id' => 'Activity ID',
            'total' => 'Total',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 获取当天信息是否存在
     * @param $user_id
     * @param $activity_id
     * @param $machine_id
     * @return array|bool
     */
    public function getTodayInfo($user_id,$activity_id,$machine_id){

        $params['cond'] = 'date=:date AND activity_id=:activity_id AND user_id=:user_id AND is_deleted="N" AND machine_id=:machine_id';
        $params['args'] = [':date'=>date("Ymd"),':activity_id'=>$activity_id,":user_id"=>$user_id,":machine_id"=>$machine_id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据活动和机器返回能量信息
     * @param $user_id
     * @param $activity_id
     * @param $machine_id
     * @return array|bool
     */
    public function getInfoByActivityAndMachine($user_id,$activity_id,$machine_id){
        $params['cond'] = ' activity_id=:activity_id AND user_id=:user_id AND is_deleted="N" AND machine_id=:machine_id';
        $params['args'] = [':activity_id'=>$activity_id,":user_id"=>$user_id,":machine_id"=>$machine_id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据活动和机器返回能量信息
     * @param $user_id
     * @param $activity_id
     * @param $machine_id
     * @return array|bool
     */
    public function getListByActivityAndMachine($user_id,$activity_id,$machine_id){
        $params['cond'] = ' activity_id=:activity_id AND user_id=:user_id AND is_deleted="N" AND machine_id=:machine_id';
        $params['args'] = [':activity_id'=>$activity_id,":user_id"=>$user_id,":machine_id"=>$machine_id];
        $info = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 更新新增算力
     * @param $id
     * @param $add_total
     * @return mixed
     */
    public function updateInfo($id,$add_total){

        $now = date('Y-m-d H:i:s');
        $update_data['total'] = new Expression('total + '.$add_total);
        $update_data['real_total'] = new Expression('real_total + '.$add_total);
        $update_data['modify_time'] = $now;
        $cond = 'id=:id';
        $args = [':id'=>$id];
        return $this->baseUpdate(self::tableName(),$update_data,$cond,$args);
    }

    /**
     * 新增记录
     * @param $user_id
     * @param $activity_id
     * @param $total
     * @param $machine_id
     * @return  mixed
     */
    public function insertRecord($user_id,$activity_id,$total,$machine_id){

        // 当前时间
        $now = date("Y-m-d H:i:s");

        $add_data['user_id'] = $user_id ;
        $add_data['date'] = date('Ymd') ;
        $add_data['date_timestamp'] = strtotime(date('Y-m-d 00:00:00')) ;
        $add_data['activity_id'] = $activity_id ;
        $add_data['activity_key'] = $activity_id.$user_id ;
        $add_data['total'] = $total ;

        //查询机器抵扣信息
        $machine_obj = new MiningMachine();
        $machine_info = $machine_obj->getInfoById($machine_id,'fee,limit_day');
        // 暂时服务费已经计算到商品的价格当中了
        //$real_total = $machine_info ?  ((100-$machine_info['fee']) /100 ) * $total :$total ;
        $real_total = numberSprintf($total,6);
        // 保留6位小数 不进行四舍五入
        $add_data['real_total'] = $real_total ;
        $add_data['machine_id'] = $machine_id ;
        $add_data['limit_day'] = $machine_info['limit_day'] ;
        $add_data['is_deleted'] = "N" ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;

        return $this->baseInsert(self::tableName(),$add_data);
    }

}
