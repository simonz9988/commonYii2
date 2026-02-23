<?php

namespace common\models;

use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sea_language".
 *
 * @property int $id
 * @property string $name 语言名称
 * @property string $is_default 是否是默认，Y默认/N普通
 * @property string $short 语言简写
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class Language extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_language';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['name', 'short'], 'string', 'max' => 255],
            [['is_default', 'is_deleted'], 'string', 'max' => 1],
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
            'is_default' => 'Is Default',
            'short' => 'Short',
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

        $params['cond'] = 'short=:short AND id !=:id AND is_deleted=:is_deleted';
        $params['args'] = [':short'=>$unique_key,':id'=>$id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
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
     * 返回所有的有效列表
     * @param string $fields
     * @return mixed
     */
    public function getAll($fields="*"){

        $params['cond'] = 'is_deleted=:is_deleted';
        $params['args'] = [':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $params['orderby'] = 'sort desc';
        $list  = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 获取默认语言信息
     * @return array|mixed
     */
    public  function getDefault(){
        $params['cond'] = 'is_deleted=:is_deleted AND is_default =:is_default';
        $params['args'] = [':is_deleted'=>'N',':is_default'=>'Y'];
        $params['fields'] = '*' ;
        $params['orderby'] = 'sort desc';
        $list  = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if($list){
            $list = $this->getAll();
        }

        return isset($list[0]) ?$list[0]:[];
    }

    // 获取当前选择的默认语言
    public function getUserDefaultLangId(){

        $member_obj = new Member() ;
        $user_id = $member_obj->getLoginUserIdByAccessToken();
        if($user_id){

             $redis_obj = new MyRedis();
             $redis_key = 'user_default_lang:'.$user_id ;
             $redis_info = $redis_obj->get($redis_key);
             if($redis_info){
                 return $redis_info ;
             }

             // 查询session 中的数据
            $session_user_info = Yii::$app->session->get("user_default_lang");

            if(!$session_user_info || is_null($session_user_info)){

                $default = $this->getDefault();
                $lang_id = $default ? $default['id'] :1 ;

            }else{
                $lang_id = $session_user_info ;
            }

            // 设置缓存
            $redis_obj->set($redis_key,$lang_id) ;

            return $lang_id ;


        }else{
            // 获取用户默认的语言信息
            $session_user_info = Yii::$app->session->get("user_default_lang");

            if(!$session_user_info || is_null($session_user_info)){

                $default = $this->getDefault();
                $lang_id = $default ? $default['id'] :1 ;
                Yii::$app->session->set("user_default_lang",$lang_id);
                return $lang_id ;
            }
        }


        return  Yii::$app->session->get("user_default_lang");
    }
}
