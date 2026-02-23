<?php
/**
 * Sync 数据同步功能
 * 数据同步需要检测语言交集、国家顺序、组织数据、同步功能几个步骤
 * 其中 语言交集和国家顺序放在一个接口中，组织数据和同步功能放在一个接口中
 * 不过多条数据，都是逐条处理
 *
 * @author joker.huang@ecovacs.com
 */

namespace backend_hq\widgets\sync;

use Yii;

class Sync extends \yii\base\Widget
{
    private $bundle = null;

    public $model;
    public $table;
    public $key;
    public $field;

    public function init() {
        Yii::$app->request->enableCsrfValidation = false;

        // 用户可以指定需要的 ID 字段，方便用户的自定义操作
        if (empty($this->model)) {
            throw new \yii\web\HttpException(404, 'Field `model` is essential to Sync Widget!');
        }

        if (empty($this->table)) {
            throw new \yii\web\HttpException(404, 'Field `table` is essential to Sync Widget!');
        }

        if (empty($this->key)) {
            throw new \yii\web\HttpException(404, 'Field `key` is essential to Sync Widget!');
        }

        if (empty($this->field)) {
            throw new \yii\web\HttpException(404, 'Field `field` is essential to Sync Widget!');
        }

        parent::init();
    }

    public function run() {
        $data   = [
            "model" => $this->model,
            "table" => $this->table,
            "key"   => $this->key,
            "field" => $this->field,
        ];

        return $this->render("sync", $data);
    }
}