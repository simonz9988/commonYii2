<?php
namespace sunny\controllers;

use common\components\Ecosession;
use common\components\Filter;
use common\components\MyRedis;
use common\models\Ad;
use common\models\Article;
use common\models\Coin;
use common\models\Country;
use common\models\EmailCode;
use common\models\Language;
use common\models\LanguagePacket;
use common\models\Member;
use common\models\MiningMachine;
use common\models\MiningMachineUserBalance;
use common\models\RobotUserBalance;
use common\models\SiteConfig;
use common\models\SmsLog;
use common\models\SunnyManager;
use common\models\UserPlatformKey;
use Yii;
use yii\redis\Session;

class ServiceController extends \sunny\controllers\BaseController {

    // 验证码展示
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => null,
                //背景颜色
                'backColor' => 0x000000,
                //最大显示个数
                'maxLength' => 4,
                //最少显示个数
                'minLength' => 4,
                //间距
                'padding' => 2,
                //高度
                'height' => 30,
                //宽度
                'width' => 85,
                //字体颜色
                'foreColor' => 0xffffff,
                //设置字符偏移量
                'offset' => 4,
            ],
        ];
    }

    public function actionGetLangList(){

        $params['cond'] = 'is_deleted=:is_deleted';
        $params['args'] = [':is_deleted'=>'N'];
        $obj = new Language();
        $data['list'] = $obj->getAll('name,id,short');
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    // 设置语言
    public function actionSelectLanguage(){

        $lang_id = $this->postParam('lang_id');
        $language_obj = new Language();
        $language_info = $language_obj->getInfoById($lang_id);
        if(!$language_info){
            return $this->returnJson(['code'=>100074,'msg'=>getErrorDictMsg(100074),'data'=>[]]);
        }

        // 获取当前用户ID
        $member_obj = new Member();
        $user_id = $member_obj->getLoginUserIdByAccessToken() ;
        if($user_id){
            // 放到缓存中
            $redis_obj = new MyRedis();
            $redis_obj->set('user_default_lang:'.$user_id,$lang_id) ;
        }else{
            Yii::$app->session->set("user_default_lang",$lang_id);
        }
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>[]]);
    }

    // 获取指定的key的语言包
    public function actionGetLangInfoByKey(){
        $code = $this->postParam('language_key');
        $language_package_obj = new  LanguagePacket();
        $msg = $language_package_obj->getInfoByKeyFromUser($code);
        $data['lang'] = $msg ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    public function actionGetLangListByPage(){

        $page_key = $this->postParam('page_key');
        $language_package_obj = new  LanguagePacket();
        $package_list = $language_package_obj->getListByPageKey($page_key);
        $lang_list = [];
        if($package_list){
            foreach($package_list as $v){
                $lang_list[$v['item_key']] = $language_package_obj->getInfoByKeyFromUser($v['item_key']);
            }
        }
        $data['lang_list'] = $lang_list ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }


    // 发送短信验证码
    public function actionSendEmail(){

        $email = strtolower($this->postParam('email'));
        $type = strtoupper($this->postParam('type'));

        // 复用短信发送验证码的类型是否正确
        $sms_model = new SmsLog();
        if(!in_array($type,$sms_model->type_arr)){
            return $this->returnJson(['code'=>100005,'msg'=>getErrorDictMsg(100005)]);
        }

        // 验证手机号码格式是否正确
        $filter_obj = new Filter();
        $check_mobile =$filter_obj->C_email($email);
        if(!$check_mobile){
            return $this->returnJson(['code'=>100080,'msg'=>getErrorDictMsg(100080)]);
        }

        // 执行发送
        $model = new EmailCode();
        $res = $model->sendMsg($email,$type);
        if($res){
            $data = ['code'=>1,'msg'=>getErrorDictMsg(1)];
        }else{
            $data = ['code'=>100003,'msg'=>getErrorDictMsg(100003)];
        }
        return $this->returnJson($data);

    }

    // 验证是否登录
    public function actionCheckIsLogin(){

        $session_info = Yii::$app->session->get(LOGIN_SESSION_NAME);
        if($session_info && !is_null($session_info)){
            return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]) ;
        }else{
            return $this->returnJson(['code'=>100001,'msg'=>getErrorDictMsg(100001)]) ;
        }

    }

    /**
     * 注册入口
     */
    public function actionDoRegisterByEmail(){

        $email = $this->postParam('email');
        $code = strtoupper($this->postParam('code'));
        $password = $this->postParam('password');
        $repeat_password = $this->postParam('repeat_password');
        $company_code = strtoupper($this->postParam('company_code'));

        $obj = new SunnyManager();
        $res = $obj->doRegisterByEmail($email,$code,$password,$repeat_password,$company_code);
        if($res && $res['code'] == 1){
            $user_info = $obj->getInfoByEmail($email);
            Yii::$app->session->set(LOGIN_SESSION_NAME,$user_info);

            $sms_log_obj = new EmailCode();
            $sms_log_obj->updateByEmailAndCode($email,$code,'REGISTER') ;
        }

        return $this->returnJson(['code'=>$res['code'],'msg'=>getErrorDictMsg($res['code'])]);
    }

    // 充值密码
    public function actionResetPasswordByEmail(){
        $email = $this->postParam('email');
        $code = strtoupper($this->postParam('code'));
        $password = $this->postParam('password');
        $repeat_password = $this->postParam('repeat_password');

        $obj = new SunnyManager();
        $res = $obj->resetPassword($email,$code,$password,$repeat_password);
        if($res){
            $user_info = $obj->getInfoByEmail($email);
            Yii::$app->session->set(LOGIN_SESSION_NAME,$user_info);

            $sms_log_obj = new EmailCode();
            $sms_log_obj->updateByEmailAndCode($email,$code,'FORGET') ;
        }

        return $this->returnJson(['code'=>$res['code'],'msg'=>getErrorDictMsg($res['code'])]);
    }


    /**
     * @return string
     */
    public function actionArticleRowByKey(){
        $article_cate = $this->postParam('cate_key');
        $obj = new Article();
        $fields ='id,title,create_time,intro,content';
        $info = $obj->getRowByCateKey($article_cate,$fields);
        $data['info'] = $info ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);

    }

    public function actionArticleRowById(){
        $id = $this->postParam('id');
        $obj = new Article();
        $fields ='id,title,create_time,intro,content';
        $info = $obj->getInfoById($id,$fields);
        $data['info'] = $info ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);
    }

    /**
     * 根据key值返回指定的文章列表
     */
    public function actionGetArticleListByCateKey(){

        $cate_key = strtoupper($this->getParam('cate_key'));
        $params['cond'] = 'article_cate_key=:article_cate_key AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':article_cate_key'=>$cate_key,':status'=>'ENABLED',':is_deleted'=>'N'];
        $params['fields'] = 'id,title,intro,content,seo_title,seo_keywords,seo_description,create_time';
        $params['orderby'] = 'sort desc,id desc';
        $obj = new Article();
        $list = $obj->findAllByWhere($obj::tableName(),$params,$obj::getDb());
        $list = returnCreateDateByDataList($list);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$list]);
    }

    /**
     * 获取国家列表
     */
    public function actionGetCountryList(){

        $language_obj = new Language();
        $lang_id = $language_obj->getUserDefaultLangId();


        $country_obj = new Country();
        $params['cond'] = 'is_deleted =:is_deleted';
        $params['args'] = [':is_deleted'=>'N'];
        $params['fields'] = 'id,name';
        $list = $country_obj->findAllByWhere($country_obj::tableName(),$params,$country_obj::getDb());
        $data['list'] = $list ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);

    }

}