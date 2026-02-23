<?php

/**
 * 通过 excel 导入用户的功能
 * 只做导入功能，导入成功后将用户 id 写入对应的 input 中
 *
 * @author Joker Huang <joker.huang@ecovacs.com>
 * @date 2017-04-01
 */

namespace vendor\excel\member;

use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class ImportMember extends InputWidget
{
    /**
     * @var array
     */
    public $callbackFunction;

    // button
    public $buttonID;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init() {
        $this->buttonID = "modal-import-user-button-" . $this->id;

        parent::init();
    }

    /**
     * 用作生成HTML标签
     * @return string
     */
    public function run() {
        // 注册 JS
        $this->registerClientScript();

        $html = <<<EOT
                    <button class="btn btn-primary" type="button" id="{$this->buttonID}">
                        <i class="ace-icon fa fa-plus"></i>
                        导入用户
                    </button>
EOT;

        return $html;
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript() {
        // 注册静态资源
        ImportMemberAsset::register($this->view);

        // 写动态 JS
        $registerScript = <<<EOT
            ImportMember.create('{$this->id}', '{$this->buttonID}');
EOT;

        $this->view->registerJs($registerScript, View::POS_READY);
    }
}

?>
