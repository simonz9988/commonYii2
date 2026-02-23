<?php

namespace common\models;

use common\components\EthScan;
use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sea_user_wallet".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $address 钱包地址
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class UserWallet extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_user_wallet';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['address'], 'string', 'max' => 255],
            [['is_deleted'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'address' => 'Address',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据地址返回有效信息
     * @param $address
     * @param $fields
     * @return mixed
     */
    public function getInfoByAddress($address,$fields='*'){
        $params['cond'] = ' address=:address AND is_deleted=:is_deleted';
        $params['args'] = [':address'=>$address,':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取允许使用的
     * @param integer $loop
     * @return mixed
     */
    public function getUsefulInfo($loop=0){

        $loop++ ;

        // 限定循环十次
        if($loop > 10){
            return false ;
        }
        $params['cond'] = 'user_id = :user_id AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>0,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if(!$info){
            return $this->getUsefulInfo($loop);
        }

        return $info ;
    }

    /**
     * 同步余额信息
     * @return mixed
     */
    public function syncBalance(){

        $redis_obj = new MyRedis() ;
        $redis_key = 'SyncBalance:StartId';
        if($redis_obj->checkKeyExists($redis_key)){
            $start_id = $redis_obj->get($redis_key);
        }else{
            $start_id =1 ;
        }

        // 每次处理5条，接口访问频率限制
        $params['cond'] = 'id >= :id AND is_deleted=:is_deleted AND user_id >0';
        $params['args'] = [':id'=>$start_id,':is_deleted'=>'N'];
        $params['limit'] = 5;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return false ;
        }

        foreach($list as $v){

            $address = $v['address'] ;
            $this->dealBalanceByAddress($address,$v['user_id']);
            $redis_obj->set($redis_key,$v['id'],60 );
            sleep(1);
        }
    }

    /**
     * 根据地址和用户ID处理用户余额信息
     * @param $trans_eth_address
     * @param $user_id
     * @return mixed
     */
    public function dealBalanceByAddress($trans_eth_address,$user_id){

        $trans_eth_address = strtolower($trans_eth_address);

        $model = new EthScan();
        $list = $model->getUsdtTxListByAddress($trans_eth_address);

        // 落盘交易记录
        $tx_model  = new TxList();
        $tx_model->addRecordByAddress($list,$trans_eth_address,false);

        // 查询未处理的记录
        $tx_list = $tx_model->getUnDealListByToAddress($trans_eth_address);

        if($tx_list){
            $site_config = new SiteConfig() ;
            $member_obj = new Member();
            $percent = $site_config->getByKey('machine_cash_in_percent');
            $usdt_percent = $site_config->getByKey('usdt_percent');

            foreach($tx_list as $v){

                $value = $v['value']/$usdt_percent;
                $value = $percent*$value ;
                $blockHash = $v['blockHash'];
                // 取2位小数 四舍五入
                $member_obj->addMiningMachineCashInRecord($user_id,$value,$blockHash);

                //更新为已处理
                $tx_update_data['is_deal_cash_in'] = 'Y';
                $tx_update_data['modify_time'] = date('Y-m-d H:i:s');
                $tx_model->baseUpdate($tx_model::tableName(),$tx_update_data,'id=:id',[':id'=>$v['id']]);
            }
        }
    }

    /**
     * 同步余额信息
     * @return mixed
     */
    public function syncBalanceFromRobot(){

        $redis_obj = new MyRedis() ;
        $redis_key = 'SyncBalance:StartId';
        if($redis_obj->checkKeyExists($redis_key)){
            $start_id = $redis_obj->get($redis_key);
        }else{
            $start_id =1 ;
        }

        // 每次处理5条，接口访问频率限制
        $params['cond'] = 'id >= :id AND is_deleted=:is_deleted AND user_id >0';
        $params['args'] = [':id'=>$start_id,':is_deleted'=>'N'];
        $params['limit'] = 1000;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return false ;
        }

        foreach($list as $v){

            $address = $v['address'] ;
            $this->dealBalanceByAddressFromRobot($address,$v['user_id']);
            $redis_obj->set($redis_key,$v['id'],600 );
            sleep(1);
        }
    }

    /**
     * 根据地址和用户ID处理用户余额信息
     * @param $trans_eth_address
     * @param $user_id
     * @return mixed
     */
    public function dealBalanceByAddressFromRobot($trans_eth_address,$user_id){

        $trans_eth_address = strtolower($trans_eth_address);

        $model = new EthScan();
        $list = $model->getUsdtTxListByAddress($trans_eth_address);

        // 落盘交易记录
        $tx_model  = new TxList();
        $tx_model->addRecordByAddress($list,$trans_eth_address,false);

        // 查询未处理的记录
        $tx_list = $tx_model->getUnDealListByToAddress($trans_eth_address);

        if($tx_list){
            $site_config = new SiteConfig() ;
            $member_obj = new Member();
            $percent = $site_config->getByKey('robot_cash_in_percent');
            $usdt_percent = $site_config->getByKey('usdt_percent');

            foreach($tx_list as $v){

                $value = $v['value']/$usdt_percent;
                $value = $percent*$value ;
                $blockHash = $v['blockHash'];
                // 取2位小数 四舍五入
                $member_obj->addRobotCashInRecord($user_id,$value,$blockHash);

                //更新为已处理
                $tx_update_data['is_deal_cash_in'] = 'Y';
                $tx_update_data['modify_time'] = date('Y-m-d H:i:s');
                $tx_model->baseUpdate($tx_model::tableName(),$tx_update_data,'id=:id',[':id'=>$v['id']]);
            }
        }
    }
}
