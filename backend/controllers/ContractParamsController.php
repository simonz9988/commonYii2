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
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class ContractParamsController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $field_key = isset($_GET['field_key']) ? $_GET['field_key'] : '' ;

        if($field_key){
            $params['like_arr']['field_key'] = $field_key;
        }
        $searchArr['field_key'] = $field_key ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MhHtParams() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['field_key'] = $model->getFieldKeyName($v['field_key']);
            }
        }
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;
        $renderData['type_list'] = $model->returnTypeList();
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new MhHtParams();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $renderData['type_list'] = $obj->returnTypeList();
        $this->loadResource('contact_params','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $field_key = $this->postParam('field_key');
        $detail = $this->postParam('detail');
        $status = $this->postParam('status');
        $modify_time =date('Y-m-d H:i:s');
        $add_data = compact('field_key','detail','status','modify_time');

        $obj = new MhHtParams();
        $res = $obj->savePostData($id,$add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new MhHtParams();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    /**
     * 导入批量出金记录
     */
    public function actionAddBatch(){
        $this->loadResource('contact_params','actionAddBatch') ;
        $renderData['type'] = $this->getParam('type');
        $renderData['info'] = [] ;
        return $this->render('add-batch',$renderData);
    }


    // 保存广告位
    public function actionDoImport(){

        $file_name = $this->postParam('file_name');
        $type = $this->postParam('type');

        $file_path = ROOT_PATH.$file_name ;
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($file_path)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($file_path)){
                return  $this->returnJson(['code'=>200008,'msg'=>200008]);
            }
        }

        $PHPExcel = $PHPReader->load($file_path);

        /**读取excel文件中的第一个工作表*/
        $currentSheet = $PHPExcel->getSheet(0);


        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();

        //循环读取每个单元格的内容。注意行从1开始，列从A开始
        $insert_data = [];

        $allColumn = $currentSheet->getHighestColumn();

        ++$allColumn ;
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
            $tmp = [];
            //从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn !=$allColumn; $currentColumn++) {
                //数据坐标
                $address = $currentColumn . $currentRow;
                //读取到的数据，保存到数组$arr中
                $cell = $currentSheet->getCell($address)->getValue();
                if($cell instanceof PHPExcel_RichText)     //富文本转换字符串
                    $cell = $cell->__toString();

                $tmp[] = $cell;
            }

            $insert_data[] = $tmp;
        }



        if(!$insert_data){
            return  $this->returnJson(['code'=>200009,'msg'=>getErrorDictMsg(200009)]);
        }

        $obj = new MhHtParams();
        $obj->doBatchInsert($type,$insert_data);


        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
