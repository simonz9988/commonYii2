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
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class ContractProfitController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $contract_no = isset($_GET['contract_no']) ? $_GET['contract_no'] : '' ;

        if($contract_no){
            $params['like_arr']['contract_no'] = $contract_no;
        }
        $searchArr['contract_no'] = $contract_no ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MhHtProfitCalculation() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $list = $model->formatList($list);
        $renderData['list'] =$list;

        $total_info = $model->getTotalInfo($list);
        $renderData['total_info'] = $total_info ;

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
        $id= $this->getParam('id');
        $obj = new MhHtProfitCalculation();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $renderData['is_modify'] = true ;
        $this->loadResource('contact_profit','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionModify(){
        $id= $this->getParam('id');
        $obj = new MhHtProfitCalculation();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $renderData['is_modify'] = true ;
        $this->loadResource('contact_profit','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionView(){
        $id= $this->getParam('id');
        $obj = new MhHtProfitCalculation();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $renderData['is_modify'] = false ;
        $this->loadResource('contact_profit','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $add_data = $_POST;

        $obj = new MhHtProfitCalculation();
        $res = $obj->savePostData($id,$add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhHtProfitCalculation();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
