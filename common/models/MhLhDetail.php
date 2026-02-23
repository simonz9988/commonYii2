<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mh_lh_detail".
 *
 * @property int $id
 * @property string $process_id 过程ID
 * @property string $lh_no 炉号
 * @property string $value 填入的值
 * @property string $is_deleted 是否阐述
 * @property int $admin_user_id 管理员用户ID
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhLhDetail extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_lh_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['process_id', 'lh_no', 'value'], 'string', 'max' => 255],
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
            'process_id' => 'Process ID',
            'lh_no' => 'Lh No',
            'value' => 'Value',
            'is_deleted' => 'Is Deleted',
            'admin_user_id' => 'Admin User ID',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 获取炉号列表
     * @return array
     */
    public function getNoList(){
        $params['cond'] = 'is_deleted=:is_deleted';
        $params['args'] = [':is_deleted'=>'N'];
        $params['group_by'] = 'lh_no';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        $res = [] ;
        if($list){
            foreach($list as $v){
                $res[] = $v['lh_no'] ;
            }
        }

        return $res ;
    }

    /**
     * 保存信息
     * @param $admin_user_info
     * @param $post_data
     * @return mixed
     */
    public function saveData($admin_user_info,$post_data){
        $add_data['process_id'] = $post_data['process_id'];
        $add_data['lh_no'] = $post_data['lh_no'];
        $add_data['month'] = date('m');
        $add_data['year'] = date('Y');
        $add_data['value'] = $post_data['value'];
        $add_data['admin_user_id'] = $admin_user_info['id'];
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 根据炉号和流程ID返回录入总额
     * @param $lh_no
     * @param $process_id
     * @return mixed
     */
    public function getTotalByLhAndProcessId($lh_no,$process_id){

        $params['cond'] = 'process_id=:process_id AND lh_no=:lh_no';
        $params['args'] = [':process_id'=>$process_id,':lh_no'=>$lh_no];
        $params['fields'] = 'sum(value) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    public function getTotalByYearMonthAndProcessId($year,$month,$process_id){
        $params['cond'] = 'process_id=:process_id AND year=:year AND month=:month';
        $params['args'] = [':process_id'=>$process_id,':year'=>$year,':month'=>$month];
        $params['fields'] = 'sum(value) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 格式化信息
     * @param $list
     * @param $process_list
     */
    public function formatList($list,$process_list){
        if(!$list){
            return [];
        }

        $process_list_total = [] ;
        $note_obj = new MhLhNote();

        // 预警百分比
        $waring_percent = 98 ;
        foreach($list as $k=>$v){

            $detail = [] ;
            $detail_other = [] ;
            $input_list = [];

            $is_warning = false ;
            foreach($process_list as $p_k=>$p_v){

                // 详情其他信息 存放类型 具体值
                $detail_other[$p_k]['type'] = $p_v['type'];
                $detail_other[$p_k]['is_warning'] = false ;
                // 判断类型
                if($p_v['type'] =='INPUT'){
                    // 计算总额
                    $detail[$p_k] = $this->getTotalByLhAndProcessId($v['lh_no'],$p_v['id']);
                    $detail_other[$p_k]['value'] =$detail[$p_k] ;
                    $input_list[] =  $detail[$p_k];

                    if(!isset($process_list_total[$p_k])){
                        $process_list_total[$p_k]  = $detail[$p_k];
                    }else{
                        $process_list_total[$p_k]  = $process_list_total[$p_k] + $detail[$p_k];
                    }

                }else{

                    $total_num = count($input_list) -1 ;
                    $prev_detail = isset($input_list[$total_num])?$input_list[$total_num] : 0 ;
                    $prev_prev_detail = isset($input_list[$total_num-1])?$input_list[$total_num-1] : 0 ;

                    $detail[$p_k] = $prev_prev_detail ? numberSprintf($prev_detail/$prev_prev_detail,4)*100 : 0;

                    $detail_other[$p_k]['value'] =$detail[$p_k] ;
                    if($detail[$p_k] <$waring_percent || $detail[$p_k] >100){
                        $detail_other[$p_k]['is_warning'] = true ;
                        $is_warning = true;
                    }

                    $detail[$p_k] = $detail[$p_k].'%';

                    if(!isset($process_list_total[$p_k])){
                        $process_list_total[$p_k]  = $detail[$p_k];
                    }else{
                        $process_list_total[$p_k]  = $process_list_total[$p_k] + $detail[$p_k];
                    }
                }

            }

            $list[$k]['detail'] = $detail ;
            $list[$k]['detail_other'] = $detail_other ;
            $list[$k]['note'] = $note_obj->getLhNote($v['lh_no']);
            $list[$k]['is_warning'] =$is_warning;

            // 返回是否结案
            $list[$k]['is_final'] = $note_obj->getLhIsFinal($v['lh_no']);
        }
        return compact('list','process_list_total') ;
    }

    /**
     * 格式化信息
     * @param $list
     * @param $process_list
     */
    public function formatTotalList($list,$process_list){
        if(!$list){
            return [];
        }

        $note_obj = new MhLhTotalNote();
        foreach($list as $k=>$v){

            $detail = [] ;
            $input_list = [];
            foreach($process_list as $p_k=>$p_v){


                // 计算总额
                $detail[$p_k] = $this->getTotalByYearMonthAndProcessId($v['year'],$v['month'],$p_v['id']);




            }

            $list[$k]['detail'] = $detail ;
            $list[$k]['note'] = $note_obj->getNote($v['year'],$v['month']) ;
        }
        return $list ;
    }
}
