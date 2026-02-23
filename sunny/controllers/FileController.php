<?php
/**
 * 文件上传操作
 * @Auther simonz9988@qq.com
 */

namespace sunny\controllers;

// 设置成最大执行时间为 10 分钟，如果超过 10 分钟未成功，则认为网络有问题
set_time_limit(600);

use backend\models\CoinAddressValue;
use common\controllers\BaseController;
use Yii;
use yii\web\UploadedFile;
// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;
use common\models\SiteConfig ;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

class FileController extends BaseController
{

    /**
     *
     * @var Array 保存配置的数组
     */
    public $config;
    public $php_path;
    public $php_url;
    public $root_path;
    public $root_url;
    public $save_path;
    public $save_url;
    public $max_size = 1048576;

    // 两种资源的允许格式后缀
    public $video_allow_type = ["mp4", "mpeg", "avi", "3gp", "rm", "rmvb", "mov", "wmv", "flv", "asf","svga","json"];
    public $image_allow_type = ["jpeg", "png", "gif", "jpg"];
    public $file_allow_type = ['svg','xml','cgm',"mp4", "mpeg", "avi", "3gp", "rm", "rmvb", "mov", "wmv", "flv", "asf","svga","json","jpeg", "png", "gif", "jpg",'xls','xlsx',"pdf"];
    // 两种资源对应的最大文件大小
    public $video_allow_size = 104857600;
    public $image_allow_size = 5242880;
    public $file_allow_size = 1048576000;

    public $layout = 'empty';

    /**
     * 新增图片文件
     */
    public function actionDoUpload()
    {
        $a = UploadedFile::getInstanceByName('file');
        $request = Yii::$app->request;
        $upload_file = $request->get('file_type') ;//对应的上传文件目录
        if($a){
            //step1:判断类型
            $file_type = $a->getExtension();

            $allowed_file_type = $this->file_allow_type;
            if(!in_array($file_type,$allowed_file_type)){
                //文件类型不允许

                //获取相对应的错误提示信息
                $allowedNoticeStr = '文件类型不允许';
                $info = array('status'=>'err','code'=>'-1','msg'=>$allowedNoticeStr);

            }else{
                //限定大小1M
                $max_size =  $this->file_allow_size;
                //step2:判断大小
                $file_size = $a->size;

                if($file_size>$max_size){
                    //文件大小超出
                    $outMaxSizeStr = '文件大小超出';
                    $info = array('status'=>'err','code'=>'-2','msg'=>$outMaxSizeStr);

                }else{

                    //上传之前用户自定义的文件名
                    $user_upload_file_name = $a->getBaseName();

                    //ftp上传的相对路径
                    $ftp_path = 'upload/'.$upload_file.'/'.date('Y/m/d').'/';
                    //php上传的相对路径
                    $file_dir = '/'.$ftp_path;
                    //remote上传路径
                    $remote_file_dir = '.'.$file_dir;
                    //完整的路径
                    $all_path = ROOT_PATH.$file_dir ;
                    //文件后缀
                    $file_ext = strtolower($a->getExtension());


                    if(createFolder($all_path)){

                        $file_name = $upload_file.'_'.time().mt_rand(1000,9999).".".$file_ext ;

                        //本地上传
                        $r = $a->saveAs($all_path.$file_name);

                        $file_path = $file_dir.$file_name ;

                        $user_upload_file_name = $user_upload_file_name ;
                        $data['file_name'] = $file_name ;
                        $data['file_path'] = $file_path ;
                        $data['file_relative_path'] = $file_dir . $file_name ;
                        $info = array('data' => $data, 'code' => '1', 'msg' => '上传成功', 'file_name' => $file_name,'file_path'=>$file_path,'user_upload_file_name'=>$user_upload_file_name);

                    }else{
                        //创建目录失败
                        $info = array('status'=>'err','code'=>'-3','msg'=>'上传失败');
                    }

                }

            }
        }else{
            $info = array('status'=>'err','code'=>'-3','msg'=>'上传失败','detail'=>'Uploaded init error ! contact admin ');
        }

        echo json_encode($info) ;
        exit;
    }

    /**
     * 新增图片文件
     */
    public function actionDoImageUpload()
    {
        $a = UploadedFile::getInstanceByName('file');
        $request = Yii::$app->request;
        $upload_file = $request->get('file_type') ;//对应的上传文件目录
        if($a){
            //step1:判断类型
            $file_type = $a->getExtension();
            $allowed_file_type = array('jpeg', 'png', 'gif', "jpg");
            if(!in_array($file_type,$allowed_file_type)){
                //文件类型不允许

                //获取相对应的错误提示信息
                $allowedNoticeStr = '文件类型不允许';
                $info = array('status'=>'err','code'=>'-1','msg'=>$allowedNoticeStr);

            }else{
                //限定大小1M
                $max_size =  2097152;
                //step2:判断大小
                $file_size = $a->size;

                if($file_size>$max_size){
                    //文件大小超出
                    $outMaxSizeStr = '文件大小超出';
                    $info = array('status'=>'err','code'=>'-2','msg'=>$outMaxSizeStr);

                }else{

                    //上传之前用户自定义的文件名
                    $user_upload_file_name = $a->getBaseName();

                    //ftp上传的相对路径
                    $ftp_path = 'upload/'.$upload_file.'/'.date('Y/m/d').'/';
                    //php上传的相对路径
                    $file_dir = '/'.$ftp_path;
                    //remote上传路径
                    $remote_file_dir = '.'.$file_dir;
                    //完整的路径
                    $all_path = ROOT_PATH.$file_dir ;
                    //文件后缀
                    $file_ext = strtolower($a->getExtension());


                    if(createFolder($all_path)){

                        $file_name = $upload_file.'_'.time().mt_rand(1000,9999).".".$file_ext ;

                        //本地上传
                        $r = $a->saveAs($all_path.$file_name);

                        // 计算 MD5
                        $md5 = md5_file($all_path . $file_name);

                        //上传到静态资源服务器
                        //$static_url = SiteConfig::model()->getInfoByKey('static_url');
                        //$static_url = $static_url?$static_url:'http://qas-static.ecovacs.cn/';
                        $static_url = UPLOAD_IMAGE_URL.'/';

                        $url = $static_url.'upload.php';
                        header('content-type:text/html;charset=utf8');
                        $ch = curl_init();

                        // php 版本如果大于等于5.5则需要使用CURLFile类进行文件是上传
                        if (class_exists('\CURLFile')) {
                            $img = new \CURLFile(realpath($remote_file_dir . '/' . $file_name));
                            $data = array('pic' => $img, 'path' => $remote_file_dir);
                        } else {
                            // 加@符号curl就会把它当成是文件上传处理
                            $data = array('pic' => '@' . realpath($remote_file_dir . '/' . $file_name), 'path' =>  $remote_file_dir);
                        }

                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        $result = curl_exec($ch);
                        curl_close($ch);

                        // 删除本地文件
                        $this->del($all_path . $file_name);

                        $info = array('data' => $file_dir . $file_name, 'code' => '1', 'msg' => '上传成功', 'md5' => $md5, 'file_name' => $file_name);

                    }else{
                        //创建目录失败
                        $info = array('status'=>'err','code'=>'-3','msg'=>'上传失败');
                    }

                }

            }
        }else{
            $info = array('status'=>'err','code'=>'-3','detail'=>'Uploaded init error ! contact admin ');
        }

        echo json_encode($info) ;
        exit;
    }
    
    /**
     * 上传视频
     */
    public function actionDoVideoUpload()
    {
        $a = UploadedFile::getInstanceByName('file');
        $request = Yii::$app->request;
        $upload_file = $request->get('file_type') ;//对应的上传文件目录
        if($a){
            //step1:判断类型
            $file_type = $a->getExtension();
            $allowed_file_type = $this->video_allow_type;
            if(!in_array($file_type,$allowed_file_type)){
                //文件类型不允许
                
                //获取相对应的错误提示信息
                $allowedNoticeStr = '文件类型不允许';
                $info = array('status'=>'err','code'=>'-1','msg'=>$allowedNoticeStr);
                
            }else{
                // 限定大小40M
                $max_size = 41943040;
                //step2:判断大小
                $file_size = $a->size;
                
                if($file_size>$max_size){
                    //文件大小超出
                    $outMaxSizeStr = '文件大小超出';
                    $info = array('status'=>'err','code'=>'-2','msg'=>$outMaxSizeStr);
                    
                }else{
                    
                    //上传之前用户自定义的文件名
                    $user_upload_file_name = $a->getBaseName();
                    
                    //ftp上传的相对路径
                    $ftp_path = 'upload/'.$upload_file.'/'.date('Y/m/d').'/';
                    //php上传的相对路径
                    $file_dir = '/'.$ftp_path;
                    //remote上传路径
                    $remote_file_dir = '.'.$file_dir;
                    //完整的路径
                    $all_path = ROOT_PATH.$file_dir ;
                    //文件后缀
                    $file_ext = strtolower($a->getExtension());
                    
                    
                    if(createFolder($all_path)){
                        
                        $file_name = $upload_file.'_'.time().mt_rand(1000,9999).".".$file_ext ;
                        
                        //本地上传
                        $r = $a->saveAs($all_path.$file_name);
                        
                        // 计算 MD5
                        $md5 = md5_file($all_path . $file_name);
                        
                        //上传到静态资源服务器
                        //$static_url = SiteConfig::model()->getInfoByKey('static_url');
                        //$static_url = $static_url?$static_url:'http://qas-static.ecovacs.cn/';
                        $static_url = UPLOAD_IMAGE_URL.'/';
                        
                        $url = $static_url.'upload.php';
                        header('content-type:text/html;charset=utf8');
                        $ch = curl_init();
                        
                        // php 版本如果大于等于5.5则需要使用CURLFile类进行文件是上传
                        if (class_exists('\CURLFile')) {
                            $img = new \CURLFile(realpath($remote_file_dir . '/' . $file_name));
                            $data = array('pic' => $img, 'path' => $remote_file_dir, "need_frame" => 1);
                        } else {
                            // 加@符号curl就会把它当成是文件上传处理
                            $data = array('pic' => '@' . realpath($remote_file_dir . '/' . $file_name), 'path' =>  $remote_file_dir, "need_frame" => 1);
                        }

                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        $result = curl_exec($ch);
                        curl_close($ch);

                        // 删除本地文件
                        $this->del($all_path . $file_name);

                        $info = array('data' => $file_dir . $file_name, 'code' => '1', 'msg' => '上传成功', 'md5' => $md5);
                        
                    }else{
                        //创建目录失败
                        $info = array('status'=>'err','code'=>'-3','msg'=>'上传失败');
                    }
                    
                }
                
            }
        }else{
            $info = array('status'=>'err','code'=>'-3','detail'=>'Uploaded init error ! contact admin ');
        }
        
        echo json_encode($info) ;
        exit;
    }

    /**
     * 编辑器内上传图片的函数
     */
    public function actionDoKindUpload() {
        $this->save_path = ROOT_PATH . '/upload/';
        $this->save_url = '/upload/';

        // 定义允许上传的文件扩展名
        $ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );

        // PHP上传失败
        if (!empty($_FILES['imgFile']['error'])) {
            switch ($_FILES['imgFile']['error']) {
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            $this->alert($error);
        }

        // 有上传文件时
        if (empty($_FILES) === false) {
            // 原文件名
            $file_name = $_FILES['imgFile']['name'];
            // 服务器上临时文件名
            $tmp_name = $_FILES['imgFile']['tmp_name'];
            // 文件大小
            $file_size = $_FILES['imgFile']['size'];
            // 检查文件名
            if (!$file_name) {
                $this->alert("请选择文件。");
            }
            // 检查目录
            if (@is_dir($this->save_path) === false) {
                $this->alert("上传目录不存在。");
            }
            // 检查目录写权限
            if (@is_writable($this->save_path) === false) {
                $this->alert("上传目录没有写权限。");
            }
            // 检查是否已上传
            if (@is_uploaded_file($tmp_name) === false) {
                $this->alert("上传失败。");
            }
            // 检查文件大小
            if ($file_size > $this->max_size) {
                $this->alert("上传文件大小超过限制。");
            }
            // 检查目录名
            $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
            if (empty($ext_arr[$dir_name])) {
                $this->alert("目录名不正确。");
            }
            // 获得文件扩展名
            $temp_arr = explode(".", $file_name);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            // 检查扩展名
            if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
                $this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
            }
            // 创建文件夹
            if ($dir_name !== '') {
                $save_path=$this->save_path . $dir_name . "/";
                $save_url=$this->save_url . $dir_name . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
            }
            $ymd = date("Ymd");
            $save_path .= $ymd . "/";
            $save_url .= $ymd . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            // 新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
            // 移动文件
            $file_path = $save_path . $new_file_name;
            if (move_uploaded_file($tmp_name, $file_path) === false) {
                $this->alert("上传文件失败。");
            }
            @chmod($file_path, 0644);
            $file_url = $save_url . $new_file_name;

            // 上传到静态资源服务器
            $remote_file_dir = './upload/' . $dir_name . '/' . $ymd . '/';

            $url = UPLOAD_IMAGE_URL . '/upload.php';
            header('Content-type: text/html; charset=UTF-8');
            $ch = curl_init();

            // php 版本如果大于等于5.5则需要使用CURLFile类进行文件是上传
            if (class_exists('\CURLFile')) {
                $img = new \CURLFile(realpath($file_path));
                $data = array('pic' => $img, 'path' => $remote_file_dir);
            } else {
                // 加@符号curl就会把它当成是文件上传处理
                $data = array('pic' => '@' . $file_path, 'path' =>  $remote_file_dir);
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            curl_exec($ch);
            curl_close($ch);

            // 删除本地文件
            $this->del($file_path);

            echo json_encode(array('error' => 0, 'url' => CDN_IMAGE_URL . $file_url));
            exit;
        }
    }
    
    /**
     * CKeditor编辑器内上传图片的函数
     */
    public function actionDoCkeditorUpload() {
        $this->save_path = ROOT_PATH . '/upload/';
        $this->save_url = '/upload/';
        
        // 定义允许上传的文件扩展名
        $ext_arr = array(
            'image' => array('gif', 'jpg', 'jpeg', 'png', 'bmp'),
            'flash' => array('swf', 'flv'),
            'media' => array('swf', 'flv', 'mp3', 'wav', 'wma', 'wmv', 'mid', 'avi', 'mpg', 'asf', 'rm', 'rmvb'),
            'file' => array('doc', 'docx', 'xls', 'xlsx', 'ppt', 'htm', 'html', 'txt', 'zip', 'rar', 'gz', 'bz2'),
        );
        
        // PHP上传失败
        if (!empty($_FILES['upload']['error'])) {
            switch ($_FILES['upload']['error']) {
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            $this->alert($error);
        }
        
        // 有上传文件时
        if (empty($_FILES) === false) {
            // 原文件名
            $file_name = $_FILES['upload']['name'];
            // 服务器上临时文件名
            $tmp_name = $_FILES['upload']['tmp_name'];
            // 文件大小
            $file_size = $_FILES['upload']['size'];
            // 检查文件名
            if (!$file_name) {
                $this->alert("请选择文件。");
            }
            // 检查目录
            if (@is_dir($this->save_path) === false) {
                $this->alert("上传目录不存在。");
            }
            // 检查目录写权限
            if (@is_writable($this->save_path) === false) {
                $this->alert("上传目录没有写权限。");
            }
            // 检查是否已上传
            if (@is_uploaded_file($tmp_name) === false) {
                $this->alert("上传失败。");
            }
            // 检查文件大小
            if ($file_size > $this->max_size) {
                $this->alert("上传文件大小超过限制。");
            }
            // 检查目录名
            $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
            if (empty($ext_arr[$dir_name])) {
                $this->alert("目录名不正确。");
            }
            // 获得文件扩展名
            $temp_arr = explode(".", $file_name);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            // 检查扩展名
            if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
                $this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
            }
            // 创建文件夹
            if ($dir_name !== '') {
                $save_path=$this->save_path . $dir_name . "/";
                $save_url=$this->save_url . $dir_name . "/";
                if (!file_exists($save_path)) {
                    mkdir($save_path);
                }
            }
            $ymd = date("Ymd");
            $save_path .= $ymd . "/";
            $save_url .= $ymd . "/";
            if (!file_exists($save_path)) {
                mkdir($save_path);
            }
            // 新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
            // 移动文件
            $file_path = $save_path . $new_file_name;
            if (move_uploaded_file($tmp_name, $file_path) === false) {
                $this->alert("上传文件失败。");
            }
            @chmod($file_path, 0644);
            $file_url = $save_url . $new_file_name;
            
            // 上传到静态资源服务器
            $remote_file_dir = './upload/' . $dir_name . '/' . $ymd . '/';
            
            $url = UPLOAD_IMAGE_URL . '/upload.php';
            header('Content-type: text/html; charset=UTF-8');
            $ch = curl_init();
            
            // php 版本如果大于等于5.5则需要使用CURLFile类进行文件是上传
            if (class_exists('\CURLFile')) {
                $img = new \CURLFile(realpath($file_path));
                $data = array('pic' => $img, 'path' => $remote_file_dir);
            } else {
                // 加@符号curl就会把它当成是文件上传处理
                $data = array('pic' => '@' . $file_path, 'path' =>  $remote_file_dir);
            }
            
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            
            curl_exec($ch);
            curl_close($ch);
            
            // 删除本地文件
            $this->del($file_path);

            echo json_encode(array('error' => 0, 'fileName' => $new_file_name, 'uploaded' => 1, 'url' => CDN_IMAGE_URL . $file_url));
            exit;
        }
    }

    /**
     * KindEditor返回用的函数
     * @param $msg
     */
    private function alert($msg) {
        header('Content-type: text/html; charset=UTF-8');
        echo json_encode(array('error' => 1, 'message' => $msg));
        exit;
    }


    /**
     * UEditer  编辑器上传文件操作
     * @return [type] [description]
     */
    public function actionDoUeUpload(){
        $UE_BASE_URL = ROOT_PATH.'/public/ace_static/js/editor/php/';
        $config_json = $UE_BASE_URL.'config.json';
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents($config_json)), true);

        //根据网站域名动态生成上传目录
        $http_host = $_SERVER['HTTP_HOST'] ;
        $host_str = base64_encode($http_host);
        $path_str = substr($host_str, 0, 6);
        $upload_path = "/upload/".$path_str."/ueditor/image/{yyyy}{mm}{dd}{time}";
        $CONFIG['imagePathFormat'] = $upload_path ;
        $CONFIG['scrawlPathFormat'] = $upload_path ;
        $CONFIG['snapscreenPathFormat'] = $upload_path ;
        $CONFIG['catcherPathFormat'] = $upload_path ;
        $CONFIG['videoPathFormat'] = $upload_path ;
        $CONFIG['filePathFormat'] = $upload_path ;
        $CONFIG['imageManagerListPath'] = $upload_path ;
        $CONFIG['fileManagerListPath'] = $upload_path ;


        $action = getParam('action');
        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
                /* 上传涂鸦 */
            case 'uploadscrawl':
                /* 上传视频 */
            case 'uploadvideo':
                /* 上传文件 */
            case 'uploadfile':
                $result = include($UE_BASE_URL."action_upload.php");
                break;

            /* 列出图片 */
            case 'listimage':
                $result = include($UE_BASE_URL."action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include($UE_BASE_URL."action_list.php");
                break;

            /* 抓取远程文件 */
            case 'catchimage':
                $result = include($UE_BASE_URL."action_crawler.php");
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {

            if($action =='config'){
                echo $result;
            }else{
                $rst_arr = json_decode($result,true) ;

                $rst_arr['url'] = static_url($rst_arr['url']);
                echo json_encode($rst_arr);
            }
        }
    }

    /**
     * 解析Excel
     * @throws \PHPExcel_Exception
     */
    public function actionParseExcel() {
        $parseColumn = (int)$this->getParam('column');

        if ($parseColumn <= 0 || $parseColumn > 26) {
            $info = ['status' => 'success', 'code' => '-2', 'msg' => '获取文件列数不能超过26列'];

            exit(json_encode($info));
        }

        $file = $this->uploadExcel();

        $objPHPExcelReader = \PHPExcel_IOFactory::load($file);  //加载excel文件

        $sheet = $objPHPExcelReader->getSheet(0);
        $maxRow = $sheet->getHighestRow(); // 取得总行数

        if ($maxRow > 20001) {
            $info = ['status' => 'success', 'code' => '-2', 'msg' => '文件内容不能超过2万行'];

            exit(json_encode($info));
        }

        $data = [];
        for ($i = 2; $i <= $maxRow; $i++) {
            if ($parseColumn == 1) {
                $data[] = $sheet->getCell('A' . $i)->getValue();
            } else {
                $tmp = [];
                for ($ascii = 0; $ascii < $parseColumn; $ascii++) {
                    $tmp[] = $sheet->getCell(chr(65 + $ascii) . $i)->getValue();
                }
                $data[] = $tmp;
            }
        }

        // 删除本地文件
        $this->del($file);

        $info = ['status' => 'success', 'code' => '1', 'data' => $data];

        exit(json_encode($info));
    }

    /**
     * 上传 Excel 文件
     */
    private function uploadExcel() {
        $a = UploadedFile::getInstanceByName('file');
        $upload_file = "tmp_excel";

        if ($a) {
            //step1:判断类型
            $file_type = $a->getExtension();
            $allowed_file_type = array("xls", "xlsx");

            if (!in_array($file_type, $allowed_file_type)) {
                //文件类型不允许
                $info = ['status' => 'err', 'code' => '-1', 'msg' => '文件类型不允许'];
            } else {
                //限定大小3M
                $max_size =  1048576*3;
                //step2:判断大小
                $file_size = $a->size;

                if ($file_size > $max_size) {
                    //文件大小超出
                    $info = ['status' => 'err', 'code' => '-2', 'msg' => '文件大小超出'];
                } else {
                    //完整的路径
                    $all_path = ROOT_PATH . '/upload/' . $upload_file . '/' . date('Y/m/d') . '/';

                    //文件后缀
                    $file_ext = strtolower($a->getExtension());

                    if (createFolder($all_path)) {

                        $file_name = $upload_file . '_' . time() . mt_rand(1000,9999) . "." . $file_ext;

                        //本地上传
                        $r = $a->saveAs($all_path . $file_name);

                        return $all_path . $file_name;
                    } else {
                        //创建目录失败
                        $info = ['status' => 'err', 'code' => '-3', 'msg' => '上传失败'];
                    }
                }
            }
        }else{
            $info = array('status'=>'err','code'=>'-3','detail'=>'Uploaded init error ! contact admin ');
        }

        exit(json_encode($info));
    }



    /**
     * 新增图片文件
     */
    public function actionDoCdnUpload()
    {
        $a = UploadedFile::getInstanceByName('file');
        $request = Yii::$app->request;
        $upload_file = $request->get('file_type') ;//对应的上传文件目录
        if($a){
            //step1:判断类型
            $file_type = $a->getExtension();
            $allowed_file_type = array('jpeg', 'png', 'gif', "jpg");
            if(!in_array($file_type,$allowed_file_type)){
                //文件类型不允许

                //获取相对应的错误提示信息
                $allowedNoticeStr = '文件类型不允许';
                $info = array('status'=>'err','code'=>'-1','msg'=>$allowedNoticeStr);

            }else{
                //限定大小5M
                $max_size =  1048576*5;
                //step2:判断大小
                $file_size = $a->size;

                if($file_size>$max_size){
                    //文件大小超出
                    $outMaxSizeStr = '文件大小超出';
                    $info = array('status'=>'err','code'=>'-2','msg'=>$outMaxSizeStr);

                }else{

                    //上传之前用户自定义的文件名
                    $user_upload_file_name = $a->getBaseName();

                    //ftp上传的相对路径
                    $ftp_path = 'upload/'.$upload_file.'/'.date('Y/m/d').'/';
                    //php上传的相对路径
                    $file_dir = '/'.$ftp_path;
                    //remote上传路径
                    $remote_file_dir = '.'.$file_dir;
                    //完整的路径
                    $all_path = ROOT_PATH.$file_dir ;
                    //文件后缀
                    $file_ext = strtolower($a->getExtension());


                    if(createFolder($all_path)){

                        $file_name = $upload_file.'_'.time().mt_rand(1000,9999).".".$file_ext ;

                        //本地上传
                        $r = $a->saveAs($all_path.$file_name);

                        // 计算 MD5
                        $md5 = md5_file($all_path . $file_name);

                        $site_config_obj = new SiteConfig();

                        //上传七牛
                        // 构建鉴权对象
                        $accessKey  = 'lynggCpSh5XzKYML1Ip7-IrosI5tFBYRmJIBSA2x';
                        $accessKey = $site_config_obj->getInfoByKey('cdn_accessKey');
                        $secretKey = 'gO1p848TvJNBwUuYYllysij9XIYHdo670aZkA1Qn';
                        $secretKey = $site_config_obj->getInfoByKey('cdn_secretKey');
                        // 要上传的空间
                        $bucket = 'ecovacs-static';
                        $bucket = $site_config_obj->getInfoByKey('cdn_bucket');

                        $auth = new Auth($accessKey, $secretKey);

                        // 生成上传 Token
                        $token = $auth->uploadToken($bucket);
                        // 要上传文件的本地路径
                        $filePath = $remote_file_dir.'/'.$file_name;
                        // 上传到七牛后保存的文件名
                        $key = $ftp_path.$file_name;

                        // 初始化 UploadManager 对象并进行文件的上传。
                        $uploadMgr = new UploadManager();
                        // 调用 UploadManager 的 putFile 方法进行文件的上传。
                        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

                        if ($err !== null) {
                            $info = array('status'=>'err','code'=>'-4','msg'=>'上传失败');
                        }else{
                            $info = array('data' => $file_dir . $file_name, 'code' => '1', 'msg' => '上传成功', 'md5' => $md5);
                        }

                        // 删除本地文件
                        $this->del($all_path . $file_name);

                    }else{
                        //创建目录失败
                        $info = array('status'=>'err','code'=>'-3','msg'=>'上传失败');
                    }

                }

            }
        }else{
            $info = array('status'=>'err','code'=>'-3','detail'=>'Uploaded init error ! contact admin ');
        }

        echo json_encode($info) ;
        exit;
    }

       
    /**
     * 普通上传文件
     */
    public function actionDoUploadFile()
    {
        $a = UploadedFile::getInstanceByName('file');
        $request = Yii::$app->request;
        $upload_file = $request->get('file_type') ;//对应的上传文件目录
        $is_to_cdn = $request->get('is_to_cdn') ;//是否上传到static
        if($a){
            $file_type = $a->getExtension();
            // 允许的文件类型
            $allowed_file_type = array('pdf','csv');
            if(!in_array($file_type,$allowed_file_type)){
                
                //获取相对应的错误提示信息
                $allowedNoticeStr = '文件类型不允许';
                $info = array('status'=>'err','code'=>'-1','msg'=>$allowedNoticeStr);
                
            }else{
                // 限定大小10M
                $max_size =  31457280;
                
                $file_size = $a->size;
                // 判断大小
                if($file_size>$max_size){
                    //文件大小超出
                    $outMaxSizeStr = '文件大小超出';
                    $info = array('status'=>'err','code'=>'-2','msg'=>$outMaxSizeStr);
                    
                }else{
                    
                    //上传之前用户自定义的文件名
                    $user_upload_file_name = $a->getBaseName();
                    
                    //ftp上传的相对路径
                    $ftp_path = 'upload/'.$upload_file.'/'.date('Y/m/d').'/';
                    //php上传的相对路径
                    $file_dir = '/'.$ftp_path;
                    //remote上传路径
                    $remote_file_dir = '.'.$file_dir;
                    //完整的路径
                    $all_path = ROOT_PATH.$file_dir ;
                    //文件后缀
                    $file_ext = strtolower($a->getExtension());
                    
                    
                    if(createFolder($all_path)){
                        
                        $file_name = $upload_file.'_'.time().mt_rand(1000,9999).".".$file_ext ;
                        
                        //本地上传
                        $a->saveAs($all_path.$file_name);
                        
                        // 计算 MD5
                        $md5 = md5_file($all_path . $file_name);
                        
                        //上传到静态资源服务器
                        $static_url = UPLOAD_IMAGE_URL.'/';
                        
                        $url = $static_url.'upload.php';
                        header('content-type:text/html;charset=utf8');

                        if($is_to_cdn != 'n'){
                            $ch = curl_init();
                            // php 版本如果大于等于5.5则需要使用CURLFile类进行文件是上传
                            if (class_exists('\CURLFile')) {
                                $img = new \CURLFile(realpath($remote_file_dir . '/' . $file_name));
                                $data = array('pic' => $img, 'path' => $remote_file_dir);
                            } else {
                                // 加@符号curl就会把它当成是文件上传处理
                                $data = array('pic' => '@' . realpath($remote_file_dir . '/' . $file_name), 'path' =>  $remote_file_dir);
                            }
                            curl_setopt($ch, CURLOPT_URL, $url);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_exec($ch);
                            curl_close($ch);
                        }

                        // 删除本地文件
                        $this->del($all_path . $file_name);
                        
                        $info = array('data' => $file_dir . $file_name, 'code' => '1', 'msg' => '上传成功', 'md5' => $md5);
                        
                    }else{
                        //创建目录失败
                        $info = array('status'=>'err','code'=>'-3','msg'=>'上传失败');
                    }
                    
                }
                
            }
        }else{
            $info = array('status'=>'err','code'=>'-3','detail'=>'Uploaded init error ! contact admin ');
        }
        
        echo json_encode($info) ;
        exit;
    }

    /**
     * 隐私上传视频
     */
    public function actionPrivateVideo() {
        $tag = "VIDEO";

        $allow_type = $this->video_allow_type;
        $allow_size = $this->video_allow_size;

        return $this->upload($tag, $allow_type, $allow_size);
    }

    /**
     * 隐私上传图片
     */
    public function actionPrivateImage() {
        $tag = "IMAGE";

        $allow_type = $this->image_allow_type;
        $allow_size = $this->image_allow_size;

        return $this->upload($tag, $allow_type, $allow_size);
    }

    /**
     * 隐私图片上传，采用独立的上传路径
     * 因为需要用户先登录才能使用
     * 同步到静态资源服务器后删除本地临时文件
     */
    private function upload($tag, $allow_type, $allow_size) {
        // 用户
        $user_id = trim($this->getParam("user_id"));
        if (!$user_id) {
            return $this->returnJson(array('code'=>100001, 'msg' => getErrorDictMsg(100001)));
        }

        // 客户端文件
        $file = UploadedFile::getInstanceByName("file");
        // 资源用途
        $path = trim($this->getParam("file_type"));

        if (!$file) {
            return $this->returnJson(["code" => 100101, "msg" => getErrorDictMsg(100101)]);
        }

        if (!$path) {
            return $this->returnJson(["code" => 100003, "msg" => getErrorDictMsg(100003)]);
        }

        // 文件格式判断
        $file_type = strtolower($file->getExtension());

        if (!in_array($file_type, $allow_type)) {
            return $this->returnJson(["code" => 100102, "msg" => getErrorDictMsg(100102)]);
        }

        // 文件大小判断
        if ($file->size > $allow_size) {
            return $this->returnJson(["code" => 100103, "msg" => getErrorDictMsg(100103)]);
        }

        // 相关目录
        $user_path = $this->generateUserPath($user_id);
        $upload_directory = $this->getDirectory($tag, $path);
        $upload_filename = $this->getFilename($file_type);

        // 上传本地
        $local_file_path = ROOT_PATH . "/upload/" . $user_path . $upload_directory;
        createFolder($local_file_path);

        //本地上传
        $local_status = $file->saveAs($local_file_path . $upload_filename);

        if (!$local_status) {
            Yii::$app->CommonLogger->logError("上传静态文件失败：" . $local_file_path . $upload_filename);

            return $this->returnJson(["code" => 100104, "msg" => getErrorDictMsg(100104)]);
        }

        // 同步静态资源
        $sync_status = $this->sync($user_path . $upload_directory, $upload_filename);

        // 同步失败，日志报错，并返回失败
        if (!$sync_status) {
            Yii::$app->CommonLogger->logError("同步静态文件失败：" . $user_path . $upload_directory . $upload_filename);

            return $this->returnJson(["code" => 100105, "msg" => getErrorDictMsg(100105)]);
        }

        // 删除本地文件
        $this->del($local_file_path . $upload_filename);

        return $this->returnJson(["code" => 1, "msg" => getErrorDictMsg(1), "data" => $upload_directory . $upload_filename]);
    }

    /**
     * 通过文件类型和用途来生成用户的上传路径
     * @param string $type video / photo
     * @param string $path 功能标识
     * @return string
     */
    private function getDirectory($type, $path) {
        return "/" . strtolower($type) . "/" . $path . "/" . date("Y/m/d") . "/";
    }

    // 生成用户个人的路径
    private function generateUserPath($user_id) {
        $user_id = str_pad($user_id, 12, "0", STR_PAD_LEFT);

        $user_path = str_split($user_id, 3);

        return implode("/", $user_path);
    }

    /**
     * 生成用户上传的文件名
     * @param string $extension 文件扩展名
     * @return string
     */
    private function getFilename($extension) {
        return date("His") . "_" . rand(1000, 9999) . "." . $extension;
    }

    /**
     * 将资源同步到静态资源服务器
     * @param string $upload_path 路径
     * @param string $upload_filename 文件名
     * @return bool
     */
    private function sync($upload_path, $upload_filename) {
        header('content-type:text/html;charset=utf8');

        $local_relative_path = "./upload/" . $upload_path;

        //上传到静态资源服务器
        $url = PRIVATE_UPLOAD_URL . "/sync.php";

        $ch = curl_init();

        // php 版本如果大于等于 5.5 则需要使用 CURLFile 类进行文件是上传
        if (class_exists("\CURLFile")) {
            $img = new \CURLFile(realpath($local_relative_path . $upload_filename));
            $data = array("pic" => $img, "path" => $upload_path);
        } else {
            // 加@符号curl就会把它当成是文件上传处理
            $data = array("pic" => '@' . realpath($local_relative_path . $upload_filename), "path" =>  $upload_path);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $callback = curl_exec($ch);

        curl_close($ch);

        if (!$callback) {
            return false;
        }

        $result = json_decode($callback, true);

        if ($result["code"] == 1) {
            return true;
        }

        return false;
    }

    /**
     * 删除文件
     * @param string $file_path 本地文件路径
     */
    private function del($file_path) {
        unlink($file_path);
    }


    /**
    * 解析Excel
    * @throws \PHPExcel_Exception
    */
    public function actionUploadImportOrderFile() {

        //设置不限时
        set_time_limit(0);

        //$parseColumn = (int)getParam('column');
        $parseColumn = 9 ; // 暂时写死
        if ($parseColumn <= 0 || $parseColumn > 26) {
            $info = ['status' => 'success', 'code' => '-2', 'msg' => '获取文件列数不能超过26列'];
            responseJson($info) ;
        }
        $adminUserInfo = Yii::$app->session->get('login_user');
        //用户信息
        $user_id = $adminUserInfo['id'];

        $a = UploadedFile::getInstanceByName('file');

        if(!$a){
            $info = array('status'=>'err','code'=>'-3','detail'=>'Uploaded init error ! contact admin ');
            responseJson($info);
        }

        //step1:判断类型
        $file_type = $a->getExtension() ;
        $allowed_file_type = array("xls");

        if (!in_array($file_type, $allowed_file_type)) {
            //文件类型不允许
            $info = ['status' => 'err', 'code' => '-1', 'msg' => '文件类型不允许'];
            responseJson($info);
        }

        //限定大小3M
        $max_size =  1048576*3;
        //step2:判断大小
        $file_size = $a->size;

        if ($file_size > $max_size) {
            //文件大小超出
            $info = ['status' => 'err', 'code' => '-2', 'msg' => '文件大小超出'];
            responseJson($info);
        } else {
            //完整的路径
            $all_path = ROOT_PATH . '/upload/import_order/';

            //文件后缀
            $file_ext = strtolower($a->getExtension());

            if (createFolder($all_path)) {

                $file_name = $user_id . '_import_order.'  . $file_ext;
                //本地上传
                $a->saveAs($all_path . $file_name);
            } else {
                //创建目录失败
                $info = ['status' => 'err', 'code' => '-3', 'msg' => '上传失败'];
                responseJson($info) ;
            }
        }

        $model = new CoinAddressValue();
        //判断导入数据是否符合数目和格式要去
        $import_data_rst = $model->getImportOrderDataByUser($user_id,$parseColumn);

        $rst = $model->checkImportBaseInfo($import_data_rst['data']);

        responseJson($rst);

    }
}