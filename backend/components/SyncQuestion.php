<?php
/*
 * 同步常见问题
 * date 2018.07.31
 */
namespace backend\components;

use Yii;
use yii\base\Component;

class SyncQuestion extends Component{

    public function __construct() {
    }

    /**
     * 获取全部的常见问题信息
     * @return mixed
     */
    public function getAllQuestion() {
        return $this->postData(CRM_API_URL."/api/productKnowledge/getAllQuestion");
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