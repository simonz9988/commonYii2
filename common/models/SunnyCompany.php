<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_company".
 *
 * @property int $id
 * @property int $country_id 所属国家
 * @property int $province 省
 * @property int $city 市
 * @property int $area 区
 * @property string $status 状态(ENABLED-启用 DISABLED-禁用)
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyCompany extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'province', 'city', 'area'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['status'], 'string', 'max' => 50],
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
            'country_id' => 'Country ID',
            'province' => 'Province',
            'city' => 'City',
            'area' => 'Area',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @param string $fields
     * @return mixed
     */
    public function getInfoById($id,$fields="*"){
        $params['cond']  = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @param string $fields
     * @return mixed
     */
    public function getNameById($id,$fields="*"){
        $params['cond']  = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = 'company_name';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['company_name'] : '' ;
    }

    /**
     * 根据ID返回指定信息
     * @param $unique_key
     * @param string $fields
     * @return mixed
     */
    public function getInfoByUniqueKey($unique_key,$fields="*"){
        $params['cond']  = 'unique_key=:unique_key AND is_deleted=:is_deleted';
        $params['args'] = [':unique_key'=>$unique_key,':is_deleted'=>'N'];
        $params['fields'] = $fields;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
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
        $desc_params['cond'] = 'company_id=:company_id  AND is_deleted=:is_deleted';
        $desc_params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N'];
        $desc_obj = new SunnyCompanyDetail();
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
                'name'=>$desc ? $desc['name']:'',
                'address'=>$desc ? $desc['address']:'',
            ];
        }

        return $item_list ;

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
     * 保存公司信息
     * @param $id
     * @param $post_data
     * @return mixed
     */
    public function addData($id,$post_data){

        // 当前时间
        $now =date('Y-m-d H:i:s');
        $add_data['country_id'] = $post_data['country_id'];
        $add_data['company_name'] = $post_data['company_name'];
        $add_data['unique_key'] = $post_data['unique_key'];
        $add_data['img_url'] = $post_data['img_url'];
        $add_data['province'] = $post_data['province'];
        $add_data['city'] = $post_data['city'];
        $add_data['area'] = $post_data['area'];
        $add_data['status'] = $post_data['status'];
        $add_data['modify_time'] = $now;

        if($id){

            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$id]);

        }else{
            $add_data['create_time'] = $now ;
            $id = $this->baseInsert(self::tableName(),$add_data);
        }

        // 删除语言包相关的信息
        $detail_obj = new SunnyCompanyDetail();
        $detail_obj->deleteByCompanyId($id) ;

        $language_item_list = isset($post_data['language_item_list']) ?$post_data['language_item_list']:[];
        if($language_item_list){
            foreach($language_item_list as $language_id=>$v){
                $lang_add_data['company_id'] = $id ;
                $lang_add_data['language_id'] = $language_id ;
                $lang_add_data['name'] = $v['name'] ;
                $lang_add_data['address'] = $v['address'] ;
                $lang_add_data['is_deleted'] = 'N' ;
                $lang_add_data['create_time'] = $now;
                $lang_add_data['modify_time'] = $now;

                $this->baseInsert($detail_obj::tableName(),$lang_add_data);
            }
        }

        return true ;

    }

    /**
     * 获取登陆信息
     * @param $username
     * @param $password
     * @return mixed
     */
    public function getUserInfo($username,$password){

        $params['cond'] = 'email=:email AND password=:password';
        $params['args'] = [':email'=>$username,':password'=>md5($password)];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取所有允许的公司列表
     * @return mixed
     */
    public function getAllAllowed(){

        $params['cond'] = 'status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':status'=>'ENABLED',':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
}
