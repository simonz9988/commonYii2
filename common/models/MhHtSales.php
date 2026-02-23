<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_ht_sales".
 *
 * @property int $id
 * @property string $contract_no 合同编号
 * @property string $supplier_name 供应商名称
 * @property string $chanpin_bie 产品别
 * @property string $paihao 牌号
 * @property string $product_form 产品形态
 * @property string $diameter 尺寸直径
 * @property string $long 尺寸长度
 * @property string $purchase_volume 采购量
 * @property string $cgl 实际交货量
 * @property string $cgdj 采购单价
 * @property string $yinfkze 应付款金额
 * @property string $yifukuanze 已付款金额
 * @property string $fkce 付款差额
 * @property string $fuzt 付款状态
 * @property string $cgrq 采购日期
 * @property string $htjq 合同交期
 * @property string $sjjq 实际交期
 * @property string $jhzt 交货状态
 * @property string $status ENABLED DISABLED
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhHtSales extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_ht_sales';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['contract_no', 'supplier_name', 'chanpin_bie', 'paihao', 'product_form', 'diameter', 'long', 'purchase_volume', 'cgl', 'cgdj', 'yinfkze', 'yifukuanze', 'fkce', 'fuzt', 'cgrq', 'htjq', 'sjjq', 'jhzt', 'status'], 'string', 'max' => 255],
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
            'supplier_name' => 'Supplier Name',
            'chanpin_bie' => 'Chanpin Bie',
            'paihao' => 'Paihao',
            'product_form' => 'Product Form',
            'diameter' => 'Diameter',
            'long' => 'Long',
            'purchase_volume' => 'Purchase Volume',
            'cgl' => 'Cgl',
            'cgdj' => 'Cgdj',
            'yinfkze' => 'Yinfkze',
            'yifukuanze' => 'Yifukuanze',
            'fkce' => 'Fkce',
            'fuzt' => 'Fuzt',
            'cgrq' => 'Cgrq',
            'htjq' => 'Htjq',
            'sjjq' => 'Sjjq',
            'jhzt' => 'Jhzt',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回指定的信息
     * @param $id
     * @return mixed
     */
    public function getInfoById($id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
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
        $add_data['customer_name'] = $post_data['customer_name'] ;
        $add_data['chanpin_bie'] = $post_data['chanpin_bie'] ;
        $add_data['paihao'] = $post_data['paihao'] ;
        $add_data['product_form'] = $post_data['product_form'] ;
        $add_data['diameter'] = $post_data['diameter'] ;
        $add_data['long'] = $post_data['long'] ;
        $add_data['danjia'] = $post_data['danjia'] ;
        $add_data['dingdl'] = $post_data['dingdl'] ;
        $add_data['dingdl_type'] = $post_data['dingdl_type'] ;
        $add_data['sjchl'] = $post_data['sjchl'] ;
        $add_data['kszl'] = $post_data['kszl'] ;
        $add_data['sjchl_type'] = $post_data['sjchl_type'] ;
        // 应收款 = 实际出货量 * 单价
        $add_data['yinskze'] = floatval($post_data['danjia'])*floatval($post_data['sjchl']) ;
        $add_data['shijishk'] = $post_data['shijishk'] ;
        // 收款诧异 = 应收 - 实际收款
        $add_data['skcy'] = floatval($add_data['yinskze']) - floatval($add_data['shijishk']) ;
        $add_data['jiaohzt'] = $post_data['jiaohzt'] ;
        $add_data['jdrq'] = $post_data['jdrq'] ;
        $add_data['htjq'] = $post_data['htjq'] ;
        $add_data['sjchrq'] = $post_data['sjchrq'] ;
        $add_data['chnf'] = $post_data['chnf'] ;
        $add_data['chyf'] = $post_data['chyf'] ;
        $add_data['note'] = $post_data['note'] ;
        $add_data['status'] = $post_data['status'] ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;

        // 合同编号可以一对多
        $contract_res = $this->checkContractNo($add_data['contract_no'],$id);
        if(!$contract_res){
            //$this->setError('200044');
            //return false ;
        }

        $old_content = $this->getInfoById($id) ;

        if($id){
            $add_extra = $post_data['add_extra'];
            if($add_extra =='y'){
                //新增
                $info = $this->getInfoById($id);
                $add_data['contract_no'] = $info['contract_no'];
                $add_data['customer_name'] = $info['customer_name'];
                $add_data['create_time'] = date('Y-m-d H:i:s');
                $add_data['is_deleted'] = 'N';
                $this->baseInsert(self::tableName(),$add_data);
            }else{
                $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$id]);

            }
        }else{
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            $this->baseInsert(self::tableName(),$add_data);
        }

        $new_content = $this->getInfoById($id) ;
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'CONTRACT_SALES',
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
            $list[$k]['chanpin_bie'] = $params_obj->getDetailById($v['chanpin_bie']);
            $list[$k]['paihao'] = $params_obj->getDetailById($v['paihao']);
            $list[$k]['product_form'] = $params_obj->getDetailById($v['product_form']);
            $list[$k]['jiaohzt'] = $params_obj->getDetailById($v['jiaohzt']);
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
        $total_key = ['danjia','dingdl','sjchl','kszl','yinskze','shijishk','skcy','jiaohzt'] ;
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
        $total_info['danjia'] = numberSprintf($total_info['danjia']/$total_num,2) ;
        return $total_info ;
    }
}
