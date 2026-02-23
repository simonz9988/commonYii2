<?php

namespace common\models;

use common\components\OkexTrade;
use Yii;

/**
 * This is the model class for table "sdb_cancel_symbol_task".
 *
 * @property string $id
 * @property string $user_symbol_id 用户币种ID
 * @property string $symbol
 * @property string $apiKey
 * @property string $secretKey
 * @property int $status 撤单状态 跟着订单状态走
 * @property string $deal_status 任务处理状态(UNDEAL-未处理 DEALED-已处理)
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class CancelSymbolTask extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_cancel_symbol_task';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_okex');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['user_symbol_id', 'symbol', 'apiKey', 'secretKey'], 'string', 'max' => 255],
            [['deal_status'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_symbol_id' => 'User Symbol ID',
            'symbol' => 'Symbol',
            'apiKey' => 'Api Key',
            'secretKey' => 'Secret Key',
            'status' => 'Status',
            'deal_status' => 'Deal Status',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public function dealOrder($order_id){

    }

    /**
     * 处理取消币种的销售
     * @param $symbol
     * @param $task_id
     */
    public function dealSell($symbol,$task_id){

        //获取当前币种的最新价格
        $okex_trade_model = new OkexTrade();
        $last_price = $okex_trade_model->getLastPrice($symbol);

        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$task_id];
        $task_row = $this->findOneByWhere('sdb_cancel_symbol_task',$params,self::getDb());
        $apiKey = $task_row['apiKey'];
        $secretKey = $task_row['secretKey'];
        $order_id = $okex_trade_model->doSell($apiKey,$secretKey,$symbol,$last_price);
        //$order_id = 9999 ;

        if($order_id){

            //step1 更新任务状态
            $updata_data['deal_status'] = 'DEALING';
            $this->baseUpdate('sdb_cancel_symbol_task',$updata_data,'id =:id',[':id'=>$task_id],'db_okex');

            //step2 添加日志
            $add_add['task_id']  = $task_id;
            $add_add['order_id']  = $order_id;
            $add_add['status']  = 'PROCESSING';
            $add_add['create_time']  = date('Y-m-d H:i:s');
            $add_add['update_time']  = date('Y-m-d H:i:s');
            $this->baseInsert('sdb_cancel_symbol_task_order',$add_add,'db_okex');

        }

        return $order_id ;


    }

    /**
     * 取消售出的后续操作
     * @param $symbol
     * @param $apiKey
     * @param $secretKey
     * @param $user_symbol_id
     */
    public function doAfterCancelSellByCancel($symbol,$apiKey,$secretKey,$user_symbol_id,$order_id){


        //step1 通过订单ID查找任务ID
        $params['cond'] = 'order_id =:order_id' ;
        $params['args'] = [':order_id'=>$order_id];
        $task_order_row = $this->findOneByWhere('sdb_cancel_symbol_task_order',$params,self::getDb());
        $task_id = $task_order_row['task_id'];

        //step2  更新 状态为已处理
        //sdb_cancel_symbol_task  ==>DEALED
        $task_where_str = 'id=:id';
        $task_where_arr[':id'] = $task_id ;
        $this->baseUpdate('sdb_cancel_symbol_task',['deal_status'=>'DEALED'],$task_where_str,$task_where_arr,'db_okex');

        //step3 更新取消订单的状态为取消
        //sdb_cancel_symbol_task_order  ==>CANCEL
        $task_order_where_str = 'order_id =:order_id';
        $task_order_where_arr = [':order_id'=>$order_id];
        $this->baseUpdate('sdb_cancel_symbol_task_order',['status'=>'CANCEL'],$task_order_where_str,$task_order_where_arr,'db_okex');

        //step4 新建售出任务
        //sdb_cancel_symbol_task 一条任务
        $add_data['user_symbol_id'] = $user_symbol_id ;
        $add_data['symbol'] = $symbol ;
        $add_data['apiKey'] = $apiKey ;
        $add_data['secretKey'] = $secretKey;
        $add_data['deal_status'] = 'UNDEAL' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['update_time'] = date('Y-m-d H:i:s') ;

        $this->baseInsert('sdb_cancel_symbol_task',$add_data,'db_okex');
    }


    /**
     * 成功售出处理相关数据
     * @param $order_id
     */
    public function dealSellSuccess($order_id){


        //step1 通过订单ID查找任务ID
        $params['cond'] = 'order_id =:order_id' ;
        $params['args'] = [':order_id'=>$order_id];
        $task_order_row = $this->findOneByWhere('sdb_cancel_symbol_task_order',$params,self::getDb());
        $task_id = $task_order_row['task_id'];

        //step2 更新任务状态
        $task_where_str = 'id=:id';
        $task_where_arr[':id'] = $task_id ;
        $this->baseUpdate('sdb_cancel_symbol_task',['deal_status'=>'DEALED'],$task_where_str,$task_where_arr,'db_okex');

        //step3 更新任务订单
        //sdb_cancel_symbol_task_order  ==>CLOSED
        $task_order_where_str = 'order_id =:order_id';
        $task_order_where_arr = [':order_id'=>$order_id];
        $this->baseUpdate('sdb_cancel_symbol_task_order',['status'=>'CLOSED'],$task_order_where_str,$task_order_where_arr,'db_okex');


        //step4 更新用户币种状态
        //sdb_user_symbol  =>disabled
        $params['cond'] = 'id =:id' ;
        $params['args'] = [':id'=>$task_id];
        $task_row = $this->findOneByWhere('sdb_cancel_symbol_task_order',$params,self::getDb());
        $user_symbol_id = $task_row['user_symbol_id'];
        $this->baseUpdate('sdb_user_symbol',['status'=>'disabled'],'id=:id',[':id'=>$user_symbol_id],'db_okex');
    }
}
