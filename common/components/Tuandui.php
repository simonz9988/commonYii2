<?php
namespace common\components;
//团队业绩奖励
use common\models\CashInsert;
use common\models\DayBalance;
use common\models\EarnInfo;
use common\models\Zhitui;
use yii\db\Expression;

class Tuandui
{
    public function init(){
        #TODO
    }

    /**
     * 两部判断，返回最终符合条件团队级别
     * @param $zhitui
     * @param $team_total
     * @return int
     */
    public function getLevel($zhitui,$team_total){
       $total = $team_total-$zhitui ;

       $level = 0 ;

       // 先判断有没有初步符合拿取到团队业绩的要求
       //if($total >=1000 && $total <2000){
       if($total >=1000 && $total <2000){
           //level1
           $level = 1 ;
       }else if($total >=2000 && $total <4000){
            //level2
           $level = 2 ;
        }else if($total >=4000 && $total <6000){
            //level3
           $level = 3 ;
       }else if($total >=6000 && $total <10000){
           //level4
           $level = 4 ;
       }else if($total >=10000 && $total <20000){
           //level5
           $level = 5 ;
       }else if($total >=20000){
           //level6
           $level = 6 ;
       }

       if(!$level){
           return  0 ;
       }

       // 通过直推判断有没有得到第二阶段的要求 假如不符合直接降档
        $zhitui_level = 0 ;
        if($zhitui >=100 && $zhitui <150){
            //level1
            $zhitui_level = 1 ;
        }else if($zhitui >=150 && $zhitui <200){
            //level2
            $zhitui_level = 2 ;
        }else if($zhitui >=200 && $zhitui <300){
            //level3
            $zhitui_level = 3 ;
        }else if($zhitui >=300 && $zhitui <400){
            //level4
            $zhitui_level = 4 ;
        }else if($zhitui >=400 && $zhitui <600){
            //$zhitui_level
            $zhitui_level = 5 ;
        }else if($zhitui >=600){
            //level6
            $zhitui_level = 6 ;
        }

        if(!$zhitui_level){
            return  0 ;
        }

        if($zhitui_level >$level){
            return $level ;
        }else{
            return $zhitui_level ;
        }

    }

    /**
     * 获取团队级别对应的提成比例
     * @param $level
     * @return int|mixed
     */
    public function getLevelPercent($level){
        $arr =  [
            0=>0,
            1=>0.001,
            2=>0.002,
            3=>0.003,
            4=>0.004,
            5=>0.005,
            6=>0.006,
        ];
        return isset($arr[$level]) ? $arr[$level] : 0 ;
    }

    /**
     * 新增指定的团队奖励 作废
     * @param $day_balance
     */
    public function addTdjlByDateBak($date){

        // 判断是否达到开启的条件
        $cash_insert_model = new CashInsert();
        if(!$cash_insert_model->checkStart($date)){
            return false ;
        }

        $redis_component = new SetUserRedis();

        // 判断是否已经完成
        if($redis_component->getTagByType('TDJL',$date)){
            return true ;
        }

        $model = new Zhitui();

        $params['cond'] = 'date=:date';
        $params['args'] = [':date'=>$date];
        $total_num = $model->findCountByWhere($model::tableName(),$params,$model::getDb());

        $total_page = $total_num/LOOP_LIMIT_NUM ;

        $total_list = [];

        // 所有用户列表信息
        $user_list = [];

        for($i=0;$i<$total_page;$i++){

            $params['page']['curr_page'] = $i+1 ;
            $params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $list = $model->findAllByWhere($model::tableName(),$params,$model::getDb());

            $user_son_list = [] ;

            foreach($list as $k=>$v){

                $user_son_list[$v['inviter_user_id']][] = array(
                    'tuandui_level'=>$v['tuandui_level'],
                    'tuandui'=>$v['tuandui'],
                );

                $user_list[$v['user_id']] = $v ;
            }

            foreach($user_son_list as $inviter_user_id=>$son_v){

                if(!$inviter_user_id){
                    continue ;
                }

                foreach($son_v as $v){

                    $inviter_user_info = $user_list[$inviter_user_id] ;

                    if($v['tuandui_level']>$inviter_user_info['tuandui_level']){
                        continue ;
                    }

                    $ext = $inviter_user_info['tuandui_level'] -$v['tuandui_level'] + 1 ;
                    $total = $ext*0.001*$inviter_user_info['tuandui'] - $v['tuandui']*0.001 ;

                    if($total > 0){

                        if(isset($total_list[$inviter_user_id])){
                            $total_list[$inviter_user_id] = $total_list[$inviter_user_id] + $total ;
                        }else{
                            $total_list[$inviter_user_id] = $total ;
                        }
                    }

                }

            }
        }

        if(!$total_list){
            return false ;
        }

        $earn_model = new EarnInfo() ;

        $res  = [];

        $user_redis = new SetUserRedis() ;

        foreach($total_list as $user_id=>$total){

            $total = checkMaxEarn($total,$user_id,$user_list[$user_id]['date']);

            $balance  = $user_list[$user_id]['balance'] ;

            if($balance <=0){
                continue ;
            }



            if($total >0 ){

                $temp_add = [] ;
                $temp_add[] = $user_list[$user_id]['user_id'] ;
                $temp_add[] = $user_list[$user_id]['user_level'] ;
                $temp_add[] = $user_list[$user_id]['user_root_path'] ;
                $temp_add[] = $user_list[$user_id]['date'] ;
                $temp_add[] = 'TDJL' ;
                $temp_add[] = $total ;
                $temp_add[] = 0 ;
                $temp_add[] = 'N' ;
                $temp_add[] = date('Y-m-d H:i:s');
                $temp_add[] = date('Y-m-d H:i:s');

                $res[] = $temp_add ;

                $redis_key = 'DayShouyi:'.$user_id.':'.$date;
                $redis_model = new MyRedis() ;
                $redis_info = $redis_model->get($redis_key);
                $redis_model->set($redis_key,$redis_info+$total);


            }



        }

        if($res){

            $res_num = count($res);
            $res_page_num =ceil($res_num/LOOP_LIMIT_NUM) ;
            $chunk_result = array_chunk($res, $res_page_num);
            // 批量插入
            if($chunk_result){
                foreach($chunk_result as $v){
                    $fields = ['user_id','user_level','user_root_path','day','type','total','extra_level','is_tx','create_time','modify_time'];
                    $earn_model->baseBatchInsert($earn_model::tableName(),$fields,$v,'db');
                }
            }

        }

        return $user_redis->setTagByType('TDJL',$date) ;
    }

    /**
     * 新增指定日期的团队奖励
     * @param $start_date
     * @param $end_date
     * @return bool
     */
    public function addTdjl($start_date,$end_date){

        $redis_component = new SetUserRedis();
        $complete_tag = $redis_component->getTagByType('TGJL',$end_date) ;
        if(!$complete_tag){
            return true ;
        }

        $model = new Zhitui();
        $ext = $model->getExtByTime($start_date,$end_date);
        if(!$ext){
            $this->addTdjlByDate($start_date) ;
        }

        for($i=0;$i<=$ext;$i++){
            $date = date('Y-m-d',strtotime($start_date) + $i*86400) ;
            $this->addTdjlByDate($date) ;
        }

    }


    /**
     * 新增指定的团队奖励
     * @param $day_balance
     */
    public function addTdjlByDate($date){

        $redis_component = new SetUserRedis();

        // 判断是否达到开启的条件
        $cash_insert_model = new CashInsert();
        if(!$cash_insert_model->checkStart($date)){
            return $redis_component->setTagByType('TDJL',$date) ;
        }

        // 判断是否已经完成
        if($redis_component->getTagByType('TDJL',$date)){
            return true ;
        }

        $model = new Zhitui();

        $params['cond'] = 'date=:date';
        $params['args'] = [':date'=>$date];
        $total_num = $model->findCountByWhere($model::tableName(),$params,$model::getDb());

        $total_page = $total_num/LOOP_LIMIT_NUM ;

        // 所有用户列表信息
        $user_list = [];

        $page_list = [] ;

        //扣减用户记录表信息
        $reduce_user_list = [];

        // 便利所有用户信息
        for($i=0;$i<$total_page;$i++){

            $params['page']['curr_page'] = $i+1 ;
            $params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $list = $model->findAllByWhere($model::tableName(),$params,$model::getDb());

            $page_list[$i] = $list ;

            foreach($list as $k=>$v){

                $user_list[$v['user_id']] = $v ;
            }
        }

        // 计算总的收益
        for($i=0;$i<$total_page;$i++){

            $list = $page_list[$i];

            foreach($list as $k=>$v){

                $inviter_user_id = $v['inviter_user_id'];
                if(!$inviter_user_id){
                    continue ;
                }

                $inviter_user_info = $user_list[$inviter_user_id];
                if($v['tuandui_level']>=$inviter_user_info['tuandui_level']){
                    continue ;
                }
                if(isset($reduce_user_list[$inviter_user_id]['total'])){
                    $reduce_user_list[$inviter_user_id]['total'] += ($v['tuandui']-$v['total'])*0.001*$v['tuandui_level'] ;
                }else{
                    $reduce_user_list[$inviter_user_id]['total'] = ($v['tuandui']-$v['total'])*0.001*$v['tuandui_level'] ;
                }

                $reduce_user_list[$inviter_user_id]['user_id'] = $inviter_user_id ;
            }
        }

        $user_redis = new SetUserRedis() ;

        $earn_model = new EarnInfo();
        if($reduce_user_list){
            $trunk_list = array_chunk($reduce_user_list, LOOP_LIMIT_NUM);
            foreach($trunk_list as $trunk_son_list) {

                $res = [];
                foreach ($trunk_son_list as $k => $v) {

                    $user_id = $v['user_id'];
                    $user_info = $user_list[$user_id];
                    $total = ($user_info['tuandui']-$user_info['total']) * 0.001 * $user_info['tuandui_level'] - $v['total'];

                    $total = checkMaxEarn($total, $user_id, $user_list[$user_id]['date']);
                    $balance = $user_list[$user_id]['balance'];

                    if ($balance <= 0) {
                        continue;
                    }

                    if ($total > 0) {

                        $temp_add = [];
                        $temp_add[] = $user_list[$user_id]['user_id'];
                        $temp_add[] = $user_list[$user_id]['user_type'];
                        $temp_add[] = $user_list[$user_id]['user_level'];
                        $temp_add[] = $user_list[$user_id]['user_root_path'];
                        $temp_add[] = $user_list[$user_id]['date'];
                        $temp_add[] = 'TDJL';
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
                    $fields = ['user_id','user_type', 'user_level', 'user_root_path', 'day', 'type', 'total', 'extra_level', 'is_tx', 'create_time', 'modify_time'];
                    $earn_model->baseBatchInsert($earn_model::tableName(), $fields, $res, 'db');
                }
            }
        }

        return $user_redis->setTagByType('TDJL',$date) ;

    }


}