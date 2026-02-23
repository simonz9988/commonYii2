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
class Menu
{
    const MENU_CACHE_KEY = 'navigation'; // 菜单缓存key
    const MENU_PRIVILEGE_CACHE_KEY = 'menu_privilege'; // 权限带菜单层次缓存key

    /**
     * 设置用户导航栏信息
     * @param $username
     * @return bool
     */
    public static function setNavigation($username)
    {
        $menus = $navigation =  [];                              // 初始化定义导航栏信息
        $menus = self::getCasUserMenu($username);

        // 处理导航栏信息
        if ($menus) {
            $navigation = $menus;

            // 将导航栏信息添加到缓存
            $cache = Yii::$app->cache;
            $index = self::MENU_CACHE_KEY.$username;
            // 存在先删除
            if ($cache->get($index)) $cache->delete($index);
            return $cache->set($index, $navigation, Yii::$app->params['cacheTime']);
        }

        return false;
    }

    /**
     * 获取用户导航栏信息
     * @param $username
     * @return mixed
     */
    public static function getUserMenus($username, $refresh = false)
    {
        // 查询导航栏信息
        $menus = Yii::$app->cache->get(self::MENU_CACHE_KEY.$username);
        if ($refresh ||  ! $menus) {
            // 生成缓存导航栏文件
            Menu::setNavigation($username);
            $menus = Yii::$app->cache->get(self::MENU_CACHE_KEY.$username);
        }

        return $menus;
    }

    /**
     * 从中台获取用户菜单
     * @param $username
     * @return array
     */
    public static function getCasUserMenu($username){
        $url = SYSTEMADMIN_API_URL."/api/user/queryMenuByUserName?userName={$username}&domain=SHOP";

        $url = scheme_url($url);
        $result = curlGo($url);
        $result = json_decode($result, true);
        $menu = isset($result['data']['subMenuList']) ? $result['data']['subMenuList'] : array();

        return $menu;
    }

    /**
     * 获取本地带菜单层次结构的权限列表缓存
     * @param bool $refresh
     * @return mixed
     */
    public static function getPrivilegeWithMenu($refresh = false)
    {
        // 查询导航栏信息
        $privilege_menus = Yii::$app->cache->get(self::MENU_PRIVILEGE_CACHE_KEY);
        if ($refresh ||  ! $privilege_menus) {
            // 生成缓存导航栏文件
            Menu::setPrivilegeWithMenu();
            $privilege_menus = Yii::$app->cache->get(self::MENU_PRIVILEGE_CACHE_KEY);
        }

        return $privilege_menus;
    }

    /**
     * 从中台获取带菜单层次结构的权限列表，并且缓存到本地
     * @return bool
     */
    public static function setPrivilegeWithMenu()
    {
        $privilege_menus = self::getCasPrivilegeWithMenu();
        if ($privilege_menus) {
            $cache = Yii::$app->cache;
            $index = self::MENU_PRIVILEGE_CACHE_KEY;
            // 存在先删除
            if ($cache->get($index)){
                $cache->delete($index);
            }
            return $cache->set($index, $privilege_menus, Yii::$app->params['cacheTime']);
        }

        return false;
    }

    /**
     * 从中台获取带菜单层次结构的权限列表
     * @return array
     */
    public static function getCasPrivilegeWithMenu(){
        $url = SYSTEMADMIN_API_URL."/api/user/queryPrivilegeWithMenu?domain=SHOP";
        $url = scheme_url($url);
        $result = curlGo($url);
        $result = json_decode($result, true);

        $menu = isset($result['data']) ? $result['data'] : array();

        $result = array();
        if($menu){
            foreach($menu as $m){
                $result[$m['priValue']] = $m;
            }
        }
        return $result;
    }

    /**
     * 删除菜单缓存和权限带菜单层次缓存
     * @param string $username 用户名
     */
    public static function delAllCacheAboutMenu($username){

        $menu_key = self::MENU_CACHE_KEY.$username;
        $privilege_key = self::MENU_PRIVILEGE_CACHE_KEY;
        Yii::$app->cache->delete($menu_key);
        Yii::$app->cache->delete($privilege_key);
        

    }
}
