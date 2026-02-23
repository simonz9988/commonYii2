<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_cash_record".
*/
class CashRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_cash_record';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [] ;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [] ;
    }

    /**
     * 添加转账需要的快照记录
     * @param integer $value
     * @param array   $from_user
     * @param integer $business_id
     * @param string  $date
     * @return mixed
     */
    public function addTrans($value,$from_user,$business_id,$date){

        // 插入每日充值记录表


        //根据比例要生成指定的token
        $site_config_model = new SiteConfig() ;
        $eth_to_token_num =  $site_config_model->getByKey('eth_to_token_num');

        //需要转出的token数量
        $token_num = intval($eth_to_token_num*$value) ;
        if(!$token_num){
            return true ;
        }

        // 任务模型
        $push_task_model = new PushTask();

        $task_params['cond'] = 'business_id =:business_id AND business_type=:business_type ';
        $task_params['args'] = [':business_id'=>$business_id ,':business_type'=>'TRANS_TOKEN'];
        $task_info = $push_task_model->findOneByWhere($push_task_model::tableName(),$task_params,$push_task_model::getDb());
        if($task_info){
            return true ;
        }

        // 转Token的业务ID对应的是充值记录表的ID
        $task_add_data['business_id'] = $business_id ;
        $task_add_data['business_type'] = 'TRANS_TOKEN';
        $task_add_data['business_time'] = time();
        $task_add_data['business_timestamp'] = date('Y-m-d H:i:s');
        $task_add_data['to_address'] = $from_user['address'];
        $task_add_data['token_num'] = $token_num;
        $task_add_data['tx_hash'] = '';
        $task_add_data['admin_allowed'] = 'Y';
        $task_add_data['status'] = 'NOPUSH';
        $task_add_data['push_url'] = '';
        $task_add_data['create_time'] = date('Y-m-d H:i:s');
        $task_add_data['modify_time'] = date('Y-m-d H:i:s');

        return $this->baseInsert($push_task_model::tableName(), $task_add_data, 'db');
    }


}
