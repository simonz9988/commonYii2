<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_lh_process".
 *
 * @property int $id
 * @property string $name 名称
 * @property string $type INPUT--输入框/CALC计算值
 * @property string $note 备注
 * @property int $sort 排序
 * @property string $status ENABLED DISABLED
 * @property string $is_deleted 是否阐述
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhLhProcess extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_lh_process';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['name', 'type', 'note', 'status'], 'string', 'max' => 255],
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
            'type' => 'Type',
            'note' => 'Note',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回指定名称
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

    /**
     * 保存信息
     * @param $id
     * @param $post_data
     * @return mixed
     */
    public function savePostData($id,$post_data){

        $add_data['name'] = $post_data['name'];
        $add_data['type'] = $post_data['type'];
        $add_data['note'] = $post_data['note'];
        $add_data['sort'] = $post_data['sort'];
        $add_data['modify_time'] = date('Y-m-d H:i:s');

        $old_content = $this->getInfoById($id) ;
        if($id){
            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $this->baseInsert(self::tableName(),$add_data);
        }
        $new_content = $this->getInfoById($id) ;

        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'LH_PROCESS',
            'redundancy_id' => $id,
            'old_content' => $old_content,
            'new_content' => $new_content
        );

        // 日志操作
        $log_operate_obj = new LogOperate();
        $log_operate_obj->insert( $log_data);

        return true;
    }

    /**
     * 返回指定类型所有信息
     * @param $type
     * @return mixed
     */
    public function getListByType($type){
        $params['cond'] = 'type =:type AND is_deleted=:is_deleted';
        $params['args'] = [':type'=>$type,':is_deleted'=>'N'];
        $params['orderby'] = ' sort ASC ';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 获取所有有效列表
     * @return mixed
     */
    public function getTotalList(){
        $params['cond'] = ' is_deleted=:is_deleted';
        $params['args'] = [':is_deleted'=>'N'];
        $params['orderby'] = ' sort ASC ';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
}
