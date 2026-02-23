<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_category".
 *
 * @property int $id
 * @property string $name 分类名称
 * @property string $unique_key 唯一识别符
 * @property string $is_setting_light_level 是否需要设置亮度等级
 * @property string $is_having_usb_port 是否有USB端口
 * @property string $note 备注说明
 * @property string $status 状态(ENABLED-启用 DISABLED-禁用)
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceCategory extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['name', 'unique_key', 'is_setting_light_level', 'is_having_usb_port', 'status'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 255],
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
            'unique_key' => 'Unique Key',
            'is_setting_light_level' => 'Is Setting Light Level',
            'is_having_usb_port' => 'Is Having Usb Port',
            'note' => 'Note',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
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
     * 根据ID返回指定的信息
     * @param $id
     * @param string $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    public function getNameById($id){
        $info = $this->getInfoById($id,'name');
        return $info ? $info['name'] :'';
    }

    /**
     * 根据父类ID返回所有列表信息
     * @param int $parent_id
     * @param string $fields
     * @param int $id
     * @return mixed
     */
    public function getListByParentId($parent_id=0,$fields='*',$id=0){
        $params['cond'] = 'parent_id=:parent_id AND is_deleted=:is_deleted AND id !=:id';
        $params['args'] = [':parent_id'=>$parent_id,':is_deleted'=>'N',':id'=>$id];
        $params['fields'] = $fields;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 获取所有语言返回的信息列表
     * @param $info
     * @return mixed
     */
    public function getLanguageItemList($info){

        // 查询所有有效的语言列表
        $language_obj = new Language() ;
        $language_list = $language_obj->getAll();
        if(!$language_list){
            return  [];
        }

        $company_id = $info ? $info['id']:0;
        $desc_params['cond'] = 'category_id=:category_id  AND is_deleted=:is_deleted';
        $desc_params['args'] = [':category_id'=>$company_id,':is_deleted'=>'N'];
        $desc_obj = new SunnyDeviceCategoryDetail();
        $temp_desc_list = $this->findAllByWhere($desc_obj::tableName(),$desc_params,self::getDb());
        $desc_list = [];
        if($temp_desc_list){
            foreach($temp_desc_list as $v){
                $desc_list[$v['language_id']] = $v;
            }
        }
        $item_list = [] ;
        foreach($language_list as $v){

            $desc = isset($desc_list[$v['id']])? $desc_list[$v['id']]:[];
            $item_list[] = [
                'language_id'=>$v['id'],
                'language_name'=>$v['name'],
                'sop_url'=>$desc ? $desc['sop_url']:'',
                'file_name'=>$desc ? $desc['file_name']:'',
                'show_name'=>$desc ? $desc['show_name']:'',
            ];
        }

        return $item_list ;

    }

    /**
     * 获取所有分类列表信息
     * @return mixed
     */
    public function getFilterList(){
        $list = $this->getListByParentId(0);
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['son_list'] = $this->getListByParentId($v['id']);
            }
        }
        return $list ;
    }

    /**
     * 获取指定语言的分类名称
     * @param $category_id
     * @return mixed
     */
    public function getCategoryName($category_id){
        $lang_obj = new Language();
        $lang_id = $lang_obj->getUserDefaultLangId();

        $detail_obj = new SunnyDeviceCategoryDetail();
        $params['cond'] = 'category_id=:category_id AND language_id=:language_id AND is_deleted=:is_deleted';
        $params['args'] = [':category_id'=>$category_id,':language_id'=>$lang_id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere($detail_obj::tableName(),$params,self::getDb());
        return $info ? $info['show_name'] :'';

    }
}
