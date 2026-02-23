<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_cdn_deal_contents".
 *
 * @property string $id
 * @property string $url 软件分类名称
 * @property string $from_domain ios下载地址
 * @property string $new_static_domain 安卓下载地址
 * @property string $new_url 屏蔽地区
 * @property string $create_time 更新时间
 */
class CdnDealContents extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_cdn_deal_contents';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_newyxcms');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['new_url'], 'required'],
            [['create_time'], 'safe'],
            [['url'], 'string', 'max' => 50],
            [['from_domain', 'new_static_domain', 'new_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'url' => 'Url',
            'from_domain' => 'From Domain',
            'new_static_domain' => 'New Static Domain',
            'new_url' => 'New Url',
            'create_time' => 'Create Time',
        ];
    }

    public function addRow($addData){
        $this->baseInsert('sea_cdn_deal_contents',$addData,'db_newyxcms');
    }
}
