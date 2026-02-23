<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_robot_trade_batch_snapshot".
 *
 * @property int $id
 * @property int $user_id 用户Id
 * @property int $user_platform_id 用户平台配置表ID
 * @property int $bacth_id 对应批次表的ID
 * @property string $price 委托价格
 * @property string $min_qty 每次交易数量
 * @property int $block 对应网格的层级
 * @property int $status 交易状态(BUYING-委托购买中,SELLING委托挂单中,BUY-已购买,SELL-已出售)
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class RobotTradeBatchSnapshot extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_trade_batch_snapshot';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_platform_id', 'bacth_id', 'block', 'status'], 'integer'],
            [['price', 'min_qty'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
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
            'user_platform_id' => 'User Platform ID',
            'bacth_id' => 'Bacth ID',
            'price' => 'Price',
            'min_qty' => 'Min Qty',
            'block' => 'Block',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据批次号返回所有的快照信息
     * @param $batch_id
     * @return array
     */
    public function getListByBatchId($batch_id){
        $params['cond'] = 'batch_id =:batch_id';
        $params['args'] = [':batch_id'=>$batch_id];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return  $list ;
    }
}
