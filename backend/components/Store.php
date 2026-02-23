<?php
namespace backend\components;

use yii\base\Component;
use Yii;

class Store extends Component{
    // 请求域名，根据环境的不同会在测试环境及正式环境下切换
    private $request_domain = "";

    public function __construct() {
        $this->request_domain = trim(STORE_API_URL, "/") . "/";
    }

    /**
     * 获取门店信息
     * @param int $store_id
     * @return mixed
     */
    public function getStoreInfo($store_id) {
        return $this->postData("api/appointmentStore/getDetailByStoreId", ["storeId" => $store_id]);
    }

    /**
     * 获取全部的门店信息
     * @return mixed
     */
    public function getAllStores() {
        return $this->postData("api/store/search");
    }

    /**
     * parseURL($api)
     * 解析一个接口的地址
     * @param string $api 请求的接口
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
        
        $rst = curlGo($url, $post);

        return json_decode($rst, true);
    }
}