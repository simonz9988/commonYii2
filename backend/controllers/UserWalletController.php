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
use common\models\SiteConfig;
use common\models\UserWallet;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class UserWalletController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $mobile = isset($_GET['mobile']) ? $_GET['mobile'] : '' ;

        if($mobile){
            $params['like_arr']['mobile'] = $mobile;
        }
        $searchArr['mobile'] = $mobile ;

        $address = isset($_GET['address']) ? $_GET['address'] : '' ;

        if($address){
            $params['like_arr']['address'] = strtolower($address);
        }
        $searchArr['address'] = $mobile ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $this->view->params['customParam'] = 'customValue';
        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new UserWallet() ;
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

    /**
     * 导入批量出金记录
     */
    public function actionAddBatch(){
        $this->loadResource('wallet','actionAddBatch') ;
        $site_config = new SiteConfig();
        $cash_in_coin_type = $site_config->getByKey('cash_in_coin_type');
        $coin_type_list = json_decode($cash_in_coin_type,true);
        $renderData['coin_type_list'] = $coin_type_list ;
        $site_config_obj = new SiteConfig();
        $renderData['cash_out_min_amount'] = $site_config_obj->getByKey('cash_out_min_amount');
        $renderData['cash_out_max_amount'] = $site_config_obj->getByKey('cash_out_max_amount');
        $renderData['info'] = [] ;
        return $this->render('add-batch',$renderData);
    }


    // 保存广告位
    public function actionDoImport(){

        $file_name = $this->postParam('file_name');

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
        /**取得最大的列号*/
        $allColumn = $currentSheet->getHighestColumn();
        $allColumn = 'A';

        /**取得一共有多少行*/
        $allRow = $currentSheet->getHighestRow();

        //循环读取每个单元格的内容。注意行从1开始，列从A开始
        $insert_data = [];
        for($rowIndex=2;$rowIndex<=$allRow;$rowIndex++){
            $add_data = [] ;
            for($colIndex='A';$colIndex<=$allColumn;$colIndex++){
                $addr = $colIndex.$rowIndex;
                $cell = $currentSheet->getCell($addr)->getValue();
                if($cell instanceof PHPExcel_RichText)     //富文本转换字符串
                    $cell = $cell->__toString();

                $add_data[] = $cell ;

            }
            $insert_data[] = $add_data ;
        }

        if(!$insert_data){
            return  $this->returnJson(['code'=>200009,'msg'=>getErrorDictMsg(200009)]);
        }

        $obj = new UserWallet();

        $batch_insert_data = [] ;

        $now = date('Y-m-d H:i:s');
        foreach($insert_data as $k=>$v){

            $address = trim($v[0]);

            $exits_info = $obj->getInfoByAddress($address);
            if($exits_info){
                return $this->returnJson(['code'=>999,'msg'=>'第'.($k+1).'行已存在']);
            }

            $check_data['address'] = $address;
            $check_data['user_id'] = 0;
            $check_data['mobile'] = '';
            $check_data['is_deleted'] = 'N';
            $check_data['create_time'] = $now;
            $check_data['modify_time'] = $now;
            $batch_insert_data[] = $check_data ;

        }

        foreach($batch_insert_data as $v){
            $obj->baseInsert($obj::tableName(),$v);
        }


        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new UserWallet();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
