<?php
/**
 * @copyright Copyright(c) 2018
 * @file      ccbInstallment.php
 * @brief     建行分期接口
 * @author    haigang.chen
 * @date      2018-10-16
 * @version   1.0.1
 * @note
 */

namespace backend\extensions\ccb_installment;

use Yii;

/**
 * @class ccb_installment
 * @brief 建设银行分期接口
 */
class ccbInstallment
{
    // 配置参数
    public $config_params = [];
    
    // 请求退款服务地址
    private $socket_address = '127.0.0.1';
    
    /**
     * X constructor.
     */
    public function __construct()
    {
    
    }
    
    /**
     * 配置
     * @param array $config_param
     * @return null
     */
    public function setConfig($config_param)
    {
        
        $this->config_params = [
            
            // 商户id
            'mid'         => $config_param['mid'],
            // 操作员号
            'user_id'     => $config_param['user_id'],
            // 密码
            'password'    => $config_param['password'],
            // 公钥
            'public_key'  => $config_param['public_key'],
        ];
    }
    
    /**
     * 退款接口
     * @param string $bill_no       交易号
     * @param string $refund_amount 需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
     * @param string $refund_token  退款流水号
     * @param string $request_sn    请求序列号
     * @return  array
     */
    public function refund($bill_no, $refund_amount, $refund_token, $request_sn)
    {
        // 请求参数
        $params = array(
            'REQUEST_SN' => $request_sn,
            'CUST_ID'    => $this->config_params['mid'],
            'USER_ID'    => $this->config_params['user_id'],
            'PASSWORD'   => $this->config_params['password'],
            'TX_CODE'    => '5W1004',
            'LANGUAGE'   => 'CN',
            //'SIGN_INFO'  => '签名信息',
            //'SIGNCERT'   => '签名CA信息',
            'TX_INFO'    => [
                'MONEY'       => $refund_amount,
                'ORDER'       => $bill_no,
                'REFUND_CODE' => $refund_token,
            ],
        );
        
        // 生成XML格式
        $xml = $this->ToXml($params);
        
        // 发起请求
        $response = $this->socketXml($xml, $this->socket_address);
        
        // XML 转数组
        $result = $this->FromXml($response);
        
        // 失败记录报警日志
        if (!$result || $result['RETURN_CODE'] != '000000') {
            
            $error_info = [
                'request'  => $params,
                'response' => $result,
            ];
            Yii::$app->CommonLogger->logError("建行分期退款返回失败：" . json_encode($error_info, JSON_UNESCAPED_UNICODE), 'refund');
        }
        
        // 组装日志数据
        $log_data = array(
            'class_name'    => __CLASS__,
            'function_name' => __FUNCTION__,
            'action'        => '建行分期退款返回信息',
            'redundancy_id' => $bill_no,
            'old_content'   => [],
            'new_content'   => $result
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
        
        return $result;
    }
    
    /**
     * 退款查询
     * @param string $bill_no      业务订单号
     * @param string $request_sn   请求序列号
     * @return  array
     */
    public function refundQuery($bill_no, $request_sn)
    {
        // 请求参数
        $params = array(
            'REQUEST_SN' => $request_sn,
            'CUST_ID'    => $this->config_params['mid'],
            'USER_ID'    => $this->config_params['user_id'],
            'PASSWORD'   => $this->config_params['password'],
            'TX_CODE'    => '5W1003',
            'LANGUAGE'   => 'CN',
            'TX_INFO'    => [
                'KIND'     => '1',
                'ORDER'    => $bill_no,
                'NORDERBY' => '2',
                'PAGE '    => '1',
                'STATUS '  => '3',
            ],
        );
        
        // 生成XML格式
        $xml = $this->ToXml($params);
        
        // 发起请求
        $response = $this->socketXml($xml, $this->socket_address);
        // XML 转数组
        $result = $this->FromXml($response);
        
        // 失败记录报警日志
        if (!$result || $result['RETURN_CODE'] != '000000') {
            
            $error_info = [
                'request'  => $params,
                'response' => $result,
            ];
            Yii::$app->CommonLogger->logError("建行分期退款查询返回失败：" . json_encode($error_info, JSON_UNESCAPED_UNICODE), 'refund');
        }
        
        // 组装日志数据
        $log_data = array(
            'class_name'    => __CLASS__,
            'function_name' => __FUNCTION__,
            'action'        => '建行分期退款查询返回信息',
            'redundancy_id' => $bill_no,
            'old_content'   => [],
            'new_content'   => $result
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
        
        return $result;
    }
    
    /**
     * 输出xml字符
     * @param array $params
     * @return string
     **/
    private function ToXml($params)
    {
        $xml = "<TX>";
        foreach ($params as $key => $val) {
            if ($key == 'TX_INFO'){
            	if(is_array($val)) {
	                $xml .= "<TX_INFO>";
	                foreach ($val as $info_key => $info) {
	                    $xml .= "<" . $info_key . ">" . $info . "</" . $info_key . ">";
	                }
	                $xml .= "</TX_INFO>";
                }
            }else{
            	$xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
            
        }
        $xml .= "</TX>";
        return $xml;
    }
    
    /**
     * 将xml转为array
     * @param string $xml
     * @return array
     */
    private function FromXml($xml)
    {
        $content = str_replace('GB18030', 'UTF-8', $xml);
        $content = mb_convert_encoding($content, "UTF-8", "GB18030");
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }
    
    /**
     * 请求本地服务
     *
     * @param  string  $params
     * @param  string  $socket_address
     * @return string
     */
    private function socketXml($params, $socket_address)
    {
        // 创建socket
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            Yii::$app->CommonLogger->logError("建设银行分期SOCKET创建失败原因：" . mb_convert_encoding(socket_strerror($socket), "UTF-8", "GBK"), 'refund');
            return false;
        }
        
        //发送超时2秒
        socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>2, "usec"=>0 ) );
        //接收超时3秒
        //socket_set_option($socket,SOL_SOCKET,SO_SNDTIMEO,array("sec"=>2, "usec"=>0 ) );
        
        $result = socket_connect($socket, $socket_address, 55544);
        if ($result < 0) {
            Yii::$app->CommonLogger->logError("建设银行分期SOCKET连接失败原因: (".$result.") " . mb_convert_encoding(socket_strerror($result), "UTF-8", "GBK"), 'refund');
            return false;
        }
        
        // 发送命令
        $sent = socket_write($socket, $params, strlen($params));
        if ($sent === false) {
            Yii::$app->CommonLogger->logError("建设银行分期发送命令失败（socket_write）", 'refund');
            return false;
        }
        
        // 读取信息
        $socket_read_res = "";
        while ($out = socket_read($socket, 2048)) {
            $socket_read_res .= $out;
        }
        
        if($socket_read_res == ""){
            Yii::$app->CommonLogger->logError("建设银行分期读取失败: (socket_read) " . mb_convert_encoding(socket_strerror($result), "UTF-8", "GBK"), 'refund');
            return false;
        }
        
        // Close socket
        socket_close($socket);
        
        return $socket_read_res;
    }
    
    /**
     * 对象转数组
     * @param  $array
     * @return array
     */
    private function objectToArray($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = $this->objectToArray($value);
            }
        }
        return $array;
    }
    
    /**
     * 返回当前的毫秒时间戳
     * @return string
     */
    public function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }
}

