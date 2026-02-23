<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_ad_position".
 *
 * @property int $id
 * @property string $name 广告位名称
 * @property string $unique_key 广告位关键字
 * @property int $sort 排序
 * @property string $status 状态是否有效
 * @property string $is_del 是否已经删除
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class AdPosition extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_ad_position';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['unique_key', 'status'], 'string', 'max' => 50],
            [['is_del'], 'string', 'max' => 1],
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
            'unique_key' => 'Unique Key',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_del' => 'Is Del',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回广告位的信息
     * @param $id
     * @param string $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){

        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 判断唯一key值是否重复
     * @param $unique_key
     * @param $id
     * @return mixed
     */
    public function checkRepeatKey($unique_key,$id){

        $params['cond'] = 'unique_key=:unique_key AND id !=:id';
        $params['args'] = [':unique_key'=>$unique_key,':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据key值获取名称
     * @param $uniqu_key
     * @return mixed
     */
    public function getNameByKey($unique_key){
        $params['cond'] = 'unique_key=:unique_key';
        $params['args'] = [':unique_key'=>$unique_key];
        $params['fields'] = 'name' ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['name']:'';
    }

    /**
     * 获取所有有效的广告位
     * @return mixed
     */
    public function getAll(){

        $params['cond'] = 'status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':status'=>'ENABLED',':is_deleted'=>'N'];
        $params['fields'] = '*' ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list;
    }
}
