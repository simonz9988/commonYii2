<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mining_machine_cash_in_record".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $amount 入金总金额
 * @property string $status 处理状态NOPUSH-未处理 PUSHED已处理
 * @property int $inviter_user_id 邀请人用户ID
 * @property string $user_root_path 用户推广路径
 * @property int $user_level 用户层级
 * @property string $blockHash 交易Hash值
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class MiningMachineCashInRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_cash_in_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'inviter_user_id', 'user_root_path'], 'required'],
            [['user_id', 'inviter_user_id', 'user_level'], 'integer'],
            [['amount'], 'number'],
            [['user_root_path'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['status', 'blockHash'], 'string', 'max' => 50],
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
            'amount' => 'Amount',
            'status' => 'Status',
            'inviter_user_id' => 'Inviter User ID',
            'user_root_path' => 'User Root Path',
            'user_level' => 'User Level',
            'blockHash' => 'Block Hash',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
}
