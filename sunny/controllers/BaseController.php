<?php

namespace sunny\controllers;

use backend\components\Resource;
use common\models\Member;
use JmesPath\Parser;
use Lcobucci\JWT\Exception;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\ValidationData;
use Yii;


class BaseController extends \common\controllers\BaseController
{
    public $page_num = 20;
    public $page_rows = 20;

    /**
     * 不需要校验权限的请求
     * @var array
     */
    private $no_check_pri_acts = array("/site/login","/site/error","/site/logout",'/site/do-login','/site/do-logout','/site/check-is-login');

    public $currentMenuName = '工作台';
    public $selectedLevel0Key = '';//选中一级菜单的key值
    public $selectedLevel1Key = '';//选中二级菜单的key值
    public $selectedLevel0Name = '';//选中一级菜单的名称
    public $selectedLevel1Name = '';//选中二级菜单的名称
    public $selectedLevel0MenuUniqueKey = '';//选中二级菜单的名称
    public $allAllowedMenusCate = array();//所有允许的菜单

    public $adminUserInfo = [] ;
    public $adminMenuList = [] ;
    public $loginUserInfo = [] ;

    //需要额外引用的js,css
    public $resource = array();

    /**
     * 或者登录状态用户信息
     * @return mixed
     */
    public function getLoginUser(){



        $login_user = Yii::$app->session->get(LOGIN_SESSION_NAME);
        $this->loginUserInfo = $login_user ;
        return $login_user;
    }

    /**
     * 用户id
     * @return int
     */
    public function getLoginUserId(){

        $member_obj = new Member();
        $login_user_id = $member_obj->getLoginUserIdByAccessToken();
        return $login_user_id ;
    }

    /**
     * 获取用户手机号码
     */
    public function getUserMobile(){
        $user_id = $this->getLoginUserId();
        if(!$user_id){
            return  '';
        }

        $user_obj = new Member();
        $mobile = $user_obj->getMobileByUserId($user_id);
        return $mobile ;
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

            if(!in_array($action->controller->id,['news','service'])){
                if(!in_array($url_action,$this->no_check_pri_acts)){
                    $check_login_res =  $this->getLoginUserId();
                    if(!$check_login_res){
                        exit($this->returnJson(['code'=>100001,'msg'=>getErrorDictMsg(100001)]));
                    }
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



}