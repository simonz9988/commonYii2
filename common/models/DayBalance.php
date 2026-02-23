<?php

namespace common\models;

use common\components\EthWallet;
use common\components\MyRedis;
use common\components\OkexTrade;
use common\components\SetUserRedis;
use common\components\Tuandui;
use Yii;

/**
 * This is the model class for table "sea_day_balance".
*/
class DayBalance extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_day_balance';
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
     * @param $user_id
     * @param $date
     * @return string
     */
    public function returnRepeatKey($user_id,$date){
        return 'InitBalance:'.$user_id.$date;
    }

    /**
     * 判断是否重复插入
     * @param $user_id
     * @param $date
     * @return bool
     */
    public function checkRepeat($user_id,$date){

        // 使用redis来做
        $redis_key = $this->returnRepeatKey($user_id,$date);
        $redis_model = new MyRedis();
        if($redis_model->get($redis_key) >0){
            return true ;
        }else{
            return false;
        }

    }

    /**
     * 根据日期返回用户列表信息
     * @param $date
     * @return array
     */
    public function getUserListByDate($date){

        // 查询有多少个用户充值
        $params['cond'] = ' date <=:date';
        $params['args'] = [':date'=>$date];
        $params['fields'] = '*';
        $params['group_by'] = 'user_id';
        $list = $this->findAllByWhere('sea_cash_insert',$params,self::getDb());

        $user_list = [];
        if(!$list){
            return $user_list ;
        }

        foreach($list as $v){
            $user_list[] = array(
                'user_id' =>$v['user_id'],
                'user_level' =>$v['user_level'],
                'user_root_path' =>$v['user_root_path'],
                'inviter_user_id' =>$v['inviter_user_id'],
                'inviter_username' =>$v['inviter_username'],
                'user_type' =>$v['user_type'],
                'is_super' =>$v['is_super'],
            );
        }

        return $user_list ;
    }

    /**
     * 添加每日用户的账户数据
     * @param $start_time
     * @param $end_time
     * @return true
     */
    public function addDayRecord($start_time,$end_time){

        $total_day = ( strtotime($end_time) - strtotime($start_time) ) / 86400 ;
        if(!$total_day){
            $this->addRecordByDate(date('Y-m-d',strtotime($end_time))) ;
        }

        // 查询当前时间的资金总额
        for($i=0 ;$i<=$total_day;$i++){

            $date = date("Y-m-d",strtotime($start_time) + $i*86400) ;
            $this->addRecordByDate($date) ;
        }

        return true ;

    }

    /**
     * 快照用户的所有记录
     * @param $date
     * @param $user_list
     * @return mixed
     */
    public function addRecordByDate($date){

        $redis_component = new SetUserRedis();
        $complete_tag = $redis_component->getTagByType('DayBalance',$date) ;
        if($complete_tag){
            // 判断指定日期的是否已经完成，完成的话直接继续其他日期的操作
            // 一般初始化的时候是循环多个日期的，所以不存在单个日期重复执行
            // 初始化完成后，改任务改成每分钟执行一次即可
            return true ;
        }

        // 获取当前日期之前(包含当天) 用户列表信息
        $user_list = $this->getUserListByDate($date);

        // 需要查询当前数据库中所有的用户列表信息
        $cash_insert_model = new  CashInsert() ;

        // 获取所有数目
        $total_num = $cash_insert_model->getTotalNumByDate($date);
        // 总的分页数
        $total_page = ceil($total_num/LOOP_LIMIT_NUM) ;

        if($total_num){

            // 先把用户的余额存入到redis中
            $redis_component = new SetUserRedis();
            for($i=0;$i<$total_page;$i++){
                $list = $cash_insert_model->getListByDateAndPage($date,$i);

                foreach($list as $v){
                    // 设置用户的余额的redis值
                    $user_id  = $v['user_id'] ;
                    $redis_component->setDayBalance($user_id,$v['amount']) ;
                }
            }
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');

        // 针对所有用户进行批量插入
        $total_user_num = count($user_list);
        $total_user_page = ceil($total_user_num/LOOP_LIMIT_NUM);

        for($i=0 ;$i<$total_user_page;$i++){

            //去除指定区间段的用户信息
            $start_num = $i*LOOP_LIMIT_NUM;
            $end_num = ($i+1)*LOOP_LIMIT_NUM > $total_user_num ?  $total_user_num:($i+1)*LOOP_LIMIT_NUM ;

            $temp_list = [] ;
            for($k=$start_num;$k<$end_num;$k++) {
                $temp_list[] = $user_list[$k];
            }


            $batch_data = [] ;
            foreach($temp_list as $user_info){

                $temp_add_data = [] ;

                // redis中存在的账户余额
                $user_total = $redis_component->getDayBalance($user_info['user_id']) ;

                $redis_component->setUserInfo($user_info['user_id'],$user_info['user_root_path'],$user_info['inviter_user_id'],$user_info['inviter_username'],$user_info['user_level'],$user_info);

                $temp_add_data[] = $user_info['user_id'] ;
                $temp_add_data[] = $date ;
                $temp_add_data[] = $user_total ;
                $temp_add_data[] = $user_info['user_level'] ; ;
                $temp_add_data[] = $user_info['user_root_path'] ; ;
                $temp_add_data[] = $user_info['inviter_user_id'] ; ;
                $temp_add_data[] = $user_info['inviter_username'] ; ;
                $temp_add_data[] = $user_info['user_type'] ; ;
                $temp_add_data[] = $user_info['is_super'] ; ;
                $temp_add_data[] = $now ;
                $temp_add_data[] = $now ;

                $redis_component->setDayBalanceByDate($user_info['user_id'],$user_total,$date) ;

                $batch_data[] = $temp_add_data ;
            }

            // 批量插入
            $fields = ['user_id','date','balance','user_level','user_root_path','inviter_user_id','inviter_username','user_type','is_super','create_time','modify_time'];
            //$fields = ['user_id','date','balance','user_level','create_time','modify_time'];
            if($batch_data){
                $this->baseBatchInsert(self::tableName(),$fields,$batch_data,'db');
            }

        }

        // 设置当天账户余额完成的redis标志
        $redis_component->setTagByType('DayBalance',$date) ;
        return true ;

    }


    /**
     * 添加团队的每日数据
     * @param $start_time
     * @param $end_time
     * @return true
     */
    public function addTeamRecord($start_time,$end_time){

        $total_day = ( strtotime($end_time) - strtotime($start_time) ) / 86400 ;
        if(!$total_day){
            $this->addTeamRecordByDate(date('Y-m-d',strtotime($end_time))) ;
        }

        // 查询当前时间的资金总额
        for($i=0 ;$i<$total_day;$i++){

            $date = date("Y-m-d",strtotime($start_time) + $i*86400) ;
            $this->addTeamRecordByDate($date) ;
        }

        return true ;

    }

    /**
     * 根据日期添加团队记录
     * @param $date
     */
    public function addTeamRecordByDate($date){

        // 当天总记录数
        $params['cond'] = 'date = :date';
        $params['args'] = [':date'=>$date];
        $total_num = $this->findCountByWhere(self::tableName(),$params,self::getDb());

        // 计算总页数
        $total_page = ceil($total_num/LOOP_LIMIT_NUM);

        // 所有用户列表
        $user_list = [];

        // 用户余额列表
        $user_balance_list = [] ;

        // 团队总额
        $team_user_list = [];

        // 直推用户列表
        $zhitui_user_list = [];

        // 用户级别
        $user_level_list = [] ;

        //团队组件
        $tuandui_component = new Tuandui();

        // 用户redis
        $user_redis_component = new SetUserRedis();

        for($i=0;$i<$total_page;$i++){

            // 查询指定分页的账户余额
            $params['page']['curr_page'] = $i+1 ;
            $params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $list = $this->findAllByWhere(self::tableName(),$params,self::getDb()) ;

            foreach($list as $v){

                $user_list[] = $v['user_id'] ;

                $user_level_list[$v['user_id']] = $v['user_level'] ;

                $user_info = $user_redis_component->getUserInfo($v['user_id']) ;
                $user_root_path = $user_info['user_root_path'] ;

                // 账户余额
                $balance = $v['balance'] ;

                $user_balance_list[$v['user_id']] = $balance ;

                $user_root_path_arr = explode('--',$user_root_path);
                foreach($user_root_path_arr as $parent_user_id){

                    $team_user_list[$parent_user_id] = isset($team_user_list[$parent_user_id]) ? ($team_user_list[$parent_user_id] + $balance): $balance ;
                }

                $inviter_user_id = $user_info['inviter_user_id'];

                $zhitui_user_list[$inviter_user_id] = isset($zhitui_user_list[$inviter_user_id]) ? ($zhitui_user_list[$inviter_user_id] +$balance) :$balance;
            }
        }

        $total_user_num = count($user_list) ;

        $total_user_page = ceil( $total_user_num / LOOP_LIMIT_NUM ) ;
        for($i=0;$i<$total_user_page;$i++){

            //去除指定区间段的用户信息
            $start_num = $i*LOOP_LIMIT_NUM;
            $end_num = ($i+1)*LOOP_LIMIT_NUM > $total_user_num ?  $total_user_num:($i+1)*LOOP_LIMIT_NUM ;

            $temp_user_list = [] ;
            for($k=$start_num;$k<$end_num;$k++) {
                $temp_user_list[] = $user_list[$k];
            }


            // 临时批量插入数据
            $batch_data = [] ;
            foreach($temp_user_list as $user_id){

                $temp_add_data = [] ;

                // 直推总额
                $zhitui = isset($zhitui_user_list[$user_id]) && $zhitui_user_list[$user_id] >0 ?$zhitui_user_list[$user_id]:0 ; // 直推总额#TODO必须限定总额

                // 团队总额
                $team_total = isset($team_user_list[$user_id]) ?$team_user_list[$user_id] : 0 ;

                $temp_add_data[] = $user_id ;
                $temp_add_data[] = $date ;
                $temp_add_data[] = $user_balance_list[$user_id] ;
                $temp_add_data[] = $zhitui ;
                $temp_add_data[] = $team_total ;
                $temp_add_data[] = $tuandui_component->getLevel($zhitui,$team_total) ;
                $temp_add_data[] = $user_level_list[$user_id] ;
                $temp_add_data[] = date('Y-m-d H:i:s') ;
                $temp_add_data[] = date('Y-m-d H:i:s') ;

                $batch_data[] = $temp_add_data ;

            }

            if($batch_data){
                $fields = ['user_id','date','balance','total','tuandui','tuandui_level','user_level','create_time','modify_time'];
                $this->baseBatchInsert('sea_zhitui',$fields,$batch_data,'db');
            }

        }


    }

    /**
     * 初始化处理直推金额
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public function initZhiTuiAndTuandui($start_date,$end_date){

        // 判断是否已经完成最终日期的账户余额的初始化
        $redis_component = new SetUserRedis() ;
        $final_date_tag = $redis_component->getTagByType('DayBalance',$end_date) ;
        if(!$final_date_tag){
            //没有完成最后的日期的余额初始化不能进行后续的操作
            return false ;
        }

        $ext = (strtotime($end_date) - strtotime($start_date) ) / 86400 ;

        if(!$ext){

            if($redis_component->getTagByType('ZhituiAndTeam',$end_date) ){
                return true ;
            }

            $params['cond'] = 'date =:date  ';
            $params['args'] = [':date'=>$start_date];
            $total_num = $this->findCountByWhere(self::tableName(),$params,self::getDb());

            $total_page = ceil($total_num/LOOP_LIMIT_NUM) ;
            for($i=0;$i<$total_page;$i++){

                $params['page']['curr_page'] = $i+1;
                $params['page']['page_num'] = LOOP_LIMIT_NUM;

                $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

                $this->initZhiTuiAndTuanduiByList($list) ;
            }

            // 设置当日直推和团队金额完成的总额
            $redis_component->setTagByType('ZhituiAndTeam',$end_date) ;

        }else{

            for($i = 0 ;$i<=$ext ;$i++ ){

                $date =date('Y-m-d' ,(strtotime($start_date) + $i*86400)) ;

                if($redis_component->getTagByType('ZhituiAndTeam',$end_date) ){
                    continue ;
                }

                $params['cond'] = 'date =:date  ';
                $params['args'] = [':date'=>$date];
                $total_num = $this->findCountByWhere(self::tableName(),$params,self::getDb());

                $total_page = ceil($total_num/LOOP_LIMIT_NUM) ;
                for($i=0;$i<$total_page;$i++){

                    $params['page']['curr_page'] = $i+1;
                    $params['page']['page_num'] = LOOP_LIMIT_NUM;

                    $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
                    $this->initZhiTuiAndTuanduiByList($list) ;
                }

                $redis_component->setTagByType('ZhituiAndTeam',$date) ;

            }
        }

        return true ;

    }

    public function initZhiTuiAndTuanduiByList($list){

        if(!$list){
            return false ;
        }

        $tuandui_component = new Tuandui();

        $batch_data = [];

        $sum_total_arr = [] ;

        $team_total_arr = [];

        $redis_components = new SetUserRedis();

        foreach($list as $v){

            $temp_data = [];

            $key = $v['user_id'].$v['date'];

            $redis_user_info  = $redis_components->getUserInfo($v['user_id']) ;

            if(isset($sum_total_arr[$redis_user_info['inviter_user_id']])){
                $sum_total_arr[$redis_user_info['inviter_user_id']] = $sum_total_arr[$redis_user_info['inviter_user_id']] + $v['balance'] ;
            }else{
                $sum_total_arr[$redis_user_info['inviter_user_id']] =  $v['balance'] ;
            }

            // 设置团队的数量 都是按天进行运算，所以不需要加上天的限制
            $user_root_path_arr = explode('--',$redis_user_info['user_root_path']);
            foreach($user_root_path_arr as $root_user){

                if(!$root_user){
                    continue ;
                }

                if(isset($team_total_arr[$root_user])){
                    $team_total_arr[$root_user] = $team_total_arr[$root_user] + $v['balance'] ;
                }else{
                    $team_total_arr[$root_user] =  $v['balance'] ;
                }

            }

            $temp_data[] = $v['user_id'] ;
            $temp_data[] = $v['date'] ;
            $temp_data[] = $v['balance'] ;
            $temp_data[] = $v['balance'] >0 ?$sum_total_arr[$redis_user_info['inviter_user_id']] :0 ;
            $temp_data[] = 0 ;
            $temp_data[] = 0 ;
            $temp_data[] = $v['user_level'] ;
            //$temp_data[] = $v['user_root_path'] ;
            //$temp_data[] = $v['inviter_user_id'] ;
            //$temp_data[] = $v['inviter_username'] ;
            $temp_data[] = date('Y-m-d H:i:s') ;
            $temp_data[] = date('Y-m-d H:i:s') ;

            $batch_data[$key] = $temp_data ;

        }

        $res = [];
        foreach($batch_data as $k=>$v){

            $v[3] = isset($sum_total_arr[$v[0]]) ? $sum_total_arr[$v[0]] : 0 ;
            $v[4] = isset($team_total_arr[$v[0]]) ? $team_total_arr[$v[0]] : 0  ;
            $zhitui = $v[3] ;
            $team_total = $v[4] ;
            $v[5] = $tuandui_component->getLevel($zhitui,$team_total) ;
            $res[] = $v ;
        }

        // 批量插入
        //$fields = ['user_id','date','balance','total','tuandui','tuandui_level','user_level','user_root_path','inviter_user_id','inviter_username','create_time','modify_time'];
        $fields = ['user_id','date','balance','total','tuandui','tuandui_level','user_level','create_time','modify_time'];
        $this->baseBatchInsert('sea_zhitui',$fields,$res,'db');
    }

    /**
     * 获取当日所有用户的信息
     * @param $date
     * @return mixed
     */
    public function getListByDate($date){

        $params['cond'] = 'date=:date';
        $params['args'] = [':date'=>$date];
        return $this->findAllByWhere(self::tableName(),$params,self::getDb());
    }

    /**
     * 获取指定时间段内用户的信息
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public function getListByRangeDate($start_date,$end_date){

        $params['cond'] = 'date >= :start_date AND date <=:end_date';
        $params['args'] = [':start_date'=>$start_date,':end_date'=>$end_date];
        return $this->findAllByWhere(self::tableName(),$params,self::getDb());
    }

    /**
     * 获取团队总业绩
     * @param $user_id
     * @param $date
     * @return mixed
     */
    public function getTeamTotal($user_id,$date){
        $params['cond'] = 'date=:date AND user_root_path like :user_root_path';
        $params['args'] = [':date'=>$date,':user_root_path'=>'%--'.$user_id.'--%'] ;
        $params['fields'] = ' sum(balance) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

}
