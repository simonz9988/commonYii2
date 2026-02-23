<?php
namespace backend\components;


use common\models\Order;
use common\models\OrderFamilyEnergy;
use common\models\OrderPushTask;
use common\models\PushTask;
use common\models\TradeInApply;

class OrderTask
{
    /**
     * 添加推送任务
     * @param  integer $order_id
     * @param  boolean $check_order_type 是否需要验证订单类型（默认 false）14 为 以旧换新订单类型
     * @param  array   $order_info 订单信息
     * @return bool
     */
    public function addPushTask($order_id, $check_order_type = false, $order_info = array())
    {
        // 是否需要验证订单类型（以旧换新订单需要等收货完成后才能推送订单）
        if($check_order_type == true && $order_info['type'] == 14 ){

            //以旧换新订单 已收货 已完成允许添加订单任务
            $trade_in_apply_model = new TradeInApply();
            $params['cond'] = 'order_id =:order_id';
            $params['args'] =  [':order_id'=>$order_id];
            $params['fields'] = 'status';
            $trade_in_apply_info = $trade_in_apply_model->findOneByWhere('sdb_trade_in_apply',$params);
            $trade_in_status =  $trade_in_apply_info['status'];
            if(!in_array($trade_in_status,array('RECEIVED','FINISH','CANCEL'))){
                return true;
            }
        }
        
        $data['business_id'] = $order_id ;
        $data['business_type'] = 'ORDER';
        $data['status'] = 'NOPUSH';
        $push_task_common = new PushTaskCommon();
        $data['push_url'] = $push_task_common->getPushOrderUrl();
        $data['asm_order_no'] = '';
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['modify_time'] = date('Y-m-d H:i:s');

        $push_task_obj = new PushTask();
        $result = $push_task_obj->baseInsert('sdb_push_task',$data);
        return $result;
    }

    /**
     * 添加取消发货任务
     * @param $order_id
     * @return bool
     */
    public function addCancelDeliveryTask($order_id)
    {
        $order_model = new Order();
        $order_info = $order_model->getRowInfoByWhere(['id'=>$order_id],'order_no');
        $order_no = isset($order_info['order_no'])?$order_info['order_no']:'';

        $order_push_task_obj = new OrderPushTask();
        $data['order_id'] = $order_id;
        $data['order_no'] = $order_no;
        $data['params'] =  '';
        $data['url'] = '';
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['update_time'] = date("Y-m-d H:i:s");
        $data['push_count'] = 0;
        $data['status'] = 'NOTSTART';

        $result = $order_push_task_obj->baseInsert('sdb_order_push_task',$data);
        return $result;
    }

    /**
     * 添加取消同事圈能量的任务
     * @param $order_id
     * @return mixed
     */
    public function addCancelFamilyEnergy($order_no){

        // 查询能量消耗快照表信息
        $model = new OrderFamilyEnergy();
        $info = $model->getInfoByOrderNo($order_no) ;
        if(!$info){
            return true ;
        }

        $data['business_id'] = $info['energy_record_id'];
        $data['business_type'] = 'CANCEL_FAMILY_ENERGY';
        $data['status'] = 'NOPUSH';
        $data['push_url'] = CC_API_URL.'/shop-api/return-energy-for-order-cancel';
        $data['asm_order_no'] = '';
        $data['create_time'] = date("Y-m-d H:i:s");
        $data['modify_time'] =  date("Y-m-d H:i:s");

        $push_task_obj = new PushTask();
        $result = $push_task_obj->baseInsert('sdb_push_task',$data);
        return $result;

    }


}
