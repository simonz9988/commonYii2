<?php

namespace common\models;

use Web3\Methods\Eth\Mining;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "sea_mining_machine_earn".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $total 盈利值
 * @property string $date 日期
 * @property int $date_timestamp 日期时间戳
 * @property string $type 类型
 * @property int $user_level 开始时间
 * @property string $user_root_path 结束时间
 * @property string $is_deleted 是否已经删除
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class MiningMachineEarn extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_earn';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'date_timestamp', 'user_level'], 'integer'],
            [['total'], 'number'],
            [['user_root_path'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['date'], 'string', 'max' => 8],
            [['type'], 'string', 'max' => 50],
            [['is_deleted'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'total' => 'Total',
            'date' => 'Date',
            'date_timestamp' => 'Date Timestamp',
            'type' => 'Type',
            'user_level' => 'User Level',
            'user_root_path' => 'User Root Path',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据计划任务生成收益信息
     * @return mixed
     */
    public function addByCronTab()
    {
        #TODO  先试用测试数据信息
        //$end_date = strtotime(date("Y-m-d 00:00:00")) + 86400*30;
        //for($start_date_timestamp = strtotime(date("Y-m-d 00:00:00"));$start_date_timestamp<=$end_date;$start_date_timestamp = $start_date_timestamp+86400){

        $start_date_timestamp = strtotime(date("Y-m-11 00:00:00")) ;
        $this->addByDate($start_date_timestamp) ;
        //}

    }

    /**
     * 根据截至日期发放收益
     * @param $end_date_timestamp
     * @param int $activity_id
     * @param $is_admin
     * @return mixed
     */
    public function addByDate($end_date_timestamp,$activity_id=0,$is_admin=false)
    {
        set_time_limit(0);

        // 查询截止日期之前的的所有订单记录数目  也就是前一天的记录信息
        $power_obj = new MiningMachineUserPower();
        if($activity_id){
            $power_params['cond'] = ' date_timestamp  <:date_timestamp AND is_deleted=:is_deleted AND activity_id=:activity_id';
            $power_params['args'] = [':date_timestamp'=>$end_date_timestamp,':is_deleted'=>'N',':activity_id'=>$activity_id];
            $power_params['fields'] = " count(1) as total_num ";
        }else{
            $power_params['cond'] = ' date_timestamp  <:date_timestamp AND is_deleted=:is_deleted';
            $power_params['args'] = [':date_timestamp'=>$end_date_timestamp,':is_deleted'=>'N'];
            $power_params['fields'] = " count(1) as total_num ";
        }

        //$power_params['group_by'] = " activity_key";
        $total_info = $this->findOneByWhere($power_obj::tableName(),$power_params,self::getDb());
        $total_num = $total_info && !is_null($total_info['total_num']) ? $total_info['total_num'] : 0 ;

        // 每次处理1000
        $page_num = 1000;
        $total_page = ceil($total_num/$page_num);

        // 活动信息列表
        $activity_list_info = [] ;
        // 当前截止时间总的算力
        $activity_total_list = [] ;
        // 当前截止时间总的有效算力
        $activity_total_useful_list = [] ;
        $activity_obj = new MiningMachineActivity();
        // 用户列表信息
        $user_list = [];
        $member_obj = new Member();

        $machine_list = [];
        $machine_obj = new MiningMachine() ;
        //$daily_add_obj = new MiningMachineActivityDailyAdd();
        for($i=0;$i<$total_page;$i++){

            //$power_params['fields'] = "user_id,activity_key,date,date_timestamp,activity_id,sum(total) as total_sum";
            $power_params['fields'] = "*";
            $power_params['limit'] = 1000;
            $list = $this->findAllByWhere($power_obj::tableName(),$power_params,self::getDb());
            if(!$list){
                continue ;
            }

            foreach($list as $v){
                $activity_id = $v['activity_id'];
                $activity_info = isset($activity_list_info[$activity_id]) ? $activity_list_info[$activity_id] : [];
                if(!$activity_info){
                    $activity_info = $activity_obj->getInfoById($activity_id);
                    $activity_list_info[$activity_id] = $activity_info ;
                }

                //$daily_add_obj->addByActivityIdAndEndTimestamp($activity_id,$end_date_timestamp,$activity_info['daily_add']);

                // 获取总量
                if(!isset($activity_total_list[$activity_id])){
                    $activity_total_power = $activity_obj->getTotalByEndDate($activity_info,$end_date_timestamp);
                    $activity_total_list[$activity_id] = $activity_total_power ;
                }else{
                    $activity_total_power =$activity_total_list[$activity_id]  ;
                }

                // 获取有效总量
                if(!isset($activity_total_useful_list[$activity_id])){
                    $activity_total_useful_power = $activity_obj->getTotalUsefulByEndDate($activity_info,$end_date_timestamp,$activity_total_power);
                    $activity_total_useful_list[$activity_id] = $activity_total_useful_power ;
                }else{
                    $activity_total_useful_power = $activity_total_useful_list[$activity_id] ;
                }

                // 当前记录总值
                $real_total = !is_null($v['real_total']) ? $v['real_total'] : 0 ;

                if(isset($user_list[$v['user_id']])){
                    $user_info = $user_list[$v['user_id']];
                }else{
                    $user_info = $member_obj->getUserInfoById($v['user_id']);
                    $user_list[$v['user_id']] = $user_info ;
                }

                if(isset($machine_list[$v['machine_id']])){
                    $machine_info = $machine_list[$v['machine_id']] ;
                }else{
                    $machine_info = $machine_obj->getInfoById($v['machine_id']);
                    $machine_list[$v['machine_id']] = $machine_info ;
                }

                // 当前记录的实际算力
                $user_get_power = $activity_total_power > 0 ? ($real_total/$activity_total_power)*$activity_total_useful_power : 0 ;
                $this->addRecord($v,$user_get_power,$activity_info,$user_info,$machine_info,$end_date_timestamp,$is_admin);


            }
        }
    }

    /**
     * 添加对饮的佣金记录
     * @param $power_info
     * @param $user_get_power
     * @param $activity_info
     * @param $user_info
     * @param $machine_info
     * @param $end_date_timestamp
     * @param $is_admin
     * @return mixed
     */
    public function addRecord($power_info,$user_get_power,$activity_info,$user_info,$machine_info,$end_date_timestamp,$is_admin=false){

        if($user_get_power <= 0){
            return false ;
        }

        // 每个单位固定的收益
        //$unit_earn = $activity_info['unit_earn'];
        // 通过日志读取每日收益
        $log_obj = new MiningMachineActivityLog();
        $unit_earn = $log_obj->getUnitEarn($activity_info['id'],$end_date_timestamp);

        $total_earn = $unit_earn*$user_get_power ;

        // 需要扣除机器手续费
        $total_earn =  $total_earn*(100-$machine_info['fee'])*0.01 ;

        $ext_day = ceil(($end_date_timestamp-$power_info['date_timestamp'])/86400);


        $site_config = new SiteConfig();

        // 固定的收益
        if($ext_day >= 1){


            // 判断是否已经超出服务期限
            if($ext_day > $power_info['limit_day']){
                return false ;
            }else{
                #TODO 直接收益需要手动方法
                // 0.25
                //if($is_admin){
                if(true){
                    $jiangli_keyong_bili = $site_config->getByKey('jiangli_keyong_bili');
                    $real_earn = $total_earn*$jiangli_keyong_bili*0.01 ;

                    $this->addByUserAndType($user_info,$real_earn,'GUDING',$machine_info,$power_info['id'],$end_date_timestamp);

                    // 冻结记录也需要手动新增才会增加
                    //0.75 增加冻结记录
                    $frozen_obj = new MiningMachineFrozenEarn();
                    $jiangli_dongjie_bili = $site_config->getByKey('jiangli_dongjie_bili');
                    $frozen_earn = $total_earn*$jiangli_dongjie_bili*0.01 ;

                    $frozen_obj->addByUserAndType($user_info,$frozen_earn,'SHIFANG',$machine_info,$end_date_timestamp,$activity_info,$power_info['id']);

                }
            }
        }

        // 锁定的收益
        //if($ext_day >=2  && !$is_admin){
        if($ext_day >=2  ){
            // 找到前一天的冻结
            $frozen_obj = new MiningMachineFrozenEarn();
            $real_earn_list = $frozen_obj->getPrevDayEarnList($end_date_timestamp,$user_info,'SHIFANG');

            if($real_earn_list){
                foreach($real_earn_list as $v){
                    $frozen_id = $v['id'];
                    $real_earn = $v['real_earn'];
                    $this->addByUserAndType($user_info,$real_earn,'SHIFANG',$machine_info,$frozen_id,$end_date_timestamp);
                }
            }

        }

    }

    /**
     * 插入指定币种的指定类型的盈利信息
     * @param $user_info
     * @param $total
     * @param $type
     * @param $machine_info
     * @param $business_id SHIFANG类型的需要传入冻结记录表的ID
     * @param $end_date_timestamp
     * @return bool|string
     */
    private function addByUserAndType($user_info,$total,$type,$machine_info,$business_id =0,$end_date_timestamp){
        if($total <= 0 ){
            return false ;
        }

        $coin = $machine_info['coin'] ;

        // 判断是否重复添加
        $params['cond'] = 'user_id=:user_id AND type=:type AND business_id=:business_id AND coin=:coin AND date_timestamp=:date_timestamp AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_info['id'],':type'=>$type,':business_id'=>$business_id,':coin'=>$coin,':date_timestamp'=>$end_date_timestamp ,':is_deleted'=>'N'];
        $params['fields'] = 'id';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            return false ;
        }

        $total = numberSprintf($total,6);

        $add_data['user_id'] = $user_info['id'] ;
        $add_data['total'] = $total ;
        $add_data['business_id'] = $business_id ;


        $add_data['coin'] = $coin ;
        $add_data['date'] = date("Ymd",$end_date_timestamp) ;
        $add_data['date_timestamp'] = strtotime(date("Y-m-d 00:00:00",$end_date_timestamp)) ;
        $add_data['type'] = $type ;
        $add_data['user_level'] = $user_info['user_level'] ;
        $add_data['user_root_path'] = $user_info['user_root_path'] ;
        $add_data['is_deleted'] = 'N' ;

        // 当前时间
        $now = date('Y-m-d H:i:s') ;
        $add_data['create_time'] = $now ;
        $add_data['modify_time'] = $now ;

        $this->baseInsert(self::tableName(),$add_data);

        // 变更资产
        $balance_obj = new MiningMachineUserBalance();
        $balance_obj->addByMachineEarn($user_info['id'],$coin,$total,$type);

        // 新增资产记录
        $balance_record_obj = new MiningMachineUserBalanceRecord();
        $balance_record_obj->addByMachineEarn($user_info['id'],$coin,$total,$type);
    }

    /**
     * 判断是否重复插入
     * @param $user_id
     * @param $type
     * @param $business_id
     * @return mixed
     */
    public function checkRepeatInsert($user_id,$type,$business_id){
        $params['cond'] = 'user_id=:user_id AND type=:type AND business_id=:business_id';
        $params['args'] = [':user_id'=>$user_id,':type'=>$type,':business_id'=>$business_id];
        $params['fields'] = 'id';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? true :false ;
    }

    /**
     * 获取团队界别
     * @param $team_total
     * @param $dict_info
     * @return mixed
     */
    public function getTeamLevel($team_total,$dict_info){

        $p1_tuandui_yeji = $dict_info['p1_tuandui_yeji'];
        $p2_tuandui_yeji = $dict_info['p2_tuandui_yeji'];
        $p3_tuandui_yeji = $dict_info['p3_tuandui_yeji'];

        if($team_total < $p1_tuandui_yeji){
            return  0 ;
        }else if($team_total >= $p1_tuandui_yeji && $team_total < $p2_tuandui_yeji){
            return 1 ;
        }else  if($team_total >= $p2_tuandui_yeji && $team_total < $p3_tuandui_yeji){
            return 2 ;
        }else  if($team_total >= $p3_tuandui_yeji){
            return 3 ;
        }

        return  0 ;

    }

    /**
     * 判断当前用户的其他业绩是否符合要求
     * @param $team_level
     * @param $dict_info
     * @param $user_info
     * @param $value
     * @param $pay_user_info
     * @return mixed
     */
    public function checkOtherLevel($team_level,$dict_info,$user_info,$value,$pay_user_info){

        $params['cond'] = 'inviter_user_id = :inviter_user_id ';
        $params['args'] = [':inviter_user_id'=>$user_info['id']];

        $params['fields'] = 'id,team_total,self_total' ;
        $params['orderby'] = 'team_total desc';
        $son_list = $this->findAllByWhere('sea_user',$params,self::getDb());

        $pay_user_root_path_arr = [] ;
        if($pay_user_info){
            $pay_user_root_path = $pay_user_info['user_root_path'];
            $pay_user_root_path_arr = explode('--',$pay_user_root_path);
        }

        foreach($son_list as $k=> $v){

            // 下级团队业绩算上自身，recover
            $son_list[$k]['team_total'] = $v['team_total'] + $v['self_total'];
            if($pay_user_root_path_arr && in_array($v['id'],$pay_user_root_path_arr)){
                $son_list[$k]['team_total'] =  ($v['team_total']-$value) ;
            }

        }


        $son_list = array_sort($son_list,'team_total','desc');


        if(!$son_list){
            return  0 ;
        }

        // 最大分区的
        $max_son_info = $son_list[0] ;

        unset($son_list[0]);
        $left_total = 0 ;


        foreach($son_list as $v){
            $left_total += $v['team_total'];
        }

        $return_level =  0 ;
        if($team_level == 1){
            if($left_total >= $dict_info['p1_qita_yeji']){
                $return_level = 1 ;
            }
        }


        if($team_level == 2){
            if($left_total >= $dict_info['p2_qita_yeji']){
                $return_level = 2 ;
            }
        }


        if($team_level == 3){
            if($left_total >= $dict_info['p3_qita_yeji']){
                $return_level = 3 ;
            }
        }

        // 判断是否服务最大分区的要求 总业绩的60%

        $p1_tuandui_yeji_percent = $dict_info['p1_tuandui_yeji']*0.6;
        $p2_tuandui_yeji_percent = $dict_info['p2_tuandui_yeji']*0.6;
        $p3_tuandui_yeji_percent = $dict_info['p3_tuandui_yeji']*0.6;

        if($return_level == 1){
            if($max_son_info['team_total'] < $p1_tuandui_yeji_percent){
                $return_level = 0 ;
            }
        }

        if($return_level == 2){
            if($max_son_info['team_total'] < $p2_tuandui_yeji_percent && $max_son_info['team_total'] >= $p1_tuandui_yeji_percent){
                $return_level = 1 ;
            }else if($max_son_info['team_total'] < $p1_tuandui_yeji_percent){
                $return_level = 0 ;
            }
        }

        if($return_level == 3){
            if($max_son_info['team_total'] < $p3_tuandui_yeji_percent && $max_son_info['team_total'] >= $p2_tuandui_yeji_percent){
                $return_level = 2 ;
            }else if($max_son_info['team_total'] < $p2_tuandui_yeji_percent  && $max_son_info['team_total'] >= $p1_tuandui_yeji_percent){
                $return_level = 1 ;
            }else if($max_son_info['team_total'] < $p1_tuandui_yeji_percent){
                $return_level = 0 ;
            }
        }

        return $return_level ;

    }

    /**
     * 获取盈利的团队和分享收益
     * @param $user_id
     * @return mixed
     */
    public function getTotalEarnUsdt($user_id){

        $params['cond'] = 'user_id =:user_id AND coin=:coin AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>'USDT',':is_deleted'=>'N'];
        $params['fields'] = 'sum(total) as total_sum';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        $total = $info && !is_null($info['total_sum']) ? $info['total_sum'] : 0 ;
        return $total ;
    }

    /**
     * 获取用户的盈利列表
     * @param $user_id
     * @param $page
     * @return mixed
     */
    public function getUserEarnList($user_id,$page){

        $params['cond'] = 'user_id =:user_id  AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N'];
        $params['fields'] = 'total,id,coin,type,create_time';
        $params['page']['curr_page'] = $page ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        return $list ;
    }


    /**
     * 获取用户的盈利列表
     * @param $user_id
     * @param $page
     * @return mixed
     */
    public function getUserEarnFilList($user_id,$page){

        $record_obj = new  MiningMachineUserBalanceRecord();
        $params['cond'] = 'user_id =:user_id  AND is_deleted=:is_deleted and type in("GUDING","SHIFANG")';
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N'];
        $params['fields'] = 'total,id,coin,type,create_time';
        $params['page']['curr_page'] = $page ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if($list){
            foreach($list as $k=>$v){
                $list[$k]['type_name'] = $record_obj->getTypeName($v['type']);
            }
        }
        return $list ;
    }

    /**
     * 获取资产总记录数
     * @param $user_id
     * @return int
     */
    public function getUserEarnNum($user_id){

        $params['cond'] = 'user_id =:user_id  AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N'];
        $params['fields'] = 'count(1) as total_num';

        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        return $info&& !is_null($info['total_num'])  ? $info['total_num']:0; ;
    }

    /**
     * 根据用户ID和订单ID返回执行的盈利值
     * @param $user_id
     * @param $order_id
     * @return mixed
     */
    public function getTotalByOrderId($user_id,$order_id){

        $params['cond'] = 'user_id =:user_id AND business_id=:business_id  AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':business_id'=>$order_id,':is_deleted'=>'N'];
        $params['fields'] = 'total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['total'] : 0 ;
    }

    /**
     * 判断当前币种已发放的总收益
     * @param $user_id
     * @param $coin
     * @return mixed
     */
    public function getTotalEarnByUserIdAndCoin($user_id,$coin){
        $params['cond'] = 'user_id =:user_id AND coin=:coin  AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':coin'=>$coin,':is_deleted'=>'N'];
        $params['fields'] = 'sum(total) as sum_total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['sum_total']) ?  numberSprintf($info['sum_total'],6) : 0 ;
    }

    /**
     * 手动发放收益
     * @param $unit_earn
     * @param $daily_add
     * @param $activity_id
     * @return mixed
     */
    public function addByAdmin($unit_earn,$daily_add,$activity_id){

        $now = date('Y-m-d H:i:s');

        $activity_obj = new MiningMachineActivity() ;
        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            // 更新活动基本基本信息
            $update_data['unit_earn'] = $unit_earn;
            $update_data['daily_add'] = $daily_add;
            $update_data['total_supply'] = new Expression('total_supply + '.$daily_add);
            $update_data['modify_time'] = $now;

            $activity_obj->baseUpdate($activity_obj::tableName(),$update_data,'id=:id',[':id'=>$activity_id]);

            $earn_obj = new MiningMachineEarn();

            $end_date_timestamp = strtotime(date('Y-m-d 00:00:00'));
            //TODO 暫時去除發放
            //$earn_obj->addByDate($end_date_timestamp,$activity_id,true);

            $update_data1['send_earn_date'] = date("Ymd",$end_date_timestamp);
            $update_data1['modify_time'] = $now;

            $res = $activity_obj->baseUpdate($activity_obj::tableName(),$update_data1,'id=:id',[':id'=>$activity_id]);
            $transaction->commit();
            return  true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

}