<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_earn_info".
*/
class Airplane extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_airplane';
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
     * 获取所有飞机信息
     * @return 返回值依据
     */
    public function getAll(){
        $params['where_arr']['is_open'] = 1 ;
        return $this->findByWhere(self::tableName(),$params);
    }

    /**
     * 根据ID获取指定飞机的名称
     * @param $id
     * @return string
     */
    public function getNameById($id){
        $params['where_arr']['id'] = $id ;
        $params['return_type'] = 'row';
        $info = $this->findByWhere(self::tableName(),$params);
        return $info ? $info['name']:'';
    }


}
