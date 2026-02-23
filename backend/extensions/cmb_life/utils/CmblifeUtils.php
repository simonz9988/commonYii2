<?php
namespace backend\extensions\cmb_life\utils;

use backend\extensions\cmb_life\lib\phpseclib\Crypt\Crypt_RSA;
/**
 * Created by CMB-CCD
 * V1.0.1
 */
class CmblifeUtils
{
    protected $rsa;

    /**
     * X constructor.
     */
    public function __construct()
    {
        $this->rsa = new Crypt_RSA();

        $this->aesUtils = new AesUtils();

        $this->rsaUtils = new RsaUtils();

        $this->urlUtils = new URLUtils();

        $this->jsonUtils = new JsonUtils();

        $this->rsa->setHash('sha256');
        //设置签名加密方式
        $this->rsa->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        //设置加密方式
        $this->rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
    }

    /**
     * 生成签名
     *
     * @param $plaintext
     * @return string
     */
    public function createSign($plaintext, $priKey, $signAlgorithm)
    {
        $this->rsa->setHash($signAlgorithm);
        $this->rsa->loadKey($priKey);
        $signature = $this->rsa->sign($plaintext);
        return base64_encode($signature);
    }

    /**
     * 验签,使用商户的公钥对返回的参数验签
     *
     * @param $ciphertext
     * @return string
     */
    public function verifySign($ciphertext, $signature, $pubKey, $signAlgorithm)
    {
        $this->rsa->setHash($signAlgorithm);
        $this->rsa->loadKey($pubKey);
        $signature = $this->rsa->verify($ciphertext, base64_decode($signature));
        return $signature;
    }

    /**
     * 拼接掌上生活协议
     * @param funcName 功能名
     * @param queryString 参数
     * @return 拼接后的字符串
     */
    public function assembleProtocol($funcName, $queryString) {
        return $this->urlUtils->assembleUrl('cmblife://'.$funcName, $queryString);
    }

    /**
     * 拼接掌上生活协议
     * @param funcName 功能名
     * @param paramsMap 参数
     * @param isUrlEncode 是否urlEncode
     * @return 拼接后的字符串
     */
    public function assembleProtocolWithParams($funcName, $params, $isUrlEncode) {
        return $this->urlUtils->assembleUrlWithParams('cmblife://'.$funcName, $params, $isUrlEncode);
    }

    /**
     * 拼接签名
     * @param protocol 协议
     * @param sign 签名
     * @return 拼接签名后的协议
     */
    public function assembleSign($protocol, $sign) {
        if (empty($protocol)) {
            return null;
        }
        return $protocol.(strpos($protocol, "?") !== false ? "&" : "?")."sign=".urlencode($sign);
    }

    /**
     * 生成掌上生活协议,带有签名
     * @param funcName 功能名
     * @param paramsMap 参数
     * @param signKey 签名所使用的Key，为商户私钥
     * @param signAlgorithm 签名算法（SHA1WithRSA 或 SHA256WithRSA）
     * @return 掌上生活协议
     */
    public function genProtocolWithAlgorithm($funcName, $params, $signKey, $signAlgorithm) {
        if (empty($funcName)) {
            return null;
        }
        $signProtocol = $this->assembleProtocolWithParams($funcName, $params, false);
        $sign = $this->createSign($signProtocol, $signKey, $signAlgorithm);
        return $this->assembleSign($this->assembleProtocolWithParams($funcName, $params, true), $sign);
    }

    /**
     * 生成掌上生活协议，带有签名
     * @param funcName 功能名
     * @param paramsMap 参数
     * @param signKey 签名所使用的Key，为商户私钥
     * @return 掌上生活协议
     */
    public function genProtocol($funcName, $params, $signKey) {
        return $this->genProtocolWithAlgorithm($funcName, $params, $signKey, 'sha256');
    }

    /**
     * 生成请求报文体
     * @param params 参数
     * @return 请求报文体，如： key1=value1&key2=value2...
     */
    public function genRequestBody($params) {
        if (empty($params)) {
            return null;
        }
        return $this->urlUtils->assembleUrlWithParams(null, $params, true);
    }

    /**
     * 对响应验签
     * 调用方向：商户 --> 掌上生活
     *
     * @param response 响应报文
     * @param verifyKey 验签所使用的Key，为掌上生活公钥
     * @return true为验签成功，false为验签失败
     * @throws GeneralSecurityException
     */
    public function verifyForResponse($response, $verifyKey) {
        $params = $this->jsonUtils->stringToObject($response);
        $sign = $params["sign"];
        unset($params["sign"]);
        return $this->verify($this->urlUtils->assembleUrlWithParams("", $params, false), $sign, $verifyKey, 'sha256');
    }

    /**
     * 对请求验签
     * 调用方向：掌上生活 --> 商户
     *
     * @param params 参数
     * @param verifyKey 验签所使用的Key，为掌上生活公钥
     * @return true为验签成功，false为验签失败
     * @throws GeneralSecurityException
     */
    public function verifyForRequest($params, $verifyKey) {
        $sign = $params["sign"];
        unset($params["sign"]);
        return $this->verify($this->urlUtils->assembleUrlWithParams("", $params, false), $sign, $verifyKey, 'sha256');
    }

    /**
     * 对响应验签
     * 调用方向：商户 --> 掌上生活
     *
     * @param response 响应报文
     * @param verifyKey 验签所使用的Key，为掌上生活公钥
     * @return true为验签成功，false为验签失败
     * @throws GeneralSecurityException
     */
    public function signForRequest($funcName, $params, $signKey) {
        return $this->sign($this->urlUtils->assembleUrlWithParams($funcName.".json", $params, false), $signKey, 'sha256');
    }

    /**
     * 对响应签名
     * 调用方向：掌上生活 --> 商户
     *
     * @param params 参数
     * @param signKey 签名使用的Key，为商户RSA私钥
     * @return 签名
     * @throws GeneralSecurityException
     */
    public function signForResponse($params, $signKey) {
        return $this->sign($this->urlUtils->assembleUrlWithParams("", $params, false), $signKey, 'sha256');
    }

    /**
     * 签名
     * @param signBody 待签名数据，queryString
     * @param signKey 签名使用的Key，为商户私钥
     * @param signAlgorithm 签名算法（SHA1WithRSA 或 SHA256WithRSA）
     * @return 签名
     */
    public function signWithAlgorithm($signBody, $signKey, $signAlgorithm) {
        if (empty($signBody) || empty($signKey) || empty($signAlgorithm)) {
            return null;
        }
        return $this->createSign($signBody, $signKey, $signAlgorithm);
    }

    /**
     * 签名
     * @param signBody 待签名数据，queryString
     * @param signKey 签名使用的Key，为商户私钥
     * @return 签名
     */
    public function sign($signBody, $signKey) {
        if (empty($signBody) || empty($signKey)) {
            return null;
        }
        return $this->signWithAlgorithm($signBody, $signKey, 'sha256');
    }

    /**
     * 签名
     * @param paramsMap 待签名数据，params
     * @param signKey 签名使用的Key，为商户私钥
     * @param signAlgorithm 签名算法（SHA1WithRSA 或 SHA256WithRSA）
     * @return 签名
     */
    public function signWithParamsAndAlgorithm($params, $signKey, $signAlgorithm) {
        if (empty($params) || empty($signKey) || empty($signAlgorithm)) {
            return null;
        }
        return $this->signWithAlgorithm($this->urlUtils->mapToQueryString($params, true, false), $signKey, $signAlgorithm);
    }

    /**
     * 签名
     * @param paramsMap 待签名数据，queryString
     * @param signKey 签名使用的Key，为商户私钥
     * @return 签名
     */
    public function signWithParams($params, $signKey) {
        if (empty($params) || empty($signKey)) {
            return null;
        }
        return $this->signWithParamsAndAlgorithm($params, $signKey, 'sha256');
    }

    /**
     * 签名
     * @param prefix 前缀，如interface.json
     * @param paramsMap 待签名数据，queryString
     * @param signKey 签名使用的Key，为商户私钥
     * @param signAlgorithm 签名算法（SHA1WithRSA 或 SHA256WithRSA）
     * @return 签名
     */
    public function signWithPrefixAndParamsAndAlgorithm($prefix, $params, $signKey, $signAlgorithm) {
        $url = $this->urlUtils->assembleUrlWithParams($prefix, $params, false);
        return $this->sign($url, $signKey, $signAlgorithm);
    }

    /**
     * 签名
     * @param prefix 前缀，如interface.json
     * @param paramsMap 待签名数据，queryString
     * @param signKey 签名使用的Key，为商户私钥
     * @param signAlgorithm 签名算法（SHA1WithRSA 或 SHA256WithRSA）
     * @return 签名
     */
    public function signWithPrefixAndParams($prefix, $params, $signKey) {
        $url = $this->urlUtils->assembleUrlWithParams($prefix, $params, false);
        return $this->sign($url, $signKey, 'sha256');
    }

    /**
     * 验签
     * @param verifyBody 待验签的数据，queryString
     * @param sign 签名
     * @param verifyKey 验签所使用的Key，为掌上生活公钥
     * @param verifyAlgorithm  验签算法（SHA1WithRSA 或 SHA256WithRSA）
     * @return 验签结果
     */
    public function verifyWithAlgorithm($verifyBody, $sign, $verifyKey, $algorithm) {
        if (empty($verifyBody) || empty($sign) || empty($verifyKey) || empty($algorithm)) {
            return null;
        }
        return $this->verifySign($verifyBody, $sign, $verifyKey, $algorithm);
    }

    /**
     * 验签
     * @param verifyBody 待验签的数据，queryString
     * @param sign 签名
     * @param verifyKey 验签所使用的Key，为掌上生活公钥
     * @return 验签结果
     */
    public function verify($verifyBody, $sign, $verifyKey) {
        return $this->verifyWithAlgorithm($verifyBody, $sign, $verifyKey, 'sha256');
    }

    /**
     * 验签
     * @param paramsMap 掌上生活返回报文
     * @param verifyKey 验签所使用的Key，为掌上生活公钥
     * @param verifyAlgorithm 验签算法（SHA1WithRSA 或 SHA256WithRSA）
     * @return 验签结果
     */
    public function verifyWithParamsAndAlgorithm($params, $verifyKey, $algorithm) {
        $sign = $params['sign'];
        unset($params['sign']);
        return $this->verifyWithAlgorithm($this->urlUtils->mapToQueryString($params, true, false), $sign, $verifyKey, $algorithm);
    }

    /**
     * 验签
     * @param paramsMap 掌上生活返回报文
     * @param verifyKey 验签所使用的Key，为掌上生活公钥
     * @return 验签结果
     */
    public function verifyWithParams($params, $verifyKey) {
        return $this->verifyWithParamsAndAlgorithm($params, $verifyKey, 'sha256');
    }

    /**
     * 掌上生活加密
     * @param encryptBody 需要加密的字符串
     * @param encryptKey 加密使用的Key，为掌上生活RSA公钥
     * @return 密文
     */
    public function encrypt($encryptBody, $encryptKey) {
        if (empty($encryptBody) || empty($encryptKey)) {
            return null;
        }
        $aesKey = $this->aesUtils->genKey(128);
        $aesEncryptedBody = $this->aesUtils->encrypt($encryptBody, $aesKey, 'ECB', 'PKCS5', null, 128);
        $encryptedAesKey = $this->rsaUtils->encrypt(base64_decode($aesKey), CRYPT_RSA_ENCRYPTION_PKCS1, $encryptKey);
        return $encryptedAesKey."|".$aesEncryptedBody;
    }

    /**
     * 掌上生活解密
     * @param decryptBody 需要解密的字符串
     * @param decryptKey 解密使用的Key，为商户RSA私钥
     * @return 明文
     */
    public function decrypt($decryptBody, $decryptKey) {
        if (empty($decryptBody) || empty($decryptKey)) {
            return null;
        }
        $data = explode('|', $decryptBody);
        if(2 != count($data)) {
            return null;
        }
        $aesKey = $this->rsaUtils->decrypt($data[0], CRYPT_RSA_ENCRYPTION_PKCS1, $decryptKey);
        return $this->aesUtils->decrypt($data[1], base64_encode($aesKey), 'ECB', 'PKCS5', null, 128);
    }

     /**
     * 将json字符串反序列化为map
     *
     * @param json json字符串
     * @return 参数
     */
    public function jsonToMap($json) {
        return $this->jsonUtils->stringToObject($json);
    }

    /**
     * 将map序列化为json字符串
     *
     * @param params 参数
     * @return json字符串
     */
    public function mapToJson($params) {
        return $this->jsonUtils->objectToString($params);
    }
    
    /**
     * 生成日期
     *
     * @return 日期，格式为yyyyMMddHHmmss
     */
    public static function genDate() {
        date_default_timezone_set('PRC');
        return date("YmdHis");
    }

    /**
     * 生成随机数
     *
     * @return 随机数，为UUID
     */
    public static function genRandom() {
        return str_replace('-', '', uniqid());
    }
}
?>