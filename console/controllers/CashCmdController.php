<?php
namespace console\controllers;
use common\components\CommonLogger;
use common\components\EthScan;
use common\components\SpotApi;
use common\models\CashIn;
use common\models\OkexCoinTicker;
use common\models\OkexSpotOrder;
use common\models\PushTask;
use common\models\SiteConfig;
use common\models\TxList;
use common\models\UserPlatform;
use common\models\UserPlatformEarn;
use common\models\UserPointRecord;
use yii\db\Expression;
use yii\web\User;


/**
 * CommonCmd controller
 */
class CashCmdController extends CmdBaseController
{

    public $start_date = '';
    public $end_date = '';

    public function init()
    {


    }

    // 处理新增用户 编辑用户 删除用户
    public function actionSendOrder(){

        $task_obj = new PushTask();
        $task_list = $task_obj->getListByType('CASH_PAY_SUCCESS','NOPUSH',1000);


        if(!$task_list){
            echo 'Empty';exit;
        }


        foreach($task_list as $v){
            $post_data = [];
            $cash_in_obj = new CashIn();
            $cash_in_info = $cash_in_obj->getInfoById($v['business_id']);

            if($cash_in_info){
                $url =  $cash_in_info['callback_url'];
                $admin_params['cond'] = 'id=:id';
                $admin_params['args'] = [':id'=>$cash_in_info['admin_user_id']];
                $admin_user_info = $cash_in_obj->findOneByWhere('sea_admin',$admin_params,$cash_in_obj::getDb());

                $appKey = $admin_user_info['mark_key'];
                $secret_key= $admin_user_info['secret_key'];

                $post_data['amount'] = ($cash_in_info['amount']) ;
                $post_data['appKey'] = $appKey ;
                $post_data['coinType'] = $cash_in_info['coin_type'] ;
                $post_data['orderNo'] = $cash_in_info['order_no'] ;
                $post_data['payStatus'] = $cash_in_info['pay_status'] ;
                //$time = floor(microtime(true) * 1000);
                //$post_data['timestamp'] = $time ;

                $sign_str = ($appKey.$this->arrayToString($post_data).$secret_key);

                 $post_data['sign'] = md5($sign_str) ;
                $res = curlGo($url,$post_data);

                if($res =='success' || $v['send_times'] >=10){
                    $update_data['status'] ='PUSHED';
                    $update_data['modify_time'] =date('Y-m-d H:i:s');
                    $task_obj->baseUpdate($task_obj::tableName(),$update_data,'business_id=:business_id',[':business_id'=>$v['business_id']]);
                }else{
                    $update_data['modify_time'] =date('Y-m-d H:i:s');
                    $update_data['send_times'] =new Expression('send_times + 1');
                    $task_obj->baseUpdate($task_obj::tableName(),$update_data,'business_id=:business_id',[':business_id'=>$v['business_id']]);

                }
            }else{
                $update_data['status'] ='PUSHED';
                $update_data['modify_time'] =date('Y-m-d H:i:s');
                $task_obj->baseUpdate($task_obj::tableName(),$update_data,'id=:id',[':id'=>$v['id']]);
            }
        }

    }

    private function  arrayToString($params)
    {
        $arg = "";
        foreach($params as $key => $val){
            $arg.=$key."=".$val;
        }

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){
            $arg = stripslashes($arg);
        }

        return $arg;
    }

}
