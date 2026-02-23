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
//开启幸运大奖
class Kaijiang
{
    // 当前时间段
    public $stage = [0,100000,200000,300000,400000,600000,735000];

    public function init(){
        #TODO
    }

    /**
     * 根据总额获取当前所处的阶段
     * @param $total
     * @return  integer
     */
    public function getStageByTotal($total){

        if($total < 100000){
            return  0 ;
        }else if($total >= 100000 && $total <200000 ){
            return  1 ;
        }else if($total >= 200000 && $total <300000 ){
            return  2 ;
        }else if($total >= 300000 && $total <400000 ){
            return  3 ;
        }else if($total >= 400000 && $total <600000 ){
            return  4 ;
        }else if($total >= 600000 && $total <735000 ){
            return  5 ;
        }else{
            return  5 ;
        }

    }

    /**
     * 执行开奖
     * @param $start_time
     * @param $end_time
     */
    public function doSend($start_time,$end_time){

        $redis_component = new SetUserRedis();
        $complete_tag = $redis_component->getTagByType('SUPERPLAYER',$end_time) ;
        if(!$complete_tag){
            return true ;
        }

        $ext = (strtotime($end_time) - strtotime($start_time) )/86400 ;
        if(!$ext){

            $this->dealByDate($start_time);

        }else{
            for($i=0;$i<=$ext;$i++){
                $date = date('Y-m-d', (strtotime($start_time) + $i*86400) ) ;

                $this->dealByDate($date);
            }
        }
    }

    /**
     * 按照日期进行处理
     * @param $date
     * @return mixed
     * Note:此方法已作废
     */
    public function dealByDate($date){

        $redis_component = new SetUserRedis();
        if($redis_component->getTagByType('KAIJIANG',$date)){
            return true ;
        }

        #TODO 开奖直接不做处理，直接后台操作
        //return $redis_component->setTagByType('KAIJIANG',$date) ;

        // 查询有没有开奖记录
        $dajiang_model = new Dajiang();

        $cash_insert_model = new CashInsert();
        $total_cash_num = $cash_insert_model->getTotalNumByDate($date);
        $total_page = ceil($total_cash_num/LOOP_LIMIT_NUM) ;

        $having_jiedian = false ;
        for($i=0 ;$i<$total_page;$i++){

            $list = $cash_insert_model->getListByDateAndPage($date,$i);

            foreach($list as $v){

                // 节点大奖直接跳出当日循环
                $total_insert = $this->getTotalInsertFromDajiang();

                $stage = $this->getStageByTotal($total_insert);

                $total_insert = $total_insert + $v['amount'] ;
                $stage2 = $this->getStageByTotal($total_insert);

                $this->setTotalInsertFromDajiang($total_insert);

                if($stage != $stage2 && !$having_jiedian){
                    //添加节点大奖 每天只有一个节点奖励
                    $dajiang_model->addJiedianDajiang($v['user_id'],$v['timeStamp']);
                    $having_jiedian = true ;
                }

            }
        }
        /*
        if($having_jiedian){
            //更新赛季已结束   并且不执行以下内容
            $redis_component->setTagByType('KAIJIANG',$date) ;
            return true ;

        }else{

            $today_end_time = date('Y-m-d 00:18:00') ;
            $today_end_time_str = strtotime($today_end_time) ;

            $prev_user_id =  0 ;

            for($i=0 ;$i<$total_page;$i++){

                $list = $cash_insert_model->getListByDateAndPage($date,$i);

                foreach($list as $v){

                    if($v['timeStamp'] > $today_end_time_str){
                        if($prev_user_id){
                            //插入24小时大奖
                            $this->add24HByUserId($prev_user_id) ;
                        }
                        $redis_component->setTagByType('KAIJIANG',$date) ;
                        return false ;
                    }

                    if($v['timeStamp'] <= $today_end_time){
                        $prev_user_id = $v['user_id'] ;

                        // 需要新增的时长
                        $add_seconds = $this->addByAmount($v['amount']) ;

                        $end_time = date('Y-m-d H:i:s',strtotime($today_end_time) + $add_seconds ) ;
                        if($end_time > date('Y-m-d 23:59:59')){
                            $today_end_time_str = strtotime(date('Y-m-d 23:59:59')) ;
                        }else{
                            $today_end_time_str = strtotime($end_time) ;
                        }

                    }


                }
            }
        }*/

        return $redis_component->setTagByType('KAIJIANG',$date) ;

    }

    /**
     * 按照日期进行处理
     * @param $date
     */
    public function dealByDateBak($date){

        $redis_component = new SetUserRedis();
        if($redis_component->getTagByType('KAIJIANG',$date)){
            return true ;
        }

        // 查询有没有开奖记录
        $dajiang_model = new Dajiang();
        $lasted_dajiang = $dajiang_model->getLastedInfo();

        $cash_insert_model = new CashInsert();
        $total_cash_num = $cash_insert_model->getTotalNumByDate($date);
        $total_page = ceil($total_cash_num/LOOP_LIMIT_NUM) ;

        $having_jiedian = false ;
        for($i=0 ;$i<$total_page;$i++){

            $list = $cash_insert_model->getListByDateAndPage($date,$i);

            foreach($list as $v){

                // 节点大奖直接跳出当日循环
                $total_insert = $this->getTotalInsertFromDajiang();

                $stage = $this->getStageByTotal($total_insert);

                $total_insert = $total_insert + $v['amount'] ;
                $stage2 = $this->getStageByTotal($total_insert);

                $this->setTotalInsertFromDajiang($total_insert);

                if($stage != $stage2 && !$having_jiedian){
                    //添加节点大奖 每天只有一个节点奖励
                    $dajiang_model->addJiedianDajiang($v['user_id'],$v['timeStamp']);
                    $having_jiedian = true ;
                }

            }
        }

        if($having_jiedian){
            //更新赛季已结束   并且不执行以下内容
            $dajiang_model->updateDoneBySeason($lasted_dajiang['season']) ;

        }else{

            for($i=0 ;$i<$total_page;$i++){

                $list = $cash_insert_model->getListByDateAndPage($date,$i);

                foreach($list as $v){

                    // 判断当前的总入金额有没有达到
                    if(!$lasted_dajiang){

                        // 判断是否需要新增
                        $add_seconds = $this->addByAmount($v['amount']) ;
                        if($add_seconds){
                            $add_data['start_time'] = date('Y-m-d H:i:s',$v['timeStamp']);
                            $add_data['next_period_time'] = date('Y-m-d H:i:s',$v['timeStamp']+$add_seconds) ;
                            $add_data['season'] = 1;
                            $add_data['is_done'] = 'N';
                            $add_data['create_time'] =date('Y-m-d H:i:s');
                            $add_data['modify_time'] =date('Y-m-d H:i:s');
                            $lasted_dajiang = $add_data ;
                            $dajiang_model->baseInsert($dajiang_model::tableName(),$add_data,'db') ;
                        }

                        continue ;

                    }else{

                        // 当前充值时间
                        $timeStamp = $v['timeStamp'];
                        // 判断当前时间有没有超过截止时间
                        if($lasted_dajiang['is_done']=='N' ){

                            if( $timeStamp > strtotime($lasted_dajiang['next_period_time'])){
                                // 发放给前一个用户
                                $lasted_dajiang['is_done'] = 'Y' ;
                                $dajiang_model->updateDoneBySeason($lasted_dajiang['season']) ;

                                $dajiang_model->add24Dajiang($lasted_dajiang['season']) ;

                                continue ;
                            }

                        }else{

                            // 判断是否需要新增
                            $add_seconds = $this->addByAmount($v['amount']) ;
                            if($add_seconds){
                                $add_data['start_time'] = date('Y-m-d H:i:s',$v['timeStamp']);
                                $add_data['next_period_time'] = date('Y-m-d H:i:s',$v['timeStamp']+$add_seconds) ;
                                $add_data['season'] = $lasted_dajiang['season'] + 1;
                                $add_data['is_done'] = 'N';
                                $add_data['create_time'] =date('Y-m-d H:i:s');
                                $add_data['modify_time'] =date('Y-m-d H:i:s');
                                $lasted_dajiang = $add_data ;
                                $dajiang_model->baseInsert($dajiang_model::tableName(),$add_data,'db') ;
                            }

                        }
                    }

                }
            }
        }

        return $redis_component->setTagByType('KAIJIANG',$date) ;

    }

    /**
     * 根据入金总量，判断是否延长以及延长时间
     * @param $amount
     * @return int
     */
    public function addByAmount($amount){

        if($amount < 0.1){
            return  0 ;
        }else{

            if($amount > 8 ){
                $amount  = 8 ;
            }

            $min = ($amount/0.1) * 18 ;
            return intval($min*60) ;
        }

    }

    /**
     * 获取当前的总入金量
     */
    private function getTotalInsertFromDajiang(){
        $redis_key = "Kaijiang:total_insert" ;
        $redis_model = new MyRedis();
        $redis_info = $redis_model->get($redis_key);
        return floatval($redis_info);
    }


    private function setTotalInsertFromDajiang($total){
        $redis_key = "Kaijiang:total_insert" ;
        $redis_model = new MyRedis();
        return $redis_model->set($redis_key,$total) ;
    }

    /**
     * 添加24小时倒计时大奖
     * @param $user_id
     * @return mixed
     */
    public function add24HByUserId($user_id){
        $params['cond'] = 'id = :id';
        $params['args'] = [':id'=>$user_id];
        $params['fields'] = 'eth_address';
        $model = new Member();
        $info = $model->findOneByWhere('sea_user',$params) ;
        $eth_address = $info['eth_address'] ;

        $components = new PrivateUser() ;
        $total_private_list = $components->returnPrivateAddress();
        $address_list = array_keys($total_private_list);
        if(!in_array($eth_address,$address_list)){
            $eth_address = array_rand($address_list,1);
            $info = $model->getUserInfoByAddress($eth_address);
        }

        if(!$info){
            return true;
        }

        //获取当天投入总和
        $cash_insert_model = new CashInsert();
        $total_insert = $cash_insert_model->getTotalInsertFromEndTime(date('Y-m-d'),date('Y-m-d'));
        $total = $total_insert*0.1 ;

        if($total <=0){
            return false ;
        }
        $add_data['user_id'] = $info['id'] ;
        $add_data['son_user_id'] = 0 ;
        $add_data['user_level'] = $info['user_level'] ;
        $add_data['user_root_path'] = $info['user_root_path'] ;
        $add_data['day'] = date('Y-m-d') ;
        $add_data['type'] = '24H' ;
        $add_data['total'] = $total ;
        $add_data['season'] = 0 ;
        $add_data['base_percent'] = 0 ;
        $add_data['extra_percent'] = 0 ;
        $add_data['extra_level'] = 0 ;
        $add_data['is_tx'] = 'N' ;
        $add_data['status'] = 'UNDEAL' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $model->baseInsert('sea_earn_info',$add_data) ;

    }

}