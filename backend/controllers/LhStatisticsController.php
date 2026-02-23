<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminRolePrivilege;
use backend\models\AdminTotalApiKey;
use backend\models\AdminUserApiKey;
use common\components\ExportFile;
use common\components\GoogleAuthenticator;
use common\components\PHPGangsta_GoogleAuthenticator;
use common\models\AdPosition;
use common\models\Areas;
use common\models\CashIn;
use common\models\CashOut;
use common\models\EmailCode;
use common\models\MhHtMaterialPurchase;
use common\models\MhHtParams;
use common\models\MhHtProfitCalculation;
use common\models\MhHtSales;
use common\models\MhLhDetail;
use common\models\MhLhNote;
use common\models\MhLhProcess;
use common\models\MhLhTotalNote;
use common\models\MhStorageProduct;
use common\models\MhStorageProductTotal;
use common\models\MhStorageSfl;
use common\models\MhStorageSflTotal;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class LhStatisticsController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $lh_no = isset($_GET['lh_no']) ? $_GET['lh_no'] : '' ;

        if($lh_no){
            $params['like_arr']['lh_no'] = $lh_no;
        }
        $searchArr['lh_no'] = $lh_no;

        $process_model = new MhLhProcess() ;
        $process_list = $process_model->getTotalList();
        $renderData['process_list'] = $process_list ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $model = new MhLhDetail();
        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['group_by'] = 'lh_no';


        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $format_info = $model->formatList($list,$process_list);
        $list = $format_info['list'] ;
        $renderData['list'] =$list;

        $total_rows = count($list);
        $renderData['total_rows'] = $total_rows;

        $process_list_total = $format_info['process_list_total'];
        $renderData['process_list_total']  = $process_list_total;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $lh_no= $this->getParam('lh_no');

        $renderData['lh_no'] = $lh_no ;
        $note_obj = new MhLhNote();
        $note  = $note_obj->getLhNote($lh_no) ;
        $renderData['note'] = $note ;
        $this->loadResource('lh_statistics','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $lh_no = $this->postParam('lh_no');
        $note = $this->postParam('note');


        $obj = new MhLhNote();
        $obj->saveNote($lh_no,$note,$this->adminUserInfo);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }



    public function actionDetail(){

        $obj = new MhLhProcess();
        $detail_obj = new MhLhDetail();
        $renderData['info'] = [] ;
        $renderData['lh_no_list'] =$detail_obj->getNoList();
        $renderData['process_list'] = $obj->getListByType('INPUT');
        $this->loadResource('lh_process','actionDetail');
        return $this->render('detail',$renderData) ;
    }

    public function actionSaveDetail(){
        $admin_user_info = $this->adminUserInfo;
        $detail_obj = new MhLhDetail();
        $detail_obj->saveData($admin_user_info,$_POST);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 仓储总表
    public function actionTotal(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $lh_no = isset($_GET['lh_no']) ? $_GET['lh_no'] : '' ;

        if($lh_no){
            $params['like_arr']['lh_no'] = $lh_no;
        }
        $searchArr['lh_no'] = $lh_no;

        $process_model = new MhLhProcess() ;
        $process_list = $process_model->getListByType('INPUT');
        $renderData['process_list'] = $process_list ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $model = new MhLhDetail();
        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['group_by'] = 'year,month';


        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $list = $model->formatTotalList($list,$process_list);
        $renderData['list'] =$list;



        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;



        return $this->render('total',$renderData) ;
    }

    public function actionTotalEdit(){
        $year= $this->getParam('year');
        $month= $this->getParam('month');

        $renderData['year'] = $year ;
        $renderData['month'] = $month ;
        $note_obj = new MhLhTotalNote();
        $note  = $note_obj->getNote($year,$month) ;

        $renderData['note'] = $note ;
        $this->loadResource('lh_statistics','actionTotalEdit');
        return $this->render('total-edit',$renderData) ;
    }

    public function actionTotalSave(){
        $year = $this->postParam('year');
        $month = $this->postParam('month');
        $note = $this->postParam('note');


        $obj = new MhLhTotalNote();
        $obj->saveNote($year,$month,$note,$this->adminUserInfo);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    /**
     *最终结案
     */
    public function actionFinalSave(){
        $lh_no = $this->postParam('lh_no');
        $note_obj = new MhLhNote();
        $admin_user_info = $this->adminUserInfo;
        $note_obj->finalCheck($lh_no,$admin_user_info);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

}
