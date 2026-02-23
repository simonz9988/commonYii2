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
use common\models\Ad;
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
class AdController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $position_id = isset($_GET['position_id']) ? $_GET['position_id'] : '' ;

        if($position_id){
            $params['where_arr']['position_id'] = $position_id;
        }
        $searchArr['position_id'] = $position_id ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new Ad() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            $position_obj = new AdPosition();
            foreach($list as $k=>$v){
                $list[$k]['position_name'] = $position_obj->getNameByKey($v['position_key']);
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

        // 广告位列表
        $position_obj = new AdPosition();
        $renderData['position_list'] = $position_obj->getAll();

        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new Ad();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        // 广告位列表
        $position_obj = new AdPosition();
        $renderData['position_list'] = $position_obj->getAll();
        $this->loadResource('ad','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $position_id = $this->postParam('position_id');
        $title = $this->postParam('title');
        $summary = $this->postParam('summary');
        $img_url = $this->postParam('img_url');
        $link = $this->postParam('link');
        $content = $this->postParam('content');
        $sort = $this->postParam('sort');
        $status = $this->postParam('status');
        $modify_time =date('Y-m-d H:i:s');

        $position_obj = new AdPosition();
        $position_info= $position_obj->getInfoById($position_id);
        $position_key = $position_info ? $position_info['unique_key']: 0 ;
        $add_data = compact('position_id','position_key','title','summary','img_url','link','content','sort','status','modify_time');

        $obj = new Ad();
        if($id){
            $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);
        }else{
            $add_data['create_time']  = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            $obj->baseInsert($obj::tableName(),$add_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new Ad();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
