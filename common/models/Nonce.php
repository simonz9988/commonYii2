<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_nonce".
*/
class Nonce extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_nonce';
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

    public function getLasted(){
        $params['orderby'] = ' nonce DESC ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 新增日志
     * @param $id
     * @return mixed
     */
    public function addRecord($id = 0 ,$tx_hash){

        if($id){
            $add_data['id'] = $id ;
        }
        $add_data['tx_hash'] = $tx_hash ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $id = $this->baseInsert(self::tableName(),$add_data,'db') ;

        return $id ;
    }


}
