<?php
namespace backend\components;

use common\models\Goods;
use common\models\PushTask;
use Yii;

/**
 *
 * 微信好物圈
 * 参考文档如下
 * Note:https://wsad.weixin.qq.com/wsad/zh_CN/htmledition/order/html/document/orderlist/import.part.html
 */

class WxHaowuquan {

    /**
     * 获取小程序码(不是二维码)
     * @param  array  $list
     * @return bool
     */
    public function syncGoods($list){

        $url = "https://api.weixin.qq.com/mall/importproduct?";
        $url .= "access_token=" . $this->getToken();
        $postdata = [
            "product_list" => $list
        ];

        // 测试环境直接跳过不做任何推送
        if(YII_ENV == 'dev') {
            return true ;
        }
       
        // 成功直接返回图片二进制内容，失败返回错误json对象
        $res = $this->curl_post($url,json_encode($postdata,JSON_UNESCAPED_UNICODE),$options=array());
        // 判断是否有错误
        $wx_code_data = json_decode($res, true);
        $error_code = isset($wx_code_data['errcode']) ? $wx_code_data['errcode'] : 9999 ;
        if($error_code!=0){
            Yii::$app->CommonLogger->logError("好物圈同步商品错误：".json_encode($wx_code_data, JSON_UNESCAPED_UNICODE));
            return false;
        }

        return true ;


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

    /**
     * 同步订单信息
     * @param $order_list
     * @return bool
     */
    public function syncOrder($order_list){

        #暂时导入测试数据
        //$url = 'https://api.weixin.qq.com/mall/importorder?action=add-order&is_history=0/1&access_token='. $this->getToken();
        if(YII_ENV == 'dev') {
            $url = 'https://api.weixin.qq.com/mall/importorder?action=add-order&is_test=1&access_token=' . $this->getToken();
        }else if(YII_ENV == 'pro'){
            $url = 'https://api.weixin.qq.com/mall/importorder?action=add-order&is_history=0/1&access_token='. $this->getToken();
        }
        $post_data = [
            "order_list" => $order_list
        ];

        // 成功直接返回图片二进制内容，失败返回错误json对象
        $res = $this->curl_post($url,json_encode($post_data,JSON_UNESCAPED_UNICODE),$options=array());
        // 判断是否有错误
        $wx_code_data = json_decode($res, true);
        $error_code = isset($wx_code_data['errcode']) ? $wx_code_data['errcode'] : 9999 ;
        if($error_code!=0){

            Yii::$app->CommonLogger->logError("好物圈同步订单错误：".json_encode($wx_code_data, JSON_UNESCAPED_UNICODE));

            return false;
        }

        return true;
    }

    /**
     * @param $cart_list
     */
    public function syncCart($cart_list){
        if(YII_ENV == 'dev'){
            $url = 'https://api.weixin.qq.com/mall/addshoppinglist?is_test=1&access_token='. $this->getToken();
        }else if(YII_ENV == 'pro'){
            $url = 'https://api.weixin.qq.com/mall/addshoppinglist?access_token='. $this->getToken();
        }

        $post_data = $cart_list ;

        // 成功直接返回图片二进制内容，失败返回错误json对象
        $res = $this->curl_post($url,json_encode($post_data,JSON_UNESCAPED_UNICODE),$options=array());
        // 判断是否有错误
        $wx_code_data = json_decode($res, true);
        $error_code = isset($wx_code_data['errcode']) ? $wx_code_data['errcode'] : 9999 ;
        if($error_code!=0){
            Yii::$app->CommonLogger->logError("好物圈同步收藏列表错误：".json_encode($wx_code_data, JSON_UNESCAPED_UNICODE));
            return false;
        }

        return true;
    }
}