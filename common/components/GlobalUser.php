<?php

namespace common\components;

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
class GlobalUser
{
    // 缓存key
    const CACHE_KEY = 'global_user';

    /**
     * 设置用户权限信息
     * @param $username
     * @return bool
     */
    public static function setUserInfo($username)
    {
        $user_info = self::getGlobalUserInfo($username);
        if ($user_info) {
            $cache = Yii::$app->cache;
            $index = self::CACHE_KEY.$username;

            if ($cache->get($index)){
                $cache->delete($index);
            }
            return $cache->set($index, $user_info, Yii::$app->params['cacheTime']);
        }

        return false;
    }

    /**
     * 获取用户信息
     * @param $username
     * @param bool $refresh
     * @return mixed
     */
    public static function getUserInfo($username, $refresh = false)
    {
        $user_info = Yii::$app->cache->get(self::CACHE_KEY.$username);
        if ($refresh || ! $user_info) {
            self::setUserInfo($username);
            $user_info = Yii::$app->cache->get(self::CACHE_KEY.$username);
        }

        return $user_info;
    }

    /**
     * 从中台获取用户信息
     * @param $username
     * @return array
     */
    public static function getGlobalUserInfo($username){
        $url = SYSTEMADMIN_API_URL."/api/user/queryUserInfoByUserName?userName={$username}";
        $url = scheme_url($url);

        $result = curlGo($url);
        $result = json_decode($result, true);
        $privilege = isset($result['data']) ? $result['data'] : array();
        return $privilege;
    }
}
