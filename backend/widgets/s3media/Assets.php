<?php
namespace backend_hq\widgets\s3media;

class Assets extends \yii\web\AssetBundle {
    public $css = [
        "webuploader.css",
    ];

    public $js = [
        "webuploader.js"
    ];

    public function init() {
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR . "webuploader" . DIRECTORY_SEPARATOR;
    }
}