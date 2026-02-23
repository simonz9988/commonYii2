<?php
namespace backend\extensions\cmb_life\utils;
/**
 * @author CMB_CCD_Developer
 * V1.0.1
 */
class URLUtils{

     public function __construct()
    {
        $this->jsonUtils = new JsonUtils();
    }

    /**
     * 将map转为queryString
     * @param map 参数
     * @param isSort 是否排序
     * @param isUrlEncode 是否需要UrlEncode
     * @return String
     */
    public function mapToQueryString($params, $isSort, $isUrlEncode)
    {
        if (sizeof($params) <= 0) {
            return null;
        }
        $tempParams = $params;
        if ($isSort) {
            ksort ($params);
            $tempParams = $params;
        }
        foreach($tempParams as $key=>$value) {
            if(empty($value) && "0" != $value) {
                continue;
            }
            if(false === $value) {$value = "false";}
            if(true === $value) {$value = "true";}
            if (is_array($value)) {
                $value = $this->transfer($value);
            }
            if ($isUrlEncode) {
                $value = urlencode($value);
            }
            $sb[] = $key.'='.$value;
        }

        return implode('&', $sb);
    }

    /**
     * 将Array转为String
     * @param value 参数
     * @return String
     */
    private function transfer($value) {
      if($this->_isAssocArray($value)) {
            return $this->jsonUtils->objectToString($value);
      } else {
            foreach ($value as $k=>$v){ 
              if(is_array($v)) {
                $value[$k] = $this->transfer($v);
              }
            } 
            return "[".implode(",", $value)."]";
      }     
    }

    /**
     * 判断数组是否为关联数组
     * @param $var 参数
     * @return boolen
     */
    private function _isAssocArray(array $var) {  
        return array_diff_assoc(array_keys($var), range(0, sizeof($var)));  
    }  

    /**
     * 拼接签名字符串
     * @param prefix 前缀
     * @param queryString 参数
     * @return 拼接后的字符串
     */
    public function assembleUrl($prefix, $queryString) {
        return empty($prefix) ? $queryString : 
        $prefix.(strpos($prefix, "?") !== false ? "&" : "?").$queryString;
    }

    /**
     * 拼接签名字符串
     * @param prefix 前缀
     * @param paramsMap 参数
     * @param isUrlEncode 是否urlEncode
     * @return 拼接后的字符串
     */
    public function assembleUrlWithParams($prefix, $params, $isUrlEncode) {
        return $this->assembleUrl($prefix, $this->mapToQueryString($params, true, $isUrlEncode));
    }
    
}
?>