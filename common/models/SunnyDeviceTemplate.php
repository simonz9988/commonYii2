<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_template".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $type BATTERY-电池 LOAD-负载参数
 * @property string $content 内容json_encode存入的内容
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceTemplate extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['type'], 'string', 'max' => 255],
            [['is_deleted'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'content' => 'Content',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据类型返回对应的列表信息
     * @param $type
     * @param string $fields
     * @return mixed
     */
    public function getListByType($type,$fields='*'){
        $params['cond'] = 'type=:type AND is_deleted=:is_deleted';
        $params['args'] = [':type'=>$type,':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 获取根据 ID获取具体信息
     * @param $id
     * @return mixed
     */
    public function getDetailInfoById($id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        $content = $info ? json_decode($info['content'],true):[];
        return $content ;
    }
}
