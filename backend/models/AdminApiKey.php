<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "sea_admin_api_key".
 *
 * @property string $id
 * @property string $admin_user_id 管理员ID
 * @property string $type USD/USDT USD-币本位 USDT-USDT本位
 * @property string $platform 允许的平台
 * @property string $api_key api key
 * @property string $api_secret api secret
 * @property string $passphrase 交易密码
 * @property string $note 备注信息
 * @property int $sort 排序
 * @property string $status 是否有效 ENABLED/DISABLED
 * @property string $create_time 创建时间
 * @property string $modify_time 更新时间
 */
class AdminApiKey extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_api_key';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['admin_user_id', 'type', 'platform'], 'string', 'max' => 20],
            [['api_key', 'api_secret', 'passphrase', 'note'], 'string', 'max' => 100],
            [['status'], 'string', 'max' => 50],
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
            'type' => 'Type',
            'platform' => 'Platform',
            'api_key' => 'Api Key',
            'api_secret' => 'Api Secret',
            'passphrase' => 'Passphrase',
            'note' => 'Note',
            'sort' => 'Sort',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 返回管理员用户的一条密钥信息
     * @param $admin_user_id
     * @return mixed
     */
    public function getRowByAdminUserId($admin_user_id){
        $params['where_arr']['admin_user_id'] = $admin_user_id ;
        $params['where_arr']['admin_user_id'] = $admin_user_id ;
        $params['cond'] = 'admin_user_id =:admin_user_id AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':admin_user_id'=>$admin_user_id,':status'=>'ENABLED',':is_deleted'=>'N'] ;
        $info = $this->findOneByWhere(self::tableName(),$params) ;
        return $info ;
    }

    /**
     * 获取总数目
     * @return int
     */
    public function getTotal(){
        $params['cond'] = 'id>:id';
        $params['args'] = [':id'=>0];
        $list = $this->findAllByWhere(self::tableName(),$params);
        return count($list) ;
    }

    /**
     * 根据ID返回指定账户信息
     * @param $id
     * @return array|bool
     */
    public function getInfoById($id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params);
        return $info ;
    }

    /**
     * 返回未同步列表信息
     * @return mixed
     */
    public function getUnSyncList(){
        $params['cond'] = 'is_sync=:is_sync';
        $params['args'] = [':is_sync'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
}
