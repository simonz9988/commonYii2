<?php

namespace common\models;

use common\components\EthScan;
use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_season_zhitui".
*/
class SeasonZhitui extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_season_zhitui';
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
     * 获取之前一天的最后一个赛季
     * @param $date
     * @return mixed
     */
    public function getPrevDayLastSeason($date){

        $params['cond'] = 'date = :date' ;
        $prev_date = date('Y-m-d',strtotime($date)-86400 );
        $params['args'] = [':date'=>$prev_date];
        $params['orderby'] = 'season DESC';
        $info = $this->findOneByWhere(self::tableName(),$params);
        return $info ? $info['season'] : 0 ;
    }

    /**
     *
     * @param $parent_user_list
     * @param $parent_user_ids
     * @param $date
     * @return mixed
     */
    public function copyPrevDayInfo($parent_user_list,$parent_user_ids,$date){

        // 查询之前一天的记录，不存在的用户ID需要copy 存在的ID需要重新插入
        if($parent_user_list){
            $prev_day_params['cond'] =  'date=:date   AND season=:season AND user_id not in ('.implode(',',$parent_user_ids).')';
        }else{
            $prev_day_params['cond'] =  'date=:date AND season=:season ';
        }

        $season = $this->getPrevDayLastSeason($date) ;
        $prev_day = date('Y-m-d' ,(strtotime($date) - 86400 ) ) ;
        $prev_day_params['args'] = [':date'=>$prev_day,':season'=>$season];

        $prev_day_total = $this->findCountByWhere(self::tableName(),$prev_day_params,self::getDb()) ;

        $prev_day_page = ceil($prev_day_total/LOOP_LIMIT_NUM) ;
        for($i = 0 ;$i<$prev_day_page;$i++){

            $prev_day_params['page']['curr_page'] = $i+1 ;
            $prev_day_params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $prev_day_list  = $this->findAllByWhere(self::tableName(),$prev_day_params,self::getDb());

            if($prev_day_list){
                $batch_data = [] ;
                foreach($prev_day_list as $v){
                    $temp_add = [] ;

                    $temp_add[] = $v['user_id'] ;
                    $temp_add[] = $v['season'] ;
                    $temp_add[] = $v['zhenying'] ;
                    $temp_add[] = $date;
                    $temp_add[] = $v['total'] ;
                    $temp_add[] = date('Y-m-d H:i:s');
                    $temp_add[] = date('Y-m-d H:i:s');

                    $batch_data[] = $temp_add ;
                }

                if($batch_data){
                    $fields = ['user_id','season','zhenying','date','total','create_time','modify_time'];
                    $this->baseBatchInsert(self::tableName(),$fields,$batch_data,'db');
                }
            }

        }
    }
}
