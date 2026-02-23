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
use common\models\MhHtParams;
use common\models\MhLsPublic;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class TurnoverPublicController extends BackendController
{
    public function actionIndex(){
        $params_obj = new MhHtParams();
        $renderData['department_list'] = $params_obj->getDetailListByKey('BUMEN') ;
        $renderData['kuanxiang_list'] = $params_obj->getDetailListByKey('KUANXIANG') ;
        $renderData['type_list'] = $params_obj->getDetailListByKey('SHOURUZHICHU') ;
        $renderData['invoice_status_list'] = $params_obj->getDetailListByKey('FAPIAOZHUANGTAI') ;
        $renderData['baoxiao_fangshi_list'] = $params_obj->getDetailListByKey('BAOXIAOFANGSHI') ;
        $renderData['baoxiao_zhuangtai_list'] = $params_obj->getDetailListByKey('BAOXIAOZHUANGTAI') ;

        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['contract_no','mingxi','amount','balance','invoice_no','note'] ;
        $where_key = ['from_date','department','kuanxiang','type','baoxiao_fangshi','baoxiao_zhuangtai'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);


        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MhLsPublic() ;
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
        //$renderData['type_list'] = $model->returnTypeList();

        $this->loadResource('contact_purchase','actionList');
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new MhLsPublic();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $params_obj = new MhHtParams();
        $renderData['department_list'] = $params_obj->getDetailListByKey('BUMEN') ;
        $renderData['kuanxiang_list'] = $params_obj->getDetailListByKey('KUANXIANG') ;
        $renderData['type_list'] = $params_obj->getDetailListByKey('SHOURUZHICHU') ;
        $renderData['invoice_status_list'] = $params_obj->getDetailListByKey('FAPIAOZHUANGTAI') ;
        $renderData['baoxiao_fangshi_list'] = $params_obj->getDetailListByKey('BAOXIAOFANGSHI') ;
        $renderData['baoxiao_zhuangtai_list'] = $params_obj->getDetailListByKey('BAOXIAOZHUANGTAI') ;
        $renderData['is_modify'] = true ;
        $this->loadResource('turnover_public','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionModify(){
        $id= $this->getParam('id');
        $obj = new MhLsPublic();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $params_obj = new MhHtParams();
        $renderData['department_list'] = $params_obj->getDetailListByKey('BUMEN') ;
        $renderData['kuanxiang_list'] = $params_obj->getDetailListByKey('KUANXIANG') ;
        $renderData['type_list'] = $params_obj->getDetailListByKey('SHOURUZHICHU') ;
        $renderData['invoice_status_list'] = $params_obj->getDetailListByKey('FAPIAOZHUANGTAI') ;
        $renderData['baoxiao_fangshi_list'] = $params_obj->getDetailListByKey('BAOXIAOFANGSHI') ;
        $renderData['baoxiao_zhuangtai_list'] = $params_obj->getDetailListByKey('BAOXIAOZHUANGTAI') ;
        $renderData['is_modify'] = true ;
        $this->loadResource('turnover_public','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionView(){
        $id= $this->getParam('id');
        $obj = new MhLsPublic();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $params_obj = new MhHtParams();
        $renderData['department_list'] = $params_obj->getDetailListByKey('BUMEN') ;
        $renderData['kuanxiang_list'] = $params_obj->getDetailListByKey('KUANXIANG') ;
        $renderData['type_list'] = $params_obj->getDetailListByKey('SHOURUZHICHU') ;
        $renderData['invoice_status_list'] = $params_obj->getDetailListByKey('FAPIAOZHUANGTAI') ;
        $renderData['baoxiao_fangshi_list'] = $params_obj->getDetailListByKey('BAOXIAOFANGSHI') ;
        $renderData['baoxiao_zhuangtai_list'] = $params_obj->getDetailListByKey('BAOXIAOZHUANGTAI') ;
        $renderData['is_modify'] = false ;
        $this->loadResource('turnover_public','actionEdit');
        return $this->render('edit',$renderData) ;
    }


    // 保存广告位
    public function actionSave(){

        $id = $this->postParam('id');
        $obj = new MhLsPublic();
        $res = $obj->savePostData($id,$_POST);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhLsPublic();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 执行导出
    private function doExport($list){

        $export_data[] = [
            '合同编号','日期','部门','款项','明细','支出/收入',
            '金额','账户余额','报销方式','报销状态','发票编号','备注','修改时间'
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['contract_no'];
                $data[] = $v['from_date'];
                $data[] = $v['department'];
                $data[] = $v['kuanxiang'];
                $data[] = $v['mingxi'];
                $data[] = $v['type'];
                $data[] = $v['amount'];
                $data[] = $v['balance'];
                $data[] = $v['baoxiao_fangshi'];
                $data[] = $v['baoxiao_zhuangtai'];
                $data[] = $v['invoice_no'];
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
