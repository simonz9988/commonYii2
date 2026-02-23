<?php
/**
 * s3media 的展示面板
 * 展示面板包含了上传按钮/选择图片按钮的展示
 * 并通过初始化时传入的 target 字段来标明目标字段
 * 全部的配置信息存在 config.php 文件中, 尽可能只操作 config.php 而不修改实际执行类
 *
 * @author joker.huang@ecovacs.com
 */

namespace backend_hq\widgets\s3media;

use Yii;
use yii\helpers\ArrayHelper;

class Panel extends \yii\base\Widget
{
    private $config = [];
    private $bundle = null;

    public $id;
    public $path;
    public $callback;
    public $display;

    public function init() {
        Yii::$app->request->enableCsrfValidation = false;

        $_config = require_once __DIR__ . "/config.php";

        $this->config = ArrayHelper::merge($_config, $this->config);

        $this->bundle = Assets::register($this->view);

        // 用户可以指定需要的 ID 字段，方便用户的自定义操作
        if (empty($this->id)) {
            $this->id = time() . rand(1000, 9999);
        }

        if (empty($this->path)) {
            throw new \yii\web\HttpException(404, 'Field `path` is essential to S3Media Widget!');
        }

        if (empty($this->callback)) {
            throw new \yii\web\HttpException(404, 'Field `callback` is essential to S3Media Widget!');
        }

        if (empty($this->display)) {
            $this->display = "show";
        }

        parent::init();
    }

    public function run() {
        $data   = [
            "swf"       => $this->bundle->baseUrl . "/Uploader.swf",
            "id"        => $this->id,
            "path"      => $this->path,
            "callback"  => $this->callback,
            "display"   => $this->display == "hide" ? "hide" : "show"
        ];

        return $this->render("panel", $data);
    }
}