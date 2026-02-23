<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_count_down_record".
*/
class CountDownRecord extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_count_down_record';
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
     * 获取最新的截止时间
     * @param $date
     * @return string
     */
    public function getEndTime($date){
        $params['cond'] = 'is_kj =:is_kj' ;
        $params['args'] = [':is_kj'=>'N'];
        $params['orderby'] = ' deadline_time DESC ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['deadline_time'] : date('Y-m-d 23:59:59',strtotime($date)) ;
    }


    /**
     * 获取最新的开始时间
     * @return string
     */
    public function getStartTime(){
        $params['cond'] = 'is_kj =:is_kj' ;
        $params['args'] = [':is_kj'=>'N'];
        $params['orderby'] = ' deadline_time ASC ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['deadline_time'] : '' ;
    }

    /**
     * 新增记录 记录最新的截止时间
     * @param $start_time
     * @param $value
     * @param $timeStamp
     * @return mixed
     */
    public function addRecord($start_time,$value,$timeStamp){

        if($value < 0.1){
            return false ;
        }

        if($value >=0.1 && $value < 1){
            // 增加18分钟
        }else{

        }
    }


}
