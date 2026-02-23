<?php
/**
 * 普通售后
 */
namespace backend\components;

use common\models\AfterMarketTempParams;
use common\models\Areas;
use common\models\TaskWorkOrderLog;
use Yii ;
use common\models\PushTask;
use common\models\AfterMarketDoc;
use common\models\OrderGoods ;
use common\models\Order ;
use common\models\Member;

class PushTaskAftermarket extends PushTaskCommon
{
    //基础售后任务
    public $base_aftermarket_task_type = 'BASE_AFTERMARKET';

    //真实的售后任务
    public $real_aftermarket_task_type = 'REAL_AFTERMARKET';
    
    /**
     * 推送第一批次基础任务
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushBaseTask($push_task_status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList($this->base_aftermarket_task_type, $push_task_status);

        if(!$push_task_list){
            return false;
        }
        
        //根据任务列表获取对应售后表的信息
        $doc_obj = new  AfterMarketDoc();
        $doc_fields = '*';
        $doc_list = $doc_obj->getListByTaskList($push_task_list,$doc_fields);
        if(!$doc_list){
            return false ;
        }

        $task_work_order_log_obj = new  TaskWorkOrderLog() ;
        $push_task_obj = new PushTask();

        foreach($doc_list as $task_id=>$v){

            //申请售后第一阶段执行结果
            $after_market_doc_id = $v['id'];
            $apply_info = $v ;
            $post_data = $apply_info ;
            $first_rst = $this->doApplyFirstForNew($after_market_doc_id,$apply_info,$post_data);
            if($first_rst['code']!=1){

                //step4.1 不符合售后条件并给出对应日志信息  售后关闭
                $aftermarket_doc_update_data = $doc_obj->getApplyFailUpdateData($after_market_doc_id,$first_rst);
                $doc_obj->baseUpdate('sdb_after_market_doc',$aftermarket_doc_update_data,"id = :id", array(":id" => $after_market_doc_id));

                if($first_rst['code'] =='603005'){
                    //step4.2 以旧换新退款申请失败 需要直接将退款申请任务关闭
                    $doc_obj->baseUpdate('sdb_push_task', [ 'status' => 'PUSHED', 'modify_time' => date("Y-m-d H:i:s")], "id = :id", array(":id" => $task_id));
                }else{
                    //step4.2 更新任务推送表的状态
                    $doc_obj->baseUpdate('sdb_push_task', [ 'status' => 'FAILED', 'modify_time' => date("Y-m-d H:i:s")], "id = :id", array(":id" => $task_id));
                }

                //step4.3 更新order_goods_single表信息
                $doc_obj->baseUpdate('sdb_order_goods_single',['amd_id'=>0,'after_market_status'=>0],'amd_id =:amd_id',[":amd_id"=>$after_market_doc_id]);

                //step4.4 记录日志
                $log_data['push_task_id'] = $task_id;
                $log_data['request_data'] = json_encode($v);
                $log_data['response_data'] = json_encode($first_rst);
                $log_data['create_time'] = date('Y-m-d H:i:s');
                $log_data['modify_time'] = date('Y-m-d H:i:s');
                $task_work_order_log_obj->baseInsert('task_work_order_log',$log_data,'db_log');
                continue ;
            }

            //预售退定金的时候不走截单
            $order_info = $first_rst['data']['data']['order_info'];

            $presell_cancel_rst = true ;
            if($order_info['type'] ==6 && $order_info['pay_status']==2){
                $presell_cancel_rst = false ;
            }
            if($apply_info['after_market_type'] ==1 && $presell_cancel_rst){

                //step5.1 直接发送截单任务
                $order_task_obj = new OrderTask();
                $order_id = $apply_info['order_id'] ;
                $goods_id = $apply_info['goods_id'] ;
                $order_task_rst = $order_task_obj->addPushTask($order_id);
                if($order_task_rst){
                    //step5.2 创建截单任务成功
                    $push_task_obj->baseUpdate('sdb_push_task', ['status' => 'CLOSED', 'modify_time' => date("Y-m-d H:i:s")], "id = :id", array(":id" => $task_id));
                }

                //step5.3 记录日志
                $log_data['push_task_id'] = $task_id;
                $log_data['request_data'] = json_encode($v);
                $log_data['response_data'] = json_encode($first_rst);
                $log_data['create_time'] = date('Y-m-d H:i:s');
                $log_data['modify_time'] = date('Y-m-d H:i:s');
                $task_work_order_log_obj->baseInsert('task_work_order_log',$log_data,'db_log');

                continue;

            }

            $second_rst = $this->doApplySecondForNewTask($apply_info);

            if($second_rst['code']!=1){
                //step6.1 需要关闭售后
                $aftermarket_doc_update_data = $doc_obj->getApplyCloseUpdateData($after_market_doc_id,$second_rst);
                $doc_obj->baseUpdate('sdb_after_market_doc',$aftermarket_doc_update_data,"id = :id", array(":id" => $after_market_doc_id));

                //step6.2 关闭任务
                $push_task_obj->baseUpdate('sdb_push_task', ['status' => 'FAILED', 'modify_time' => date("Y-m-d H:i:s")], "id = :id", array(":id" => $task_id));

                //step6.3 记录日志
                $log_data['push_task_id'] = $task_id;
                $log_data['request_data'] = json_encode($v);
                $log_data['response_data'] = json_encode($second_rst);
                $log_data['create_time'] = date('Y-m-d H:i:s');
                $log_data['modify_time'] = date('Y-m-d H:i:s');
                $task_work_order_log_obj->baseInsert('task_work_order_log',$log_data,'db_log');
                continue ;
            }

            //step7 sdb_after_market_temp_params标记为已处理
            $temp_params_obj = new AfterMarketTempParams();
            $temp_params_obj->baseUpdate('sdb_after_market_temp_params', ['status' => 'DEALED'], "after_market_doc_id = :after_market_doc_id", array(":after_market_doc_id" => $after_market_doc_id));

            //step8 将任务处理成已成功
            $push_task_obj->baseUpdate('sdb_push_task', ['status' => 'CLOSED', 'modify_time' => date("Y-m-d H:i:s")], "id = :id", array(":id" => $task_id));

            //step9 记录日志
            $log_data['push_task_id'] = $task_id;
            $log_data['request_data'] = json_encode($v);
            $log_data['response_data'] = json_encode($second_rst);
            $log_data['create_time'] = date('Y-m-d H:i:s');
            $log_data['modify_time'] = date('Y-m-d H:i:s');
            $task_work_order_log_obj->baseInsert('task_work_order_log',$log_data,'db_log');
        }

    }


    /**
     * 申请售后第一步
     * @param integer $after_market_doc_id 售后单ID
     * @param array $apply_info 申请售后的信息
     * @param array $post_data  后台用户提交的表单数据
     * @return array
     * Note:剩余部分要截单成功之后才进行
     */
    public function doApplyFirstForNew($after_market_doc_id,$apply_info=array(),$post_data)
    {

        $rst['code'] = 1;
        $rst['data'] = array();

        $after_market_type = $apply_info['after_market_type'];
        $diff_amount = $apply_info['refund_price'];
        //step1.1 售后金额不正确
        if ($after_market_type == 5 && !$diff_amount) {
            $rst['code'] = '400016';
            return $rst;
        }

        //step1.2 售后金额不正确
        if (($after_market_type == 1 || $after_market_type == 2) && !$diff_amount) {
            $rst['code'] = '400026';
            return $rst;
        }

        //step1.3售后申请原因不能为空
        $request_content = $apply_info['request_content'];
        if (!$request_content) {
            $rst['code'] = '400027';
            return $rst;
        }
        //step1.4 退款方式不能为空
        $payment_class_name = $apply_info['payment_class_name'];
        if($apply_info['original_road_refund'] !='ALL'){
            if (in_array($after_market_type, array(1, 2, 5)) && !$payment_class_name) {
                $rst['code'] = '400028';
                return $rst;
            }
        }
        //step1.5  银行和支付宝的对应传递参数是否正确
        if ($payment_class_name) {
            if ($payment_class_name == 'bank_pay') {
                $cash_user = $apply_info['cash_user'];
                $refund_accountBank = $apply_info['refund_accountBank'];
                $cash_account = $apply_info['cash_account'];
                $bankDistrict = $apply_info['bankDistrict'];
                $subBank = $apply_info['subBank'];
                if (!$cash_user || !$refund_accountBank || !$cash_account || !$bankDistrict || !$subBank) {
                    $rst['code'] = '400029';
                    return $rst;
                }
            }

            if ($payment_class_name == 'direct_alipay') {
                $cash_user = $apply_info['cash_user'];
                $cash_account = $apply_info['cash_account'];
                if (!$cash_user || !$cash_account) {
                    $rst['code'] = '400030';
                    return $rst;
                }
            }
        }

        //step2 判断其他传递参数是否正确
        $order_id = $apply_info['order_id'];
        $goods_id = $apply_info['goods_id'];
        $product_id = $apply_info['product_id'];
        $goods_nums = $apply_info['goods_nums'];
        $contact = $apply_info['contact'];
        if ($after_market_type != 5 && (!$order_id || (!$goods_id && !$product_id) || !$after_market_type || !$goods_nums || !$contact)) {
            $rst['code'] = '400017';
            return $rst;
        }

        //step3 获取订单商品的售后详细信息
        $order_goods_obj = new OrderGoods();
        //任务的请求需要排除是否重复提交售后的判断
        $is_task = true;
        $data = $order_goods_obj->getOrderGoodsAfterMarketDetail($order_id, $goods_id, $product_id,$is_task);
        if ($data['code'] != 1) {
            $rst['code'] = $data['code'];
            return $rst;
        }

        //step4 截取以旧换新申请单 只有退款类型采取需要截单
        if($after_market_type ==1){

            $doc_model = new AfterMarketDoc();
            //任务的请求需要排除是否重复提交售后的判断
            $cancel_trade_in_apply_rst = $doc_model->cancelTradeInApply($order_id);
            if (!$cancel_trade_in_apply_rst) {
                $rst['code'] = '603005';
                return $rst;
            }
        }

        //step5 在退货退款的情况下 要先截单 截单完成才能进行退货退款
        //step5.1保存用户传递的数据存入临时售后关联表
        //走不到截单操作会直接
        $order_info = $data['data']['order_info'];

        $temp_params_obj = new AfterMarketTempParams();
        $temp_params_add_data['after_market_doc_id'] = $after_market_doc_id;
        $temp_params_add_data['order_id'] = $order_id;
        $temp_params_add_data['goods_id'] = $goods_id;

        $temp_params['apply_info'] = $apply_info;
        $temp_params['post_data'] = $post_data;
        $temp_params['data'] = $data;
        $temp_params_add_data['params'] = json_encode($temp_params);
        $temp_params_add_data['status'] = 'UNDEAL';
        $temp_params_add_data['order_no'] = $order_info['order_no'];
        $temp_params_add_data['create_time'] = date('Y-m-d H:i:s');
        $temp_params_add_data['update_time'] = date('Y-m-d H:i:s');
        $temp_params_insert_id = $temp_params_obj->baseInsert('sdb_after_market_temp_params', $temp_params_add_data);

        if (!$temp_params_insert_id) {
            $rst['code'] = '400031';
            return $rst;
        }

        $rst['data'] = $data ;
        return $rst ;

    }


    /**
     * 执行申请售后的第二阶段操作
     * @param $apply_info
     * @return array
     */
    public function doApplySecondForNewTask($apply_info){

        $rst['code'] = 1 ;
        $rst['msg'] = '' ;

        $order_id = $apply_info['order_id'];
        $goods_id = $apply_info['goods_id'];
        $product_id = $apply_info['product_id'];

        $after_market_type = $apply_info['after_market_type'];

        $transaction = Yii::$app->db->beginTransaction();
        try{
            //step2 插入售后单信息
            $aftermarket_doc_id = $apply_info['id'] ;

            //step3 返回订单商品的更新数据
            $order_goods_obj = new OrderGoods();
            $deal_aftermarket_obj = new DealAftermarket();
            $update_order_goods_data = $deal_aftermarket_obj->getOrderGoodsUpdateData($after_market_type);
            if($update_order_goods_data) {
                $update_order_goods_where_str = 'order_id =:order_id and goods_id =:goods_id and product_id=:product_id';
                $update_order_goods_where_arr[':order_id'] = $order_id;
                $update_order_goods_where_arr[':goods_id'] = $goods_id;
                $update_order_goods_where_arr[':product_id'] = $product_id;
                $order_goods_obj->baseUpdate("sdb_order_goods", $update_order_goods_data, $update_order_goods_where_str, $update_order_goods_where_arr);
            }

            //step5 创建计划任务表
            $push_task_obj = new PushTask() ;
            $task_data['business_id'] = $aftermarket_doc_id ;
            $task_data['business_type'] = $this->real_aftermarket_task_type ;
            $task_data['status'] = 'NOPUSH' ;
            $task_data['push_url'] = $this->getCreateAsmOrderUrl() ;
            $task_data['asm_order_no'] = '' ;
            $task_data['create_time'] = date('Y-m-d H:i:s');
            $task_data['modify_time'] = date('Y-m-d H:i:s');
            $push_task_id = $push_task_obj->baseInsert('sdb_push_task',$task_data);

            $rst['data']['aftermarket_doc_id'] = $aftermarket_doc_id ;
            $rst['data']['push_task_id'] = $push_task_id ;
            $transaction->commit();
            return $rst ;

        }catch (\Exception $e){

            $transaction->rollBack();
            $rst['code'] = '400019';

            return $rst;
        }
    }

    /**
     * 售后推送任务
     * @param  string $status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList($this->real_aftermarket_task_type, $status);
        if(!$push_task_list){
            return false;
        }

        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);

        /*** 业务相关数据(需要根据不同的业务而定) start ***/
        $aftermarket_model = new AfterMarketDoc();
        $aftermarket_push_task_list = $aftermarket_model->getListByIds($business_ids);
        if(!$aftermarket_push_task_list){
            return false;
        }
        /*** 业务相关数据 end ***/

        // 数据处理 追加 push_task_id,push_url 字段 (不用动)
        foreach($push_task_list as $row){
            if(isset($aftermarket_push_task_list[$row['business_id']])){
                $aftermarket_push_task_list[$row['business_id']]['push_task_id'] = $row['id'];
                $aftermarket_push_task_list[$row['business_id']]['push_url'] = $row['push_url'];
            }
        }

        // 循环推送售后信息到售后中心 (不用动)
        foreach($aftermarket_push_task_list as $row){
            $this->pushDataToAsm($row);
        }
    }

    /**
    * 发送售后单到售后中心
    * @param  array $request_data 请求信息
    * @return bool
    */
    public function pushDataToAsm($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];

        $push_task_model = new PushTask();
        $now_time = date("Y-m-d H:i:s");

        // 推送相关数据处理
        $push_data = $this->getPushData($request_data);

        //获取任务基本信息
        $push_row_info = $push_task_model->findOneByWhere('sdb_push_task', array("cond" => "id = :id", "args" => array(":id" => $push_task_id)));
        $after_market_doc_id = $push_row_info['business_id'] ;

        //获取售后doc 表信息
        $doc_model = new AfterMarketDoc();
        $doc_info = $doc_model->findOneByWhere('sdb_after_market_doc', array("fields"=>'order_id',"cond" => "id = :id", "args" => array(":id" => $after_market_doc_id)));
        $order_id = $doc_info?$doc_info['order_id']:0 ;

        //获取订单信息
        $order_model = new Order();
        $order_info = $order_model->findOneByWhere('sdb_order', array("cond" => "id = :id", "args" => array(":id" => $order_id)));

        // 新增send_to_asm_log日志信息
        $send_asm_obj = new SendToAsm();
        $log_id = $send_asm_obj::createOmsAfterMarketLog($after_market_doc_id,$order_info,'send');

        // 发送售后信息到中台
        $response_data = $this->pushDataToAsmCommon($request_data, $push_data);
        if(!$response_data){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新任务为已推送
            //售后工单号
            $oms_data = $response_data['data'] ;
            $asmOrderNo = $oms_data['asmOrderNo'];
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED', 'asm_order_no' => $asmOrderNo, 'modify_time' => $now_time]);

            //更新send_to_asm_log信息
            $log_update_data['status'] = 'unlock';
            $log_update_data['asmOrderNo'] = $asmOrderNo;
            $log_where_str = ' id=:id';
            $log_where_arr[":id"] = $log_id;
            $push_task_model->baseUpdate('sdb_send_oms_after_market_log',$log_update_data,$log_where_str,$log_where_arr);


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
     * 获取推送的维修数据
     * @param  array   $request_data
     * @return array
     */
    private function getPushData($request_data){

        // 售后编号（维修）
        $after_market_type = $request_data['after_market_type'];
        $type_arr = $this->type_arr ;
        // 工单类型
        $order_type = $type_arr[$after_market_type];


        //获取客户名称
        $user_id = $request_data['user_id'] ;
        $member_obj = new Member();
        $user_info = $member_obj->getUserInfoById($user_id);
        $username  = $user_info['username'];

        //获取售后对应的订单信息
        $order_model = new Order();
        $order_where_arr = ['id'=>$request_data['order_id']];
        $order_info = $order_model->getRowInfoByWhere($order_where_arr);

        $problemDesc = $request_data['request_content'];//官网后台输入的问题描述
        $extra_problem_desc = $this->getProblemDesc($request_data,$order_type,$order_info);//拼接额外的问题描述
        $problemDesc  = $problemDesc.$extra_problem_desc ;
        $rst['shopNo'] = $this->asmShopNo;
        $rst['originalOrderNo'] = $request_data['order_no'];
        $rst['problemDesc'] = $problemDesc;//问题描述
        $rst['customerNick'] = $username;//客户名称 对应user表的username
        $rst['customerName'] = $request_data['contact_user'];//顾客姓名
        $rst['customerPhone'] = $request_data['contact'];//顾客手机
        $rst['orderType'] = $order_type;//工单类型
        $rst['callBackNotifyUrl'] = $this->getWorkOrderCallbackUrlForNew();//回调地址 工单完成关闭
        $rst['validateCode'] = $this->createValidateCode($request_data);//发往中台唯一标识符
        $rst['orderSource'] = 'asm_order_source_ecovacs';
        $rst['problemTypeStr'] = $this->getAftermarketProblemTypeStr($order_type,$request_data);//传问题类型，非必填

        //新增REPAIR所需字段
        $request_data['province'] = $order_info['province'];
        $request_data['city'] = $order_info['city'];
        $request_data['area'] = $order_info['area'];
        $request_data['username'] =$username;
        $request_data['buy_realname'] =$order_info['accept_name'];
        $request_data['mobile'] =$order_info['mobile'];
        $request_data['address'] =$order_info['address'];

        //读取商品名称和物料号 均是读取快照信息
        $order_id = $request_data['order_id'] ;
        $goods_id = $request_data['goods_id'] ;
        $order_goods_obj = new OrderGoods();
        $order_goods_where['order_id'] = $order_id;
        $order_goods_where['goods_id'] = $goods_id;
        $order_goods_return_field = 'goods_array';
        $goods_info = $order_goods_obj->getRowInfoByWhere($order_goods_where,$order_goods_return_field);
        $tmp_info    =json_decode($goods_info['goods_array'],true);
        $productName =isset($tmp_info['name'])?$tmp_info['name']:'';
        $materialNo  =isset($tmp_info['ecovacs_goods_no'])?$tmp_info['ecovacs_goods_no']:'';
        $request_data['product_model'] =$productName;//产品名称
        $request_data['material_no'] =$materialNo;//物料号


        $rst = $this->formateOmsDataByAftermarketType($rst,$request_data,$order_info,'common') ;
        $rst = $this->formateOmsFileInfo($rst,$request_data);

        return $rst ;
    }

    /**
     *获取退货的时候详细问题描述
     * @param  array   $after_market_info 售后信息
     * @param  string  $order_type        售后类型
     * @param  array   $order_info        订单信息
     * @return string
     */
    private function getProblemDesc($after_market_info=array(),$order_type,$order_info){

        $rst = '';
        if($after_market_info){

            //step1 查询指定订单商品表信息
            $order_id = $after_market_info['order_id'] ;
            $goods_id = $after_market_info['goods_id'] ;
            $order_goods_obj = new OrderGoods();
            $order_goods_where['order_id'] = $order_id;
            $order_goods_where['goods_id'] = $goods_id;
            $order_goods_return_field = '*';
            $goods_info = $order_goods_obj->getRowInfoByWhere($order_goods_where,$order_goods_return_field);

            //step1.1 获取售后类型中文释义
            $orderTypeStr = $this->getAftermarketTypeStr($order_type,$after_market_info);

            //step2 读取文件名和物料号 均是读取快照信息
            $tmp_info    =json_decode($goods_info['goods_array'],true);
            $productName =isset($tmp_info['name'])?$tmp_info['name']:'';
            $materialNo  =isset($tmp_info['ecovacs_goods_no'])?$tmp_info['ecovacs_goods_no']:'';

            //step3获取主品对应的成交价
            $price  ='';
            if($order_type =='RECEIVE_REFUND' || $order_type =='REFUND' ){
                //推送给中台实际的成交价
                $price = $after_market_info['refund_price'];

            }

            //step3.1收货地址
            $area_model = new Areas();
            $order_province =$area_model->getAreaName($order_info['province']);
            $order_city =$area_model->getAreaName($order_info['city']);
            $order_area =$area_model->getAreaName($order_info['area']);
            $rst .= '【收货地址】'.$order_province.$order_city.$order_area.$order_info['address'].'，';

            //step4.1 存在运费
            if($after_market_info['after_market_type']==1 && $after_market_info['refund_freight']){
                $rst .='【退货款:'.$after_market_info['refund_price'].', 退运费'.$after_market_info['refund_freight'].'】' ;
            }

            //step4 拼接主品的商品信息
            $rst .='【商品编码】'.$materialNo.'，【商品名称】'.$productName.'，【数量】'.$after_market_info['goods_nums'].'，【售后类型】'.$orderTypeStr.'，【金额】'.$price;

            //step5 拼接主品对应赠品信息
            $giveaway_params = ['cond'=>'order_id = :order_id and main_goods_id = :main_goods_id','args'=>[':order_id'=>$order_id,':main_goods_id'=>$goods_id],'fields'=>'*'];
            $giveaway_goods_list = $order_goods_obj->findAllByWhere('sdb_giveaway_activity_order',$giveaway_params);
            if($giveaway_goods_list){
                foreach($giveaway_goods_list as $v){

                    $giveaway_goods_id = $v['giveaway_goods_id'];
                    $giveaway_order_goods_params['cond']='order_id =:order_id and goods_id=:goods_id';
                    $giveaway_order_goods_params['args']=[":order_id"=>$order_id,':goods_id'=>$giveaway_goods_id];
                    $giveaway_order_goods_params['fields']='goods_array,global_no';
                    $giveaway_order_goods = $order_goods_obj->findOneByWhere('sdb_order_goods',$giveaway_order_goods_params);
                    $tmp_info    =json_decode($giveaway_order_goods['goods_array'],true);
                    $productName =isset($tmp_info['name'])?$tmp_info['name']:'';
                    $materialNo  =isset($tmp_info['ecovacs_goods_no'])?$tmp_info['ecovacs_goods_no']:'';
                    $all_giveaway_num = $v['giveaway_num']*$after_market_info['goods_nums'] ;
                    $rst .='【商品编码】'.$materialNo.'，【商品名称】'.$productName.'，【数量】'.$all_giveaway_num.'，【售后类型】'.$orderTypeStr.'，【金额】';

                }
            }

            //step5 判断物流信息
            $doc_info_info_params['cond'] = ' after_market_doc_id = :after_market_doc_id';
            $doc_info_info_params['args'] = [':after_market_doc_id'=>$after_market_info['id']];
            $doc_info_info  = $order_goods_obj->findOneByWhere('sdb_after_market_doc_info',$doc_info_info_params);

            //维修类型
            if($after_market_info['after_market_type'] ==4){
                if($doc_info_info){
                    //获取省市区名称

                    $province_name = $area_model->getAreaName($doc_info_info['network_province']);
                    $city_name = $area_model->getAreaName($doc_info_info['network_city']);
                    $area_name = $area_model->getAreaName($doc_info_info['network_area']);

                    $addr_str = $province_name.$city_name.$area_name.$doc_info_info['network_name'];
                    if($doc_info_info['repair_type']=='MAILING'){
                        $rst .= '【维修-寄修】'.$addr_str.'，';
                        $rst .= '【物流公司】'.$after_market_info['freight_name'].'【物流单号】'.$after_market_info['freight_code'];
                    }else{
                        $rst .= '【维修-送修】'.$addr_str;
                    }
                }
            }

            //退货换货
            if(in_array($after_market_info['after_market_type'],array(2,3))){
                if($doc_info_info){
                    $rst .= '【物流公司】'.$after_market_info['freight_name'].'【物流单号】'.$after_market_info['freight_code'];
                }
            }


            $order_amount = $order_info['order_amount'];
            $real_freight= $order_info['real_freight'];

            $rst .= '【沟通记录】'.$after_market_info['backend_audit_idea'];
            $rst .='【订单支付金额】'.$order_amount.'(含运费'.$real_freight.'元)';

            //拼接后台备注信息
            if($after_market_info['admin_note']){
                $admin_note_arr = json_decode($after_market_info['admin_note'],true);
                if($admin_note_arr){
                    $rst .='【联系记录】';
                    foreach($admin_note_arr as $v){

                        $rst.=$v['username'].'-'.$v['time'].'-'.$v['admin_note'].'；';
                    }
                }
            }

        }

        return $rst ;
    }
}
