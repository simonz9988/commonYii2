<?php

namespace common\models;

use common\components\EthScan;
use common\components\EthWallet;
use common\components\OkexTrade;
use common\components\PrivateUser;
use Yii;

/**
 * This is the model class for table "sea_tx_list".
*/
class PushTask extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_push_task';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [] ;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [] ;
    }

    /**
     * 新增日志
     * @param integer $id
     * @param string $type
     * @param string $business_time
     * @return mixed
     */
    public function addRecord($id,$type,$business_time){
        $params['cond'] = 'business_id =:business_id AND business_type=:business_type';
        $params['args'] = [':business_id'=>$id,':business_type'=>$type];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        //if(!$info){
            $add_data['business_id'] = $id ;
            $add_data['business_type'] = $type ;
            $add_data['business_time'] = $business_time ;
            $add_data['business_timestamp'] = date('Y-m-d H:i:s',$business_time) ;
            $add_data['status'] = 'NOPUSH' ;
            $add_data['push_url'] = '' ;
            $add_data['create_time'] = date('Y-m-d H:i:s') ;
            $add_data['modify_time'] = date('Y-m-d H:i:s') ;
            $this->baseInsert(self::tableName(),$add_data,'db') ;
        //}
        return true ;
    }

    /**
     * 根据指定类型和状态返回对应的任务列表信息
     * @param $type
     * @param $status
     * @param int $limit
     * @return array
     */
    public function getListByType($type,$status,$limit=1000){
        $params['cond'] = 'business_type=:business_type AND status=:status';
        $params['args'] = [':business_type'=>$type,':status'=>$status];
        $params['orderby'] = 'business_time ASC ';
        $params['limit'] = $limit ;
        $list  = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据指定类型、状态和状态返回对应的任务列表信息
     * @param $type_arr
     * @param $status
     * @param admin_allowed
     * @param int $limit
     * @return array
     */
    public function getListByTypeAndAdmin($type_arr,$status,$admin_allowed,$limit=1000){

        if(count($type_arr) ==2){
            $params['cond'] = '( business_type  =:business_type1 OR business_type =:business_type2) AND status=:status AND admin_allowed=:admin_allowed';
            $params['args'] = [':business_type1'=>$type_arr[0],':business_type2'=>$type_arr[1],':status'=>$status,':admin_allowed'=>$admin_allowed];

        }else{
            $params['cond'] = '( business_type  =:business_type1 ) AND status=:status AND admin_allowed=:admin_allowed';
            $params['args'] = [':business_type1'=>$type_arr[0],':status'=>$status,':admin_allowed'=>$admin_allowed];

        }
       $params['orderby'] = 'business_time ASC ';
        $params['limit'] = $limit ;
        $list  = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 更新任务已完成
     * @param $id
     * @return mixed
     */
    public function updateTaskPushed($id){
        $update_data['status'] = 'PUSHED';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$id],'db');
    }

    /**
     * 处理转账的信息
     * @return mixed
     */
    public function dealTrans(){
        //查询未完成的转入金额任务
        $task_list = $this->getListByType('TRANS_CASH','NOPUSH') ;
        if(!$task_list){
            return false ;
        }

        // 充值列表
        $tx_list_model = new TxList();

        // 会员model类
        $member_model = new Member() ;

        // 现金插入类
        $cash_insert_model = new CashInsert();

        foreach($task_list as $v){

            // 获取充值列表信息
            $business_id = $v['business_id'] ;
            $tx_info = $tx_list_model->getInfoById($business_id);

            // 获取充值人员的地址信息
            $from = $tx_info['from'];
            $from_user =  $member_model->getUserInfoByAddress($from);

            if(!$from_user){
                //任务不能更新为已完成，直到绑定用户为止
                //基本不会存在这种情况，只有用户绑定钱包地址才能进行所有的操作
                continue ;
            }

            // 插入充值类型
            $value = $tx_info['value'] ;
            if(!$value){
                //任务更新已完成
                $this->updateTaskPushed($v['id']) ;
                continue ;
            }

            // 插入充值记录表
            $value = $value/1000000000000000000 ;
            $cash_insert_model->addTrans($value,$from_user,$v['id'],date('Y-m-d',($tx_info['timeStamp'])) , $tx_info['timeStamp']) ;

            //同时更新任务已完成
            $this->updateTaskPushed($v['id']) ;
        }
    }

    /**
     * 发送token
     * @return bool
     */
    public function dealSendTokenAndEth(){

        // 严格限定数目，防止执行超时，所以每分钟执行10个
        $task_list = $this->getListByTypeAndAdmin(['SEND_TOKEN','SEND_ETH'],'NOPUSH','Y',10) ;
        if(!$task_list){
            return false ;
        }

        $wallet_component = new EthWallet();
        $wallet_address = $wallet_component->getAddressByPrivateKey(ETH_FROM_KEY) ;

        $scan_component = new EthScan() ;

        $push_task_log_model = new PushTaskLog();

        $tx_model = new TxList();

        $nonce_model = new Nonce();

        //获取最新可以操作的nonce
        $start_nonce = $wallet_component->getNonce($wallet_address) ;

        foreach($task_list as $v){
            $tx_hash = $v['tx_hash'] ;
            if($tx_hash){

                // 查询交易是否完成 完整直接更新任务完成
                $scan_res = $scan_component->getTransByAction($tx_hash,'gettxreceiptstatus') ;
                $scan_res = json_decode($scan_res,true) ;
                $status = isset($scan_res['result']['status']) ? $scan_res['result']['status'] : 0 ;

                if($status == 1 ){
                    //更新为成功
                    $success_data['status'] = 'PUSHED';
                    $success_data['modify_time'] = date('Y-m-d H:i:s') ;
                    $this->baseUpdate(self::tableName(),$success_data,'id=:id',[':id'=>$v['id']],'db');
                }else{
                    //查询tx_list中
                    $tx_params['cond'] = 'hash =:hash';
                    $tx_params['args'] = [':hash'=>$tx_hash];
                    $tx_params['fields'] = '*';
                    $tx_info = $tx_model->findOneByWhere($tx_model::tableName(),$tx_params,$tx_model::getDb());
                    if($tx_info['isError']){
                        //重新申请转账
                        $error_data['tx_hash'] = '';
                        $error_data['modify_time'] = date('Y-m-d H:i:s') ;
                        $this->baseUpdate(self::tableName(),$error_data,'id=:id',[':id'=>$v['id']],'db');
                    }
                }

            }else{
                // 执行交易流程
                $to_address = $v['to_address'] ;

                #TODO  此处同样适用于转账
                if($v['business_type'] =='SEND_TOKEN'){
                    $num = $v['token_num'] ;
                    $res = $wallet_component->sendContract(ETH_FROM_KEY,$to_address,ETH_CONTRACT_HASH,$num,$start_nonce) ;
                }else{
                    $num = $v['eth_num'] ;
                    $res = $wallet_component->sendRow(ETH_FROM_KEY,$to_address,$num,$start_nonce) ;
                }


                if($res){
                    // 更新hash地址
                    $update_data['tx_hash'] = $res ;
                    $update_data['nonce'] = $start_nonce ;// 用户快照当前nonce信息
                    $update_data['modify_time'] = date('Y-m-d H:i:s');
                    $this->baseUpdate(self::tableName(),$update_data,'id=:id',[":id"=>$v['id']],'db');

                    $nonce_add_data['nonce'] = $start_nonce ;
                    $nonce_add_data['tx_hash'] = $res ;
                    $nonce_add_data['create_time'] = date('Y-m-d H:i:s');
                    $this->baseInsert($nonce_model::tableName(),$nonce_add_data,'db') ;
                    $start_nonce++ ;
                }

                $push_task_log_model->addRecord($v['id'],$res) ;
            }
        }
    }

    /**
     * 查询已发放token的总和
     * @return int
     */
    public function getTotalSendToken(){
        $params['cond'] = 'business_type =:business_type';
        $params['args'] = [':business_type'=>'TRANS_TOKEN'] ;
        $params['fields'] = ' sum(token_num) as total';
        $info = $this->findOneByWhere(self::tableName(),$params) ;
        $total = isset($info['total']) ? $info['total'] : 0 ;
        return intval($total) ;
    }

    /**
     * 发送token
     * @return bool
     */
    public function sendEthToAlita(){

        // 严格限定数目，防止执行超时，所以每分钟执行10个
        $task_list = $this->getListByTypeAndAdmin(['SEND_ETH_TO_ALITA'],'NOPUSH','Y',10) ;
        if(!$task_list){
            return false ;
        }

        $wallet_component = new EthWallet();
        $wallet_address = $wallet_component->getAddressByPrivateKey(ETH_FROM_KEY) ;

        $scan_component = new EthScan() ;

        $push_task_log_model = new PushTaskLog();

        $tx_model = new TxList();

        $nonce_model = new Nonce();

        //获取最新可以操作的nonce
        $start_nonce = $wallet_component->getNonce($wallet_address) ;

        foreach($task_list as $v){
            $tx_hash = $v['tx_hash'] ;
            if($tx_hash){

                // 查询交易是否完成 完整直接更新任务完成
                $scan_res = $scan_component->getTransByAction($tx_hash,'gettxreceiptstatus') ;
                $scan_res = json_decode($scan_res,true) ;
                $status = isset($scan_res['result']['status']) ? $scan_res['result']['status'] : 0 ;

                if($status == 1 ){
                    //更新为成功
                    $success_data['status'] = 'PUSHED';
                    $success_data['modify_time'] = date('Y-m-d H:i:s') ;
                    $this->baseUpdate(self::tableName(),$success_data,'id=:id',[':id'=>$v['id']],'db');
                }else{
                    //查询tx_list中
                    $tx_params['cond'] = 'hash =:hash';
                    $tx_params['args'] = [':hash'=>$tx_hash];
                    $tx_params['fields'] = '*';
                    $tx_info = $tx_model->findOneByWhere($tx_model::tableName(),$tx_params,$tx_model::getDb());
                    if($tx_info['isError']){
                        //重新申请转账
                        $error_data['tx_hash'] = '';
                        $error_data['modify_time'] = date('Y-m-d H:i:s') ;
                        $this->baseUpdate(self::tableName(),$error_data,'id=:id',[':id'=>$v['id']],'db');
                    }
                }

            }else{
                // 执行交易流程
                $to_address = $wallet_address ;

                $private_user_components = new PrivateUser();
                $from_address = $v['from_address'] ;
                $from_address_key =$private_user_components->getAutoInsertUserKey($from_address);
                $num = $v['eth_num'] ;
                $res = $wallet_component->sendRow($from_address_key,$to_address,$num,$start_nonce) ;

                if($res){
                    // 更新hash地址
                    $update_data['tx_hash'] = $res ;
                    $update_data['nonce'] = $start_nonce ;// 用户快照当前nonce信息
                    $update_data['modify_time'] = date('Y-m-d H:i:s');
                    $this->baseUpdate(self::tableName(),$update_data,'id=:id',[":id"=>$v['id']],'db');

                    $nonce_add_data['nonce'] = $start_nonce ;
                    $nonce_add_data['tx_hash'] = $res ;
                    $nonce_add_data['create_time'] = date('Y-m-d H:i:s');
                    $this->baseInsert($nonce_model::tableName(),$nonce_add_data,'db') ;
                    $start_nonce++ ;
                }

                $push_task_log_model->addRecord($v['id'],$res) ;
            }
        }
    }

    /**
     * 添加设备地址redis更新的计划任务
     * @param $device_id
     * @return mixed
     */
    public function addPositionTask($device_id){

        $add_data['business_id'] = $device_id ;
        $add_data['business_type'] = 'UPDATE_POSITION' ;
        $add_data['business_time'] = time() ;
        $add_data['business_timestamp'] = date('Y-m-d H:i:s') ;
        $add_data['status'] = 'NOPUSH' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $this->baseInsert(self::tableName(),$add_data);
    }


    /**
     * 添加设备地址redis更新的计划任务
     * @param $device_id
     * @return mixed
     */
    public function addLoadParams($device_id){

        $add_data['business_id'] = $device_id ;
        $add_data['business_type'] = 'LOAD_PARAMS' ;
        $add_data['business_time'] = time() ;
        $add_data['business_timestamp'] = date('Y-m-d H:i:s') ;
        $add_data['status'] = 'NOPUSH' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 添加设备地址redis更新的计划任务
     * @param $device_id
     * @return mixed
     */
    public function addBatteryParams($device_id){

        $add_data['business_id'] = $device_id ;
        $add_data['business_type'] = 'BATTERY_PARAMS' ;
        $add_data['business_time'] = time() ;
        $add_data['business_timestamp'] = date('Y-m-d H:i:s') ;
        $add_data['status'] = 'NOPUSH' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 判断任务是否完成
     * @param $business_id
     * @param $business_type
     * @return bool
     */
    public function checkDoneByTypeAndBusinessId($business_id,$business_type){
        $params['cond'] = 'business_id = :business_id AND business_type=:business_type AND status=:status';
        $params['args'] = [':business_id'=>$business_id,':business_type'=>$business_type,':status'=>'NOPUSH'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? false :true ;
    }

    /**
     * 标记任务完成
     * @param $business_type
     * @param $business_id
     */
    public function deleteTask($business_type,$business_id){
        $update_data['status'] = 'PUSHED';
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        $cond= 'business_id = :business_id AND business_type=:business_type AND status=:status';
        $args = [':business_id'=>$business_id,':business_type'=>$business_type,':status'=>'NOPUSH'];
        return $this->baseUpdate(self::tableName(),$update_data,$cond,$args);
    }

}
