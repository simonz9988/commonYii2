<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_site_config".
 *
 * @property string $id
 * @property string $config_key 网站配置关键字
 * @property string $config_desc 配置描述
 * @property string $config_value 配置值
 * @property string $insert_sql 插入的sql 语句
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class SiteConfig extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_site_config';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['config_value', 'insert_sql'], 'required'],
            [['config_value', 'insert_sql'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['config_key', 'config_desc'], 'string', 'max' => 50],
            [['config_key'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'config_key' => 'Config Key',
            'config_desc' => 'Config Desc',
            'config_value' => 'Config Value',
            'insert_sql' => 'Insert Sql',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }


    public function getByKey($key,$msg_type='string'){

        $params['where_arr']['config_key'] = $key ;
        $params['cond'] = 'config_key=:config_key';
        $params['args'] = [':config_key'=>$key];
        $info = $this->findOneByWhere($this->tableName(),$params,self::getDb());
        $rst = '';

        if($info){
            $rst = $info['config_value'];
        }
        if($msg_type=='string'){
            return $rst ;
        }

        if($msg_type =='json'){
            return json_decode($rst,true);
        }

        if($msg_type=='formate'){
            $info = json_decode($rst,true);
            return isset($info['formate_info'])?$info['formate_info']:array();
        }

        if($msg_type=='all_info'){
            $info = json_decode($rst,true);
            return isset($info['all_info'])?$info['all_info']:array();
        }
        //逗号分隔数据
        if($msg_type=='douhao_fenge'){
            $info = explode(',',$rst);
            return $info ;
        }
    }

    /**
     * 保存制定key值的信息
     * @param $config_key
     * @param $config_value
     * @return mixed
     */
    public function saveByKey($config_key,$config_value){
         // 判断是否存在
        $params['cond'] = 'config_key=:config_key';
        $params['args'] = [':config_key'=>$config_key];
        $info = $this->findOneByWhere($this->tableName(),$params,self::getDb());

        // 当前时间
        $now = date('Y-m-d H:i:s');

        if($info){
            $update_data['config_value'] = $config_value ;
            $update_data['update_time'] = $now ;
            return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }else{

            $add_data['config_key'] = $config_key  ;
            $add_data['config_desc'] = $config_key  ;
            $add_data['config_value'] = $config_value  ;
            $add_data['insert_sql'] = ''  ;
            $add_data['create_time'] = $now  ;
            $add_data['update_time'] = $now  ;
            return $this->baseInsert(self::tableName(),$add_data);
        }

    }

    /**
     * 批量保存机器配置信息
     * @param $post_data
     * @return mixed
     */
    public function saveMachineSettingBatch($post_data){

        $value = isset($post_data['jiangli_keyong_bili'])?($post_data['jiangli_keyong_bili']):0 ;
        $this->saveByKey('jiangli_keyong_bili',$value) ;

        $value = isset($post_data['jiangli_dongjie_bili'])?($post_data['jiangli_dongjie_bili']):0 ;
        $this->saveByKey('jiangli_dongjie_bili',$value) ;

        $value = isset($post_data['fenxiang1_total'])?($post_data['fenxiang1_total']):0 ;
        $this->saveByKey('fenxiang1_total',$value) ;

        $value = isset($post_data['fenxiang1_level1'])?($post_data['fenxiang1_level1']):0 ;
        $this->saveByKey('fenxiang1_level1',$value);

        $value = isset($post_data['fenxiang1_level2'])?($post_data['fenxiang1_level2']):0 ;
        $this->saveByKey('fenxiang1_level2',$value) ;

        $value = isset($post_data['fenxiang2_total'])?($post_data['fenxiang2_total']):0 ;
        $this->saveByKey('fenxiang2_total',$value) ;

        $value = isset($post_data['fenxiang2_level1'])?($post_data['fenxiang2_level1']):0 ;
        $this->saveByKey('fenxiang2_level1',$value) ;

        $value = isset($post_data['fenxiang2_level2'])?($post_data['fenxiang2_level2']):0 ;
        $this->saveByKey('fenxiang2_level2',$value) ;

        $value = isset($post_data['p1_tuandui_yeji'])?($post_data['p1_tuandui_yeji']):0 ;
        $this->saveByKey('p1_tuandui_yeji',$value) ;

        $value = isset($post_data['p1_xinzeng_yeji_bili'])?($post_data['p1_xinzeng_yeji_bili']):0 ;
        $this->saveByKey('p1_xinzeng_yeji_bili',$value) ;

        $value = isset($post_data['p1_qita_yeji'])?($post_data['p1_qita_yeji']):0 ;
        $this->saveByKey('p1_qita_yeji',$value) ;

        $value = isset($post_data['p2_tuandui_yeji'])?($post_data['p2_tuandui_yeji']):0 ;
        $this->saveByKey('p2_tuandui_yeji',$value) ;

        $value = isset($post_data['p2_xinzeng_yeji_bili'])?($post_data['p2_xinzeng_yeji_bili']):0 ;
        $this->saveByKey('p2_xinzeng_yeji_bili',$value) ;

        $value = isset($post_data['p2_qita_yeji'])?($post_data['p2_qita_yeji']):0 ;
        $this->saveByKey('p2_qita_yeji',$value) ;

        $value = isset($post_data['p3_tuandui_yeji'])?($post_data['p3_tuandui_yeji']):0 ;
        $this->saveByKey('p3_tuandui_yeji',$value) ;

        $value = isset($post_data['p3_xinzeng_yeji_bili'])?($post_data['p3_xinzeng_yeji_bili']):0 ;
        $this->saveByKey('p3_xinzeng_yeji_bili',$value) ;

        $value = isset($post_data['p3_qita_yeji'])?($post_data['p3_qita_yeji']):0 ;
        $this->saveByKey('p3_qita_yeji',$value) ;

        $value = isset($post_data['usdt_cash_out_fee'])?($post_data['usdt_cash_out_fee']):0 ;
        $this->saveByKey('usdt_cash_out_fee',$value) ;

        $value = isset($post_data['fil_cash_out_fee'])?($post_data['fil_cash_out_fee']):0 ;
        $this->saveByKey('fil_cash_out_fee',$value) ;

    }

    /**
     * 批量保存robot 相关设置信息
     * @param $post_data
     * @return mixed
     */
    public function saveRobotSettingBatch($post_data){

        $value = isset($post_data['robot_register_send_integral'])?($post_data['robot_register_send_integral']):0 ;
        $this->saveByKey('robot_register_send_integral',$value) ;

        $value =  isset($post_data['robot_usdt_integral_percent'])?($post_data['robot_usdt_integral_percent']):0 ;
        $this->saveByKey('robot_usdt_integral_percent',$value) ;
    }
}
