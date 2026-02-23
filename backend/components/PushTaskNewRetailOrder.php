<?php

namespace backend\components;

use common\models\Member;
use common\models\Order;
use common\models\OrderGoods;
use Yii ;
use yii\base\Exception;
use yii\db\Expression;

use common\models\PushTask;
use backend\components\PushTaskCommon;


class PushTaskNewRetailOrder extends PushTaskCommon
{
    /**
     * 推送订单信息
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('NEW_RETAIL_ORDER', $status, 500);
        if(!$push_task_list){
            return false;
        }

        // 数据处理 追加 push_task_id,push_url 字段 (不用动)
        $order_push_task_list = [];
        foreach($push_task_list as $row){
            $order_push_task_list[$row['business_id']]['push_task_id'] = $row['id'];
            $order_push_task_list[$row['business_id']]['order_id'] = $row['business_id'];
            $order_push_task_list[$row['business_id']]['push_url'] = $this->getPushStoreOrderUrl();
        }

        // 循环推送门店
        $pushed_order_ids = [];
        foreach($order_push_task_list as $row){
            if(!in_array($row['order_id'],$pushed_order_ids)){

                $push_rst = $this->pushDataToStore($row);
                if($push_rst){
                    $push_task_id = $row['push_task_id'];
                    $pushed_order_ids[] = $row['order_id'];

                    $update_data = ['status'=>'CLOSED','modify_time'=>date('Y-m-d H:i:s')];
                    $update_where_str = 'id < :id AND business_id=:business_id AND business_type =:business_type AND status != :status1 AND  status != :status2 ';
                    $update_where_arr[':business_type'] = 'ORDER';
                    $update_where_arr[':business_id'] = $row['order_id'];
                    $update_where_arr[':status1'] = 'PUSHED';
                    $update_where_arr[':status2'] = 'CLOSED';
                    $update_where_arr[':id'] = $push_task_id;
                    $push_task_model->baseUpdate('sdb_push_task',$update_data,$update_where_str,$update_where_arr);
                }
            }else{
                continue ;
            }
        }
    }

    /**
     * 发送售后单到门店
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToStore($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];

        $push_task_model = new PushTask();

        $now_time = date("Y-m-d H:i:s");

        // 推送相关数据处理
        $order_id = $request_data['order_id'];
        $order_data = $this->prepareOrder($order_id);

        // 发送售后信息到中台
        $response_data = $this->pushDataToNewRetailCommon($request_data, $order_data);
        if(!$response_data){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新任务为已推送
            $push_url = $request_data['push_url'];
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED', 'push_url'=>$push_url,'modify_time' => $now_time]);

            // 提交事务
            $transaction->commit();

            return true;
        }catch (Exception $e) {

            //回滚事务
            $transaction->rollback();

            return false;
        }
    }

    /**
     * 准备订单数据
     * @param $order_id
     * @return array
     */
    private function prepareOrder($order_id){
        $data = [];

        $order_obj = new Order();
        $order_data = $order_obj->findOneByWhere('sdb_order',['cond'=>'id = :id','args'=>[':id'=>$order_id]]);

        //用户数据
        $user_obj = new Member();
        $user_data = $user_obj->findOneByWhere('sdb_user',['cond'=>'id = :id','args'=>[':id'=>$order_data['user_id']]]);

        $data['orderNo'] = $order_data['order_no'];
        $data['gwMemberNo'] = $user_data['member_no'];
        $data['orderTime'] = strtotime($order_data['create_time']) * 1000;
        $data['payAmount'] = floatval($order_data['order_amount']);

        $data['shippingType'] = $order_data['distribution']  == 1 ? 'EXPRESS' : 'STORE_SELF';
        $data['receiveProvinceId'] = $order_data['province'];
        $data['receiveCityId'] = $order_data['city'];
        $data['receiveDistrictId'] = $order_data['area'];
        $data['receiveAddress'] = $order_data['address'];
        $data['receiver'] = $order_data['accept_name'];
        $data['receivePhone'] = $order_data['mobile'];
        $data['discountAmount'] = floatval($order_data['promotions'] - $order_data['discount']);
        $data['otherCharges'] = floatval($order_data['real_freight']);

        $product_list = [];

        $order_goods_obj = new OrderGoods();
        $order_goods_data = $order_goods_obj->getEcNormalOrderInfo($order_id);
        if($order_goods_data){
            foreach($order_goods_data as $goods){
                $tmp = [];
                $tmp['materialNo'] = $goods['goods_array']['ecovacs_goods_no'];
                $tmp['count'] = intval($goods['goods_nums']);
                $tmp['sellPrice'] = floatval($goods['real_price']);
                $product_list[] = $tmp;
            }
        }
        $data['productList'] = $product_list;

        return $data;
    }
 
}
