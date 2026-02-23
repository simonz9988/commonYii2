<?php

/**
 * 注册资源文件
 *
 * @author Joker Huang <joker.huang@ecovacs.com>
 * @date 2017-04-01
 */

namespace vendor\excel\member;
use yii\web\AssetBundle;

class ImportMemberAsset extends AssetBundle {
    public $js=[
        'plupload.full.min.js',
        'import-user.js'
    ];

    public $jsOptions = [
        'charset' => 'utf8',
    ];

    public function init() {
        //资源所在目录
        $this->sourcePath = dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }
}
?>
