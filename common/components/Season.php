<?php
namespace common\components;

use common\models\CashInsert;
use common\models\DayBalance;
use common\models\EarnInfo;
use common\models\SeasonZhitui;
use common\models\Zhitui;

class Season
{
    public function init(){
        #TODO
    }

    /**
     * 获取总量的redis值
     * @return string
     */
    private function returnTotalRedisKey(){
        return  "Season:total";
    }

    /**
     * 设置总插入量的redis
     * @param integer total
     */
    public function setTotalInsertRedis($total){
        $redis_key = $this->returnTotalRedisKey();
        $redis_model = new MyRedis();
        if($redis_model->checkKeyExists($redis_key)){
            $redis_info = $redis_model->get($redis_key);
            $redis_info = floatval($redis_info) ;
            $total = $total + $redis_info ;
        }

        $redis_model->set($redis_key,$total) ;
    }

    private function returnUserSeasonZhituiKey($user_id,$season){
        return 'Season_zhitui:'.$season.':'.$user_id ;
    }

    /**
     * 获取用户赛季直推总和
     * @param $user_id
     * @param $season
     */
    public function getUserSeasonZhitui($user_id,$season){
        $redis_model = new MyRedis() ;
        $redis_info = $redis_model->get($this->returnUserSeasonZhituiKey($user_id, $season));

        return floatval($redis_info) ;
    }

    /**
     * 获取当前日期之前的入金总和
     * @param $date
     */
    public function getTotalFromDb($date){
        $params['cond'] = 'date < :date' ;
        $params['args'] = [':date'=>$date];
        $params['fields'] = ' sum(amount) as total';
        $model = new CashInsert() ;
        $info = $model->findOneByWhere($model::tableName(),$params,$model::getDb());
        $total = $info && !is_null($info['total']) ? $info['total'] : 0 ;
        return $total ;
    }
    /**
     * 设置用户赛季直推
     * @param $user_id
     * @param $season
     * @param $total
     */
    public function setUserSeasonZhitui($user_id,$season,$total){
        $current_total = $this->getUserSeasonZhitui($user_id,$season);
        $total = $current_total + $total ;
        $redis_model = new MyRedis();
        $redis_model->set($this->returnUserSeasonZhituiKey($user_id,$season),$total) ;
    }

    /**
     * 获取当前redis对应的总额
     * @return mixed
     */
    public function getTotalFromRedis(){
        $redis_key = $this->returnTotalRedisKey();
        $redis_model = new MyRedis();

        $redis_info = $redis_model->get($redis_key) ;
        return floatval($redis_info) ;



    }

    /**
     * 根据当前总量返回当前的赛季
     * @param $total
     * @return int
     */
    public function getSeasonFromTotal($total){
        if($total < 2000){
            //直接开启第一赛季
            return  1 ;
        }else if($total >=2000 && $total < 5000){
            return 1 ;
        }else if($total >=5000 && $total <10000){
            return 2 ;
        }else if($total >=10000 && $total <20000){
            return 3 ;
        }else if($total >=20000 && $total <40000){
            return 4 ;
        }else if($total >=40000 && $total <80000){
            return 5 ;
        }else if($total >=80000 && $total <160000){
            return 6 ;
        }else if($total >=160000 && $total <320000){
            return 7 ;
        }else if($total >=320000 && $total <640000){
            return 8 ;
        }else if($total >=640000 && $total <735000){
            return 9 ;
        }else{
            return 10 ;
        }
    }

    public function getTotalInsertBySeason($season){

        $arr = [0,5000,10000,20000,40000,80000,160000,320000,640000,735000];
        return isset($arr[$season]) ? $arr[$season] : 0 ;
    }

    /**
     * 添加赛季奖励
     * @param $start_time
     * @param $end_time
     * @return mixed
     * Note:必须结算完所有的奖励，才能够将所剩余额的20% 按比例发放给各个阵营的会员
     */
    public function sendEth($start_time,$end_time){


        $redis_component = new SetUserRedis();
        $complete_tag = $redis_component->getTagByType('KAIJIANG',$end_time) ;
        if(!$complete_tag){
            return true ;
        }

        //step3 查询当前时间段的入金量 按照转账的先后顺序
        $ext = ( strtotime($end_time) - strtotime($start_time) ) / 86400 ;
        if(!$ext){
            $this->addSeasonByDate($start_time) ;
        }else{
            for($i =0 ; $i<=$ext ;$i++){
                $date = date('Y-m-d' , strtotime($start_time) + $i*86400) ;
                $this->addSeasonByDate($date) ;
            }
        }

    }

    public function addSeasonByDate($date){

        $redis_component = new SetUserRedis();
        if($redis_component->getTagByType('SEASON',$date)){
            return true ;
        }

        // step1 查询当日的总记录数 一次性解决
        $cash_insert_model = new CashInsert() ;
        $total_params['cond'] = 'date=:date AND amount > 0 ';
        $total_params['args'] = [':date'=>$date];
        $total_num  = $cash_insert_model->findCountByWhere($cash_insert_model::tableName(),$total_params,$cash_insert_model::getDb());
        $total_page = ceil($total_num/LOOP_LIMIT_NUM) ;

        // 父级所有账户信息
        $parent_user_list = [];

        // 所有父类用户ID
        $parent_user_ids = [] ;

        // 此次循环的赛季总和
        $season_list = [];

        // 获取当前日期之前的总和
        $current_total = $this->getTotalFromDb($date) ;
        for($i =0 ;$i<$total_page ;$i++){
            $params['page']['curr_page'] = $i+1 ;
            $params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $params['cond'] = 'date=:date';
            $params['args'] = [':date'=>$date] ;
            $params['orderby']  = 'timeStamp ASC ';
            $list = $cash_insert_model->findAllByWhere($cash_insert_model::tableName(),$params,$cash_insert_model::getDb());

            foreach($list as $v){

                // 未计算之前的赛季
                $season = $this->getSeasonFromTotal($current_total) ;

                if(!in_array($season,$season_list)){
                    $season_list []   = $season ;
                }

                $inviter_user_id = $v['inviter_user_id'];
                if($inviter_user_id){
                    // 设置父级别用户赛季直推总额
                    $redis_component->setUserSeasonInfo($inviter_user_id,$season,$v['amount']) ;

                    $parent_user_list[$season][$inviter_user_id]['total'] = $redis_component->getUserSeasonInfo($inviter_user_id,$season) ;
                    $parent_user_list[$season][$inviter_user_id]['season'] = $season ;

                    if(!in_array($inviter_user_id,$parent_user_ids)){
                        $parent_user_ids[] =  $inviter_user_id ;
                    }

                }

                $current_total = $current_total + $v['amount'];

            }
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


        $season_zhitui_model = new SeasonZhitui() ;

        if($parent_user_list){
            foreach($parent_user_list as $parent_user_list_son){

                $batch_data = [] ;
                foreach($parent_user_list_son as $k=>$v){
                    $temp_add = [] ;

                    if($v['total'] <=0){
                        continue ;
                    }

                    $zhenying = $this->getZhenyinBySeason($v['total'],$v['season']) ;
                    if(!$zhenying ){
                        continue ;
                    }
                    $temp_add[] = $k ;
                    $temp_add[] = $v['season'] ;
                    $temp_add[] = $zhenying ;
                    $temp_add[] = $date;
                    $temp_add[] = $v['total'] ;
                    $temp_add[] = date('Y-m-d H:i:s');
                    $temp_add[] = date('Y-m-d H:i:s');

                    $batch_data[] = $temp_add ;
                }
                $fields = ['user_id','season','zhenying','date','total','create_time','modify_time'];
                $season_zhitui_model->baseBatchInsert($season_zhitui_model::tableName(),$fields,$batch_data,'db');
            }

        }

        ///*
        // 判断当前有没有跨赛季
        if(count($season_list) >1 ){
            rsort($season_list) ;
            $lasted_season = $season_list[0];
        }else{
            if(!$season_list){
                // 当天没有持续性的投入
                $lasted_season = $this->getSeasonFromTotal($current_total);
            }else{
                $lasted_season = $season_list ? $season_list[0] : 0 ;
            }

        }


        // 拷贝前一天的赛季记录
        $season_zhitui_model->copyPrevDayInfo($parent_user_list,$parent_user_ids,$date) ;

        //查看当前赛季是否多个赛季 存在多个赛季需要发放前一个赛季的奖励
        $season_num = count($season_list);

        if($season_num>1){

            // 根据赛季计算总的入金量
            $total_insert = $this->getTotalInsertBySeason($lasted_season-1) ;

            $total_out_params['cond'] = ' day <=:day' ;
            $total_out_params['args'] = [':day'=>$date];

            $total_out_params['fields'] = 'sum(total) as total_earn' ;
            $total_out_info = $cash_insert_model->findOneByWhere('sea_earn_info',$total_out_params,$cash_insert_model::getDb());
            $total_out = isset($total_out_info['total_earn']) && !is_null($total_out_info['total_earn']) ? $total_out_info['total_earn'] : 0 ;

            $total_balance = $total_insert - $total_out ;
            $total_balance = $total_balance > 0 ? $total_balance : 0 ;

            $total_percent = 0 ;
            foreach($season_list as $k=>$season){
                if($k !=0){
                    $total_percent += $this->getPercentBySeason($season) ;
                }
            }

            foreach($season_list as $k=>$season){
                if($k!=0){
                    $final_total_balance = ($total_balance * $this->getPercentBySeason($season) )/$total_percent ;

                    $final_total_balance = $final_total_balance*0.2 ;// 余额的20%奖励给用户
                    $this->dealBySeason($season,$final_total_balance,$date,$zhitui_user_list) ;
                }
            }

        }

        return  $redis_component->setTagByType('SEASON',$date);
    }

    public function getTotalSeasonPercent(){
        return [0,3000,5000,10000,20000,40000,80000,160000,320000,95000];
    }

    public function getPercentBySeason($season){
        $all = $this->getTotalSeasonPercent();
        return isset($all[$season]) ? $all[$season] : 0 ;
    }

    public function allRole(){
        return [1=>'ALITA',2=>'LIEREN' ,3=>'SHASHOU' ,4=>'QIUSHOU',5=>'EMO',6=>'XUNLIANSHI',7=>'LINGZHU'] ;
    }

    /**
     * 根据直推金额去计算对应赛季的所属阵营
     * @param $zhitui
     * @param $season
     */
    public function getZhenyinBySeason($zhitui,$season){

        if($season ==0){
            return  0 ;
        }

        if($season == 1){
            if($zhitui >=10 && $zhitui <30){
                return 1 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 2 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 3 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 4 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 5 ;
            }else if($zhitui >=200 && $zhitui <500){
                return 6 ;
            }else if($zhitui >=500 ){
                return 7 ;
            }

        }

        if($season == 2){
            if($zhitui >=10 && $zhitui <30){
                return 2 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 1 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 3 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 4 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 5 ;
            }else if($zhitui >=200 && $zhitui <500){
                return 6 ;
            }else if($zhitui >=500 ){
                return 7 ;
            }

        }

        if($season == 3){
            if($zhitui >=10 && $zhitui <30){
                return 2 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 3 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 1 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 4 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 5 ;
            }else if($zhitui >=200 && $zhitui <500){
                return 6 ;
            }else if($zhitui >=500 ){
                return 7 ;
            }

        }

        if($season == 4){
            if($zhitui >=10 && $zhitui <30){
                return 2 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 3 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 4 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 1 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 5 ;
            }else if($zhitui >=200 && $zhitui <500){
                return 6 ;
            }else if($zhitui >=500 ){
                return 7 ;
            }

        }

        if($season == 5){
            if($zhitui >=10 && $zhitui <30){
                return 2 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 3 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 4 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 5 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 1 ;
            }else if($zhitui >=200 && $zhitui <500){
                return 6 ;
            }else if($zhitui >=500 ){
                return 7 ;
            }

        }

        if($season == 6){
            if($zhitui >=10 && $zhitui <30){
                return 2 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 3 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 4 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 5 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 6 ;
            }else if($zhitui >=200 && $zhitui <500){
                return 1 ;
            }else if($zhitui >=500 ){
                return 7 ;
            }

        }

        if($season == 7){
            if($zhitui >=10 && $zhitui <30){
                return 2 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 3 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 4 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 5 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 6 ;
            }else if($zhitui >=200 && $zhitui <500){
                return 7 ;
            }else if($zhitui >=500 ){
                return 1 ;
            }

        }

        if($season == 8){
            if($zhitui >=10 && $zhitui <30){
                return 2 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 3 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 4 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 5 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 6 ;
            }else if($zhitui >=200 && $zhitui <700){
                return 7 ;
            }else if($zhitui >=700 ){
                return 1 ;
            }

        }

        if($season == 9){
            if($zhitui >=10 && $zhitui <30){
                return 2 ;
            }else if($zhitui >=30 && $zhitui <50){
                return 3 ;
            }else if($zhitui >=50 && $zhitui <60){
                return 4 ;
            }else if($zhitui >=60 && $zhitui <100){
                return 5 ;
            }else if($zhitui >=100 && $zhitui <200){
                return 6 ;
            }else if($zhitui >=200 && $zhitui <900){
                return 7 ;
            }else if($zhitui >=900 ){
                return 1 ;
            }

        }
    }

    /**
     * 获取赛季阵营对应的收益百分比
     * @param $zhenyin
     * @param $season
     * @return int|mixed
     */
    public function getPercentByZhenyinAndSeason($zhenyin,$season){
        $percent = 0 ;
        if($season ==1){

            $arr = [1=>3,2=>5,3=>7,4=>10,5=>15,6=>20,7=>40];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }

        if($season ==2){

            $arr = [1=>5,2=>3,3=>7,4=>10,5=>15,6=>20,7=>40];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }

        if($season ==3){

            $arr = [1=>7,2=>3,3=>5,4=>10,5=>15,6=>20,7=>40];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }

        if($season ==4){

            $arr = [1=>10,2=>3,3=>5,4=>7,5=>15,6=>20,7=>40];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }

        if($season ==5){

            $arr = [1=>15,2=>3,3=>5,4=>7,5=>10,6=>20,7=>40];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }

        if($season ==6){

            $arr = [1=>20,2=>3,3=>5,4=>7,5=>10,6=>15,7=>40];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }

        if($season ==7){

            $arr = [1=>40,2=>3,3=>5,4=>7,5=>10,6=>15,7=>20];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }

        if($season ==8){

            $arr = [1=>40,2=>3,3=>5,4=>7,5=>10,6=>15,7=>20];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }


        if($season ==9){

            $arr = [1=>40,2=>3,3=>5,4=>7,5=>10,6=>15,7=>20];
            $percent = isset($arr[$zhenyin]) ? $arr[$zhenyin] : 0 ;
        }

        return $percent ;
    }

    /**
     * 按照赛季发放奖励
     * @param $season
     * @param $total_balance
     */
    public function dealBySeason($season,$total_balance,$date,$zhitui_user_list){

        if(!$total_balance){
            return true ;
        }

        // 获取每个阵营人数
        $model = new SeasonZhitui();
        $earn_info_model = new EarnInfo() ;
        // 获取所有阵营
        $total_zhenying = $this->allRole();
        foreach($total_zhenying as $k=>$v){

            $zhenying  = $k ;
            $params['cond'] = 'season =:season AND zhenying=:zhenying AND date=:date';
            $params['args'] = [':season'=>$season,':zhenying'=>$zhenying,':date'=>$date] ;
            $total_user_num =$model->findCountByWhere($model::tableName(),$params,$model::getDb());

            $total_user_page = ceil($total_user_num/LOOP_LIMIT_NUM) ;

            //赛季对应阵营的奖励比例
            $zy_percent  = $this->getPercentByZhenyinAndSeason($zhenying,$season);
            $zy_percent = $zy_percent/100;

            for($i = 0 ;$i<$total_user_page;$i++){

                $params['page']['curr_page'] = $i+1 ;
                $params['page']['page_num'] = LOOP_LIMIT_NUM ;
                $list  = $model->findAllByWhere($model::tableName(),$params,$model::getDb());

                $batch_data = [] ;
                $total = ($total_balance*$zy_percent)/$total_user_num ;
                foreach($list as $row){
                    $temp_add = [] ;

                    $user_info = $zhitui_user_list[$row['user_id']] ;

                    $temp_add[] = $row['user_id'] ;
                    $temp_add[] = $user_info['user_type'] ;
                    $temp_add[] = $user_info['user_level'] ;
                    $temp_add[] = $user_info['user_root_path'] ;
                    $temp_add[] = $date ;
                    $temp_add[] = $total ;
                    $temp_add[] = $season ;
                    $temp_add[] = 'SEASON';
                    $temp_add[] = 'N' ;
                    $temp_add[] = 'UNDEAL' ;
                    $temp_add[] = date('Y-m-d H:i:s');
                    $temp_add[] = date('Y-m-d H:i:s');

                    $batch_data[] = $temp_add ;
                }

                if($batch_data){
                    $fields = ['user_id','user_type','user_level','user_root_path','day','total','season','type','is_tx','status','create_time','modify_time'];
                   $earn_info_model->baseBatchInsert($earn_info_model::tableName(),$fields,$batch_data,'db');
                }


            }


        }



    }

}