<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\validators;

use Yii;
use yii\base\InvalidConfigException;

/**
 * 检查多个字段，判断必须有一个以上的字段被赋值，且值不能为空
 *
 * @since 2.0
 */
class EitherValidator extends Validator
{
    public $skipOnEmpty = false;

    /**
     * @var 主字段与 with 中的各个字段进行比较，必须有一个值必须存在且不为空
     */
    public $with;

    /**
     * @var string the user-defined error message. It may contain the following placeholders which
     * will be replaced accordingly by the validator:
     *
     * - `{attribute}`: the label of the attribute being validated
     * - `{withAttribute}`: the label of the attribute to be checked with
     */
    public $message;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', 'Requires at least one {attribute} or {withAttribute}.');
        }
    }

    /**
     * 检验属性的功能
     * @param \yii\base\Model $model
     * @param string $attribute
     * @throws InvalidConfigException
     */
    public function validateAttribute($model, $attribute)
    {
        $values = array($model->$attribute);
        $attributeLabel = $model->getAttributeLabel($attribute);
        $withLabel = array();

        if ($this->with) {
            foreach ($this->with as $v) {
                $values[] = $model->$v;
                $withLabel[] = $model->getAttributeLabel($v);
            }

            // 将空字符过滤，检查最后剩余的字段信息
            $values = array_filter($values, array("self", "filter"));

            if (empty($values)) {
                $this->addError($model, $attribute, $this->message, [
                    'attribute' => $attributeLabel,
                    'withAttribute' => implode(',', $withLabel)
                ]);
            }
        } else {
            throw new InvalidConfigException('EitherValidator::"with" must be set.');
        }
    }

    static public function filter($var) {
        if (is_string($var)) {
            return trim($var) ? true : false;
        } else {
            return $var ? true : false;
        }
    }
}
