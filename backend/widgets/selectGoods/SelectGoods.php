<?php
namespace backend_hq\widgets\selectGoods;

use Yii;

class SelectGoods extends \yii\base\Widget
{
    private $bundle = null;

    public $is_multiple;
    public function init() {
        Yii::$app->request->enableCsrfValidation = false;

        parent::init();
    }

    public function run() {
        $data = [] ;

        if (empty($this->is_multiple)) {
            throw new \yii\web\HttpException(404, 'Field `is_multiple` is essential to Select Goods Widget!');
        }

        $data['is_multiple'] = $this->is_multiple ;
        return $this->render("selectGoods", $data);
    }
}