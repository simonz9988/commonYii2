<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_cash_out".
 *
 * @property int $id
 * @property string $order_no 出金单号
 * @property string $amount 出金金额
 * @property string $coin_type 币种类型
 * @property string $bank_name 银行名称
 * @property string $bank_account 开户名称
 * @property string $bank_detail 开户行详细
 * @property string $bank_no 银行卡号
 * @property string $fee 手续费
 * @property string $status 状态TO_PAY-待支付 PAYED-已支付
 * @property string $is_deleted 默认是否删除
 * @property string $apply_time 申请时间
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class CashOut extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_cash_out';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_no'], 'required'],
            [['amount'], 'number'],
            [['apply_time', 'create_time', 'modify_time'], 'safe'],
            [['order_no', 'bank_name', 'bank_detail', 'bank_no', 'fee', 'status'], 'string', 'max' => 255],
            [['coin_type', 'bank_account'], 'string', 'max' => 50],
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
            'order_no' => 'Order No',
            'amount' => 'Amount',
            'coin_type' => 'Coin Type',
            'bank_name' => 'Bank Name',
            'bank_account' => 'Bank Account',
            'bank_detail' => 'Bank Detail',
            'bank_no' => 'Bank No',
            'fee' => 'Fee',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'apply_time' => 'Apply Time',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 创建交易订单号
     * @return mixed
     */
    public function createOrderNo(){

        $order_no = date("YmdHis");
        $order_no .= mt_rand(10000000,99999999);

        $params['cond'] = 'order_no =:order_no';
        $params['args'] = [':order_no'=>$order_no];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        if($info){
            return $this->createOrderNo();
        }
        return $order_no ;
    }

    /**
     * 判断新增数据是否符合格式要求
     * @param $add_data
     * @return array
     */
    public function checkAddData($add_data){

        $amount = $add_data['amount'];
        $site_config = new SiteConfig();
        $min= $site_config->getByKey('cash_out_min_amount');
        $max= $site_config->getByKey('cash_out_max_amount');

        if(!is_numeric($amount) || $amount <$min || $amount >$max){
            return ['code'=>'200010'] ;
        }

        if(!$add_data['bank_account']){
            return ['code'=>'200011'] ;
        }

        if(!$add_data['bank_no']){
            return ['code'=>'200012'] ;
        }

        if(!$add_data['province_name']){
            return ['code'=>'200013'] ;
        }

        if(!$add_data['city_name']){
            return ['code'=>'200014'] ;
        }

        if(!$add_data['bank_detail']){
            return ['code'=>'200015'] ;
        }

        if(!$add_data['bank_name']){
            return ['code'=>'200016'] ;
        }

        // 判断前三个是不是out
        $s2 = 'out';

        if(substr($add_data['bank_no'], 0, strlen($s2)) != $s2){
            return ['code'=>'200017'] ;
        }

        $area_obj = new Areas();
        $province_info =  $area_obj->getInfoByName($add_data['province_name']);
        if(!$province_info){
            return ['code'=>'200018'] ;
        }

        $city_info =  $area_obj->getInfoByName($add_data['city_name']);
        if(!$city_info){
            return ['code'=>'200019'] ;
        }

        $data['province'] = $province_info['area_id'];
        $data['city'] = $city_info['area_id'];
        $data['bank_no'] = str_replace("out","",$add_data['bank_no']);
        return ['code'=>1,'data'=>$data];

    }

    /**
     * 批量插入记录信息
     * @param $batch_insert_data
     * @param $admin_user_id
     * @return bool
     */
    public function batchInsertData($batch_insert_data,$admin_user_id){
        if(!$batch_insert_data){
            return false ;
        }

        //当前时间
        $now = date('Y-m-d H:i:s');

        foreach($batch_insert_data as $v){
            $add_data['order_no'] = $this->createOrderNo();
            $add_data['admin_user_id'] = $admin_user_id;
            $add_data['amount'] = $v['amount'];
            $add_data['coin_type'] = 'CNY';
            $add_data['bank_name'] = $v['bank_name'];
            $add_data['bank_account'] = $v['bank_account'];
            $add_data['bank_detail'] = $v['bank_detail'];
            $add_data['bank_no'] = $v['bank_no'];
            $add_data['province'] = $v['province'];
            $add_data['city'] = $v['city'];
            $add_data['status'] = 'TO_PAY';
            $add_data['is_deleted'] = 'N';
            $add_data['pay_time'] = NULL;
            $add_data['create_time'] = $now;
            $add_data['modify_time'] = $now;
            $this->baseInsert(self::tableName(),$add_data);
        }

        return true ;
    }

    public function getTotal($adminUserInfo,$start_time,$end_time){
        $where_cond[] = ' is_deleted=:is_deleted AND is_confirm =:is_confirm';
        $where_args[':is_deleted'] ='N';
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

        $today_out_total = $info ? $info['total'] : 0 ;

        $params['fields'] = 'count(1) as total';
        $total_num = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        $total_num = $total_num ? $total_num['total'] :0 ;
        $total_fee = $total_num * 3 ;

        /*
        $site_config_obj = new SiteConfig();
        $cash_out_fee_percent = $site_config_obj->getByKey('cash_out_fee_percent');

        $today_out_total = round($today_out_total*(100-$cash_out_fee_percent)/100 ,2 )*/ ;
        return $today_out_total - $total_fee ;
    }
}
