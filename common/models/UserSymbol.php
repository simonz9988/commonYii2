<?php

namespace common\models;

use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sdb_user_symbol".
 *
 * @property string $id
 * @property string $user_id
 * @property string $symbol_key
 * @property string $apiKey
 * @property string $secretKey
 * @property string $status 是否有效 enabled-有效disabled-无效 dealing-处理中
 * @property int $is_open 是否删除
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class UserSymbol extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_user_symbol';
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
            [['is_open'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['user_id', 'symbol_key', 'apiKey', 'secretKey', 'status'], 'string', 'max' => 255],
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
            'symbol_key' => 'Symbol Key',
            'apiKey' => 'Api Key',
            'secretKey' => 'Secret Key',
            'status' => 'Status',
            'is_open' => 'Is Open',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public function getUserSymbolId($symbol,$apiKey,$secretKey){


        $params['cond'] = 'apiKey =:apiKey AND secretKey=:secretKey and  symbol_key=:symbol_key';
        $params['args'] = [':apiKey'=>$apiKey,':secretKey'=>$secretKey,':symbol_key'=>$symbol];

        $row = $this->findOneByWhere('sdb_user_symbol',$params,self::getDb());

        return $row?$row['id']:0;
    }

    /**
     * 根据用户ID
     * @param $user_id
     */
    public function getUserSymbolListById($user_id){

        $redis_key = 'UserSymbolList:'.$user_id;
        $redis_model = new MyRedis();
        $redis_info = $redis_model->get($redis_key);
        if($redis_info){
            $user_symbol_list = json_decode($redis_info,true);
        }else{
            $symbol_model = new UserSymbol();
            $params['cond'] = 'status =:status AND is_open=:is_open AND user_id=:user_id';
            $params['args'] = [':status'=>'enabled',':is_open'=>1,':user_id'=>$user_id] ;
            $user_symbol_list = $symbol_model->findAllByWhere('sdb_user_symbol',$params,$symbol_model::getDb());

            $redis_model->set($redis_key,json_encode($user_symbol_list),360);
        }
        return $user_symbol_list ;
    }

    /**
     * 根据ID获取单条信息
     * @param $id
     * @return array|bool
     */
    public function getRowInfoById($id){

        $params['cond'] = 'id =:id ';
        $params['args'] = [':id'=>$id];

        $row = $this->findOneByWhere('sdb_user_symbol',$params,self::getDb());
        return $row ;
    }

    public function getListByPlatformAndMinute($platform,$minute){

        $redis_key = 'UserSymbol:'.$platform.':'.$minute.':list';
        $redis_model = new MyRedis();
        $redis_info = $redis_model->get($redis_key);
        if($redis_info){
            $list = json_decode($redis_info,true);
        }else{

            $model = new UserSymbol();
            $params['cond'] = 'platform =:platform';
            $params['args'] = [':platform'=>$platform];
            $params['group_by'] = 'symbol_key';
            $list = $model->findAllByWhere('sdb_user_symbol',$params,$model::getDb());
            $redis_model->set($redis_key,json_encode($list),210+$minute);
        }
        return $list ;

    }
}
