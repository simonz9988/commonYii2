<?php

/**
 * 注册资源文件
 *
 * @author Joker Huang <joker.huang@ecovacs.com>
 * @date 2017-04-10
 */

namespace vendor\goods\select;

use yii\web\AssetBundle;

class SelectGoodsAsset extends AssetBundle {
    public $js=[
        'select-goods.js'
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
