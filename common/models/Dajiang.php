<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_dajiang".
 */
class Dajiang extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_dajiang';
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
     * @return array|bool获取最新的大奖信息
     */
    public function getLastedInfo(){
        $params['cond'] = 'is_done =:is_done ';
        $params['args'] = [':is_done'=>'N'];
        $params['orderby'] = 'next_period_time DESC';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info  ;
    }

    /**
     * 将当赛季更新为已完成
     * @param $season
     * @return mixed
     */
    public function updateDoneBySeason($season){

        $update_data['is_done'] = 'Y' ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $this->baseUpdate(self::tableName(),$update_data,'season=:season',[':season'=>$season],'db') ;
    }

    /**
     * 添加24小时奖励
     * @param $season
     * @return mixed
     */
    public function add24Dajiang($season){

        //查询season的开始时间，结束时间
        $params['cond'] = 'season =:season' ;
        $params['args'] = [':season'=>$season];
        $params['orderby'] = 'start_time ASC' ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb()) ;
        if(!$list){
            return true ;
        }

        $start_time = $list[0]['start_time'] ;
        $total_num = count($list) ;
        $end_time = $list[$total_num-1]['next_period_time'] ;

        //查询符合条件的用户id
        $user_record = [] ;
        $cash_insert_model = new CashInsert();
        $cash_params['cond'] = 'timeStamp >= :start_time AND timeStamp <= :end_time';
        $cash_params['args'] = [':start_time'=>strtotime($start_time),':end_time'=>strtotime($end_time)];
        $cash_params['orderby'] = 'id desc' ;
        $list = $cash_insert_model->findAllByWhere($cash_insert_model::tableName(),$cash_params,$cash_insert_model::getDb()) ;
        if(!$list){
            return true ;
        }

        foreach($list as $v){
            if($v['amount'] > 0.1){
                $user_record = $v ;
                break ;
            }
        }

        // 查询该时间段的总入金值

        $cash_params['cond'] = 'timeStamp >= :start_time AND timeStamp <= :end_time';
        $cash_params['args'] = [':start_time'=>strtotime($start_time),':end_time'=>strtotime($end_time)];
        $cash_params['fields'] = 'sum(amount)  as total';
        $info = $cash_insert_model->findOneByWhere($cash_insert_model::tableName(),$cash_params,$cash_insert_model::getDb()) ;
        $total = $info && !is_null($info['total']) ? $info['total'] : 0 ;

        if($total > 0  && $user_record ){
            // 盘面余额的10%作为奖励
            $total = $total * 0.1 ;

            $add_data['user_id'] = $user_record['id'] ;
            $add_data['son_user_id'] =0 ;
            $add_data['user_level'] = $user_record['user_level'] ;
            $add_data['user_root_path'] = $user_record['user_root_path'] ;
            $add_data['day'] = $user_record['date'] ;
            $add_data['type'] = '24DAJIANG';
            $add_data['total'] = $total;
            $add_data['season'] = $season ;
            $add_data['base_percent'] = 0;
            $add_data['extra_percent'] = 0 ;
            $add_data['extra_level'] = 0 ;
            $add_data['is_tx'] = 'N' ;
            $add_data['status'] = 'UNDEAL' ;

            $earn_model = new EarnInfo() ;
            return $earn_model->baseInsert($earn_model::tableName(),$add_data) ;
        }

        return true ;
    }

    /**
     * 添加节点大奖
     * @param $user_id
     * @return mixed
     */
    public function addJiedianDajiang($user_id,$timeStamp){

        $cash_model = new CashInsert();
        // 获取盘面余额
        $total_insert_params['cond'] = 'timeStamp <=:timeStamp ';
        $total_insert_params['args'] = [':timeStamp'=>$timeStamp];
        $total_insert_params['fields'] = ' sum(amount) as total ' ;
        $total_insert_info = $cash_model->findOneByWhere($cash_model::tableName(),$total_insert_params,$cash_model::getDb());
        $total_insert = isset($total_insert_info['total']) && !is_null($total_insert_info['total']) ? $total_insert_info['total'] : 0 ;

        $earn_model = new EarnInfo() ;
        $out_params['cond'] = "day <=:day" ;
        $out_params['args'] = [':day'=>date('Y-m-d',$timeStamp)] ;
        $out_params['fields'] = ' sum(total) as total_sum ' ;
        $total_out_info = $earn_model->findOneByWhere($earn_model::tableName(),$out_params,$earn_model::getDb());
        $total_out = isset($total_out_info['total_sum']) && !is_null($total_out_info['total_sum']) ? $total_out_info['total_sum'] : 0 ;

        $ext = $total_insert - $total_out ;
        $ext = $ext*0.1 ;
        if($ext <=0){
            return false ;
        }

        // 需求变更 1 变更为0.1个
        $total_user_params['cond'] = 'timeStamp <=:timeStamp AND amount >= 0.1 and user_id !=:user_id' ;
        $total_user_params['args'] = [':timeStamp'=>$timeStamp,':user_id'=>$user_id];
        $total_user_params['group_by'] = 'user_id';
        $total_user_num = $cash_model->findCountByWhere($cash_model::tableName(),$total_user_params,$cash_model::getDb()) ;

        $amount = ($ext*0.2)/$total_user_num ;

        $total_page = ceil($total_user_num /LOOP_LIMIT_NUM) ;
        for($i=0;$i<$total_page;$i++){
            $total_user_params['page']['curr_page'] = $i+1 ;
            $total_user_params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $total_user_list = $cash_model->findAllByWhere($cash_model::tableName(),$total_user_params,$cash_model::getDb()) ;
            $batch_data = [] ;
            foreach($total_user_list as $v){

                $temp_add = [] ;

                $temp_add[] = $v['user_id'] ;
                $temp_add[] = 0 ;
                $temp_add[] = $v['user_type']  ;
                $temp_add[] = $v['user_level'] ;
                $temp_add[] = $v['user_root_path'] ;
                $temp_add[] = date('Y-m-d',$timeStamp) ;
                $temp_add[] = 'JIEDUANDAJIANG' ;
                $temp_add[] = $amount ;
                $temp_add[] = 'N' ;
                $temp_add[] = 'UNDEAL' ;
                $temp_add[] = date('Y-m-d H:i:s');
                $temp_add[] = date('Y-m-d H:i:s');

                $batch_data[] = $temp_add ;

            }

            if($batch_data){
                $fields = ['user_id','son_user_id','user_type','user_level','user_root_path','day','type','total','is_tx','status','create_time','modify_time'];
                $earn_model->baseBatchInsert($earn_model::tableName(),$fields,$batch_data,'db');
            }

        }

        // 指定为自己的用户
        $user_params['cond'] = 'eth_address=:eth_address';
        //0x5b1a0ea73d8d22d20ec9504d416897bac5852bd2
        $user_params['args'] = [':eth_address'=>strtolower('0x5B1A0ea73D8D22D20EC9504D416897Bac5852BD2')];
        $user_info = $cash_model->findOneByWhere('sea_user',$user_params);
        $batch_data = [];

        $temp_add = [] ;
        $amount = $ext*0.8;
        $temp_add[] = $user_info['id'] ;
        $temp_add[] = 0 ;
        $temp_add[] = $user_info['type'] ;
        $temp_add[] = $user_info['user_level'] ;
        $temp_add[] = $user_info['user_root_path'] ;
        $temp_add[] = date('Y-m-d',$timeStamp) ;
        $temp_add[] = 'JIEDUANDAJIANG' ;
        $temp_add[] = $amount ;
        $temp_add[] = 'N' ;
        $temp_add[] = 'UNDEAL' ;
        $temp_add[] = date('Y-m-d H:i:s');
        $temp_add[] = date('Y-m-d H:i:s');
        $batch_data[] = $temp_add ;

        if($batch_data){
            $fields = ['user_id','son_user_id','user_type','user_level','user_root_path','day','type','total','is_tx','status','create_time','modify_time'];
            $earn_model->baseBatchInsert($earn_model::tableName(),$fields,$batch_data,'db');
        }
        //幸运奖池的 80% 由【胜出玩家 - 即本轮最后一位投入ETH的用户】获得。
        //幸运奖池的 20% 由【所有玩家-投入大于1个ETH的用户】 共同瓜分


    }
}
