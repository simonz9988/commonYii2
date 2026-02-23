<?php
namespace common\components;

use Yii;

/**
 *
 * 清除缓存
 * @author haigang.chen
 * @date   2017-03-20 17:59:00
 */

class ClearCache
{
    /*
    **
    * 删除 redis 缓存方法
    * @param  array   $params
    * @return null
    */
    public function clearRedisCache($params)
    {
        $url = scheme_url(API_URL.'/clearCache/execute');
        $result = curlGo($url, $params);
        $result = json_decode($result, true);
        return $result;
    }

}