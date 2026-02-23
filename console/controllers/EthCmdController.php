<?php
namespace console\controllers;
use common\components\AutoInsert;
use common\components\EthScan;
use common\components\EthWallet;
use common\components\Jingtai;
use common\components\Kaijiang;
use common\components\PrivateUser;
use common\components\Season;
use common\components\SuperPlayer;
use common\components\Tuandui;
use common\components\TuijianWanJia;
use common\models\CashInsert;
use common\models\DayBalance;
use common\models\EarnInfo;
use common\models\PushTask;
use common\models\TxList;

/**
 * CommonCmd controller
 */
class EthCmdController extends CmdBaseController
{

    public $start_date = '';
    public $end_date = '';

    public function init()
    {
        parent::init();
        //$this->start_date = '2019-09-02';
        $this->start_date = '2019-06-01';
        //$this->start_date = '2019-04-11';
        $this->end_date = '2019-06-30';
        //$this->end_date = date('Y-m-d',time()-86400);
        //$this->end_date = '2019-09-02';

    }

    // 同步记录 每分钟执行一次
    public function actionTxList(){
        $model = new EthScan();
        $from_key = ETH_FROM_KEY;
        $list = $model->getTxList($from_key);

        $tx_model  = new TxList();
        $tx_model->addRecord($list,$from_key);
    }

    // 针对转账记录 新增到转账记录中  每分钟执行一次
    public function actionAddCashRecordFromTrans(){
        $push_task_model = new PushTask() ;
        $push_task_model->dealTrans();
    }

    // 向task中指定的用户推送日志信息 此进程非常重要，每次只能起一个，所以一分钟只能处理10个
    public function actionSendTokenAndEth(){
        $push_task_model = new PushTask() ;
        $push_task_model->dealSendTokenAndEth();
    }

    // 创建私有用户，此脚本为一次性脚本
    public function actionCreatePrivateUser(){
        $model = new PrivateUser();
        $model->createUserByList();
    }

    // 创建倒计时24小时自动插入的用户
    public function actionCreateAutoInsertUser(){
        $model = new PrivateUser();
        $model->createAutoInsertUserByList();
    }

    // 自动插入小时倒计时的数据
    public function actionAutoInsert24(){
        $component = new AutoInsert() ;
        $component->doRun() ;
    }

    //给alita发送token
    public function actionSendToAlita(){
        $push_task_model = new PushTask() ;
        $push_task_model->sendEthToAlita();
    }



}
