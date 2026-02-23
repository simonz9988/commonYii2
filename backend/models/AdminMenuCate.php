<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "sea_admin_menu_cate".
 *
 * @property string $id
 * @property string $name 菜单分类名称
 * @property string $unique_key 菜单分类唯一关键字
 * @property int $sort 排序
 * @property int $is_open 是否删除
 * @property string $status 是否有效 enabled-有效 disabled-无效
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class AdminMenuCate extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_menu_cate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort', 'is_open'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['name', 'unique_key'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
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
            'is_open' => 'Is Open',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    //获取所有有用的
    public function getAll(){
        $params['where_arr']['is_open'] = 1 ;
        $params['where_arr']['status'] = 'enabled' ;
        $list = $this->findByWhere(self::tableName(),$params);
        return $list;

    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @return 返回值依据
     */
    public function getRowInfoById($id){
        $params['where_arr']['id'] = $id ;
        $params['return_type'] = 'row' ;
        $info = $this->findByWhere('sea_admin_menu_cate',$params);
        return $info;
    }

    public function getUniqueKeyById($id=''){
        $rst = '';
        if($id){
            $info = $this->getRowInfoById($id);
            $rst = $info?$info['unique_key']:'';
        }

        return $rst ;
    }

    public function getNameById($id=''){
        $rst = '';
        if($id){
            $info = $this->getRowInfoById($id);
            $rst = $info?$info['name']:'';
        }

        return $rst ;
    }
}
