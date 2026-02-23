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
use common\models\MhHtEntrust;
use common\models\MhHtMaterialPurchase;
use common\models\MhHtParams;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class ContractEntrustController extends BackendController
{
    public function actionIndex(){

        $params_obj = new MhHtParams();
        $renderData['chanpin_bie_list'] = $params_obj->getDetailListByKey('CHANPINBIE') ;
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO') ;
        $renderData['chuliao_xingtai_list'] = $params_obj->getDetailListByKey('CHULIAOXINGTAI') ;
        $renderData['weituo_zhuangtai_list'] = $params_obj->getDetailListByKey('WEITUOXINGTAI') ;
        $renderData['jhzt_list'] = $params_obj->getDetailListByKey('JIAOHUOZHUANGTAI') ;
        $renderData['fkzt_list'] = $params_obj->getDetailListByKey('FUKUANZHUANGTAI') ;

        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['clhcl','ccl','fcl','wjgfhzl','contract_no','customer_name','chuliao_diameter','weituo_diameter','dingdl','clzl','kszl','jiagongdj','jiagongje','cphcl'] ;
        $where_key = ['chanpin_bie','paihao','chuliao_xingtai','weituo_zhuangtai','wwrq','clrq','htjq','jhzt','fkzt','chyf','chnf'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MhHtEntrust() ;
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
        $this->loadResource('contact_purchase','actionList');
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new MhHtEntrust();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['chanpin_bie_list'] = $params_obj->getDetailListByKey('CHANPINBIE') ;
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO') ;
        $renderData['chuliao_xingtai_list'] = $params_obj->getDetailListByKey('CHULIAOXINGTAI') ;
        $renderData['weituo_zhuangtai_list'] = $params_obj->getDetailListByKey('WEITUOXINGTAI') ;
        $renderData['jhzt_list'] = $params_obj->getDetailListByKey('JIAOHUOZHUANGTAI') ;
        $renderData['fkzt_list'] = $params_obj->getDetailListByKey('FUKUANZHUANGTAI') ;

        $renderData['start_year'] = date("Y");
        $renderData['end_year'] = date("Y") +10;
        $renderData['is_modify'] = true;

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

        $this->loadResource('contact_entrust','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionModify(){
        $id= $this->getParam('id');
        $obj = new MhHtEntrust();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['chanpin_bie_list'] = $params_obj->getDetailListByKey('CHANPINBIE') ;
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO') ;
        $renderData['chuliao_xingtai_list'] = $params_obj->getDetailListByKey('CHULIAOXINGTAI') ;
        $renderData['weituo_zhuangtai_list'] = $params_obj->getDetailListByKey('WEITUOXINGTAI') ;
        $renderData['jhzt_list'] = $params_obj->getDetailListByKey('JIAOHUOZHUANGTAI') ;
        $renderData['fkzt_list'] = $params_obj->getDetailListByKey('FUKUANZHUANGTAI') ;

        $renderData['start_year'] = date("Y");
        $renderData['end_year'] = date("Y") +10;
        $renderData['is_modify'] = true;
        $renderData['add_extra'] = '' ;
        $this->loadResource('contact_entrust','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionView(){
        $id= $this->getParam('id');
        $obj = new MhHtEntrust();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $params_obj = new MhHtParams();
        $renderData['chanpin_bie_list'] = $params_obj->getDetailListByKey('CHANPINBIE') ;
        $renderData['paihao_list'] = $params_obj->getDetailListByKey('PAIHAO') ;
        $renderData['chuliao_xingtai_list'] = $params_obj->getDetailListByKey('CHULIAOXINGTAI') ;
        $renderData['weituo_zhuangtai_list'] = $params_obj->getDetailListByKey('WEITUOXINGTAI') ;
        $renderData['jhzt_list'] = $params_obj->getDetailListByKey('JIAOHUOZHUANGTAI') ;
        $renderData['fkzt_list'] = $params_obj->getDetailListByKey('FUKUANZHUANGTAI') ;

        $renderData['start_year'] = date("Y");
        $renderData['end_year'] = date("Y") +10;
        $renderData['is_modify'] = false;
        $renderData['add_extra'] = '' ;
        $this->loadResource('contact_entrust','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $add_data = $_POST;
        $obj = new MhHtEntrust();
        $res = $obj->savePostData($id,$add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhHtEntrust();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 执行导出
    private function doExport($list){

        $export_data[] = [
            '合同编号','客户名称','产品别','牌号','出料形态','出料尺寸(mm)','委外加工尺寸（mm）','委外加工状态','订单量',
            '出料重量','客诉重量','加工单价','加工金额','委外日期','出料日期','合同交期','实际交期','成品回厂量','残料回厂量',
            '未加工返回重量','成材率','返材率','交货状态','付款状态','出货年份','出货月份','修改时间'
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['contract_no'];
                $data[] = $v['customer_name'];
                $data[] = $v['chanpin_bie'];
                $data[] = $v['paihao'];
                $data[] = $v['chuliao_xingtai'];
                $data[] = $v['chuliao_diameter'];
                $data[] = $v['weituo_diameter'];
                $data[] = $v['weituo_zhuangtai'];
                $data[] = $v['dingdl'].$v['dingdl_type'];
                $data[] = $v['clzl'].$v['clzl_type'];
                $data[] = $v['kszl'];
                $data[] = $v['jiagongdj'];
                $data[] = $v['jiagongje'];
                $data[] = $v['wwrq'];
                $data[] = $v['clrq'];
                $data[] = $v['htjq'];
                $data[] = $v['sjjq'];
                $data[] = $v['cphcl'];
                $data[] = $v['clhcl'];
                $data[] = $v['wjgfhzl'];
                $data[] = $v['ccl'];
                $data[] = $v['fcl'];
                $data[] = $v['jhzt'];
                $data[] = $v['fkzt'];
                $data[] = $v['chnf'];
                $data[] = $v['chyf'];
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
