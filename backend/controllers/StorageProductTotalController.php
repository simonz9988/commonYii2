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
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class StorageProductTotalController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

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
        $params['group_by'] = 'from_date_timestamp,name';
        $params['order_by'] = ' id desc ';
        $params['return_field'] = 'id,from_date,name,month,note,sum(amount) as total_amount,sum(weight) as total_weight,from_date_timestamp';

        $model = new MhStorageProduct() ;
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
        $renderData['name_list'] = $params_obj->getDetailListByKey('CHANCHENGPCHANPMINGCHENG');
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $name= $this->getParam('name');
        $from_date_timestamp= $this->getParam('from_date_timestamp');
        $obj = new MhStorageProductTotal();
        $info = $obj->getInfoByFromDateAndName($from_date_timestamp,$name) ;
        $renderData['name'] = $name ;
        $renderData['from_date'] = date('Y/m/d',$from_date_timestamp) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['name_list'] = $params_obj->getDetailListByKey('CHANCHENGPCHANPMINGCHENG');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');
        $this->loadResource('storage_product_total','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        //$id = $this->postParam('id');
        $add_data = $_POST;

        $obj = new MhStorageProductTotal();
        $res = $obj->savePostData($add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhStorageProduct();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
