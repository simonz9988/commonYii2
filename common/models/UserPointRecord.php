<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "sea_user_point_record".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $type IN/OUT
 * @property int $point 积分值
 * @property int $timestamp 实际产生的时间戳
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class UserPointRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_user_point_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'point', 'timestamp'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['type'], 'string', 'max' => 50],
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
            'type' => 'Type',
            'point' => 'Point',
            'timestamp' => 'Timestamp',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 处理入金记录
     * @return mixed
     */
    public function addInRecord(){

        $site_config_obj = new SiteConfig();
        $to_address = $site_config_obj->getByKey('cash_insert_eth_address');
        $to_address = strtolower($to_address);
        // 查询当前未处理的入金记录
        $params['cond'] = '`to`=:to AND is_deal_cash_in=:is_deal_cash_in';
        $params['args'] = [':to'=>$to_address,':is_deal_cash_in'=>'N'];
        $params['limit'] = 500 ;
        $params['orderby'] = ' timeStamp DESC ' ;
        $list = $this->findAllByWhere('sea_tx_list',$params,self::getDb());

        if(!$list){
            return false ;
        }

        $member_obj = new Member();

        $user_point_obj = new UserPointRecord() ;
        $eth_max_deal_seconds = $site_config_obj->getByKey('eth_max_deal_seconds');
        $now = date('Y-m-d H:i:s');
        foreach($list as $v){
            $ext = time() - $v['timeStamp'];
            if($ext >= $eth_max_deal_seconds){
                // 超出最大处理时间
                $this->baseUpdate('sea_tx_list',['is_deal_cash_in'=>'Y','modify_time'=>$now],'id=:id',[':id'=>$v['id']]);
                continue ;
            }

            $from = $v['from'] ;
            $user_info = $member_obj->getUserInfoByAddress($from);
            if(!$user_info){
                continue ;
            }

            $point = ($v['value']/100000000000000000) * $site_config_obj->getByKey('eth_point_percent');

            $point = intval($point);
            if($point > 0){
                $add_data['user_id'] = $user_info['id'];
                $add_data['type'] = 'IN';
                $add_data['point'] = $point;
                $add_data['timestamp'] = $v['timeStamp'];
                $add_data['hash'] = $v['hash'];
                $add_data['create_time'] = $now;
                $add_data['modify_time'] = $now;
                $this->baseInsert($user_point_obj::tableName(),$add_data) ;

                $user_update_data['point'] = new Expression("point + " . $point);
                $user_update_data['modify_time'] = $now;
                $this->baseUpdate('sea_user',$user_update_data,'id=:id',[':id'=>$user_info['id']]) ;
            }

            $this->baseUpdate('sea_tx_list',['is_deal_cash_in'=>'Y','modify_time'=>$now],'id=:id',[':id'=>$v['id']]);

        }
    }
}
