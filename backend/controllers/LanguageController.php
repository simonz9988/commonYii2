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
use common\models\Language;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class LanguageController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $name = isset($_GET['name']) ? $_GET['name'] : '' ;

        if($name){
            $params['like_arr']['name'] = $name;
        }
        $searchArr['name'] = $name ;

        $short = isset($_GET['short']) ? $_GET['short'] : '' ;

        if($short){
            $params['like_arr']['short'] = $short;
        }
        $searchArr['short'] = $short ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new Language() ;
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

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new Language();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $this->loadResource('language','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $short = $this->postParam('short');
        $sort = $this->postParam('sort');
        $name = $this->postParam('name');
        $is_default = $this->postParam('is_default');
        $modify_time =date('Y-m-d H:i:s');
        $add_data = compact('sort','short','name','is_default','modify_time');

        $obj = new Language();
        if($id){
            $old_info = $obj->getInfoById($id);
            if($old_info['short'] !=$short){
                // 判断unique_key是否重复
                $exist_info = $obj->checkRepeatKey($short,$id);
                if($exist_info){
                    return $this->returnJson(['code'=>'200040','msg'=>getErrorDictMsg(200040)]);
                }
                $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);
            }
        }else{
            $exist_info = $obj->checkRepeatKey($short,$id);
            if($exist_info){
                return $this->returnJson(['code'=>'200040','msg'=>getErrorDictMsg(200040)]);
            }
            $add_data['create_time']  = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            $obj->baseInsert($obj::tableName(),$add_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new Language();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
