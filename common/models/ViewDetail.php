<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_view_detail".
 *
 * @property string $id
 * @property int $app_id
 * @property string $app_name
 * @property string $create_time 创建时间
 */
class ViewDetail extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_view_detail';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_ms115');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id'], 'integer'],
            [['create_time'], 'safe'],
            [['app_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'app_name' => 'App Name',
            'create_time' => 'Create Time',
        ];
    }

    public function addByIdAndName($app_id,$app_name){

        $add_data['app_id'] = $app_id ;
        $add_data['app_name'] = $app_name ;
        $add_data['create_time'] = date('Y-m-d H:i:s');
        return $this->baseInsert($this->tableName(),$add_data,'db_ms115');
    }
}
