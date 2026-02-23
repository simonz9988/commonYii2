<?php

/**
 * 商品选择的插件
 * 商品查询的功能根据传入的参数区分有：多选与单选、主商品与赠品与全部商品、回调函数等
 *
 * @author Joker Huang <joker.huang@ecovacs.com>
 * @date 2017-04-10
 */

namespace vendor\goods\select;

use common\models\Category;
use Yii;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\InputWidget;

class SelectGoods extends InputWidget
{
    /**
     * @var array
     */
    public $callbackFunction;

    public $_id;

    // 商品的类型
    public $goods_type;
    public $goods_type_id;

    // 是否多选
    public $multi;

    // 回调函数
    public $callback;

    // 商品状态
    public $is_del;

    // button
    public $buttonID;

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init() {
        $this->_id = $this->id;
        $this->_id = str_replace(['[', ']'], '_', $this->_id);

        $this->buttonID = "modal-select-goods-button-" . $this->_id;

        if (stripos($this->goods_type, ',') !== false) {
            // 多个商品类型
            $_goods_types = explode(',', $this->goods_type);
        } else if ($this->goods_type) {
            // 单个商品类型
            $_goods_types = [$this->goods_type];
        }

        $this->goods_type_id = [];
        if (isset($_goods_types) && $_goods_types) {
            foreach ($_goods_types as $v) {
                if ($v == '常规商品') {
                    $this->goods_type_id[] = 0;
                } else if ($v == '赠品') {
                    $this->goods_type_id[] = 2;
                } else if ($v == 'DIY商品') {
                    $this->goods_type_id[] = 3;
                } else if ($v == '配件') {
                    $this->goods_type_id[] = 4;
                } else if ($v == '虚拟商品') {
                    $this->goods_type_id[] = 5;
                }
            }

            if ($this->goods_type_id) {
                $this->goods_type_id = json_encode($this->goods_type_id);
            }
        }

        if (isset($this->is_del) && $this->is_del) {
            if ($this->is_del == '上架') {
                $this->is_del = 0;
            } else if ($this->is_del == '下架') {
                $this->is_del = 2;
            } else {
                $this->is_del = 'null';
            }
        } else {
            $this->is_del = 'null';
        }

        if (!$this->goods_type_id) {
            $this->goods_type_id = 'null';
        }

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
                        <i class="ace-icon fa fa-plus"></i>
                        选择商品
                    </button>
EOT;

        return $html;
    }

    /**
     * 注册客户端脚本
     */
    protected function registerClientScript() {
        // 注册静态资源
        SelectGoodsAsset::register($this->view);

        $queryPath = Url::to("/goods/select-goods-plugin");

        // 分类
        $category_model = new Category();
        $_category_list = $category_model->getGoodsCategoryList();

        $category_list = [];
        $category_json = "{}";
        if ($_category_list) {
            foreach ($_category_list as $v) {
                $category_list[] = ['id' => $v['id'], 'name' => $v['name']];
            }
            $category_json = json_encode($category_list);
        }

        // 写动态 JS
        $registerScript = <<<EOT
            var SelectGoodsCategoryList = {$category_json};
            var selectGoodsInstance_{$this->_id} = new SelectGoods('{$this->_id}', '{$this->id}', '{$this->buttonID}', '{$queryPath}', {$this->goods_type_id}, {$this->multi}, {$this->is_del}, '{$this->callback}', SelectGoodsCategoryList);
EOT;

        $this->view->registerJs($registerScript, View::POS_READY);
    }
}

?>
