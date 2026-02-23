<?php
/**
 * 处理售后组件
 * author simon.zhang
 */
namespace backend\components;

use common\models\AfterMarketDoc;
use common\models\LogAftermarket;
use common\models\Member;
use common\models\Order;
use common\models\OrderGoodsSingle;
use common\models\SendOmsAfterMarketLog;
use common\models\TradeInAfterMarketDoc;
use common\models\TradeInSendOmsAftermarketLog;
use common\models\TuijianyouliVoucherCommission;
use common\models\VoucherRecord;
use Yii;
use common\models\OrderGoods ;
use yii\db\Query ;
use common\models\OrderPresell;
use common\models\AsmTask;
use common\models\AfterMarketTempParams;
use yii\db\Expression;
use backend\components\SendToAsm;
use common\models\PushTask;
use common\models\Repair;
use common\models\TaskWorkOrderCallbackLog;
use yii\base\Exception;


class DealAftermarket
{
    /**
     * 判断是否符合退款要求
     * @param array $order_info
     * @param array $goods_info
     * @return mixed
     */
    public static function checkAfterMarketType1($order_info=array(),$goods_info=array()){

        $rst['code'] = 1 ;
        $rst['msg'] = '';
        $rst['data'] = array();


        //待发货商品数量(已支付未发货商品)-退款成功数量>0
        $order_id = $order_info['id'];
        $goods_id = $goods_info['goods_id'];
        $order_goods_id = $goods_info['id'];


        //查询未发货商品状态
        //中台发货中和未发货的状态都属于未发货的商品
        $db = new Query();
        $query = $db->select("id")->from("sdb_order_goods_single");
        $where_str = " order_id = :order_id and goods_id = :goods_id and order_goods_id = :order_goods_id ";
        $where_str .= "and  send_status in(0,4) and after_market_status !=2 " ;
        $where_arr[':order_id'] = $order_id;
        $where_arr[':goods_id'] = $goods_id;
        $where_arr[':order_goods_id'] = $order_goods_id;
        $query->where($where_str,$where_arr);
        $not_send_num = $query->count();
        $final_num = $not_send_num ;

        //商品允许退款数量不符合要求
        if(!$final_num){
            $rst['code'] = '400013' ;
            return $rst ;
        }

        //判断预售尾款支付时间超期 下单后3个月才能进行操作
        $order_type = $order_info['type'];
        if($order_type==6){
            $order_presell_obj = new OrderPresell();
            $order_presell_where['order_id'] = $order_id ;
            $order_presell_return_field = 'balance_end_time';
            $presell_info = $order_presell_obj->getRowInfoByWhere($order_presell_where,$order_presell_return_field);
            $balance_end_time = $presell_info['balance_end_time'] ;
            $balance_end_time=strtotime ($balance_end_time);
            $now=time() ;
            $out_time=ceil(($now-$balance_end_time)/86400); //过期天数
            if($out_time>=90){
                $rst['code'] = '400014' ;
                return $rst ;
            }
        }

        $rst['data'] = array('final_num'=>$final_num) ;
        return $rst ;
    }

    //判断是否符合退货要求
    public static function checkAfterMarketType234($order_info=array(),$goods_info=array()){

        $rst['code'] = 1 ;
        $rst['msg'] = '';
        $rst['data'] = array();

        $order_id = $order_info['id'];
        $goods_id = $goods_info['goods_id'];
        $order_goods_id = $goods_info['id'];

        //虚拟商品退货，换货和维修商品数量为0
        if($goods_info['goods_type']==5){
            $rst['data'] = array('final_num'=>0) ;
            return $rst;
        }

        //已发货商品数量-退货成功数量>0
        //发货商品数量
        $db = new Query();
        $query = $db->select("id")->from("sdb_order_goods_single");
        $base_where_str = " order_id = :order_id and goods_id = :goods_id and order_goods_id = :order_goods_id ";
        $where_str = $base_where_str." and  send_status =1 " ;
        $where_arr[':order_id'] = $order_id;
        $where_arr[':goods_id'] = $goods_id;
        $where_arr[':order_goods_id'] = $order_goods_id;
        $query->where($where_str,$where_arr);
        $num1 = $query->count();

        //换货成功的
        $where_str2 = $base_where_str." and  send_status =5 and after_market_status=2";
        $query->where($where_str2,$where_arr);
        $num2 =$query->count();

        $final_num = $num1+$num2 ;

        if($final_num <=0){
            $rst['code'] = '400015' ;
            return $rst ;
        }

        $rst['data'] = array('final_num'=>$final_num) ;
        return $rst ;
    }


    /**
     * 判断退差价是否符合要求
     * @param  array  $order_info [description]
     * @param  array  $goods_info [description]
     * @return [type]             [description]
     */
    public function checkAfterMarketType5($order_info=array(),$goods_info=array()){

        $rst['code'] = 1 ;
        $rst['msg'] = '';
        $rst['data'] = array();

        //step1 判断是否发货，未发货的不能进行退差价
        $is_send = $goods_info['is_send'];
        if($is_send==0){
            $rst['code'] = '400011' ;
            $rst['msg'] = '未发货的不能进行退差价';
            return $rst;
        }

        if($order_info['status']!=5){
            $rst['code'] = '400045' ;
            $rst['msg'] = '已发货未确认订单的不能进行退差价';
            return $rst;
        }

        //step2 查询已经发货的商品的数量
        $order_id = $order_info['id'];
        $goods_id = $goods_info['goods_id'];
        $order_goods_id = $goods_info['id'];

        //step3 已发货商品数量-退款商品数量-退货商品数量>0
        //已经发货的商品数量 且排除退货完成的商品
        $db = new Query();
        $query = $db->select("id")->from("sdb_order_goods_single");
        $where_str = " order_id = :order_id and goods_id = :goods_id and order_goods_id = :order_goods_id ";
        $where_str .= " and (send_status = 1 or send_status =5 ) and send_status !=4 and send_status !=2" ;
        $where_arr[':order_id'] = $order_id;
        $where_arr[':goods_id'] = $goods_id;
        $where_arr[':order_goods_id'] = $order_goods_id;
        $query->where($where_str,$where_arr);
        $final_num = $query->count();

        //商品允许退差价数量不符合要求
        if(!$final_num){
            $rst['code'] = '400012' ;
            $rst['is_send_goods_num'] = 0 ;
            $rst['receive_goods_num'] = 0 ;
            $rst['refund_goods_num'] = 0 ;
            return $rst ;
        }

        $rst['data'] = array('final_num'=>$final_num) ;
        return $rst ;

    }

    /**
     * 获取当前订单已经退差总金额
     * @param $order_id
     * @return int
     */
    public function getRefundedPriceAll($order_id){
        $db = new Query();
        $query = $db->select(" sum(refund_price) as all_refund_price ")->from("sdb_after_market_doc");
        $where_str = "order_id = :order_id and after_market_type in(5,1,2) and  audit_status =1 and dispose_status in(1,2)";
        $where_arr[':order_id'] = $order_id;
        $query->where($where_str,$where_arr);
        $info = $query->one();

        $refunded_price = isset($info['all_refund_price']) ? $info['all_refund_price'] : 0;
        return $refunded_price ;
    }

    /**
     * /获取当前订单所有已经退差价完成的金额
     * @param  array   $order_info 订单信息
     * @param  integer $goods_id   商品ID
     * @return decimal
     */
    public function getRefundedAmountPrice($order_info=array(),$goods_id=0){

        $rst = 0 ;

        if($order_info && $goods_id){

            $order_id  = $order_info['id'];

            $db = new Query();
            $query = $db->select(" sum(refund_price) as all_refund_price ")->from("sdb_after_market_doc");
            $where_str = " order_id = :order_id and goods_id = :goods_id ";
            $where_str .= " and  after_market_type in(5,1,2) and  audit_status =1 " ;
            $where_str .=" and dispose_status in(1,2)";
            $where_arr[':order_id'] = $order_id;
            $where_arr[':goods_id'] = $goods_id;
            $query->where($where_str,$where_arr);
            $info = $query->one();

            $rst = isset($info['all_refund_price'])?$info['all_refund_price']:0;

        }

        return $rst ;
    }

    /**
     * /当前可以允许的最大的的退差价金额
     * @param  array  $order_info [description]
     * @param  array  $goods_info [description]
     * @return descimal
     */
    public function getAllowedRefundedNum($order_info=array(),$goods_info=array()){

        $rst = 0 ;

        $check_rst = $this->checkAfterMarketType5($order_info,$goods_info);
        if($check_rst['code']==1){
            $data = $check_rst['data'];
            //允许的数量
            $final_num = isset($data['final_num'])?$data['final_num']:0;

            $rst = $final_num ;
        }

        return $rst ;
    }


    /**
     * /当前可以允许的最大的的退差价金额
     * @param  array  $order_info [description]
     * @param  array  $goods_info [description]
     * @return descimal
     */
    public function getAllowedRefundedPrice($order_info=array(),$goods_info=array()){

        $rst = 0 ;

        $check_rst = self::checkAfterMarketType5($order_info,$goods_info);
        if($check_rst['code']==1){
            $data = $check_rst['data'];
            //允许的数量
            $final_num =  isset($goods_info['goods_nums'])?$goods_info['goods_nums']:0;
            //商品成本价
            $order_id = $order_info['id'];
            $goods_id = $goods_info['goods_id'];
            $order_goods_obj = new OrderGoods();

            $all_order_goods_params['cond'] = 'order_id=:order_id';
            $all_order_goods_params['args'] = [':order_id'=>$order_id];
            $all_order_goods_info = $order_goods_obj->findAllByWhere('sdb_order_goods',$all_order_goods_params);

            $goods_cost_price_info = $order_goods_obj->getGoodsCostPriceByGoodsId($order_info, $all_order_goods_info,$goods_id);
            $goods_cost_price = isset($goods_cost_price_info['price'])?$goods_cost_price_info['price']:0;
            $final_price = $final_num*$goods_cost_price;
            //已经退差价的总金额
            $refunedPrice = $this->getRefundedAmountPrice($order_info,$goods_id);

            $rst = $final_price - $refunedPrice ;
        }

        //保留2位小数
        $rst = round($rst,2);

        return $rst ;
    }


    /**
     * /当前可以允许退款商品的数量
     * @param  array  $order_info [description]
     * @param  array  $goods_info [description]
     * @return descimal
     */
    public function getAllowedRefundedGoodsNum($order_info=array(),$goods_info=array()){

        $rst = 0 ;

        $check_rst = $this->checkAfterMarketType1($order_info,$goods_info);
        if($check_rst['code']==1){
            $data = $check_rst['data'];
            //允许的数量
            $final_num = isset($data['final_num'])?$data['final_num']:0;
            $rst = $final_num ;
        }

        return $rst ;
    }

    /**
     * 获取可以允许的换货维修退货的商品数目
     * @param  array  $order_info [description]
     * @param  array  $goods_info [description]
     * @return num
     * Note:这三种使用的是同一个判断条件
     */
    public function getMaxExchangeReceiveRepaireGoodsNum($order_info=array(),$goods_info=array()){
        $rst = 0  ;
        $check_rst = $this->checkAfterMarketType234($order_info,$goods_info);

        if($check_rst['code']==1){
            $data = $check_rst['data'];
            $final_num = $data['final_num'];
            $rst = $final_num ;
        }

        return $rst ;
    }


    /**
     * 判断退款数量是否符合已经发货的数量要求
     * @param int $order_id
     * @param int $goods_id
     * @param int $goods_nums
     * @return bool
     * #TODO 此函数名暂用这个，等功能确定好需求再进行修改
     */
    public function dealGoodsIsSended($order_id=0,$goods_id=0,$goods_nums=0){

        if($order_id && $goods_id && $goods_nums){

            //step1 查询所有的未发货和中台发货中的商品
            $order_goods_single_obj = new OrderGoodsSingle();
            $num1 = $order_goods_single_obj->getCanSendCount($order_id,$goods_id);

            //step2 查询已经发货的数量
            $num2 = $order_goods_single_obj->getSendingCount($order_id,$goods_id);

            $num = $num1+$num2 ;
            if($num < $goods_nums){
                return false ;
            }


            #TODO 具体后续还要讨论
            //已发货商品退款操作才会出发截单操作
            //中台操作新框架已经取消
            /*
            //step3 查询中台发货中的发货单号
            $query = new IQuery("order_goods_single");
            $query->fields = "distinct delivery_id";
            $query->where = "order_id = $order_id and  send_status = 4 and goods_id = $goods_id and delivery_id is not null and delivery_id  !=  ''";
            $items = $query->find();
            $did = isset($items[0]['delivery_id'])?$items[0]['delivery_id']:0;
            if($did){

                $cancelgoods = array($did);
                Order_Class::addCancelDeliveryLog($order_id,$cancelgoods);
                $orderDB = new IModel("order");
                $orderDB->setData(array('lock_status'=>'locking'));
                $processingOrder = $orderDB->update( "id = {$order_id} and lock_status = 'unlock'");
                if(!$processingOrder){
                    return false ;
                }

                $error =  Order_Class::omsCancelSendDelivery($order_id,$cancelgoods);
                $orderDB->setData(array('lock_status'=>'unlock'));
                $orderDB->update( "id = {$order_id} and lock_status = 'locking'");

            }*/

            return true;
        }else{
            return false;
        }


    }

    /**
     * 根据用户申请的信息返回需要插入售后表的数据
     * @param array  $apply_info 申请信息
     * @param array  $order_info 订单信息
     * @param string $rnd_num    随机数
     * @return array
     */
    public function getAddAftermarketDocData($apply_info=array(),$order_info=array(),$rnd_num=''){

        $str = "`order_id`,`order_no`,`goods_id`,`product_id`,`user_id`,
        `request_content`,`contact`,`contact_user`,`goods_nums`,
        `create_time`,`after_market_type`,`refund_price`,`doc_log`
        ,`img_url`,`payment_class_name`,`cash_user`,`cash_account`,
        `refund_accountBank`,`subBank`,`bankDistrict`,`rnd_num`,`admin_id`";

        //当前时间
        $current_time = date('Y-m-d H:i:s');

        $add_data['order_id'] = $apply_info['order_id'] ;
        $add_data['order_no'] = $apply_info['order_no'] ;
        $add_data['goods_id'] = $apply_info['goods_id'] ;
        $add_data['product_id'] = $apply_info['product_id'] ;
        $add_data['user_id'] = $order_info['user_id'];
        $add_data['request_content'] = $apply_info['request_content'];
        $add_data['contact'] = $apply_info['contact'];
        $add_data['contact_user'] = $apply_info['contact_user'];
        $add_data['goods_nums'] = $apply_info['goods_nums'];
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['after_market_type'] = $apply_info['after_market_type'];
        $add_data['refund_price'] = $apply_info['refund_price'];

        $doc_log=array(
            0=>array('time'=>$current_time,'type'=>'发起售后申请','operater'=>'管理员'),
        );
        $add_data['doc_log'] = serialize($doc_log);
        $add_data['img_url'] = $apply_info['img_url'];
        $add_data['payment_class_name'] = $apply_info['payment_class_name'];
        $add_data['cash_user'] = $apply_info['cash_user'];
        $add_data['cash_account'] = $apply_info['cash_account'];
        $add_data['refund_accountBank'] = $apply_info['refund_accountBank'];
        $add_data['subBank'] = $apply_info['subBank'];
        $add_data['bankDistrict'] = $apply_info['bankDistrict'];
        $add_data['refund_freight'] = $apply_info['refund_freight'];
        $add_data['is_user_send_cancel'] = 'NO';
        //$add_data['rnd_num'] = $rnd_num ;//此字段作废

        //获取管理员信息
        $login_user = Yii::$app->session->get('login_user');
        $admin_id  = isset($login_user['id'])?$login_user['id']:0;
        $add_data['admin_id'] = $admin_id ;

        $add_data['backend_audit_status'] = 'UNDEAL';
        $add_data['backend_audit_idea'] = '';
        $add_data['backend_audit_time'] = '0000-00-00 00:00:00';
        $add_data['is_submit_freight'] = 'N';

        if($apply_info['after_market_type'] ==1){
            $add_data['backend_audit_status'] = 'SUCCESS';
            $add_data['backend_audit_idea'] = '审核成功';
            $add_data['backend_audit_time'] = date('Y-m-d H:i:s');
        }

        return $add_data ;
    }

    /**
     * 获取需要更新的订单商品的数据
     * @param int $after_market_type
     * @return array
     */
    public function getOrderGoodsUpdateData($after_market_type=0){
        $rst = array();
        switch ($after_market_type){
            #TODO 其他的几个字段暂时去除，目前用不到
            case 1://退款
                //$goods_update_info="refund_status=1,refund_nums={$sult_refund_num},exchange_nums={$sult_exchange_num},return_goods_nums={$sult_return_num}";
                $rst['refund_status'] =1;
                break;
            case 2://退货
                //$goods_update_info="return_goods_status=1,refund_nums={$sult_refund_num},exchange_nums={$sult_exchange_num},return_goods_nums={$sult_return_num}";
                $rst['return_goods_status'] =1;
                break;
            case 3://换货
                //$goods_update_info="exchange_status=1,refund_nums={$sult_refund_num},exchange_nums={$sult_exchange_num},return_goods_nums={$sult_return_num}";
                $rst['exchange_status'] =1;
                break;
            case 4://维修
                //$goods_update_info="maintain_status=1,refund_nums={$sult_refund_num},exchange_nums={$sult_exchange_num},return_goods_nums={$sult_return_num}";
                $rst['maintain_status'] =1;
            case 5://退差价
                break;
        }
        return $rst ;
    }

    /**
     * 根据申请信息获取需要的订单发货表的信息
     * @param array $apply_info
     * @return array
     */
    public function getOrderGoodsSingleIdsByApplyInfo($apply_info=array()){
        $after_market_type = $apply_info['after_market_type'] ;
        if($after_market_type ==5){
            $rst = array();
        }else {

            $order_id = $apply_info['order_id'] ;
            $goods_id = $apply_info['goods_id'] ;
            $product_id = $apply_info['product_id'] ;
            $goods_nums = $apply_info['goods_nums'] ;

            $where_str = ' order_id =:order_id and goods_id =:goods_id and product_id =:product_id';
            $where_arr[':order_id'] = $order_id;
            $where_arr[':goods_id'] = $goods_id;
            $where_arr[':product_id'] = $product_id;
            if($after_market_type==2){
                $where_str.= ' and after_market_status in(0,2,3)';
                $where_str.= " and (send_status = 1 or send_status = 5)";
            }else{
                $where_str .= $after_market_type == 1 ? " and (after_market_status = 0 or after_market_status = 3) and (send_status = 0 or send_status = 4)" : " and ( ((after_market_status = 0 or after_market_status = 3) and send_status = 1 ) or (send_status =5 and  after_market_status =2) )";
            }
            $rst = array();
            $db = new Query();
            $query = $db->select("id")->from("sdb_order_goods_single");
            $query->where($where_str,$where_arr);
            $query->limit($goods_nums);
            $info  = $query->all();
            if($info){
                foreach($info as $v){
                    $rst[] = $v['id'];
                }
            }

        }
        return $rst ;
    }

    /**
     * 返回需要添加售后任务表数据
     * @param integer $after_market_doc_id 售后ID
     * @param array $post_data post信息
     * @return array
     */
    public function getAsmTaskAddData($after_market_doc_id,$post_data=array()){



        $name = '发送售后请求到售后中心';
        $service_key = 'send_aftermarket_to_asm';
        $add_data['after_market_doc_id'] = $after_market_doc_id ;
        $add_data['name'] = $name ;
        $add_data['service_key'] = $service_key ;

        $params['after_market_doc_id'] = $after_market_doc_id ;
        $params['postData'] = $post_data ;
        $params['userInfo'] = array() ;
        $params = serialize($params);
        $add_data['params'] = $params;

        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['status'] = 'SENDING';

        return $add_data;
    }

    /**
     * 申请售后第一步
     * @param integer $after_market_doc_id 售后单ID
     * @param array $apply_info 申请售后的信息
     * @param array $post_data  后台用户提交的表单数据
     * @return array
     * Note:剩余部分要截单成功之后才进行
     */
    public function doApplyFirst($after_market_doc_id,$apply_info=array(),$post_data)
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




        //step4 类型为退款，判断是否已经发货
        //判断退款的商品数目是否符合商品发货的数目要求
        /*
        if ($after_market_type == 1) {
            $check_is_send = $this->dealGoodsIsSended($order_id, $goods_id, $goods_nums);

            if (!$check_is_send) {
                $rst['code'] = '400018';
                return $rst;
            }
        }
        */

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
     * @param $apply_info
     * @param $post_data
     * @param $data
     * @return array
     */
    public function doApplySecond($apply_info,$post_data,$data){

        $rst['code'] = 1 ;
        $rst['msg'] = '' ;

        //step1 创建随机数，用来判断操作批次
        $rnd_num	= createRndNum(20);

        $order_info = $data['data']['order_info'];
        $goods_info = $data['data']['goods_info'];
        $order_id = $apply_info['order_id'];
        $goods_id = $apply_info['goods_id'];
        $product_id = $apply_info['product_id'];

        $after_market_type = $apply_info['after_market_type'];

        //$transaction = Yii::$app->db->beginTransaction();
        //try{
            //step2 插入售后单信息
            $aftermarket_doc_id = $apply_info['id'] ;

            //step3 返回订单商品的更新数据
            $order_goods_obj = new OrderGoods();
            $update_order_goods_data = $this->getOrderGoodsUpdateData($after_market_type);
            if($update_order_goods_data) {
                $update_order_goods_where_str = 'order_id =:order_id and goods_id =:goods_id and product_id=:product_id';
                $update_order_goods_where_arr[':order_id'] = $order_id;
                $update_order_goods_where_arr[':goods_id'] = $goods_id;
                $update_order_goods_where_arr[':product_id'] = $product_id;
                $order_goods_obj->baseUpdate("sdb_order_goods", $update_order_goods_data, $update_order_goods_where_str, $update_order_goods_where_arr);
            }

            //step4 返回订单发货表更新数据 此方法已经作废已经在申请售后的手就需要更新
            /*
            $update_order_goods_single_ids = $this->getOrderGoodsSingleIdsByApplyInfo($apply_info);
            if($update_order_goods_single_ids) {
                $order_goods_single_obj = new OrderGoodsSingle();
                $update_order_goods_single_data['after_market_status'] = 1;
                $update_order_goods_single_data['amd_id'] = $aftermarket_doc_id;
                $single_where_str = " id in ( :update_order_goods_single_ids)";
                $single_where_arr[":update_order_goods_single_ids"] = implode(",", $update_order_goods_single_ids);
                $order_goods_single_obj->baseUpdate("sdb_order_goods_single", $update_order_goods_single_data, $single_where_str, $single_where_arr);
            }
            */

            //step5 创建计划任务表
            $asm_task_obj = new AsmTask();
            $add_asm_task_data = $this->getAsmTaskAddData($aftermarket_doc_id,$post_data);
            $asm_task_id = $asm_task_obj->baseInsert("sdb_asm_task",$add_asm_task_data);

            $rst['data']['aftermarket_doc_id'] = $aftermarket_doc_id ;
            $rst['data']['asm_task_id'] = $asm_task_id ;
            return $rst ;

        //}catch (\Exception $e){
            //如果抛出错误则进入catch，返回错误
            //$transaction->rollBack();
           // debug($e,1);
            //$rst['code'] = '400019';
            //#TODO 异常日志处理
            //return $rst;
        //}
    }


    /**
     * 判断前台用户是否有进行售后的权限
     * @param  array  $order_info 订单信息
     * @return boolean
     */
    public function checkUserCanAftermarket($order_info=array(),$goods_info){
        $rst = false ;

        //可以进行退差价的商品的数量
        $can_refund_diff_num = $this->getAllowedRefundedNum($order_info,$goods_info);

        //获取允许的最大的退款商品数量
        $can_refund_num =$this->getAllowedRefundedGoodsNum($order_info,$goods_info) ;

        //获取退货换货维修的最大数目
        $max_left_goods_num = $this->getMaxExchangeReceiveRepaireGoodsNum($order_info,$goods_info);

        if($can_refund_diff_num || $can_refund_num || $max_left_goods_num){
            $rst = true ;
        }

        return $rst ;
    }


    /**
     * 判断当前订单中的商品是否可以进行售后
     * @param  [type] $order_info [description]
     * @param  [type] $goods_info [description]
     * @return [type]             [description]
     */
    public function checkOrderGoodsCanAftermarket($order_info,$goods_info){
        $order_obj = new Order();
        $order_status = $order_obj->getOrderStatus($order_info);

        //订单未付款、已经取消、已退款、已经关闭的状态，不能申请售后
        if(in_array($order_status,array(2,5,7)))
        {
            return false;
        }

        //定金已支付，尾款逾期未支付的订单不能申请退差价
        if($order_info['type']==6) {
            $order_presell_obj = new OrderPresell();
            $check_presell_overtime = $order_presell_obj->checkOvertimePresell($order_info);

            if(($order_info['pay_status'] == 1 || $check_presell_overtime) ){

            }else{
                return false;
            }
        }

        if($goods_info['goods_type'] ==2){
            return false;
        }

        if($goods_info['goods_price']==0){
            return false;
        }

        //订单扣除运费金额为0 也不允许售后
        $excepet_freight_amount = $order_info['order_amount']-$order_info['real_freight'] ;
        if( $excepet_freight_amount == 0){
            return false;
        }


        $aftermarket_params['cond'] = 'order_id =:order_id and goods_id=:goods_id and dispose_status = 0 ';
        $aftermarket_params['args'] = [':order_id'=>$goods_info['order_id'],'goods_id'=>$goods_info['goods_id']];
        $processing = $order_obj->findOneByWhere('sdb_after_market_doc',$aftermarket_params);
        if($processing){
            return false;
        }


        $max_diff_amount = $this->getAllowedRefundedPrice($order_info, $goods_info);

        $data['can_refund_diff_num'] = $this->getAllowedRefundedNum($order_info, $goods_info);

        $data['max_diff_amount'] = $max_diff_amount;

        //获取允许的最大的退款商品数量
        $data['afterMarketInfo']['can_refund_num'] = $this->getAllowedRefundedGoodsNum($order_info, $goods_info);

        //获取退货换货维修的最大数目
        $max_left_goods_num = $this->getMaxExchangeReceiveRepaireGoodsNum($order_info, $goods_info);

        if (!$data['can_refund_diff_num'] && !$data['afterMarketInfo']['can_refund_num'] && !$max_left_goods_num) {

           return false ;
        }



        return true ;
    }


    /**
     * 判断统一状态是否为中台第二次请求
     * @param  array  $log_info  日志信息
     * @param  string $status    ASM 售后中心推送的状态
     * @return PROCESSING  WAITING FINISHED
     */
    public  function  check_is_asm_second_send($log_info=array(),$status=''){
        $rst = false ;
        //当前状态
        $callback_status = $log_info['callback_status'] ;

        if($callback_status =='PROCESSING'){
            //推送状态
            if($status =='PROCESSING' || $status=='WAITING'){
                $rst = true ;
            }
        }

        return $rst ;
    }

    /**
     * 返回售后处理完成的时候，需要返回一条处理日志为前台用户
     * @param  array  $after_market_doc_info [description]
     * @return array
     * Note:中台处理状态为PROCESSING的时候才会添加
     */
    public function return_asm_deal_log($after_market_doc_info=array()){

        $logdata = array(
            'act'=> 'aftermarket_deal_by_asm',
            'act_name'=>'售后单管理',
            'act_type'=>'售后中同意本次售后请求',
            'act_content'=>'售后单号'.$after_market_doc_info['id'],
            'order_id'=>$after_market_doc_info['order_id'],
            'order_no'=>$after_market_doc_info['order_no'] ,
            'note'=>'售后中同意本次售后请求',
            'aftermarket_id'=>$after_market_doc_info['id'],
            'act_content'=>'同意了本次售后服务申请',
            'addtime'=> date('Y-m-d H:i:s'),
            'ip' =>getIp(),
        );

        //$logdata['user_id'] = $after_market_doc_info['user_id'];
        //$member_obj  = new Member();
        //$user_info = $member_obj->getUserInfoById($logdata['user_id']);
        //$username = isset($user_info['username'])?$user_info['username']:'';

        //系统回调自动处理成admin
        $logdata['username'] = 'admin';
        $logdata['user_id'] = 1;
        $logdata['role'] = 'system';

        $log_obj = new LogAftermarket();
        return $log_obj->baseInsert('sdb_log_aftermarket',$logdata);

    }


    /**
     * 返回取消或者作废售后工单的用户需要的日志
     * @param  array  $after_market_doc_info 售后信息
     * @param  string $status                中台返回状态 CANCEL /EXCEPTION
     * @param  string $msg                   中台的审核一键
     * @return
     */
    public function return_cancel_or_exception($after_market_doc_info,$status='',$msg='',$log_info=array()){

        $logdata = array(
            'act'=> 'aftermarket_cancel_or_exception_by_asm',
            'act_name'=>'售后单管理',
            'act_type'=>'售后中取消该售后单',
            'act_content'=>'售后单号'.$after_market_doc_info['id'],
            'order_id'=>$after_market_doc_info['order_id'],
            'order_no'=>$after_market_doc_info['order_no'] ,
            'note'=>'售后中同意本次售后请求',
            'aftermarket_id'=>$after_market_doc_info['id'],
            //'act_content'=>'拒绝了本次售后服务申请。原因说明：'.$msg,
            'addtime'=> date('Y-m-d H:i:s'),
            'ip' => getClientIP(),
        );

        //$logdata['user_id'] = $after_market_doc_info['user_id'];
        //$member_obj  = new Member();
        //$user_info = $member_obj->getUserInfoById($logdata['user_id']);
        //$username = isset($user_info['username'])?$user_info['username']:'';

        $logdata['username'] = 'admin';
        $logdata['user_id'] = 1;
        $logdata['role'] = 'system';

        if($log_info['callback_status']=='SENDING'){
            //售后工单由待审核变更作废
            $act_content = '拒绝了本次售后服务申请。原因说明：'.$msg ;
        }else{
            //审核通过变更为作废或异常
            $act_content = '关闭了本次售后服务申请。原因说明：'.$msg ;
        }

        $logdata['act_content'] = $act_content ;

        $log_obj = new LogAftermarket();
        return $log_obj->baseInsert('sdb_log_aftermarket',$logdata);
    }


    //处理完成的日志
    public function return_deal_succ($after_market_doc_info){


        $logdata = array(
            'act'=> 'aftermarket_succ_by_asm',
            'act_name'=>'售后单管理',
            'act_type'=>'售后中处理完成',
            'act_content'=>'售后单号'.$after_market_doc_info['id'],
            'order_id'=>$after_market_doc_info['order_id'],
            'order_no'=>$after_market_doc_info['order_no'] ,
            'note'=>'完成了本次售后请求',
            'aftermarket_id'=>$after_market_doc_info['id'],
            'act_content'=>'完成了本次售后服务。',
            'addtime'=>date('Y-m-d H:i:s'),
            'ip' => getIp(),
        );

        //$logdata['user_id'] = $after_market_doc_info['user_id'];
        //$member_obj  = new Member();
        //$user_info = $member_obj->getUserInfoById($logdata['user_id']);
        //$username = isset($user_info['username'])?$user_info['username']:'';

        $logdata['username'] = 'admin';
        $logdata['user_id'] = 1;
        $logdata['role'] = 'system';

        $act_content = '完成了本次售后服务。';
        $logdata['act_content'] = $act_content ;

        $log_obj = new LogAftermarket();
        return $log_obj->baseInsert('sdb_log_aftermarket',$logdata);
    }


    /**
     * 确认退款回调 退款操作失败
     * @param  array  $data_detail     官网售后单ID
     * @param  string $asmOrderNo      售后中心工单号
     * @param  string $originalOrderNo 官网的订单号
     * @param  string $msg             中台审核意见
     * @return boolean
     * 相当于之前中台处理失败  callback/confirmRefund  -> aftermarket::oms_refund_fail
     */
    public static function aftermarket_confirm_fail($data_detail=array(),$asmOrderNo,$originalOrderNo,$msg=''){

        $rst['code'] =1 ;
        $rst['msg'] = '';



        $order_goods_single_db = new OrderGoodsSingle();
        $order_goods_single_update_data = ['after_market_status'=>3];
        $order_goods_single_db->baseUpdate('sdb_order_goods_single',$order_goods_single_update_data,'amd_id = :amd_id',[':amd_id'=>$data_detail['id'] ] );

        //step1 获取官网售后单的log
        $current_time = date('Y-m-d H:i:s');
        if($data_detail['doc_log']){
            $doc_log=@unserialize($data_detail['doc_log']);
        }else{
            $doc_log = array();
        }
        $doc_log[count($doc_log)]	=array('time'=>$current_time,'type'=>'退款单关闭','operater'=>'中台接口回调处理');
        $doc_log = serialize($doc_log);

        //step2 退款完成后相关的sql操作
        $update_where=" where order_id={$data_detail['order_id']} and goods_id={$data_detail['goods_id']} ";
        $update_str="refund_nums=refund_nums+{$data_detail['goods_nums']},";
        $update_str.="return_goods_nums=return_goods_nums+{$data_detail['goods_nums']},";
        $update_str.="exchange_nums=exchange_nums+{$data_detail['goods_nums']} {$update_where}";

        //step3 退货取消的类型 需要恢复换货到成功的状态
        if($data_detail['after_market_type'] == 2){
            /*
            $sql[]	="update sdb_order_goods_single set  after_market_status=0 where amd_id={$data_detail['id']} and send_status !=5 ";	//更新商品售后信息
            $sql[]	="update sdb_order_goods_single set  after_market_status=2 where amd_id={$data_detail['id']}  and send_status =5 ";	//更新商品售后信息
            */
            $order_goods_single_db->baseUpdate('sdb_order_goods_single',['after_market_status'=>0],'amd_id=:amd_id and send_status != :send_status',[":amd_id"=>$data_detail['id'],":send_status"=>5]);
            $order_goods_single_db->baseUpdate('sdb_order_goods_single',['after_market_status'=>2],'amd_id=:amd_id and send_status = :send_status',[":amd_id"=>$data_detail['id'],":send_status"=>5]);

        }else{
            //$sql[]	="update sdb_order_goods_single set  after_market_status=0 where amd_id={$data_detail['id']}";	//更新商品售后信息
            $order_goods_single_db->baseUpdate('sdb_order_goods_single',['after_market_status'=>0],"amd_id=:amd_id",[":amd_id"=>$data_detail['id']]);
        }
        //更新订单的商品的相关字段
        $order_goods_where_str = ' order_id =:order_id and  goods_id=:goods_id';
        $order_goods_where_arr = [':order_id'=>$data_detail['order_id'],':goods_id'=>$data_detail['goods_id']];
        $goods_nums = $data_detail['goods_nums'] ;
        $order_goods_update_data =["refund_status"=>0,"refund_nums"=> new Expression('refund_nums + '.$goods_nums), "return_goods_nums"=> new Expression('return_goods_nums + '.$goods_nums),"exchange_nums"=> new Expression('exchange_nums + '.$goods_nums) ];
        $order_goods_single_db->baseUpdate('sdb_order_goods',$order_goods_update_data,$order_goods_where_str,$order_goods_where_arr);

        //更新售后单的相关字段
        $doc_update_data = ['audit_status'=>2,'audit_idea'=>$msg,'dispose_status'=>2,'dispose_time'=>$current_time,'dispose_idea'=>$msg,'doc_log'=>$doc_log]  ;
        $order_goods_single_db->baseUpdate('sdb_after_market_doc',$doc_update_data,'id=:id',[":id"=>$data_detail['id']]);

        //step4 执行日志
        $logdata = array(
            'act'=> 'aftermarket_refund_close',
            'act_name'=>'售后单管理',
            'act_type'=>'关闭售后单',
            'act_content'=>'关闭售后退款单:'.$asmOrderNo.'售后单号'.$data_detail['id'],
            'order_id'=>$data_detail['order_id'],
            'order_no'=>$data_detail['order_no'] ,
            'note'=>'关闭理由:'.$msg,
            'content' =>json_encode($data_detail),
            'aftermarket_id'=>$data_detail['id']
        );

        $logdata['username'] = 'admin';
        $logdata['user_id'] = 1;
        $logdata['role'] = 'system';

        $order_goods_single_db->baseInsert('sdb_log_aftermarket',$logdata);



        return $rst ;

    }


    /**
     * 确认退款回调 退款操作失败
     * @param  array  $after_market_doc_info    官网售后单信息
     * @param  string $msg                      中台审核意见
     * @return boolean
     * 相当于之前中台处理失败  callback/confirmRefund  -> aftermarket::oms_refund_fail
     */
    public static function aftermarket_confirm_fail_from_trade_in($after_market_doc_info=array(),$msg=''){

        $obj = new TradeInAfterMarketDoc();

        // 开启事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            
            //step1 更新售后单的相关字段
            $current_time = date('Y-m-d H:i:s');
            $doc_update_data = ['audit_status'=>2,'audit_idea'=>$msg,'dispose_status'=>2,'dispose_time'=>$current_time,'dispose_idea'=>$msg]  ;
            $trade_in_after_market_update = $obj->baseUpdate('sdb_trade_in_after_market_doc',$doc_update_data,'id=:id',[":id"=>$after_market_doc_info['id']]);
    
            //step2 更新以旧换新申请单信息
            $trade_in_data = ['status' => 'FINISH', 'aftermarket_status' => 'FAIL', 'admin_note' => $msg, 'update_time' => $current_time];
            $where_str = 'id=:id';
            $where_arr = [':id'=>$after_market_doc_info['trade_in_apply_id']];
            $trade_in_apply_update = $obj->baseUpdate('sdb_trade_in_apply', $trade_in_data, $where_str, $where_arr);

            // 提交事务
            $transaction->commit();
    
            return ($trade_in_after_market_update && $trade_in_apply_update) ? true : false;
            
        }catch (Exception $e) {
        
            //回滚事务
            $transaction->rollback();
        
            return false;
        }

        /*
        $delivery_code = $data_detail['delivery_code'];
        $trade_in_apply_params['cond'] = 'delivery_code =:delivery_code';
        $trade_in_apply_params['args'] = [':delivery_code'=>$delivery_code];
        $trade_in_apply_params['fields'] = 'id';
        $trade_in_apply_info = $obj->findOneByWhere('sdb_trade_in_apply',$trade_in_apply_params);
        if($trade_in_apply_info) {
            $trade_in_data = ['status' => 'FINISH', 'aftermarket_status' => 'FAIL', 'voucher_status' => 'FAIL', 'admin_note' => $msg, 'update_time' => $current_time];
            $where_str = 'id=:id';
            $where_arr = [':id'=>$trade_in_apply_info['id']];
            $obj->baseUpdate('sdb_trade_in_apply',$trade_in_data,$where_str,$where_arr);
        }
        */
    }


    /**
     * 确认退款回调 将售后确认为完成阶段
     * @param  array  $data_detail 官网售后单ID
     * @return boolean
     * Note:,$refundment_type 0=直接退款,1=退货退款,2=换货退款,3=差价退款
     * 相当于之前中台处理成功  callback/confirmRefund  -> aftermarket::oms_refund_success
     */
    public function aftermarket_refund_confirm_success($data_detail=array(),$asmOrderNo,$originalOrderNo){


        $order_id = $data_detail['order_id'];
        $goods_id = $data_detail['goods_id'];
        $obj = new OrderGoodsSingle();
        //如果退款类型不是换货退款和差价退款，商品状态修改已退款
        $order_goods_single_update_data = array (
            'global_after_market_no' => $asmOrderNo,
            'global_after_market_asm_no'=>$asmOrderNo,
            'after_market_status'=> 2,
            'send_status' =>3,
        );
        $obj->baseUpdate('sdb_order_goods_single',$order_goods_single_update_data,'amd_id=:amd_id',[':amd_id'=>$data_detail['id']]);



        //如果是退款单或者退货单, 更新售后单状态
        $after_market_doc_data = $data_detail;

        //退款，退货，换货，退差价
        if(in_array($after_market_doc_data['after_market_type'] ,array(1,2,3,5))){

            $doc_update_data = array('dispose_status'=> 1, 'dispose_time'=>date("Y-m-d H:i:s"));
            $obj->baseUpdate('sdb_after_market_doc',$doc_update_data,'id=:id',[":id"=>$data_detail['id']]);

            //更新订单发货状态
            $orderCancelStatus = $obj->checkOrderCancelStatus($order_id);

            //判断是否金额已经全部退完
            if($orderCancelStatus){
                //如果订单商品已经全部退货或者换货，订单更新为取消
                $orderStatusArray['status'] = 8;
            }

            //订单后台状态
            $backSendStatus = $obj->getOrderBackSendStatus($order_id);
            $orderStatusArray['back_distribution_status'] = $backSendStatus;
            $obj->baseUpdate('sdb_order',$orderStatusArray,'id=:id',[":id"=>$order_id]);

            #处理优惠券、代金券、折扣券、积分
            $order_model = new Order();
            $user_id = $after_market_doc_data['user_id'];
            $order_model->recoverPromotions($order_id,$goods_id,$data_detail['refund_price'],$user_id,$orderCancelStatus,$asmOrderNo);
        }

        //将订单商品表的数据 is_send更新为5 状态释义为已退款
        $order_goods_obj = new OrderGoods();
        $order_goods_obj->updateOrderGoodsStatusByAftermarket($order_id,$goods_id,$data_detail['after_market_type']);


        //检查微积分订单，更新微积分用户结算佣金
        $order_params['fields']  = '*';
        $order_params['cond']  = 'id=:id';
        $order_params['args']  = [':id'=>$order_id];
        $order_data = $obj->findOneByWhere('sdb_order',$order_params);
        if($order_data['cf_uid']){

            //$amount 计算规则修改成和buy_list.html 一致
            $order_obj = new Order();
            $cf_info = $order_obj->getCfInfo();
            if(isset($cf_info[$order_data['id']])&&$cf_info[$order_data['id']]>0 ){

                $cf_amount = $order_obj->getCfAmount($order_data['id']);
                $amount    = floatval($order_data['order_amount'])-floatval($cf_amount);

            }else{
                $amount = $order_data['order_amount'] ;
            }

            $order_obj->updateCfRebate($order_id, $amount);
        }

        // 只有针对退款类型才能进行取消售后申请操作
        if($order_data['type']=='14' && $after_market_doc_data['after_market_type'] ==1){
            //以旧换新订单需要更新依旧换新的申请状态
            $trade_in_apply_update_data['status'] = 'CANCEL';
            $trade_in_apply_update_data['update_time'] = date('Y-m-d H:i:s');
            $obj->baseUpdate('sdb_trade_in_apply',$trade_in_apply_update_data,'order_id=:order_id',[':order_id'=>$order_data['id']]);
        }


    }


    /**
     * 处理退款类型的售后单
     * @param  array $data_detail 官网售后单信息
     * @param  string  $asmOrderNo 中台返回的工单号
     * @param  string  $originalOrderNo 官网订单号
     * @return array
     * Note:中台是退款完成才会关闭工单
     * return code  2001 2002 2003
     */
    public function aftermarket_refund_close($data_detail = array(),$asmOrderNo,$originalOrderNo){

        $rst['code'] = 1 ;
        $rst['msg'] = '';

        //当前时间
        $current_time = date('Y-m-d H:i:s');

        //step1 获取官网售后单的log
        if($data_detail['doc_log']){
            $doc_log=@unserialize($data_detail['doc_log']);
        }else{
            $doc_log = array();
        }
        $doc_log[count($doc_log)]	=array('time'=>$current_time,'type'=>'退款单关闭','operater'=>'中台接口回调处理');
        $doc_log = serialize($doc_log);


        //step2 退款完成后相关的sql操作
        //$update_where=" where order_id={$data_detail['order_id']} and goods_id={$data_detail['goods_id']} ";
        //$update_str="refund_nums=refund_nums+{$data_detail['goods_nums']},";
        //$update_str.="return_goods_nums=return_goods_nums+{$data_detail['goods_nums']},";
        //$update_str.="exchange_nums=exchange_nums+{$data_detail['goods_nums']} {$update_where}";
        //$sql[]	="update sdb_order_goods_single set  after_market_status=0 where amd_id={$data_detail['id']}";	//更新商品售后信息
        //更新订单的商品的相关字段 作废，不需要重复更新
        //$sql[]		="update sdb_order_goods set  refund_status=0,{$update_str} ";
        //更新售后单的相关字段
        //$sql[]		="update sdb_after_market_doc set dispose_status=2,audit_status=1,dispose_time='{$current_time}',dispose_idea='退款单关闭,售后单也随着关闭',doc_log='{$doc_log}' where id={$data_detail['id']}";

        //更新售后单的相关字段
        $order_goods_single_db = new OrderGoodsSingle();
        $doc_update_data = ['dispose_status'=>2,'audit_status'=>1,'dispose_time'=>$current_time,'dispose_idea'=>'退款单关闭,售后单也随着关闭','doc_log'=>$doc_log]  ;
        $order_goods_single_db->baseUpdate('sdb_after_market_doc',$doc_update_data,'id=:id',[":id"=>$data_detail['id']]);


        //step3 执行日志
        $logdata = array(
            'act'=> 'aftermarket_refund_close',
            'act_name'=>'退款单管理',
            'act_type'=>'关闭退款单',
            'act_content'=>'关闭售后退款单:'.$asmOrderNo.'售后单号'.$data_detail['id'],
            'order_id'=>$data_detail['order_id'],
            'order_no'=>$data_detail['order_no'] ,
            'note'=>'关闭理由:中台回调处理关闭',
            'content' =>json_encode($data_detail),
            'aftermarket_id'=>$data_detail['id']
        );

        $logdata['username'] = 'admin';
        $logdata['user_id'] = 1;
        $logdata['role'] = 'system';

        $order_goods_single_db->baseInsert('sdb_log_aftermarket',$logdata);
        return $rst ;
    }

    /**
     * 更新订单表
     * @param  [type] $order_id 订单ID
     * @return [type]           [description]
     */
    public function update_order_distribution_status($order_id){

        $obj = new OrderGoodsSingle();
        //更新订单发货状态
        $orderSendStatus = $obj->getOrderSendStatus($order_id);
        $orderBackSendStatus = $obj->getOrderBackSendStatus($order_id);

        //更新发货状态
        $update_data = ['distribution_status'=>$orderSendStatus,'back_distribution_status'=>$orderBackSendStatus,'send_time'=>date('Y-m-d H:i:s')] ;
        $obj->baseUpdate('sdb_order',$update_data,'id=:id',[":id"=>$order_id]);

    }

    /**
     * 确认退货回调的处理 将售后确认为完成阶段
     * @param  array  $data_detail 官网售后单ID
     * @return boolean
     * Note:,$refundment_type 0=直接退款,1=退货退款,2=换货退款,3=差价退款
     * 相当于之前中台处理成功  callback/confirmReceive  -> aftermarket::oms_receive_success
     *
     */
    public function aftermarket_return_confirm_success($data_detail = array(),$asmOrderNo,$originalOrderNo){

        $obj = new OrderGoodsSingle();
        //如果是退货，更新订单商品状态
        $order_goods_single_update_data = ['send_status'=>2,'after_market_status'=>2];
        $obj->baseUpdate('sdb_order_goods_single',$order_goods_single_update_data,'amd_id=:amd_id',[':amd_id'=>$data_detail['id']]);

        //如果是退货，更新订单商品状态
        $order_goods_update_data = ['return_goods_status'=>4];
        $order_goods_where_str = ' order_id=:order_id and goods_id=:goods_id ';
        $order_goods_where_arr = [':order_id'=>$data_detail['order_id'],':goods_id'=>$data_detail['goods_id']];
        $obj->baseUpdate('sdb_order_goods',$order_goods_update_data,$order_goods_where_str,$order_goods_where_arr);

        //如果有赠品，更新赠品的状态
        /*
        $order_goods_params['fields'] = 'id,goods_nums';
        $order_goods_params['cond'] = 'order_id=:order_id and goods_id=:goods_id';
        $order_goods_params['args'] = [':order_id'=>$data_detail['order_id'],':goods_id'=>$data_detail['goods_id']];
        $order_goods_info = $obj->findOneByWhere('sdb_order_goods',$order_goods_params);
        $amd_batch_num = isset($order_goods_info['amd_batch_num']) ? $order_goods_info['amd_batch_num'] : '';
        if(!empty($amd_batch_num)){
            $batch_order_goods_where_str = 'amd_batch_num =:amd_batch_num and goods_id != :goods_id';
            $batch_order_goods_where_arr = [':amd_batch_num'=>$amd_batch_num ,':goods_id'=>$data_detail['goods_id']];
            $obj->baseUpdate('sdb_order_goods',['is_send'=>5],$batch_order_goods_where_str,$batch_order_goods_where_arr);
        }*/



        //全部退货完成将订单商品状态切换为5

        //获取退货完成的商品数量
            /*
            $all_return_goods_nums = $obj->getAllReturnGoodsNums($data_detail['order_id'],$data_detail['goods_id']);

            $order_goods_obj = new OrderGoods();
            $order_goods_info = $order_goods_obj->getRowInfoByWhere(["order_id"=>$data_detail['order_id'],"goods_id"=>$data_detail['goods_id']]);
            $order_goods_num = $order_goods_info['goods_nums'];

            if($order_goods_num==$all_return_goods_nums){

                $obj->baseUpdate('sdb_order_goods',['is_send'=>5],'id=:id',[':id'=>$order_goods_info['id']]);

                //同时更新对应赠品的信息
                $giveaway_params['cond'] = 'order_id=:order_id and main_goods_id=:main_goods_id';
                $giveaway_params['args'] = [':order_id'=>$data_detail['order_id'],':main_goods_id'=>$data_detail['goods_id']];
                $giveaway_list = $obj->findAllByWhere('sdb_giveaway_activity_order',$giveaway_params);
                if($giveaway_list){
                    foreach($giveaway_list as $v){
                        //赠品已退款数量是否等于总数量
                        $giveaway_all_return_num = $obj->getAllReturnGoodsNums($data_detail['order_id'],$v['giveaway_goods_id']);
                        $giveaway_all_num = $order_goods_obj->getGoodsNum($data_detail['order_id'],$v['giveaway_goods_id']);
                        if($giveaway_all_return_num==$giveaway_all_num){
                            $obj->baseUpdate('sdb_order_goods',['is_send'=>5],'order_id=:order_id and goods_id=:goods_id',[':order_id'=>$data_detail['order_id'],':goods_id'=>$v['giveaway_goods_id']]);
                        }
                    }
                }
            }
            */
            $order_goods_obj = new OrderGoods();
            $order_goods_obj->updateOrderGoodsStatusByAftermarket($data_detail['order_id'],$data_detail['goods_id'],$data_detail['after_market_type']);



        //更新订单状态
        $orderCancelStatus = $obj->checkOrderCancelStatus($data_detail['order_id']);

        if($orderCancelStatus){
            $obj->baseUpdate('sdb_order',['status'=>8,'update_time'=> date('Y-m-d H:i:s')],'id=:id',[":id"=>$data_detail['order_id']]);
        }

        #处理优惠券、代金券、折扣券、积分
        $order_model = new Order();
        $order_id = $data_detail['order_id'] ;
        $goods_id = $data_detail['goods_id'] ;
        $user_id  = $data_detail['user_id'];
        $order_model->recoverPromotions($order_id,$goods_id,$data_detail['refund_price'],$user_id,$orderCancelStatus,$asmOrderNo);
    }

    /**
     * 处理退货类型的售后单
     * @param  array $data_detail 官网售后单信息
     * @param  string  $asmOrderNo 中台返回的工单号
     * @param  string  $originalOrderNo 官网订单号
     * @return array
     * Note:return code  3001 3002 3003
     */
    public function aftermarket_return_close($data_detail = array(),$asmOrderNo,$originalOrderNo){


        $rst['code'] = 1 ;
        $rst['msg'] = '';

        //当前时间
        $current_time = date('Y-m-d H:i:s');

        //step1 获取官网售后单的log
        if($data_detail['doc_log']){
            $doc_log=@unserialize($data_detail['doc_log']);
        }else{
            $doc_log = array();
        }
        $doc_log[count($doc_log)]	=array('time'=>$current_time,'type'=>'退货单关闭','operater'=>'中台接口回调处理');
        $doc_log = serialize($doc_log);

        /*
        $dispose_idea = '官网回调关闭';
        $update_where="where order_id={$data_detail['order_id']} and goods_id={$data_detail['goods_id']} ";
        $update_str="refund_nums=refund_nums+{$data_detail['goods_nums']},";
        $update_str.="return_goods_nums=return_goods_nums+{$data_detail['goods_nums']},";
        $update_str.="exchange_nums=exchange_nums+{$data_detail['goods_nums']} {$update_where}";
        $sql[]	="update sdb_order_goods set  return_goods_status=0,{$update_str} ";
        */

        //step1 更新订单商品表数据
        $obj = new OrderGoods();
        $goods_nums = $data_detail['goods_nums'] ;
        $order_goods_update_data =["return_goods_status"=>0,"refund_nums"=> new Expression('refund_nums + '.$goods_nums), "return_goods_nums"=> new Expression('return_goods_nums + '.$goods_nums),"exchange_nums"=> new Expression('exchange_nums + '.$goods_nums) ];
        $order_goods_where_str = 'order_id=:order_id and goods_id=:goods_id';
        $order_goods_where_arr = [":order_id"=>$data_detail['order_id'],':goods_id'=>$data_detail['goods_id']];
        $obj->baseUpdate('sdb_order_goods',$order_goods_update_data,$order_goods_where_str,$order_goods_where_arr);

        //step2 更新售后单的相关字段
        //$sql[]		="update sdb_after_market_doc set dispose_status=2,dispose_time='{$current_time}',dispose_idea='由于退货单关闭,售后单也随之关闭',doc_log='{$doc_log}' where id={$data_detail['id']}";
        $doc_update_data = ['dispose_status'=>2,'audit_status'=>1,'dispose_time'=>$current_time,'dispose_idea'=>'由于退货单关闭,售后单也随之关闭','doc_log'=>$doc_log] ;
        $obj->baseUpdate('sdb_after_market_doc',$doc_update_data,'id=:id',[':id'=>$data_detail['id']]);

        //step3 执行日志
        $logdata = array(
            'act'=> 'aftermarket_return_audit',
            'act_name'=>'退货单管理',
            'act_type'=>'关闭退货单',
            'act_content'=>'关闭退货单:'.$asmOrderNo.'售后单号'.$data_detail['id'],
            'order_id'=>$data_detail['order_id'],
            'order_no'=>$data_detail['order_no'] ,
            'note'=>'关闭理由:中台回调处理关闭',
            'content' =>json_encode($data_detail),
            'aftermarket_id'=>$data_detail['id']
        );

        $logdata['username'] = 'admin';
        $logdata['user_id'] = 1;
        $logdata['role'] = 'system';

        $obj->baseInsert('sdb_log_aftermarket',$logdata);
        return $rst ;
    }


    /**
     * 确认换货回调的初步信息处理
     * @param  array  $data_detail 官网售后单ID
     * @return boolean
     * Note:,$refundment_type 0=直接退款,1=退货退款,2=换货退款,3=差价退款
     *  相当于之前中台处理成功  callback/confirmExchange  -> aftermarket::oms_exchange_success
     */
    public function aftermarket_exchange_confirm_success($data_detail = array(),$asmOrderNo,$originalOrderNo){

        $obj = new OrderGoodsSingle();

        //如果是换货，更新订单商品状态
        //将售后回复成初始状态
        $obj->baseUpdate('sdb_order_goods_single',['send_status'=>1,'after_market_status'=>0],'amd_id=:amd_id',[":amd_id"=>$data_detail['id']]);

        //更新订单状态
        $orderCancelStatus = $obj->checkOrderCancelStatus($data_detail['order_id']);
        if($orderCancelStatus){
            $obj->baseUpdate('sdb_order',['status'=>8],'id=:id',[':id'=>$data_detail['order_id']]) ;
        }

        //更新售后单状态
        $obj->baseUpdate('sdb_after_market_doc',['dispose_status'=> 1, 'dispose_time'=>date("Y-m-d H:i:s")],'id=:id',[":id"=>$data_detail['id']]);

    }


    /**
     * 处理换货类型的售后单
     * @param  array $data_detail 官网售后单信息
     * @param  string  $asmOrderNo 中台返回的工单号
     * @param  string  $originalOrderNo 官网订单号
     * @return array
     * Note:中台是换货完成才会关闭工单
     * return code  4001 4002 4003
     */
    public function aftermarket_exchange_close($data_detail = array(),$asmOrderNo,$originalOrderNo){

        $rst['code'] = 1 ;
        $rst['msg'] = '';

        //当前时间
        $current_time = date('Y-m-d H:i:s');

        //step1 获取官网售后单的log
        if($data_detail['doc_log']){
            $doc_log=@unserialize($data_detail['doc_log']);
        }else{
            $doc_log = array();
        }
        $doc_log[count($doc_log)]	=array('time'=>$current_time,'type'=>'换货单关闭','operater'=>'中台接口回调处理');
        $doc_log = serialize($doc_log);

        //step2 更新订单商品表数据
        $obj = new OrderGoods();
        $goods_nums = $data_detail['goods_nums'] ;
        $order_goods_update_data =["exchange_status"=>0,"refund_nums"=> new Expression('refund_nums + '.$goods_nums), "return_goods_nums"=> new Expression('return_goods_nums + '.$goods_nums),"exchange_nums"=> new Expression('exchange_nums + '.$goods_nums) ];
        $order_goods_where_str = 'order_id=:order_id and goods_id=:goods_id';
        $order_goods_where_arr = [":order_id"=>$data_detail['order_id'],':goods_id'=>$data_detail['goods_id']];
        $obj->baseUpdate('sdb_order_goods',$order_goods_update_data,$order_goods_where_str,$order_goods_where_arr);

        //step3 更新售后单的相关字段
        $doc_update_data = ['dispose_status'=>2,'audit_status'=>1,'dispose_time'=>$current_time,'dispose_idea'=>'由于换货单关闭,售后单也随之关闭','doc_log'=>$doc_log] ;
        $obj->baseUpdate('sdb_after_market_doc',$doc_update_data,'id=:id',[':id'=>$data_detail['id']]);

        //step4 执行日志
        $log_data = array(
            'act'=> 'aftermarket_exchange_close',
            'act_name'=>'换货单管理',
            'act_type'=>'关闭换货单',
            'act_content'=>'关闭换货单:'.$asmOrderNo.'售后单号'.$data_detail['id'],
            'order_id'=>$data_detail['order_id'],
            'order_no'=>$data_detail['order_no'] ,
            'note'=>'关闭理由:中台回调处理关闭',
            'content' =>json_encode($data_detail),
            'aftermarket_id'=>$data_detail['id']
        );

        $log_data['username'] = 'admin';
        $log_data['user_id'] = 1;
        $log_data['role'] = 'system';

        $obj->baseInsert('sdb_log_aftermarket',$log_data);

        return $rst ;

    }

    /**
     * 处理维修类型的售后单
     * @param  array $data_detail 官网售后单信息
     * @param  string  $asmOrderNo 中台返回的工单号
     * @param  string  $originalOrderNo 官网订单号
     * @return array
     * Note:中台是维修完成才会关闭工单
     * 维修单只需要直接关闭就可以了 可以无限重复维修
     * return code  5001 5002
     */
    public function aftermarket_repair_close($data_detail = array(),$asmOrderNo,$originalOrderNo){

        $rst['code'] = 1 ;
        $rst['msg'] = '';

        //当前时间
        $current_time = date('Y-m-d H:i:s');

        //step1 获取官网售后单的log
        if($data_detail['doc_log']){
            $doc_log=@unserialize($data_detail['doc_log']);
        }else{
            $doc_log = array();
        }
        $doc_log[count($doc_log)]	=array('time'=>$current_time,'type'=>'维修单关闭','operater'=>'中台接口回调处理');
        $doc_log = serialize($doc_log);

        //step2 更新订单商品表数据
        $obj = new OrderGoods();
        $goods_nums = $data_detail['goods_nums'] ;
        $order_goods_update_data =["maintain_status"=>0,"refund_nums"=> new Expression('refund_nums + '.$goods_nums), "return_goods_nums"=> new Expression('return_goods_nums + '.$goods_nums),"exchange_nums"=> new Expression('exchange_nums + '.$goods_nums) ];
        $order_goods_where_str = 'order_id=:order_id and goods_id=:goods_id';
        $order_goods_where_arr = [":order_id"=>$data_detail['order_id'],':goods_id'=>$data_detail['goods_id']];
        $obj->baseUpdate('sdb_order_goods',$order_goods_update_data,$order_goods_where_str,$order_goods_where_arr);

        //step3 更新售后单的相关字段
        $doc_update_data = ['dispose_status'=>2,'audit_status'=>1,'dispose_time'=>$current_time,'dispose_idea'=>'由于维修单关闭,售后单也随之关闭','doc_log'=>$doc_log] ;
        $obj->baseUpdate('sdb_after_market_doc',$doc_update_data,'id=:id',[':id'=>$data_detail['id']]);

        //step4  维修可以无限维护，没有数量限制，需要更新订单商品状态
        $obj->baseUpdate('sdb_order_goods_single',['after_market_status'=>0],'amd_id =:amd_id',[":amd_id"=>$data_detail['id']]);

        //step5 执行日志
        $log_data = array(
            'act'=> 'aftermarket_exchange_close',
            'act_name'=>'维修单管理',
            'act_type'=>'关闭维修单',
            'act_content'=>'关闭维修单:'.$asmOrderNo.'售后单号'.$data_detail['id'],
            'order_id'=>$data_detail['order_id'],
            'order_no'=>$data_detail['order_no'] ,
            'note'=>'关闭理由:中台回调处理关闭',
            'content' =>json_encode($data_detail),
            'aftermarket_id'=>$data_detail['id']
        );

        $log_data['username'] = 'admin';
        $log_data['user_id'] = 1;
        $log_data['role'] = 'system';

        $obj->baseInsert('sdb_log_aftermarket',$log_data);

        return $rst ;
    }



    /**
     * 关闭售后工单 并处理官网售后的关闭
     * @param  integer $after_market_doc_info 官网售后单基本信息
     * @param  string  $asmOrderNo 中台返回的工单号
     * @param  string  $originalOrderNo 官网订单号
     * @param  string  $status   中台返回的工单处理状态 EXCEPTION：异常工单 CLOSED：已关闭 CANCEL：作废  PROCESSING：处理中 WAITING: 待处理
     * @param  string  $msg      中台返回审核意见  异常和作废的工单会给出指定的审核意见
     * @return array
     * Note:中台处理结束会发送请求 官网接收请求并处理
     * confirm 相当于之前手动确认售后通过 并且回调完成
     * close   相当于之前直接关闭审核通过的售后单
     */
    public function close_after_market_doc($after_market_doc_info=array(),$asmOrderNo,$originalOrderNo,$status='',$msg=''){
        $rst['code'] = 1 ;
        $rst['msg'] = '';

        $log_obj = new SendOmsAfterMarketLog();
        $cond = "asmOrderNo = :asmOrderNo and order_no = :order_no and status = :status ";
        $args =[":asmOrderNo"=>$asmOrderNo,':order_no'=>$originalOrderNo,':status'=>'unlock'];
        $params['cond'] = $cond ;
        $params['args'] = $args ;
        $params['orderby'] = ' id DESC ' ;
        $log_info = $log_obj->findOneByWhere('sdb_send_oms_after_market_log',$params);



        //判断是否为二次请求
        $check_is_second = $this->check_is_asm_second_send($log_info,$status);
        if($check_is_second){
            return $rst ;
        }

        if($status =='PROCESSING'){

            $this->return_asm_deal_log($after_market_doc_info);
        }

        //异常工单和作废工单，都是对应的售后单失败
        if($status =='EXCEPTION' || $status =='CANCEL'){
            //失败需要返回日志
            $this->return_cancel_or_exception($after_market_doc_info,$status,$msg,$log_info);

        }

        //更新中台回调的状态信息
        if($log_info){
            $log_update_data = ['callback_status'=>$status];
            $log_obj->baseUpdate('sdb_send_oms_after_market_log',$log_update_data,'id=:id',[":id"=>$log_info['id']]);
        }

        //同时需要更新售后单的处理时间 和is_user_send_cancel字段回复
        $doc_obj = new AfterMarketDoc();
        $doc_update_data = ['dispose_time'=>date('Y-m-d H:i:s'),'is_user_send_cancel'=>'NO'];
        $doc_obj->baseUpdate('sdb_after_market_doc',$doc_update_data,'id=:id',[":id"=>$after_market_doc_info['id']]) ;


        //处理中和待处理的只要更新中台工单发送记录表
        if($status =='PROCESSING' || $status=='WAITING' ){
            return $rst ;
        }

        //订单完成状态处理退款信息
        if($status=='FINISHED'){

            //处理退款信息
            $refund_rst = $this->doOriginalRoadRefund($after_market_doc_info);

            if(!is_bool($refund_rst) || !$refund_rst){
                $rst['code'] = '400060';
            }

            //处理关闭相关操作
            $this->dealClosed($after_market_doc_info['after_market_type'],$after_market_doc_info,$asmOrderNo,$originalOrderNo);

            //添加完成的日志
            $this->addFinishedLog($after_market_doc_info) ;
            return $rst ;
        }

        //异常工单和作废工单，都是对应的售后单失败
        if($status =='EXCEPTION' || $status =='CANCEL'){
            //失败需要返回日志
            $this->aftermarket_confirm_fail($after_market_doc_info,$asmOrderNo,$originalOrderNo,$msg);
            return $rst ;
        }

        //异常工单 关闭工单 和 作废工单，对应的用户的售后单都做关闭
        if($after_market_doc_info ){

            //处理退款信息
            $refund_rst = $this->doOriginalRoadRefund($after_market_doc_info);

            if(!is_bool($refund_rst) || !$refund_rst){
                $rst['code'] = '400060';
                return $rst ;
            }

            //处理关闭相关操作
            $this->dealClosed($after_market_doc_info['after_market_type'],$after_market_doc_info,$asmOrderNo,$originalOrderNo);

            //处理成功添加用户的相关日志
            $this->return_deal_succ($after_market_doc_info);

        }

        return $rst ;
    }



    /**
     * 处理回调数据
     * @param array $data
     * @return array
     */
    public function dealCallback($data=array()){

        $rst['code'] = 1;

        //step1 基本参数判断
        if(!$data){
            $rst['code'] = '400035';
            $rst['msg'] = '传递参数不完整';
            return $rst ;
        }

        $asmOrderNo = isset($data['asmOrderNo'])?$data['asmOrderNo']:'';
        $status = isset($data['status'])?$data['status']:'';
        $msg = isset($data['msg'])?$data['msg']:'';//异常和作废会给出对应的原因
        $originalOrderNo = isset($data['originalOrderNo'])?$data['originalOrderNo']:'';


        if(!$asmOrderNo || !$status ||!$originalOrderNo){
            $rst['code'] = '400036';
            $rst['msg'] = '传递参数不正确';
            return $rst ;
        }

        //step2 基本信息查询判断
        $doc_obj = new AfterMarketDoc();
        $after_market_doc_info = $doc_obj->getDocInfoByAsmNoAndOrderNo($asmOrderNo,$originalOrderNo);
        if(!$after_market_doc_info){
            $rst['code'] = '400037';
            $rst['msg'] = '未查询到对应的官网售后信息';
            return $rst ;
        }

        //step3 处理售后单信息
        $deal_rst = $this->close_after_market_doc($after_market_doc_info,$asmOrderNo,$originalOrderNo,$status,$msg);
        if($deal_rst['code'] != 1){
            $rst['code'] = $deal_rst['code'];
        }

        //step4 记录日志 需要对新旧任务进行
        $push_task_model = new  PushTask();
        $task_info = $push_task_model->findOneByWhere('sdb_push_task',['cond'=>'asm_order_no=:asm_order_no','args'=>[':asm_order_no'=>$asmOrderNo]]);
        if($task_info){
            $push_task_id = $task_info['id'];
            $log_data['push_task_id'] =$push_task_id;
            $log_data['response_data'] =json_encode($data);
            $log_data['create_time'] = date('Y-m-d H:i:s');
            $log_data['modify_time'] = date('Y-m-d H:i:s');
            $log_model = new TaskWorkOrderCallbackLog();
            $log_model->baseInsert('task_work_order_callback_log',$log_data,'db_log');
        }

        return $rst ;


    }

    /**
     * 前台用户根据商品ID和数目获取实际的可以售后金额
     * @param  integer $order_id   订单ID
     * @param  integer $goods_id   商品ID
     * @param  integer $goods_nums 商品数目
     * @return number
     */
    public function frontUserGetPrice($order_id ,$goods_id,$goods_nums,$after_market_type=1){


        $after_market_info['order_id'] = $order_id;
        $after_market_info['goods_id'] = $goods_id;
        $after_market_info['goods_nums']  = $goods_nums;
        $after_market_info['after_market_type'] = $after_market_type ;

        $order_obj  = new Order();
        $order_info = $order_obj->getInfoById($order_id);

        $asm_obj = new SendToAsm();
        $rst =  $asm_obj->getRefundGoodsPrice($after_market_info,$order_info);
        return  sprintf('%.2f', $rst);
    }


    /**
     * 关闭售后工单 并处理官网售后的关闭
     * @param  integer $after_market_doc_info 官网售后单基本信息
     * @param  string  $data 中台返回信息
     * @return array
     * Note:中台处理结束会发送请求 官网接收请求并处理
     * confirm 相当于之前手动确认售后通过 并且回调完成
     * close   相当于之前直接关闭审核通过的售后单
     */
    public function close_after_market_doc_from_trade_in($after_market_doc_info=array(),$data){

        $rst['code'] = 1 ;
        $rst['msg']  = '';

        $asmOrderNo = isset($data['asmOrderNo']) ? $data['asmOrderNo'] : '';
        $status     = isset($data['status']) ? $data['status'] : '';
        // 异常和作废会给出对应的原因
        $msg        = isset($data['msg']) ? $data['msg'] : '';

        /*$log_obj = new TradeInSendOmsAftermarketLog();
        $cond = "asmOrderNo = :asmOrderNo  and status = :status ";
        $args =[":asmOrderNo"=>$asmOrderNo,':status'=>'unlock'];
        $params['cond'] = $cond ;
        $params['args'] = $args ;
        $params['orderby'] = ' id DESC ' ;
        $log_info = $log_obj->findOneByWhere('sdb_trade_in_send_oms_after_market_log',$params);

        //判断是否为二次请求
        $check_is_second = $this->check_is_asm_second_send($log_info,$status);
        if($check_is_second){
            return $rst ;
        }*/

        //售后关闭货完成状态下不允许在此处更新状态
        /*if($log_info && $status !='CLOSED' && $status != 'FINISHED' ){
            $log_update_data = ['callback_status'=>$status,'update_time'=>date('Y-m-d H:i:s')];
            $log_obj->baseUpdate('sdb_trade_in_send_oms_after_market_log',$log_update_data,'id=:id',[":id"=>$log_info['id']]);
        }*/
    
        //处理中和待处理的不需要其他处理
        if($status =='PROCESSING' || $status=='WAITING' ){
            
            //同时需要更新售后单的处理时间
            $doc_obj = new TradeInAfterMarketDoc();
            $doc_update_data = ['dispose_time'=>date('Y-m-d H:i:s')];
            $doc_obj->baseUpdate('sdb_trade_in_after_market_doc',$doc_update_data,'id=:id',[":id"=>$after_market_doc_info['id']]) ;
            
            return $rst ;
        }

        //异常工单和作废工单，都是对应的售后单失败
        if($status =='EXCEPTION' || $status =='CANCEL'){
            
            // 更新以旧换新售后状态为失败
            $trade_in_aftermarket_fail_res = $this->aftermarket_confirm_fail_from_trade_in($after_market_doc_info,$msg);
            if(!$trade_in_aftermarket_fail_res){
                $rst['code'] = 400061;
    
                return $rst ;
            }
            
        }
    
        // $close_rst = $this->trade_in_aftermarket_close($after_market_doc_info,$data,$log_info);
        // close状态
        // 启用新方法、保留老方法
        $close_rst = $this->trade_in_aftermarket_close_new($after_market_doc_info,$data);
    
        return $rst ;
        
    }

    /**
     * 处理以旧换新关闭2018年
     * @param $doc_info 售后单信息
     * @param $data 中台返回信息
     * Note:FINISHED CLOSED状态会进入此方法
     */
    public function trade_in_aftermarket_close_new($doc_info,$data){

        $rst['code'] =1 ;
        $rst['msg'] = '' ;
        $obj = new TradeInAfterMarketDoc();


        $asmOrderNo = isset($data['asmOrderNo'])?$data['asmOrderNo']:'';
        $status = isset($data['status'])?$data['status']:'';
        if(!$asmOrderNo){
            return $rst ;
        }

        //查询以旧换新申请表
        $trade_in_apply_id = $doc_info['trade_in_apply_id'];
        $trade_in_apply_params['cond'] = 'id =:id';
        $trade_in_apply_params['args'] = [':id'=>$trade_in_apply_id];
        $trade_in_apply_params['fields'] = 'id,mobile,order_id';
        $trade_in_apply_info = $obj->findOneByWhere('sdb_trade_in_apply',$trade_in_apply_params);

        //查询以旧换新对应的信息
        $receive_materiel_no = isset($data['receiveMaterielNo'])?$data['receiveMaterielNo']:'';
        $receive_goods_name = isset($data['receiveGoodsName'])?$data['receiveGoodsName']:'';
        $receive_params['cond'] = 'materiel_no=:materiel_no';
        $receive_params['args'] = [':materiel_no'=>$receive_materiel_no];
        $trade_in_info = $obj->findOneByWhere('sdb_trade_in',$receive_params);

        /*
        if(!$trade_in_info){
           $rst['code'] = '604016';
            $rst['msg'] = getErrorDictMsg('604016');
            return $rst ;
        }*/

        // 开启事务
        $transaction = Yii::$app->db->beginTransaction();
        try {

            //更新售后单信息
            $current_time = date('Y-m-d H:i:s');
            $doc_update_data = ['dispose_status'=>2,'dispose_time'=>$current_time,'dispose_idea'=>'中台处理结束，售后单关闭','update_time'=>$current_time] ;
            $obj->baseUpdate('sdb_trade_in_after_market_doc',$doc_update_data,'id=:id',[':id'=>$doc_info['id']]);

            //以旧换新申请状态更新为已收货
            $where_str = 'id=:id';
            $where_arr = [':id'=>$trade_in_apply_info['id']];
            $update_data['receive_time'] = date('Y-m-d H:i:s');
            $update_data['update_time'] = date('Y-m-d H:i:s');
            if($status =='CANCEL'){
                $update_data['status'] = 'CANCEL';
            }else{
                $update_data['status'] = 'RECEIVED';
            }

            $update_data['receive_materiel_no'] = $trade_in_info?$receive_materiel_no:'' ;
            $update_data['receive_goods_name'] = $trade_in_info?$receive_goods_name:'' ;
            $update_data['act_estimated_price'] = $trade_in_info?$trade_in_info['estimated_price']:0 ;
            $update_data['act_asm_order_no'] = $asmOrderNo ;//实际售后工单号
            $update_data['act_receive_time'] =date('Y-m-d H:i:s');//实际收货时间

            $obj->baseUpdate('sdb_trade_in_apply',$update_data,$where_str,$where_arr);

            //新增订单任务
            $order_id = $trade_in_apply_info['order_id'];
            Yii::$app->OrderTask->addPushTask($order_id);

            // 提交事务
            $transaction->commit();

            return $rst ;

        }catch (Exception $e) {

            //回滚事务
            $transaction->rollback();

            $rst['code'] = '603004';
            $rst['msg'] = getErrorDictMsg('603004');
            return $rst;
        }


    }

    /**
     * 处理以旧换新关闭 作废
     * @param $doc_info 售后单信息
     * @param $data 中台返回信息
     * @param $log_info 中台推送日志信息
     * Note:FINISHED CLOSED状态会进入此方法
     */
    public function trade_in_aftermarket_close($doc_info,$data,$log_info){

        $rst['code'] =1 ;
        $rst['msg'] = 1 ;
        $obj = new TradeInAfterMarketDoc();


        $asmOrderNo = isset($data['asmOrderNo'])?$data['asmOrderNo']:'';
        if(!$asmOrderNo){
            return $rst ;
        }

        $status = $data['status'];
/*
        //判断售后关闭和完成回调是否重复推送
        $repeat_push_params['cond'] = 'asmOrderNo=:asmOrderNo and (callback_status=:callback_status1 or callback_status=:callback_status2)';
        $repeat_push_params['args'] = [':asmOrderNo'=>$asmOrderNo,':callback_status1'=>'CLOSED','callback_status2'=>'FINISHED'];
        $repeat_push_info = $obj->findOneByWhere('sdb_trade_in_send_oms_after_market_log',$repeat_push_params);

        //更新售后状态信息
        $log_update_data = ['callback_status'=>$status,'update_time'=>date('Y-m-d H:i:s')];
        $obj->baseUpdate('sdb_trade_in_send_oms_after_market_log',$log_update_data,'id=:id',[":id"=>$log_info['id']]);

        if($repeat_push_info){
            //不做任何处理
            return $rst ;
        }
*/
        //更新售后单信息
        $current_time = date('Y-m-d H:i:s');
        $doc_update_data = ['dispose_status'=>2,'dispose_time'=>$current_time,'dispose_idea'=>'中台处理结束，售后单关闭','update_time'=>$current_time] ;
        $obj->baseUpdate('sdb_trade_in_after_market_doc',$doc_update_data,'id=:id',[':id'=>$doc_info['id']]);

        //查询以旧换新申请表
        $trade_in_apply_id = $doc_info['trade_in_apply_id'];
        $trade_in_apply_params['cond'] = 'id =:id';
        $trade_in_apply_params['args'] = [':id'=>$trade_in_apply_id];
        $trade_in_apply_params['fields'] = 'id,mobile';
        $trade_in_apply_info = $obj->findOneByWhere('sdb_trade_in_apply',$trade_in_apply_params);



        //获取得到售后中心返回的物料号
        $receive_materiel_no = isset($data['receiveMaterielNo'])?$data['receiveMaterielNo']:'';
        $receive_goods_name = isset($data['receiveGoodsName'])?$data['receiveGoodsName']:'';
        $receive_params['cond'] = 'materiel_no=:materiel_no';
        $receive_params['args'] = [':materiel_no'=>$receive_materiel_no];
        $trade_in_info = $obj->findOneByWhere('sdb_trade_in',$receive_params);
        if(!$trade_in_info){
            //查询不到代金券
            //需要更新申请表的错误日志
            $update_data['admin_note'] = getErrorDictMsg('604016');
            $update_data['update_time'] = date('Y-m-d H:i:s');
            //$update_data['aftermarket_status'] = 'SUCCESS';
            $update_data['voucher_status'] = 'FAIL';
            $update_data['receive_goods_name'] = $receive_goods_name;
            $update_data['receive_materiel_no'] = $receive_materiel_no;
            $where_str = 'id=:id';
            $where_arr = [':id'=>$trade_in_apply_info['id']];
            $obj->baseUpdate('sdb_trade_in_apply',$update_data,$where_str,$where_arr);
            return $rst ;
        }

        //发放代金券
        $voucher_id = $trade_in_info['voucher_id'];
        $user_id = $doc_info['user_id'];
        $record_obj = new  VoucherRecord();

        $voucher_record_rst = $record_obj->createVoucherRecord($voucher_id,$user_id);

        if($voucher_record_rst['code']==1){

            $record_id = $voucher_record_rst['data']['record_id'];

            $act_estimated_price = $voucher_record_rst['data']['award'];

            $update_data['voucher_record_id'] = $record_id;
            $update_data['receive_goods_name'] =$receive_goods_name;
            $update_data['receive_materiel_no'] = $receive_materiel_no ;
            $update_data['act_estimated_price'] = $act_estimated_price ;//实际金额
            //$update_data['aftermarket_status'] = 'SUCCESS';
            $update_data['status'] = 'FINISH';
            $update_data['voucher_status'] = 'SUCCESS';
            $update_data['admin_note'] = isset($data['msg'])?$data['msg']:'';
            $update_data['update_time'] = date('Y-m-d H:i:s');
            $update_data['receive_time'] = date('Y-m-d H:i:s');

            $where_str = 'id=:id';
            $where_arr = [':id'=>$trade_in_apply_info['id']];
            $obj->baseUpdate('sdb_trade_in_apply',$update_data,$where_str,$where_arr);

            //添加短信验证
            $mobile = $trade_in_apply_info['mobile'];
            if($mobile) {
                $use_time_end = $voucher_record_rst['data']['use_time_end'];
                $time_str = date('Y年m月d日', strtotime($use_time_end));
                $msg = '小科已收到您寄回的机器，以旧换新代金券已发放到您的科沃斯官网商城账号中，购买DD35E可立即抵用，点击：t.cn/RCHdKri，有效期至' . $time_str . '。感谢您对小科的支持，祝生活愉快！回复TD退订';
                Yii::$app->SMS->send($mobile, $msg);
            }
        }else{

            //需要更新申请表的错误日志
            $update_data['admin_note'] = isset($voucher_record_rst['msg'])?$voucher_record_rst['msg']:'';
            $update_data['update_time'] = date('Y-m-d H:i:s');
            //$update_data['aftermarket_status'] = 'SUCCESS';
            $update_data['voucher_status'] = 'FAIL';
            $update_data['receive_goods_name'] =$receive_goods_name;
            $update_data['receive_materiel_no'] = $receive_materiel_no ;
            $where_str = 'id=:id';
            $where_arr = [':id'=>$trade_in_apply_info['id']];
            $obj->baseUpdate('sdb_trade_in_apply',$update_data,$where_str,$where_arr);

            $rst['code'] = $voucher_record_rst['code'];
            $rst['msg'] = $voucher_record_rst['msg'];
        }

        return $rst ;
    }

    /**
     * 处理以旧换新回调数据
     * @param array $data
     * @return array
     */
    public function dealTradeInCallback($data=array()){
    
        // 记录回调日志
        $this->addTradeInResponseLog($data);

        $rst['code'] = 1;

        //step1 基本参数判断
        if(!$data){
            $rst['code'] = '400035';
            $rst['msg'] = '传递参数不完整';
            return $rst ;
        }
        $doc_obj = new TradeInAfterMarketDoc();
    
        // 售后单号
        $asmOrderNo = isset($data['asmOrderNo'])?$data['asmOrderNo']:'';
        // 售后处理状态
        $status = isset($data['status'])?$data['status']:'';
        // 异常和作废会给出对应的原因
        $msg = isset($data['msg'])?$data['msg']:'';
        // 返回的物料号
        $receive_materiel_no = isset($data['receiveMaterielNo']) ? $data['receiveMaterielNo'] : '';

        if(!$asmOrderNo || !$status ){
            $rst['code'] = '400036';
            $rst['msg'] = '传递参数不正确';
            return $rst ;
        }

        //step2 基本信息查询判断
        $after_market_doc_info = $doc_obj->getDocInfoByAsmNo($asmOrderNo);
        if(!$after_market_doc_info){
            $rst['code'] = '400037';
            $rst['msg']  = '未查询到对应的官网售后信息';
            return $rst ;
        }
    
        /*
         * TODO: trade_in_after_market_doc 中已经存在不需要再查询
        $receive_materiel_no = isset($data['receiveMaterielNo'])?$data['receiveMaterielNo']:'';
        $receive_materiel_params['cond'] = 'materiel_no=:materiel_no';
        $receive_materiel_params['args'] = [':materiel_no'=>$receive_materiel_no];
        $receive_info = $doc_obj->findOneByWhere('sdb_trade_in',$receive_materiel_params);
        */
        /*
        if($after_market_doc_info['apply_materiel_no'] != $receive_materiel_no){
            $rst['code'] = '400043';
            $rst['msg']  = '物料号在官网不存在';
            return $rst ;
        }*/

        //step3 处理售后单信息
        $deal_rst = $this->close_after_market_doc_from_trade_in($after_market_doc_info,$data);
        if($deal_rst['code'] != 1){
            $rst['code'] = $deal_rst['code'];
        }

        return $rst ;


    }
    
    /**
     * 处理维修回调数据
     * @param  array $data
     * @return array
     */
    public function dealRepairCallback($data){
        
        $rst['code'] = 1;
        $now_time = date("Y-m-d H:i:s");
    
        $task_work_order_callback_log_model = new TaskWorkOrderCallbackLog();
        // 回调日志
        $task_work_order_callback_log_data = [
            'push_task_id' => 0,
            'response_data' => json_encode($data),
            'create_time' => $now_time,
            'modify_time' => $now_time,
        ];
        $task_work_order_callback_log_id = $task_work_order_callback_log_model->saveInfo($task_work_order_callback_log_data);
        
        // 判断参数是否为空
        if(!$data){
            return ['code' => '400035', 'msg' => '传递参数不完整'];
        }
        
        $push_task_model = new PushTask();
        $repair_model = new Repair();
    
        // 售后工单
        $asm_order_no = isset($data['asmOrderNo'])?$data['asmOrderNo']:'';
        $status = isset($data['status'])?$data['status']:'';
        
        if(!$asm_order_no || !$status ){
            return ['code' => '400036', 'msg' => '传递参数不正确'];
        }
        
        // 根据售后工单查询推送任务
        $push_task_info = $push_task_model->getPushTaskInfoByAsmOrderNo($asm_order_no, 'REPAIR');
        if(!$push_task_info){
            return ['code' => '400037', 'msg' => '未查询到对应的官网报修信息'];
        }
    
        // 更新状态
        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新日志中的任务ID
            $task_work_order_callback_log_model->updateInfo($task_work_order_callback_log_id, ['push_task_id' => $push_task_info['id']]);
    
            // 客审回调
            if($status =='PROCESSING'){
        
                // 更新维修状态为处理中
                $repair_model->updateInfo($push_task_info['business_id'], ['status' => 'PROCESSING', 'modify_time' => $now_time]);
            }
            // 售后返回状态为 CLOSED、FINISHED 更新相关业务
            elseif($status =='CLOSED' || $status == 'FINISHED' || $status == 'CANCEL'){
                // 更新任务为已关闭
                $push_task_model->updateInfo($push_task_info['id'], ['status' => 'CLOSED', 'modify_time' => $now_time]);
    
                // 更新维修状态为完成
                $repair_model->updateInfo($push_task_info['business_id'], ['status' => 'FINISHED', 'modify_time' => $now_time]);
            }
        
            // 提交事务
            $transaction->commit();

            return ['code' => 1, 'msg' => '成功'];
        }catch (Exception $e) {
        
            //回滚事务
            $transaction->rollback();
    
            return ['code' => '400044', 'msg' => '维修回调修改状态失败'];
        }
    }

    /**
     * 添加依旧换新售后处理日志
     * @param $asmResponseData 售后回调数据
     */
    public function addTradeInResponseLog($asmResponseData){

        $asm_order_no = isset($asmResponseData['asmOrderNo'])?$asmResponseData['asmOrderNo']:0;
        $push_task_model = new PushTask();
        $push_task_params['cond'] =  'asm_order_no=:asm_order_no';
        $push_task_params['args'] = [':asm_order_no'=>$asm_order_no];
        $push_task_params['fields'] = 'id';
        $push_task_info  = $push_task_model->findOneByWhere('sdb_push_task',$push_task_params);
        $push_task_id = isset($push_task_info['id'])?$push_task_info['id']:0;

        $params = json_encode($asmResponseData);
        $now_time = date('Y-m-d H:i:s');

        //当BaseModel来用
        $obj = new AfterMarketDoc();

        $task_work_order_callback_log_data = [
            'push_task_id' => $push_task_id,
            'response_data' => $params,
            'create_time' => $now_time,
            'modify_time' => $now_time,
        ];

        $obj->baseInsert('task_work_order_callback_log',$task_work_order_callback_log_data,'db_log');
    }


    /**
     * 处理以旧换新回调数据
     * @param array $data
     * @return array
     */
    public function dealFixTradeInCallback($data=array()){

        $rst['code'] = 1;

        //step1 基本参数判断
        if(!$data){
            $rst['code'] = '400035';
            $rst['msg'] = '传递参数不完整';
            return $rst ;
        }

        $asmOrderNo = isset($data['asmOrderNo'])?$data['asmOrderNo']:'';
        $status = isset($data['status'])?$data['status']:'';
        $msg = isset($data['msg'])?$data['msg']:'';//异常和作废会给出对应的原因
        //$originalOrderNo = isset($data['originalOrderNo'])?$data['originalOrderNo']:'';

        if(!$asmOrderNo || !$status ){
            $rst['code'] = '400036';
            $rst['msg'] = '传递参数不正确';
            return $rst ;
        }

        //step2 基本信息查询判断
        $doc_obj = new TradeInAfterMarketDoc();
        $after_market_doc_info = $doc_obj->getDocInfoByAsmNo($asmOrderNo);

        if(!$after_market_doc_info){
            $rst['code'] = '400037';
            $rst['msg'] = '未查询到对应的官网售后信息';
            return $rst ;
        }

        //step3 处理售后单信息
        $deal_rst = $this->close_fix_after_market_doc_from_trade_in($after_market_doc_info,$data);
        if($deal_rst['code'] != 1){
            $rst['code'] = $deal_rst['code'];
        }

        return $rst ;


    }


    /**
     * 关闭售后工单 并处理官网售后的关闭
     * @param  integer $after_market_doc_info 官网售后单基本信息
     * @param  string  $data 中台返回信息
     * @return array
     * Note:中台处理结束会发送请求 官网接收请求并处理
     * confirm 相当于之前手动确认售后通过 并且回调完成
     * close   相当于之前直接关闭审核通过的售后单
     */
    public function close_fix_after_market_doc_from_trade_in($after_market_doc_info=array(),$data){
        $rst['code'] = 1 ;
        $rst['msg'] = '';

        $asmOrderNo = isset($data['asmOrderNo'])?$data['asmOrderNo']:'';
        $status = isset($data['status'])?$data['status']:'';
        $msg = isset($data['msg'])?$data['msg']:'';//异常和作废会给出对应的原因

        $log_obj = new TradeInSendOmsAftermarketLog();
        $cond = "asmOrderNo = :asmOrderNo  and status = :status ";
        $args =[":asmOrderNo"=>$asmOrderNo,':status'=>'unlock'];
        $params['cond'] = $cond ;
        $params['args'] = $args ;
        $params['orderby'] = ' id DESC ' ;
        $log_info = $log_obj->findOneByWhere('sdb_trade_in_send_oms_after_market_log',$params);

        //判断是否为二次请求
        $check_is_second = $this->check_is_asm_second_send($log_info,$status);
        if($check_is_second){
            return $rst ;
        }

        //售后关闭货完成状态下不允许在此处更新状态
        if($log_info && $status !='CLOSED' && $status != 'FINISHED' ){
            $log_update_data = ['callback_status'=>$status,'update_time'=>date('Y-m-d H:i:s')];
            $log_obj->baseUpdate('sdb_trade_in_send_oms_after_market_log',$log_update_data,'id=:id',[":id"=>$log_info['id']]);
        }


        //同时需要更新售后单的处理时间
        $doc_obj = new TradeInAfterMarketDoc();
        $doc_update_data = ['dispose_time'=>date('Y-m-d H:i:s')];
        $doc_obj->baseUpdate('sdb_trade_in_after_market_doc',$doc_update_data,'id=:id',[":id"=>$after_market_doc_info['id']]) ;


        //处理中和待处理的不需要其他处理
        if($status =='PROCESSING' || $status=='WAITING' ){
            $this->addTradeInResponseLog($after_market_doc_info,$data,array());//添加日志
            return $rst ;
        }

        //异常工单和作废工单，都是对应的售后单失败
        if($status =='EXCEPTION' || $status =='CANCEL'){
            //失败
            $this->aftermarket_confirm_fail_from_trade_in($after_market_doc_info,$msg);
            $this->addTradeInResponseLog($after_market_doc_info,$data,array());//添加日志
            return $rst ;
        }

        //close状态
        $close_rst = $this->fix_trade_in_aftermarket_close($after_market_doc_info,$data,$log_info);
        $this->addTradeInResponseLog($after_market_doc_info,$data,$close_rst);//添加日志

        return $rst ;
    }


    /**
     * 处理以旧换新关闭
     * @param $doc_info 售后单信息
     * @param $data 中台返回信息
     * @param $log_info 中台推送日志信息
     * Note:FINISHED CLOSED状态会进入此方法
     */
    public function fix_trade_in_aftermarket_close($doc_info,$data,$log_info){

        $rst['code'] =1 ;
        $rst['msg'] = 1 ;
        $obj = new TradeInAfterMarketDoc();


        $asmOrderNo = isset($data['asmOrderNo'])?$data['asmOrderNo']:'';
        if(!$asmOrderNo){
            return $rst ;
        }

        $status = $data['status'];

        //判断售后关闭和完成回调是否重复推送
        $repeat_push_params['cond'] = 'asmOrderNo=:asmOrderNo and (callback_status=:callback_status1 or callback_status=:callback_status2)';
        $repeat_push_params['args'] = [':asmOrderNo'=>$asmOrderNo,':callback_status1'=>'CLOSED','callback_status2'=>'FINISHED'];
        $repeat_push_info = $obj->findOneByWhere('sdb_trade_in_send_oms_after_market_log',$repeat_push_params);

        //更新售后状态信息
        $log_update_data = ['callback_status'=>$status,'update_time'=>date('Y-m-d H:i:s')];
        $obj->baseUpdate('sdb_trade_in_send_oms_after_market_log',$log_update_data,'id=:id',[":id"=>$log_info['id']]);

        if($repeat_push_info){
            //不做任何处理
            return $rst ;
        }

        //更新售后单信息
        $current_time = date('Y-m-d H:i:s');
        $doc_update_data = ['dispose_status'=>2,'dispose_time'=>$current_time,'dispose_idea'=>'中台处理结束，售后单关闭','update_time'=>$current_time] ;
        $obj->baseUpdate('sdb_trade_in_after_market_doc',$doc_update_data,'id=:id',[':id'=>$doc_info['id']]);

        //查询以旧换新申请表
        $trade_in_apply_id = $doc_info['trade_in_apply_id'];
        $trade_in_apply_params['cond'] = 'id =:id';
        $trade_in_apply_params['args'] = [':id'=>$trade_in_apply_id];
        $trade_in_apply_params['fields'] = 'id,mobile';
        $trade_in_apply_info = $obj->findOneByWhere('sdb_trade_in_apply',$trade_in_apply_params);



        //获取得到售后中心返回的物料号
        $receive_materiel_no = isset($data['receiveMaterielNo'])?$data['receiveMaterielNo']:'';
        $receive_goods_name = isset($data['receiveGoodsName'])?$data['receiveGoodsName']:'';
        $receive_params['cond'] = 'materiel_no=:materiel_no';
        $receive_params['args'] = [':materiel_no'=>$receive_materiel_no];
        $trade_in_info = $obj->findOneByWhere('sdb_trade_in',$receive_params);
        if(!$trade_in_info){
            //查询不到代金券
            //需要更新申请表的错误日志
            $update_data['admin_note'] = getErrorDictMsg('604016');
            $update_data['update_time'] = date('Y-m-d H:i:s');
            //$update_data['aftermarket_status'] = 'SUCCESS';
            $update_data['voucher_status'] = 'FAIL';
            $update_data['receive_goods_name'] = $receive_goods_name;
            $update_data['receive_materiel_no'] = $receive_materiel_no;
            $where_str = 'id=:id';
            $where_arr = [':id'=>$trade_in_apply_info['id']];
            $obj->baseUpdate('sdb_trade_in_apply',$update_data,$where_str,$where_arr);
            //return $rst ;
        }

        //发放代金券
        $voucher_id = $trade_in_info['voucher_id'];
        $user_id = $doc_info['user_id'];


        if(true){
            $record_params = ['cond'=>'voucher_id=:voucher_id and get_user_id =:user_id','args'=>[':voucher_id'=>$voucher_id,':user_id'=>$user_id]];
            $voucher_record_info = $obj->findOneByWhere('sdb_voucher_record',$record_params);

            $record_id = $voucher_record_info['id'];
            $act_estimated_price = $voucher_record_info['award'];

            $update_data['voucher_record_id'] = $record_id;
            $update_data['receive_goods_name'] =$receive_goods_name;
            $update_data['receive_materiel_no'] = $receive_materiel_no ;
            $update_data['act_estimated_price'] = $act_estimated_price ;//实际金额
            $update_data['status'] = 'FINISH';
            $update_data['voucher_status'] = 'SUCCESS';
            $update_data['admin_note'] = isset($data['msg'])?$data['msg']:'';
            $update_data['update_time'] = date('Y-m-d H:i:s');
            $update_data['receive_time'] = date('Y-m-d H:i:s');

            $where_str = 'id=:id';
            $where_arr = [':id'=>$trade_in_apply_info['id']];
            $obj->baseUpdate('sdb_trade_in_apply',$update_data,$where_str,$where_arr);

        }

        return $rst ;
    }

    /**执行原路退回信息
     * @param $after_market_doc_info
     * @return bool
     */
    public function doOriginalRoadRefund($after_market_doc_info){

        if($after_market_doc_info['original_road_refund'] =='ALL'){

            //订单信息
            $order_model = new Order();
            $order_id = $after_market_doc_info['order_id'] ;
            $order_return_fields = 'user_id,pay_type,type,order_no,order_amount';
            $order_info = $order_model->getRowInfoByWhere(array('id'=>$order_id),$order_return_fields);

            //售后单ID
            $business_id = $after_market_doc_info['id'];

            //订单类型
            $order_type = $order_info['type'];

            //订单号
            $order_no  = $order_info['order_no'];

            //获取支付信息
            $pay_info_params['cond'] = 'order_no =:order_no';
            $pay_info_params['args'] = [':order_no'=>$order_no];
            $pay_list_info = $order_model->findAllByWhere('sdb_pay_info',$pay_info_params);
            if(!$pay_list_info){
                return true ;
            }

            if($order_type ==6){
                //预售类型
                foreach($pay_list_info as $v){
                    $refund_model = new Refund();
                    $refund_params = ['pay_order_no'=>$v['pay_order_no'],'payment_id'=>$v['payment_id'],'business_id' => $business_id,'refund_amount' => $v['total_fee'],'total_amount'=> $v['total_fee'],'trade_no' => $v['trade_no'],'channel' => 'MALL','refund_reason' => '退款', 'user_id' => $order_info['user_id'], 'create_time' => $v['create_time']] ;
                    $info = $refund_model->doRefund($refund_params);
                    if(!isset($info['code']) || (isset($info['code']) &&$info['code']!=1) ){
                        return false;
                    }
                }

            }else{
                //其他订单类型
                $pay_info  = $pay_list_info[0];
                $refund_model = new Refund();

                $refund_price = $after_market_doc_info['refund_price'] ;
                $refund_freight = $after_market_doc_info['refund_freight'] ;
                if(is_numeric($refund_freight) && $refund_freight>0){
                    $refund_price = $refund_freight+$refund_price ;
                }

                if($refund_price > 0 ) {
                    $info = $refund_model->doRefund(['pay_order_no' => $pay_info['pay_order_no'], 'payment_id' => $pay_info['payment_id'], 'business_id' => $business_id, 'refund_amount' => $refund_price, 'total_amount' => $pay_info['total_fee'], 'trade_no' => $pay_info['trade_no'], 'channel' => 'MALL', 'refund_reason' => '退款', 'user_id' => $order_info['user_id'], 'create_time' => $pay_info['create_time']]);
                    if (!isset($info['code']) || (isset($info['code']) && $info['code'] != 1)) {
                        return false;
                    }
                }

            }

        }

        return true;
    }

    /**
     * 添加售后FINISHED状态下的日志
     * @param array $doc_info 售后单信息
     * @return integer
     */
    public function addFinishedLog($doc_info){

        //组装日志数据
        $log_data = array(
            'act'=> 'aftermarket_finished',
            'act_name'=>'售后单管理',
            'act_type'=>'完成售后单',
            'act_content'=>'完成了本次售后服务。',
            'order_id'=>$doc_info['order_id'],
            'order_no'=>$doc_info['order_no'] ,
            'note'=>'完成理由:售后中心处理完成',
            'content' =>json_encode($doc_info),
            'aftermarket_id'=>$doc_info['id']
        );

        $log_data['username'] = 'admin';
        $log_data['user_id'] = 1;
        $log_data['role'] = 'system';
        $log_data['addtime'] = date("Y-m-d H:i:s");
        $log_data['ip'] = getIp();

        $model = new LogAftermarket();
        return  $model->baseInsert('sdb_log_aftermarket',$log_data);

    }

    /**
     * 处理售后关闭 在FINISHED和CLOSED状态下都会调用
     * @param $type
     * @param $after_market_doc_info
     * @param $asmOrderNo
     * @param $originalOrderNo
     */
    public function dealClosed($type,$after_market_doc_info,$asmOrderNo,$originalOrderNo){
        $commission_model = new TuijianyouliVoucherCommission();
        if($type ==1){
            //退款
            $this->aftermarket_refund_confirm_success($after_market_doc_info,$asmOrderNo,$originalOrderNo);
            $this->aftermarket_refund_close($after_market_doc_info,$asmOrderNo,$originalOrderNo);

            // 处理佣金
            $commission_model->updateStatusByAftermarket($originalOrderNo) ;
        }else if($type ==2){

            //退货
            $this->aftermarket_return_confirm_success($after_market_doc_info,$asmOrderNo,$originalOrderNo);
            $this->aftermarket_return_close($after_market_doc_info,$asmOrderNo,$originalOrderNo);

            // 处理佣金
            $commission_model->updateStatusByAftermarket($originalOrderNo) ;

        }else if($type ==3){
            //换货
            $this->aftermarket_exchange_confirm_success($after_market_doc_info,$asmOrderNo,$originalOrderNo);
            $this->aftermarket_exchange_close($after_market_doc_info,$asmOrderNo,$originalOrderNo);
        }else if($type ==4){
            //维修  该情况下直接完成售后就OK
            $this->aftermarket_repair_close($after_market_doc_info,$asmOrderNo,$originalOrderNo);
        }else if($type ==5){
            //退差价
            $this->aftermarket_refund_confirm_success($after_market_doc_info,$asmOrderNo,$originalOrderNo);
            $this->aftermarket_refund_close($after_market_doc_info,$asmOrderNo,$originalOrderNo);

            // 处理佣金
            $commission_model->updateStatusByAftermarket($originalOrderNo) ;
        }

    }

}
