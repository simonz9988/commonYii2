<?php
namespace console\controllers;

use common\components\EthScan;
use common\components\EthWallet;
use common\components\PrivateUser;
use common\models\BaseModel;
use common\models\CashInsert;
use common\models\Member;

/**
 * CommonCmd controller
 */

/*
 * 初始化的时候需要清空 sea_day_balance sea_cash_insert
 * sea_earn_info  sea_zhitui  sea_gongzhen sea_count_down sea_dajiang
 * 同时需要清空redis  Check:Jt:
 *  DEL Check:Jt:690:5 Check:Jt:121:5 Check:Jt:441:5 Check:Jt:884:5 Check:Jt:450:5 Check:Jt:300:5 Check:Jt:252:5 Check:Jt:662:5 Check:Jt:843:5 Check:Jt:264:5 Check:Jt:962:5 Check:Jt:644:5 Check:Jt:903:5 C
 *
 * Kaijiang:total_insert
 * 需要设置mysql  max_allowed_packet 为10M 10*1024*1024
 *
 * 需要设置当前总入金量的redis 通过总量来判断当前是第几赛季
 * */
class EthTestCmdController extends CmdBaseController
{

    public $start_date = '';
    public $end_date = '';

    public function init()
    {
        parent::init();
        //$this->start_date = '2019-09-02';
        $this->start_date = '2019-06-01';
        //$this->start_date = '2019-04-11';
        $this->end_date = '2019-06-15';
        //$this->end_date = date('Y-m-d',time()-86400);
        //$this->end_date = '2019-09-02';

    }
    public function actionTest1(){

        $i = 1 ;
        $add_data['user_id'] = 800088 ;
        $add_data['business_id'] = 0 ;
        $add_data['date'] = $this->start_date ;
        $add_data['amount'] = 50 ;
        $add_data['user_level'] = 0 ;
        $add_data['user_root_path'] = "--0--" ;
        $add_data['inviter_user_id'] = 0 ;
        $add_data['inviter_username'] = '' ;
        $add_data['user_type'] = 'PERSON' ;
        $add_data['is_super'] = 'N' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        $model = new CashInsert();
        $model->baseInsert($model::tableName(),$add_data,'db');

        $m = 800095 ;

        $son = [
            10,4,4,4,2
        ];

        $son_num = count($son) ;

        $ext  = (strtotime($this->end_date) - strtotime($this->start_date) ) /86400 + 1;

        //第一层1推20、第二层1推2、第三层1推4、第四层1推8、这4层全部按照50个算，第5层开始打乱，1推几都可以，10-50个整数倍随机。看一下
        for ($j=0 ;$j<=$ext;$j++){

            if($j < $son_num){
                $params['cond'] = 'user_level=:user_level';
                $params['args'] = [':user_level'=>$j];
                $list = $model->findAllByWhere($model::tableName(),$params,$model::getDb());
            }else{
                $params['cond'] = 'user_level=:user_level';
                $params['args'] = [':user_level'=>$j];
                $params['limit'] = 93;
                $params['orderby'] = "  RAND()  " ;
                $list = $model->findAllByWhere($model::tableName(),$params,$model::getDb());
            }

            foreach($list as $v) {

                /*
                 * 第一层1推20、第二层1推2、第三层1推三、第三层1推4、第四层1推8、第五层1推8，
                 * 这5层全部按照50个算，
                 * 第六层开始打乱，1推几都可以，10-50个整数倍随机
                 */
                $loop = $j < $son_num ? $son[$j] : 1 ;
                for ($x = 0; $x < $loop; $x++){
                    $add_data1['user_id'] = $m;
                    $add_data1['business_id'] = 0;
                    $add_data1['date'] = date('Y-m-d', strtotime($v['date']) + 86400);
                    $add_data1['timeStamp'] = strtotime($add_data1['date']) + mt_rand(10,86399);
                    $add_data1['amount'] = $j < $son_num ?50:rand(10,50);
                    $add_data1['user_level'] = $v['user_level'] + 1;
                    $add_data1['user_root_path'] = $v['user_root_path'] . $v['user_id'] . "--";
                    $add_data1['inviter_user_id'] = $v['user_id'];
                    $add_data1['inviter_username'] = $v['user_id'];
                    $add_data1['user_type'] = $v['PERSON'];
                    $add_data1['is_super'] = 'N';
                    $add_data1['create_time'] = date('Y-m-d H:i:s');
                    $add_data1['modify_time'] = date('Y-m-d H:i:s');

                    $model->baseInsert($model::tableName(), $add_data1, 'db');

                    //同时插入用户表信息
                    $user_add_data['id'] = $m ;
                    $user_add_data['username'] = $m ;
                    $user_add_data['type'] = 'PERSON' ;
                    $user_add_data['email'] = $m.'@qq.com' ;
                    $user_add_data['audit_status'] = 'SUCCESS' ;
                    $user_add_data['reg_from'] = 'ADMIN' ;
                    $user_add_data['password'] = md5(111111) ;
                    $user_add_data['user_level'] = $v['user_level'] + 1;
                    $user_add_data['user_root_path'] = $v['user_root_path'] . $v['user_id'] . "--";
                    $user_add_data['inviter_user_id'] = $v['user_id'];
                    $user_add_data['inviter_username'] = $v['user_id'];
                    $user_add_data['is_super'] = 'N';
                    $user_add_data['create_time'] = date('Y-m-d H:i:s');
                    $user_add_data['modify_time'] = date('Y-m-d H:i:s');
                    $model->baseInsert('sea_user', $user_add_data, 'db');
                    $m++;
                }


            }

        }
    }

    public function actionTest3(){
        $components = new PrivateUser() ;
        $model = new Member();
        $total_private_list = $components->returnPrivateAddress();
        $address_list = array_keys($total_private_list);
        if(!in_array(11,$address_list)){
            $eth_address_num = array_rand($address_list,1);
            $eth_address = $address_list[$eth_address_num] ;
            $info = $model->getUserInfoByAddress($eth_address);
        }

        if(!$info){
            return true;
        }
        var_dump($info); exit;
    }

    public function actionTest4(){

        $str = 's:4:"null";';
        $arr = unserialize($str);
        var_dump($arr);
    }


    public function actionTest5(){
        $model = new BaseModel();
        $total_num = $model->findCountByWhere('sea_address_value',[],$model::getDb());
        $total_page = ceil($total_num/20);

        $total = 0  ;
        for($i =0 ;$i<$total_page;$i++) {

            $params['page']['curr_page'] = $i + 1;
            $params['page']['page_num'] = 20;
            $list = $model->findAllByWhere('sea_address_value', $params, $model::getDb());
            $address = [] ;
            foreach($list as $v){
                $address[] = $v['eth_address'] ;
            }

            $c = new EthScan();
            $res = $c->getListBalance($address) ;
            $total = $total + $res ;
            echo $res.'=====';

        }

        echo '**************'.$total.'**************' ;
    }

    public function actionTest6(){


        $url = "https://blockchair.com/bitcoin/address/158g9sdvM9kJEQ78zu3apCMuLsoFFC3nJd";
        $content = curlGo($url);
        var_dump($content);exit;
    }
}
