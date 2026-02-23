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
use common\models\SiteConfig;
use common\models\SunnyCompany;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class SunnyCompanyController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $country_id = isset($_GET['country_id']) ? $_GET['country_id'] : '' ;

        if($country_id){
            $params['like_arr']['country_id'] = $country_id;
        }
        $searchArr['country_id'] = $country_id ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new SunnyCompany() ;
        $country_obj = new Country();

        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['country_name'] = $country_obj->getNameById($v['country_id']) ;
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

        $renderData['country_list'] = $country_obj->getList();
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new SunnyCompany();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $country_obj = new Country();
        $renderData['country_list'] = $country_obj->getList();

        $renderData['language_item_list'] = $obj->getLanguageItemList($info);
        $this->loadResource('sunny-company','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $unique_key = $this->postParam('unique_key');


        $obj = new SunnyCompany();
        if($id){
            $old_info = $obj->getInfoById($id);
            if($old_info['unique_key'] !=$unique_key){
                // 判断unique_key是否重复
                $exist_info = $obj->checkRepeatKey($unique_key,$id);
                if($exist_info){
                    return $this->returnJson(['code'=>'200045','msg'=>getErrorDictMsg(200045)]);
                }

            }
        }else{
            $exist_info = $obj->checkRepeatKey($unique_key,$id);
            if($exist_info){
                return $this->returnJson(['code'=>'200045','msg'=>getErrorDictMsg(200045)]);
            }
            $add_data['create_time']  = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';

        }


        $obj->addData($id,$_POST);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new SunnyCompany();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
