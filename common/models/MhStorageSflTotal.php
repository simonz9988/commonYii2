<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_storage_sfl_total".
 *
 * @property int $id
 * @property string $from_date_timestamp
 * @property string $type 发料厂家
 * @property string $note 备注
 * @property string $status ENABLED DISABLED
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhStorageSflTotal extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_storage_sfl_total';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['from_date_timestamp', 'type', 'note', 'status'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'from_date_timestamp' => 'From Date Timestamp',
            'type' => 'Type',
            'note' => 'Note',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据时间和类型获取指定信息
     * @param $from_date_timestamp
     * @param $type
     * @return mixed
     */
    public function getNoteByFromDateAndType($from_date_timestamp,$type)
    {
        $params['cond'] = 'from_date_timestamp=:from_date_timestamp AND type=:type';
        $params['args'] = [':from_date_timestamp'=>$from_date_timestamp,':type'=>$type];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['note']:'';

    }

    public function getInfoByFromDateAndType($from_date_timestamp,$type){
        $params['cond'] = 'from_date_timestamp=:from_date_timestamp AND type=:type';
        $params['args'] = [':from_date_timestamp'=>$from_date_timestamp,':type'=>$type];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    public function savePostData($post_data){
        $from_date = $post_data['from_date'];
        $type = $post_data['type'];
        $note  = $post_data['note'];
        $from_date_timestamp = strtotime($from_date);
        $info = $this->getInfoByFromDateAndType($from_date_timestamp,$type);

        $old_content = $info;
        if($info){
            $update_data['note'] = $note ;
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }else{
            $add_data['from_date_timestamp'] = $from_date_timestamp ;
            $add_data['type'] = $type ;
            $add_data['note'] = $note ;
            $add_data['create_time'] = date('Y-m-d H:i:s'); ;
            $add_data['modify_time'] = date('Y-m-d H:i:s'); ;
            $this->baseInsert(self::tableName(),$add_data);

        }

        $new_content = $this->getInfoByFromDateAndType($from_date_timestamp,$type);

        $params_obj = new MhHtParams();

        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'STORAGE_SFL_TOTAL',
            'redundancy_id' => $from_date.'--'.$params_obj->getDetailById($type),
            'old_content' => $old_content,
            'new_content' => $new_content
        );

        // 日志操作
        $log_operate_obj = new LogOperate();
        $log_operate_obj->insert( $log_data);

        return true ;
    }

}
