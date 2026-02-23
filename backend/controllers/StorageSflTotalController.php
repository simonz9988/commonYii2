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
class StorageSflTotalController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $type = isset($_GET['type']) ? $_GET['type'] : '' ;

        if($type){
            $params['like_arr']['type'] = $type;
        }
        $searchArr['type'] = $type ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['group_by'] = 'from_date_timestamp,type';
        $params['order_by'] = ' id desc ';

        $model = new MhStorageSfl() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $list = $model->formatTotalList($list);
        $renderData['list'] =$list;
        $renderData['total_info'] = $model->getTotalTotalInfoByList($list);

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;
        $params_obj = new MhHtParams();
        $renderData['type_list'] = $params_obj->getDetailListByKey('XIAOSHOUDAIGONG');
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $type_id= $this->getParam('type_id');
        $from_date_timestamp= $this->getParam('from_date_timestamp');
        $obj = new MhStorageSflTotal();
        $info = $obj->getInfoByFromDateAndType($from_date_timestamp,$type_id) ;
        $renderData['type_id'] = $type_id ;
        $renderData['from_date'] = date('Y/m/d',$from_date_timestamp) ;
        $renderData['info'] = $info ;

        $params_obj = new MhHtParams();
        $renderData['type_list'] = $params_obj->getDetailListByKey('XIAOSHOUDAIGONG');

        $this->loadResource('storage_sfl_total','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        //$id = $this->postParam('id');
        $add_data = $_POST;

        $obj = new MhStorageSflTotal();
        $res = $obj->savePostData($add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
