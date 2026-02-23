<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_gong_zhen".
*/
class GongZhen extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_gong_zhen';
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
     * 返回共振启动的总量
     * @return array
     */
    public function returnGongZhengQidong(){
        // 返回共振启动的资金量 位置周期是10天
        return array(
            '',
            [
                'total' => 10000, //触发条件 1W个
                'limit' =>10 , //持续时间10天
            ]
        ) ;


    }



    /**
     * 获取当前日期符合条件的共振日期
     * @param  string $date
     * @return boolean
     */
    public function checkInAllowed($date){
        $params['cond'] = "id > :id";
        $params['args'] = [':id'=>0] ;
        $params['order_by'] = 'date desc';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        // 获取所有共振的配置信息
        $total_gongzhen = $this->returnGongZhengQidong();

        if(!$info){

            // 获取第一个共振信息
            $current_gongzheng = $total_gongzhen[1] ;

            // 查看是否满足当前总的入金
            $cash_insert_model = new CashInsert();
            $total_insert = $cash_insert_model->getTotalInsertFromEndTime($date);
            if($total_insert >= $current_gongzheng['total']){
                $add_data['date'] = $date ;
                $add_data['stage'] = 1 ;
                $add_data['create_time'] = date('Y-m-d H:i:s');
                $add_data['modify_time'] = date('Y-m-d H:i:s');
                $insert_id = $this->baseInsert(self::tableName(),$add_data,'db');
                if(!$insert_id){
                    return false ;
                }

                $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
                return $info  ;
            }else{
                return false ;
            }


        }

        $current_gongzheng = $total_gongzhen[$info['stage']] ;

        //判断有没有超过当前时间的持续周期  单位为天
        $ext = (strtotime($date) - strtotime($info['date']) )/86400 ;
        if($ext <= $current_gongzheng['limit']){
            return $info ;
        }

        // 查看有没有符合下一阶段的要求
        $next_stage = $info['stage'] + 1 ;
        if(!isset($total_gongzhen[$next_stage])){
            return false ;
        }

        #TODO 下一阶段暂时不做
    }
}
