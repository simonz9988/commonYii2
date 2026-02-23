<?php
namespace backend\controllers;

use backend\models\Admin;
use common\components\GoogleAuthenticator;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use phpCAS;


/**
 * Site controller
 */
class SiteController extends BackendController
{
    protected $menu_key = 'shop_menu_system_manage';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login','logout', 'index','dologin', 'error'],
                        'allow' => true,
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    //'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }


    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Login
     * @return \yii\web\Response
     */
    public function actionLogin(){

        $this->layout = 'empty';
        $login_user = $this->getLoginUser();
        if($login_user){
            return $this->redirect('/site/index');
        }
        return $this->render('login') ;
    }

    // 执行登录操作
    public function actionDologin(){

        $username = $this->postParam('username');
        $password = $this->postParam('password');
        $google_code = $this->postParam('google_code');

        if(!$username || !$password){
            debug('请将信息填写完整');exit;
        }
        $admin_model = new Admin() ;
        $info = $admin_model->getUserInfoByPassword($username,$password);
        if(!$info){
            debug('用户名或者密码不存在!');
            exit;
        }

        // 验证谷歌验证码是否正确
        $ga = new GoogleAuthenticator();

        $checkResult = $ga->verifyCode($info['google_private_key'], $google_code, 2);
        if(!$checkResult){
            //debug('谷歌验证码不正确!');
            //exit;
        }

        Yii::$app->session->set('login_user',$info);
        $url = '/site/index';
        $this->redirect($url);
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        //Yii::$app->session->destroy();
        Yii::$app->session->set('login_user',[]);
        return $this->goHome();
    }
}
