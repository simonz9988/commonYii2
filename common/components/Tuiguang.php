<?php
namespace common\components;

use common\models\CashInsert;
use common\models\DayBalance;
use common\models\EarnInfo;
use common\models\Zhitui;

class Tuiguang
{
    public function init(){
        #TODO
    }


    public function addTgjl($start_date,$end_date){
        $redis_component = new SetUserRedis();
        $complete_tag = $redis_component->getTagByType('JTSY',$end_date) ;
        if(!$complete_tag){
            return true ;
        }


        $model = new Zhitui();
        $ext = $model->getExtByTime($start_date,$end_date);
        if(!$ext){
            $this->addTgjlByDate($start_date) ;
        }

        for($i=0;$i<=$ext;$i++){
            $date = date('Y-m-d',strtotime($start_date) + $i*86400) ;
            $this->addTgjlByDate($date) ;
        }
    }

    /**
     * 根据日期查看推广奖励
     * @param $date
     * @return mixed
     */
    public function addTgjlByDate($date){

        $redis_component = new SetUserRedis();

        // 判断是否达到开启的条件
        $cash_insert_model = new CashInsert();
        if(!$cash_insert_model->checkStart($date)){
            return $redis_component->setTagByType('TGJL',$date) ;
        }

        // 判断是否已经完成
        if($redis_component->getTagByType('TGJL',$date)){
            return true ;
        }

        $model = new Zhitui();

        $params['cond'] = 'date=:date';
        $params['args'] = [':date'=>$date];
        $total_num = $model->findCountByWhere($model::tableName(),$params,$model::getDb());

        $total_page = $total_num/LOOP_LIMIT_NUM ;

        $total_user_balance = [];

        $earn_list = [];

        $total_list = [];
        // 便利所有用户信息
        for($i=0;$i<$total_page;$i++){

            $params['page']['curr_page'] = $i+1 ;
            $params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $list = $model->findAllByWhere($model::tableName(),$params,$model::getDb());
            $total_list[$i] = $list ;

            foreach($list as $v){
                $user_id = $v['user_id'] ;
                $total_user_balance[$user_id] = $v ;
            }
        }

        for($i=0;$i<$total_page;$i++){
            $list = $total_list[$i] ;
            foreach($list as $v){
                $user_root_path_arr = explode('--',trim($v['user_root_path'],'--')) ;
                $total_num = count($user_root_path_arr)  -1 ;
                $parent_1_user_id = isset($user_root_path_arr[$total_num]) ? $user_root_path_arr[$total_num] : 0;

                if($parent_1_user_id){
                    //直接一层
                    $total = $this->getTotal($parent_1_user_id,$v['balance'],1,$total_user_balance) ;
                    if($total > 0 ){
                        $earn_list[$parent_1_user_id]['user_id'] = $parent_1_user_id ;
                        if(isset($earn_list[$parent_1_user_id]['total'])){
                            $earn_list[$parent_1_user_id]['total'] = $earn_list[$parent_1_user_id]['total'] + $total ;
                        }else{
                            $earn_list[$parent_1_user_id]['total'] = $total ;
                        }
                    }
                }

                $parent_2_user_id = isset($user_root_path_arr[$total_num-1]) ? $user_root_path_arr[$total_num-1] : 0;

                if($parent_2_user_id){
                    //直接二层
                    $total = $this->getTotal($parent_2_user_id,$v['balance'],2,$total_user_balance) ;
                    if($total > 0 ){
                        $earn_list[$parent_2_user_id]['user_id'] = $parent_2_user_id ;
                        if(isset($earn_list[$parent_2_user_id]['total'])){
                            $earn_list[$parent_2_user_id]['total'] = $earn_list[$parent_2_user_id]['total'] + $total ;
                        }else{
                            $earn_list[$parent_2_user_id]['total'] = $total ;
                        }
                    }
                }

                $parent_3_user_id = isset($user_root_path_arr[$total_num-2]) ? $user_root_path_arr[$total_num-2] : 0;

                if($parent_3_user_id){

                    //直接三层
                    $total = $this->getTotal($parent_3_user_id,$v['balance'],3,$total_user_balance) ;
                    if($total > 0 ){
                        $earn_list[$parent_3_user_id]['user_id'] = $parent_3_user_id ;
                        if(isset($earn_list[$parent_3_user_id]['total'])){

                            $earn_list[$parent_3_user_id]['total'] = $earn_list[$parent_3_user_id]['total'] + $total ;
                        }else{
                            $earn_list[$parent_3_user_id]['total'] = $total ;
                        }
                    }
                }
            }
        }

        if($earn_list){
            // 批量增加
            $trunk_list = array_chunk($earn_list, LOOP_LIMIT_NUM);

            foreach($trunk_list as $trunk_list_son) {

                $res = [];
                foreach ($trunk_list_son as $k => $v) {


                    $user_id = $v['user_id'];
                    $total = $v['total'];

                    $total = checkMaxEarn($total, $user_id, $date);
                    $balance = $total_user_balance[$user_id]['balance'];

                    if ($balance <= 0) {
                        continue;
                    }



                    if ($total > 0) {

                        $temp_add = [];
                        $temp_add[] = $total_user_balance[$user_id]['user_id'];
                        $temp_add[] = $total_user_balance[$user_id]['user_type'];
                        $temp_add[] = $total_user_balance[$user_id]['user_level'];
                        $temp_add[] = $total_user_balance[$user_id]['user_root_path'];
                        $temp_add[] = $total_user_balance[$user_id]['date'];
                        $temp_add[] = 'TGJL';
                        $temp_add[] = $total;
                        $temp_add[] = 0;
                        $temp_add[] = 'N';
                        $temp_add[] = date('Y-m-d H:i:s');
                        $temp_add[] = date('Y-m-d H:i:s');

                        $res[] = $temp_add;

                        $redis_key = 'DayShouyi:' . $user_id . ':' . $date;
                        $redis_model = new MyRedis();
                        $redis_info = $redis_model->get($redis_key);
                        $redis_model->set($redis_key, $redis_info + $total);


                    }

                }

                if ($res) {
                    $earn_model = new EarnInfo();
                    $fields = ['user_id', 'user_type','user_level', 'user_root_path', 'day', 'type', 'total', 'extra_level', 'is_tx', 'create_time', 'modify_time'];
                    $earn_model->baseBatchInsert($earn_model::tableName(), $fields, $res, 'db');
                }
            }
        }

        //设置redis
        return $redis_component->setTagByType('TGJL',$date) ;
    }

    /**
     * @param $user_id
     * @param $balance
     * @param $son_balance
     * @param $level
     * @param $total_user_balance
     * @return mixed
     */
    public function getTotal($user_id,$son_balance,$level,$total_user_balance){

        $balance = $this->getBalanceByUserList($user_id,$total_user_balance) ;

        $balance = $balance <=$son_balance ? $balance :$son_balance ;

        $user_info = $total_user_balance[$user_id] ;

        $percent = 0 ;

        if($level==1){
            //0.005
            $percent = 0.5 ;
        }

        if($level==2){
            //0.004
            if($user_info['total']>=30){
                $percent = 0.4 ;
            }
        }

        if($level==3){
            //0.003
            if($user_info['total']>=100){
                $percent = 0.3 ;
            }
        }

        $component = new Jingtai();
        $balance_level = $component->getLevel($balance);

        $base_percent = $component->getLevelPercent($balance_level) ;

        $res =   $percent*$balance*$base_percent ;

        return $res ;

    }

    /**
     * 获取用户余额信息
     * @param $user_id
     * @param $user_list
     * @return int
     */
    private function getBalanceByUserList($user_id,$user_list){
        $user_info = $user_list[$user_id];
        $balance =  $user_info['balance'] ;
        $balance = $balance > 50 ? 50 :$balance ;
        return $balance ;
    }


}