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
use common\models\MhHtSales;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class ContractSalesController extends BackendController
{
    public function actionIndex(){

        $params_obj = new MhHtParams();
        $renderData['chanpin_bie_list'] = $params_obj->getDetailListByKey('CHANPINBIE') ;
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO') ;
        $renderData['product_form_list'] = $params_obj->getDetailListByKey('CHANPXINGTAI') ;
        $renderData['jhzt_list'] = $params_obj->getDetailListByKey('JIAOHUOZHUANGTAI') ;


        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['contract_no','customer_name','diameter','danjia','dingdl','sjchl','kszl','yinskze','shijishk','skcy'] ;
        $where_key = ['chanpin_bie','paihao','product_form','jiaohzt','jdrq','htjq','sjchrq','chnf','chyf'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MhHtSales() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $list = $model->formatList($list);
        $renderData['total_info'] = $model->getTotalInfoByList($list);
        
        $renderData['list'] =$list;

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
        $this->loadResource('contact_sales','actionList');
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new MhHtSales();
        $info = $obj->getInfoById($id) ;
        $params_obj = new MhHtParams();
        $renderData['chanpin_bie_list'] = $params_obj->getDetailListByKey('CHANPINBIE') ;
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO') ;
        $renderData['product_form_list'] = $params_obj->getDetailListByKey('CHANPXINGTAI') ;
        $renderData['jhzt_list'] = $params_obj->getDetailListByKey('JIAOHUOZHUANGTAI') ;

        $renderData['start_year'] = date("Y");
        $renderData['end_year'] = date("Y") +10;
        $renderData['is_modify'] = true ;

        $add_extra= $this->getParam('add_extra');
        if($add_extra=='y' && $info){
            foreach($info as $k=>$v){
                if(!in_array($k,['id','contract_no','customer_name'])){
                    $info[$k]= '';
                }
            }
        }
        $renderData['add_extra'] = $add_extra ;
        $renderData['info'] = $info ;

        $this->loadResource('contact_sales','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 修改
    public function actionModify(){
        $id= $this->getParam('id');
        $obj = new MhHtSales();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['chanpin_bie_list'] = $params_obj->getDetailListByKey('CHANPINBIE') ;
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO') ;
        $renderData['product_form_list'] = $params_obj->getDetailListByKey('CHANPXINGTAI') ;
        $renderData['jhzt_list'] = $params_obj->getDetailListByKey('JIAOHUOZHUANGTAI') ;

        $renderData['start_year'] = date("Y");
        $renderData['end_year'] = date("Y") +10;
        $renderData['is_modify'] = true ;
        $renderData['add_extra'] =  '';
        $this->loadResource('contact_sales','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 浏览
    public function actionView(){
        $id= $this->getParam('id');
        $obj = new MhHtSales();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['chanpin_bie_list'] = $params_obj->getDetailListByKey('CHANPINBIE') ;
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO') ;
        $renderData['product_form_list'] = $params_obj->getDetailListByKey('CHANPXINGTAI') ;
        $renderData['jhzt_list'] = $params_obj->getDetailListByKey('JIAOHUOZHUANGTAI') ;

        $renderData['start_year'] = date("Y");
        $renderData['end_year'] = date("Y") +10;
        $renderData['is_modify'] = false ;
        $renderData['add_extra'] =  '';

        $this->loadResource('contact_sales','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $add_data = $_POST;

        $obj = new MhHtSales();
        $res = $obj->savePostData($id,$add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhHtMaterialPurchase();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 执行导出
    private function doExport($list){

        $export_data[] = [
            '合同编号','客户名称','产品别','牌号','产品形态','尺寸规格(mm)',
            '单价(元/kg/pcs)','订单量','实际出货量','客诉重量','应收款','实际收款','收款差异',
            '交货状态','接单日期','合同交期','实际出货日期','出货年份','出货月份','备注','修改时间'
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['contract_no'];
                $data[] = $v['supplier_name'];
                $data[] = $v['chanpin_bie'];
                $data[] = $v['paihao'];
                $data[] = $v['product_form'];
                $data[] = $v['diameter'];
                $data[] = $v['danjia'];
                $data[] = $v['dingdl'].$v['dingdl_type'];
                $data[] = $v['sjchl'].$v['sjchl_type'];
                $data[] = $v['kszl'];
                $data[] = $v['yinskze'];
                $data[] = $v['shijishk'];
                $data[] = $v['skcy'];
                $data[] = $v['jiaohzt'];
                $data[] = $v['jdrq'];
                $data[] = $v['htjq'];
                $data[] = $v['sjchrq'];
                $data[] = $v['chnf'];
                $data[] = $v['chyf'];
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
