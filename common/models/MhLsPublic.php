<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_ls_public".
 *
 * @property int $id
 * @property string $contract_no 合同编号
 * @property string $from_date 日期
 * @property string $department 部门
 * @property string $kuanxiang 款项
 * @property string $mingxi 明细
 * @property string $type 收入/支出
 * @property string $amount 金额
 * @property string $balance 账户余额
 * @property string $invoice_status 发票状态
 * @property string $invoice_no 发票编号
 * @property string $note 备注
 * @property string $status 状态
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhLsPublic extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_ls_public';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['contract_no', 'from_date', 'department', 'kuanxiang', 'mingxi', 'type', 'amount', 'balance', 'invoice_status', 'invoice_no', 'note', 'status'], 'string', 'max' => 255],
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
            'contract_no' => 'Contract No',
            'from_date' => 'From Date',
            'department' => 'Department',
            'kuanxiang' => 'Kuanxiang',
            'mingxi' => 'Mingxi',
            'type' => 'Type',
            'amount' => 'Amount',
            'balance' => 'Balance',
            'invoice_status' => 'Invoice Status',
            'invoice_no' => 'Invoice No',
            'note' => 'Note',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    public function getInfoById($id,$fields='*'){

        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 新增数据
     * @param $id
     * @param $post_data
     * @return mixed
     */
    public function savePostData($id,$post_data){

        $add_data['contract_no'] = $post_data['contract_no'] ;
        $add_data['from_date'] = $post_data['from_date'] ;
        $add_data['department'] = $post_data['department'] ;
        $add_data['kuanxiang'] = $post_data['kuanxiang'] ;
        $add_data['mingxi'] = $post_data['mingxi'] ;
        $add_data['type'] = $post_data['type'] ;
        $add_data['amount'] = $post_data['amount'] ;
        $add_data['balance'] = $post_data['balance'] ;
        $add_data['invoice_status'] = $post_data['invoice_status'] ;
        $add_data['invoice_no'] = $post_data['invoice_no'] ;
        $add_data['note'] = $post_data['note'] ;
        $add_data['status'] = $post_data['status'] ;
        $add_data['baoxiao_fangshi'] = $post_data['baoxiao_fangshi'] ;
        $add_data['baoxiao_zhuangtai'] = $post_data['baoxiao_zhuangtai'] ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;

        $contract_res = $this->checkContractNo($add_data['contract_no'],$id);
        if(!$contract_res){
            $this->setError('200044');
            return false ;
        }

        $old_content = $this->getInfoById($id) ;
        if($id){
            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            $this->baseInsert(self::tableName(),$add_data);
        }

        $new_content = $this->getInfoById($id) ;
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'TURNOVER_PUBLIC',
            'redundancy_id' => $id,
            'old_content' => $old_content,
            'new_content' => $new_content
        );

        // 日志操作
        $log_operate_obj = new LogOperate();
        $log_operate_obj->insert( $log_data);

        return true ;
    }

    /**
     * 判断合同编号是否重复
     * @param $contract_no
     * @param $id
     * @return mixed
     */
    public function checkContractNo($contract_no,$id){
        if(!$id){

            $exists_info = $this->getInfoByContractNo($contract_no);
            if($exists_info){
                return false ;
            }
        }else{

            $info = $this->getInfoById($id);
            if($info['contract_no']!=$contract_no){
                $exists_info = $this->getInfoByContractNo($contract_no);
                if($exists_info){
                    return false ;
                }
            }
        }

        return true ;
    }

    /**
     * @param $contract_no
     * @param string $fields
     * @return mixed
     */
    public function getInfoByContractNo($contract_no,$fields='id'){
        $params['cond'] = 'contract_no=:contract_no AND is_deleted=:is_deleted';
        $params['args'] = [':contract_no'=>$contract_no,':is_deleted'=>'N'];
        $params['fields']= $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 格式化处理列表信息
     * @param $list
     * @return  mixed
     */
    public function formatList($list){
        if(!$list){
            return [] ;
        }

        $params_obj = new MhHtParams();
        foreach($list as $k=>$v){
            $list[$k]['department'] = $params_obj->getDetailById($v['department']);
            $list[$k]['kuanxiang'] = $params_obj->getDetailById($v['kuanxiang']);
            $list[$k]['type'] = $params_obj->getDetailById($v['type']);
            $list[$k]['invoice_status'] = $params_obj->getDetailById($v['invoice_status']);
            $list[$k]['baoxiao_zhuangtai'] = $params_obj->getDetailById($v['baoxiao_zhuangtai']);
            $list[$k]['baoxiao_fangshi'] = $params_obj->getDetailById($v['baoxiao_fangshi']);

        }

        return $list ;
    }

    /**
     * 根据列表返回汇总信息
     * @param $list
     * @return mixed
     */
    public function getTotalInfoByList($list){

        if(!$list){
            return [] ;
        }

        $total_info = [] ;
        $total_key = ['amount','balance'] ;
        foreach($list as $v){
            foreach($total_key as $key){
                if(isset($total_info[$key])){
                    $total_info[$key] +=$v[$key] ;
                }else{
                    $total_info[$key] =$v[$key] ;
                }
            }
        }

        //总数目
        $total_num = count($list);
        //$total_info['cgdj'] = numberSprintf($total_info['cgdj']/$total_num,2) ;
        return $total_info ;
    }
}
