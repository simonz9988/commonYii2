<?php
/**
 * Class ClientInfo
 * 获取设备相关的信息
 * 这是个和框架无关的信息获取类
 */

class ClientInfo {
    /**
     * SOURCE 类型修饰的是‘来源相关’的常量
     * 如果更新了来源信息，需要在 getSourceList 中补充信息
     */
    const SOURCE_GLOBALAPP      = "globalapp";
    const SOURCE_APP            = "app";
    const SOURCE_WEIXIN         = "weixin";
    const SOURCE_WEIXINMINI     = "weixinmini";
    const SOURCE_WAP            = "wap";
    const SOURCE_PC             = "pc";
    const SOURCE_NEWRETAIL      = "newretail";
    const SOURCE_BAIDUMINI      = "baidumini";

    const SOURCE_LABEL_GLOBALAPP      = "ECOVACS HOME App";
    const SOURCE_LABEL_APP            = "科沃斯机器人APP";
    const SOURCE_LABEL_WEIXIN         = "微信";
    const SOURCE_LABEL_WEIXINMINI     = "微信小程序";
    const SOURCE_LABEL_WAP            = "移动版";
    const SOURCE_LABEL_PC             = "电脑版";
    const SOURCE_LABEL_BAIDUMINI      = "百度小程序";
    
    const SENSORS_LABEL_GLOBALAPP      = "ECOVACSHOME";
    const SENSORS_LABEL_APP            = "科沃斯机器人";
    const SENSORS_LABEL_WEIXIN         = "微信公众号";
    const SENSORS_LABEL_WEIXINMINI     = "微信小程序";
    const SENSORS_LABEL_WAP            = "Wap";
    const SENSORS_LABEL_PC             = "Web";
    const SENSORS_LABEL_BAIDUMINI      = "百度小程序";

    /**
     * SYSTEM 系统，因为版本差异较大，这里只罗列系统的品牌而系列忽略
     * 如果更新了系统信息，需要在 getSystemList 中补充信息
     */
    const SYSTEM_WINDOWS    = "Windows";
    const SYSTEM_LINUX      = "Linux";
    const SYSTEM_UNIX       = "Unix";
    const SYSTEM_ANDROID    = "Android";
    const SYSTEM_IOS        = "IOS";
    const SYSTEM_MAC        = "Mac OS";
    const SYSTEM_OTHER      = "Other System";

    const SYSTEM_LABEL_WINDOWS    = "Windows";
    const SYSTEM_LABEL_LINUX      = "Linux";
    const SYSTEM_LABEL_UNIX       = "Unix";
    const SYSTEM_LABEL_ANDROID    = "Android";
    const SYSTEM_LABEL_IOS        = "iOS";
    const SYSTEM_LABEL_MAC        = "Mac OS";
    const SYSTEM_LABEL_OTHER      = "Other System";

    /**
     * DEVICE 硬件设备类型
     * 如果更新了设备信息，需要在 getDeviceList 中补充信息
     */
    const DEVICE_PC     = "pc";
    const DEVICE_MOBILE = "mobile";
    const DEVICE_PAD    = "pad";

    const DEVICE_LABEL_PC       = "PC端";
    const DEVICE_LABEL_MOBILE   = "移动端";
    const DEVICE_LABEL_PAD      = "PAD端";

    /**
     * 爬虫的标识
     * 如果只是想知道是否是爬虫，调用 isRobot() 即可
     */
    const ROBOT_GOOGLE	= "Google";
    const ROBOT_BAIDU	= "Baidu";
    const ROBOT_360     = "360";
    const ROBOT_SOSO    = "Soso";
    const ROBOT_SOGOU   = "Sogou";
    const ROBOT_YAHOO   = "Yahoo";
    const ROBOT_YOUDAO  = "YouDao";
    const ROBOT_MSN     = "MSN";
    const ROBOT_BING    = "Bing";
    const ROBOT_YISOU   = "YiSou";
    const ROBOT_ALEXA   = "Alexa";
    const ROBOT_ETAO    = "Etao";
    const ROBOT_HUMAN   = "human";
    
    /**
     * 获取神策来源列表
     * @return array
     */
    static function getSensorsSourceList() {
        return [
            self::SOURCE_GLOBALAPP  => self::SENSORS_LABEL_GLOBALAPP,
            self::SOURCE_APP        => self::SENSORS_LABEL_APP,
            self::SOURCE_WEIXIN     => self::SENSORS_LABEL_WEIXIN,
            self::SOURCE_WEIXINMINI => self::SENSORS_LABEL_WEIXINMINI,
            self::SOURCE_WAP        => self::SENSORS_LABEL_WAP,
            self::SOURCE_PC         => self::SENSORS_LABEL_PC,
            self::SOURCE_BAIDUMINI  => self::SENSORS_LABEL_BAIDUMINI
        ];
    }
    
    /**
     * 获取来源列表
     * @return array
     */
    static function getSourceList() {
        return [
            self::SOURCE_GLOBALAPP  => self::SOURCE_LABEL_GLOBALAPP,
            self::SOURCE_APP        => self::SOURCE_LABEL_APP,
            self::SOURCE_WEIXIN     => self::SOURCE_LABEL_WEIXIN,
            self::SOURCE_WEIXINMINI => self::SOURCE_LABEL_WEIXINMINI,
            self::SOURCE_WAP        => self::SOURCE_LABEL_WAP,
            self::SOURCE_PC         => self::SOURCE_LABEL_PC,
            self::SOURCE_BAIDUMINI  => self::SOURCE_LABEL_BAIDUMINI
        ];
    }

    /**
     * 获取系统列表
     * @return array
     */
    static function getSystemList() {
        return [
            self::SYSTEM_WINDOWS    => self::SYSTEM_LABEL_WINDOWS,
            self::SYSTEM_LINUX      => self::SYSTEM_LABEL_LINUX,
            self::SYSTEM_UNIX       => self::SYSTEM_LABEL_UNIX,
            self::SYSTEM_ANDROID    => self::SYSTEM_LABEL_ANDROID,
            self::SYSTEM_IOS        => self::SYSTEM_LABEL_IOS,
            self::SYSTEM_MAC        => self::SYSTEM_LABEL_MAC,
            self::SYSTEM_OTHER      => self::SYSTEM_LABEL_OTHER
        ];
    }

    /**
     * 获取设备列表
     * @return array
     */
    static function getDeviceList() {
        return [
            self::DEVICE_PC     => self::DEVICE_LABEL_PC,
            self::DEVICE_MOBILE => self::DEVICE_LABEL_MOBILE,
            self::DEVICE_PAD    => self::DEVICE_LABEL_PAD
        ];
    }

    /**
     * 通过系统的值，来匹配对应的中文名称
     * @param $value
     * @return string
     */
    static function getSystemLabel($value) {
        $list = self::getSystemList();

        if (isset($list[$value])) {
            return $list[$value];
        }

        return "";
    }

    /**
     * 通过设备的值，来匹配对应的中文名称
     * @param $value
     * @return string
     */
    static function getDeviceLabel($value) {
        $list = self::getDeviceList();

        if (isset($list[$value])) {
            return $list[$value];
        }

        return "";
    }

    /**
     * 通过来源的值，来匹配对应的中文名称
     * @param $value
     * @return string
     */
    static function getSourceLabel($value) {
        $list = self::getSourceList();

        if (isset($list[$value])) {
            return $list[$value];
        }

        return "";
    }
    
    /**
     * 通过来源的值，来匹配对应的中文名称
     * @param $value
     * @return string
     */
    static function getSensorsSourceLabel($value) {
        $list = self::getSensorsSourceList();
        
        if (isset($list[$value])) {
            return $list[$value];
        }
        
        return "";
    }

    /**
     * 获取访问来源
     * @TODO 微信小程序的来源对于页面展示的情况只有注入到 JS 当中的 window.__wxjs_environment 是准确的
     * @TODO 微信小程序的异步请求只认自定义 Header 头，其他情况不准确
     *
     * @return string
     */
    static function getSource() {
        // Global App 的判断 user agent 是不准确的，在多个 webview 当中可能会不存在
        if (isset($_SERVER["HTTP_USER_AGENT"]) && stripos($_SERVER["HTTP_USER_AGENT"], "ecovacsGlobalApp") !== false) {
            return self::SOURCE_GLOBALAPP;
        }

        if (isset($_SERVER["HTTP_USER_AGENT"]) && stripos($_SERVER["HTTP_USER_AGENT"], "globalApp") !== false) {
            return self::SOURCE_GLOBALAPP;
        }

        if (isset ($_SERVER["HTTP_USER_AGENT"]) && stripos($_SERVER["HTTP_USER_AGENT"], "ecovacsApp") !== false) {
            return self::SOURCE_APP;
        }

        // 生态圈 APP 和 Global App 共用一部分设备信息
        // 需要通过 APPCODE 进行设备区分
        if (isset($_SERVER["HTTP_APPCODE"]) && isset($_SERVER["HTTP_CHANNEL"]) && isset($_SERVER["HTTP_APPVERSION"]) && isset($_SERVER["HTTP_DEVICEID"]) && isset($_SERVER["HTTP_DEVICETYPE"])) {
            if ($_SERVER["HTTP_APPCODE"] == "global_e" || $_SERVER["HTTP_APPCODE"] == "global_a") {
                return self::SOURCE_GLOBALAPP;
            } else if ($_SERVER["HTTP_APPCODE"] == "eco_e" || $_SERVER["HTTP_APPCODE"] == "eco_a") {
                return self::SOURCE_APP;
            }
        }

        // 判断微信的各个渠道
        if (isset($_SERVER["HTTP_USER_AGENT"]) && stripos($_SERVER["HTTP_USER_AGENT"], "MicroMessenger") !== false) {

            // 判断是否微信小程序
            // webview 和 微信浏览器共用 cookie 等信息，所以通过判断 session id 的方式会导致相互污染

            // miniprogram 的标识暂时只出现在调试工具上
            if (isset($_SERVER["HTTP_USER_AGENT"]) && stripos($_SERVER["HTTP_USER_AGENT"], "miniprogram") !== false) {
                return self::SOURCE_WEIXINMINI;
            } else if (isset($_SERVER["HTTP_MINIPROGRAM_REQUEST"])) {
                // 微信小程序在发起请求的时候，手动设置的 Header 值
                return self::SOURCE_WEIXINMINI;
            }

            // 判断是否微信公众号浏览
            return self::SOURCE_WEIXIN;
        }
    
        // 判断百度渠道
        if (isset($_SERVER["HTTP_USER_AGENT"]) && stripos($_SERVER["HTTP_USER_AGENT"], "swan-baiduboxapp") !== false) {
            return self::SOURCE_BAIDUMINI;
        }

        // 如果有 HTTP_X_WAP_PROFILE 则一定是移动设备
        if (isset($_SERVER["HTTP_X_WAP_PROFILE"])) {
            return self::SOURCE_WAP;
        }

        // 如果 via 信息含有 wap 则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER["HTTP_VIA"])) {
            // 找不到为 flase, 否则为 true
            return stristr($_SERVER["HTTP_VIA"], "wap") ? self::SOURCE_WAP : self::SOURCE_PC;
        }

        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        // 从 HTTP_USER_AGENT 中查找品牌信息
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $device_brands = array ("nokia", "sony", "ericsson", "mot", "samsung", "htc", "sgh", "netfront", "android", "mobile",
                "lg", "sharp", "sie-", "philips", "panasonic", "alcatel", "lenovo", "iphone", "ipod", "blackberry", "meizu",
                "symbian", "ucweb", "windowsce", "palm", "operamini", "operamobi", "openwave", "nexusone", "cldc", "midp", "wap");

            if (preg_match("/(" . implode("|", $device_brands) . ")/i", strtolower($_SERVER["HTTP_USER_AGENT"]))) {
                return self::SOURCE_WAP;
            }
        }

        // 协议法，因为有可能不准确，放到最后判断
        // 如果只支持 wml 并且不支持 html 那一定是移动设备
        // 如果支持 wml 和 html 但是 wml 在 html 之前则是移动设备
        if (isset($_SERVER["HTTP_ACCEPT"])) {
            if ((stripos($_SERVER["HTTP_ACCEPT"], "vnd.wap.wml") !== false) && (stripos($_SERVER["HTTP_ACCEPT"], "text/html") === false || (stripos($_SERVER["HTTP_ACCEPT"], "vnd.wap.wml") < stripos($_SERVER["HTTP_ACCEPT"], "text/html")))) {
                return self::SOURCE_WAP;
            }
        }

        return self::SOURCE_PC;
    }

    /**
     * 获取系统品牌
     *
     * @return string
     */
    static function getSystem() {
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $agent = $_SERVER["HTTP_USER_AGENT"];
        } else {
            return self::SYSTEM_OTHER;
        }

        if (stripos($agent, "window")) {
            return self::SYSTEM_WINDOWS;
        } else if (stripos($agent, "linux")) {
            if (stripos($agent, "android")) {
                return self::SYSTEM_ANDROID;
            }

            return self::SYSTEM_LINUX;
        } else if (stripos($agent, "unix")) {
            return self::SYSTEM_UNIX;
        } else if (preg_match("/(iPhone|iPad|iPod)/i", $agent)) {
            return self::SYSTEM_IOS;
        } else if (stripos($agent, "mac os")) {
            return self::SYSTEM_MAC;
        }

        return self::SYSTEM_OTHER;
    }

    /**
     * 获取设备类型
     *
     * @return string
     */
    static function getDevice() {
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $agent = $_SERVER["HTTP_USER_AGENT"];
        } else {
            return self::DEVICE_PC;
        }

        // 平板设备，发现更多的及时补充
        if (preg_match("/(iPad|Pad|Tablet|Xoom)/i", $agent)) {
            return self::DEVICE_PAD;
        }

        // 移动端设备，发现更多的及时补充
        if (preg_match("/(Android|iPhone|phone|BlackBerry|Symbian|iPod|globalApp|ecovacsApp|ecovacsGlobalApp)/i", $agent)) {
            return self::DEVICE_MOBILE;
        }

        // 我们的 app 设备
        if (isset($_SERVER["HTTP_APPCODE"]) && isset($_SERVER["HTTP_CHANNEL"]) && isset($_SERVER["HTTP_APPVERSION"]) && isset($_SERVER["HTTP_DEVICEID"]) && isset($_SERVER["HTTP_DEVICETYPE"])) {
            return self::DEVICE_MOBILE;
        }

        if (isset($_SERVER["HTTP_ACCEPT"])) {
            if ((stripos($_SERVER["HTTP_ACCEPT"], "vnd.wap.wml") !== false) && (stripos($_SERVER["HTTP_ACCEPT"], "text/html") === false || (stripos($_SERVER["HTTP_ACCEPT"], "vnd.wap.wml") < stripos($_SERVER["HTTP_ACCEPT"], "text/html")))) {
                return self::DEVICE_MOBILE;
            }
        }

        // 默认都是 PC
        return self::DEVICE_PC;
    }

    // e-link 设备判断
    // e-link 设备不作为来源判断，全部信息归官网 (pc/wap) 所有
    static function isELink() {
        // 本次设备上是 "Qing/" 大小写匹配查询
        // @TODO 等到 e-link 给我们增加绝对的 OEM 标示后，需要再次修改
        if (isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "Qing/") !== false) {
            return true;
        }

        return false;
    }

    /**
     * 获取客户端 IP，采用点分十进制
     *
     * @return string
     */
    static function getIp() {
        $ip_address = '0.0.0.0';

        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && !empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CDN_SRC_IP"])) {
            $ip_address = $_SERVER["HTTP_CDN_SRC_IP"];
        } else if (isset($_SERVER["REMOTE_ADDR"]) && isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_address = $_SERVER["HTTP_CLIENT_IP"];
        } else if (isset($_SERVER["REMOTE_ADDR"])) {
            $ip_address = $_SERVER["REMOTE_ADDR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip_address = $_SERVER["HTTP_CLIENT_IP"];
        }

        if ($ip_address === 'Unknown') {
            $ip_address = '0.0.0.0';
            return $ip_address;
        }

        if (stripos($ip_address, ",") !== false) {
            $x = explode(",", $ip_address);
            $ip_address = trim(end($x));
        }

        return $ip_address;
    }

    /**
     * 获取数值型 IP 地址
     *
     * @return int|string
     */
    static function getIpToLong() {
        if (($ip = self::getIp()) != "0.0.0.0") {
            return sprintf("%u", ip2long($ip));
        } else {
            return 0;
        }
    }

    /**
     * 判断是那种类型的爬虫
     *
     * @return string
     */
    static function getRobot() {
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $agent = $_SERVER["HTTP_USER_AGENT"];
        } else {
            return self::ROBOT_HUMAN;
        }

        // Google robot includes website, news, image, mobile, adsense and adwords
        $pattern_google = "/(googlebot|googlebot\-mobile|googlebot\-image|mediapartners\-google|adsbot\-google)/i";
        if (preg_match($pattern_google, $agent)) {
            return self::ROBOT_GOOGLE;
        }

        // Baidu robot includes news, image, video, mobile, website
        $pattern_baidu = "/(baiduspider|BaiduSpider|Baiduspider\-image|Baiduspider\-mobile|Baiduspider\-video|Baiduspider\-news)/i";
        if (preg_match($pattern_baidu, $agent)) {
            return self::ROBOT_BAIDU;
        }

        // 360 spider
        $pattern_360 = "/(360Spider|haosouspider)/i";
        if (preg_match($pattern_360, $agent)) {
            return self::ROBOT_360;
        }

        // soso spider
        $pattern_soso = "/(Sosospider)/i";
        if (preg_match($pattern_soso, $agent)) {
            return self::ROBOT_SOSO;
        }

        // sogou spider includes news, web, inst, blog, orion
        $pattern_sogou = "/(Sogou web spider|Sougou inst spider|Sogou spider2|Sogou blog|Sogou News Spider|Sogou Orion spider)/i";
        if (preg_match($pattern_sogou, $agent)) {
            return self::ROBOT_SOGOU;
        }

        // Yahoo!
        $pattern_yahoo = "/(Yahoo! Slurp China|Yahoo! Slurp)/i";
        if (preg_match($pattern_yahoo, $agent)) {
            return self::ROBOT_YAHOO;
        }

        // youdao
        $pattern_youdao = "/(YoudaoBot|YodaoBot)/i";
        if (preg_match($pattern_youdao, $agent)) {
            return self::ROBOT_YOUDAO;
        }

        // msn
        $pattern_msn = "/(msnbot|msnbot\-media|msnbot\-newsblogs|msnbot\-products|msnbot\-academic)/i";
        if (preg_match($pattern_msn, $agent)) {
            return self::ROBOT_MSN;
        }

        // bing
        $pattern_bing = "/(bingbot)/i";
        if (preg_match($pattern_bing, $agent)) {
            return self::ROBOT_BING;
        }

        // yisou, UC's product
        $pattern_yisou = "/(Yisouspider)/i";
        if (preg_match($pattern_yisou, $agent)) {
            return self::ROBOT_YISOU;
        }

        // alexa
        $pattern_alexa = "/(ia_archiver)/i";
        if (preg_match($pattern_alexa, $agent)) {
            return self::ROBOT_ALEXA;
        }

        // etao, Taobao's product
        $pattern_etao = "/(EtaoSpider)/i";
        if (preg_match($pattern_etao, $agent)) {
            return self::ROBOT_ETAO;
        }

        return self::ROBOT_HUMAN;
    }

    /**
     * 判断是否是网页蜘蛛
     *
     * @return bool
     */
    static function isRobot() {
        if (self::getRobot() == self::ROBOT_HUMAN) {
            return false;
        }

        return true;
    }
}