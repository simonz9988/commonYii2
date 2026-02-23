<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use common\components\SetUserRedis;
use common\components\Tuandui;
use Yii;

/**
 * This is the model class for table "sea_zhitui".
*/
class Zhitui extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_zhitui';
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
     * 获取指定时间段内用户的信息
     * @param $start_date
     * @param $end_date
     * @return mixed
     */
    public function getListByDate($date){

        $params['cond'] = 'date = :start_date ';
        $params['args'] = [':start_date'=>$date];
        return $this->findAllByWhere(self::tableName(),$params,self::getDb());
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

            $this->dealByDate($end_date) ;
            $redis_component->setTagByType('ZhituiAndTeam',$end_date) ;

        }else{

            for($i = 0 ;$i<=$ext ;$i++ ){

                $date =date('Y-m-d' ,(strtotime($start_date) + $i*86400)) ;

                $this->dealByDate($date) ;

                $redis_component->setTagByType('ZhituiAndTeam',$date) ;

            }
        }

        return true ;

    }

    /**
     * 按照日期处理数据
     * @param $date
     * @return bool
     */
    public function dealByDate($date)
    {

        // 判断redis是否为
        $redis_component = new SetUserRedis();
        if ($redis_component->getTagByType('ZhituiAndTeam', $date)) {
            return true;
        }

        $tuandui_component = new Tuandui();

        // 查询前一天的团队金额
        $prev_date = date('Y-m-d', (strtotime($date) - 86400));

        $prev_params['cond'] = 'date=:date';
        $prev_params['args'] = [':date' => $prev_date];
        $prev_count = $this->findCountByWhere(self::tableName(), $prev_params);
        $prev_list = [];
        $prev_total_page = ceil($prev_count / LOOP_LIMIT_NUM);
        for ($i = 0; $i < $prev_total_page; $i++) {
            $prev_params['page']['curr_page'] = $i + 1;
            $prev_params['page']['page_num'] = LOOP_LIMIT_NUM;
            $list = $this->findAllByWhere(self::tableName(), $prev_params);
            foreach ($list as $v) {
                $prev_list[$v['user_id']] = $v;
            }
        }

        // 查询当天插入的总量
        $params['cond'] = 'date =:date AND amount > 0 ';
        $params['args'] = [':date' => $date];
        $total_num = $this->findCountByWhere('sea_cash_insert', $params, self::getDb());
        $total_page = ceil($total_num / LOOP_LIMIT_NUM);
        for ($i = 0; $i < $total_page; $i++) {

            $params['page']['curr_page'] = $i + 1;
            $params['page']['page_num'] = LOOP_LIMIT_NUM;

            $list = $this->findAllByWhere('sea_cash_insert', $params, self::getDb());

            foreach ($list as $v) {

                $user_day_balance = $redis_component->getDayBalanceByDate($v['user_id'],$date) ;
                $prev_list[$v['user_id']]['balance'] = $user_day_balance;
                $prev_list[$v['user_id']]['user_level'] = $v['user_level'];
                $prev_list[$v['user_id']]['user_id'] = $v['user_id'];
                $prev_list[$v['user_id']]['inviter_user_id'] = $v['inviter_user_id'];

                // 设置用户列表的直推额
                if($v['inviter_user_id']){

                    $invite_user_info = $redis_component->getUserInfo($v['inviter_user_id']) ;
                    $prev_list[$v['inviter_user_id']]['balance'] = $redis_component->getDayBalanceByDate($v['inviter_user_id'],$date) ;
                    $prev_list[$v['inviter_user_id']]['user_id'] = $v['inviter_user_id'];
                    $prev_list[$v['inviter_user_id']]['user_level'] = $invite_user_info['user_level'];
                    $prev_list[$v['inviter_user_id']]['inviter_user_id'] = $invite_user_info['inviter_user_id'];
                    $prev_list[$v['inviter_user_id']]['total'] = isset($prev_list[$v['inviter_user_id']]['total']) ? ($prev_list[$v['inviter_user_id']]['total'] + $v['amount'] ) : $v['amount'];

                }

                // 设置团队的数量 都是按天进行运算，所以不需要加上天的限制
                $user_root_path_arr = explode('--', $v['user_root_path']);
                foreach ($user_root_path_arr as $root_user) {

                    if (!$root_user) {
                        continue;
                    }

                    $invite_user_info = $redis_component->getUserInfo($root_user) ;
                    $prev_list[$root_user]['balance'] = $redis_component->getDayBalanceByDate($root_user,$date) ;
                    $prev_list[$root_user]['user_id'] = $root_user;
                    $prev_list[$root_user]['user_level'] = $invite_user_info['user_level'];
                    $prev_list[$root_user]['inviter_user_id'] = $invite_user_info['inviter_user_id'];
                    $prev_list[$root_user]['tuandui'] = isset($prev_list[$root_user]['tuandui']) ? ($prev_list[$root_user]['tuandui'] + $v['amount'] ) : $v['amount'];
                }
            }
        }

        $trunk_list = array_chunk($prev_list, LOOP_LIMIT_NUM);

        foreach ($trunk_list as $son_list){

            $batch_data = [];
            foreach ($son_list as $v) {


                $zhitui = isset($v['total']) ? $v['total'] : 0;
                $team_total = isset($v['tuandui']) ? $v['tuandui'] : 0;
                $temp_data = [];


                $user_info = $redis_component->getUserInfo($v['user_id']) ;

                $temp_data[] = $v['user_id'];
                $temp_data[] = $date;
                $temp_data[] = isset($v['balance']) ? $v['balance'] : 0;
                $temp_data[] = $zhitui;
                $temp_data[] = $team_total;
                $temp_data[] = $tuandui_component->getLevel($zhitui, $team_total);;
                $temp_data[] = $user_info['user_level'];
                $temp_data[] = $user_info['user_root_path'];
                $temp_data[] = $user_info['inviter_user_id'];
                $temp_data[] = $user_info['inviter_username'];
                $temp_data[] = $user_info['user_type'];
                $temp_data[] = $user_info['is_super'];
                $temp_data[] = date('Y-m-d H:i:s');
                $temp_data[] = date('Y-m-d H:i:s');

                $batch_data[] = $temp_data;


            }

            $fields = ['user_id', 'date', 'balance', 'total', 'tuandui', 'tuandui_level', 'user_level','user_root_path','inviter_user_id','inviter_username','user_type','is_super', 'create_time', 'modify_time'];
            $this->baseBatchInsert('sea_zhitui', $fields, $batch_data, 'db');
        }

        return true ;
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
        $list  = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
}
