<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_road".
 *
 * @property int $id
 * @property int $project_id 项目管理
 * @property string $name 名称
 * @property string $note 备注
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $status 状态(ENABLED-启用 DISABLED-禁用)
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyRoad extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_road';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['name', 'note'], 'string', 'max' => 255],
            [['is_deleted'], 'string', 'max' => 1],
            [['status'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'name' => 'Name',
            'note' => 'Note',
            'is_deleted' => 'Is Deleted',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 保存信息
     * @param $id
     * @param $add_data
     * @return mixed
     */
    public function saveData($id,$add_data){

        if($id){
            return $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
             $add_data['create_time'] = date('Y-m-d H:i:s');
             $add_data['is_deleted'] = 'N';
             $add_data['status'] = 'ENABLED';
             return $this->baseInsert(self::tableName(),$add_data) ;
        }
    }

    /**
     * 根据项目ID返回总数目
     * @param $project_id
     * @return mixed
     */
    public function getTotalNumByProjectId($project_id){
        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N'];
        $params['fields']  = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据项目ID获取列表信息
     * @param $project_id
     * @param $page
     * @param $page_num
     * @param $fields
     * @return array
     */
    public function getListByProjectId($project_id,$page,$page_num,$fields){
        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N'];
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;
        $params['orderby'] = 'id desc';
        $params['fields'] = $fields;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据ID返回路段名称
     * @param $id
     * @return mixed
     */
    public function getNameById($id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = 'name';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? $info['name']:'';
    }

    /**
     * 根据ID返回指定信息
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
     * 根据公司ID返回总数目
     * @param $company_ids
     * @return mixed
     */
    public function getTotalNumByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in ('.implode(',',$company_ids).') AND is_deleted=:is_deleted AND status=:status';
        $params['args'] = [':is_deleted'=>'N',':status'=>'ENABLED'];
        $params['fields'] = ' count(1) as total ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }
}
