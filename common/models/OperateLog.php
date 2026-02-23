<?php

namespace common\models;

use Yii;
use yii\db\Query;

/**
 * This is the model class for table "sea_operate_log".
 *
 * @property int $id
 * @property string $operate_user_name 操作用户
 * @property string $operate_time 操作时间
 * @property string $class_name 类名
 * @property string $function_name 方法名
 * @property string $action 动作
 * @property int $redundancy_id 冗余id 分别对应 order_id/goods_id
 * @property string $file_path 文件日志路径
 * @property string $old_content 老数据
 * @property string $new_content 新数据
 * @property string $ip
 */
class OperateLog extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_operate_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['operate_time', 'class_name', 'function_name', 'action', 'old_content', 'new_content', 'ip'], 'required'],
            [['operate_time'], 'safe'],
            [['redundancy_id'], 'integer'],
            [['old_content', 'new_content'], 'string'],
            [['operate_user_name'], 'string', 'max' => 100],
            [['class_name'], 'string', 'max' => 255],
            [['function_name'], 'string', 'max' => 50],
            [['action', 'ip'], 'string', 'max' => 20],
            [['file_path'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'operate_user_name' => 'Operate User Name',
            'operate_time' => 'Operate Time',
            'class_name' => 'Class Name',
            'function_name' => 'Function Name',
            'action' => 'Action',
            'redundancy_id' => 'Redundancy ID',
            'file_path' => 'File Path',
            'old_content' => 'Old Content',
            'new_content' => 'New Content',
            'ip' => 'Ip',
        ];
    }

    public function returnTypeList(){
        return  [
            'EDIT_CATEGORY'=>'编辑设备分类',

        ] ;
    }

    /**
     * 根据条件查询充值信息
     * @param  array  $ids
     * @param  $table_name
     * @return array
     */
    public function getLogCompareInfoByIdAll($ids,$table_name){
        $db = new Query();

        // 日志数据库
        $db_log = Yii::$app->db;

        $query = $db->select('*')->from($table_name);
        $query->where(['in', 'id', $ids]);
        $query->orderBy("id DESC");

        return $query->all($db_log);
    }

    /**
     * 格式化操作类型名称信息
     * @param $list
     * @param $action_type_list
     * @return mixed
     */
    public function formatList($list,$action_type_list){

        if(!$list){
            return  [] ;
        }

        foreach($list as $k=>$v){
            $action = isset($action_type_list[$v['action']]) ? $action_type_list[$v['action']]  : $v['action'];
            $list[$k]['action'] = $action ;
        }
        return $list ;
    }
}
