<?php
namespace common\components;
//推荐玩家奖励
use common\models\DayBalance;
use common\models\EarnInfo;
use yii\db\Expression;

class TuijianWanJia
{
    public function init(){
        #TODO
    }

    public function getLevel($zhitui){
        if($zhitui < 30){
            return 1 ;
        }else if($zhitui >=30 && $zhitui <100){
            return 2 ;
        }else{
            return 3 ;
        }
    }

    /**
     * 获取指定指定级别的子节点信息的计算总额
     * @param $user_id
     * @param $level
     * @return mixed
     */
    public function getLevelSon($day_balance ,$level){
        $model = new DayBalance();
        $params['cond'] = 'date=:date AND user_level =:user_level AND user_root_path like :user_root_path ';
        $params['args'] = [':date'=>$day_balance['date'],':user_level'=>$day_balance['user_level'] + $level ,':user_root_path'=>'%--'.$day_balance['user_id'].'--%'];

        $expression = new Expression('sum( LEAST(balance,'.$day_balance['balance'].')  )  total') ;
        $params['fields'] = $expression;
        $info = $model->findOneByWhere($model::tableName(),$params,$model::getDb()) ;

        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }


    public function add($day_balance,$earn_model){

        //通过通过直推可以获取拿到的是几层奖励
        $zhitui = $day_balance['zhitui'];
        $level = $this->getLevel($zhitui) ;

        $total = 0 ;
        $jt_componet = new Jingtai();
        $base_percent = $jt_componet->getLevelPercent($level) ;

        // 获取额外的提成
        $zhitui = $day_balance['zhitui'];
        $extra_percent = $jt_componet->getExtraPercentByZhitui($zhitui);

        $base_percent = $base_percent + $extra_percent ;

        if($level ==1){
            $son_total_1 = $this->getLevelSon($day_balance,$level) ;
            $total +=   $son_total_1*0.5*$base_percent ;
        }

        if($level ==2){
            $son_total_1 = $this->getLevelSon($day_balance,1) ;
            $total +=   $son_total_1*0.5*$base_percent ;

            $son_total_2 = $this->getLevelSon($day_balance,$level) ;
            $total +=   $son_total_2*0.4*$base_percent ;
        }

        if($level ==3){
            $son_total_1 = $this->getLevelSon($day_balance,1) ;
            $total +=   $son_total_1*0.5*$base_percent ;

            $son_total_2 = $this->getLevelSon($day_balance,2) ;
            $total +=   $son_total_2*0.4*$base_percent ;

            $son_total_3 = $this->getLevelSon($day_balance,3) ;
            $total +=   $son_total_3*0.3*$base_percent ;
        }

        if($total > 0 ){

            //新增
            $add_data['user_id'] = $day_balance['user_id'] ;
            $add_data['user_level'] = $day_balance['user_level'] ;
            $add_data['user_root_path'] = $day_balance['user_root_path'] ;
            $add_data['day'] = $day_balance['date'] ;
            $add_data['type'] = 'TGJL' ;
            $add_data['total'] = $total ;
            $add_data['extra_level'] = $level ;
            $add_data['is_tx'] = 'N' ;
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            return $earn_model->baseInsert($earn_model::tableName(),$add_data,'db') ;
        }
    }


    public function addTgjl($start_date,$end_date){
        $model = new DayBalance();
        $earn_model = new EarnInfo() ;
        $list = $model->getListByRangeDate($start_date,$end_date);

        if(!$list){
            return true ;
        }
        foreach($list as $v){
            $this->add($v,$earn_model) ;
        }
        return true ;
    }



}