<?php
namespace console\controllers;

//require_once ROOT_PATH.'/../../common/components/XunSearch/lib/XS.php';
use common\components\HuobiLib;
use common\components\SpotApi;
use common\models\CoinPrice;
use common\models\PushTask;
use common\models\RobotCashOut;
use common\models\RobotCoinPlatformInfo;
use common\models\UserPlatformKey;
use common\models\UserWallet;
use okv3\AccountApi;
use trade\models\RobotDistributeModel;
use Yii ;

/**
 * Cmd controller
 */
class TradeController extends CmdBaseController
{

    // 同步所使用的地址对应的账户的余额信息 每分钟同步一次
    public function actionSyncBalance(){
        $action = $this->getShellAction();
        $this->checkShell($action) ;
        $wallet_obj = new UserWallet();
        $wallet_obj->syncBalanceFromRobot();
        echo 'success';
    }

    // 同步okex所有币种价格信息
    public function actionSyncPriceOkex(){
        $action = $this->getShellAction();
        $this->checkShell($action) ;
        $obj = new RobotCoinPlatformInfo();
        $obj->syncPrice('OKEX');
    }

    /**
     * 删除价格快照信息
     */
    public function actionDelPriceOkex(){
        $action = $this->getShellAction();
        $this->checkShell($action) ;
        $obj = new RobotCoinPlatformInfo();
        $obj->delPrice('OKEX');
    }

    // 同步火币所有币种价格信息
    public function actionSyncPriceHuobi(){
        $action = $this->getShellAction();
        $this->checkShell($action) ;

        $obj = new RobotCoinPlatformInfo();
        $obj->syncPrice('HUOBI');

    }

    public function actionSyncPrice(){
        $coin_price_obj = new CoinPrice();
        $price = $coin_price_obj->getCurrentPrice() ;
        $coin_price_obj->addPriceRecord('SCA',$price);
    }

    /**
     * 大派送计划任务 CLI模式运行
     * @params null
     * @return mixed
     */
    public function actionTask(){
        // 开启事务
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try{
            $distribute_res = new RobotDistributeModel();
            $distribute_ret = $distribute_res->guide();
            if($distribute_ret === true){
                $transaction->commit();
                exit('success');
            }else{
                exit($distribute_ret);
            }
        }catch(\Exception $e){
//			print_r($e);
            $transaction->rollBack();
            return false;
        }
    }


    /**
     * tibi
     */
    public function actionDealCashOut(){

        $config=[
            "apiKey"=>"49d907a5-210b-4e56-b71c-ec94c8192f2a",
            "apiSecret"=>"FF111BE50BC3DC37CB560B79C6CFDAC5",
            "passphrase"=>"a12345678",
        ];
        $trade_password = "840824" ;
        debug($config,1);
        $obj = new SpotApi($config);
        $coin = "USDT";
        $fee_list = $obj -> getWithdrawalFee($coin);
        $fee = 8 ;
        foreach($fee_list as $v){
            if($v['currency'] == 'USDT-ERC20'){
                $fee = $v['min_fee'] ;
                //$fee = $v['max_fee'] ;
            }
        }

        $task_obj = new PushTask();
        $task_list = $task_obj->getListByType('USDT_CASH_OUT','NOPUSH','100');
        if(!$task_list){
            echo  'empty';
            exit;
        }

        $cash_out_obj = new RobotCashOut();
        foreach($task_list as $v){
            $cash_out_info = $cash_out_obj->getInfoById($v['business_id']);
            if(!$cash_out_info || $cash_out_info['total'] <2){
                $task_obj->baseUpdate($task_obj::tableName(),['status'=>"PUSHED"],'id=:id',[':id'=>$v['id']]);
                continue ;
            }


            var_dump($fee);exit;
            // 提币
            $res = $obj -> withdrawal($coin,$cash_out_info['total'],"4",$cash_out_info['address'],$trade_password,$fee);
            var_dump($res);exit;
        }
    }

}
