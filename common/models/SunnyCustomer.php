<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_customer".
 *
 * @property int $id
 * @property int $user_id 关联用户ID
 * @property string $customer_no 客户编号
 * @property string $name 客户名称
 * @property string $address 客户地址
 * @property string $contact1 联系人1
 * @property string $contact2 联系人2
 * @property string $phone1 联系电话1
 * @property string $phone2 联系电话2
 * @property string $email1 邮箱1
 * @property string $email2 邮箱2
 * @property int $country_id 国家ID
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyCustomer extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_customer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'country_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['customer_no', 'name', 'contact1', 'contact2', 'email1', 'email2'], 'string', 'max' => 255],
            [['address'], 'string', 'max' => 1000],
            [['phone1', 'phone2'], 'string', 'max' => 50],
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
            'customer_no' => 'Customer No',
            'name' => 'Name',
            'address' => 'Address',
            'contact1' => 'Contact1',
            'contact2' => 'Contact2',
            'phone1' => 'Phone1',
            'phone2' => 'Phone2',
            'email1' => 'Email1',
            'email2' => 'Email2',
            'country_id' => 'Country ID',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
}
