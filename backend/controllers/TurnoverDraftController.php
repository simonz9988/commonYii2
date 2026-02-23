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
use common\models\MhLsDraft;
use common\models\MhLsPublic;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class TurnoverDraftController extends BackendController
{
    public function actionIndex(){

        $params_obj = new MhHtParams();
        $renderData['department_list'] = $params_obj->getDetailListByKey('BUMEN') ;
        $renderData['project_list'] = $params_obj->getDetailListByKey('XIANGMU') ;
        $renderData['kuanxiang_list'] = $params_obj->getDetailListByKey('KUANXIANG') ;
        $renderData['type_list'] = $params_obj->getDetailListByKey('SHOURUZHICHU') ;

        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['contract_no','piaohao','fukuan_company','shoukuan_company','amount','piaohao','chendui_name','note'] ;
        $where_key = ['from_date','department','project','type'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MhLsDraft() ;
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
        $obj = new MhLsDraft();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $params_obj = new MhHtParams();
        $renderData['department_list'] = $params_obj->getDetailListByKey('BUMEN') ;
        $renderData['project_list'] = $params_obj->getDetailListByKey('XIANGMU') ;
        $renderData['kuanxiang_list'] = $params_obj->getDetailListByKey('KUANXIANG') ;
        $renderData['type_list'] = $params_obj->getDetailListByKey('SHOURUZHICHU') ;
        $renderData['is_modify'] = true ;
        $add_extra = $this->getParam('add_extra');
        if($add_extra=='y' && $info){
            foreach($info as $k=>$v){
                if(!in_array($k,['id','piaohao'])){
                    $info[$k]= '';
                }
            }
        }
        $renderData['add_extra'] = $add_extra ;
        // 获取所有票号列表

        $this->loadResource('turnover_draft','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionModify(){
        $id= $this->getParam('id');
        $obj = new MhLsDraft();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $params_obj = new MhHtParams();
        $renderData['department_list'] = $params_obj->getDetailListByKey('BUMEN') ;
        $renderData['project_list'] = $params_obj->getDetailListByKey('XIANGMU') ;
        $renderData['kuanxiang_list'] = $params_obj->getDetailListByKey('KUANXIANG') ;
        $renderData['type_list'] = $params_obj->getDetailListByKey('SHOURUZHICHU') ;
        $renderData['is_modify'] = true ;
        $renderData['add_extra'] = '' ;
        $this->loadResource('turnover_draft','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    public function actionView(){
        $id= $this->getParam('id');
        $obj = new MhLsDraft();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $params_obj = new MhHtParams();
        $renderData['department_list'] = $params_obj->getDetailListByKey('BUMEN') ;
        $renderData['project_list'] = $params_obj->getDetailListByKey('XIANGMU') ;
        $renderData['kuanxiang_list'] = $params_obj->getDetailListByKey('KUANXIANG') ;
        $renderData['type_list'] = $params_obj->getDetailListByKey('SHOURUZHICHU') ;
        $renderData['is_modify'] = false ;
        $renderData['add_extra'] = '' ;
        $this->loadResource('turnover_draft','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){

        $id = $this->postParam('id');
        $obj = new MhLsDraft();
        $res = $obj->savePostData($id,$_POST);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhLsDraft();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 执行导出
    private function doExport($list){

        $export_data[] = [
            '合同编号','日期','付款公司名称','收款公司名称','部门','项目','支出/收入',
            '票面金额','票号','承兑人名称','备注','修改时间'
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['contract_no'];
                $data[] = $v['from_date'];
                $data[] = $v['fukuan_company'];
                $data[] = $v['shoukuan_company'];
                $data[] = $v['department'];
                $data[] = $v['project'];
                $data[] = $v['type'];
                $data[] = $v['amount'];
                $data[] = $v['piaohao'];
                $data[] = $v['chendui_name'];
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
