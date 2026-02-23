<?php

namespace backend\models;

use common\models\SiteConfig;
use Yii;

/**
 * This is the model class for table "sea_platform_total_order".
 *
 * @property int $id
 * @property string $coin
 * @property int $admin_user_id 后台管理员用户ID
 * @property string $order_id
 * @property string $instrument_id
 * @property string $filled_qty
 * @property string $price_avg
 * @property string $contract_val
 * @property string $fee
 * @property string $order_type
 * @property string $price
 * @property string $size
 * @property string $state
 * @property string $status
 * @property string $timestamp
 * @property string $trigger_price
 * @property string $type
 * @property string $host 对应的域名信息
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class PlatformTotalOrder extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_platform_total_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['coin', 'admin_user_id', 'order_id', 'instrument_id', 'filled_qty', 'price_avg', 'contract_val', 'fee', 'order_type', 'price', 'size', 'state', 'status', 'timestamp', 'trigger_price', 'type', 'host'], 'required'],
            [['admin_user_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['coin', 'order_id', 'instrument_id', 'filled_qty', 'price_avg', 'contract_val', 'fee', 'order_type', 'price', 'size', 'state', 'status', 'timestamp', 'trigger_price', 'type', 'host'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coin' => 'Coin',
            'admin_user_id' => 'Admin User ID',
            'order_id' => 'Order ID',
            'instrument_id' => 'Instrument ID',
            'filled_qty' => 'Filled Qty',
            'price_avg' => 'Price Avg',
            'contract_val' => 'Contract Val',
            'fee' => 'Fee',
            'order_type' => 'Order Type',
            'price' => 'Price',
            'size' => 'Size',
            'state' => 'State',
            'status' => 'Status',
            'timestamp' => 'Timestamp',
            'trigger_price' => 'Trigger Price',
            'type' => 'Type',
            'host' => 'Host',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }


    /**
     * 发送订单信息到远程服务器
     */
    public function sendOrderFromTotalOrder(){
        $order_total_order_obj = new OkexTotalOrder();
        $order_list = $order_total_order_obj->getUnSyncOrderList();
        $site_config_obj = new SiteConfig();
        $remote_sync_host = $site_config_obj->getByKey('remote_sync_host');
        $url = $remote_sync_host.'/sync/download-from-remote';
        $host = $site_config_obj->getByKey('local_http_host');
        $res = curlGo($url,['order_list'=>$order_list,'host'=>$host]);
        $res =json_decode($res,true);
        if(isset($res['code']) && $res['code'] ==1){

            foreach($order_list as $v){
                $update_data['is_sync'] = 'Y';
                $this->baseUpdate($order_total_order_obj::tableName(),$update_data,'id=:id',[':id'=>$v['id']]) ;
            }
        }
    }

    /**
     * 通过订单ID判断是否存在
     * @param $order_id
     * @return mixed
     */
    public function checkExistsByOrderId($order_id){
        $params['cond'] = 'order_id=:order_id';
        $params['args'] = [':order_id'=>$order_id];
        $params['fields'] = 'id';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info?true:false;
    }

    /**
     * 下载订单
     * @param $host
     * @param $order_list
     * @return mixed
     */
    public function downloadOrder($host,$order_list){

        $now = date('Y-m-d H:i:s') ;

        $total_api_key_obj = new AdminTotalApiKey();

        foreach($order_list as $v){

            // 判断是否已经存在
            $check_exist = $this->checkExistsByOrderId($v['order_id']);
            if($check_exist){
                continue ;
            }

            $v['remote_id'] = $v['id'];
            $admin_user_id = $v['admin_user_id'] ;
            $local_admin_user_info = $total_api_key_obj->getInfoByIdAndHost($admin_user_id,$host);
            if(!$local_admin_user_info){
                return false ;
            }
            $v['local_admin_user_id']  = $local_admin_user_info['id'];

            unset($v['id']);
            unset($v['is_sync']);
            $v['host'] = $host ;
            $v['create_time'] = $now ;
            $v['modify_time'] = $now ;
            $this->baseInsert(self::tableName(),$v);

        }

        return false ;
    }
}
