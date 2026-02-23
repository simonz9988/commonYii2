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
use common\models\Article;
use common\models\CashIn;
use common\models\CashOut;
use common\models\EmailCode;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class ArticleController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $cate_id = isset($_GET['cate_id']) ? $_GET['cate_id'] : '' ;

        if($cate_id){
            $params['where_arr']['article_cate_id'] = $cate_id;
        }
        $searchArr['cate_id'] = $cate_id ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';

        $site_config_obj = new SiteConfig();
        $cate_list = $site_config_obj->getByKey('article_cate_list','json') ;

        $model = new Article() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['cate_name'] = isset($cate_list[$v['article_cate_id']]) ? $cate_list[$v['article_cate_id']]['name'] : '';
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

        $renderData['cate_list'] = $cate_list ;

        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new Article();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        // 分类列表
        $site_config_obj = new SiteConfig();
        $cate_list = $site_config_obj->getByKey('article_cate_list','json') ;
        $renderData['cate_list'] = $cate_list ;

        $this->loadResource('article','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $article_cate_id = $this->postParam('article_cate_id');
        $title = $this->postParam('title');
        $intro = $this->postParam('intro');
        $cover_img_url = $this->postParam('cover_img_url');
        $content = $this->postParam('content1');
        $seo_title = $this->postParam('seo_title');
        $seo_keywords = $this->postParam('seo_keywords');
        $seo_description = $this->postParam('seo_description');
        $sort = $this->postParam('sort');
        $status = $this->postParam('status');
        $modify_time =date('Y-m-d H:i:s');

        $site_config_obj = new SiteConfig();
        $cate_list= $site_config_obj->getByKey('article_cate_list','json');
        $article_cate_key = isset($cate_list[$article_cate_id]['key']) ? $cate_list[$article_cate_id]['key']: '' ;
        $add_data = compact('article_cate_id','article_cate_key','title','intro','cover_img_url','content','seo_title','seo_keywords','seo_description','sort','status','modify_time');
        $obj = new Article();

        if($id){
            $res = $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);
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
        $obj = new Article();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
