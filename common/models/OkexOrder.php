<?php

namespace common\models;

use common\components\EthWallet;
use common\components\OkexTrade;
use common\components\PlatformTradeCommonV4;
use Web3\Eth;
use Yii;

/**
 * This is the model class for table "sea_tx_list".
*/
class OkexOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_okex_order';
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
     * 根据表名返回指定用户最新的交易信息
     * @param $admin_user_id
     * @param $table_name
     * @param $fields
     */
    public function getLastedInfoByTableName($admin_user_id,$table_name,$fields='*'){

        $params['cond'] = 'admin_user_id = :admin_user_id AND is_deleted=:is_deleted';
        $params['args'] = [':admin_user_id'=>$admin_user_id,':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $params['orderby'] = ' id desc';
        $info = $this->findOneByWhere($table_name,$params);
        return $info ;
    }

    /**
     * 查询最新开始的一条信息
     * @param $admin_user_id
     * @param $table_name
     * @param $group_id
     * @param string $fields
     * @return mixed
     */
    public function getFirstInfoByGroupId($admin_user_id,$table_name,$group_id,$fields='*'){
        $params['cond'] = 'admin_user_id = :admin_user_id AND group_id=:group_id AND is_deleted=:is_deleted';
        $params['args'] = [':admin_user_id'=>$admin_user_id,':group_id'=>$group_id,':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $params['orderby'] = ' id asc';
        $info = $this->findOneByWhere($table_name,$params);
        return $info ;
    }

    /**
     *
     * @param $admin_user_id
     * @param $buy_up_table_name
     * @param $buy_down_table_name
     * @return mixed
     */
    public function getLastedGroupId($admin_user_id,$buy_up_table_name,$buy_down_table_name){

        // 查询返回字段
        $fields = 'group_id,create_time';
        $up_info = $this->getLastedInfoByTableName($admin_user_id,$buy_up_table_name,$fields) ;
        $down_info = $this->getLastedInfoByTableName($admin_user_id,$buy_down_table_name,$fields) ;

        if(!$up_info && !$down_info){
            return 0 ;
        }else if($up_info && !$down_info){
            return $up_info['group_id'] ;
        }else if(!$up_info && $down_info){
            return $down_info['group_id'] ;
        }else{

            $up_first_info = $this->getFirstInfoByGroupId($admin_user_id,$buy_up_table_name,$up_info['group_id'],$fields);
            $down_first_info = $this->getFirstInfoByGroupId($admin_user_id,$buy_down_table_name,$down_info['group_id'],$fields);

            return $up_first_info['create_time'] > $down_first_info['create_time'] ?  $up_first_info['group_id']: $down_first_info['group_id'];
        }
    }


    /**
     * 返回group_id
     * @param $table_name
     * @param $buy_order_id
     * @return mixed
     * Note:用户后期补仓使用
     */
    public function createGroupId($table_name,$buy_order_id=''){

        if($buy_order_id){
            $params['cond'] = 'order_id=:order_id';
            $params['args'] = [':order_id'=>$buy_order_id];
            $info = $this->findOneByWhere($table_name,$params);
            if($info && $info['group_id']){
                return $info['group_id'] ;
            }
        }

        $group_id = time().mt_rand(10000,99999);
        $params['cond'] = 'group_id=:group_id AND `level`=:level';
        $params['args'] = [':group_id'=>$group_id,':level'=>1];
        $info = $this->findOneByWhere($table_name,$params);
        if(!$info){
            return $group_id ;
        }

        return $this->createGroupId($table_name,$buy_order_id);
    }

    /**
     * 根据group_id获取得到订单列表的信息
     * @param $admin_user_id
     * @param $table_name
     * @param $group_id
     * @return mixed
     */
    public function getOrderListByGroupId($admin_user_id,$table_name,$group_id){
        $params['cond'] = 'admin_user_id = :admin_user_id AND group_id=:group_id AND is_deleted=:is_deleted';
        $params['args'] = [':admin_user_id'=>$admin_user_id,':group_id'=>$group_id,':is_deleted'=>'N'];
        $params['orderby'] = ' id desc ';
        $list = $this->findAllByWhere($table_name,$params) ;
        return $list ;
    }

    /**
     * 获取订单具体的信息
     * @param $order_id
     * @param $buy_table_name
     * @return mixed
     */
    public function getOrderInfoByOrderId($order_id,$buy_table_name){

        $params['cond'] ='order_id=:order_id';
        $params['args'] = [':order_id'=>$order_id];
        $info = $this->findOneByWhere($buy_table_name ,$params);
        return $info ;
    }

    /**
     * 获取已经卖出的信息
     * @param $buy_id
     * @param $sell_table_name
     * @return mixed
     */
    public function getSellInfoByBuyInfo($buy_id,$sell_table_name){
        $params['cond'] = 'buy_id=:buy_id AND is_deleted=:is_deleted';
        $params['args'] = [':buy_id'=>$buy_id,':is_deleted'=>'N'];
        $params['orderby'] = 'id DESC';
        $info = $this->findOneByWhere($sell_table_name,$params);
        return $info ;
    }

    /**
     * 判断指定级别的订单是否存在
     * @param $table_name
     * @param $group_id
     * @param $level
     * @return mixed
     */
    public function checkExistsByLevelAndGroupId($table_name,$group_id,$level){

        $params['cond'] = 'group_id=:group_id AND level=:level AND is_deleted=:is_deleted';
        $params['args'] = [':group_id'=>$group_id,':level'=>$level,':is_deleted'=>'N'];
        $params['orderby'] = 'id DESC';
        $info = $this->findOneByWhere($table_name,$params);
        return $info ;
    }

    /**
     * 获取已完成的订单列表
     * @param $table_name
     * @param $group_id
     * @return mixed
     */
    public function getCompletedOrderList($table_name,$group_id){

        $params['cond'] = 'state = :state AND group_id=:group_id AND is_deleted=:is_deleted';
        $params['args'] = [':state'=>2,':group_id'=>$group_id,':is_deleted'=>'N'];
        $params['orderby'] = ' id desc ';
        $list = $this->findAllByWhere($table_name,$params) ;
        return $list ;
    }

    public function getInfoByOrginalGroupId($table_name,$orginal_group_id){
        $params['cond'] = 'orginal_group_id=:orginal_group_id AND is_deleted=:is_deleted';
        $params['args'] = [':orginal_group_id'=>$orginal_group_id,':is_deleted'=>'N'];
        $params['orderby'] = 'id DESC';
        $info = $this->findOneByWhere($table_name,$params);
        return $info ;
    }


    /**
     * 获取指定币种的最新一条信息
     * @param $admin_user_id
     * @param $table_name
     * @param $coin
     * @return mixed
     */
    public function getLastInfo($admin_user_id,$table_name,$coin){
        // 获取最新的一条信息
        $params['cond'] ='coin=:coin AND is_deleted="N" AND admin_user_id=:admin_user_id' ;
        $params['args'] = [':coin'=>$coin,':admin_user_id'=>$admin_user_id];
        $params['orderby'] = 'id DESC';
        $info = $this->findOneByWhere($table_name,$params);
        return $info ;
    }


    /**
     * 获取已经卖出的信息
     * @param $admin_user_id
     * @param $buy_id
     * @param $sell_table_name
     * @return mixed
     */
    public function getSellOrderListByOrderId($admin_user_id,$buy_id,$sell_table_name){
        $params['cond'] = 'buy_id=:buy_id AND is_deleted=:is_deleted AND admin_user_id=:admin_user_id';
        $params['args'] = [':buy_id'=>$buy_id,':is_deleted'=>'N',':admin_user_id'=>$admin_user_id];
        $params['orderby'] = 'id DESC';
        $info = $this->findAllByWhere($sell_table_name,$params);
        return $info ;
    }


    public function getListByOrginalGroupId($table_name,$orginal_group_id){
        $params['cond'] = 'orginal_group_id=:orginal_group_id AND is_deleted=:is_deleted';
        $params['args'] = [':orginal_group_id'=>$orginal_group_id,':is_deleted'=>'N'];
        $params['orderby'] = 'id DESC';
        $info = $this->findAllByWhere($table_name,$params);
        return $info ;
    }

    /**
     * 添加对冲订单
     * @param $api_key_info
     * @return mixed
     */
    public function addDoubleOrder($api_key_info){

        $trade_obj = new PlatformTradeCommonV4();
        $admin_user_id = $api_key_info['admin_user_id'] ;
        $coin = $api_key_info['coin'] ;
        $type = 'up';
        $trade_obj->setConfigInfo($admin_user_id,$api_key_info,$coin,$type) ;

        // 查询当前持有
        $trade_obj->getAllHoldingInfo();

        $mark_price = $trade_obj->mark_price;
        if(!$mark_price){
            $mark_price = $trade_obj->getCoinMarkPriceFromService($coin);
        }
    }


}
