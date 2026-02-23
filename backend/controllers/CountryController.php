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
use common\models\Country;
use common\models\EmailCode;
use common\models\Language;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class CountryController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $name = isset($_GET['name']) ? $_GET['name'] : '' ;

        if($name){
            $params['like_arr']['name'] = $name;
        }
        $searchArr['name'] = $name ;

        $iso_code_2 = isset($_GET['iso_code_2']) ? $_GET['iso_code_2'] : '' ;

        if($iso_code_2){
            $params['like_arr']['iso_code_2'] = $iso_code_2;
        }
        $searchArr['iso_code_2'] = $iso_code_2 ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new Country() ;
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

    // 编辑
    public function actionEdit(){

        $id= $this->getParam('id');
        $obj = new Country();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $renderData['language_item_list'] = $obj->getLanguageItemList($info);
        $this->loadResource('country','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存
    public function actionSave(){

        $obj = new Country();
        $post_data = $_POST ;
        $id = $this->postParam('id');
        $obj->savePostData($id,$post_data);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
