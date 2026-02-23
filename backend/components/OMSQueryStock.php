<?php
namespace backend\components;

use yii\base\Component;
use Yii;

class OMSQueryStock extends Component{
    // 请求域名，根据环境的不同会在测试环境及正式环境下切换
    private $request_domain = "";

    public function __construct() {
        $this->request_domain = trim(EWM_URL, "/") . "/";
    }

    /**
     * 根据条件获取
     * @param  array $sku_items SKU List
     * @return array
     */
    public function getOMSQueryStockAll($sku_items) {
    
        $data = $this->postData("/api/WMSFOROMS/OMSQueryStockListWMS", ["LOC_ID" => '8500-1100',"SKUItems" => $sku_items]);
        if(!$data || $data['FLAG'] == 'FAIL'){
            Yii::$app->CommonLogger->logError("仓储库存调用失败：".json_encode($data, JSON_UNESCAPED_UNICODE));
            return array();
        }
        
        return $data;
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
    
        $rst = curlGo($url, $post, false, null, 'json');

        return json_decode($rst, true);
    }
}