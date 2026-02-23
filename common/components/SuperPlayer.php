<?php
namespace common\components;

use common\models\CashInsert;
use common\models\CountDownRecord;
use common\models\Dajiang;
use common\models\DayBalance;
use common\models\EarnInfo;
use common\models\Member;
use common\models\TxList;
use common\models\Zhitui;
//超级玩家
class SuperPlayer
{
    // 当前时间段
    public $stage = [0,500,700,1000,1500,2500,4500,9500,19500,33500,50000];

    public function init(){
        #TODO
    }

    /**
     * 根据总额获取当前所处的阶段
     * @param $total
     * @return  integer
     */
    public function getStageByTotal($total){

        if($total < 500){
            return  0 ;
        }else if($total >=500 && $total < 700){
            return  1 ;
        }else if($total >=700 && $total < 1000){
            return  2 ;
        }else if($total >=1000 && $total < 1500){
            return  3 ;
        }else if($total >=1500 && $total < 2500){
            return  4 ;
        }else if($total >=2500 && $total < 4500){
            return  5 ;
        }else if($total >=4500 && $total < 9500){
            return  6 ;
        }else if($total >=9500 && $total < 19500){
            return  7 ;
        }else if($total >=19500 && $total < 33500){
            return  8 ;
        }else if($total >=33500 && $total < 50000){
            return  9 ;
        }else{
            return  10 ;
        }

    }

    /**
     * 根据得到的阶段获得已经得到的奖励
     * @param $stage
     * @return int|mixed
     */
    public function getJiangByStage($stage){
        $all =  [0,50,20,30,50,100,200,500,1000,1400,1650] ;
        return isset($all[$stage])? $all[$stage] : 0 ;
    }

    /**
     * 添加指定时间内的超级玩家
     * @param $start_time
     * @param $end_time
     */
    public function send($start_time,$end_time){

        $redis_component = new SetUserRedis();
        $complete_tag = $redis_component->getTagByType('TDJL',$end_time) ;
        if(!$complete_tag){
            return true ;
        }

        // 必须处理完day_balance 才能进行下面的操作

        $ext =( strtotime($end_time) - strtotime($start_time) ) / 86400 ;
        if(!$ext){
            $this->sendByDate($start_time) ;
        }else{
            for($i =0 ;$i<=$ext ;$i++){
                $date = date('Y-m-d',strtotime($start_time)+$i*86400) ;

                $this->sendByDate($date) ;
            }
        }
    }

    public function getTotalByUserId($user_id){
        $model = new MyRedis();
        $redis_key = "SuperUser:total:".$user_id ;
        $redis_info = floatval($model->get($redis_key)) ;
        return $redis_info ;
    }

    public function setTotalByUserId($user_id,$total){
        $model = new MyRedis();
        $redis_key = "SuperUser:total:".$user_id ;
        return $model->set($redis_key,$total) ;
    }

    /**
     * 按日期记性超级玩家
     * @param $date
     * @return bool|mixed
     */
    public function sendByDate($date){

        // 判断是否达到开启的条件
        $cash_insert_model = new CashInsert();
        #TODO 不需要

        // 判断当天是否已经处理过
        $redis_component = new SetUserRedis();
        if($redis_component->getTagByType('SUPERPLAYER',$date)){
            return true ;
        }

        // 获取所有的直推的信息
        $zhitui_model = new Zhitui();
        $total_zhitui_num = $zhitui_model->getTotalNumByDate($date);
        $total_zhitui_page = ceil($total_zhitui_num/LOOP_LIMIT_NUM);

        $zhitui_user_list = [];
        for($i=0;$i<$total_zhitui_page;$i++){
            $zhitui_list = $zhitui_model->getListByDateAndPage($date,$i);
            foreach($zhitui_list as $v){
                $zhitui_user_list[$v['user_id']] = $v;
            }
        }

        $earn_info_model = new EarnInfo() ;

        $total_cash_num = $cash_insert_model->getTotalNumByDate($date);
        $total_page = ceil($total_cash_num/LOOP_LIMIT_NUM) ;

        $member_model = new Member() ;

        for($i=0 ;$i<$total_page;$i++){

            $list = $cash_insert_model->getListByDateAndPage($date,$i);

            foreach($list as $v) {
                $user_root_path  = $v['user_root_path'] ;
                $user_root_arr = explode('--',$user_root_path) ;

                $amount = $v['amount'] ;

                if($amount <=0){
                    continue ;
                }

                $batch_data = [] ;

                foreach($user_root_arr as $user_id){

                    if(!$user_id){
                        continue ;
                    }

                    $user_amount = $redis_component->getUserSuperInfo($user_id) ;

                    $stage = $this->getStageByTotal($user_amount);

                    $user_amount = $user_amount + $amount ;
                    $stage1 = $this->getStageByTotal($user_amount);

                    // 设置用户实时直推总量
                    $redis_component->setUserSuperInfo($user_id,$amount) ;

                    if($stage !=$stage1){

                        $earn_total = $this->getJiangByStage($stage1) ;

                        $temp_add = [] ;

                        // 玩家必须为超级玩家才能够产品超级玩家的收益
                        if(!$member_model->checkIsSuper($user_id)){
                            continue ;
                        }

                        // 判断是否存在，存在直接更新操作
                        $check_exists = $earn_info_model->checkSuperExistsByDate($user_id,$date,$earn_total);
                        if($check_exists){
                            continue ;
                        }

                        $user_info = $zhitui_user_list[$user_id] ;
                        $temp_add['user_id'] = $user_id ;
                        $temp_add['user_type'] = $user_info['user_type'] ;
                        $temp_add['user_level'] = $user_info['user_level'] ;
                        $temp_add['user_root_path'] = $user_info['user_root_path'] ;

                        $temp_add['day'] = $date ;
                        $temp_add['total'] = $earn_total ;
                        $temp_add['season'] = 0 ;
                        $temp_add['type'] = 'SUPERPLAYER';
                        $temp_add['is_tx'] = 'N' ;
                        $temp_add['status'] = 'UNDEAL' ;
                        $temp_add['create_time'] = date('Y-m-d H:i:s');
                        $temp_add['modify_time'] = date('Y-m-d H:i:s');

                        $earn_info_model->baseInsert($earn_info_model::tableName(),$temp_add);
                    }

                }

                /*
                if($batch_data){
                    $fields = ['user_id','user_type','user_level','user_root_path','day','total','season','type','is_tx','status','create_time','modify_time'];
                    $earn_info_model->baseBatchInsert($earn_info_model::tableName(),$fields,$batch_data,'db');
                }*/
            }
        }

        return $redis_component->setTagByType('SUPERPLAYER',$date) ;
    }



}