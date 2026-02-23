<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use Web3\Eth;
use Yii;

/**
 * This is the model class for table "sea_tx_list".
*/
class TxList extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_tx_list';
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
     * 数据列表
     * @param array $list
     * @param string $from_key
     * @return mixed
     */
    public function addRecord($list,$from_key){

        // 读取当前时间
        $now = date('Y-m-d H:i:s');

        foreach($list as $v){
            $params['cond'] = 'blockHash = :blockHash';
            $params['args'] = [':blockHash'=>$v['blockHash']];
            $params['fields'] = 'id';
            $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
            if(!$info){
                $add_data['blockNumber'] = $v['blockNumber'] ;
                $add_data['timeStamp'] = $v['timeStamp'] ;
                $add_data['hash'] = $v['hash'] ;
                $add_data['nonce'] = $v['nonce'] ;
                $add_data['blockHash'] = $v['blockHash'] ;
                $add_data['transactionIndex'] = $v['transactionIndex'] ;
                $add_data['from'] = $v['from'] ;
                $add_data['to'] = $v['to'] ;
                $add_data['value'] = $v['value'] ;
                $add_data['gas'] = $v['gas'] ;
                $add_data['gasPrice'] = $v['gasPrice'] ;
                $add_data['isError'] = $v['isError'] ;
                $add_data['txreceipt_status'] = $v['txreceipt_status'] ;
                $add_data['input'] = $v['input'] ;
                $add_data['contractAddress'] = $v['contractAddress'] ;
                $add_data['cumulativeGasUsed'] = $v['cumulativeGasUsed'] ;
                $add_data['gasUsed'] = $v['gasUsed'] ;
                $add_data['confirmations'] = $v['confirmations'] ;
                $add_data['create_time'] = $now ;
                $add_data['modify_time'] = $now ;

                $id = $this->baseInsert(self::tableName(),$add_data,'db') ;

            }else{
                $id = $info['id'] ;
            }

            // 判断是否由外部用户转账
            $wallet_model = new EthWallet();
            $to_address = $wallet_model->getAddressByPrivateKey($from_key);

            if($v['to'] == $to_address){
                // 插入执行任务
                $push_task_model = new PushTask();
                $type = 'TRANS_CASH';//转入金额
                $push_task_model->addRecord($id,$type,$v['timeStamp']) ;
            }

        }

        return true ;
    }

    /**
     * 根据ID获取单条记录信息
     * @param $id
     * @param string $fields
     * @return array
     */
    public function getInfoById($id,$fields='*'){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 返回指定时间段内的总的入金记录
     * @param $date
     * @return integer
     */
    public function getTotalNumByDate($date){

        $start_time = strtotime($date) ;
        $end_time =  $start_time + 86400 ;

        $wallet_component = new EthWallet() ;

        $to_address = $wallet_component->getAddressByPrivateKey(ETH_FROM_KEY) ;
        $params['cond'] = 'timeStamp >=:start_time AND timeStamp <:end_time AND to =:to_address AND value > 0';
        $params['args'] = [':start_time'=>$start_time,':end_time'=>$end_time,':to_address'=>$to_address];
        $params['fields'] = ' count(1) as total ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb()) ;
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 返回指定日期的分页数据
     * @param $date
     * @param $page
     * @return array
     */
    public function getListByDate($date,$page){

        $start_time = strtotime($date) ;
        $end_time =  $start_time + 86400 ;

        $wallet_component = new EthWallet() ;

        $to_address = $wallet_component->getAddressByPrivateKey(ETH_FROM_KEY) ;

        $params['cond'] = 'timeStamp >=:start_time AND timeStamp <:end_time AND to =:to_address AND value > 0';
        $params['args'] = [':start_time'=>$start_time,':end_time'=>$end_time,':to_address'=>$to_address];
        $params['page'] = $page ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb()) ;
        return $list ;
    }

    /**
     * 获取当前日期包含当天的入金总额
     * @param $date
     * @return mixed
     */
    public function getTotalBeforeDate($date){
        $start_time = strtotime($date) ;
        $end_time =  $start_time + 86400 ;

        $wallet_component = new EthWallet() ;

        $to_address = $wallet_component->getAddressByPrivateKey(ETH_FROM_KEY) ;

        $params['cond'] = ' timeStamp <= :end_time AND to =:to_address AND value > 0';
        $params['args'] = [':end_time'=>$end_time,':to_address'=>$to_address];
        $params[] = ' sum(value) as total ';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb()) ;

        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }


    /** 数据列表
    * @param array $list
    * @param string $address
    * @param boolean $is_add_task 是否插入任务
    * @return mixed
    */
    public function addRecordByAddress($list,$address,$is_add_task = false ){

        // 读取当前时间
        $now = date('Y-m-d H:i:s');

        if(!$list){
            return false ;
        }
        foreach($list as $v){
            $params['cond'] = 'blockHash = :blockHash';
            $params['args'] = [':blockHash'=>$v['blockHash']];
            $params['fields'] = 'id';
            $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
            if(!$info){
                $add_data['blockNumber'] = $v['blockNumber'] ;
                $add_data['timeStamp'] = $v['timeStamp'] ;
                $add_data['hash'] = $v['hash'] ;
                $add_data['nonce'] = $v['nonce'] ;
                $add_data['blockHash'] = $v['blockHash'] ;
                $add_data['transactionIndex'] = $v['transactionIndex'] ;
                $add_data['from'] = $v['from'] ;
                $add_data['to'] = $v['to'] ;
                $add_data['value'] = $v['value'] ;
                $add_data['gas'] = $v['gas'] ;
                $add_data['gasPrice'] = $v['gasPrice'] ;
                $add_data['isError'] = $v['isError'] ;
                $add_data['txreceipt_status'] = $v['txreceipt_status'] ;
                $add_data['input'] = $v['input'] ;
                $add_data['contractAddress'] = $v['contractAddress'] ;
                $add_data['cumulativeGasUsed'] = $v['cumulativeGasUsed'] ;
                $add_data['gasUsed'] = $v['gasUsed'] ;
                $add_data['confirmations'] = $v['confirmations'] ;
                $add_data['create_time'] = $now ;
                $add_data['modify_time'] = $now ;

                $id = $this->baseInsert(self::tableName(),$add_data,'db') ;

            }else{
                $id = $info['id'] ;
            }

            // 判断是否由外部用户转账
            $to_address = $address;

            if($v['to'] == $to_address && $is_add_task){
                // 插入执行任务
                $push_task_model = new PushTask();
                $type = 'TRANS_CASH';//转入金额
                $push_task_model->addRecord($id,$type,$v['timeStamp']) ;
            }

        }

        return true ;
    }

    /**
     * 根据入金地址返回未处理的入金列表
     * @param $to_address
     * @return mixed
     */
    public function getUnDealListByToAddress($to_address){
        $params['cond'] = '`to`=:to AND is_deal_cash_in =:is_deal_cash_in';
        $params['args'] = [':to'=>$to_address,'is_deal_cash_in'=>'N'];

        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

}
