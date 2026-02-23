<?php
namespace sunny\controllers;

use common\models\Ad;
use common\models\Member;
use common\models\SunnyCompany;
use common\models\SunnyManager;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Yii;

class SiteController extends \sunny\controllers\BaseController {
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ],
        ];
    }
    
    /**
     * 首页
     */
    public function actionIndex() {
        $data = [];


        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 执行登录
    public function actionDoLogin(){
        $username = $this->postParam('username');
        $password = ($this->postParam('password'));

        $user_model = new SunnyManager();
        $user_info = $user_model->getInfoByEmailAndPassword($username,$password);

        if(!$user_info){
            return $this->returnJson(['code'=>100002,'msg'=>getErrorDictMsg(100002)]) ;
        }
        Yii::$app->session->set(LOGIN_SESSION_NAME,$user_info);

        //
        $request = Yii::$app->getRequest();
        $signer = new \Lcobucci\JWT\Signer\Hmac\Sha256();//使用Sha256加密，常用加密方式有Sha256,Sha384,Sha512
        $time = time();
        $tokenBuilder = (new Builder())
            ->issuedBy($request->getHostInfo()) // 设置发行人
            ->permittedFor(isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '') // 设置接收
            ->identifiedBy(Yii::$app->security->generateRandomString(10), true) // 设置id
            ->issuedAt($time) // 设置生成token的时间
            ->canOnlyBeUsedAfter($time) // 设置token使用时间(实时使用)
            ->expiresAt($time + 86400*30); //设置token过期时间
        //定义自己所需字段
        $user = ['user_id' => $user_info['id']];
        $tokenBuilder->withClaim('user', $user);
        //使用Sha256加密生成token对象，该对象的字符串形式为一个JWT字符串
        $token = $tokenBuilder->getToken($signer, new Key(JWT_SECRET_TOKEN));
        $data['token'] = (string)$token ;
        //同时更新用户登录时间
        $update_data['last_login_time'] = date('Y-m-d H:i:s');
        $user_model->baseUpdate($user_model::tableName(),$update_data,'id=:id',[':id'=>$user_info['id']]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]) ;
    }

    // 登出
    public function  actionDoLogout(){
        Yii::$app->session->remove(LOGIN_SESSION_NAME);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]) ;
    }




}