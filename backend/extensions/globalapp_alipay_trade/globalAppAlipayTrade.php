<?php
/**
 * @copyright Copyright(c) 2017
 * @file alipay.php
 * @brief 支付宝接口
 * @author haigang.chen
 * @date 2017-05-24
 * @version 2.0
 * @note
 */

namespace backend\extensions\globalapp_alipay_trade;

use Yii;
use backend\extensions\globalapp_alipay_trade\service\AlipayTradeService;
use backend\extensions\globalapp_alipay_trade\buildermodel\AlipayTradeRefundContentBuilder;
use backend\extensions\globalapp_alipay_trade\buildermodel\AlipayTradeFastpayRefundQueryContentBuilder;
use backend\extensions\globalapp_alipay_trade\buildermodel\AlipayTradeQueryContentBuilder;

/**
 * @class alipay_trade
 * @brief 支付宝接口
 */
class globalAppAlipayTrade
{
    public $config_params = array();
    public $gateway_url = 'https://openapi.alipay.com/gateway.do';
    
    
    /**
     * 配置
     * @param  array $config_param
     * @return null
     */
    public function setConfig($config_param)
    {
        $this->config_params = array (
            
            // 应用ID,您的APPID。
            'app_id' => $config_param['alipay.app_id'],
        
            // 商户私钥
            'merchant_private_key' => $config_param['alipay.private_key'],
        
            // 异步通知地址
            'notify_url' => '',
        
            // 同步跳转
            'return_url' => '',
        
            // 编码格式
            'charset' => "UTF-8",
        
            // 签名方式
            'sign_type'=>"RSA2",
        
            // 支付宝网关
            'gatewayUrl' => $this->gateway_url,
        
            // 支付宝公钥
            'alipay_public_key' => $config_param['alipay.ali_public_key'],
        );
    }
    
    /**
     * alipay.trade.refund(统一收单交易退款接口) 以下参数二选一（ out_trade_no 、 trade_no 如果同时存在优先取trade_no）
     * @param   string $trade_no         支付宝交易号
     * @param   string $refund_amount    需要退款的金额，该金额不能大于订单金额,单位为元，支持两位小数
     * @param   string $refund_trade_no  标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
     * @param   string $refund_reason    退款的原因说明
     * @param   string $out_trade_no     商户订单号
     * @return  array
     */
    public function refund($trade_no, $refund_amount, $refund_trade_no, $refund_reason = '', $out_trade_no)
    {
        // 请求数据
        $request_data = compact('out_trade_no', 'trade_no', 'refund_amount', 'refund_trade_no', 'refund_reason');
        
        //构造参数
        $RequestBuilder=new AlipayTradeRefundContentBuilder();
        $RequestBuilder->setOutTradeNo($out_trade_no);
        $RequestBuilder->setTradeNo($trade_no);
        $RequestBuilder->setRefundAmount($refund_amount);
        $RequestBuilder->setOutRequestNo($refund_trade_no);
        $RequestBuilder->setRefundReason($refund_reason);
        
        $aop = new AlipayTradeService($this->config_params);

        // alipay.trade.refund (统一收单交易退款接口)
        $response = (array)$aop->Refund($RequestBuilder);
        
        // 失败记录报警日志
        if(!$response || $response['code'] != '10000'){
            $error_info = [
                'request'  => $request_data,
                'response' => $response,
            ];
            Yii::$app->CommonLogger->logError("globalApp支付宝退款返回失败：".json_encode($error_info,JSON_UNESCAPED_UNICODE));
        }
    
        // 组装日志数据
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'globalApp支付宝退款返回信息',
            'redundancy_id' => $trade_no,
            'old_content' => [],
            'new_content' => $response
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
    
        return $response;
    }
    
    /**
     * 退款查询   alipay.trade.fastpay.refund.query (统一收单交易退款查询)
     * @param   string $trade_no         支付宝交易号
     * @param   string $refund_trade_no  标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
     * @param   string $out_trade_no     商户订单号
     * @return  array
     */
    public function refundQuery($trade_no, $refund_trade_no, $out_trade_no)
    {
        //构造参数
        $RequestBuilder=new AlipayTradeFastpayRefundQueryContentBuilder();
        $RequestBuilder->setOutTradeNo($out_trade_no);
        $RequestBuilder->setTradeNo($trade_no);
        $RequestBuilder->setOutRequestNo($refund_trade_no);
    
        $aop = new AlipayTradeService($this->config_params);
    
        /**
         * 退款查询   alipay.trade.fastpay.refund.query (统一收单交易退款查询)
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @return $response 支付宝返回的信息
         */
        $response = (array)$aop->refundQuery($RequestBuilder);
    
        // 组装日志数据
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'globalApp支付宝退款查询返回信息',
            'redundancy_id' => $trade_no,
            'old_content' => [],
            'new_content' => $response
        );
        // 日志操作
        Yii::$app->LogOperate->insert('refund', $log_data);
    
        return $response;
    }
    
    /**
     * 订单查询   alipay.trade.query (统一收单订单查询)
     * @param   string $pay_order_no     商户订单号
     * @return  array
     */
    public function orderQuery($pay_order_no)
    {
        //构造参数
        $RequestBuilder=new AlipayTradeQueryContentBuilder();
        $RequestBuilder->setOutTradeNo($pay_order_no);
        
        $aop = new AlipayTradeService($this->config_params);
        
        /**
         * 退款查询   alipay.trade.query (统一收单订单查询)
         * @param $builder 业务参数，使用buildmodel中的对象生成。
         * @return $response 支付宝返回的信息
         */
        $response = (array)$aop->Query($RequestBuilder);
        return $response;
    }
   
}
