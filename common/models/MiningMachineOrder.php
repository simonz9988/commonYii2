<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "sea_mining_machine_order".
 *
 * @property int $id
 * @property int $order_no 订单号
 * @property int $user_id 用户ID
 * @property int $machine_id 机器ID
 * @property int $num 购买数量
 * @property string $order_amount 订单总金额
 * @property string $pay_time 支付时间
 * @property string $status 状态 TO_PAY-待支付 PAYED-已支付
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class MiningMachineOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_no', 'user_id', 'machine_id', 'num'], 'integer'],
            [['user_id', 'machine_id'], 'required'],
            [['order_amount'], 'number'],
            [['pay_time', 'create_time', 'modify_time'], 'safe'],
            [['status'], 'string', 'max' => 50],
            [['order_no'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'user_id' => 'User ID',
            'machine_id' => 'Machine ID',
            'num' => 'Num',
            'order_amount' => 'Order Amount',
            'pay_time' => 'Pay Time',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 创建交易订单号
     * @return mixed
     */
    public function createOrderNo()
    {

        $order_no = date("YmdHis");
        $order_no .= mt_rand(1000, 9999);

        $params['cond'] = 'order_no =:order_no';
        $params['args'] = [':order_no' => $order_no];
        $info = $this->findOneByWhere(self::tableName(), $params, self::getDb());
        if ($info) {
            return $this->createOrderNo();
        }
        return $order_no;
    }

    /**
     * 创建订单
     * @param $machine_id
     * @param $machine_num
     * @param $user_id
     * @return mixed
     */
    public function createOrder($machine_id, $machine_num, $user_id)
    {

        //  判断矿机信息是否存在
        $machine_obj = new MiningMachine();
        $machine_info = $machine_obj->getInfoById($machine_id);
        if (!$machine_info) {
            $this->setError('100027');
            return false;
        }

        // 判断状态是否有效
        if ($machine_info['status'] != 'ENABLED') {
            $this->setError('100028');
            return false;
        }

        //  判断订单数量是否符合要求
        if ($machine_num <= 0) {
            $this->setError('100030');
            return false;
        }

        // 判断库存
        $ext = $machine_info['store_num'] - $machine_num;
        if ($ext < 0) {
            $this->setError('100029');
            return false;
        }

        $machine_price = $machine_info['price'];
        $activity_id = $machine_info['activity_id'];

        // 查询活动信息
        $activity_obj = new MiningMachineActivity();
        $activity_info = $activity_obj->getInfoById($activity_id);
        // 保留2位，四舍五入
        $fee = $machine_price * $machine_info['fee'] * $machine_num / 100;
        $fee = round($fee, 2);

        // 手续费扣减为0
        $fee = 0 ;

        $machine_amount = $machine_price * $machine_num;
        $order_amount = $machine_amount + $fee;


        // 当前时间
        $now = date('Y-m-d H:i:s');

        // 计算总算力
        $order_total_calc_power = $machine_info['calc_power'] * $machine_num;
        if ($order_total_calc_power > $activity_info['left_total']) {
            $this->setError('100056');
            return false;
        }


        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        $member_obj = new Member();
        $user_info = $member_obj->getUserInfoById($user_id);
        try {
            // 新增数据
            $order_data['order_no'] = $this->createOrderNo();
            $order_data['user_id'] = $user_id;
            $order_data['user_level'] = $user_info['user_level'];
            $order_data['user_root_path'] = $user_info['user_root_path'];
            $order_data['machine_id'] = $machine_id;
            $order_data['activity_id'] = $activity_id;
            $order_data['num'] = $machine_num;
            $order_data['price'] = $machine_price;
            $order_data['total_calc_power'] = $order_total_calc_power;
            $order_data['fee'] = $fee;
            $order_data['machine_amount'] = $machine_amount;
            $order_data['order_amount'] = $order_amount;
            $order_data['pay_time'] = NULL;
            $order_data['status'] = 'TO_PAY';
            $order_data['create_time'] = $now;
            $order_data['modify_time'] = $now;
            $this->baseInsert(self::tableName(), $order_data);

            // 更新库存
            $machine_update_data['store_num'] = new Expression("store_num - " . $machine_num);
            $machine_update_data['modify_time'] = $now;
            $machine_obj->baseUpdate($machine_obj::tableName(), $machine_update_data, 'id=:id AND store_num - ' . $machine_num . ' >=0', [':id' => $machine_id]);

            // 更新剩余算力
            $activity_update['left_total'] = new Expression("left_total - " . $order_total_calc_power);
            $activity_update['modify_time'] = $now;

            $activity_cond = " id=:id AND left_total - " . $order_total_calc_power . " >=0 ";
            $activity_args[':id'] = $activity_info['id'];
            $activity_obj->baseUpdate($activity_obj::tableName(), $activity_update, $activity_cond, $activity_args);
            $transaction->commit();
            return $order_data['order_no'];
        } catch (\Exception $e) {

            $transaction->rollBack();
            $this->setError(100031);
            return false;
        }
    }

    /**
     * 自动需要订单
     * @return mixed
     */
    public function autoCancelOrder()
    {
        $site_config = new SiteConfig();
        $auto_cancel_order_second = $site_config->getByKey('auto_cancel_order_second');
        //状态 TO_PAY-待支付 PAYED-已支付 CANCEL-用户取消 OUT_TIME-超时取消
        $params['cond'] = 'status=:status AND create_time <:create_time';
        $create_time = date('Y-m-d H:i:s', time() - $auto_cancel_order_second);
        $params['args'] = [':status' => 'TO_PAY', ':create_time' => $create_time];
        $list = $this->findAllByWhere(self::tableName(), $params, self::getDb());
        if (!$list) {
            return false;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');

        $machine_obj = new MiningMachine();
        $activity_obj = new MiningMachineActivity();
        foreach ($list as $v) {
            //  更新订单状态
            $order_update_data['status'] = 'OUT_TIME';
            $order_update_data['modify_time'] = $now;
            $update_res = $this->baseUpdate(self::tableName(), $order_update_data, 'id=:id AND status=:status1', [':id' => $v['id'], ':status1' => 'TO_PAY']);

            // 回滚库存
            if ($update_res) {
                $machine_update_data['store_num'] = new Expression("store_num + " . $v['num']);
                $machine_update_data['modify_time'] = $now;
                $machine_obj->baseUpdate($machine_obj::tableName(), $machine_update_data, 'id=:id', [':id' => $v['machine_id']]);
            }

            // 回滚算力
            $activity_update_data['left_total'] = new Expression("left_total + " . $v['total_calc_power']);
            $activity_update_data['modify_time'] = $now;
            $activity_obj->baseUpdate($activity_obj::tableName(), $activity_update_data, 'id=:id', [':id' => $v['activity_id']]);
        }
    }

    /**
     * 返回所有的类型
     * @return array
     */
    public function returnAllType()
    {

        //状态 TO_PAY-待支付 PAYED-已支付 CANCEL-用户取消 OUT_TIME-超时取消
        $type['TO_PAY'] = '待支付';
        $type['PAYED'] = '已支付';
        $type['CANCEL'] = '用户取消';
        $type['OUT_TIME'] = '超时取消';
        return $type;
    }

    /**
     * 判断传入的类型值
     * @param $type
     * @return mixed
     */
    public function checkType($type)
    {

        if (!$type) {
            return true;
        }
        $all_type = $this->returnAllType();
        return isset($all_type[$type]) ? true : false;
    }

    /**
     * 获取状态名称
     * @param $status
     * @return mixed
     */
    public function getStatusName($status)
    {

        $type_arr = $this->returnAllType();
        $status_name = isset($type_arr[$status]) ? $type_arr[$status] : '';
        return $status_name;
    }

    /**
     * 根据类型返回总数据
     * @param $user_id
     * @param $type
     * @return mixed
     */
    public function getTotalNumByType($user_id, $type)
    {
        $cond = ' user_id =:user_id';

        $params['args'][':user_id'] = $user_id;
        if ($type) {
            $cond .= ' AND status=:status';
            $params['args'][':status'] = $type;
        }

        $params['cond'] = $cond;
        $params['fields'] = ' count(1) as total ';
        $info = $this->findOneByWhere(self::tableName(), $params, self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0;
    }

    /**
     * 根据类型和分页返回列表信息
     * @param $user_id
     * @param $type
     * @param $page
     * @param $page_num
     * @return mixed
     */
    public function getListByPageAndType($user_id, $type, $page, $page_num)
    {

        if ($type) {
            $params['cond'] = 'user_id = :user_id AND status=:status';
            $params['args'] = [':user_id' => $user_id, ':status' => $type];
        } else {
            $params['cond'] = 'user_id =:user_id';
            $params['args'] = [':user_id' => $user_id];
        }

        $params['page']['curr_page'] = $page;
        $params['page']['page_num'] = $page_num;
        $params['orderby'] = 'id desc';
        $list = $this->findAllByWhere(self::tableName(), $params, self::getDb());
        return $list;
    }

    /**
     * 格式化处理返回订单列表页的信息
     * @param $list
     * @return mixed
     */
    public function formatListInfo($list)
    {

        if (!$list) {
            return [];
        }

        $res = [];

        $machine_list = [];
        $activity_list = [];
        $machine_obj = new MiningMachine();
        $activity_obj = new MiningMachineActivity();
        foreach ($list as $v) {
            $data['order_no'] = $v['order_no'];
            $machine_id = $v['machine_id'];
            if (isset($machine_list[$machine_id])) {
                $machine_info = $machine_list[$machine_id];
            } else {
                $machine_info = $machine_obj->getInfoById($machine_id);
                $machine_list[$machine_id] = $machine_info;
            }

            $activity_id = $v['activity_id'];
            if (isset($activity_list[$activity_id])) {
                $activity_info = $activity_list[$activity_id];
            } else {
                $activity_info = $activity_obj->getInfoById($activity_id);
                $activity_list[$activity_id] = $activity_info;
            }


            $data['machine_name'] = $machine_info['title'];
            $data['activity_name'] = $activity_info['name'];
            $data['price'] = formatNumZero($v['price']);
            $data['num'] = $v['num'];
            $data['create_time'] = $v['create_time'];
            $data['order_amount'] = formatNumZero($v['order_amount']);
            $data['status_name'] = $this->getStatusName($v['status']);

            $res[] = $data;
        }

        return $res;
    }

    /**
     * 执行付款操作
     * @param $order_info
     * @param $user_id
     * @return mixed
     */
    public function doPay($order_info, $user_id)
    {

        // 订单总额
        $order_amount = $order_info['order_amount'];

        //查询用户信息
        $member_obj = new Member();
        $user_info = $member_obj->getUserInfoById($user_id);
        $balance = $user_info['balance'];

        if ($balance < $order_amount) {
            $this->setError('100036');
            return false;
        }
        // 用户分享路径
        $user_root_path = $user_info['user_root_path'];

        // 开启事物
        $connection = Yii::$app->db;
        $transaction = $connection->beginTransaction();
        //当前时间
        $now = date('Y-m-d H:i:s');

        // 查询用户每日能量信息
        $user_power_obj = new MiningMachineUserPower();
        $user_power_info = $user_power_obj->getInfoByActivityAndMachine($user_id, $order_info['activity_id'], $order_info['machine_id']);

        // 用户购买总算力
        $add_total_power = $order_info['total_calc_power'];

        try {

            // 更新订单信息
            $order_update_data['status'] = 'PAYED';
            $order_update_data['modify_time'] = $now;
            $order_update_data['pay_time'] = $now;
            $order_cond = 'id=:id AND status=:status1';
            $order_args = [':id' => $order_info['id'], ':status1' => "TO_PAY"];
            $this->baseUpdate(self::tableName(), $order_update_data, $order_cond, $order_args);

            // 账户余额更新
            $user_update_data['balance'] = new Expression('balance - ' . $order_amount);
            $user_update_data['modify_time'] = $now;
            $user_cond = 'id=:id AND balance - ' . $order_amount . ' > 0';
            $user_args = [':id' => $user_id];
            $this->baseUpdate('sea_user', $user_update_data, $user_cond, $user_args);

            // 更新用户资产
            $balance_obj = new MiningMachineUserBalance();
            $balance_obj->updateByBuyMachine($user_id, $order_amount);

            // 更新资金消耗记录
            $balance_record_obj = new MiningMachineUserBalanceRecord();
            $balance_record_obj->addRecordByOrder($user_id, $order_amount);

            // 增加用户合计算力信息
            if ($user_power_info) {
                // 更新
                $user_power_obj->updateInfo($user_power_info['id'], $add_total_power);
            } else {
                // 新增
                $machine_id = $order_info['machine_id'];
                $user_power_obj->insertRecord($user_id, $order_info['activity_id'], $add_total_power, $machine_id);
            }

            // 订单ID
            $order_id = $order_info['id'];
            // 处理团队金额
            $member_obj->updateTeamTotalByUserRootPath($user_root_path, $add_total_power,$user_id);

            // 处理分享收益
            $member_obj->dealShareEarn($user_info, $order_amount, $order_id);

            // 处理团队收益
            $member_obj->dealTeamEarn($user_info, $order_amount,$add_total_power);

            $transaction->commit();
            return true;
        } catch (\Exception $e) {

            $transaction->rollBack();
            $this->setError(100037);
            return false;
        }

    }

    /**
     * 获取所有已购买订单的总人数
     * @param $user_id
     * @return mixed
     */
    public function getAllBuySonNum($user_id)
    {


        $params['cond'] = 'user_root_path like "%--' . $user_id . '--%" AND id>:id AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':id' => 0, ':status' => 'PAYED', ':is_deleted' => 'N'];
        $params['fields'] = 'count(1) as total';
        $params['group_by'] = 'user_id';
        $list = $this->findAllByWhere(self::tableName(), $params, self::getDb());

        return count($list);
    }

    /**
     * 获取所有子节点购买的总算力
     * @param $user_id
     * @return mixed
     */
    public function getTotalSonPower($user_id)
    {

        $params['cond'] = 'user_root_path like "%--' . $user_id . '--%" AND id>:id AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':id' => 0, ':status' => 'PAYED', ':is_deleted' => 'N'];
        $params['fields'] = 'sum(total_calc_power) as total';
        $info = $this->findOneByWhere(self::tableName(), $params, self::getDb());
        $total = $info && !is_null($info['total']) ? $info['total'] : 0;
        return $total;
    }

    /**
     * 获取总的邀请列表
     * @param $user_id
     * @param $user_level
     * @param $max_level
     * @return mixed
     */
    public function getInviteList($user_id, $user_level, $max_level)
    {

        $params['cond'] = 'user_root_path like "%--' . $user_id . '--%" AND user_level <=:user_level AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':user_level' => $max_level, ':status' => 'PAYED', ':is_deleted' => 'N'];
        $params['fields'] = 'id,user_id,order_no,order_amount,pay_time,create_time,user_level,total_calc_power,num';
        $params['orderby'] = 'id desc';
        $list = $this->findAllByWhere(self::tableName(), $params, self::getDb());
        if ($list) {
            $member_obj = new Member();
            $earn_obj = new MiningMachineEarn();
            $mobile_list = [];
            
            $to_user_id  = $user_id;
            foreach ($list as $k => $v) {
                $user_id = $v['user_id'];

                if (!isset($mobile_list[$user_id])) {

                    $user_info = $member_obj->getUserInfoById($user_id, 'mobile');
                    $mobile = $user_info ? $user_info['mobile'] : '';
                    $mobile = formatPhone($mobile);
                    $mobile_list[$user_id] = $mobile;
                } else {
                    $mobile = $mobile_list[$user_id];
                }

                
                

                $list[$k]['mobile'] = $mobile;
                $list[$k]['create_time'] = date('Y-m-d', strtotime($v['create_time']));

                // 查看从属关系 直接还是间接
                $list[$k]['relation_name'] = $v['user_level'] - $user_level == 1 ? '直接' : '间接';

                // 查询佣金
                $order_id = $v['id'];
                $list[$k]['total_earn'] = $earn_obj->getTotalByOrderId($to_user_id, $order_id);
            }
        }
        return $list;
    }

    /**
     * 通过算力管理页面返回所有的列表信息
     * @param $user_id
     * @param $calc_page
     * @return mixed
     */
    public function getListByCalcPage($user_id,$calc_page)
    {
        $machine_obj = new MiningMachine();
        $machine_list = $machine_obj->getCurrentUsefulList(true);

        if(!$machine_list){
            return false ;
        }

        $machine_ids = [] ;
        foreach($machine_list as $v){
            $machine_ids[] = $v['id'];
        }

        //1. 存储空间大小 购买日期 状态
        //2. 状态分为 待上架 使用中 暂停 终止
        //3. 默认是待上架
        //4. 首次发放变成使用中
        //5. 合约到期是暂停

        // 矿机租赁(保底)
        $params['cond'] = ' user_id=:user_id AND machine_id in('.implode(',',$machine_ids).') AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':status'=>'PAYED',':user_id'=>$user_id,':is_deleted'=>'N'];
        $params['fields'] = '*';
        $params['page']['curr_page'] = $calc_page ;
        $list =  $this->findAllByWhere(self::tableName(),$params,self::getDb());
        $res = [] ;
        if($list){
            foreach($list as $k=>$v){
                $temp_data = [] ;
                //$list[$k]['name'] = '矿机租赁(保底)';

                $temp_data['total_calc_power'] = $v['total_calc_power'];
                $temp_data['create_time'] = date('Y-m-d',strtotime($v['create_time']));
                $temp_data['status_name'] = $this->getStatusByOrderInfo($v);
                $res[] = $temp_data ;
            }
        }
        return $res ;
    }

    /**
     * 根据订单新新返回具体状态
     * @param $order_info
     * @return mixed
     */
    public function getStatusByOrderInfo($order_info){

        //2. 状态分为 待上架 使用中 暂停 终止
        //3. 默认是待上架
        //4. 首次发放变成使用中
        //5. 合约到期是暂停
        $order_id = $order_info['id'];
        // 判断是否合约到期  发放收益第一天
        $status_name = '待上架';

        $ext = strtotime(date('Y-m-d 00:00:00')) - strtotime(date('Y-m-d 00:00:00',strtotime($order_info['create_time']))) ;
        $ext = $ext/86400 ;

        $power_obj = new MiningMachineUserPower();

        $user_id = $order_info['user_id'];
        $activity_id = $order_info['activity_id'];
        $machine_id = $order_info['machine_id'];

        $machine_obj = new MiningMachine();
        $machine_info  = $machine_obj->getInfoById($machine_id,'limit_day');
        $limit_day = $machine_info['limit_day'] ;
        if($ext >$limit_day){
            return  '暂停';
        }

        $power_list = $power_obj->getListByActivityAndMachine($user_id,$activity_id,$machine_id);
        $power_ids = [] ;
        if(!$power_list){
            return  $status_name;
        }

        foreach ($power_list as $v){
            $power_ids[] = $v['id'] ;
        }

        $earn_obj = new MiningMachineEarn();
        $earn_params['cond'] = ' user_id=:user_id AND type =:type AND business_id in('.implode(',',$power_ids).')';
        $earn_params['args'] = [':user_id'=>$user_id,':type'=>'GUDING'];
        $eran_info = $earn_obj->findOneByWhere($earn_obj::tableName(),$earn_params,self::getDb());
        if($eran_info){
            return  '使用中';
        }

        return $status_name ;

    }

    /**
     * 算力页面计算订单总数
     * @param $user_id
     * @return bool|int
     */
    public function getTotalByCalcPage($user_id){

        $machine_obj = new MiningMachine();
        $machine_list = $machine_obj->getCurrentUsefulList();

        if(!$machine_list){
            return false ;
        }

        $machine_ids = [] ;
        foreach($machine_list as $v){
            $machine_ids[] = $v['id'];
        }

        // 矿机租赁(保底)
        $params['cond'] = ' user_id=:user_id AND machine_id in('.implode(',',$machine_ids).') AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':status'=>'PAYED',':user_id'=>$user_id,':is_deleted'=>'N'];
        $params['fields'] = 'count(1) as total';
        $info =  $this->findOneByWhere(self::tableName(),$params,self::getDb());

        return $info && !is_null($info['total']) ? $info['total'] : 0  ;
    }

    /**
     * 根据用户ID返回用户的总算力
     * @param $user_id
     * @return mixed
     */
    public function getTotalCalcPowerByUserId($user_id){

        $params['cond'] = ' user_id=:user_id AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':status'=>'PAYED',':user_id'=>$user_id,':is_deleted'=>'N'];
        $params['fields'] = 'sum(total_calc_power) as sum_power';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['sum_power']) ?$info['sum_power'] : 0 ;
    }

    /**
     * 根据用户ID返回用户的总算力
     * @param $user_id
     * @param $activity_id
     * @return mixed
     */
    public function getTotalCalcPowerByUserIdAndActivityId($user_id,$activity_id){

        $params['cond'] = ' user_id=:user_id AND status=:status AND is_deleted=:is_deleted AND activity_id =:activity_id';
        $params['args'] = [':status'=>'PAYED',':user_id'=>$user_id,':is_deleted'=>'N',':activity_id'=>$activity_id];
        $params['fields'] = 'sum(total_calc_power) as sum_power';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['sum_power']) ?$info['sum_power'] : 0 ;
    }

    /**
     * 获取当前活动已经领取的总算力
     * @param $activity_id
     * @return mixed
     */
    public function getTotalCalcPowerByUActivityId($activity_id){

        $params['cond'] = ' status=:status AND is_deleted=:is_deleted AND activity_id =:activity_id';
        $params['args'] = [':status'=>'PAYED',':is_deleted'=>'N',':activity_id'=>$activity_id];
        $params['fields'] = 'sum(total_calc_power) as sum_power';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['sum_power']) ?$info['sum_power'] : 0 ;
    }


    /**
     * 根据活动列表和用户ID返回总算力和有效算例信息
     * @param $activity_list
     * @param $user_id
     * @return mixed
     */
    public function getTotalCalcInfoByActivityAndUserId($activity_list,$user_id){

        $user_total_power = 0 ;
        $user_total_useful_power = 0 ;
        if(!$activity_list){
            return compact('user_total_power','user_total_useful_power');
        }
        //$daily_add_obj = new MiningMachineActivityDailyAdd();
        foreach($activity_list as $v){

            //获取用户指定活动的总算里信息
            $activity_id = $v['id'];
            $user_activity_total = $this->getTotalCalcPowerByUserIdAndActivityId($user_id ,$activity_id );
            $user_total_power += $user_activity_total ;


            // 获取当前活动的所有用户获得的总能量
            //$all_user_activity_total = $this->getTotalCalcPowerByUActivityId($activity_id );
            // 直接取活动的总算力
            $all_user_activity_total = $v['total'];

            // 理论上最大的有效算力
            $max_useful_power =  $v['total']*$v['useful_percent']/100 ;

            // 查询每天释放的算力
            $ext_day  =  strtotime(date('Y-m-d 00:00:00'))- strtotime(date('Y-m-d 00:00:00',strtotime($v['start_time']) )) ;
            $ext_day = $ext_day/86400 ;

            //$daily_add_total_power = $v['daily_add']*$ext_day ;
            $daily_add_total_power = $v['total_supply'] ;
            //$daily_add_total_power = $daily_add_obj->getTotalByActivityId($activity_id,strtotime(date('Y-m-d 00:00:00')));
            $real_user_activity_total = $all_user_activity_total > $daily_add_total_power ? $daily_add_total_power : $all_user_activity_total ;

            $final_power = $real_user_activity_total > $max_useful_power  ? $max_useful_power : $real_user_activity_total;

            $temp_user_total_useful_power = $all_user_activity_total > 0 ?$final_power*($user_activity_total/$all_user_activity_total) : 0 ;

            $user_total_useful_power += $temp_user_total_useful_power ;
        }

        $user_total_power = numberSprintf($user_total_power,6);
        $user_total_useful_power = numberSprintf($user_total_useful_power,6);
        return compact('user_total_power','user_total_useful_power');
    }
}