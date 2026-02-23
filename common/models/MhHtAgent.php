<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_ht_agent".
 *
 * @property int $id
 * @property string $contract_no 合同编号
 * @property string $customer_name 客户名称
 * @property string $chanpin_bie 产品别
 * @property string $paihao 牌号
 * @property string $lailiao_xingtai 来料形态
 * @property string $lailiao_diameter 来料尺寸直径
 * @property string $lailiao_long 来料尺寸长度
 * @property string $jiagong_diameter 加工尺寸直径
 * @property string $jiagong_long 加工尺寸长度
 * @property string $dingdl 订单量
 * @property string $llzl 来料重量
 * @property string $jiagongdj 加工单价
 * @property string $jiagongje 加工金额
 * @property string $jdrq 接单日期
 * @property string $llrq 来料日期
 * @property string $htjq 合同交期
 * @property string $sjjq 实际交期
 * @property string $cpchl 成品出货量
 * @property string $canliaochl 残料出货量
 * @property string $wjgfhzl 未加工返回重量
 * @property string $ccl 成材率
 * @property string $fcl 返材率
 * @property string $jhzt 交货状态
 * @property string $fkzt 付款状态
 * @property string $chnf 出货年份
 * @property string $chyf 出货月份
 * @property string $note 备注
 * @property string $status ENABLED DISABLED
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhHtAgent extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_ht_agent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['contract_no', 'customer_name', 'chanpin_bie', 'paihao', 'lailiao_xingtai', 'lailiao_diameter', 'lailiao_long', 'jiagong_diameter', 'jiagong_long', 'dingdl', 'llzl', 'jiagongdj', 'jiagongje', 'jdrq', 'llrq', 'htjq', 'sjjq', 'cpchl', 'canliaochl', 'wjgfhzl', 'ccl', 'fcl', 'jhzt', 'fkzt', 'chnf', 'chyf', 'note', 'status'], 'string', 'max' => 255],
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
            'customer_name' => 'Customer Name',
            'chanpin_bie' => 'Chanpin Bie',
            'paihao' => 'Paihao',
            'lailiao_xingtai' => 'Lailiao Xingtai',
            'lailiao_diameter' => 'Lailiao Diameter',
            'lailiao_long' => 'Lailiao Long',
            'jiagong_diameter' => 'Jiagong Diameter',
            'jiagong_long' => 'Jiagong Long',
            'dingdl' => 'Dingdl',
            'llzl' => 'Llzl',
            'jiagongdj' => 'Jiagongdj',
            'jiagongje' => 'Jiagongje',
            'jdrq' => 'Jdrq',
            'llrq' => 'Llrq',
            'htjq' => 'Htjq',
            'sjjq' => 'Sjjq',
            'cpchl' => 'Cpchl',
            'canliaochl' => 'Canliaochl',
            'wjgfhzl' => 'Wjgfhzl',
            'ccl' => 'Ccl',
            'fcl' => 'Fcl',
            'jhzt' => 'Jhzt',
            'fkzt' => 'Fkzt',
            'chnf' => 'Chnf',
            'chyf' => 'Chyf',
            'note' => 'Note',
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
        $add_data['lailiao_xingtai'] = $post_data['lailiao_xingtai'] ;
        $add_data['jiagong_xingtai'] = $post_data['jiagong_xingtai'] ;
        $add_data['lailiao_diameter'] = $post_data['lailiao_diameter'] ;
        $add_data['lailiao_long'] = $post_data['lailiao_long'] ;
        $add_data['jiagong_diameter'] = $post_data['jiagong_diameter'] ;
        $add_data['jiagong_long'] = $post_data['jiagong_long'] ;
        $add_data['dingdl'] = $post_data['dingdl'] ;
        $add_data['dingdl_type'] = $post_data['dingdl_type'] ;
        $add_data['llzl'] = $post_data['llzl'] ;
        $add_data['llzl_type'] = $post_data['llzl_type'] ;
        $add_data['jiagongdj'] = $post_data['jiagongdj'] ;
        $add_data['jiagongje'] = $post_data['jiagongdj']*$post_data['llzl'] ;
        $add_data['jdrq'] = $post_data['jdrq'] ;
        $add_data['llrq'] = $post_data['llrq'] ;
        $add_data['htjq'] = $post_data['htjq'] ;
        $add_data['sjjq'] = $post_data['sjjq'] ;
        $add_data['cpchl'] = $post_data['cpchl'] ;
        $add_data['canliaochl'] = $post_data['canliaochl'] ;
        $add_data['wjgfhzl'] = $post_data['wjgfhzl'] ;

        $add_data['jhzt'] = $post_data['jhzt'] ;
        $add_data['fkzt'] = $post_data['fkzt'] ;
        $add_data['chnf'] = $post_data['chnf'] ;
        $add_data['chyf'] = $post_data['chyf'] ;
        $add_data['note'] = $post_data['note'] ;
        $add_data['kszl'] = $post_data['kszl'] ;

        // 成品出货量
        $cpchl = $add_data['cpchl'];
        $llzl = $add_data['llzl'];
        $wjgfhzl = $add_data['wjgfhzl'];

        $ccl = $llzl - $wjgfhzl ?  $cpchl/($llzl - $wjgfhzl) :0 ;
        $ccl = numberSprintf($ccl,4)*100;
        $add_data['ccl'] = $ccl ;

        $cpchl = $add_data['cpchl'];
        $fcl_total =  $cpchl + $cpchl ;

        $fcl = $llzl - $wjgfhzl ?  $fcl_total/($llzl - $wjgfhzl) :0 ;
        $fcl = numberSprintf($fcl,4)*100;
        $add_data['fcl'] = $fcl ;


        $add_data['status'] = $post_data['status'] ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;

        $contract_res = $this->checkContractNo($add_data['contract_no'],$id);
        if(!$contract_res){
            $this->setError('200044');
            return false ;
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
            'action' => 'CONTRACT_AGENT',
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
            $list[$k]['lailiao_xingtai'] = $params_obj->getDetailById($v['lailiao_xingtai']);
            $list[$k]['jiagong_xingtai'] = $params_obj->getDetailById($v['jiagong_xingtai']);
            $list[$k]['fkzt'] = $params_obj->getDetailById($v['fkzt']);
            $list[$k]['jhzt'] = $params_obj->getDetailById($v['jhzt']);
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
        $total_key = ['dingdl','llzl','kszl','jiagongdj','jiagongje','dingdl','cpchl','canliaochl','wjgfhzl','ccl','fcl'] ;
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
        $total_info['ccl'] = $total_info['ccl']/$total_num ;
        $total_info['fcl'] = $total_info['fcl']/$total_num ;
        return $total_info ;
    }
}
