<?php
/*
 * 同步常见问题
 * date 2018.07.31
 */
namespace backend\components;

use Yii;
use yii\base\Component;

class SyncInstructions extends Component{

    public function __construct() {
    }

    /**
     * 获取全部说明书信息
     * @return mixed
     */
    public function getAllSyncInstructions() {

        return $this->postData(OMS_API_URL."/api/product/getSpecificationList");
    }

    /**
     * postData($api, $data, $ignore_error)
     * post 一个接口请求数据
     *
     * @param  $url 接口
     * @param  $post 数据
     * @return array
     */
    private function postData($url, $post = null) {
        
        $rst = curlGo($url, $post);

        return json_decode($rst, true);
    }
}