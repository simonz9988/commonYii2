<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use common\models\Airplane;
use common\models\Shouce;
use common\models\SiteConfig;

/**
 * 手册管理
 */
class ShouceController extends BackendController
{
    public function actionList(){

        $searchArr = array();

        $file_name= $this->getParam('file_name');
        if($file_name){
            $params['like_arr']['file_name'] = $file_name ;
        }
        $searchArr['file_name'] = $file_name ;

        $file_type= $this->getParam('file_type');
        if($file_type){
            $params['like_arr']['file_type'] = $file_type ;
        }
        $searchArr['file_type'] = $file_type ;

        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id desc ';

        $model = new Shouce() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        if($list){
            $airplane_model = new Airplane();
            foreach($list as $k=>$v){
                $list[$k]['airplane_name'] = $airplane_model->getNameById($v['airplane_id']);
            }
        }
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params,  $model::getDb());

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;
        //debug($page_data,1);
        return $this->render('list',$renderData) ;
    }

    public function actionEdit(){
        $info = array();
        $id = $this->getParam('id');

        $shouce_model = new Shouce() ;

        if($id) {
            $params['where_arr']['id'] = $id;
            $params['return_type'] = 'row';

            $info = $shouce_model->findByWhere($shouce_model::tableName(), $params);

        }

        $renderData['info'] = $info ;

        $renderData['type_list'] = ['XML','CGM','SVG'];

        $airplane_model = new Airplane();
        $renderData['airplane_list'] = $airplane_model->getAll() ;

        $renderData['parent_list'] = $shouce_model->getParentList($id);

        $this->loadResource('shouce','actionEdit') ;

        return $this->render('edit',$renderData);
    }

    public function actionSave(){
        $id= $this->postParam('id');
        $file_path = $this->postParam('file_path') ;
        $file_name = $this->postParam('file_name') ;
        $file_name = strtoupper($file_name) ;// 文件名称必须为大写，且必须为唯一的

        $file_type = $this->postParam('file_type') ;
        $airplane_id = $this->postParam('airplane_id') ;
        $parent_id = $this->postParam('parent_id','int') ;

        $now = date('Y-m-d H:i:s');
        $modify_time = $now ;

        $file_url = ROOT_PATH.$file_path ;

        $add_data = compact('file_path','file_name','file_type','airplane_id','parent_id','modify_time');
        $xml_content = '';
        if($file_type=='XML'){
            $xml_content = file_get_contents($file_url);
            $xml_content_arr = xmlToArray($xml_content);
            $xml_content_arr = object_array($xml_content_arr);
            $dmCode = $xml_content_arr['identAndStatusSection']['dmAddress']['dmIdent']['dmCode']['@attributes'];
            $add_data['modelIdentCode'] = $dmCode['modelIdentCode'];
            $add_data['systemDiffCode'] = $dmCode['systemDiffCode'];
            $add_data['systemCode'] = $dmCode['systemCode'];
            $add_data['subSystemCode'] = $dmCode['subSystemCode'];
            $add_data['subSubSystemCode'] = $dmCode['subSubSystemCode'];
            $add_data['assyCode'] = $dmCode['assyCode'];
            $add_data['disassyCode'] = $dmCode['disassyCode'];
            $add_data['disassyCodeVariant'] = $dmCode['disassyCodeVariant'];
            $add_data['infoCode'] = $dmCode['infoCode'];
            $add_data['infoCodeVariant'] = $dmCode['infoCodeVariant'];
            $add_data['itemLocationCode'] = $dmCode['itemLocationCode'];

        }

        #TODO 具体的是按照文件名称去进行全局检索

        $add_data['xml_content'] = $xml_content ;

        $model = new Shouce();
        if($id){
            $model->baseUpdate($model::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = $now ;
            $model->baseInsert($model::tableName(),$add_data);
        }
        return $this->redirect('/shouce/list');
    }

    // 返回所有的飞机列表
    public function actionAirplaneList(){

        $searchArr = array();

        $name= $this->getParam('name');
        if($name){
            $params['like_arr']['name'] = $name ;
        }
        $searchArr['name'] = $name ;


        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' modify_time desc ';
        $params['where_arr']['is_open'] = 1 ;
        $model = new Airplane() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params,  $model::getDb());

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;
        //debug($page_data,1);
        return $this->render('airplane_list',$renderData) ;
    }

    public function actionAddAirplane(){

        $info = array();
        $id = $this->getParam('id');

        $airplane_model = new Airplane() ;

        if($id) {
            $params['where_arr']['id'] = $id;
            $params['return_type'] = 'row';

            $info = $airplane_model->findByWhere($airplane_model::tableName(), $params);

        }

        $renderData['info'] = $info ;

        $parent_params['where_arr']['is_open'] = 1 ;
        $parent_params['where_arr']['parent_id'] = 0  ;
        if($id){
            $parent_params['not_where_arr']['id'] = $id;
        }
        $parent_list = $airplane_model->findByWhere($airplane_model::tableName(),$parent_params) ;


        $renderData['parent_list'] = $parent_list ;

        $this->loadResource('system','actionAddMenu') ;

        return $this->render('add_airplane',$renderData);

    }

    // 保存飞机信息
    public function actionSaveAirplane(){
        $name = $this->postParam('name');
        $parent_id = $this->postParam('parent_id');
        $id = $this->postParam('id');

        $now = date('Y-m-d H:i:s');
        $modify_time = $now ;

        $model = new  Airplane();
        $add_data = compact('name','modify_time','parent_id') ;
        if($id){
            $model->baseUpdate($model::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = $now ;
            $model->baseInsert($model::tableName(),$add_data);
        }

        $url = '/shouce/airplane-list';
        return  $this->redirect($url);
    }

    // 删除飞机信息
    public function actionDelAirplane(){

        $id = $this->getParam('id') ;
        $model = new Airplane();
        $update_data['is_open'] = 0 ;

        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1] ) ;
    }


    // 删除飞机信息
    public function actionDel(){

        $id = $this->getParam('id') ;
        $model = new Shouce();
        //$update_data['is_open'] = 0 ;

        $model->baseDelete($model::tableName(),'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1] ) ;
    }


}
