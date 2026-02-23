<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_admin_bank".
 *
 * @property int $id
 * @property int $admin_user_id 后台管理员用户信息
 * @property string $name 姓名
 * @property string $telphone 电话
 * @property string $address 地址
 * @property string $website 网址
 * @property string $bank_no 银行卡号
 * @property string $bank_name 银行名称
 * @property string $bank_address 开户行地址
 * @property string $alipay_no 支付宝账号
 * @property string $bank_username 开户姓名
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class AdminBank extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['telphone'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 100],
            [['website', 'bank_no', 'bank_name', 'bank_address', 'alipay_no', 'bank_username'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_user_id' => 'Admin User ID',
            'name' => 'Name',
            'telphone' => 'Telphone',
            'address' => 'Address',
            'website' => 'Website',
            'bank_no' => 'Bank No',
            'bank_name' => 'Bank Name',
            'bank_address' => 'Bank Address',
            'alipay_no' => 'Alipay No',
            'bank_username' => 'Bank Username',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    public function getInfoByAdminUserId($admin_user_id){
        $params['cond'] = ' admin_user_id = :admin_user_id';
        $params['args'] = [':admin_user_id'=>$admin_user_id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }
}
