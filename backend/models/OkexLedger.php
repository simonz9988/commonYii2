<?php

namespace backend\models;

use common\components\PlatformTradeCommonV4;
use Yii;

/**
 * This is the model class for table "sea_okex_ledger".
 *
 * @property int $id
 * @property string $amount
 * @property string $balance
 * @property string $fee
 * @property string $from
 * @property string $to
 * @property string $currency
 * @property string $type
 * @property string $instrument_id
 * @property string $ledger_id
 * @property string $timestamp
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class OkexLedger extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_okex_ledger';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount', 'balance', 'fee', 'from', 'to', 'currency', 'type', 'instrument_id', 'ledger_id', 'timestamp'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['amount', 'balance', 'fee', 'from', 'to', 'currency', 'type', 'instrument_id', 'ledger_id', 'timestamp'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amount' => 'Amount',
            'balance' => 'Balance',
            'fee' => 'Fee',
            'from' => 'From',
            'to' => 'To',
            'currency' => 'Currency',
            'type' => 'Type',
            'instrument_id' => 'Instrument ID',
            'ledger_id' => 'Ledger ID',
            'timestamp' => 'Timestamp',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 判断是否已经插入
     * @param $ledger_id
     * @return mixed
     */
    public function checkExistsByLedgerId($ledger_id){

        $params['cond'] = 'ledger_id=:ledger_id';
        $params['args'] = [':ledger_id'=>$ledger_id];
        $list = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 返回类型名称
     * @param $type
     * @return string
     */
    public function typeName($type)
    {
        $arr[9] = '永续合约账户';
        $arr[6] = '资金账户';
        return isset($arr[$type]) ? $arr[$type] : '未知';
    }
    /**
     * 同步订单
     * @param $id
     * @return mixed
     */
    public function syncOrder($id){
        $type = 6 ;
        $limit = 100;
        $api_key_obj =  new AdminApiKey() ;
        $api_key_info = $api_key_obj->getInfoById($id);
        $admin_user_id = $api_key_info['admin_user_id'];
        $coin = $api_key_info['coin'];
        $trade_obj = new PlatformTradeCommonV4();
        $trade_obj->setConfigInfo($admin_user_id,$api_key_info,$coin,'up') ;
        $list = $trade_obj->queryLedgerByType($coin, $type, $limit);
        if($list){
            // 当前时间
            $now = date('Y-m-d H:i:s');

            foreach($list as $v){

                $ledger_id = $v['ledger_id'];
                $exists_info = $this->checkExistsByLedgerId($ledger_id);
                if($exists_info){
                    continue ;
                }
                $details = $v['details'] ;
                $from = $details['from'] ;
                $to = $details['to'] ;
                $add_data['amount'] = $v['amount'];
                $add_data['admin_user_id'] = $admin_user_id;
                $add_data['admin_user_note'] = $api_key_info['note'];
                $add_data['balance'] = $v['balance'];
                $add_data['fee'] = $v['fee'];
                $add_data['from'] = $from;
                $add_data['to'] = $to;
                $add_data['currency'] = $v['currency'];
                $add_data['type'] = $v['type'];
                $add_data['instrument_id'] = $v['instrument_id'];
                $add_data['ledger_id'] = $v['ledger_id'];
                $add_data['timestamp'] = $v['timestamp'];
                $add_data['create_time'] = $now;
                $add_data['modify_time'] = $now;
                $this->baseInsert(self::tableName(),$add_data) ;
            }
        }

    }
}
