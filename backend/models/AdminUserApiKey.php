<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "sea_admin_user_api_key".
 *
 * @property int $id
 * @property int $admin_user_id
 * @property int $total_api_key_id
 * @property string $host 来源域名
 * @property string $create_time 创建时间
 */
class AdminUserApiKey extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_user_api_key';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_user_id', 'total_api_key_id'], 'required'],
            [['admin_user_id', 'total_api_key_id'], 'integer'],
            [['host', 'create_time'], 'string', 'max' => 100],
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
            'total_api_key_id' => 'Total Api Key ID',
            'host' => 'Host',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 根据后台管理员ID返回拥有的集合
     * @param $admin_user_id
     * @return mixed
     */
    public function getKeyIdsByAdminUserId($admin_user_id){

        $params['cond'] = 'admin_user_id=:admin_user_id';
        $params['args'] = [':admin_user_id'=>$admin_user_id];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return  [] ;
        }

        $res = [];
        foreach($list as $v){
            $res[] = $v['total_api_key_id'] ;
        }
        return $res ;
    }

    /**
     * 保存信息
     * @param $admin_user_id
     * @param $pri_arr
     * @return mixed
     */
    public function addByAdminUserAndKeyIds($admin_user_id,$pri_arr){

        $this->baseDelete(self::tableName(),'admin_user_id=:admin_user_id', [':admin_user_id'=>$admin_user_id]);
        if(!$pri_arr){
            return true ;
        }

        $total_api_key_obj = new AdminTotalApiKey();
        // 当前时间
        $now = date('Y-m-d H:i:s');
        foreach($pri_arr as $v){

            $add_data['admin_user_id'] = $admin_user_id ;
            $add_data['total_api_key_id'] = $v ;
            $api_key_info = $total_api_key_obj->getInfoById($v);

            $add_data['host'] = $api_key_info ? $api_key_info['host'] :'' ;
            $add_data['create_time'] = $now ;
            $this->baseInsert(self::tableName(),$add_data);
        }

        return true ;
    }


}
