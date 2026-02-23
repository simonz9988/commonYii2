<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_lh_note".
 *
 * @property string $id
 * @property string $lh_no 炉号
 * @property string $note 备注
 * @property string $is_deleted 是否阐述
 * @property int $admin_user_id 管理员用户ID
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhLhNote extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_lh_note';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['lh_no', 'note'], 'string', 'max' => 255],
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
            'lh_no' => 'Lh No',
            'note' => 'Note',
            'is_deleted' => 'Is Deleted',
            'admin_user_id' => 'Admin User ID',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据炉号干活指定信息
     * @param $lh_no
     * @return mixed
     */
    public function getInfoByLh($lh_no){
        $params['cond'] = 'lh_no=:lh_no AND is_deleted=:is_deleted';
        $params['args'] = [':lh_no'=>$lh_no,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取炉号对应的备注信息
     * @param $lh_no
     * @return mixed
     */
    public function getLhNote($lh_no){
        $info = $this->getInfoByLh($lh_no);
        return $info ? $info['note']:'';
    }

    /**
     * 获取炉号对应的备注信息
     * @param $lh_no
     * @return mixed
     */
    public function getLhIsFinal($lh_no){
        $info = $this->getInfoByLh($lh_no);
        $is_final =  $info ? $info['is_final']:'N';
        return $is_final =='Y' ? true:false ;
    }



    /**
     * 保存炉号备注信息
     * @param $lh_no
     * @param $note
     * @param $adminUserInfo
     * @return mixed
     */
    public function saveNote($lh_no,$note,$adminUserInfo){

        $info = $this->getInfoByLh($lh_no);
        $old_content = $info ;
        if($info){
            $update_data['note'] = $note;
            $update_data['admin_user_id'] = $adminUserInfo['id'];
            $update_data['modify_time'] = date('Y-md- H:i:s');
            $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }else{

            $add_data['admin_user_id'] = $adminUserInfo['id'];
            $add_data['lh_no'] = $lh_no ;
            $add_data['note'] = $note ;
            $add_data['admin_user_id'] = $adminUserInfo['id'] ;
            $add_data['modify_time'] = date('Y-md- H:i:s');
            $add_data['create_time'] = date('Y-md- H:i:s');
            $this->baseInsert(self::tableName(),$add_data);
        }

        $new_content = $this->getInfoByLh($lh_no);
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'LH_STATISTICS',
            'redundancy_id' => $lh_no,
            'old_content' => $old_content,
            'new_content' => $new_content
        );

        // 日志操作
        $log_operate_obj = new LogOperate();
        $log_operate_obj->insert( $log_data);

    }

    /**
     * 最终结案
     * @param $lh_no
     * @param $admin_user_info
     * @return mixed
     */
    public function finalCheck($lh_no,$admin_user_info){

        $info = $this->getInfoByLh($lh_no);
        if($info){
            $update_data['is_final'] = 'Y';
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseUpdate(self::tableName(),$update_data,'lh_no=:lh_no',[':lh_no'=>$lh_no]);
        }else{
            $add_data['lh_no'] = $lh_no;
            $add_data['admin_user_id'] = $admin_user_info['id'];
            $add_data['note'] = '结案';
            $add_data['sort'] = '99';
            $add_data['is_final'] = 'Y';
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            return $this->baseInsert(self::tableName(),$add_data);
        }

    }
}
