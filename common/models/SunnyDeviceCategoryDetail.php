<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_category_detail".
 *
 * @property int $id
 * @property int $category_id 分类ID
 * @property int $language_id 语言
 * @property string $sop_url 手册地址
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceCategoryDetail extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_category_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'language_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['sop_url'], 'string', 'max' => 255],
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
            'category_id' => 'Category ID',
            'language_id' => 'Language ID',
            'sop_url' => 'Sop Url',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * @param $category_id
     * @return mixed
     */
    public function delDataByCategoryId($category_id){
        $update_data['is_deleted'] ='Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        return $this->baseUpdate(self::tableName(),$update_data,'category_id=:category_id',[':category_id'=>$category_id]);
    }

    /**
     * @param $category_id
     * @param $language_id
     * @return string
     */
    public function getSopUrlByIdAndLangId($category_id,$language_id){
        $params['cond'] = ' category_id =:category_id AND language_id=:language_id AND is_deleted=:is_deleted ';
        $params['args'] = [':category_id'=>$category_id,':language_id'=>$language_id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['sop_url'] :'';
    }

    /**
     * 保存sop信息
     * @param $category_id
     * @param $language_id
     * @param $sop_url
     * @param $file_name
     * @return bool
     */
    public function saveSop($category_id,$language_id,$sop_url,$file_name){
        if(!$category_id || !$language_id || !$sop_url){
            return false ;
        }
        $category_obj = new SunnyDeviceCategory();
        $category_info = $category_obj->getInfoById($category_id);
        $add_data['parent_category_id'] = $category_info?$category_info['parent_id']:0 ;
        $params['cond'] = ' category_id =:category_id AND language_id=:language_id AND is_deleted=:is_deleted ';
        $params['args'] = [':category_id'=>$category_id,':language_id'=>$language_id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            // 更新
            $update_data['sop_url'] = $sop_url ;
            $update_data['parent_category_id'] = $category_info?$category_info['parent_id']:0  ;
            $update_data['file_name'] = $file_name ;
            $update_data['modify_time'] = date('Y-m-d H:i:s') ;
            return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);

        }else{
            // 新增


            $add_data['category_id'] = $category_id ;
            $add_data['parent_category_id'] = $category_info?$category_info['parent_id']:0 ;
            $add_data['language_id'] = $language_id;
            $add_data['sop_url'] = $sop_url;
            $add_data['file_name'] = $file_name;
            $add_data['is_deleted'] = "N";
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseInsert(self::tableName(),$add_data);
        }
    }

    /**
     * 批量保存分类的展示名称
     * @param $post_data
     * @param $category_id
     * @return mixed
     */
    public function saveShowNameList($post_data,$category_id){

        if(!$post_data){
            return [];
        }

        $category_obj = new SunnyDeviceCategory();
        $category_info = $category_obj->getInfoById($category_id);

        foreach($post_data as $language_id=>$v){
            $params['cond'] = ' category_id =:category_id AND language_id=:language_id AND is_deleted=:is_deleted ';
            $params['args'] = [':category_id'=>$category_id,':language_id'=>$language_id,':is_deleted'=>'N'];
            $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
            if($info){
                // 更新
                $update_data['show_name'] = $v ;
                $update_data['modify_time'] = date('Y-m-d H:i:s') ;
                $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);

            }else{
                // 新增
                $add_data['category_id'] = $category_id ;
                $add_data['parent_category_id'] = $category_info?$category_info['parent_id']:0 ;
                $add_data['language_id'] = $language_id;
                $add_data['sop_url'] = '';
                $add_data['file_name'] = '';
                $add_data['show_name'] = $v;
                $add_data['is_deleted'] = "N";
                $add_data['create_time'] = date('Y-m-d H:i:s');
                $add_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseInsert(self::tableName(),$add_data);
            }
        }

        return true ;
    }
}
