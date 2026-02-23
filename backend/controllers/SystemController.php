<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminRolePrivilege;
use backend\models\AdminTotalApiKey;
use backend\models\AdminUserApiKey;
use common\components\GoogleAuthenticator;
use common\components\PHPGangsta_GoogleAuthenticator;
use common\models\AdminBank;
use common\models\AdminMappingCompany;
use common\models\SiteConfig;
use common\models\SunnyCompany;

/**
 * System
 */
class SystemController extends BackendController
{
    public function actionMenuList(){

        $searchArr = array();

        $controller= $this->getParam('controller');
        if($controller){
            $params['where_arr']['controller'] = $controller ;
        }
        $searchArr['controller'] = $controller ;

        $function= $this->getParam('function');
        if($function){
            $params['where_arr']['function'] = $function ;
        }
        $searchArr['function'] = $function ;
        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' modify_time desc ';
        $params['where_arr']['is_open'] = 1 ;
        $params['where_arr']['category'] = 'menu' ;
        $model = new AdminPrivilege() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params,  $model::getDb());

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;
        //debug($page_data,1);
        return $this->render('menuList',$renderData) ;
    }

    public function actionAddMenu(){
        $parent_id = 0 ;
        $info = array();
        $id = $this->getParam('id');

        $admin_privilege_model = new AdminPrivilege() ;

        if($id) {
            $params['where_arr']['id'] = $id;
            $params['return_type'] = 'row';

            $info = $admin_privilege_model->findByWhere($admin_privilege_model::tableName(), $params);
            $parent_id = isset($info['parent_id'])?$info['parent_id']:0;
        }

        $levelTopArr =  $admin_privilege_model->getMenuByLevel(0);
        $renderData['levelTopArr'] = $levelTopArr ;
        $renderData['parent_id'] = $parent_id ;
        $renderData['info'] = $info ;

        //获取所有菜单分类
        $admin_menu_cate_model = new AdminMenuCate() ;

        $allMenuCate = $admin_menu_cate_model->getAll();

        $renderData['allMenuCate'] = $allMenuCate ;
        $this->loadResource('system','actionAddMenu') ;

        return $this->render('addMenu',$renderData);
    }

    //添加菜单
    public function actionDoAddMenu(){
        $controller =  $this->getParam('controller');
        $function   =  $this->getParam('function');
        $parent_id  =  intval($this->getParam('parent_id'));
        $name       =  $this->getParam('name');
        $sort       =  $this->getParam('sort');
        $id       =    $this->getParam('id');
        $is_open       =    $this->getParam('is_open');

        $addData = array(
            'controller' => $controller,
            'function'   => $function,
            'parent_id'  => $parent_id,
            'name'       => $name ,
            'sort'       => $sort,
            'is_open'       => $is_open,

        );

        $menu_cate_id = $this->getParam('menu_cate_id');
        $menu_cate_model = new AdminMenuCate() ;
        $menu_cate_unique_key = $menu_cate_model->getUniqueKeyById($menu_cate_id);
        $menu_cate_name = $menu_cate_model->getNameById($menu_cate_id);

        $addData['menu_cate_id'] = $menu_cate_id ;
        $addData['menu_cate_unique_key'] = $menu_cate_unique_key ;
        $addData['menu_cate_name'] = $menu_cate_name ;
        $privilege_model = new AdminPrivilege() ;
        if($id){

            $privilege_model->updatePrivilege($addData,$id,'menu');
        }else{
            $privilege_model->addPrivilege($addData) ;
        }

        $url = '/system/menu-list';
        return  $this->redirect($url);
    }

    public function actionDelMenu(){
        $id = $this->getParam('id');
        if($id){
            $addData['is_open'] = 0 ;
            $model = new AdminPrivilege();
            $model->baseUpdate($model::tableName(),$addData, "id = :id",array(":id"=>$id));
        }
        return $this->returnJson(array('code'=>1));
    }

    //权限列表
    public function actionOperationList(){

        $searchArr = array();

        $controller= $this->getParam('controller');
        if($controller){
            $params['where_arr']['controller'] = $controller ;
        }
        $searchArr['controller'] = $controller ;

        $function= $this->getParam('function');
        if($function){
            $params['where_arr']['function'] = $function ;
        }
        $searchArr['function'] = $function ;
        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' modify_time desc ';
        $params['where_arr']['is_open'] = 1 ;
        $params['where_arr']['category'] = 'operation';
        $admin_privilege_model = new AdminPrivilege();
        $list = $admin_privilege_model->findByWhere( $admin_privilege_model::tableName(),$params);
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $admin_privilege_model->findByWhere( $admin_privilege_model::tableName(), $params);
        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return $this->render('operationList',$renderData) ;
    }

    //新增权限
    public function actionAddOperation(){

        $parent_id = 0 ;
        $info = array();
        $id = $this->getParam('id');
        $privilege_model = new AdminPrivilege() ;
        if($id) {
            $params['where_arr']['id'] = $id;
            $params['return_type'] = 'row';
            $info = $privilege_model->findByWhere($privilege_model::tableName(), $params);
            $parent_id = isset($info['parent_id'])?$info['parent_id']:0;
        }
        $levelTopArr =  $privilege_model->getMenuByLevel(0);

        //权限级别最多四级别
        foreach($levelTopArr as $k=>$v){

            $sun_arr = $privilege_model->getMenuByParentId($v['id']);

            if($sun_arr){
                foreach($sun_arr as $son_k=>$son_v){
                    $sun_sun_arr = $privilege_model->getMenuByParentId($son_v['id']);
                    $sun_arr[$son_k]['all_sun'] = $sun_sun_arr ;
                }
            }

            $levelTopArr[$k]['all_sun'] = $sun_arr ;

        }


        $renderData['levelTopArr'] = $levelTopArr ;
        $renderData['parent_id'] = $parent_id ;
        $renderData['info'] = $info ;
        $this->loadResource('system','actionAddMenu');
        return $this->render('addOperation',$renderData);

    }

    public function actionDoAddOperation(){

        $controller =  $this->getParam('controller');
        $function   =  $this->getParam('function');
        $parent_id  =  intval($this->getParam('parent_id'));
        $name       =  $this->getParam('name');
        $sort       =  $this->getParam('sort');
        $id       =    $this->getParam('id');

        $addData = array(
            'controller' => $controller,
            'function'   => $function,
            'parent_id'  => $parent_id,
            'name'       => $name ,
            'sort'       => $sort,

        );

        $model = new AdminPrivilege() ;
        if($id){

            $rst = $model->updatePrivilege($addData,$id,'operation');
        }else{
            $rst = $model->addPrivilege($addData) ;
        }

        $url = '/system/operation-list';
        return $this->redirect($url);


    }


    //所有角色
    public function actionRoleList(){

        $function= $this->getParam('function');
        if($function){
            $params['where_arr']['function'] = $function ;
        }
        $searchArr['function'] = $function ;
        $renderData['searchArr'] = $searchArr;


        $model = new AdminRole();
        $curr_page =  getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['where_arr']['is_open'] = 1 ;
        $list = $model->findByWhere( $model::tableName(),$params);
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params);
        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return $this->render('roleList',$renderData);
    }


    public function actionAddRole(){

        $model = new AdminRole();
        $info = array();
        $id = getParam('id');
        if($id) {
            $params['where_arr']['id'] = $id;
            $params['return_type'] = 'row';
            $info = $model->findByWhere($model::tableName(), $params);

        }

        $renderData['info'] = $info ;

        return  $this->render('addRole',$renderData);
    }

    public function actionDoAddRole(){
        $id = getParam('id');

        $role_key = getParam('role_key');
        $role_value = getParam('role_value');
        $role_description = getParam('role_description');
        $sort = getParam('sort');
        $is_open = getParam('is_open');


        $addData = compact('role_key','role_value','role_description','sort','is_open');

        $now = date('Y-m-d H:i:s');
        $model = new AdminRole();
        if($id){
            $addData['modify_time'] = $now ;
            $model->baseUpdate($model::tableName(),$addData,"id = :id",array(":id"=>$id));
        }else{
            $addData['create_time'] = $now ;

            $model->baseInsert($model::tableName(),$addData);
        }
        $url ='/system/role-list';
        $this->redirect($url);

    }

    //用户列表
    public function actionUserList(){

        $username= $this->getParam('username');
        if($username){
            $params['like_arr']['username'] = $username ;
        }
        $searchArr['username'] = $username ;
        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['where_arr']['is_open'] = 1 ;
        $model = new Admin();
        $list = $model->findByWhere( $model::tableName(),$params);

        $google_components = new GoogleAuthenticator();
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['role_name'] = $model->getUserRoleName($v['role_id']);
                $list[$k]['google_img'] = $google_components->getQRCodeGoogleUrl($v['username'].'@'.$_SERVER['HTTP_HOST'], $v['google_private_key'], 'googleAuth');
            }
        }

        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total =$model->findByWhere( $model::tableName(), $params);
        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return $this->render('userList',$renderData);
    }

    public function actionAddUser(){

        $admin_role_model = new AdminRole() ;
        //查询得到所有角色信息
        $roleParams['where_arr']['is_open'] = 1 ;
        $all_role_info = $admin_role_model->findByWhere($admin_role_model::tableName(),$roleParams);

        $id = $this->getParam('id');
        $info = array() ;
        if($id){
            $admin_model = new Admin() ;
            $userParams['where_arr']['id'] = $id ;
            $userParams['return_type'] = 'row';
            $info = $admin_model->findByWhere($admin_model::tableName(),$userParams);

        }

        $renderData['all_role_info'] = $all_role_info ;
        $renderData['info'] = $info ;

        // 查询所有公司信息
        $company_obj = new SunnyCompany();
        $company_list = $company_obj->getAllAllowed();
        $renderData['company_list'] = $company_list ;
        $mapping_obj = new AdminMappingCompany();
        $renderData['company_ids'] = $info ? $mapping_obj->getCompanyIdsByAdminId($info['id']):[];

        return  $this->render('addUser',$renderData);
    }

    public function actionDoAddUser(){
        //$id = getP阿扰民（）
        $id = $this->getParam('id');
        $username = getParam('username');
        $nickname = getParam('nickname');
        $password =getParam('password');
        $role_id = getParam('role_id');
        $sort = getParam('sort');
        $is_open = getParam('is_open');
        $email = getParam('email');
        $secret_key = getParam('secret_key');
        $mark_key = getParam('mark_key');
        $company_type= getParam('company_type');
        $company_type = $company_type ? $company_type:'PART';
        $addData = compact('username','nickname','role_id','sort','is_open','email','secret_key','mark_key','company_type');
        $now = date('Y-m-d H:i:s');

        $admin_model = new Admin();
        $google_component = new GoogleAuthenticator();
        if($id){
            //更新
            $addData['update_time'] = $now ;
            if($password){
                $addData['password'] = md5($password) ;
            }

            $admin_params['cond'] = 'id=:id';
            $admin_params['args'] = [':id'=>$id];
            $info = $admin_model->findOneByWhere($admin_model::tableName(),$admin_params,$admin_model::getDb());
            if(!$info['google_private_key']){
                $addData['google_private_key'] = $google_component->createSecret();
            }
            $admin_model->baseUpdate($admin_model::tableName(),$addData, "id = :id",array(":id"=>$id) );
        }else{
            //新增
            $password = md5($password);
            $addData['password'] = $password;
            $addData['create_time'] = $now ;
            $addData['google_private_key'] = $google_component->createSecret();
            $id = $admin_model->baseInsert($admin_model::tableName(),$addData);
        }

        $company_id = $_POST['company_id'];
        $mapping_obj = new AdminMappingCompany();
        $mapping_obj->deleteByAdminId($id);

        if($company_type =='PART'){

            if($company_id){
                foreach($company_id as $v){
                    $mapping_add_data['admin_id']= $id;
                    $mapping_add_data['company_id']= $v;
                    $mapping_add_data['create_time']= date('Y-m-d H:i:s');
                    $mapping_add_data['modify_time']= date('Y-m-d H:i:s');
                    $mapping_obj->baseInsert($mapping_obj::tableName(),$mapping_add_data);
                }
            }
        }
        $url ='/system/user-list';
        return  $this->redirect($url);
    }

    //编辑角色权限信息
    public function actionEditRolePrivilege(){
        $role_id = getParam('role_id');
        //返回所有节点
        $admin_privilege_model = new AdminPrivilege();
        $allNode = $admin_privilege_model->allNode('arr');

        //用户拥有的权限
        $params['where_arr']['role_id'] = $role_id ;
        $role_privileges     = $admin_privilege_model->findByWhere('sea_admin_role_privilege',$params);

        $ids_arr = array();
        foreach($role_privileges as $v){
            $ids_arr[] = $v['privilege_id'] ;
        }

        //处理用户已经选择的权限节点
        foreach($allNode as $k=>$v){
            $ch_rs = in_array($v['id'], $ids_arr);
            if($ch_rs){

                $allNode[$k]['checked'] = 'true' ;
            }
        }
        $zNodes_str = json_encode($allNode);
        //pre($allNode,1);
        $renderData['zNodes_str'] = $zNodes_str ;
        $renderData['role_id'] = $role_id ;
        return  $this->render('editRolePrivilege',$renderData);
    }



    //保存角色的权限信息
    public function actionAjaxSaveRolePrivilege(){

        $role_id = getParam('role_id');
        $privileges_str = trim(getParam('privileges_str'),'@');
        $pri_arr = explode('@', $privileges_str) ;
        $admin_privilege_model = new AdminPrivilege() ;
        $rst = $admin_privilege_model->saveRolePrivileges($role_id,$pri_arr);


        if($rst){
            $arr  = array('info'=>'succ','url'=>url('/system/role-list')) ;
        }else{
            $arr  = array('info'=>'err');
        }

        echo json_encode($arr) ;exit;
    }

    public function actionDelRole(){
        //step1判断有没有用户
        $id = getParam('id');
        $params['where_arr']['role_id'] = $id ;
        $userInfo = AdminUserRole::model()->findByWhere(AdminUserRole::model()->tableName(),$params);
        if($userInfo){
            pre('不能删除，该角色下有用户存在',1);
        }

        AdminRole::model()->baseDelete(AdminRole::model()->tableName(),'id=:id',array(":id"=>$id));
        $url = ADMIN_BASE_URL.'/system/roleList';
        $this->redirect($url);
    }


    public function actionWebsiteConfig(){
        $params['where_arr']['id'] = 1 ;
        $params['return_type'] = 'row';
        $info = Config::model()->findByWhere(Config::model()->tableName(),$params);
        $renderData['info'] = $info ;
        $this->render('/system/websiteConfig',$renderData);
    }

    public function actionDoWebsiteConfig(){
        $data  = $_POST ;
        Config::model()->baseUpdate(Config::model()->tableName(),$data,'id=:id',array(":id"=>1));
        $url = ADMIN_BASE_URL.'/system/websiteConfig';
        $this->redirect($url);
    }

    public function actionAjaxResetPassword(){
        $password = getParam('password');
        $password2 = getParam('password2');
        $repeartpassword2 = getParam('repeartpassword2');

        //step1判断旧密码是否准确
        $username = $this->adminUserInfo['username'];
        $admin_model = new Admin() ;
        $info = $admin_model->getUserInfoByPassword($username,$password);
        if(!$info){
            responseJson(array('code'=>'10001','msg'=>'旧密码不正确'));
        }

        if($password2 !=$repeartpassword2){
            responseJson(array('code'=>'10001','msg'=>'新密码两次密码不一直'));
        }
        $updateDate['password'] = md5($password2);
        $updateDate['update_time'] = date('Y-m-d H:i:s');
        $obj = new Admin();
        $rst = $obj->baseUpdate($obj::tableName(),$updateDate,'username=:username',array(':username'=>$username));
        if($rst){
            responseJson(array('code'=>1));
        }else{
            responseJson(array('code'=>'10002','msg'=>'修改失败'));
        }


    }

    public function actionMenuCate(){

        $searchArr = array();

        $name= $this->getParam('name');
        if($name){
            $params['like_arr']['name'] = $name ;
            $searchArr['name'] = $name ;
        }


        $renderData['searchArr'] = $searchArr;

        $curr_page =  $this->getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['where_arr']['is_open'] = 1 ;
        $menu_cate_model = new AdminMenuCate();
        $list = $menu_cate_model->findByWhere( $menu_cate_model::tableName(),$params);
        
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $menu_cate_model->findByWhere( $menu_cate_model::tableName(), $params);

        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return  $this->render('menuCate',$renderData);

    }

    public function actionAddMenuCate(){

        $id = $this->getParam('id');
        $info = array();

        $model = new AdminMenuCate() ;
        if($id){
            $params['where_arr']['id'] = $id ;
            $params['return_type']= 'row';
            $info =$model->findByWhere($model::tableName(),$params);
        }
        $renderData['info'] = $info ;

        return $this->render('addMenuCate',$renderData);
    }

    public function actionDoAddMenuCate(){

        $id = $this->getParam('id');

        $name = $this->getParam('name');
        $unique_key = $this->getParam('unique_key');
        $sort = $this->getParam('sort');
        $status = $this->getParam('status');
        $addData = compact('name','unique_key','sort','status');

        $now = date('Y-m-d H:i:s');

        $model = new AdminMenuCate();

        if($id){
            $addData['update_time'] = $now ;
            $model->baseUpdate($model::tableName(),$addData,"id = :id",array(":id"=>$id));
        }else{

            $params['where_arr']['is_open'] =1 ;
            $params['where_arr']['unique_key'] =$unique_key ;
            $params['return_type'] = 'row';
            $info = $model->findByWhere($model::tableName(),$params,$model::getDb());
            if($info){
                return $this->showerror(['code'=>'100011']);
            }

            $addData['is_open'] = 1 ;
            $addData['create_time'] = $now ;
            $model->baseInsert($model::tableName(),$addData);
        }
        $url = '/system/menu-cate';
        return $this->redirect($url);
    }

    // 删除菜单分类
    public function actionDelMenuCate(){
        $id = $this->getParam('id');
        $update_date['is_open'] = 0 ;
        $update_date['update_time'] = date('Y-m-d H:i:s') ;
        $model = new AdminMenuCate();
        $model->baseUpdate($model::tableName(),$update_date,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    public function actionSiteConfig(){

        $config_key= getParam('config_key');
        if($config_key){
            $params['like_arr']['config_key'] = $config_key ;
        }
        $searchArr['config_key'] = $config_key ;

        $config_desc= getParam('config_desc');
        if($config_desc){
            $params['like_arr']['config_desc'] = $config_desc ;
        }
        $searchArr['config_desc'] = $config_desc ;
        $renderData['searchArr'] = $searchArr;

        $model = new SiteConfig();
        $curr_page =  getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = 'id desc';
        $list = $model->findByWhere( $model::tableName(),$params);
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = $model->findByWhere( $model::tableName(), $params);
        $page_data = $this->getPageData($total);
        $renderData['page_data'] = $page_data;

        return $this->render('siteConfig',$renderData);
    }

    public function actionAddSiteConfig(){
        $id = getParam('id');
        $info = array();
        if($id){
            $model = new SiteConfig();
            $params['where_arr']['id'] = $id ;
            $params['return_type'] = 'row';
            $info = $model->findByWhere($model::tableName(),$params) ;
        }
        $renderData['info'] = $info;
        $this->loadResource('system','actionAddSiteConfig');
        return  $this->render('addSiteConfig',$renderData);
    }

    public function actionDoAddSiteConfig(){


        $id = getParam('id');

        $config_key = getParam('config_key');
        $config_desc = getParam('config_desc');
        $config_value = getParam('config_value');
        $insert_sql = getParam('insert_sql');
        $addData = compact('config_key','config_desc','config_value','insert_sql');

        $now = date('Y-m-d H:i:s');
        $model = new SiteConfig() ;
        if($id){
            $addData['update_time'] = $now ;
            unset($addData['config_key']);
            $model->baseUpdate($model::tableName(),$addData,"id = :id",array(":id"=>$id));
        }else{

            $params['where_arr']['config_key'] =$config_key ;
            $params['return_type'] = 'row';
            $info = $model->findByWhere($model::tableName(),$params);
            if($info){
                debug('不能重复配置，该配置key值已存在',1);
            }

            $addData['create_time'] = $now ;
            $model->baseInsert($model::tableName(),$addData);
        }


        $url = '/system/site-config';
        return $this->redirect($url);

    }

    public function actionTestCateList(){

        $name = getParam('name');
        if($name){
            $params['like_arr']['name'] = $name ;
        }
        $searchArr['name'] = $name;

        $unique_key = getParam('unique_key');
        if($unique_key){
            $params['like_arr']['unique_key'] = $unique_key ;
        }
        $searchArr['unique_key'] = $unique_key;

        $parent_id = getParam('parent_id');
        if($parent_id){
            $params['where_arr']['parent_id'] = $parent_id ;
        }
        $searchArr['parent_id'] = $parent_id ;

        $renderData['searchArr'] = $searchArr ;

        $curr_page =  getParam('p');
        $curr_page = $curr_page ? $curr_page : 1;
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' id desc ';
        $params['where_arr']['is_open'] = 1 ;
        $list = CommonCate::model()->findByWhere( CommonCate::model()->tableName(),$params, $this->db);
        $renderData['list'] =$list;

        $params['return_type'] = 'num';
        unset( $params['page']);
        $total = CommonCate::model()->findByWhere( CommonCate::model()->tableName(), $params, $this->db);
        $page = Base::model()->getPage($total,$this->page_rows) ;
        $renderData['page'] =$page;


        //顶级分类的ID
        $topParams['where_arr']['parent_id'] = 0 ;
        $topParams['where_arr']['is_open'] = 1 ;
        $allInfo = CommonCate::model()->findByWhere(CommonCate::model()->tableName(),$topParams,$this->db);
        $renderData['allInfo'] = $allInfo ;

        $this->render('/system/testCateList',$renderData);


    }

    //用添加通用分类
    public function actionAddTestCate(){


        $id = getParam('id');
        $info = CommonCate::model()->getRowInfoById($id);
        $renderData['info'] = $info ;

        //顶级分类的ID
        $params['where_arr']['parent_id'] = 0 ;
        $params['where_arr']['is_open'] = 1 ;
        if($id){
            $params['not_where_arr']['id'] = $id ;
        }
        $allInfo = CommonCate::model()->findByWhere(CommonCate::model()->tableName(),$params,$this->db);
        $renderData['allInfo'] = $allInfo ;

        //所有来源网站
        $all_web_name = SiteConfig::model()->getByKey('common_all_from_web_name','douhao_fenge');
        $renderData['all_web_name'] = $all_web_name ;

        $this->loadResource('system','actionAddTestCate');
        $this->render('/system/addTestCate',$renderData);
    }


    public function actionDoAddTestCate(){

        $id = getParam('id');

        $name = getParam('name');
        $parent_id = getParam('parent_id');
        $real_id = getParam('real_id');
        $unique_key = getParam('unique_key');
        $sort = getParam('sort');
        $status = getParam('status');
        $from_web_name = getParam('from_web_name');
        $addData = compact('name','parent_id','real_id','unique_key','sort','status','from_web_name');

        if($id){
            //更新
            $addData['is_open']= 1 ;
            CommonCate::model()->baseUpdate(CommonCate::model()->tableName(),$addData,"id = :id",array(":id"=>$id));
        }else{
            //新增
            $addData['is_open']= 1 ;
            CommonCate::model()->baseInsert(CommonCate::model()->tableName(),$addData);
        }
        $url = ADMIN_BASE_URL.'/system/testCateList';
        $this->redirect($url);
    }

    public function actionCreateTestJson(){
        $params['where_arr']['is_open'] = 1 ;
        $all =  CommonCate::model()->findByWhere(CommonCate::model()->tableName(),$params);
        $rst = array();
        if($all){
            $all_info = array();
            foreach($all as $v) {
                $all_info[$v['id']] = $v;
            }
            $rst['all_info'] = $all_info;
        }
        $params['where_arr']['parent_id'] = 0 ;
        $params['order_by'] = 'sort ASC ';
        $top_level = CommonCate::model()->findByWhere(CommonCate::model()->tableName(),$params);
        if($top_level){
            foreach($top_level as $k=>$v){
                $params['where_arr']['parent_id']=$v['id'];
                $top_level[$k]['son_info'] = CommonCate::model()->findByWhere(CommonCate::model()->tableName(),$params);
            }
            $rst['formate_info'] = $top_level;
        }

        echo json_encode($rst);exit;

    }

    public function actionCreateTestJson1(){


        $params['where_arr']['parent_id'] = 0 ;
        $params['order_by'] = 'sort ASC ';

        $top_level = CommonCate::model()->findByWhere(CommonCate::model()->tableName(),$params);
        $rst = array();
        if($top_level){
            foreach($top_level as $k=>$v){
                $rst[$k] = array('id'=>$v['real_id'],'name'=>$v['name'],'parent_id'=>0);
                $params['where_arr']['parent_id']=$v['id'];
                $son_info = CommonCate::model()->findByWhere(CommonCate::model()->tableName(),$params);
                $son_info_rst = array();
                if($son_info){
                    foreach($son_info as $son_k=>$son_v){
                        $son_info_rst[$son_k] = array('id'=>$son_v['real_id'],'name'=>$son_v['name'],'parent_id'=>$v['real_id']);
                    }
                }
                $rst[$k]['son_info'] = $son_info_rst;
            }
        }

        echo json_encode($rst);exit;
    }


    //编辑用户允许的账户信息
    public function actionEditRoleOrder(){

        $id = getParam('id');

        //返回所有节点
        $admin_total_obj = new AdminTotalApiKey();
        $allNode = $admin_total_obj->allNode();

        // 用户拥有的所有的权限信息
        $admin_user_api_key_obj = new AdminUserApiKey();
        $ids_arr = $admin_user_api_key_obj->getKeyIdsByAdminUserId($id);

        //处理用户已经选择的权限节点
        foreach($allNode as $k=>$v){
            $ch_rs = in_array($v['id'], $ids_arr);
            if($ch_rs){

                $allNode[$k]['checked'] = 'true' ;
            }
        }
        $zNodes_str = json_encode($allNode);
        //pre($allNode,1);
        $renderData['zNodes_str'] = $zNodes_str ;
        $renderData['admin_user_id'] = $id ;
        return  $this->render('editRoleApiKey',$renderData);
    }

    // 保存后台管理人员对应的
    public function actionAjaxSaveRoleApiKey(){
        $admin_user_id = getParam('admin_user_id');
        $privileges_str = trim(getParam('privileges_str'),'@');
        $pri_arr = explode('@', $privileges_str) ;
        $admin_privilege_model = new AdminUserApiKey() ;
        $rst = $admin_privilege_model->addByAdminUserAndKeyIds($admin_user_id,$pri_arr);


        if($rst){
            $arr  = array('info'=>'succ','url'=>url('/system/user-list')) ;
        }else{
            $arr  = array('info'=>'err');
        }

        echo json_encode($arr) ;exit;
    }

    // 删除权限
    public function actionDelOperation(){
        $id = $this->getParam('id');
        $model = new AdminPrivilege();
        $model->baseDelete($model::tableName(),'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1]) ;
    }

    // 编辑用户银行信息
    public function actionEditUserBank(){
        $admin_user_id = $this->getParam('id');
        $info  = [] ;
        $admin_bank_obj = new AdminBank();
        if($admin_user_id){
            $params['cond'] = 'admin_user_id =:admin_user_id';
            $params['args'] = [':admin_user_id'=>$admin_user_id];
            $info = $admin_bank_obj->findOneByWhere($admin_bank_obj::tableName(),$params,$admin_bank_obj::getDb());
        }
        $renderData['info'] = $info ;
        $renderData['admin_user_id'] = $admin_user_id ;
        $this->loadResource('system','actionEditUserBank');
        return $this->render('/system/editUserBank',$renderData);
    }

    // 保存用户银行信息
    public function actionSaveUserBank(){

        $id = $this->postParam('id');
        $admin_user_id = $this->postParam('admin_user_id');
        $name = $this->postParam('name');
        $telphone = $this->postParam('telphone');
        $address = $this->postParam('address');
        $website = $this->postParam('website');
        $bank_name = $this->postParam('bank_name');
        $bank_address = $this->postParam('bank_address');
        $alipay_no = $this->postParam('alipay_no');
        $bank_no = $this->postParam('bank_no');
        $bank_username = $this->postParam('bank_username');

        $add_data = compact('bank_no','admin_user_id','name','telphone','address','website','bank_name','bank_address','alipay_no','bank_username');
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        $model= new AdminBank();
        if($id){
            $model->baseUpdate($model::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $model->baseInsert($model::tableName(),$add_data);
        }
        return $this->redirect('/system/user-list');
    }
}
