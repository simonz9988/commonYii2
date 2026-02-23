<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mining_machine_activity_log".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property int $total 总算力
 * @property int $left_total 剩余总算力
 * @property int $daily_add 每天释放算力
 * @property int $frozen 冻结期数
 * @property string $unit_earn 每T产生的收益
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class MiningMachineActivityLog extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_activity_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['start_time', 'end_time', 'create_time', 'modify_time'], 'safe'],
            [['total', 'left_total', 'daily_add', 'frozen'], 'integer'],
            [['unit_earn'], 'number'],
            [['name'], 'string', 'max' => 255],
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
            'left_total' => 'Left Total',
            'daily_add' => 'Daily Add',
            'frozen' => 'Frozen',
            'unit_earn' => 'Unit Earn',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * @param $id
     * @param $add_data
     * @return mixed
     */
    public function addLog($id,$add_data){

        // 当前时间
        $now  =date('Y-m-d H:i:s');
        $log_data['activity_id'] = $id;
        $log_data['name'] = $add_data['name'];
        $log_data['start_time'] = $add_data['start_time'];
        $log_data['end_time'] = $add_data['end_time'];
        $log_data['total'] = $add_data['total'];
        $log_data['daily_add'] = $add_data['daily_add'];
        $log_data['frozen'] = $add_data['frozen'];
        $log_data['unit_earn'] = $add_data['unit_earn'];
        $log_data['daily_add'] = $add_data['daily_add'];
        $log_data['create_time'] = $now;
        $log_data['modify_time'] = $now;
        return $this->baseInsert(self::tableName(),$log_data);
    }

    /**
     * 根据活动查询执行的盈利信息
     * @param $activity_id
     * @param $end_date_timestamp
     * @return mixed
     */
    public function getUnitEarn($activity_id,$end_date_timestamp){

        $end_time = date("Y-m-d 23:59:59",$end_date_timestamp-86400);
        $start_time = date("Y-m-d 00:00:00",$end_date_timestamp-86400);
        $params['cond'] = ' activity_id=:activity_id AND create_time >=:start_time AND create_time <=:end_time';
        $params['args'] = [':activity_id'=>$activity_id,':start_time'=>$start_time,':end_time'=>$end_time];
        $params['orderby'] = 'id DESC';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['unit_earn']: 0 ;
    }
}
