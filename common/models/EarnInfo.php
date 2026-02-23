<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_earn_info".
*/
class EarnInfo extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_earn_info';
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
     * 获取阵营可以得到的共振奖池的比例
     * @param $type
     * @return array
     */
    private function getZhenYinPercent($type){

        $arr =  [
            'CAINIAO' =>9,
            'CHUJI' =>10,
            'ZHONGJI' =>12,
            'GAOJI' =>13,
            'JINGYING' =>15,
            'BOSS' =>18,
            'CHUANSHUO' =>23,
        ];

        return $arr[$type] ;
    }

    /**
     * 通过直推业绩获取对应的阵营的业绩
     * @param float $zhitui 获取直推金额
     * @return string
     */
    public function getZhenYinByZhiTui($zhitui){

        /*
        菜鸟级  无任何直推业绩        占赛季共振奖池的9%
        初级    直推业绩达到30ETH    占赛季共振奖池的10%
        中级    直推业绩达到60ETH    占赛季共振奖池的12%
        高级    直推业绩达到100ETH    占赛季共振奖池的13%
        精英级  直推业绩达到200ETH    占赛季共振奖池的15%
        Boss级  直推业绩达到300ETH   占赛季共振奖池的18%
        传说级  直推业绩达到500ETH    占赛季共振奖池的23%
        */

        if($zhitui < 30){
            return 'CAINIAO';
        }else if($zhitui >=30 &&  $zhitui <  60){
            return 'CHUJI';
        }else if($zhitui >=60 &&  $zhitui <  100){
            return  'ZHONGJI' ;
        }else if($zhitui >=100 &&  $zhitui <  200){
            return 'GAOJI' ;
        }else if($zhitui >=200 &&  $zhitui <  300){
            return 'JINGYING' ;
        }else if($zhitui >=300 &&  $zhitui <  500){
            return 'BOSS';
        }else{
            return 'CHUANSHUO';
        }
    }

    public function getZhenYinRange($zhen_yin){

        if($zhen_yin == 'CAINIAO'){
            $min = 0 ;
            $max = 30 ;
        }else if($zhen_yin == 'CHUJI'){
            $min = 30 ;
            $max = 60 ;
        }else if($zhen_yin == 'ZHONGJI'){
            $min = 60 ;
            $max = 100 ;
        }else if($zhen_yin == 'GAOJI'){
            $min = 100 ;
            $max = 200 ;
        }else if($zhen_yin == 'JINGYING'){
            $min = 200 ;
            $max = 300 ;
        }else if($zhen_yin == 'BOSS'){
            $min = 300 ;
            $max = 500 ;
        }else if($zhen_yin == 'CHUANSHUO'){
            $min = 500 ;
            $max = 99999999999 ;
        }

        return compact('min','max');
    }

    /**
     * 判断是否重复插入
     * @param $user_id
     * @param $type
     * @param $date
     * @retun boolean
     */
    public function checkRepeatInsert($user_id,$type,$date){
        $params['cond'] = 'user_id =:user_id AND type=:type AND day=:date';
        $params['args'] = [':user_id'=>$user_id ,':type'=>$type,':date'=>$date];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info? true:false ;
    }

    /**
     * 添加共振佣金记录信息
     * @param $start_date
     * @param $end_date
     */
    public function addGongZhen($start_date,$end_date){

        $max =  ( strtotime($end_date) - strtotime($start_date) ) / 86400 ;

        if(!$max){
            $this->addGongZhenByDate($start_date) ;
        }else{

            for($i = 0 ; $i < $max ;$i++){
                $current_date = date('Y-m-d', (strtotime($start_date) + $i*86400) ) ;
                $this->addGongZhenByDate($current_date) ;

            }
        }

    }

    /**
     * 根据当前日期添加共振信息
     * @param $current_date
     * @return bool
     */
    public function addGongZhenByDate($current_date){

        //查看是否允许共振
        $gongzhen_model = new GongZhen();

        $cash_insert_model = new CashInsert() ;

        $day_balance_model = new DayBalance();

        // 查询共振奖金占用入金比例
        $site_config_model = new SiteConfig();
        $gongzhen_jiangjin_zhanyong_rujin_percent = $site_config_model->getByKey('gongzhen_jiangjin_zhanyong_rujin_percent');

        $gongzhen = $gongzhen_model->checkInAllowed($current_date) ;


        if(!$gongzhen){
            return false ;
        }

        $gongzhen_start_time = $gongzhen['date'] ;
        // 查询当前区间段的所有入金金额
        $total_balance = $cash_insert_model->getTotalInsertFromEndTime($current_date,$gongzhen_start_time) ;

        // 用户发放奖金的总金额
        $total_balance = ($total_balance*$gongzhen_jiangjin_zhanyong_rujin_percent) /100 ;


        // 获取当前时间段内所有用户的信息
        $params['cond'] = 'date=:date';
        $params['args'] = [':date'=>$current_date];
        $total_num =  $this->findCountByWhere('sea_zhitui',$params,self::getDb());
        $total_page = ceil($total_num/LOOP_LIMIT_NUM);

        for($i=0;$i<$total_page;$i++){

            $params['page']['curr_page'] = $i+1 ;
            $params['page']['page_num'] = LOOP_LIMIT_NUM ;
            $balance_list = $this->findAllByWhere('sea_zhitui',$params,self::getDb());

            if(!$balance_list){
                return false ;
            }

            // 获取阵营对应的总人数
            $zhenyin_total_user_num = [] ;

            // 批量插入数值
            $batch_data = [];

            // 查询当前用户的级别
            foreach($balance_list as $balance_v){

                #判断是否重复插入
                #TODO

                $user_zhenyin = $this->getZhenYinByZhiTui($balance_v['total']) ;
                $zhenyin_percent = $this->getZhenYinPercent($user_zhenyin);

                if(isset($zhenyin_total_user_num[$user_zhenyin])){
                    $zhenyin_total_num = $zhenyin_total_user_num[$user_zhenyin] ;
                }else{
                    //查询阵营对应的总人数
                    $zhengying_range = $this->getZhenYinRange($user_zhenyin) ;
                    $zy_params['cond'] = 'date=:date AND total >= :min AND total <:max';
                    $zy_params['args'] = [':date'=>$current_date,':min'=>$zhengying_range['min'],':max'=>$zhengying_range['max']];
                    $zy_params['fields'] = 'id';
                    $zy_list = $this->findAllByWhere('sea_zhitui',$zy_params,self::getDb());
                    $zhenyin_total_num = count($zy_list);
                    $zhenyin_total_user_num[$user_zhenyin] = $zhenyin_total_num ;
                }
                $temp_add = [] ;
                //新增

                $total = checkMaxEarn(($zhenyin_percent*$total_balance )/(100*100*$zhenyin_total_num),$balance_v['user_id'],$balance_v['date']);
                if($total > 0){

                    $temp_add[] = $balance_v['user_id'] ;
                    $temp_add[] = $balance_v['user_level'] ;
                    $temp_add[] = $balance_v['user_root_path'] ;
                    $temp_add[] = $balance_v['date'] ;
                    $temp_add[] = 'GONGZHEN_EARN' ;
                    $temp_add[] = $total;
                    $temp_add[] = 'N';
                    $temp_add[] = date('Y-m-d H:i:s');
                    $temp_add[] = date('Y-m-d H:i:s');

                    $batch_data[] = $temp_add ;
                }

                //$this->baseInsert(self::tableName(),$add_data,'db') ;
            }

            if($batch_data){
                $fields = ['user_id','user_level','user_root_path','day','type','total','is_tx','create_time','modify_time'];
                $this->baseBatchInsert(self::tableName(),$fields,$batch_data,'db');
            }

        }

    }

    /**
     * 获取当前日期包含当天的用金额总额
     * @param $date
     * @return mixed
     */
    public function getTotalBeforeDate($date){

        $params['cond'] = ' date <= :date ';
        $params['args'] = [':date'=>$date];
        $params[] = ' sum(total) as total ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb()) ;

        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    public function checkSuperExistsByDate($user_id,$date,$amount){

        $params['cond'] =' user_id=:user_id AND day=:day AND type =:type ';
        $params['args'] = [":user_id"=>$user_id,':day'=>$date,':type'=>'SUPERPLAYER'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb()) ;
        if(!$info){
            return false ;
        }

        $update_data['total'] = $info['total'] +$amount ;
        $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]) ;
        return true ;


    }
}
