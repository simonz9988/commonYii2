<?php
namespace common\components;

//自动插入
use common\models\CashInsert;
use common\models\Dajiang;
use common\models\Member;

class AutoInsert
{
    public function init(){
        #TODO
    }

    //每分钟自动执行
    public function doRun(){

        // 查询之前一分钟有没有自动插入
        $cash_insert_model = new CashInsert();
        $cash_params['cond'] = 'timeStamp >= :start_time AND timeStamp <=:end_time';
        $start_time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:00")) -60 );
        $end_time = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:59")) -60 );
        $cash_params['args'] = [':start_time'=>$start_time,':end_time'=>$end_time];
        $list = $cash_insert_model->findOneByWhere('sea_cash_insert',$cash_params);
        if($list){
            $kaijiang_components = new Kaijiang();
            foreach($list as $v){
                $amount = $v['amount'];
                $amount = $amount/1000000000000000000 ;
                $seconds =$kaijiang_components->addByAmount($amount);
                $this->addBySeconds($seconds);
            }
        }else{
            $this->addBySeconds(0) ;
        }



    }

    public function addBySeconds($seconds){
        $params['orderby'] = 'end_time desc';
        $params['cond'] = 'date <=:date';
        $params['args'] = [':date'=>date('Y-m-d')];
        $model = new CashInsert();
        $info = $model->findOneByWhere('sea_dajiang_countdown',$params);

        if($info && $seconds > 0){

            $ext = strtotime($info['end_time'])  -time() ;
            $end_time = $info['end_time'] ;

            $left_seconds = ($ext + $seconds) > 86400 ? (86400 - $ext) : $seconds ;
            $end_time = date('Y-m-d H:i:s', strtotime($end_time)+$left_seconds) ;

            $add_data['date'] = date('Y-m-d');
            $add_data['end_time'] = $end_time;
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            return $model->baseInsert('sea_dajiang_countdown',$add_data);


        }
        $user_info = $this->getAddUserInfo() ;
        $this->addByUserInfo($user_info,$info);
    }

    /**
     * 获取符合条件的自动地址
     * @return array
     */
    private function getAddUserInfo(){
        $components = new PrivateUser() ;
        $total_private_list = $components->returnAutoInsertAddress();
        $address_list = array_keys($total_private_list);
        $model = new Member();
        $eth_address = array_rand($address_list,1);
        $eth_address = $address_list[$eth_address];
        $info = $model->getUserInfoByAddress($eth_address);
        return $info ;

    }

    /**
     * 添加记录
     * @param $user_info
     * @param $info
     * @return mixed
     */
    public function addByUserInfo($user_info,$info){

        if(!$info){
            $end_time = date('Y-m-d H:i:s',time()+18*60);

            // 直接新增
            $this->addRecordByEndTime($end_time,$user_info) ;

        }else{

            $end_time = $info['end_time'];

            if($end_time < date('Y-m-d H:i:s')){
                $end_time = date('Y-m-d H:i:s',time() +18*60 ) ;

                // 插入记录
                $this->addRecordByEndTime($end_time,$user_info) ;
            }else{

                $ext = strtotime($end_time) - time() ;
                $minute = ceil($ext/60) ;
                if( ($minute >= 10 && $minute<=14 ) || $ext < 0  ){
                    // 倒计时还有10分钟
                    $end_time = date('Y-m-d H:i:s',strtotime($end_time) +18*60 ) ;

                    // 插入记录
                    $this->addRecordByEndTime($end_time,$user_info) ;

                }

            }


        }
    }

    /**
     * 根据截止时间新增记录
     * @param $end_time
     * @param $user_info
     * @return mixed
     */
    public function addRecordByEndTime($end_time,$user_info){
        $add_data['date'] = date('Y-m-d');
        $add_data['end_time'] = $end_time ;
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        $model = new Dajiang();
        $id = $model->baseInsert('sea_dajiang_countdown',$add_data) ;

        //插入用户任务
        $task_add_data['business_id'] = $id ;
        $task_add_data['business_type'] = 'SEND_ETH_TO_ALITA' ;
        $task_add_data['business_time'] = time();
        $task_add_data['business_timestamp'] = date('Y-m-d H:i:s');
        $task_add_data['from_address'] = $user_info['eth_address'] ;
        $task_add_data['to_address'] = '' ;
        $task_add_data['token_num'] = 0 ;
        $task_add_data['eth_num'] = 0.1;
        $task_add_data['tx_hash'] = '';
        $task_add_data['nonce'] = '';
        $task_add_data['admin_allowed'] = 'Y';
        $task_add_data['status'] = 'NOPUSH';
        $task_add_data['push_url'] = '';
        $model->baseInsert('sea_push_task',$task_add_data) ;

        // 插入入金
        $cash_add_data['user_id'] = $user_info['id'];
        $cash_add_data['is_backend'] = 'Y';
        $cash_add_data['business_id'] = 0;
        $cash_add_data['date'] = date('Y-m-d');
        $cash_add_data['timeStamp'] = time();
        $cash_add_data['amount'] = 0.1;
        $cash_add_data['user_level'] = $user_info['user_level'];
        $cash_add_data['is_super'] = $user_info['is_super'];
        $cash_add_data['user_type'] = $user_info['type'];
        $cash_add_data['user_root_path'] = $user_info['user_root_path'];
        $cash_add_data['inviter_user_id'] = $user_info['inviter_user_id'];
        $cash_add_data['inviter_username'] = $user_info['inviter_username'];
        $cash_add_data['create_time'] = date('Y-m-d H:i:s');
        $cash_add_data['modify_time'] = date('Y-m-d H:i:s');
        return $model->baseInsert('sea_cash_insert',$cash_add_data) ;
    }



}