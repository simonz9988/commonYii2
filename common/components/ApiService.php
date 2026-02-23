<?php
namespace common\components;

use Yii;
/**
 * 接口操作缓存
 * Class CacheService
 */
class ApiService
{
    /**
     * 获取请求地址
     * @return string
     */
    private function getApiUrl()
    {
        $api_url = scheme_url(API_URL);
        return $api_url;
    }

    /**
     * 获取快递信息
     * @param $params
     * @return mixed
     */
    public function getExpressInfo($params)
    {
        $url = $this->getApiUrl();
        $url = $url.'/express/getExpressInfo';

        $result = curlGo($url,$params);
        $result = json_decode($result,true);
        return $result;
    }

}
