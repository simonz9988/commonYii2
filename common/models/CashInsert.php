<?php

namespace common\models;

use common\components\EthWallet;
use common\components\MyRedis;
use common\components\OkexTrade;
use common\components\SetUserRedis;
use Yii;

/**
 * This is the model class for table "sea_cash_insert".
*/
class CashInsert extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_cash_insert';
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
     * 添加转账需要的快照记录
     * @param integer $value
     * @param array   $from_user
     * @param integer $business_id
     * @param string  $date
     * @param string  $timeStamp 具体的时间戳
     * @return mixed
     */
    public function addTrans($value,$from_user,$business_id,$date,$timeStamp){
        // 插入每日充值记录表
        $check_params['cond'] = 'date=:date AND business_id = :business_id';
        $check_params['args'] = [':date'=>$date,':business_id'=>$business_id];
        $check_info = $this->findOneByWhere(self::tableName(),$check_params,self::getDb());
        if($check_info){
            return true ;
        }

        $add_data['user_id'] = $from_user['id'] ;
        $add_data['business_id'] = $business_id ;
        $add_data['timeStamp'] = $timeStamp ;
        $add_data['date'] = $date ;
        $add_data['amount'] = $value ;
        $add_data['user_level'] = $from_user['user_level'] ; ;
        $add_data['user_root_path'] = $from_user['user_root_path'] ; ;
        $add_data['inviter_user_id'] = $from_user['inviter_user_id'] ; ;
        $add_data['inviter_username'] = $from_user['inviter_username'] ; ;
        $add_data['user_type'] = $from_user['type'] ; ;
        $add_data['is_super'] = $from_user['is_super'] ; ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        $this->baseInsert(self::tableName(),$add_data,'db');

        //根据比例要生成指定的token
        $site_config_model = new SiteConfig() ;
        $eth_to_token_num =  $site_config_model->getByKey('eth_to_token_num');

        //需要转出的token数量
        $token_num = intval($eth_to_token_num*$value) ;
        if(!$token_num){
            return true ;
        }

        // 任务模型
        $push_task_model = new PushTask();

        $task_params['cond'] = 'business_id =:business_id AND business_type=:business_type ';
        $task_params['args'] = [':business_id'=>$business_id ,':business_type'=>'TRANS_TOKEN'];
        $task_info = $push_task_model->findOneByWhere($push_task_model::tableName(),$task_params,$push_task_model::getDb());
        if($task_info){
            return true ;
        }

        // 转Token的业务ID对应的是充值记录表的ID
        $task_add_data['business_id'] = $business_id ;
        $task_add_data['business_type'] = 'SEND_TOKEN';
        $task_add_data['business_time'] = time();
        $task_add_data['business_timestamp'] = date('Y-m-d H:i:s');
        $task_add_data['to_address'] = $from_user['eth_address'];
        $task_add_data['token_num'] = $token_num;
        $task_add_data['tx_hash'] = '';
        $task_add_data['admin_allowed'] = 'Y';
        $task_add_data['status'] = 'NOPUSH';
        $task_add_data['push_url'] = '';
        $task_add_data['create_time'] = date('Y-m-d H:i:s');
        $task_add_data['modify_time'] = date('Y-m-d H:i:s');

        return $this->baseInsert($push_task_model::tableName(), $task_add_data, 'db');
    }

    /**
     * 获取截止当前时间用户总充值金额
     * @param $user_id
     * @param $end_time
     * @param $total_list
     * @return int
     */
    public function getTotalFromEndTime($user_id,$end_time,$total_list){

        $total = 0 ;
        foreach($total_list as $v){
            if($v['user_id'] == $user_id && $v['date']<=$end_time){
                $total += $v['amount'];
            }
        }
        return $total ;

        $params['cond'] = 'user_id = :user_id AND date <= :end_time';
        $params['args'] = [':user_id'=>$user_id,':end_time' =>$end_time];
        $params['fields'] = " sum(amount) as total" ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        $res = $info && !is_null($info['total']) ? $info['total'] : 0 ;
        return $res ;
    }

    /**
     * 获取当前时间段内系统整个入金的金额
     * @param $end_time
     * @param int $start_time
     */
    public function getTotalInsertFromEndTime($end_time,$start_time = 0 ){
        if($start_time){
            $params['cond'] = ' date <= :end_time AND date >=:start_time AND amount > 0 ';
            $params['args'] = [':end_time' =>$end_time,':start_time'=>$start_time];
        }else{
            $params['cond'] = ' date <= :end_time AND amount > 0 ';
            $params['args'] = [':end_time' =>$end_time];
        }

        $params['fields'] = " sum(amount) as total" ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        $res = $info && !is_null($info['total']) ? $info['total'] : 0 ;
        return $res ;

    }


    /**
     * 获取前一天的入金总额
     * @param $date
     * @return mixed
     */
    public function getPrevDayTotal($date){

        $prev_date = date('Y-m-d',strtotime($date)-86400);
        $params['cond'] = 'date=:date';
        $params['args'] = [':date'=>$prev_date];
        $params['fields'] = " sum(amount) as total" ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        $res = $info && !is_null($info['total']) ? $info['total'] : 0 ;
        return $res ;

    }

    /**
     * 获取每日的总数目
     * @param $date
     * @return int
     */
    public function getTotalNumByDate($date){
        $params['cond'] = 'date=:date';
        $params['args'] = [':date'=>$date];
        $total = $this->findCountByWhere(self::tableName(),$params,self::getDb());
        return $total ;
    }

    /**
     * 依据时间和分页，按照时间顺序返回列表值
     * @param $date
     * @param $i
     * @return array
     */
    public function getListByDateAndPage($date,$i){
        $params['cond'] = 'date=:date';
        $params['args'] = [':date'=>$date];
        $params['page']['curr_page'] = $i+1 ;
        $params['page']['page_num'] = LOOP_LIMIT_NUM ;
        $params['orderby'] = ' timeStamp ASC ';
        $list  = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 设置归零
     * @param $user_id
     * @param $date
     * @param $level
     * @return mixed
     */
    public function setGuiling($user_id,$date,$level){

        // 查询总的入账金额
        $day_balance_params['cond'] = 'user_id=:user_id AND date =:date';
        $day_balance_params['args'] = [':user_id'=>$user_id,':date'=>$date];
        $info = $this->findOneByWhere('sea_day_balance',$day_balance_params);
        if(!$info){
            return false ;
        }

        // 查询上一条的充值记录是不是为负数
        // 假如为负数则不进行插入
        $last_params['cond'] = 'user_id =:user_id ';
        $last_params['args'] = [':user_id'=>$user_id];
        $last_params['fields'] = 'sum(amount) as total';
        $last_info = $this->findOneByWhere('sea_cash_insert',$last_params);
        $amount = isset($last_info['total']) ? $last_info['total'] : 0 ;

        if($amount > 0 ){

            $balance = $info['balance'] - $info['balance']*2 ;
            $add_data['user_id'] = $user_id ;
            $add_data['business_id'] = 0 ;
            $add_data['date'] = date('Y-m-d',strtotime($date) + 86400) ;
            $add_data['timeStamp'] = strtotime($date) + 86401;
            $add_data['amount'] = $balance;
            $add_data['user_level'] = $info['user_level'];
            $add_data['user_root_path'] = $info['user_root_path'];
            $add_data['inviter_user_id'] = $info['inviter_user_id'];
            $add_data['user_type'] = $info['user_type'];
            $add_data['is_super'] = $info['is_super'];
            $add_data['inviter_username'] = $info['inviter_username'];
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');

            $this->baseInsert('sea_cash_insert',$add_data) ;

        }else{
            return false ;
        }

    }

    /**
     * 判断是否开启
     * @param  $date
     * @return boolean
     */
    public function checkStart($date){
        $params['cond'] = 'date <= :date';
        $params['args'] = [':date'=>$date];
        $params['fields'] = 'sum(amount) as total';
        $info = $this->findOneByWhere('sea_cash_insert',$params);
        $total = isset($info['total']) ? $info['total'] : 0;
        if($total >=2000){
            return true ;
        }else{
            return false ;
        }
    }

}
