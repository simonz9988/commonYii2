<?php

namespace common\models;

use Yii; 

/**
 * This is the model class for table "sea_mining_machine_activity_daily_add".
 *
 * @property int $id
 * @property int $activity_id 活动ID
 * @property string $date 生成日期
 * @property int $date_timestamp 生成时间戳
 * @property int $total 每日自动录入的总量
 * @property string $is_deleted 是否已经删除
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class MiningMachineActivityDailyAdd extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_activity_daily_add';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'date_timestamp', 'total'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
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
            'activity_id' => 'Activity ID',
            'date' => 'Date',
            'date_timestamp' => 'Date Timestamp',
            'total' => 'Total',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 增加每日算例增量
     * @param $activity_id
     * @param $date_timestamp
     * @param $total
     * @return mixed
     */
    public function addByActivityIdAndEndTimestamp($activity_id,$date_timestamp,$total){
        $params['cond'] = 'activity_id=:activity_id AND date_timestamp=:date_timestamp';
        $params['args'] = [':activity_id'=>$activity_id,':date_timestamp'=>$date_timestamp];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            return false ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');
        $add_data['activity_id'] = $activity_id ;
        $add_data['date'] = date('Ymd',$date_timestamp) ;
        $add_data['total'] = $total ;
        $add_data['date_timestamp'] = $date_timestamp ;
        $add_data['is_deleted'] = 'N' ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 根据活动ID和截止时间返回增量总和
     * @param $activity_id
     * @param $date_timestamp
     * @return  mixed
     */
    public function getTotalByActivityId($activity_id,$date_timestamp){
        $params['cond'] = 'activity_id=:activity_id AND date_timestamp <=:date_timestamp';
        $params['args'] = [':activity_id'=>$activity_id,':date_timestamp'=>$date_timestamp];
        $params['fields'] = 'sum(total) as sum_total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['sum_total']) ? $info['sum_total'] : 0 ;
    }
}
