<?php
/**
 * @copyright Copyright(c) 2018
 * @file    cmbLife.php
 * @brief   招行掌上生活分期接口
 * @author  haigang.chen
 * @date    2018-10-16
 * @version 1.0.1
 * @note
 */

namespace backend\extensions\cmb_life;

use Yii;
use backend\extensions\cmb_life\utils\CmblifeUtils;
use backend\extensions\cmb_life\lib\phpseclib\Crypt\Crypt_RSA;
use backend\extensions\cmb_life\lib\phpseclib\Math\Math_BigInteger;
use backend\extensions\cmb_life\utils\RsaUtils;
use backend\extensions\cmb_life\utils\AesUtils;
use backend\extensions\cmb_life\utils\URLUtils;

/**
 * @class cmb_life
 * @brief 招行掌上生活接口
 */
class cmbLife
{
    // 配置参数
    public $config_params = [];
    // 退款网关
    public $gateway_refund_url = 'https://open.cmbchina.com/AccessGateway/transIn/refund.json';
    // 退款查询网关
    public $gateway_refund_query_url = 'https://open.cmbchina.com/AccessGateway/transIn/getRefundOrder.json';
    // 订单查询网关
    public $gateway_order_query_url = 'https://open.cmbchina.com/AccessGateway/transIn/getPayOrder.json';
    
    public $error_code_info = [];

    /**
     * X constructor.
     */
    public function __construct()
    {
        require(__DIR__ . '/utils/ErrorCodeUtils.php');
        $this->error_code_info = $error_code;
    }
    
    /**
     * 配置
     * @param  array $config_param
     * @return null
     */
    public function setConfig($config_param)
    {
        if(YII_ENV == 'dev'){
            // 退款网关
            $this->gateway_refund_url = 'https://open.cmbchina.com/DevEnv/transIn/refund.json';
            // 退款查询网关
            $this->gateway_refund_query_url = 'https://open.cmbchina.com/DevEnv/transIn/getRefundOrder.json';
        }
        
        $this->config_params = [
            
            // 商户id
            'mid' => $config_param['mid'],
            
            // 应用ID
            'aid' => $config_param['aid'],
            
            // 招行分期公钥
            'public_key' => $this->getPublicKey($config_param['public_key']),
    
            // 招行分期私钥
            'private_key' => $this->getPrivateKey($config_param['private_key']),
        ];
    }
    
    /**
     * 退款接口
     * @param   string  $bill_no          交易号
     * @param   string  $refund_amount    需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
     * @param   string  $refund_token     退款流水号
     * @param   integer $bonus            退款积分
     * @return  array
     */
    public function refund($bill_no, $refund_amount, $refund_token, $bonus = 0)
    {
        $func_name = 'refund';
    
        // 请求参数
        $params = array(
            'mid' => $this->config_params['mid'],
            'aid' => $this->config_params['aid'],
            'date' => CmblifeUtils::genDate(),
            'random' => CmblifeUtils::genRandom(),
            'billNo' => $bill_no,
            'amount' => $refund_amount * 100,
            'bonus'  => $bonus,
            'refundToken' => $refund_token,
        );
        
        // 生成签名
        $cmblifeUtils = new CmblifeUtils();
        $sign = $cmblifeUtils->signForRequest($func_name, $params, $this->config_params['private_key']);
        $params["sign"] = urlencode($sign);

        // 发起请求
        $response = $this->httpPostData($this->gateway_refund_url, $params);
        
        // 验签
        $verify = $this->verifyForResponse($response);
        if(!$verify){
            $error_info = [
                'request'  => $params,
                'response' => $response,
            ];
            Yii::$app->CommonLogger->logError("招行分期退款验签失败：".json_encode($error_info,JSON_UNESCAPED_UNICODE));
            
            // 验签失败的单子直接置为失败状态
            $response['refundStatus'] = 3;
        }
        
        // 失败记录报警日志
        if(!$response || $response['respCode'] != '1000' || $response['refundStatus'] != 2){
            // 匹配错误信息
            $error_msg = $this->pregMatchErrorCode($response['respMsg']);
            $response['respMsg'] .= '。'.$error_msg;
            
            $error_info = [
                'request'  => $params,
                'response' => $response,
            ];
            Yii::$app->CommonLogger->logError("招行分期退款返回失败：".json_encode($error_info,JSON_UNESCAPED_UNICODE), 'refund');
        }
        
        // 组装日志数据
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => '招行分期退款返回信息',
            'redundancy_id' => $bill_no,
            'old_content' => [],
            'new_content' => $response
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
        
        return $response;
    }
    
    /**
     * 退款查询
     * @param   string $bill_no       业务订单号
     * @param   string $refund_token  退款流水号
     * @return  array
     */
    public function refundQuery($bill_no, $refund_token)
    {
        $func_name = 'getRefundOrder';
    
        // 请求参数
        $params = array(
            'mid' => $this->config_params['mid'],
            'aid' => $this->config_params['aid'],
            'date' => CmblifeUtils::genDate(),
            'random' => CmblifeUtils::genRandom(),
            'billNo' => $bill_no,
            'refundToken' => $refund_token,
        );
    
        // 生成签名
        $cmblifeUtils = new CmblifeUtils();
    
        $sign = $cmblifeUtils->signForRequest($func_name, $params, $this->config_params['private_key']);
        $params["sign"] = urlencode($sign);
        
        // 发起请求
        $response = $this->httpPostData($this->gateway_refund_query_url, $params);
        
        // 验签
        $verify = $this->verifyForResponse($response);
        if(!$verify){
            $error_info = [
                'request'  => $params,
                'response' => $response,
            ];
            Yii::$app->CommonLogger->logError("招行分期退款查询验签失败：".json_encode($error_info,JSON_UNESCAPED_UNICODE));
            
            // 验签失败的单子直接置为失败状态
            $response['refundStatus'] = 3;
        }
    
        // 失败记录报警日志
        if(!$response || $response['respCode'] != '1000' || $response['refundStatus'] != 2){
            // 匹配错误信息
            $error_msg = $this->pregMatchErrorCode($response['respMsg']);
            $response['respMsg'] .= '。'.$error_msg;
            
            $error_info = [
                'request'  => $params,
                'response' => $response,
            ];
            Yii::$app->CommonLogger->logError("招行分期退款查询返回失败：".json_encode($error_info,JSON_UNESCAPED_UNICODE), 'refund');
        }
    
        // 组装日志数据
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => '招行分期退款查询返回信息',
            'redundancy_id' => $bill_no,
            'old_content' => [],
            'new_content' => $response
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
    
        return $response;
    }
    
    /**
     * 订单查询
     * @param   string $pay_order_no  商户订单号
     * @return  array
     */
    public function orderQuery($pay_order_no)
    {
        $func_name = 'getPayOrder';

        // 请求参数
        $params = array(
            'mid' => $this->config_params['mid'],
            'aid' => $this->config_params['aid'],
            'date' => CmblifeUtils::genDate(),
            'random' => CmblifeUtils::genRandom(),
            'billNo' => $pay_order_no,
        );
    
        // 生成签名
        $cmblifeUtils = new CmblifeUtils();
    
        $sign = $cmblifeUtils->signForRequest($func_name, $params, $this->config_params['private_key']);
        $params["sign"] = urlencode($sign);
    
        // 发起请求
        $response = $this->httpPostData($this->gateway_order_query_url, $params);
    
        // 验签
        $verify = $this->verifyForResponse($response);
        if(!$verify){
            return [];
        }
    
        return $response;
    }
    
    /**
     * 获取私钥
     * @param  string $private_key
     * @return null
     */
    private function getPrivateKey($private_key)
    {
        $private_key = chunk_split($private_key, 64, "\n");
        $res = "-----BEGIN RSA PRIVATE KEY-----\n$private_key-----END RSA PRIVATE KEY-----\n";
        return $res;
    }
    
    /**
     * 获取公钥
     * @param  string $public_key
     * @return null
     */
    private function getPublicKey($public_key)
    {
    
        $public_key = chunk_split($public_key, 64, "\n");
        $res = "-----BEGIN PUBLIC KEY-----\n$public_key-----END PUBLIC KEY-----\n";
        return $res;
    }
    
    /**
     * 匹配招行错误码
     * @param  string $string
     * @return null
     */
    private function pregMatchErrorCode($string)
    {
        $res = '';
        preg_match_all("/\[(.*)\]/i", $string, $result);
        if(isset($result[1][0]) && $result[1][0]){
            $data = explode('-', $result[1][0]);
            $code = (isset($data[1]) ? $data[1] :9999);
            $res = (isset($this->error_code_info[$code]) ? $this->error_code_info[$code] : $this->error_code_info[9999]);
        }
        
        return $res;
    }
    
    /**
     * 发送网络请求
     * @param  string $url
     * @param  array  $params 参数
     * @return array
     */
    private function httpPostData($url, $params) {
    
       
        $cacert = '';   // CA根证书，暂不支持
        $CA = false;    // HTTPS时是否进行严格认证
        $TIME_OUT = 30; // 超时时间
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
    
        $url_utils = new UrlUtils();
        $dataString = $url_utils->mapToQueryString($params, false, false);
        
        $ch = curl_init();
        if ($SSL && $CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);//只信任CA发布的证书
            curl_setopt($ch, CURLOPT_CAINFO, $cacert);//CA根证书，用来验证网站证书是否是CA颁布
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//检查证书中是否设置域名，并且是否与提供的主机名匹配
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//信任任何证书
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//检查证书中是否设置域名
        }
        
        curl_setopt($ch, CURLOPT_TIMEOUT, $TIME_OUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $TIME_OUT - 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array (
            'Content-Type:application/x-www-form-urlencoded',
            'Content-Length:'.strlen($dataString)
        ));
        
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $return_info = curl_getinfo($ch);
    
        //记录通用错误日志
        $error = curl_error( $ch );
        if($error){
            $log_data['url'] = $url;
            $log_data['request_data'] = $params;
            $log_data['response_data'] = $error;
            Yii::$app->CommonLogger->logError("请求返回失败：".json_encode($log_data));
        }
    
        curl_close($ch);
    
        return json_decode($return_content, true);
    }
    
    /**
     * 对响应验签
     *
     * @param  array $array
     * @return boolean
     */
    private function verifyForResponse($array) {
        $params = $this->objectToArray($array);
        
        if(!isset($params['sign'])){
            return false;
        }
        $cmblifeUtils = new CmblifeUtils();
        $pubKey = $this->config_params['public_key'];
        $result = $cmblifeUtils->verifyWithParams($params, $pubKey);

        return $result ? true : false;
    }
    
    /**
     * 对象转数组
     * @param  $array
     * @return array
     */
    private function objectToArray($array) {
        if(is_object($array))
        {
            $array = (array)$array;
        }
        if(is_array($array))
        {
            foreach($array as $key=>$value)
            {
                $array[$key] = $this->objectToArray($value);
            }
        }
        return $array;
    }

}

