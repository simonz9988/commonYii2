<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_ht_profit_calculation".
 *
 * @property int $id
 * @property string $contract_no 合同编号
 * @property string $cpsr 成品收入
 * @property string $gzpsr 改制品收入
 * @property string $flsr 废料收入
 * @property string $ylfy 原料费用
 * @property string $wxjgfy 外协加工费用
 * @property string $ysfy 运输费用
 * @property string $scfy 生产费用
 * @property string $ywfy 业务费用
 * @property string $glfy 管理费用
 * @property string $ksfy 客诉费用
 * @property string $ccl 成材率（以流转卡为准）
 * @property string $note 备注
 * @property string $status ENABLED DISABLED
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhHtProfitCalculation extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_ht_profit_calculation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['contract_no', 'cpsr', 'gzpsr', 'flsr', 'ylfy', 'wxjgfy', 'ysfy', 'scfy', 'ywfy', 'glfy', 'ksfy', 'ccl', 'note', 'status'], 'string', 'max' => 255],
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
            'cpsr' => 'Cpsr',
            'gzpsr' => 'Gzpsr',
            'flsr' => 'Flsr',
            'ylfy' => 'Ylfy',
            'wxjgfy' => 'Wxjgfy',
            'ysfy' => 'Ysfy',
            'scfy' => 'Scfy',
            'ywfy' => 'Ywfy',
            'glfy' => 'Glfy',
            'ksfy' => 'Ksfy',
            'ccl' => 'Ccl',
            'note' => 'Note',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回指定行信息
     * @param $id
     * @param string $fields
     * @return mixed
     */
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
        $add_data['cpsr'] = $post_data['cpsr'] ;
        $add_data['gzpsr'] = $post_data['gzpsr'] ;
        $add_data['flsr'] = $post_data['flsr'] ;
        $add_data['ylfy'] = $post_data['ylfy'] ;
        $add_data['wxjgfy'] = $post_data['wxjgfy'] ;
        $add_data['ysfy'] = $post_data['ysfy'] ;
        $add_data['scfy'] = $post_data['scfy'] ;
        $add_data['ywfy'] = $post_data['ywfy'] ;
        $add_data['glfy'] = $post_data['glfy'] ;
        $add_data['ksfy'] = $post_data['ksfy'] ;
        $add_data['ccl'] = $post_data['ccl'] ;
        $add_data['note'] = $post_data['note'] ;
        $add_data['status'] = $post_data['status'] ;
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
            'action' => 'CONTRACT_PROFIT',
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
     * 格式化相关数据
     * @param $list
     * @return mixed
     */
    public function formatList($list){

        if(!$list){
            return  [] ;
        }

        foreach($list as $k=>$v){
            $cpsr = floatval($v['cpsr']);
            $flsr = floatval($v['flsr']);
            $ylfy = floatval($v['ylfy']);
            $wxjgfy = floatval($v['wxjgfy']);
            $ysfy = floatval($v['ysfy']);
            $scfy = floatval($v['scfy']);
            $ywfy = floatval($v['ywfy']);
            $glfy = floatval($v['glfy']);
            $ksfy = floatval($v['ksfy']);

            $maoli = $cpsr+$flsr-$ylfy ;
            $list[$k]['maoli'] = $maoli ;
            $list[$k]['maolilv'] = $cpsr+$flsr > 0 ? numberSprintf($maoli/$cpsr+$flsr,2) : 0  ;

            //$jingli =  $cpsr + $flsr - $wxjgfy - $scfy -  $ysfy - $ywfy - $glfy - $ksfy ;
            $jingli =  $cpsr + $flsr - $wxjgfy - $scfy -  $ysfy  - $ksfy ;
            $list[$k]['jingli'] = $jingli ;
            $list[$k]['jinglilv'] = $cpsr+$flsr > 0 ? numberSprintf($jingli/$cpsr+$flsr,2) : 0  ;
        }

        return $list ;
    }

    /**
     * 获取汇总信息
     * @param $list
     * @return mixed
     */
    public function getTotalInfo($list){
        if(!$list){
            return  [];
        }

        $total_info = [];
        foreach($list as $k=>$v){

            foreach($v as $k_v=>$v_v){
                if(isset($total_info[$k_v])){
                    $total_info[$k_v] += $v_v ;
                }else{
                    $total_info[$k_v] = $v_v ;
                }
            }

        }

        $total_num = count($list) ;
        $total_info['maolilv'] = numberSprintf($total_info['maolilv']/$total_num,2);
        $total_info['jinglilv'] = numberSprintf($total_info['jinglilv']/$total_num,2);
        return $total_info ;
    }
}
