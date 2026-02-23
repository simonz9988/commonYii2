<?php

namespace backend\controllers;

use backend\components\Resource;
use backend\models\Admin;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use Yii;
use backend\components\Page;

/**
 * Class    BackendController
 * @package backend\controllers
 * Desc     后台公共的控制器
 * User     zhudl
 * Date     2017-3-13
 */
class BackendController extends \common\controllers\BaseController
{
    public $page_num = 20;
    public $page_rows = 20;

    /**
     * 不需要校验权限的请求
     * @var array
     */
    private $no_check_pri_acts = array("/site/login","/site/logout",'/site/dologin');

    public $currentMenuName = '工作台';
    public $selectedLevel0Key = '';//选中一级菜单的key值
    public $selectedLevel1Key = '';//选中二级菜单的key值
    public $selectedLevel0Name = '';//选中一级菜单的名称
    public $selectedLevel1Name = '';//选中二级菜单的名称
    public $selectedLevel0MenuUniqueKey = '';//选中二级菜单的名称
    public $allAllowedMenusCate = array();//所有允许的菜单

    public $adminUserInfo = [] ;
    public $adminMenuList = [] ;
    public $isEmpty = false ;

    //需要额外引用的js,css
    public $resource = array();

    /**
     * 左侧菜单
     * @return array
     */
    private function getLeftMenus(){
        $username =  Yii::$app->session->get('username');
        $is_super = Yii::$app->session->get('is_super');

        $admin_rule_obj = new AdminRule();
        if($is_super){
            $menu = $admin_rule_obj->getSuperLeftMenu();
        }else{
            $menu = $admin_rule_obj->getAdminUserMenu($username);
        }

        return $menu;
    }

    /**
     * 权限判断
     * @param $url_action
     * @return bool
     */
    protected function checkPrivilege($url_action){
        $username =  Yii::$app->session->get('username');
        $admin_user_role = new AdminUserRole();
        $user_privilege = $admin_user_role->getUserPrivileges($username);

        $has_privilege = $user_privilege ? in_array($url_action,$user_privilege) : false;
    
        // 权限列表
        Yii::$app->view->params['auth_menus'] = $user_privilege;
        return $has_privilege;
    }

    /**
     * Notes:获取面包屑
     * @param $url_action
     * @return array
     */
    private function getBreadcrumbs($url_action){
        $admin_rule_obj = new AdminRule();
        $breadcrumbs = $admin_rule_obj->getTreeRuleList($url_action);

        return $breadcrumbs;
    }

    /**
     * Notes:编辑页标题
     * @param $breadcrumbs
     * @return string
     */
    private function getEditPageTitle($breadcrumbs){
        $page_title_data = array_pop($breadcrumbs);
        $page_title = isset($page_title_data['name']) ? $page_title_data['name'] : '';

        return $page_title;
    }

    /**
     * 或者登录状态用户信息
     * @return mixed
     */
    public function getLoginUser(){
        $login_user = Yii::$app->session->get('login_user');
        return $login_user;
    }

    /**
     * 用户id
     * @return int
     */
    public function getLoginUserId(){
        $login_user = Yii::$app->session->get('login_user');
        $login_user_id = isset($login_user['id']) ? $login_user['id'] : 0;
        return $login_user_id;
    }

    /**
     * 检查登录状态
     */
    public function checkLogin(){

        if(!$this->getLoginUser()){
            header("Location:/site/login");
            exit();
        }
    }

    /**
     * beforeAction() 请求之前的数据验证
     * @param \yii\base\Action $action
     * @return bool
     * @throws UnauthorizedHttpException
     */
    public function beforeAction($action)
    {
        // 主控制器验证
        if (parent::beforeAction($action)) {
            $url_action = '/'.$action->controller->id . '/' . $action->id;
            Yii::$app->view->params['isEmpty'] = false  ;
            if(!in_array($url_action,$this->no_check_pri_acts)){
                $this->checkLogin();

                if(in_array($url_action,['/cash/add-out','/cash/add-batch-out'])){
                    Yii::$app->view->params['isEmpty'] = true  ;
                }

                $controller_name = $action->controller->id;
                $function_name = $action->id;
                // 判断权限是否允许
                $adminUserInfo = Yii::$app->session->get('login_user');

                Yii::$app->view->params['is_super']  = $adminUserInfo['username']=='admin' ? 1 : 0;
                $admin_role_model = new AdminRole() ;
                $check_privilege_rst = $admin_role_model->checkRolePrivilege($adminUserInfo,$controller_name,$function_name);
                if(!$check_privilege_rst){
                    $this->showerror(['code'=>'100004','msg'=>getErrorDictMsg('100004')]) ;
                }

                // 权限列表
                Yii::$app->view->params['auth_menus'] = $admin_role_model->getUserPrivilegeList($adminUserInfo);


                $this->adminUserInfo = $adminUserInfo ;
                Yii::$app->view->params['adminUserInfo'] = $this->adminUserInfo ;
                $admin_model = new Admin() ;
                $this->adminMenuList = $admin_model->getUserMenu();
                Yii::$app->view->params['adminMenuList'] = $this->adminMenuList ;
                $this->currentMenuName = '工作台';
                Yii::$app->view->params['currentMenuName'] = $this->currentMenuName ;
                $this->allAllowedMenusCate = $admin_model->getAllowedMenusCate($this->adminMenuList);

                Yii::$app->view->params['allAllowedMenusCate'] = $this->allAllowedMenusCate ;

                $admin_privilege_model = new AdminPrivilege() ;
                $privilegRowInfo = $admin_privilege_model->getRow($controller_name,$function_name) ;

                if($privilegRowInfo){
                    $this->currentMenuName = $privilegRowInfo['name'];
                    Yii::$app->view->params['currentMenuName'] = $this->currentMenuName ;
                    //获取当前菜单的顶级菜单
                    $levelInfo = $admin_privilege_model->getLevelInfo($privilegRowInfo);

                    $this->selectedLevel0Key = $levelInfo['level0Key'];
                    Yii::$app->view->params['selectedLevel0Key'] = $this->selectedLevel0Key ;

                    $this->selectedLevel0MenuUniqueKey = $levelInfo['level0MenuUniqueKey'];
                    Yii::$app->view->params['selectedLevel0MenuUniqueKey'] = $this->selectedLevel0MenuUniqueKey ;

                    $this->selectedLevel1Key = $levelInfo['level1Key'];
                    Yii::$app->view->params['selectedLevel1Key'] = $this->selectedLevel1Key ;

                    $this->selectedLevel0Name = $levelInfo['level0Name'];
                    Yii::$app->view->params['selectedLevel0Name'] = $this->selectedLevel0Name ;

                    $this->selectedLevel1Name = $levelInfo['level1Name'];
                    Yii::$app->view->params['selectedLevel1Name'] = $this->selectedLevel1Name ;
                }

            }
            return true ;

        } else {
            return false;
        }
    }

    /**
     * 加载js, css等静态资源
     * @param $ctl
     * @param $act
     * @return array
     */
    public function loadResource($ctl,$act){
        $component = new Resource();
        $this->resource = $component->getResource($ctl,$act);
        Yii::$app->view->params['resource'] = $this->resource ;
    }


    //异常处理方法
    public function showerror($message=array(), $is_exception = false)
    {
        if(Yii::$app->request->isAjax) {
            $this->returnJson($message);
        }else{
            $code = isset($message['code']) ? $message['code'] : 999 ;
            $msg = isset($message['msg'])&& $message['msg'] ? $message['msg'] : getErrorDictMsg($code);

            if($is_exception == true){
                throw new \yii\web\HttpException($code, $message);
            }else{
                $data = array('code' => $code, 'message' => $msg);
                header("Content-type:text/html;charset=utf-8");
                echo $this->render('/site/error', $data);
            }
            exit;
        }
    }

    /**
     * 没有权限异常处理
     * @param $message
     * @param int $code
     * @param null $url_action
     */
    public function handleUnauthException($url_action){
        if($url_action != '/site/error'){
            $this->layout = $url_action == '/site/index'? "empty.php" : 'empty.php';
            echo $this->render('/site/401');
            exit();
        }
    }

    /**
     * 获取分页参数
     * @param $total_rows
     * @param array $parameter
     * @param string $pageTag
     * @return array|string
     */
    public function getPageData($total_rows,$parameter = [],$pageTag = 'page',$page_num=0){
        $page_num  = $page_num > 0 ? $page_num :$this->page_num ;
        $myPage = new Page();
        $myPage->init(array('totalRows' => $total_rows, 'listRows' => $page_num,'parameter' => $parameter, 'pageTag' => $pageTag));
        $pageData = $myPage->showResForBackstage();

        return $pageData;
    }

    /**
     * 获取分页参数（此方法针对一个表中有大量数据的的情况，该方法分页只有上一页下一页无总数查询）
     */
    public function getPageDataForBigData($parameter = [],$pageTag = 'page'){
        $myPage = new Page();
        $myPage->init(array('totalRows'=>0, 'listRows' => $this->page_num,'parameter' => $parameter, 'pageTag' => $pageTag));
        $pageData = $myPage->showPageDataForBigData();
        return $pageData;
    }

    /**
     * 返回搜索参数
     * @return array
     */
    public function returnSearchArr(){

        $total_data = $_GET;

        $search_arr = [];
        if($total_data){
            foreach($total_data as $k=>$v){
                if($k !="page" && $k !="p"){
                    $search_arr[$k] = $v;
                }

            }
        }
        return $search_arr ;
    }

    public function returnParams($searchArr,$where_key,$like_key){

        $params = [] ;

        if($where_key){
            foreach($where_key as $v){
                $value = isset($searchArr[$v]) ? $searchArr[$v] : '' ;

                if($value){
                    $params['where_arr'][$v] = $value;
                }
            }
        }

        if($like_key){
            foreach($like_key as $v){
                $value = isset($searchArr[$v]) ? $searchArr[$v] : '' ;

                if($value){
                    $params['like_arr'][$v] = $value;
                }
            }
        }

        return $params ;

    }

}
