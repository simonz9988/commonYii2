<?php

namespace common\models;

use common\components\MyRedis;
use okv3\Config;
use Yii;

/**
 * This is the model class for table "sdb_user_symbol".
 *
 * @property string $id
 * @property string $user_id
 * @property string $symbol_key
 * @property string $apiKey
 * @property string $secretKey
 * @property string $status 是否有效 enabled-有效disabled-无效 dealing-处理中
 * @property int $is_open 是否删除
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class UserPlatform extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_user_platform';
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
        return [
            [['is_open'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['user_id', 'symbol_key', 'apiKey', 'secretKey', 'status'], 'string', 'max' => 255],
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
            'symbol_key' => 'Symbol Key',
            'apiKey' => 'Api Key',
            'secretKey' => 'Secret Key',
            'status' => 'Status',
            'is_open' => 'Is Open',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * 获取用户手机号信息
     * @param $user_id
     * @return string
     */
    public function getUserMobile($user_id){
        $params['cond'] = 'id =:id';
        $params['args'] = [':id'=>$user_id] ;
        $params['fields'] = 'mobile';
        $info  = $this->findOneByWhere('sea_user',$params,self::getDb());
        return $info ? $info['mobile']:'';
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @return mixed
     */
    public function getInfoById($id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        if($info){
            $user_id = $info['user_id'] ;
            $info['mobile'] = $this->getUserMobile($user_id);
        }
        return $info ;
    }

    /**
     * 根据平台返回列表信息
     * @param $platform
     * @return mixed
     */
    public function getListByPlatform($platform){
        $platform = strtoupper($platform);
        $params['cond'] = 'platform=:platform AND is_deleted=:is_deleted';
        $params['args'] = [':platform'=>$platform,':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 获取用户选择的最新一条的币种信息
     * @param $user_id
     * @param $platform
     * @param $from_coin
     * @param $from_legal_coin
     * @return mixed
     */
    public function getLastedRowByUser($user_id,$platform,$from_coin,$from_legal_coin){

        if(!$from_coin){

            $params['cond'] = 'user_id=:user_id AND is_deleted=:is_deleted';
            $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N'];
            $params['orderby'] = 'modify_time desc';
            $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

            $is_setting = false ;

        }else{

            $coin = $from_coin ;
            $legal_coin = $from_legal_coin ;
            $params['cond'] = 'user_id=:user_id AND is_deleted=:is_deleted AND coin=:coin AND legal_coin=:legal_coin';
            $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N',':coin'=>$coin,':legal_coin'=>$legal_coin];
            $params['orderby'] = 'modify_time desc';
            $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        }

        if($info){
            $is_setting = true ;
            $coin = $info['coin'];
            $legal_coin = $info['legal_coin'];
        }else{

            // 查询指定平台的第一币种
            $coin_obj = new Coin() ;
            $coin_params['cond'] = 'platform=:platform AND is_deleted=:is_deleted';
            $coin_params['args'] = [':platform'=>$platform,":is_deleted"=>'N'];
            $coin_params['orderby'] = 'sort desc,id desc';

            $coin_info = $coin_obj->findOneByWhere($coin_obj::tableName(),$coin_params,self::getDb());
            $coin = $coin_info ? $coin_info['unique_key'] : '';
            $legal_coin_list = $coin_info?explode(',',$coin_info['legal_coin_list']):[];
            $legal_coin = $legal_coin_list ? $legal_coin_list[0]:'USDT';
        }

        return compact('is_setting','coin','legal_coin','info');



    }

    /**
     * 获取用户指定平台配置信息
     * @param $user_id
     * @param $platform
     * @return array|bool
     */
    public function getInfoByUserIdAndPlatform($user_id,$platform){
        $params['cond'] = 'user_id=:user_id AND is_deleted=:is_deleted AND platform=:platform ';
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N',':platform'=>$platform];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;

    }

    /**
     * 获取用户订单列表信息
     * @param $user_id
     * @param $platform
     * @param $coin
     * @param $legal_coin
     * @param $is_deal
     * @return mixed
     */
    public function getOrderList($user_id,$platform,$coin,$legal_coin,$is_deal=true){

        #TODO 重新获取新的方法体
        // 获取订单列表
        if($platform =='OKEX'){
            $order_obj = new RobotOkexSpotOrder();
            return $order_obj->getListByUser($user_id,$coin,$legal_coin,$is_deal);
        }else{
            #TODO
        }
    }

    /**
     * 自动创建策略
     * @param $user_id
     * @param $platform
     * @param $coin
     * @param $legal_coin
     * @param $total_amount 交易金额
     * @param $buying_points 建仓点
     * @param $max_buying_points 最高建仓点
     * @param $grid_levels 交易层数
     * @return mixed
     */
    public function createStrategyByUser($user_id,$platform,$coin,$legal_coin,$total_amount,$buying_points=0,$max_buying_points=0,$grid_levels=0){

        // 查询key的信息
        $key_obj = new UserPlatformKey();
        $key_info = $key_obj->getInfoByUserIdAndPlatform($user_id,$platform);
        if(!$key_info){
            $this->setError('100076');
            return false ;
        }

        $coin_obj = new Coin();
        $coin_info = $coin_obj->getInfoByPlatformAndCoin($platform,$coin);
        if(!$buying_points){
            $buying_points = $coin_obj->getBuyPointsByInfoAndLegalCoin($coin_info,$legal_coin);
        }

        if(!$max_buying_points){
            $max_buying_points = $coin_obj->getMaxBuyPointsByInfoAndLegalCoin($coin_info,$legal_coin);
        }

        if(!$grid_levels){
            $grid_levels = $coin_info['max_block'] ;
        }

        $gap_percent = $coin_info['earn_high_percent'] ;
        $update_data['buying_points'] = $buying_points ;
        $update_data['max_buying_points'] = $max_buying_points ;
        $update_data['grid_levels'] = $grid_levels ;
        $update_data['gap_percent'] = $gap_percent ;

        $info = $this->checkExistsByPlatformAndCoin($user_id,$platform,$coin,$legal_coin);
        $push_task_obj = new PushTask();
        if($info ){

            if($info['api_key'] == $key_info['api_key']){
                $command = 'update';

                $update_data['command'] = $command ;
                $update_data['total_amount'] = $total_amount ;
                $update_data['buying_points'] = $buying_points ;
                $update_data['max_buying_points'] = $max_buying_points ;
                $update_data['grid_levels'] = $grid_levels ;
                $update_data['gap_percent'] = $gap_percent ;
                $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
                $push_task_obj->addRecord($info['id'],'UPDATE_STRATEGY',time());
            }else{

                // 旧的需要删除
                $del_data['is_deleted'] = 'Y';
                $del_data['command'] = 'delete';
                $del_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate(self::tableName(),$del_data,'id=:id',[':id'=>$info['id']]);
                $push_task_obj->addRecord($info['id'],'UPDATE_STRATEGY',time());
                $command = 'create';
            }

        }else{
            // 新增
            $command = 'create';
        }

        if($command =='create'){

            // 当前时间
            $now = date('Y-m-d H:i:s');

            $add_data['user_id'] = $user_id;
            $add_data['platform'] = $platform;
            $add_data['api_key'] = $key_info['api_key'];
            $add_data['api_secret'] = $key_info['api_secret'];
            $add_data['passphrase'] = $key_info['passphrase'];
            $add_data['command'] = $command;//create/update
            $add_data['instrument_id'] = returnInstrumentId($coin,$legal_coin);
            $add_data['coin'] = $coin;
            $add_data['legal_coin'] = $legal_coin;
            $add_data['buying_points'] = $buying_points;// 建仓点
            $add_data['max_buying_points'] = $max_buying_points;// 建仓点

            $add_data['total_amount'] = $total_amount;// 支配的资金
            $add_data['grid_levels'] = $grid_levels;// 网格的层数
            $add_data['gap_percent'] = $gap_percent;//止盈点
            $add_data['is_deleted'] = 'N';
            $add_data['create_time'] = $now;
            $add_data['modify_time'] = $now;
            $id = $this->baseInsert(self::tableName(),$add_data);
            $push_task_obj->addRecord($id,'UPDATE_STRATEGY',time());

        }

        return true ;
    }

    /**
     * 根据用户返回指定平台的信息是否存在
     * @param $user_id
     * @param $platform
     * @param $coin
     * @param $legal_coin
     * @return mixed
     */
    public function checkExistsByPlatformAndCoin($user_id,$platform,$coin,$legal_coin){

        $params['cond'] = 'user_id=:user_id AND platform=:platform AND coin=:coin AND legal_coin=:legal_coin AND is_deleted="N"';
        $params['args'] = [':user_id'=>$user_id,":platform"=>$platform,':coin'=>$coin,':legal_coin'=>$legal_coin];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 推送任务
     * @param $info
     * @return bool
     */
    public function sendByPushTask($info){

        /*
        curl -X POST --header 'Content-Type: application/json' --header 'Accept: application/json' -d '{
        "api_key" : "cd0c88ac-dbuqg6hkte-ed3dd37c-68b29",
        "secret_key" : "c39fc28b-69168658-467e1695-7bed7",
        "command" : "create",
        "instrument_id" : "xrpusdt",
        "total_amount" : 5,
        "grid_levels" : 10,
        "gap_percent" : 0.01,
        "max_orders" : 1,
        "price_ceiling" : 0.35,
        "price_floor" : 0.10
    }' 'http://127.0.0.1:5000/huobi_spot/grid_trader'

        curl -X POST --header 'Content-Type: application/json' --header 'Accept: application/json' -d '{
        "api_key" : "09289741-b1ae-4df7-93f0-899c2cb8f2f8",
        "secret_key" : "E1DE18177087C8E308BB48139F52EFBC",
        "passphrase" : "a12345678",
        "command" : "create",
        "instrument_id" : "XRP-USDT",
        "total_amount" : 6,
        "grid_levels" : 10,
        "gap_percent" : 0.01,
        "max_orders" : 1,
        "price_ceiling" : 0.6,
        "price_floor" : 0.4
    }' 'http://127.0.0.1:5000/okex_spot/grid_trader'

        */

        $platform = $info['platform'];

        if($platform != 'OKEX'){
            $instrument_id_arr = explode('-',$info['instrument_id']);
            $instrument_id = strtolower(implode('',$instrument_id_arr));
            $host ='http://127.0.0.1:5000/huobi_spot/grid_trader';
        }else{
            $instrument_id = $info['instrument_id'] ;
            $host ='http://127.0.0.1:5000/okex_spot/grid_trader';
        }
        $post_data['api_key'] = $info['api_key'];
        $post_data['secret_key'] = $info['api_secret'];
        $post_data['passphrase'] = $info['passphrase'];
        $post_data['command'] = $info['command'];
        $post_data['instrument_id'] =$instrument_id;
        $post_data['total_amount'] = floatval($info['total_amount']);
        $post_data['price_ceiling'] =floatval($info['max_buying_points']);
        $post_data['price_floor'] =floatval($info['buying_points']);
        $post_data['grid_levels'] = floatval($info['grid_levels']);
        $post_data['gap_percent'] = floatval($info['gap_percent']);

        $ignore_error = false ;
        $header[] = 'Content-Type:application/json';
        $res = curlGo($host,json_encode($post_data),$ignore_error,$header);
        $res = json_decode($res,true);
        if($res['code'] == 0){
            return true ;
        }else{
            return false ;
        }
    }

    /**
     * 获取最小的资金数
     * @param $price
     * @param $max_block
     * @param $add_percent
     * @param $coin_info
     * @return float|int
     */
    public function getMinBalance($price,$max_block,$add_percent,$coin_info){

        $up_step = ceil($max_block/2) ;
        $down_step = $up_step ;

        $total = 0 ;
        for($i=1;$i<=$up_step;$i++){
            $total += $price*(1+$add_percent*$i);
        }

        for($i=$down_step;$i>0;$i--){
            $total += $price*(1-$add_percent*$i);
        }

        // 乘以最低购买数量
        $total = $total*$coin_info['min_qty'] ;

        return $total ;
    }
}
