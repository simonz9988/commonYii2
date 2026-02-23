<?php

namespace backend\models;

use common\components\CommonLogger;
use common\models\SiteConfig;
use Yii;

/**
 * This is the model class for table "sea_admin_total_api_key".
 *
 * @property int $id
 * @property string $admin_user_id 管理员ID
 * @property string $coin 币种名称
 * @property string $base_coin USD/USDT
 * @property int $base_buy_num 最低购买数量
 * @property string $is_start_buy_up
 * @property string $is_start_trade_up
 * @property string $is_start_buy_down
 * @property string $is_start_trade_down
 * @property string $is_start_buy 是否允许购买
 * @property string $is_start_trade 是否允许交易
 * @property string $platform 允许的平台
 * @property string $api_key api key
 * @property string $api_secret api secret
 * @property string $passphrase 交易密码
 * @property int $leverage 默认杠杆倍数
 * @property int $sort 排序
 * @property string $status 是否有效 ENABLED/DISABLED
 * @property string $note 备注
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 更新时间
 * @property string $earn_percent
 * @property string $add_distance
 * @property string $buy_num_type
 * @property string $max_level
 * @property string $host 所属域名
 */
class AdminTotalApiKey extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_total_api_key';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'base_buy_num', 'leverage', 'sort'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['admin_user_id', 'coin', 'base_coin', 'platform', 'earn_percent', 'add_distance', 'buy_num_type', 'max_level', 'host'], 'string', 'max' => 20],
            [['is_start_buy_up', 'is_start_trade_up', 'is_start_buy_down', 'is_start_buy', 'is_start_trade', 'is_deleted'], 'string', 'max' => 1],
            [['is_start_trade_down', 'status'], 'string', 'max' => 50],
            [['api_key', 'api_secret', 'passphrase'], 'string', 'max' => 100],
            [['note'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_user_id' => 'Admin User ID',
            'coin' => 'Coin',
            'base_coin' => 'Base Coin',
            'base_buy_num' => 'Base Buy Num',
            'is_start_buy_up' => 'Is Start Buy Up',
            'is_start_trade_up' => 'Is Start Trade Up',
            'is_start_buy_down' => 'Is Start Buy Down',
            'is_start_trade_down' => 'Is Start Trade Down',
            'is_start_buy' => 'Is Start Buy',
            'is_start_trade' => 'Is Start Trade',
            'platform' => 'Platform',
            'api_key' => 'Api Key',
            'api_secret' => 'Api Secret',
            'passphrase' => 'Passphrase',
            'leverage' => 'Leverage',
            'sort' => 'Sort',
            'status' => 'Status',
            'note' => 'Note',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'earn_percent' => 'Earn Percent',
            'add_distance' => 'Add Distance',
            'buy_num_type' => 'Buy Num Type',
            'max_level' => 'Max Level',
            'host' => 'Host',
        ];
    }

    /**
     * 发送账户信息到聚合页
     * @return mixed
     */
    public function sendApiKey(){

        $api_key_obj = new AdminApiKey();
        $api_key_list = $api_key_obj->getUnSyncList();
        if(!$api_key_list){
            return true ;
        }

        $site_config_obj = new SiteConfig();
        $remote_sync_host = $site_config_obj->getByKey('remote_sync_host');
        $url = $remote_sync_host.'/sync/download-api-key';
        $host = $site_config_obj->getByKey('local_http_host');
        $res = curlGo($url,['api_key_list'=>$api_key_list,'host'=>$host]);
        $common_log = new CommonLogger();
        $common_log->logError($res);
        $res =json_decode($res,true);
        if(isset($res['code']) && $res['code'] ==1){

            $now = date('Y-m-d H:i:s');
            foreach($api_key_list as $v){
                $update_data['is_sync'] = 'Y';
                $update_data['modify_time'] = $now;
                $this->baseUpdate($api_key_obj::tableName(),$update_data,'id=:id',[':id'=>$v['id']]) ;
            }
        }
    }

    /**
     * 判断是否已经存在
     * @param $remote_id
     * @param $host
     * @return mixed
     */
    public function checkExistsByRemote($remote_id,$host){

        $params['cond'] = 'remote_id=:remote_id AND host=:host';
        $params['args'] = [':remote_id'=>$remote_id,':host'=>$host] ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 下载远程账户信息
     * @param $host
     * @param $api_key_list
     * @return mixed
     */
    public function downloadApiKey($host,$api_key_list){

        if(!$api_key_list){
            return true ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');
        foreach($api_key_list as $v){
            $remote_id = $v['id'];
            $info = $this->checkExistsByRemote($remote_id,$host);
            unset($v['id']);
            unset($v['is_sync']);
            if(isset($v['qiangpin_percent'])){
                unset($v['qiangpin_percent']);
            }

            $v['modify_time'] = $now ;
            if($info){
                // 更新
                unset($v['create_time']) ;
                $this->baseUpdate(self::tableName(),$v,'id=:id',[':id'=>$info['id']]) ;
            }else{
                // 新增
                $v['create_time'] = $now  ;
                $v['remote_id'] = $remote_id  ;
                $v['host'] = $host ;
                $this->baseInsert(self::tableName(),$v);
            }
        }
    }

    /**
     * 根据域名返回所有账户信息
     * @param $host
     * @return mixed
     */
    public function getListByHost($host){

        $params['cond'] = 'host=:host';
        $params['args'] = [':host'=>$host];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }


    /**
     * 获取节点树相关的数 Ztree   js插件需要
     * @return mixed
     */
    public function allNode(){

        // 查询获取所有的来源域名信息
        $site_config_obj = new SiteConfig();
        $all_from_website = $site_config_obj->getByKey('all_from_website');
        $all_from_website = explode(',',$all_from_website);
        $res = [] ;
        foreach($all_from_website as $k=>$v){

            $id=$k+100000 ;
            $res[] =array(
                'id' => $id,
                'pId'=> 0,
                'name'=>$v,
            );

            $list = $this->getListByHost($v);
            if($list){
                foreach($list as $api_key){

                    $name = $api_key['remote_id'].'--'.$api_key['note'];
                    $res[] =array(
                        'id' => $api_key['id'],
                        'pId'=> $id,
                        'name'=>$name,
                    );
                }
            }
        }

        return $res ;

    }

    /**
     * 根据ID返回指定的行集信息
     * @param $id
     * @return mixed
     */
    public function getInfoById($id){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return  $info ;
    }

    /**
     * 查询映射到本地的快照信息
     * @param $admin_user_id
     * @param $host
     * @return mixed
     */
    public function getInfoByIdAndHost($admin_user_id,$host){

        $params['cond'] = 'admin_user_id =:admin_user_id AND host =:host';
        $params['args'] = [':admin_user_id'=>$admin_user_id,':host'=>$host];
        return $this->findOneByWhere(self::tableName(),$params,self::getDb());
    }

    /**
     * 修正订单
     * @param $id
     * @param $type
     * @return bool
     */
    public function fixOrderByIdAndType($id,$type){
        $info = $this->getInfoById($id);
        if(!$info){
            return false ;
        }

        $remote_id = $info['remote_id'];
        $host = 'http://'.$info['host'].'/sync/fix-order';
        $post_data['id'] = $remote_id ;
        $post_data['type'] = $type ;
         $res = curlGo($host,$post_data);
        $res = json_decode($res,true);

        if(isset($res['code']) && $res['code'] == 1){
            return true;
        }else{
            return false ;
        }
    }

    /**
     * 添加标记信息
     * @param $id
     * @param $type
     * @return mixed
     */
    public function addMark($id,$type){

        $info = $this->getInfoById($id);
        if(!$info){
            return false ;
        }

        $remote_id = $info['remote_id'];
        $host = 'http://'.$info['host'].'/sync/add-mark';
        $post_data['id'] = $remote_id ;
        $post_data['type'] = $type ;

        $res = curlGo($host,$post_data);
        $res = json_decode($res,true);

        if(isset($res['code']) && $res['code'] == 1){
            return true;
        }else{
            return false ;
        }
    }
}
