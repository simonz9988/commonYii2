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
use common\models\LanguagePacket;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class LanguagePacketController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $item_key = isset($_GET['item_key']) ? $_GET['item_key'] : '' ;

        if($item_key){
            $params['like_arr']['item_key'] = $item_key;
        }
        $searchArr['item_key'] = $item_key ;

        $page_key = isset($_GET['page_key']) ? $_GET['page_key'] : '' ;

        if($page_key){
            $params['like_arr']['page_key'] = $page_key;
        }
        $searchArr['page_key'] = $page_key ;

        $description = isset($_GET['description']) ? $_GET['description'] : '' ;

        if($description){
            $params['like_arr']['description'] = $description;
        }
        $searchArr['description'] = $description ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new LanguagePacket() ;
        $renderData['page_list'] = $model->returnPageKey();

        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            foreach($list as $k=>$v){
                $page_key = $v['page_key'];
                $list[$k]['page_name'] = isset($renderData['page_list'][$page_key])?$renderData['page_list'][$page_key]:'';

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



        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new LanguagePacket();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        // 查询所有页面信息
        $renderData['page_list'] = $obj->returnPageKey();

        // 查询 所有允许的语言包列表信息
        $renderData['language_item_list'] = $obj->getLanguageItemList($info);

        $this->loadResource('language_packet','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $item_key = $this->postParam('item_key');
        $description = $this->postParam('description');
        $page_key = $this->postParam('page_key');
        $language_item_list = $this->postParam('language_item_list','',false);
        $modify_time =date('Y-m-d H:i:s');

        $add_data = compact('item_key','description','page_key','language_item_list','modify_time');
        $obj = new LanguagePacket();
        $res = $obj->saveData($id,$add_data);
        if(!$res){
            return $this->returnJson($obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new LanguagePacket();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
