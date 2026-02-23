<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_cash_in".
 *
 * @property int $id
 * @property string $order_no 交易单号
 * @property string $name 名称（可为空/USE-消费）
 * @property string $pay_time 支付时间
 * @property string $pay_name 支付人姓名
 * @property string $amount 交易金额
 * @property string $coin_type 币种(CNY)
 * @property string $pay_type 支付方式(ALIPAY-支付宝CARD-银行卡)
 * @property string $source
 * @property string $note 备注
 * @property string $fee 手续费
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class CashIn extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_cash_in';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_no'], 'required'],
            [['pay_time', 'create_time', 'modify_time'], 'safe'],
            [['amount', 'fee'], 'number'],
            [['order_no'], 'string', 'max' => 255],
            [['name', 'pay_name', 'coin_type', 'pay_type', 'source', 'note'], 'string', 'max' => 50],
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
            'name' => 'Name',
            'pay_time' => 'Pay Time',
            'pay_name' => 'Pay Name',
            'amount' => 'Amount',
            'coin_type' => 'Coin Type',
            'pay_type' => 'Pay Type',
            'source' => 'Source',
            'note' => 'Note',
            'fee' => 'Fee',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回行
     * @param $id
     * @param $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){
        $params['cond'] = 'id=:id' ;
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * @param $order_no
     * @param string $fields
     * @return array|bool
     */
    public function getInfoByOrderNo($order_no,$fields='*'){
        $params['cond'] = 'order_no=:order_no' ;
        $params['args'] = [':order_no'=>$order_no];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取支付类型名称
     * @param $pay_type
     * @return string
     */
    public function getPayTypeName($pay_type)
    {
        $site_config = new SiteConfig();
        $cash_pay_type = $site_config->getByKey('cash_pay_type');
        $pay_type_list = json_decode($cash_pay_type, true);
        return isset($pay_type_list[$pay_type]) ? $pay_type_list[$pay_type] : '';
    }

    public function getPayStatusName($pay_status){
        $site_config = new SiteConfig();
        $cash_pay_status = $site_config->getByKey('cash_pay_status');
        $pay_status_list = json_decode($cash_pay_status, true);
        return isset($pay_status_list[$pay_status]) ? $pay_status_list[$pay_status] : '';
    }

    public function getTotal($adminUserInfo,$start_time,$end_time){
        $where_cond[] = ' is_deleted=:is_deleted AND pay_status=:pay_status AND is_confirm=:is_confirm';
        $where_args[':is_deleted'] ='N';
        $where_args[':pay_status'] ='PAYED';
        $where_args[':is_confirm'] ='Y';
        if($adminUserInfo['username']!='admin'){
            $where_cond[]  = ' admin_user_id=:admin_user_id';
            $where_args[':admin_user_id'] = $adminUserInfo['id'] ;
        }

        if($start_time){
            $where_cond[] = ' create_time >=:start_time';
            $where_args[':start_time'] = $start_time ;
        }

        if($end_time){
            $where_cond[] = ' create_time <=:end_time';
            $where_args[':end_time'] = $end_time ;
        }

        if($where_cond){
            $params['cond'] = implode(' AND ',$where_cond);
            $params['args'] = $where_args ;
        }

        $params['fields'] = 'SUM(amount) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        $site_config_obj = new SiteConfig() ;
        $cash_in_fee_percent = $site_config_obj->getByKey('cash_in_fee_percent');

        $today_in_total = $info ? $info['total'] : 0 ;
        $today_in_total = round($today_in_total*(100 - $cash_in_fee_percent)*0.01,2);
        return $today_in_total ;
        $params['fields'] = 'count(1) as total';
        $total_num = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        $total_num = $total_num ? $total_num['total'] :0 ;
        $total_fee = $total_num *3 ;
        $today_in_total = $today_in_total - $total_fee ;
        return $today_in_total ;
    }
}
