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
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class AdPositionController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $mobile = isset($_GET['mobile']) ? $_GET['mobile'] : '' ;

        if($mobile){
            $params['like_arr']['mobile'] = $mobile;
        }
        $searchArr['mobile'] = $mobile ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new AdPosition() ;
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
        $obj = new AdPosition();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $this->loadResource('ad-position','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $unique_key = $this->postParam('unique_key');
        $name = $this->postParam('name');
        $status = $this->postParam('status');
        $modify_time =date('Y-m-d H:i:s');
        $add_data = compact('unique_key','name','status','modify_time');

        $obj = new AdPosition();
        if($id){
            $old_info = $obj->getInfoById($id);
            if($old_info['unique_key'] !=$unique_key){
                // 判断unique_key是否重复
                $exist_info = $obj->checkRepeatKey($unique_key,$id);
                if($exist_info){
                    return $this->returnJson(['code'=>'200028','msg'=>getErrorDictMsg(200028)]);
                }
                $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);
            }
        }else{
            $exist_info = $obj->checkRepeatKey($unique_key,$id);
            if($exist_info){
                return $this->returnJson(['code'=>'200028','msg'=>getErrorDictMsg(200028)]);
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
        $obj = new AdPosition();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
