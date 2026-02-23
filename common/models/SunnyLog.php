<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_log".
 *
 * @ int $id
 * @property string $content 所属国家
 * @property string $create_time 创建时间
 */
class SunnyLog extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content' => 'Content',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 添加日志
     * @param $log
     * @return string
     */
    public function addLog($log){
        $add_data['content'] = $log ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        return $this->baseInsert(self::tableName(),$add_data);
    }
}
