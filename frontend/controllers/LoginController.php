<?php
namespace frontend\controllers;

use common\controllers\BaseController;
use common\models\Member;
use common\models\Shouce;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;

/**
 * Login controller
 */
class LoginController extends BaseController
{

    public $layout ='empty';
    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        echo 111;exit;
        return $this->render('index');
    }

    public function actionRegister(){
        $username = trim($this->getParam('username'));
        $password = $this->getParam('password');

        $model = new Member();
        $params['cond'] = 'username=:username';
        $params['args'] = [':username'=>$username];
        $info = $model->findOneByWhere('sea_user',$params);

        if($info){
            echo json_encode(['code'=>'-1','msg'=>'repeat']) ;exit;
        }

        $add_data['username'] = $username;
        $add_data['password'] = md5($password);
        $model->baseInsert('sea_user',$add_data);
        echo json_encode(['code'=>'1','msg'=>'success']) ;exit;

    }

    public function actionDoLogin(){

        $username = trim($this->getParam('username'));
        $password = $this->getParam('password');
        $model = new Member();
        $params['cond'] = 'username=:username AND password=:password';
        $params['args'] = [':username'=>$username,':password'=>md5($password)];
        $info = $model->findOneByWhere('sea_user',$params);
        if($info){
            echo json_encode(['code'=>'1','msg'=>'success','data'=>$info]) ;exit;
        }else{
            echo json_encode(['code'=>'-1','msg'=>'incorrect password']) ;exit;
        }

    }
}
