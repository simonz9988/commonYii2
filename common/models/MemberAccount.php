<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_member_account".
 *
 * @property string $id
 * @property string $type 当前账户类型
 * @property string $total 当前账户总额
 * @property int $member_id 用户ID
 * @property string $user_name 用户名称
 * @property string $apiKey
 * @property string $secretKey
 * @property string $create_time 创建时间
 */
class MemberAccount extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_member_account';
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
            [['total'], 'number'],
            [['member_id'], 'integer'],
            [['create_time'], 'safe'],
            [['type'], 'string', 'max' => 50],
            [['user_name', 'apiKey', 'secretKey'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'total' => 'Total',
            'member_id' => 'Member ID',
            'user_name' => 'User Name',
            'apiKey' => 'Api Key',
            'secretKey' => 'Secret Key',
            'create_time' => 'Create Time',
        ];
    }

    public function addData($add_data){
        return  $this->baseInsert($this->tableName(),$add_data,'db_okex');
    }
}
