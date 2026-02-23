<?php
namespace backend\components;

use yii\base\Component;
use Yii;

class SnSyn extends Component{
    // 请求域名，根据环境的不同会在测试环境及正式环境下切换
    private $request_domain = "";

    public function __construct() {
        $this->request_domain = trim(MDB_URL, "/") . "/";
    }

    /**
     * 获取全部SN码
     * @return mixed
     */
    public function getAll($num) {
        return $this->postData("/mdb/sn/master/query", ["projectNo" => 'EOW',"num" => $num]);
    }
    
    /**
     * 发送通知执行结果
     * @return mixed
     */
    public function SendNotification($sync_code) {
        return $this->postData("/mdb/sn/master/update", ["projectNo" => 'EOW', "syncCode" => $sync_code]);
    }
    
    
    /**
     * parseURL($api)
     * 解析一个接口的地址
     * @param  string $api 请求的接口
     * @return string
     */
    private function parseURL($api) {
        return $this->request_domain . trim($api, '/');
    }

    /**
     * postData($api, $data, $ignore_error)
     * post 一个接口请求数据
     *
     * @param $api 接口
     * @param $post 数据
     * @return array
     */
    private function postData($api, $post = null) {
        $url = $this->parseURL($api);
    
        $rst = curlGo($url, $post, false, null);

        return json_decode($rst, true);
    }
}