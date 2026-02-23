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
use common\models\MhStorageSfl;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class StorageSflController extends BackendController
{
    public function actionIndex(){

        $params_obj = new MhHtParams();
        $renderData['zcgx_list'] = $params_obj->getDetailListByKey('ZHICHENGGONGXU');
        $renderData['cpzt_list'] = $params_obj->getDetailListByKey('SHOUFALIAOCHANPZT');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');
        $renderData['type_list'] = $params_obj->getDetailListByKey('XIAOSHOUDAIGONG');

        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['contract_no','slcj','ylh','xlh','diameter','amount','weight','bag_no','note'] ;
        $where_key = ['from_date','zcgx','cpzt','paihao','type','month'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MhStorageSfl() ;
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
        $obj = new MhStorageSfl();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['zcgx_list'] = $params_obj->getDetailListByKey('ZHICHENGGONGXU');
        $renderData['cpzt_list'] = $params_obj->getDetailListByKey('SHOUFALIAOCHANPZT');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');
        $renderData['type_list'] = $params_obj->getDetailListByKey('XIAOSHOUDAIGONG');
        $this->loadResource('storage_sfl','actionEdit');
        $renderData['is_modify'] = true ;

        $total_weight = 0 ;
        if($info && $info['is_yl_send'] =='Y'){
            $total_weight = $obj->getTotalWeightByCpxt($info['cpzt'],$info['paihao']);
        }

        // 初始化新炉号
        $xlh = '';
        if(!$info){
            $xlh = $obj->createNewLh();
        }
        $renderData['xlh'] = $xlh ;

        $renderData['total_weight'] = $total_weight ;
        return $this->render('edit',$renderData) ;
    }

    public function actionModify(){
        $id= $this->getParam('id');
        $obj = new MhStorageSfl();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['zcgx_list'] = $params_obj->getDetailListByKey('ZHICHENGGONGXU');
        $renderData['cpzt_list'] = $params_obj->getDetailListByKey('SHOUFALIAOCHANPZT');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');
        $renderData['type_list'] = $params_obj->getDetailListByKey('XIAOSHOUDAIGONG');
        $this->loadResource('storage_sfl','actionEdit');
        $renderData['is_modify'] = true ;

        $total_weight = 0 ;
        if($info && $info['is_yl_send'] =='Y'){
            $total_weight = $obj->getTotalWeightByCpxt($info['cpzt'],$info['paihao']);
        }
        $renderData['total_weight'] = $total_weight ;

        return $this->render('edit',$renderData) ;
    }

    public function actionView(){
        $id= $this->getParam('id');
        $obj = new MhStorageSfl();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['zcgx_list'] = $params_obj->getDetailListByKey('ZHICHENGGONGXU');
        $renderData['cpzt_list'] = $params_obj->getDetailListByKey('SHOUFALIAOCHANPZT');
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO');
        $renderData['type_list'] = $params_obj->getDetailListByKey('XIAOSHOUDAIGONG');
        $this->loadResource('storage_sfl','actionEdit');
        $renderData['is_modify'] = false ;

        $total_weight = 0 ;
        if($info && $info['is_yl_send'] =='Y'){
            $total_weight = $obj->getTotalWeightByCpxt($info['cpzt'],$info['paihao']);
        }
        $renderData['total_weight'] = $total_weight ;
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $add_data = $_POST;

        $obj = new MhStorageSfl();
        $res = $obj->savePostData($id,$add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhStorageSfl();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        responseJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 查询汇总信息
    public function actionGetTotal(){
        $cpzt = $this->postParam('cpzt');
        $paihao = $this->postParam('paihao');
        $obj = new MhStorageSfl();
        $data = $obj->getTotalWeightByCpxt($cpzt,$paihao);
        responseJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 执行导出
    private function doExport($list){

        $export_data[] = [
            '日期','发料厂家','收料厂家','制程工序','产品状态','原炉号',
            '新炉号','牌号','规格（mm)','数量','重量KG','销售/代工'
            ,'月份','袋号','备注','修改时间'
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['from_date'];
                $data[] = $v['ffcj'];
                $data[] = $v['slcj'];
                $data[] = $v['zcgx'];
                $data[] = $v['cpzt'];
                $data[] = $v['ylh'];
                $data[] = $v['xlh'];
                $data[] = $v['paihao'];
                $data[] = $v['diameter'];
                $data[] = $v['amount'];
                $data[] = $v['weight'];
                $data[] = $v['type'];
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
