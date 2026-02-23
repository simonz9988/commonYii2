<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_robot_user_collection".
 *
 * @property int $id
 * @property int $user_id 用户Id
 * @property int $coin_id 对应的用户表的ID
 * @property string $coin_name 币种名称
 * @property string $unique_key 币种唯一关键字
 * @property string $coin_earn_type {"NORMAL":"主流型","STEADY":"稳健性","RADICAL":"激进型"}
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class RobotUserCollection extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_user_collection';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'coin_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['coin_name', 'unique_key', 'coin_earn_type'], 'string', 'max' => 255],
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
            'coin_id' => 'Coin ID',
            'coin_name' => 'Coin Name',
            'unique_key' => 'Unique Key',
            'coin_earn_type' => 'Coin Earn Type',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 增加收藏记录信息
     * @param $user_id
     * @param $coin_info
     * @param $legal_coin
     * @return  mixed
     */
    public function addRecord($user_id,$coin_info,$legal_coin){

        $params['cond'] = 'user_id=:user_id AND coin_id=:coin_id AND is_deleted=:is_deleted AND legal_coin =:legal_coin';
        $params['args'] = [':user_id'=>$user_id,':coin_id'=>$coin_info['id'],':is_deleted'=>'N',':legal_coin'=>$legal_coin];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        // 当前时间
        $now = date('Y-m-d H:i:s');

        $add_data['coin_name'] = $coin_info['name'] ;
        $add_data['unique_key'] = $coin_info['unique_key'] ;
        $add_data['coin_earn_type'] = $coin_info['earn_type'] ;
        $add_data['modify_time'] = $now ;

        if($info){
            return $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$info['id']]);
        }

        $add_data['user_id'] = $user_id ;
        $add_data['legal_coin'] = $legal_coin ;
        $add_data['coin_id'] = $coin_info['id'] ;
        $add_data['is_deleted'] = $coin_info['is_deleted'] ;
        $add_data['create_time'] = $now ;
        return $this->baseInsert(self::tableName(),$add_data);

    }

    /**
     * 取消收藏
     * @param $user_id
     * @param $coin_id
     * @param $legal_coin
     * @return mixed
     */
    public function cancelRecord($user_id,$coin_id,$legal_coin){

        $update_data['is_deleted'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $update_cond = 'user_id=:user_id AND coin_id=:coin_id AND is_deleted=:is_deleted AND legal_coin=:legal_coin';
        $update_args = [':user_id'=>$user_id,':coin_id'=>$coin_id,':is_deleted'=>'N',':legal_coin'=>$legal_coin];
        return $this->baseUpdate(self::tableName(),$update_data,$update_cond,$update_args);
    }

    /**
     * 根据 用户ID和币种信息返回收藏信息
     * @param $user_id
     * @param $coin_id
     * @param $legal_coin
     * @return mixed
     */
    public function getInfoByUserIdAndCoinId($user_id,$coin_id,$legal_coin){

        $params['cond'] = 'user_id=:user_id AND coin_id=:coin_id AND is_deleted=:is_deleted AND legal_coin=:legal_coin';
        $params['args'] = [':user_id'=>$user_id,':coin_id'=>$coin_id,':is_deleted'=>'N',':legal_coin'=>$legal_coin];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据用户ID返回收藏的币种ID
     * @param $user_id
     * @return mixed
     */
    public function getCoinIdsByUser($user_id){
        $params['cond'] = 'user_id=:user_id  AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return  [] ;
        }

        $res = [];
        foreach($list as $v){
            $res[] = $v['coin'];
        }
        return $res ;
    }

    /**
     * 根据用户ID返回收藏列表信息
     * @param $user_id
     * @return array
     */
    public function getListByUserId($user_id){
        $params['cond'] = 'user_id=:user_id  AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N'];
        $params['orderby'] = 'id desc';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
}
