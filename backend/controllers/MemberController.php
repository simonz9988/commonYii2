<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminApiKey;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminTotalApiKey;
use backend\models\CoinAddressValue;
use backend\models\OkexLedger;
use backend\models\OkexTotalOrder;
use backend\models\PlatformTotalOrder;
use common\components\CoinBalance;
use common\components\PlatformTradeCommonV4;
use common\models\Member;
use common\models\MiningMachineEarn;
use common\models\MiningMachineFrozenEarn;
use common\models\MiningMachineUserBalance;
use common\models\RobotUserBalance;
use common\models\SiteConfig;
use common\models\UserWallet;

/**
 * System
 */
class MemberController extends BackendController
{
    public function actionList(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $mobile = isset($_GET['mobile']) ? $_GET['mobile'] : '' ;

        if($mobile){
            $params['like_arr']['mobile'] = $mobile;
        }
        $searchArr['mobile'] = $mobile ;

        $email = isset($_GET['email']) ? $_GET['email'] : '' ;

        if($email){
            $params['like_arr']['email'] = $email;
        }
        $searchArr['email'] = $email ;

        $type = isset($_GET['type']) ? $_GET['type'] : '' ;

        if($type){
            $params['where_arr']['type'] = $type;
        }
        $searchArr['type'] = $type ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new Member() ;
        $list = $model->findByWhere('sea_user',$params, $model::getDb());
        if($list){
            $frozen_obj  = new MiningMachineFrozenEarn();
            $earn_obj = new MiningMachineEarn();
            $user_balance_obj = new MiningMachineUserBalance();
            $robot_user_balance_obj = new  RobotUserBalance();
            foreach($list as $k=>$v){
                $user_id = $v['id'];
                // 可提现总额
                //$fil_balance_info = $user_balance_obj->getInfoByUserIdAndCoin($user_id,'FIL') ;
                $list[$k]['out_total'] = 0;
                $list[$k]['frozen_total'] = 0 ;
                // 已获得(FIL)
                $list[$k]['release_total'] = 0;
                $list[$k]['robot_balance'] = $robot_user_balance_obj->getTotalBalanceByCoin($user_id,'SCA') ;
                $list[$k]['frozen_balance'] = $robot_user_balance_obj->getTotalFrozenBalanceByCoin($user_id,'USDT') ;
            }


        }

        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['return_field'] = 'id';
        $list = $model->findByWhere('sea_user',$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        return $this->render('list',$renderData) ;
    }

    // 后台新增用户
    public function actionEdit1(){
        $data = [] ;
        $this->loadResource('member','actionEdit1');
        return $this->render('edit',$data);
    }

    public function actionSave1(){
        $mobile = $this->postParam('mobile');
        $password = $this->postParam('password1');
        $invite_code = $this->postParam('invite_code');
        $cash_password = $this->postParam('cash_password');

        // 判断手机号码是否存在
        $member_obj = new Member();
        $user_info = $member_obj->getInfoByMobile($mobile);
        if($user_info){
            return $this->returnJson(['code'=>'200030','msg'=>getErrorDictMsg(200030)]);
        }

        $user_wallet_obj = new UserWallet() ;
        $user_wallet_info = $user_wallet_obj->getUsefulInfo();
        if(!$user_wallet_info){
            return $this->returnJson(['code'=>'200031','msg'=>getErrorDictMsg(200031)]);
        }

        $invite_user_info = $member_obj->getInfoByInviteCode($invite_code);

        // 当前时间
        $now = date('Y-m-d H:i:s');
        // 执行注册
        $type = 'PERSON' ;
        $add_data['username'] = $member_obj->createUserName($type) ;
        $add_data['password'] = md5($password) ;
        $add_data['cash_password'] = md5($cash_password) ;
        $add_data['email'] = '' ;
        $add_data['nickName'] = '' ;
        $add_data['avatarUrl'] = '' ;
        $add_data['address'] = '' ;
        $add_data['trans_eth_address'] = $user_wallet_info['address'] ;
        $add_data['type'] = $type ;
        $add_data['reg_from'] = 'BACKEND' ;
        $add_data['audit_status'] = 'SUCCESS' ;
        $add_data['audit_idea'] = '' ;
        $add_data['mobile'] = $mobile ;
        $add_data['inviter_user_id'] = $invite_user_info ? $invite_user_info['id'] : 0;

        $invite_code = $member_obj->createInviteCode();
        $add_data['invite_code'] = $invite_code ;
        $add_data['inviter_username'] = $invite_user_info ? $invite_user_info['username'] :'' ;
        $add_data['user_root_path'] = $invite_user_info ? $invite_user_info['user_root_path'].$invite_user_info['id'].'--':'--0--' ;
        $add_data['user_level'] = $invite_user_info ? $invite_user_info['user_level'] + 1 : 0  ;
        $add_data['is_open'] = 1  ;
        $add_data['last_login'] = NULL  ;
        $add_data['create_time'] = $now  ;
        $add_data['modify_time'] = $now  ;
        $user_id = $member_obj->baseInsert('sea_user',$add_data);

        //更新地址池信息
        $user_wallet_update_data['user_id'] = $user_id ;
        $user_wallet_update_data['mobile'] = $mobile ;
        $user_wallet_update_data['modify_time'] = date('Y-m-d H:i:s') ;
        $user_wallet_obj->baseUpdate($user_wallet_obj::tableName(),$user_wallet_update_data,'id=:id AND user_id=0',[':id'=>$user_wallet_info['id']]);
        //更新节点信息
        $member_obj->addParentTools($user_id,$invite_user_info['id']);
        return $this->returnJson(['code'=>'1','msg'=>getErrorDictMsg(1)]);


    }

    /**
     * 设置为超级超级用户
     */
    public function actionSaveSpecial(){

        $user_id = $this->postParam('user_id');
        $is_special= $this->postParam('is_special');
        $type = $is_special =='Y'?'SPECIAL':'PERSON';
        $member_obj = new Member() ;
        $update_data['type'] = $type ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;

        $member_obj->baseUpdate('sea_user',$update_data,'id=:id',[':id'=>$user_id]);

        responseJson(['code'=>1]);
    }

    // 增加账户余额
    public function actionAddBalance(){

        $user_id = $this->postParam('user_id');
        $total = $this->postParam('total');

        if(!is_numeric($total)){
            responseJson(['code'=>'200038','msg'=>getErrorDictMsg(200038)]);
        }

        $type = $this->postParam('type');
        $member_obj = new Member();
        $blockHash = time().mt_rand(100000,999999) ;
        if($type =='add'){
            $member_obj->addMiningMachineCashInRecord($user_id,$total,$blockHash,true);

        }else{
            $member_obj->reduceUsdtByAdmin($user_id,$total);
        }

        responseJson(['code'=>1]);
    }

    // 增加token的 操作目前只限定用于SCA的操作
    public function actionAddTokenBalance(){

        $user_id = $this->postParam('user_id');
        $total = $this->postParam('total');

        if(!is_numeric($total)){
            responseJson(['code'=>'200038','msg'=>getErrorDictMsg(200038)]);
        }

        $type = $this->postParam('type');
        //$robot
        $balance_obj = new RobotUserBalance();
        $coin = 'SCA' ;
        $balance_obj->opByCoinAndType($user_id,$coin,$type,$total);

        responseJson(['code'=>1]);
    }

    // 增加冻结余额
    public function actionAddFrozenBalance(){

        $user_id = $this->postParam('user_id');
        $total = $this->postParam('total');

        if(!is_numeric($total)){
            responseJson(['code'=>'200038','msg'=>getErrorDictMsg(200038)]);
        }

        $type = $this->postParam('type');
        //$robot
        $balance_obj = new RobotUserBalance();
        $balance_obj->opFrozenUsdt($user_id,$type,$total);

        responseJson(['code'=>1]);
    }

}
