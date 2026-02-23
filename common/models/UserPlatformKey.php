<?php

namespace common\models;

use common\components\HuobiLib;
use common\components\SpotApi;
use Yii;

/**
 * This is the model class for table "sea_user_platform_key".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $platform 平台信息(OKEX/HUOBI)
 * @property string $api_key api key
 * @property string $api_secret eth地址
 * @property string $passphrase 交易密码
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class UserPlatformKey extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_user_platform_key';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['platform', 'api_key', 'api_secret'], 'string', 'max' => 100],
            [['passphrase'], 'string', 'max' => 255],
            [['is_deleted'], 'string', 'max' => 1],
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
            'platform' => 'Platform',
            'api_key' => 'Api Key',
            'api_secret' => 'Api Secret',
            'passphrase' => 'Passphrase',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 添加用户信息
     * @param $user_id
     * @param $platform
     * @param $api_key
     * @param $api_secret
     * @param $passphrase
     * @return mixed
     */
    public function addInfo($user_id,$platform,$api_key,$api_secret,$passphrase){
        $platform = strtoupper($platform);
        if(!$platform  || !$api_key || !$api_secret ){
            $this->setError('100017',getErrorDictMsg(100017));
            return false ;
        }

        if($platform =='OKEX' &&  !$passphrase){
            $this->setError('100017',getErrorDictMsg(100017));
            return false ;
        }

        $site_config = new SiteConfig();
        $bit_all_platform = $site_config->getByKey('bit_all_platform','json');
        $bit_all_platform = array_keys($bit_all_platform);
        if(!in_array($platform,$bit_all_platform)){
            $this->setError('100018',getErrorDictMsg(100018));
            return false ;
        }

        // 判断当前APIkey是不是重复绑定
        $repeat_params['cond'] = ' user_id !=:user_id  AND platform=:platform AND is_deleted=:is_deleted AND api_key=:api_key';
        $repeat_params['args'] = [':user_id'=>$user_id,':platform'=>$platform,':is_deleted'=>'N',':api_key'=>$api_key];
        $repeat_info = $this->findOneByWhere(self::tableName(),$repeat_params,self::getDb());
        if($repeat_info){
            $this->setError('100019',getErrorDictMsg(100019));
            return false ;
        }

        $params['cond'] = 'user_id=:user_id AND platform=:platform AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':platform'=>$platform,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            // update
            $update_data = compact('api_key','api_secret','passphrase');
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            $res = $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }else{
            // insert

            $add_data = compact('user_id','platform','api_key','api_secret','passphrase');
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $res = $this->baseInsert(self::tableName(),$add_data);
        }

        return $res ;


    }

    /**
     * 获取当前
     * @param $user_id
     * @return mixed
     */
    public function getListByUserId($user_id){

        $params['cond'] = 'user_id=:user_id AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }
    /**
     * 获取当前用户所有平台的余额总和
     * @param $user_id
     * @return mixed
     */
    public function getUserTotalBalance($user_id){

        $api_list = $this->getListByUserId($user_id);
        if(!$api_list){
            return  0 ;
        }

        $return = 0 ;
        foreach($api_list as $v){

            $platform = $v['platform'];

            $config['apiKey'] = $v['api_key'] ;
            $config['apiSecret'] = $v['api_secret'] ;
            $config['passphrase'] = $v['passphrase'] ;
            $total = $this->getUserTotalBalanceByPlatform($config,$platform) ;
            $return = $return + $total ;
        }

        return $return ;
    }

    /**
     * 获取指定平台的账户余额总额
     * @param $config
     * @param $platform
     * @return mixed
     */
    public function getUserTotalBalanceByPlatform($config,$platform){


        if($platform =='OKEX'){
            $obj = new SpotApi($config);
            $list = $obj->getAccountInfo();
            if(!$list){
                return 0 ;
            }

            // 平台所有币种个价格列表信息
            $platform_obj = new RobotCoinPlatformInfo();
            $platform_coin_list = $platform_obj->getLastedInfoByPlatform('OKEX');
            $balance_list = [] ;
            foreach($list as $hold){
                if($hold['currency'] =='USDT'){
                    $balance_list[$hold['currency']] = $hold['balance'] ;

                }else{
                    $currency = $hold['currency'];
                    $instrument_id = $currency.'-'.'USDT' ;

                    $detail = [] ;
                    foreach($platform_coin_list as $v){
                        if($v['instrument_id'] == $instrument_id){
                            $detail = $v ;
                            break ;
                        }
                    }

                    $balance_list[$hold['currency']] = $detail ? $detail['best_ask']*$hold['balance']:0; ;

                }
            }

            $total = 0 ;
            if($balance_list){
                foreach($balance_list as $v){
                    $total += $v ;
                }
            }

            return scToNum($total) ;
        }else if($platform =='HUOBI'){

            $model = new HuobiLib();
            if(!isset($config['account_id'])){
                $config['account_id'] = $this->getHuobiAccountId($config);
            }
            $model->ACCOUNT_ID = $config['account_id'];
            $model->ACCESS_KEY = $config['apiKey'];
            $model->SECRET_KEY = $config['apiSecret'];
            $list = $model->get_account_balance();
            $list = object_to_array($list);
            $list = isset($list['data']['list'])?$list['data']['list']:[];


            $balance_list = [] ;
            foreach($list as $hold){
                if($hold['currency'] =='USDT'){
                    $balance_list[$hold['currency']] = $hold['balance'] ;

                }else{
                    $currency = $hold['currency'];

                    $detail = [] ;
                    $platform_obj = new RobotCoinPlatformInfo();
                    $platform_coin_list = $platform_obj->getLastedInfoByPlatform('HUOBI');
                    foreach($platform_coin_list as $v){

                        if($v['symbol'] == strtolower($currency).'usdt'){
                            $detail = $v ;
                            break ;
                        }

                    }

                    $balance_list[$hold['currency']] = $detail ? $detail['ask']*$hold['balance']:0; ;

                }
            }

            $total = 0 ;
            if($balance_list){
                foreach($balance_list as $v){
                    $total += $v ;
                }
            }

            return scToNum($total) ;

        }

    }

    /**
     * 获取用户交易所对应的余额信息
     * @param $user_id
     * @return mixed
     */
    public function getFrontList($user_id){
        $api_list = $this->getListByUserId($user_id);
        if(!$api_list){
            return  [] ;
        }

        $res = [] ;
        foreach($api_list as $v){
            $platform = $v['platform'];

            $config['apiKey'] = $v['api_key'] ;
            $config['apiSecret'] = $v['api_secret'] ;
            $config['passphrase'] = $v['passphrase'] ;
            $config['account_id'] = $v['account_id'] ;
            $total = $this->getUserTotalBalanceByPlatform($config,$platform);
            $res[] = [
                'id'=>$v['id'],
                'total'=>$total,
                'platform'=>$platform,
            ];

        }

        return $res ;
    }

    public function getHuobiAccountId($config){
        $model = new HuobiLib();
        $model->ACCESS_KEY = $config['apiKey'];
        $model->SECRET_KEY = $config['apiSecret'];
        $account_list = $model->get_account_accounts();
        $account_list = object_to_array($account_list);

        $account_id = 0;

        if($account_list && isset($account_list['data'])){

            foreach($account_list['data'] as $v){
                if($v['type']=='spot' && $v['state'] =='working'){
                    $account_id = $v['id'] ;
                }
            }
        }

        return $account_id ;
    }

    /**
     * 根据用户ID和指定ID返回对
     * @param $user_id
     * @param $id
     * @return mixed
     */
    public function delByUserIdAndId($user_id,$id){

        $update_data['is_deleted'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $cond = 'id=:id AND user_id=:user_id';
        $args = [':id'=>$id,':user_id'=>$user_id];
        return $this->baseUpdate(self::tableName(),$update_data,$cond,$args);
    }

    /**
     * 根据已经绑定的key返回第一个已选择的平台
     * @param $key_list
     * @return mixed
     */
    public function getFirstAllowedPlatformByKeyList($key_list){
        if(!$key_list){
            return  'OKEX';
        }

        $res = '';
        foreach($key_list as $v){
            $res = $v['platform'];
            break ;
        }
        return $res ;
    }

    /**
     * 返回用户指定平台的信息
     * @param $user_id
     * @param $platform
     * @return mixed
     */
    public function getInfoByUserIdAndPlatform($user_id,$platform){
        $params['cond'] = 'user_id=:user_id AND platform=:platform AND is_deleted=:is_deleted';
        $params['args'] = [':user_id'=>$user_id,':platform'=>$platform,':is_deleted'=>'N'];
        $info  = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 返回指定平台的法币的余额
     * @param $legal_coin
     * @param $platform
     * @param $user_id
     * @return mixed
     */
    public function getLegalBalance($legal_coin,$platform,$user_id){

        // 获取用户指定平台的信息
        $user_platform_key_info = $this->getInfoByUserIdAndPlatform($user_id,$platform);
        if(!$user_platform_key_info){
            return  0 ;
        }

        if($platform =='OKEX'){

            $config['apiKey'] = $user_platform_key_info['api_key'] ;
            $config['apiSecret'] = $user_platform_key_info['api_secret'] ;
            $config['passphrase'] = $user_platform_key_info['passphrase'] ;
            $obj = new SpotApi($config);
            $list = $obj->getAccountInfo();
            if(!$list){
                return 0 ;
            }

            foreach($list as $hold){
                if($hold['currency'] ==$legal_coin){
                    return $hold['balance'] ;
                }
            }

            return  0  ;
        }
    }

    /**
     * 获取币种当前价格
     * @param $coin
     * @param $legal_coin
     * @param $platform
     * @return mixed
     */
    public function getCoinPrice($coin,$legal_coin,$platform){

        $platform_obj = new RobotCoinPlatformInfo();
        $platform_coin_list = $platform_obj->getLastedInfoByPlatform($platform);
        if($platform =='OKEX'){
            foreach($platform_coin_list as $v){
                if($v['instrument_id'] == strtoupper($coin.'-'.$legal_coin)){
                    return $v['best_ask'];
                }
            }
        }else{
            foreach($platform_coin_list as $v){
                if($v['symbol'] == strtolower($coin.$legal_coin)){
                    return $v['ask'];
                }
            }
        }

        return 0 ;

    }
}
