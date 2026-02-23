<?php
namespace backend\controllers;

use backend\components\Diff;
use common\models\AftermarketLog;
use common\models\BenefitsLog;
use common\models\LogWjf;
use common\models\MarketLog;
use common\models\TopicLog;
use common\models\UserBlack;
use common\models\UserBlackLog;
use common\models\VoucherLog;
use common\models\WjfLog;
use Yii;
use common\models\AccountLog;
use common\models\OrderLog;
use common\models\GoodsLog;
use common\models\OperateLog;
use common\models\PresellLog;
use common\models\SeckillLog;
use common\models\LotteryLog;
use common\models\ArticleLog;
use common\models\HelpLog ;
use common\models\SurveyLog ;
use common\models\AdLog ;


/**
 * Log controller
 */
class LogOperateController extends BackendController
{
    protected $menu_key = 'shop_menu_log_manage';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [

        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'Kupload' => [
                'class' => 'pjkui\kindeditor\KindEditorAction',
            ]
        ];
    }

    public function actionIndex(){

        // 搜索对应的操作类型
        $model = new OperateLog();
        $action_type_list = $model->returnTypeList();
        $renderData['action_type_list'] = $action_type_list ;
        $page_num = $this->page_rows ;

        $redundancy_id = isset($_GET['redundancy_id']) ? $_GET['redundancy_id'] : '' ;

        if($redundancy_id){
            $params['like_arr']['redundancy_id'] = $redundancy_id;
        }
        $searchArr['redundancy_id'] = $redundancy_id ;

        $action = isset($_GET['action']) ? $_GET['action'] : '' ;

        if($action){
            $params['like_arr']['action'] = $action;
        }
        $searchArr['action'] = $redundancy_id ;

        $model = new OperateLog();
        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';



        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $list = $model->formatList($list,$action_type_list);
        $list = $this->readFileContent($list) ;
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;
        return $this->render('log', $renderData);
    }

    /**
     * 数据比较
     */
    public function actionCompare() {

        $this->layout = 'empty';

        $ids   = $this->getParam('ids');
        $table_name = 'sea_operate_log';


        // 分割成数组
        $id_data = explode(',', $ids);

        $log_model = new OperateLog() ;
        // 查询数据
        $log_info = $log_model->getLogCompareInfoByIdAll($id_data,$table_name);
        // 读取文件日志
        $log_info = $this->readFileContent($log_info);

        $old_content = '';
        $new_content = '';

        if($log_info){
            $i = 1;
            foreach($log_info as $row){
                if($i == 1){
                    $old_content = $row['old_content'];
                    $new_content = $row['new_content'];
                }else if($i == 2){
                    $old_content = $row['new_content'];
                }
                $i++;
            }
        }

        $compare_info = "";
        if($log_info){
            $diff_component = new Diff();
            // 比对
            $compare_info = $diff_component->compare(jsonFormat($old_content), jsonFormat($new_content));

            // 格式化
            $compare_info = $diff_component->toTable($compare_info);

            // 解码
            $compare_info = decodeUnicode($compare_info);
        }

        return $this->render('compare', ['compare_info' => $compare_info]);
    }

    /**
     * 读取文件日志内容
     */
    private function readFileContent($list) {

        // 读取文件日志
        foreach($list as &$row){
            if(!empty($row['file_path'])){
                // 屏蔽错误
                $contents = @file_get_contents(Yii::getAlias('@backend').$row['file_path']);
                if($contents){
                    $contents = json_decode($contents, true);
                    $row['old_content'] = json_encode($contents['old_content']);
                    $row['new_content'] = json_encode($contents['new_content']);
                }else{
                    $row['old_content'] = '';
                    $row['new_content'] = '';
                }

            }
        }

        return $list;
    }
}
