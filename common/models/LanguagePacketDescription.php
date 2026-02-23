<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_language_packet_description".
 *
 * @property int $id
 * @property int $remote_id 远程id
 * @property int $language_id
 * @property int $language_packet_id
 * @property string $content 内容
 * @property string $create_time 时间戳
 * @property string $modify_time 时间戳
 */
class LanguagePacketDescription extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_language_packet_description';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remote_id', 'language_id', 'content'], 'required'],
            [['remote_id', 'language_id', 'language_packet_id'], 'integer'],
            [['content'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'remote_id' => 'Remote ID',
            'language_id' => 'Language ID',
            'language_packet_id' => 'Language Packet ID',
            'content' => 'Content',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
}
