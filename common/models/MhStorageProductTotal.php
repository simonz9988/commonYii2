<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_storage_product_total".
 *
 * @property int $id
 * @property string $from_date 日期
 * @property string $name 产品名称
 * @property string $note 备注
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhStorageProductTotal extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_storage_product_total';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['from_date', 'name', 'note'], 'string', 'max' => 255],
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
            'from_date' => 'From Date',
            'name' => 'Name',
            'note' => 'Note',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据日期和名称返回指定行信息
     * @param $from_date_timestamp
     * @param $name
     * @return mixed
     */
    public function getInfoByFromDateAndName($from_date_timestamp,$name){

        $params['cond'] = 'from_date_timestamp=:from_date_timestamp AND name=:name AND is_deleted=:is_deleted';
        $params['args'] = [':from_date_timestamp'=>$from_date_timestamp,':name'=>$name,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    public function savePostData($post_data){
        $from_date = $post_data['from_date'];
        $name = $post_data['name'];
        $note  = $post_data['note'];
        $from_date_timestamp = strtotime($from_date);
        $info = $this->getInfoByFromDateAndName($from_date_timestamp,$name);

        $old_content = $info;
        if($info){
            $update_data['note'] = $note ;
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }else{
            $add_data['from_date_timestamp'] = $from_date_timestamp ;
            $add_data['name'] = $name ;
            $add_data['note'] = $note ;
            $add_data['create_time'] = date('Y-m-d H:i:s'); ;
            $add_data['modify_time'] = date('Y-m-d H:i:s'); ;
            $this->baseInsert(self::tableName(),$add_data);

        }

        $new_content = $this->getInfoByFromDateAndName($from_date_timestamp,$name);

        $params_obj = new MhHtParams();

        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'STORAGE_PRODUCT_TOTAL',
            'redundancy_id' => $from_date.'--'.$params_obj->getDetailById($name),
            'old_content' => $old_content,
            'new_content' => $new_content
        );

        // 日志操作
        $log_operate_obj = new LogOperate();
        $log_operate_obj->insert( $log_data);

        return true ;
    }

    /**
     * 返回备注信息
     * @param $from_date_timestamp
     * @param $name
     * @return mixed
     */
    public function getNoteByFromDateAndName($from_date_timestamp,$name){
        $info = $this->getInfoByFromDateAndName($from_date_timestamp,$name);
        return $info ? $info['note']:'';
    }
}
