<?php
namespace backend\components;

use Yii;

/**
 *
 * 生成小程序二维码或小程序码
 */

class WxAQrCode {

    /**
     * 获取小程序码(不是二维码)
     * @param  int  $goods_id
     * @return bool
     */
    public function getWxACode($goods_id){
        $url = "https://api.weixin.qq.com/wxa/getwxacode?";
        $url .= "access_token=" . $this->getToken();
        $postdata = [
            "path" => "/pages/home/goodDetail/detail?goodsId=".$goods_id.'&fr=goods-detail',
            "width" => 150,
            "is_hyaline" => true
        ];
        // 成功直接返回图片二进制内容，失败返回错误json对象
        $wx_code_res = $this->curl_post($url,json_encode($postdata),$options=array());
        // 判断是否有错误
        $wx_code_data = json_decode($wx_code_res, true);
        if(is_array($wx_code_data) || is_object($wx_code_data)){
            Yii::$app->CommonLogger->logError("生成小程序码错误：".json_encode($wx_code_data, JSON_UNESCAPED_UNICODE));
            return false;
        }
        
        // 图片路径
        $path = '/upload/wx_acode/';
        // 图片名
        $file_name = $goods_id.'.png';
        $upload_res = $this->uploadWxACodeImage($path, $file_name, $wx_code_res);
        if(!$upload_res){
            return false;
        }
        return true;
    }
    
    /**
     * 创建门店小程序二维码
     * @param  int  $store_no
     * @param  int  $clerk_no
     * @param  int  $goods_id
     * @return bool
     */
    public function createStoreWxACode($store_no, $clerk_no, $goods_id){
        $url = "https://api.weixin.qq.com/wxa/getwxacode?";
        $url .= "access_token=" . $this->getToken();
        
        // 商品详情页路径
        if($goods_id > 0){
            $path = "/pages/home/goodDetail/detail?goodsId=".$goods_id."&storeNo=".$store_no.'&clerkNo='.$clerk_no;
        }
        // 首页路径
        elseif($store_no){
            $path = "/pages/home/index/index?storeNo=".$store_no.'&clerkNo='.$clerk_no;
        }
        
        // 详情页路径
        $postdata = [
            "path" => $path,
            "width" => 150,
            "is_hyaline" => true
        ];

        // 成功直接返回图片二进制内容，失败返回错误json对象
        $wx_code_res = $this->curl_post($url,json_encode($postdata),$options=array());
        // 判断是否有错误
        $wx_code_data = json_decode($wx_code_res, true);
        if(is_array($wx_code_data) || is_object($wx_code_data)){
            Yii::$app->CommonLogger->logError("生成门店小程序码错误：".json_encode($wx_code_data, JSON_UNESCAPED_UNICODE));
            return false;
        }
        
        // 图片路径
        $path = '/upload/store/wx_acode/';
        // 图片名
        $file_name = $store_no.'_'.$clerk_no.'_'.$goods_id.'.png';
        $upload_res = $this->uploadWxACodeImage($path, $file_name, $wx_code_res);
        if(!$upload_res){
            return false;
        }
        return true;
    }
    
    /**
     * 上传小程序码图片
     * @param  string  $path
     * @param  string  $file_name
     * @param  string  $wx_code_res  小程序码数据源
     * @return bool
     */
    private function uploadWxACodeImage($path, $file_name, $wx_code_res){
        // 全路径
        $all_path = ROOT_PATH.$path;

        //remote上传路径
        $remote_file_dir = '.'.$path;
        
        if(createFolder($all_path)){
            // 保存图片在本地
            if(file_put_contents($all_path.$file_name, $wx_code_res) == false){
                return false;
            }
            
            //上传到静态资源服务器
            $static_url = UPLOAD_IMAGE_URL.'/';
    
            $url = $static_url.'upload.php';
            header('content-type:text/html;charset=utf8');
    
            // php 版本如果大于等于5.5则需要使用CURLFile类进行文件是上传
            if (class_exists('\CURLFile')) {
                $img = new \CURLFile(realpath($remote_file_dir . '/' . $file_name));
                $data = array('pic' => $img, 'path' => $remote_file_dir);
            } else {
                // 加@符号curl就会把它当成是文件上传处理
                $data = array('pic' => '@' . realpath($remote_file_dir . '/' . $file_name), 'path' =>  $remote_file_dir);
            }

            $this->curl_post($url, $data);
    
            // 删除本地文件
            unlink($all_path.$file_name);
    
            return true;
        }
        return false;
    }
    
    /**
     * 获取Token
     * @return string
     */
    private function getToken() {
        $url = $this->getTokenUrlStr();
        $res = $this->curl_post($url,$postdata='',$options=array());
        $data = json_decode($res,JSON_FORCE_OBJECT);
        return $data['access_token'];
    }
    
    /**
     * Token参数拼接
     * @return string
     */
    private function getTokenUrlStr()
    {
        // 获取token的url
        $getTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?";
        // APPID
        $WXappid     =  "wxb8577287b7dfd5f5";
        // secret
        $WXsecret    = "4311a09544160e1af44e8cb82182f48c";
        $str  = $getTokenUrl;
        $str .= "grant_type=client_credential&";
        $str .= "appid=" . $WXappid . "&";
        $str .= "secret=" . $WXsecret;
        
        return $str;
    }
    
    /**
     * post 请求
     * @param  string $url
     * @param  string $postdata
     * @param  array  $options
     * @return string
     */
    private function curl_post($url, $postdata, $options=array()){
        $ch=curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if(!empty($options)){
            curl_setopt_array($ch, $options);
        }
        $data=curl_exec($ch);
        
        //记录通用错误日志
        $error = curl_error( $ch );
        if($error){
            $log_data['url'] = $url;
            $log_data['request_data'] = $postdata;
            $log_data['response_data'] = $error;
            Yii::$app->CommonLogger->logError("请求返回失败：".json_encode($log_data));
        }
        curl_close($ch);
        return $data;
    }
}