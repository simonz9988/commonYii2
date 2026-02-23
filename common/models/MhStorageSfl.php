<?php

namespace common\models;

use common\components\LogOperate;
use Yii;

/**
 * This is the model class for table "sea_mh_storage_sfl".
 *
 * @property int $id
 * @property string $from_date 日期
 * @property string $ffcj 发料厂家
 * @property string $slcj 收料厂家
 * @property string $zcgx 制程工序
 * @property string $cpzt 产品状态
 * @property string $ylh 原炉号
 * @property string $xlh 新炉号
 * @property string $paihao 牌号
 * @property string $diameter 规格直径
 * @property string $long 规格长度
 * @property string $amount 数量
 * @property string $weight 重量
 * @property string $type 销售/代工
 * @property string $month 月份
 * @property string $bag_no 袋号
 * @property string $note 备注
 * @property string $status ENABLED DISABLED
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhStorageSfl extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_storage_sfl';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['from_date', 'ffcj', 'slcj', 'zcgx', 'cpzt', 'ylh', 'xlh', 'paihao', 'diameter', 'long', 'amount', 'weight', 'type', 'month', 'bag_no', 'note', 'status'], 'string', 'max' => 255],
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
            'from_date' => 'From Date',
            'ffcj' => 'Ffcj',
            'slcj' => 'Slcj',
            'zcgx' => 'Zcgx',
            'cpzt' => 'Cpzt',
            'ylh' => 'Ylh',
            'xlh' => 'Xlh',
            'paihao' => 'Paihao',
            'diameter' => 'Diameter',
            'long' => 'Long',
            'amount' => 'Amount',
            'weight' => 'Weight',
            'type' => 'Type',
            'month' => 'Month',
            'bag_no' => 'Bag No',
            'note' => 'Note',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @param string $fields
     * @return mixed
     */
    public function getInfoById($id, $fields = '*')
    {
        $params['cond'] = 'id=:id';
        $params['args'] = [':id' => $id];
        $params['fields'] = $fields;
        $info = $this->findOneByWhere(self::tableName(), $params, self::getDb());
        return $info;
    }

    /**
     * 保存数据
     * @param $id
     * @param $post_data
     * @return mixed
     */
    public function savePostData($id, $post_data)
    {

        $add_data['from_date'] = $post_data['from_date'];
        $add_data['from_date_timestamp'] = strtotime($post_data['from_date']);
        $add_data['ffcj'] = $post_data['ffcj'];
        $add_data['slcj'] = $post_data['slcj'];
        $add_data['zcgx'] = $post_data['zcgx'];
        $add_data['cpzt'] = $post_data['cpzt'];
        $add_data['ylh'] = $post_data['ylh'];
        $add_data['xlh'] = $post_data['xlh'];
        $add_data['paihao'] = $post_data['paihao'];
        $add_data['diameter'] = $post_data['diameter'];
        $add_data['long'] = $post_data['long'];
        $add_data['amount'] = $post_data['amount'];
        $add_data['weight'] = $post_data['weight'];
        $add_data['type'] = $post_data['type'];
        $add_data['forward'] = $post_data['forward'];
        $add_data['month'] = date('m', $add_data['from_date_timestamp']);

        $add_data['bag_no'] = $post_data['bag_no'];
        $add_data['note'] = $post_data['note'];
        $add_data['status'] = $post_data['status'];
        $add_data['modify_time'] = date('Y-m-d H:i:s');

        $old_content = $this->getInfoById($id) ;

        if ($id) {
            $this->baseUpdate(self::tableName(), $add_data, 'id=:id', [':id' => $id]);
        } else {
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            $this->baseInsert(self::tableName(), $add_data);
        }

        $new_content = $this->getInfoById($id) ;
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'STORAGE_SFL',
            'redundancy_id' => $id,
            'old_content' => $old_content,
            'new_content' => $new_content
        );

        // 日志操作
        $log_operate_obj = new LogOperate();
        $log_operate_obj->insert( $log_data);

        return true;
    }

    /**
     * 格式化处理列表信息
     * @param $list
     * @return  mixed
     */
    public function formatList($list)
    {
        if (!$list) {
            return [];
        }

        $params_obj = new MhHtParams();
        foreach ($list as $k => $v) {
            $list[$k]['type_id'] = $v['type'];
            $list[$k]['type'] = $params_obj->getDetailById($v['type']);
            $list[$k]['zcgx'] = $params_obj->getDetailById($v['zcgx']);
            $list[$k]['cpzt'] = $params_obj->getDetailById($v['cpzt']);


        }

        return $list;
    }

    /**
     * 格式化处理列表信息
     * @param $list
     * @return  mixed
     */
    public function formatTotalList($list)
    {
        if (!$list) {
            return [];
        }

        $params_obj = new MhHtParams();
        $total_obj = new MhStorageSflTotal();
        foreach ($list as $k => $v) {
            $list[$k]['type_id'] = $v['type'];
            $list[$k]['type'] = $params_obj->getDetailById($v['type']);
            $list[$k]['note'] = $total_obj->getNoteByFromDateAndType($v['from_date_timestamp'],$v['type']);

            // 来料数量和重量
            $in_total_info = $this->getTotalInfoByTypeAndFromDate($v['type'],$v['from_date_timestamp'],'IN');
            $list[$k]['in_total_amount'] = $in_total_info['total_amount'];
            $list[$k]['in_total_weight'] = $in_total_info['total_weight'];
            // 发货数量和重量
            $out_total_info = $this->getTotalInfoByTypeAndFromDate($v['type'],$v['from_date_timestamp'],'OUT');
            $list[$k]['out_total_amount'] = $out_total_info['total_amount'];
            $list[$k]['out_total_weight'] = $out_total_info['total_weight'];
        }
        return $list;
    }

    /**
     * 查询汇总信息
     * @param $type
     * @param $from_date_timestamp
     * @param $forward  IN/OUT
     * @return mixed
     */
    public function getTotalInfoByTypeAndFromDate($type,$from_date_timestamp,$forward){

        $params['cond'] = 'type=:type AND from_date_timestamp=:from_date_timestamp AND forward=:forward';
        $params['args'] = [':type'=>$type,':from_date_timestamp'=>$from_date_timestamp,':forward'=>$forward];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        $total_amount = 0 ;
        $total_weight = 0 ;
        if($list){
            foreach($list as $v){
                $total_amount += $v['amount'];
                $total_weight += $v['weight'];
            }
        }

        return compact('total_amount','total_weight');
    }

    public function getTotalWeightByCpxt($cpzt,$paihao){
        $params['cond'] = 'cpzt=:cpzt AND paihao=:paihao AND is_deleted=:is_deleted';
        $params['args'] = [':cpzt'=>$cpzt,':paihao'=>$paihao,':is_deleted'=>'N'];
        $params['fields'] = 'sum(weight) as sum_weight';
        $obj = new MhStorageSfl();
        $info = $obj->findOneByWhere($obj::tableName(),$params,$obj::getDb());
        $data = $info && !is_null($info['sum_weight']) ? $info['sum_weight'] : 0 ;
        return $data ;
    }

    /**
     * 创建新炉号
     */
    public function createNewLh(){
        $order_no = date("ymd");
        $order_no .= mt_rand(1000,9999);

        $params['cond'] = 'xlh =:xlh';
        $params['args'] = [':xlh'=>$order_no];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            return $this->createNewLh();
        }
        return $order_no ;

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
        $total_key = ['amount','weight'] ;
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


    public function getTotalTotalInfoByList($list){
        if(!$list){
            return [] ;
        }

        $total_info = [] ;
        $total_key = ['in_total_amount','in_total_weight','out_total_amount','out_total_weight'] ;
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
