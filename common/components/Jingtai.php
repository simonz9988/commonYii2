<?php
namespace common\components;

use common\models\CashInsert;
use common\models\DayBalance;
use common\models\EarnInfo;
use common\models\Zhitui;

class Jingtai
{
    public function init(){
        #TODO
    }

    /**
     * 获取级别
     * @param $balance
     * @return integer
     */
    public function getLevel($balance){

        if($balance  < 10){
            return 1 ;
        }else if($balance >=10 && $balance < 20){
            return 2 ;
        }else if($balance >=20 && $balance < 30){
            return 3 ;
        }else if($balance >=30 && $balance <50){
            return 4 ;
        }else if($balance >=50){
            return 5 ;
        }
    }

    /**
     * 获取基本对应的提成比例
     * @param $level
     * @return mixed
     */
    public function getLevelPercent($level){
        $arr = [
            1=>0.0025,
            2=>0.003,
            3=>0.0035,
            4=>0.004,
            5=>0.004,
        ];

        return $arr[$level] ;
    }

    /**
     * 获取每个级别的限领天数
     * @param $level
     * @return mixed
     */
    public function  getLevelLimit($level){
        $arr = [
            1=>800,
            2=>990,
            3=>1140,
            4=>1000,
            5=>1000,
        ];

        return $arr[$level] ;
    }

    /**
     * 通过级别和余额返回允许的最大收益值
     * @param $level
     * @param $balance
     */
    public function getLevelMaxByBalance($level,$balance){
        $beishu_arr = [1=>2,2=>3,3=>4,4=>5,5=>5];
        $beishu = $beishu_arr[$level] ;
        $max = $balance* $beishu;
        return $max ;
    }

    /**
     * 计算静态收益去最大值
     * @param $balance
     * @return int
     */
    public function returnRealBalance($balance){
        return $balance > 50 ? 50 :$balance ;
    }

    /**
     * 根据直推值获取额外的
     * @param $zhitui
     * @return decimal
     */
    public function getExtraPercentByZhitui($zhitui){

        $num = floor($zhitui/10) ;
        $num = $num >40 ? 40:$num ;


        return $num*0.0005 ;
    }

    /**
     * 获取自增原子的值
     * @param $user_id
     * @param $level
     * @return bool
     */
    public function getIncrKeyByUserId($user_id,$level){
        $redis_key = 'JingtaiLevelLimit:'.$user_id.':'.$level;
        $redis_model = new MyRedis() ;
        $redis_info = $redis_model->get($redis_key);
        return $redis_info ;
    }

    /**
     * 将静态收益数目归零
     * @param $user_id
     * @param $level
     * @return bool
     */
    public function setIncrKeyByUserIdZero($user_id,$level){
        $redis_key = 'JingtaiLevelLimit:'.$user_id.':'.$level;
        $redis_model = new MyRedis() ;
        $redis_info = $redis_model->set($redis_key,0);
        return $redis_info ;
    }
    /**
     * 新增自增
     * @param $user_id
     * @param $level
     * @return int
     */
    public function setIncrByUserId($user_id,$level){
        $redis_key = 'JingtaiLevelLimit:'.$user_id.':'.$level;
        $redis_model = new MyRedis() ;
        return $redis_model->incrBy($redis_key,1);
    }


    /**
     * 添加指定时间段之内的静态收益和动态收益
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public function addJtsy($start_date,$end_date){

        // 判断上一个节点是否已经完成
        $redis_component = new SetUserRedis();
        $complete_tag = $redis_component->getTagByType('ZhituiAndTeam',$end_date) ;
        if(!$complete_tag){
            return true ;
        }

        $zhitui_model = new Zhitui();

        $ext = $zhitui_model->getExtByTime($start_date,$end_date) ;

        if(!$ext){
            $this->addJtsyByDate($start_date) ;
        }else{
            for($i=0 ;$i<=$ext ;$i++){
                $date = date('Y-m-d', (strtotime($start_date) + 86400*$i) ) ;
                $this->addJtsyByDate($date) ;
            }
        }

        return true ;
    }

    /**
     * 根据日期计算每日静态收益
     * @param $date
     * @return mixed
     */
    public function addJtsyByDate($date){

        $redis_component = new SetUserRedis();

        // 判断是否达到开启的条件
        $cash_insert_model = new CashInsert();

        if(!$cash_insert_model->checkStart($date)){
            return $redis_component->setTagByType('JTSY',$date) ;
        }

        $zhitui_model = new Zhitui();

        $model = new EarnInfo();

        $user_redis = new SetUserRedis();

        // 判断是否已经完成
        if($redis_component->getTagByType('JTSY',$date)){
            return true ;
        }

        $params['cond'] = 'date = :start_date ';
        $params['args'] = [':start_date'=>$date];

        $total_num = $zhitui_model->findCountByWhere($zhitui_model::tableName(),$params,$zhitui_model::getDb());
        $total_page = ceil($total_num/LOOP_LIMIT_NUM);
        for($i =0 ;$i<$total_page;$i++){

            $params['page']['curr_page'] = $i+1 ;
            $params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $list = $zhitui_model->findAllByWhere($zhitui_model::tableName(),$params,$zhitui_model::getDb());

            $batch_data = [];

            foreach($list as $day_balance){

                // 当前账户余额
                $balance = $day_balance['balance'];
                $balance = $this->returnRealBalance($balance) ;

                // 日期
                $date = $day_balance['date'];

                // 获取账户级别
                $level = $this->getLevel($balance);

                // 判断是否已经重复添加
                $user_id = $day_balance['user_id'] ;

                // 非普通用户不允许参与静态收益
                $user_info = $user_redis->getUserInfo($user_id);

                $total_earn = $user_redis->getUserEarnInfo($user_id);

                $limit_total_num = $this->getLevelMaxByBalance($level,$balance);


                if($total_earn >= $limit_total_num && $total_earn > 0) {

                    // 先设置redis的key为0
                    $user_redis->setUserEarnInfoZero($user_id);

                    $cash_insert_model = new CashInsert();
                    $cash_insert_model->setGuiling($user_id,$date,$level ) ;
                    continue ;
                }

                $base_percent = $this->getLevelPercent($level) ;

                // 获取额外的提成
                $zhitui = $day_balance['total'];
                $extra_percent = $this->getExtraPercentByZhitui($zhitui);

                if($balance <=0){
                    continue ;
                }

                $percent = $base_percent + $extra_percent ;

                // 获取用户每日获得收益的上线为25个
                $total = checkMaxEarn($balance * $percent,$day_balance['user_id'],$date) ;

                $temp_add = [];

                if($total > 0 ){

                    // 根据直推
                    $temp_add[] = $day_balance['user_id'] ;
                    $temp_add[] = $user_info['user_type'] ;
                    $temp_add[] = $day_balance['user_level'] ;
                    $temp_add[] = $day_balance['user_root_path'] ;
                    $temp_add[] = $date ;
                    $temp_add[] = 'JTSY' ;
                    $temp_add[] = $total ;
                    $temp_add[] = $level ;
                    $temp_add[] = 'N' ;
                    $temp_add[] = date('Y-m-d H:i:s');
                    $temp_add[] = date('Y-m-d H:i:s');

                    //$model->baseInsert($model::tableName(),$add_data,'db') ;
                    $batch_data[] = $temp_add ;

                    $this->setIncrByUserId($user_id,$level);

                    $redis_key = 'DayShouyi:'.$user_id.':'.$date;
                    $redis_model = new MyRedis() ;
                    $redis_info = $redis_model->get($redis_key);
                    $redis_model->set($redis_key,$redis_info+$total);

                    $user_redis->setUserEarnInfo($user_id,$total) ;
                }

            }

            if($batch_data){
                $fields = ['user_id','user_type','user_level','user_root_path','day','type','total','extra_level','is_tx','create_time','modify_time'];
                $model->baseBatchInsert($model::tableName(),$fields,$batch_data,'db');
            }
        }

        return $redis_component->setTagByType('JTSY',$date) ;

    }


}