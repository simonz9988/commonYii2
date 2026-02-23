<?php

namespace backend\components;

use Yii;
use common\models\SiteConfig;


/**
 * This is the model class for table "{{%menu}}".
 *
 * @property integer $id
 * @property string  $pid
 * @property string  $menu_name
 * @property string  $icons
 * @property string  $url
 * @property integer $status
 * @property integer $sort
 * @property integer $created_at
 * @property integer $created_id
 * @property integer $updated_at
 * @property integer $updated_id
 */
class Privilege
{
    // 缓存key
    const CACHE_KEY = 'privilege';

    /**
     * 设置用户权限信息
     * @param $username
     * @return bool
     */
    public static function setPrivilege($username)
    {
        $privileges = self::getCasUserPrivileges($username);
        if ($privileges) {

            // 将权限信息添加到缓存
            $cache = Yii::$app->cache;
            $index = self::CACHE_KEY.$username;
            // 存在先删除
            if ($cache->get($index)) $cache->delete($index);
            return $cache->set($index, $privileges, Yii::$app->params['cacheTime']);
        }

        return false;
    }

    /**
     * 获取用户权限信息
     * @param $username
     * @return mixed
     */
    public static function getUserPrivileges($username, $refresh = false)
    {
        // 查询导航栏信息
        $privileges = Yii::$app->cache->get(self::CACHE_KEY.$username);
        if ($refresh || ! $privileges) {
            // 生成缓存导航栏文件
            self::setPrivilege($username);
            $privileges = Yii::$app->cache->get(self::CACHE_KEY.$username);
        }

        return $privileges;
    }

    /**
     * 从中台获取用户权限
     * @param $username
     * @return array
     */
    public static function getCasUserPrivileges($username){
        $url = SYSTEMADMIN_API_URL."/api/user/queryPrivilegeByUserName?userName={$username}&domain=SHOP";
        $url = scheme_url($url);
        $result = curlGo($url);
        $result = json_decode($result, true);
        $privilege = isset($result['data']) ? $result['data'] : array();

 		//记录错误日志
        if($result['code'] != '0000'){
            $log_data['url'] = $url;
            $log_data['response_data'] = $result;
            Yii::$app->CommonLogger->logError("中台获取用户权限返回失败：".json_encode($log_data));
        }
        return $privilege;
    }

    /**
     * 删除权限相关缓存
     * @param string $username 用户名
     */
    public static function delAllCacheAboutPrivilege($username){
        $cache_key = self::CACHE_KEY.$username;
        Yii::$app->cache->delete($cache_key);
    }


}
