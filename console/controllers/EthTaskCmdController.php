<?php
namespace console\controllers;

use common\components\Jingtai;
use common\components\Kaijiang;
use common\components\Season;
use common\components\SetUserRedis;
use common\components\SuperPlayer;
use common\components\Tuandui;
use common\components\Tuiguang;
use common\models\DayBalance;
use common\models\Zhitui;

/**
 * CommonCmd controller
* Note: set global max_allowed_packet=10000000000; mysql需要设置
*/

class EthTaskCmdController extends CmdBaseController
{

    public $start_date = '';
    public $end_date = '';

    /**
     * @param \yii\base\Action $action
     * @return string
     */
    public function beforeAction($action)
    {

        //脚本检测
        $shell = $this->getShellAction();
        $this->checkShell($shell);
        return parent::beforeAction($action);
    }


    public function init()
    {
        parent::init();
        $this->start_date = '2019-06-01';
        $this->end_date = '2019-06-06';
        //$this->end_date = date('Y-m-d',time()-86400);
    }

    /**
     * 初始化用户每日的账户余额
     * Note: 一开始执行的时候是分个执行 等到全部初始化完成时候，则改为每分钟执行一次
     * Tag: DayBalance
     *  php  /www/wwwroot/command/yii /eth-task-cmd/init-balance
     */
    public function actionInitBalance(){

        // 第一次初始化，需要更新每日账户余额信息
        $start_date = $this->start_date;
        $end_date = $this->end_date;

        echo date('Y-m-d H:i:s');

        $model = new DayBalance();
        $model->addDayRecord($start_date,$end_date); // 以2W个用户为基数，一天的执行时间需要五分钟

        echo date('Y-m-d H:i:s');
    }

    /**
     * 初始化直推和团队的业绩总和
     * Tag: ZhituiAndTeam
     * php  /www/wwwroot/command/yii /eth-task-cmd/init-zhitui-and-team
     */
    public function actionInitZhituiAndTeam(){

        echo date('Y-m-d H:i:s');

        $start_date = $this->start_date;
        $end_date = $this->end_date;

        $model = new Zhitui();
        $model->initZhiTuiAndTuandui($start_date,$end_date) ;

        echo date('Y-m-d H:i:s');
    }

    /**
     * 新增静态收益
     * php  /www/wwwroot/command/yii /eth-task-cmd/add-jtsy
     */
    public function actionAddJtsy(){

        echo date('Y-m-d H:i:s');
        $model = new Jingtai();

        $start_date = $this->start_date;

        $end_date = $this->end_date;

        $model->addJtsy($start_date,$end_date);

        echo date('Y-m-d H:i:s');
    }

    /**
     * 新增团队奖励
     */
    public function actionAddTgjl(){

        echo date('Y-m-d H:i:s');

        $model = new Tuiguang();

        $start_date = $this->start_date;

        $end_date = $this->end_date;

        $model->addTgjl($start_date,$end_date) ;

        echo date('Y-m-d H:i:s');
    }

    /**
     * 新增团队奖励
     */
    public function actionAddTdjl(){

        echo date('Y-m-d H:i:s');

        $model = new Tuandui();

        $start_date = $this->start_date;

        $end_date = $this->end_date;

        $model->addTdjl($start_date,$end_date) ;

        echo date('Y-m-d H:i:s');
    }

    // 超级玩家奖励 必须在cash_insert表处理完之后才能进行
    public function actionSuperPlayer(){
        echo date('Y-m-d H:i:s');
        $super_player = new SuperPlayer();
        $super_player->send($this->start_date,$this->end_date);
        echo date('Y-m-d H:i:s');
    }

    //  开奖 节点奖和24小时倒计时奖
    public function actionKaijiang(){
        echo date('Y-m-d H:i:s');

        $component = new Kaijiang();
        $component->doSend($this->start_date,$this->end_date);
        echo date('Y-m-d H:i:s');
    }

    // 阵营奖励 需要将所有的奖励都奖励结束了，才可以进行阵营奖励
    public function actionSeason(){

        echo date('Y-m-d H:i:s') ;
        // 初始化必须要从第一次开始充值计算阵营
        // 赛季的间歇期的直推量
        $season_component = new Season() ;
        $season_component->sendEth($this->start_date ,$this->end_date) ;

        echo date('Y-m-d H:i:s') ;
    }

}
