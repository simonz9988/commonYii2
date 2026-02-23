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
use common\models\MhLhProcess;
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
class LhProcessController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = 1000 ;

        $name = isset($_GET['name']) ? $_GET['name'] : '' ;

        if($name){
            $params['like_arr']['name'] = $name;
        }
        $searchArr['name'] = $name ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' sort ASC ';

        $model = new MhLhProcess() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

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

        $obj = new MhLhProcess();
        $info = $obj->getInfoById($id) ;

        $renderData['info'] = $info ;
        $renderData['is_modify'] = true ;
        $this->loadResource('lh_process','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionModify(){
        $id= $this->getParam('id');

        $obj = new MhLhProcess();
        $info = $obj->getInfoById($id) ;

        $renderData['info'] = $info ;
        $renderData['is_modify'] = true ;
        $this->loadResource('lh_process','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionView(){
        $id= $this->getParam('id');

        $obj = new MhLhProcess();
        $info = $obj->getInfoById($id) ;

        $renderData['info'] = $info ;
        $renderData['is_modify'] = false ;
        $this->loadResource('lh_process','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $add_data = $_POST;

        $obj = new MhLhProcess();
        $res = $obj->savePostData($id,$add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhLhProcess();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

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

}
