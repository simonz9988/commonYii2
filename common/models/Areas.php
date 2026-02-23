<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_areas".
 *
 * @property int $area_id
 * @property int $parent_id 上一级的id值
 * @property string $area_name 地区名称
 * @property int $sort 排序
 * @property int $is_show 是否显示
 */
class Areas extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_areas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'area_name', 'is_show'], 'required'],
            [['parent_id', 'sort', 'is_show'], 'integer'],
            [['area_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'area_id' => 'Area ID',
            'parent_id' => 'Parent ID',
            'area_name' => 'Area Name',
            'sort' => 'Sort',
            'is_show' => 'Is Show',
        ];
    }

    /**
     * 根据父类ID返回列表信息
     * @param $parent_id
     * @return mixed
     */
    public function getListByParentId($parent_id){
        $params['cond'] = 'parent_id=:parent_id';
        $params['args'] = [':parent_id'=>$parent_id];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据名称获取地址信息
     * @param $name
     * @return array|bool
     */
    public function getInfoByName($name){
        $params['cond'] = 'area_name=:area_name';
        $params['args'] = [':area_name'=>$name];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }
}
