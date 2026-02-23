<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mh_lh_total_note".
 *
 * @property string $id
 * @property string $year 年份
 * @property string $month 月份
 * @property string $note 备注
 * @property string $is_deleted 是否阐述
 * @property int $admin_user_id 管理员用户ID
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhLhTotalNote extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_lh_total_note';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['year', 'month', 'note'], 'string', 'max' => 255],
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
            'year' => 'Year',
            'month' => 'Month',
            'note' => 'Note',
            'is_deleted' => 'Is Deleted',
            'admin_user_id' => 'Admin User ID',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据年份和月份获取指定信息
     * @param $year
     * @param $month
     * @return mixed
     */
    public function getInfoByYearAndMonth($year,$month){
        $params['cond'] = 'year=:year AND  month=:month AND is_deleted=:is_deleted';
        $params['args'] = [':year'=>$year,':month'=>$month,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取指定月份的备注信息
     * @param $year
     * @param $month
     * @return string
     */
    public function getNote($year,$month){
        $info = $this->getInfoByYearAndMonth($year,$month);
        return $info ? $info['note']:'';
    }

    /**
     * 保存总表备注信息
     * @param $year
     * @param $month
     * @param $note
     * @param $adminUserInfo
     * @return mixed|string
     */
    public function saveNote($year,$month,$note,$adminUserInfo){

        $info = $this->getInfoByYearAndMonth($year,$month);
        if($info){
            $update_data['note'] = $note;
            $update_data['admin_user_id'] = $adminUserInfo['id'];
            $update_data['modify_time'] = date('Y-md- H:i:s');
            return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }else{

            $add_data['admin_user_id'] = $adminUserInfo['id'];
            $add_data['year'] = $year ;
            $add_data['month'] = $month ;
            $add_data['note'] = $note ;
            $add_data['admin_user_id'] = $adminUserInfo['id'] ;
            $add_data['modify_time'] = date('Y-md- H:i:s');
            $add_data['create_time'] = date('Y-md- H:i:s');
            return $this->baseInsert(self::tableName(),$add_data);
        }
    }
}
