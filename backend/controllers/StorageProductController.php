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
use common\models\MhStorageSfl;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class StorageProductController extends BackendController
{
    public function actionIndex(){

        $params_obj = new MhHtParams();
        $renderData['name_list'] = $params_obj->getDetailListByKey('CHANCHENGPCHANPMINGCHENG');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');

        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['ylh','xlh','paihao','diameter','amount','weight','bag_no','note'] ;
        $where_key = ['name','from_date','is_daigong','month'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MhStorageProduct() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $list = $model->formatList($list);
        $renderData['list'] =$list;
        $renderData['total_info'] = $model->getTotalInfoByList($list);

        if($searchArr && $searchArr['is_download']){
            //导出文件
            return $this->doExport($list);
        }

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        $this->loadResource('contact_purchase','actionList');
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new MhStorageProduct();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['name_list'] = $params_obj->getDetailListByKey('CHANCHENGPCHANPMINGCHENG');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');
        $this->loadResource('storage_product','actionEdit');

        // 初始化新炉号
        $xlh = '';
        if(!$info){
            $xlh = $obj->createNewLh();
        }
        $renderData['xlh'] = $xlh ;
        $renderData['is_modify'] = true ;
        return $this->render('edit',$renderData) ;
    }

    public function actionModify(){
        $id= $this->getParam('id');
        $obj = new MhStorageProduct();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['name_list'] = $params_obj->getDetailListByKey('CHANCHENGPCHANPMINGCHENG');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');
        $this->loadResource('storage_product','actionEdit');

        $renderData['is_modify'] = true ;
        return $this->render('edit',$renderData) ;
    }

    public function actionView(){
        $id= $this->getParam('id');
        $obj = new MhStorageProduct();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['name_list'] = $params_obj->getDetailListByKey('CHANCHENGPCHANPMINGCHENG');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');
        $this->loadResource('storage_product','actionEdit');

        $renderData['is_modify'] = true ;
        return $this->render('edit',$renderData) ;
    }
    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $add_data = $_POST;

        $obj = new MhStorageProduct();
        $res = $obj->savePostData($id,$add_data);
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

    // 执行导出
    private function doExport($list){

        $export_data[] = [
            '日期','名称','原炉号','新炉号','牌号','规格（mm)',
            '数量','重量KG','是否为代工'
            ,'月份','袋号','备注','修改时间'
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['from_date'];
                $data[] = $v['name'];
                $data[] = $v['ylh'];
                $data[] = $v['xlh'];
                $data[] = $v['paihao'];
                $data[] = $v['diameter'];
                $data[] = $v['amount'];
                $data[] = $v['weight'];
                $data[] = $v['is_daigong']=='Y'?'是':'否';
                $data[] = $v['month'];
                $data[] = $v['bag_no'];
                $data[] = $v['note'];
                $data[] = $v['modify_time'];
                $export_data[] = $data;

                unset($data);
            }
        }

        $export_obj = new ExportFile();
        $file_name = '导出内容-'.date('Y-m-d').'.csv';
        $export_obj->download($export_data,$file_name);

    }


}
