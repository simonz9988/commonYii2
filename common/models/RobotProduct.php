<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_robot_product".
 *
 * @property int $productid
 * @property string $productname 商品名称
 * @property string $thumb 图片
 * @property string $meno 商品介绍
 * @property string $umoney U币
 * @property string $coin 代币
 * @property int $nodeqty 节点数量
 * @property string $shareaward 分享奖励
 * @property int $is_deleted 是否已经删除
 */
class RobotProduct extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['meno'], 'string'],
            [['umoney', 'coin'], 'number'],
            [['nodeqty', 'is_deleted'], 'integer'],
            [['productname'], 'string', 'max' => 60],
            [['thumb', 'shareaward'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'productid' => 'Productid',
            'productname' => 'Productname',
            'thumb' => 'Thumb',
            'meno' => 'Meno',
            'umoney' => 'Umoney',
            'coin' => 'Coin',
            'nodeqty' => 'Nodeqty',
            'shareaward' => 'Shareaward',
            'is_deleted' => 'Is Deleted',
        ];
    }
}
