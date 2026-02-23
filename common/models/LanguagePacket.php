<?php

namespace common\models;

use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sea_language_packet".
 *
 * @property int $id
 * @property string $item_key 语言项key
 * @property string $page_key 页面key
 * @property string $create_time 时间戳
 * @property string $modify_time 时间戳
 */
class LanguagePacket extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_language_packet';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['item_key', 'page_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_key' => 'Item Key',
            'page_key' => 'Page Key',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }


    public function returnPageKey(){
        return [
            'SITE_INDEX'=>'首页',
            'REGISTER_LOGIN'=>'注册登陆',
            'CUSTOMER_REGISTER'=>'用户注册页',
            'CUSTOMER_LOGIN'=>'用户登录页',
            'FIND_PASSWORD'=>'找回密码',
            'BIND_DEVICE'=>'绑定设备',
            'BIND_MAP'=>'设备地图',
            'TOTAL_DATA'=>'数据分析',
            'DEVICE_LIST'=>'设备列表',
            'MINE'=>'我的',
            "NAV"=>"底部导航",
            'COMMON'=>'通用',
            'ALERT'=>'弹出框',
            'ERROR'=>'错误提示'
        ];
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @param $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
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

        $language_packet_id = $info ? $info['id']:0;
        $desc_params['cond'] = 'language_packet_id=:language_packet_id ';
        $desc_params['args'] = [':language_packet_id'=>$language_packet_id];
        $desc_obj = new LanguagePacketDescription();
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
                'language_short'=>$v['short'],
                'desc_id'=> $desc ? $desc['id']:0,
                'desc_content'=> $desc ? $desc['content']:'',
            ];
        }

        return $item_list ;

    }

    /**
     * 判断key是否存在
     * @param $item_key
     * @return mixed
     */
    public function checkExitsByItemKey($item_key){
        $params['cond'] = 'item_key=:item_key AND is_deleted=:is_deleted';
        $params['args'] = [':item_key'=>$item_key,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 保存信息
     * @param $id
     * @param $post_data
     * @return mixed
     */
    public function saveData($id,$post_data){
        //$add_data = compact('item_key','description','page_key','language_item_list','modify_time');
        if($id){

            $update_data['description'] = $post_data['description'];
            $update_data['page_key'] = $post_data['page_key'];
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$id]);

        }else{
            // 判断是否存在
            $check_exists = $this->checkExitsByItemKey($post_data['item_key']);
            if($check_exists){
                $this->setError('200041');
                return false ;
            }

            $add_data['item_key'] = $post_data['item_key'];
            $add_data['page_key'] = $post_data['page_key'];
            $add_data['description'] = $post_data['description'];
            $add_data['is_deleted'] = 'N';
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $id = $this->baseInsert(self::tableName(),$add_data);
            if(!$id){
                $this->setError('200042');
                return false ;
            }
        }

        $language_item_list = $post_data['language_item_list'];
        // 先删除所有的
        $desc_obj = new LanguagePacketDescription();
        $desc_cond =  'language_packet_id=:language_packet_id';
        $desc_args = [':language_packet_id'=>$id];
        $this->baseDelete($desc_obj::tableName(),$desc_cond,$desc_args);

        // 查询进行插入
        if(!$language_item_list){
            return true ;
        }

        foreach($language_item_list as $language_id=>$v){
            $desc_add_data['content'] = $v;
            $desc_add_data['language_id'] = $language_id;
            $desc_add_data['language_packet_id'] = $id;
            $desc_add_data['create_time'] = date('Y-m-d H:i:s');
            $desc_add_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseInsert($desc_obj::tableName(),$desc_add_data);

            //删除指定的redis
            $redis_key = $this->returnPackageRedisKey($post_data['item_key'],$language_id);
            $redis_obj = new MyRedis();
            $redis_obj->del($redis_key);

        }

        return true ;
    }

    /**
     * 获取语言包默认的redis key 值
     * @param $key
     * @param $lang_id
     * @return mixed
     */
    public function returnPackageRedisKey($key,$lang_id){

        return "Notice:Language:".$key.':'.$lang_id ;
    }



    /**
     * 根据Key值返回用户对应的redis的值
     * @param $key
     * @return mixed
     */
    public function getInfoByKeyFromUser($key){

        // 当前用户选择的语言包信息
        $lang_obj = new Language();
        $lang_id = $lang_obj->getUserDefaultLangId();
        $redis_key = $this->returnPackageRedisKey($key,$lang_id) ;

        $redis_obj = new MyRedis();
        $redis_info  = $redis_obj->get($redis_key);
        if($redis_info){
            return $redis_info ;
        }

        // 数据库
        $key_info = $this->checkExitsByItemKey($key);
        if(!$key_info){
            return  '';
        }

        $desc_obj = new LanguagePacketDescription();
        $params['cond'] = 'language_id=:language_id  AND language_packet_id=:language_packet_id  ';
        $params['args'] = [':language_id'=>$lang_id,':language_packet_id'=>$key_info['id']];
        $info = $this->findOneByWhere($desc_obj::tableName(),$params,self::getDb());
        $msg = $info ? $info['content']:'';
        $redis_obj->set($redis_key,$msg,'3000');
        return $msg ;
    }

    /**
     * 根据页面返回指定的语言包列表信息
     * @param $page_key
     * @return mixed
     */
    public function getListByPageKey($page_key){
        $params['cond'] = 'page_key=:page_key AND is_deleted=:is_deleted';
        $params['args'] = [':page_key'=>$page_key,':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
}
