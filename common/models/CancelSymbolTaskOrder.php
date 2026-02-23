<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_cancel_symbol_task_order".
 *
 * @property string $id
 * @property string $task_id 用户币种ID
 * @property string $order_id
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class CancelSymbolTaskOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_cancel_symbol_task_order';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_okex');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'update_time'], 'safe'],
            [['task_id', 'order_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Task ID',
            'order_id' => 'Order ID',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
