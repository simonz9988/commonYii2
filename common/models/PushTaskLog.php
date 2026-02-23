<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_push_task_log".
*/
class PushTaskLog extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_push_task_log';
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
     * 新增日志
     * @param integer $id
     * @param string $response
     * @return mixed
     */
    public function addRecord($id,$response){

        $add_data['push_task_id'] = $id ;
        $add_data['response_data'] = $response ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $this->baseInsert(self::tableName(),$add_data,'db') ;

        return true ;
    }


}
