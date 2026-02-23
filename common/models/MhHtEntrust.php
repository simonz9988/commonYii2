<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_ht_entrust".
 *
 * @property int $id
 * @property string $contract_no 合同编号
 * @property string $customer_name 客户名称
 * @property string $chanpin_bie 产品别
 * @property string $paihao 牌号
 * @property string $chuliao_xingtai 来料形态
 * @property string $chuliao_diameter 出料尺寸直径
 * @property string $chuliao_long 出料尺寸长度
 * @property string $weituo_diameter 委托加工尺寸直径
 * @property string $weituo_long 委托加工尺寸长度
 * @property string $dingdl 订单量
 * @property string $clzl 出料重量
 * @property string $jiagongdj 加工单价
 * @property string $jiagongje 加工金额
 * @property string $wwrq 委外日期
 * @property string $clrq 出料日期
 * @property string $htjq 合同交期
 * @property string $sjjq 实际交期
 * @property string $cphcl 成品回厂量
 * @property string $clhcl 残料回厂量
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
class MhHtEntrust extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_ht_entrust';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['contract_no', 'customer_name', 'chanpin_bie', 'paihao', 'chuliao_xingtai', 'chuliao_diameter', 'chuliao_long', 'weituo_diameter', 'weituo_long', 'dingdl', 'clzl', 'jiagongdj', 'jiagongje', 'wwrq', 'clrq', 'htjq', 'sjjq', 'cphcl', 'clhcl', 'wjgfhzl', 'ccl', 'fcl', 'jhzt', 'fkzt', 'chnf', 'chyf', 'note', 'status'], 'string', 'max' => 255],
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
            'chuliao_xingtai' => 'Chuliao Xingtai',
            'chuliao_diameter' => 'Chuliao Diameter',
            'chuliao_long' => 'Chuliao Long',
            'weituo_diameter' => 'Weituo Diameter',
            'weituo_long' => 'Weituo Long',
            'dingdl' => 'Dingdl',
            'clzl' => 'Clzl',
            'jiagongdj' => 'Jiagongdj',
            'jiagongje' => 'Jiagongje',
            'wwrq' => 'Wwrq',
            'clrq' => 'Clrq',
            'htjq' => 'Htjq',
            'sjjq' => 'Sjjq',
            'cphcl' => 'Cphcl',
            'clhcl' => 'Clhcl',
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
        $add_data['chuliao_xingtai'] = $post_data['chuliao_xingtai'] ;
        $add_data['chuliao_diameter'] = $post_data['chuliao_diameter'] ;
        $add_data['chuliao_long'] = $post_data['chuliao_long'] ;
        $add_data['weituo_diameter'] = $post_data['weituo_diameter'] ;
        $add_data['weituo_long'] = $post_data['weituo_long'] ;
        $add_data['weituo_zhuangtai'] = $post_data['weituo_zhuangtai'] ;
        $add_data['dingdl'] = $post_data['dingdl'] ;
        $add_data['dingdl_type'] = $post_data['dingdl_type'] ;
        $add_data['clzl'] = $post_data['clzl'] ;
        $add_data['clzl_type'] = $post_data['clzl_type'] ;
        $add_data['jiagongdj'] = $post_data['jiagongdj'] ;
        $add_data['jiagongje'] = floatval($post_data['jiagongdj'])*floatval($post_data['clzl']) ;
        $add_data['wwrq'] = $post_data['wwrq'] ;
        $add_data['clrq'] = $post_data['clrq'] ;
        $add_data['htjq'] = $post_data['htjq'] ;
        $add_data['sjjq'] = $post_data['sjjq'] ;
        $add_data['cphcl'] = $post_data['cphcl'] ;
        $add_data['clhcl'] = $post_data['clhcl'] ;
        $add_data['wjgfhzl'] = $post_data['wjgfhzl'] ;
         // 成材率  = 成品回厂量（kg）/ 出料重量
        $add_data['ccl'] = $add_data['clzl'] > 0 ? numberSprintf($add_data['clhcl']/$post_data['clzl'],4)*100 : 0  ;

        // 返材率 =  成品回厂量（kg）+残料回厂量 / 出料重量
        $add_data['fcl'] = $add_data['clzl'] > 0 ? numberSprintf(($add_data['clhcl']+$add_data['clhcl'])/$post_data['clzl'],4)*100 : 0  ;
        $add_data['jhzt'] = $post_data['jhzt'] ;
        $add_data['fkzt'] = $post_data['fkzt'] ;
        $add_data['chnf'] = $post_data['chnf'] ;
        $add_data['chyf'] = $post_data['chyf'] ;
        $add_data['kszl'] = $post_data['kszl'] ;

        $add_data['note'] = $post_data['note'] ;
        $add_data['status'] = $post_data['status'] ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;

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
            'action' => 'CONTRACT_ENTRUST',
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
            $list[$k]['chuliao_xingtai'] = $params_obj->getDetailById($v['chuliao_xingtai']);
            $list[$k]['weituo_zhuangtai'] = $params_obj->getDetailById($v['weituo_zhuangtai']);
            $list[$k]['jhzt'] = $params_obj->getDetailById($v['jhzt']);
            $list[$k]['fkzt'] = $params_obj->getDetailById($v['fkzt']);
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
        $total_key = ['dingdl','clzl','jiagongdj','jiagongje','cphcl','clhcl','wjgfhzl','ccl','fcl','jhzt','fkzt','kszl'] ;
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
        $total_info['jiagongdj'] = $total_info['jiagongdj']/$total_num ;
        $total_info['ccl'] = $total_info['ccl']/$total_num ;
        $total_info['fcl'] = $total_info['fcl']/$total_num ;
        return $total_info ;
    }
}
