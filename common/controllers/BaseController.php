<?php
namespace common\controllers;

use backend\models\Admin;
use Yii;

class BaseController extends \yii\web\Controller
{
    public $secretAdminUserInfo = [] ;
    /**
     * 返回get请求参数
     * @param string $key
     * @param string $type
     * @return array|mixed
     */
    function getParam($key = '',$type = ''){
        $request = Yii::$app->request;
        if($key){
            $value = $request->get($key);
            $value = $type == 'int' ? intval($value) : $value;
        } else{
            $value = $request->get();
        }

        if(!$value){
            $value = $this->postParam($key,$type) ;
        }
        return trim($value);
    }

    /**
     * 返回post请求参数
     * @param string $key
     * @param string $type
     * @param boolean $is_trim
     * @return array|mixed
     */
    function postParam($key = '',$type = '',$is_trim = true){
        $request = Yii::$app->request;
        if($key){
            $value = $request->post($key);
            $value = $type == 'int' ? intval($value) : $value;
        } else{
            $value = $request->post();
        }
        return $is_trim ? trim($value):$value;
    }

    /**
     * 格式化返回数据
     * @param array $arr_ret
     * @return string
     */
    function returnJson($arr_ret = [])
    {
        $callback = $this->getParam('jsonp_callback');
        if( isset($arr_ret['code']) ){
            $arr_ret['code'] = (int)$arr_ret['code'];
        }

        // 默认数据(不存在或为空设置空对象)
        if(!isset($arr_ret['data']) || empty($arr_ret['data'])){
            $arr_ret['data'] = (object)[];
        }

        if($callback){
            return "$callback(".json_encode($arr_ret).")";
        }else{
            return json_encode($arr_ret);
        }
    }

    //
    public function verifySign($is_backend=false){
        // 来源签名
        $userMark = $this->postParam('appKey');
        if($is_backend){
            // 验证是否有
            $admin_obj = new Admin() ;
            $adminUserInfo = $admin_obj->getInfoByUserMark($userMark);

            if(!$adminUserInfo){
                $res= $this->returnJson(['code'=>100003,'msg'=>getErrorDictMsg(100003)]);
                echo $res;exit;
            }

            $this->secretAdminUserInfo = $adminUserInfo ;
        }

        // 请求参数
        $request_data = $_POST;

        // 验证公共参数是否有值
        if(!isset($request_data['appKey']) || !isset($request_data['sign']) || !isset($request_data['timestamp']) ){
            echo  $this->returnJson(['code'=>100004,'msg'=>getErrorDictMsg(100004)]); exit ;
        }

        // 请求签名参数
        $sign = $request_data['sign'];

        // 请求时间（毫秒）
        $timestamp = $request_data['timestamp'];

        // 当前毫秒时间减去10分钟
        $now_micro_time = $this->getMicrotime() - 600000;
        // 验证签名失效（10分有效期）
        if($now_micro_time > $timestamp){
            echo  $this->returnJson(['code'=>100005,'msg'=>getErrorDictMsg(100005)]); exit;
        }

        // 排序、过滤不用加签的参数
        $filter_data = $this->paraFilter($request_data);

        // 得到签名结果
        $verify_sign = MD5($userMark.$this->arrayToString($filter_data).$this->secretAdminUserInfo['secret_key']);

        if($verify_sign != $sign){
            echo  $this->returnJson(['code'=>100006,'msg'=>getErrorDictMsg(100006)]);exit;
        }
    }


    /**
     * 获取一个毫秒级别的时间戳
     * @return int
     */
    private function getMicrotime() {
        $time = floor(microtime(true) * 1000);

        return $time;
    }

    /**
     * 除去数组中的空值和签名参数
     * @param  array $params 签名参数组
     * @return array
     */
    private function paraFilter($params)
    {
        // 正序排序
        ksort($params);

        $para_filter = array();
        foreach($params as $key => $val)
        {
            // 私有图片上传信息
            if($key == "sign" || $key =='private_upload_file'){
                continue;
            }else{
                $para_filter[$key] = $val;
            }
        }
        return $para_filter;
    }

    /**
     * 数字转字符串
     * @param array $params 需要拼接的数组
     * @return string
     */
    private function arrayToString($params)
    {
        $arg = "";
        foreach($params as $key => $val){
            $arg.=$key."=".$val;
        }

        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){
            $arg = stripslashes($arg);
        }

        return $arg;
    }
}