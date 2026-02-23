<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminApiKey;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminTotalApiKey;
use backend\models\OkexTotalOrder;
use backend\models\PlatformTotalOrder;
use common\components\PlatformTradeCommonV4;
use common\models\AdminBank;
use common\models\CashIn;
use common\models\SiteConfig;

/**
 * System
 */
class CallbackController extends \common\controllers\BaseController
{
    /**
     * 获取银行账户信息
     */
    public function actionGetBankInfo(){
        $this->verifySign(true);

        $adminUserInfo = $this->secretAdminUserInfo;
        $admin_user_id = $adminUserInfo['id'];
        $admin_bank_model = new AdminBank();
        $params['cond'] = 'admin_user_id=:admin_user_id';
        $params['args'] = [':admin_user_id'=>$admin_user_id];
        $params['fields'] = 'name,telphone,address,website,bank_no,bank_name,bank_address,alipay_no,bank_username';
        $info = $admin_bank_model->findOneByWhere($admin_bank_model::tableName(),$params,$admin_bank_model::getDb());
        return $this->returnJson(['code'=>1,'msg'=>'ok','data'=>$info]);
    }

    public function actionOrder(){

        $this->verifySign(true);
        $order_no = $this->postParam('orderNo');

        $cash_in_model = new CashIn() ;
        $order_exits = $cash_in_model->getInfoByOrderNo($order_no);
        if($order_exits){
            return $this->returnJson(['code'=>200020,'msg'=>getErrorDictMsg(200020)]);
        }
        $adminUserInfo = $this->secretAdminUserInfo;

        $admin_user_id = $adminUserInfo['id'];
        $name = 'USE';
        $pay_time = date('Y-m-d H:i:s',$this->postParam('payTime'));
        $pay_name = $this->postParam('payName');
        $amount = $this->postParam('amount');
        $callback_url = $this->postParam('callbackUrl');
        $coin_type = $this->postParam('coinType');
        if(!in_array($coin_type,['CNY'])){
            return $this->returnJson(['code'=>'200022','msg'=>getErrorDictMsg(200022)]);
        }
        $pay_type = $this->postParam('payType');
        if(!in_array($pay_type,['ALIPAY','CARD'])){
            return $this->returnJson(['code'=>'200023','msg'=>getErrorDictMsg(200023)]);
        }
        $pay_status = 'TO_PAY';
        $source = 'PC';
        $note = $this->postParam('note');
        $is_deleted = 'N';
        $is_confirm = 'N';
        $create_time = date('Y-m-d H::i:s');
        $modify_time = date('Y-m-d H::i:s');

        $add_data = compact('callback_url','order_no','admin_user_id','name','pay_time','pay_name','coin_type','pay_type','pay_status','source','note','is_deleted','is_confirm','create_time','modify_time','amount');
        $id = $cash_in_model->baseInsert($cash_in_model::tableName(),$add_data);
        if($id){
            return $this->returnJson(['code'=>1,'msg'=>'ok']);
        }else{
            return $this->returnJson(['code'=>200021,'msg'=>getErrorDictMsg('200021')]);
        }
    }

    //  判断订单是否存在
    public function actionCheckOrder(){

        $this->verifySign(true);
        $order_no = $this->postParam('orderNo');
        $coin_type = $this->postParam('coinType');
        $amount = $this->postParam('amount');
        $cash_in_model = new CashIn() ;
        $order_exits = $cash_in_model->getInfoByOrderNo($order_no);
        if(!$order_exits){
            return $this->returnJson(['code'=>200025,'msg'=>getErrorDictMsg(200025)]);
        }

        $adminUserInfo = $this->secretAdminUserInfo;
        $admin_user_id = $adminUserInfo['id'];
        if($admin_user_id != $order_exits['admin_user_id']){
            return $this->returnJson(['code'=>200025,'msg'=>getErrorDictMsg(200025)]);
        }

        if($coin_type != $order_exits['coin_type']){
            return $this->returnJson(['code'=>200026,'msg'=>getErrorDictMsg(200026)]);
        }

        if($amount > $order_exits['amount'] || $amount > $order_exits['amount']){
            return $this->returnJson(['code'=>200027,'msg'=>getErrorDictMsg(200027)]);
        }

        $data['pay_status'] = $order_exits['pay_status'] ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);


    }

    // 判断支付成功
    public function actionPaySuccess(){

        $this->verifySign(true);
        $order_no = $this->postParam('orderNo');
        $coin_type = $this->postParam('coinType');
        $amount = $this->postParam('amount');
        $cash_in_model = new CashIn() ;
        $order_exits = $cash_in_model->getInfoByOrderNo($order_no);
        if(!$order_exits){
            return $this->returnJson(['code'=>200025,'msg'=>getErrorDictMsg(200025)]);
        }

        $adminUserInfo = $this->secretAdminUserInfo;
        $admin_user_id = $adminUserInfo['id'];
        if($admin_user_id != $order_exits['admin_user_id']){
            return $this->returnJson(['code'=>200025,'msg'=>getErrorDictMsg(200025)]);
        }

        if($coin_type != $order_exits['coin_type']){
            return $this->returnJson(['code'=>200026,'msg'=>getErrorDictMsg(200026)]);
        }

        if($amount > $order_exits['amount'] || $amount > $order_exits['amount']){
            return $this->returnJson(['code'=>200027,'msg'=>getErrorDictMsg(200027)]);
        }

        $data['pay_status'] = $order_exits['pay_status'] ;
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$data]);

    }

}
