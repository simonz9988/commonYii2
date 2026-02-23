<?php

/**
 * 会员选择的插件
 *
 * @author Joker Huang <joker.huang@ecovacs.com>
 * @date 2017-04-10
 */

namespace vendor\member\select;

use common\models\UserGroup;
use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class SelectMember extends InputWidget
{
    /**
     * @var array
     */
    public $callbackFunction;

    public $_id;

    // 是否多选
    public $multi;

    // 回调函数
    public $callback;

    // button
    public $buttonID;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init() {
        $this->_id = $this->id;
        $this->_id = str_replace(['[', ']'], '_', $this->_id);

        $this->buttonID = "modal-select-member-button-" . $this->_id;

        $this->multi = $this->multi ? 1 : 0;

        if (empty($this->callback)) {
            throw new \yii\web\HttpException(404, '请填写JS回调函数!');
        }

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
                        <i class="ace-icon glyphicon glyphicon-search"></i>
                        选择用户
                    </button>
EOT;

        return $html;
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript() {
        // 注册静态资源
        SelectMemberAsset::register($this->view);

        $group_model = new UserGroup();
        $list = $group_model->getNameRelationship();

        array_unshift($list, [0 => '请选择']);

        $list_json = json_encode($list);

        $queryPath = Url::to("/member/ajax-member-plugin");

        // 写动态 JS
        $registerScript = <<<EOT
            var selectMemberInstance_{$this->_id} = new SelectMember('{$this->_id}', '{$this->id}', '{$this->buttonID}', {$this->multi}, '{$queryPath}', '{$this->callback}', {$list_json});
EOT;

        $this->view->registerJs($registerScript, View::POS_READY);
    }
}

?>
