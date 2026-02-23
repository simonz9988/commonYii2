<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_country".
 *
 * @property int $id
 * @property string $name 国家名称
 * @property string $iso_code_2 名称简写
 * @property string $iso_code_3 名称简写
 * @property int $create_time 时间戳
 * @property int $modify_time 时间戳
 */
class Country extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_country';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'create_time', 'modify_time'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['iso_code_2'], 'string', 'max' => 2],
            [['iso_code_3'], 'string', 'max' => 3],
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
            'iso_code_2' => 'Iso Code 2',
            'iso_code_3' => 'Iso Code 3',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 获取所有列表
     * @return mixed
     */
    public function getList(){
        $params['cond'] = 'id >:id' ;
        $params['args'] = [":id"=>0];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    public function getNameById($id){
        $params['cond'] = 'id =:id' ;
        $params['args'] = [":id"=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info? $info['name']:'' ;
    }

    public function getInfoById($id){

        $params['cond'] = 'id =:id' ;
        $params['args'] = [":id"=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取对应的语言包的信息
     * @param $info
     * @return array
     */
    public function getLanguageItemList($info){
        // 查询所有有效的语言列表
        $language_obj = new Language() ;
        $language_list = $language_obj->getAll();
        if(!$language_list){
            return  [];
        }

        $country_id = $info ? $info['id']:0;
        $desc_params['cond'] = 'country_id=:country_id  AND is_deleted=:is_deleted';
        $desc_params['args'] = [':country_id'=>$country_id,':is_deleted'=>'N'];
        $desc_obj = new CountryDetail();
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
                'country_name'=>$desc ? $desc['country_name']:'',
                'language_name'=>$v['name'],
            ];
        }

        return $item_list ;
    }

    public function savePostData($id,$post_data){
        $name = $post_data['name'];
        $iso_code_2 = $post_data['iso_code_2'];
        $iso_code_3 = $post_data['iso_code_3'];

        $add_data = compact('name','iso_code_2','iso_code_3');
        $add_data['modify_time'] = time();
        if($id){
            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = time();
            $id = $this->baseInsert(self::tableName(),$add_data);
        }


        $detail_obj = new CountryDetail();
        $country_name_list = $post_data['country_name_list'];

        foreach($country_name_list as $language_id=>$v){
            $params['cond'] = ' country_id =:country_id AND language_id=:language_id AND is_deleted=:is_deleted ';
            $params['args'] = [':country_id'=>$id,':language_id'=>$language_id,':is_deleted'=>'N'];
            $info = $this->findOneByWhere($detail_obj::tableName(),$params,self::getDb());
            if($info){
                // 更新
                $detail_update_data['country_name'] = $v ;
                $detail_update_data['modify_time'] = date('Y-m-d H:i:s') ;
                $this->baseUpdate($detail_obj::tableName(),$detail_update_data,'id=:id',[':id'=>$info['id']]);

            }else{
                // 新增
                $detail_add_data['country_name'] = $v ;
                $detail_add_data['country_id'] = $id ;
                $detail_add_data['language_id'] = $language_id;
                $detail_add_data['is_deleted'] = "N";
                $detail_add_data['create_time'] = date('Y-m-d H:i:s');
                $detail_add_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseInsert($detail_obj::tableName(),$detail_add_data);
            }
        }
    }
}
