<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sea_earn_info".
*/
class Shouce extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_shouce';
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
        return [] ;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [] ;
    }

    /**
     * 获取所有父类信息
     * @param $id
     * @return mixed
     */
    public function getParentList($id=0,$type="XML",$field="*",$name=''){

        $params['where_arr']['parent_id'] = $id ;
        $params['where_arr']['file_type'] = $type ;
        $params['not_where_arr']['id'] = $id ;
        if($name){
            $params['like_arr']['file_name'] = $name ;
        }
        $params['return_field'] = $field;
        $params['return_type'] = 'all';
        $list = $this->findByWhere(self::tableName(),$params);
        return $list ;
    }

    /**
     * 根据名称返回指定的列表信息
     * @param $name
     * @param string $type
     * @param string $field
     * @return mixed
     */
    public function getListByName($name,$type="XML",$field="*"){
        $params['where_arr']['file_type'] = $type ;

        $name = trim($name);
        if($name){
            $params['like_arr']['file_name'] = $name ;
        }

        $params['return_field'] = $field;
        $list = $this->findByWhere(self::tableName(),$params);
        return $list ;
    }

    public function getZtreeList($name,$type,$field="id,file_name as name"){
        $list = $this->getParentList(0,$type,$field);

        if($list){
            foreach($list as $k=>$v){
                $list[$k]['open'] = true ;
                $list[$k]['children'] = $this->getParentList($v['id'],$type,$field);

            }
        }

        return $list ;
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @return mixed
     */
    public function getInfoById($id){

        $params['where_arr']['id'] = $id ;
        $params['return_type'] = 'row';
        $info = $this->findByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据DMcode 查询相关手册信息
     * @param $dmCode
     * @return array|bool
     */
    public function getInfoByDmCode($dmCode){
        $where_arr['modelIdentCode'] = $dmCode['modelIdentCode'];
        $where_arr['systemDiffCode'] = $dmCode['systemDiffCode'];
        $where_arr['systemCode'] = $dmCode['systemCode'];
        $where_arr['subSystemCode'] = $dmCode['subSystemCode'];
        $where_arr['subSubSystemCode'] = $dmCode['subSubSystemCode'];
        $where_arr['assyCode'] = $dmCode['assyCode'];
        $where_arr['disassyCode'] = $dmCode['disassyCode'];
        $where_arr['disassyCodeVariant'] = $dmCode['disassyCodeVariant'];
        $where_arr['infoCode'] = $dmCode['infoCode'];
        $where_arr['infoCodeVariant'] = $dmCode['infoCodeVariant'];
        $where_arr['itemLocationCode'] = $dmCode['itemLocationCode'];

        $params['where_arr'] = $where_arr ;
        $params['return_type'] = 'row';
        $info = $this->findByWhere(self::tableName(),$params);
        return $info ;
     }

    /**
     * 根据文件名称文件信息
     * @param $file_name 文件名称
     * @param $type 文件类型
     * @return mixed
     */
    public function getInfoByFileName($file_name,$type='SVG'){

        $params['where_arr']['file_name'] = $file_name ;
        $params['where_arr']['file_type'] = $type ;
        $params['return_type'] = 'row';
        $info = $this->findByWhere(self::tableName(),$params,self::getDb());

        return $info ;
    }

    /**
     * 返回dmcode格式化字符串
     * @param $dmCode
     * return string
     */
    public function formatDmCode($dmCode){
        if(!$dmCode){
            return  '';
        }
        $dmCode_str = $dmCode['modelIdentCode'].'-'.$dmCode['systemDiffCode'].'-'.$dmCode['systemCode'].'-'.$dmCode['subSystemCode'].$dmCode['subSubSystemCode'].'-'.$dmCode['assyCode'];
        $dmCode_str .= '-'.$dmCode['disassyCode'].$dmCode['disassyCodeVariant'].'-'.$dmCode['infoCode'].$dmCode['infoCodeVariant'].'-'.$dmCode['itemLocationCode'];

        return $dmCode_str ;
    }

}
