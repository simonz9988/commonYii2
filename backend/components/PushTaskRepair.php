<?php
/**
 * 维修售后
 */
namespace backend\components;

use Yii ;
use yii\base\Exception;
use yii\db\Expression;

use common\models\PushTask;
use common\models\Repair;
use common\models\Areas;
use backend\components\PushTaskCommon;


class PushTaskRepair extends PushTaskCommon
{
    /**
     * 维修推送任务
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($push_task_status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('REPAIR', $push_task_status);
        if(!$push_task_list){
            return false;
        }
        
        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);
        
        /*** 业务相关数据(需要根据不同的业务而定) start ***/
        $repair_model = new Repair();
        $repair_push_task_list = $repair_model->getRepairPushTaskList($business_ids);
        if(!$repair_push_task_list){
            return false;
        }
        /*** 业务相关数据 end ***/

        // 数据处理 追加 push_task_id,push_url 字段 (不用动)
        foreach($push_task_list as $row){
            if(isset($repair_push_task_list[$row['business_id']])){
                $repair_push_task_list[$row['business_id']]['push_task_id'] = $row['id'];
                $repair_push_task_list[$row['business_id']]['push_url'] = $row['push_url'];
            }
        }

        // 循环推送维修信息到售后中心 (不用动)
        foreach($repair_push_task_list as $row){
            $this->pushDataToAsm($row);
        }
    }
    
    /**
     * 发送售后单到售后中心
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToAsm($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];
        
        $push_task_model = new PushTask();
        $repair_model = new Repair();
    
        $now_time = date("Y-m-d H:i:s");
    
        // 推送相关数据处理
        $push_data = $this->getPushData($request_data);

        // 发送售后信息到中台
        $response_data = $this->pushDataToAsmCommon($request_data, $push_data);
        if(!$response_data){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新任务为已推送
            //售后工单号
            $oms_data = $response_data['data'] ;
            $asmOrderNo = $oms_data['asmOrderNo'];
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED', 'asm_order_no' => $asmOrderNo, 'modify_time' => $now_time]);
            
            // 更新维修状态为处理中
            // $repair_model->updateInfo($request_data['repair_id'], ['status' => 'PROCESSING', 'modify_time' => $now_time]);
            
            // 提交事务
            $transaction->commit();
            
            return true;
        }catch (Exception $e) {
            
            //回滚事务
            $transaction->rollback();

            return false;
        }
    }
    
    /**
     * 获取推送的维修数据
     * @param  array   $request_data
     * @return array
     */
    private function getPushData($request_data){
    
        // 售后编号（维修）
        $after_market_type = 4;
        $type_arr = $this->type_arr ;
        // 工单类型
        $order_type = $type_arr[$after_market_type];
    
        // 问题描述
        $problemDesc = $this->getProblemDescFromRepair($request_data);
        //如果有SN码，则填写在问题描述中
        if(isset($request_data['sn_number']) && $request_data['sn_number']){
            $problemDesc = 'SN码：'.$request_data['sn_number'].$problemDesc;
        }
        $rst['shopNo'] = $this->shopNo;
        $rst['orderStaffGroupId'] = 30;                                               // 客服组id
        $rst['originalOrderNo'] = '';                                                 // 渠道来源单号
        $rst['problemDesc'] = $problemDesc;                                           // 问题描述
        $rst['customerNick'] = $request_data['username'];                             // 客户名称 对应user表的username
        $rst['customerName'] = $request_data['buy_realname'];                         // 顾客姓名
        $rst['customerPhone'] = $request_data['mobile'];                              // 顾客手机
        $rst['orderType'] = $order_type;
        $rst['callBackNotifyUrl'] = $this->getWorkOrderCallbackUrlFromRepair();       // 售后回调地址
        $rst['validateCode'] = $this->createValidateCodeFromRepair($request_data);    // 发往售后唯一标识符
        $rst['orderSource'] = 'asm_order_source_ecovacs';
        $rst['contactInfo'] = $this->getRepairContactInfo($request_data);
        $rst['fileItemDTOList'] = $this->getFileItemDtoList($request_data);           // 附件信息
        $rst['orderConfirm'] = 'CONFIRM_QUALITY';                                     // 问题确认
        // 购买时间
        if(!empty($request_data['buy_time']) && $request_data['buy_time'] != '0000-00-00 00:00:00'){
            $rst['buyTimeStr'] = $request_data['buy_time'];
        }
        
        $order_info = array();
        // 不同业务的数据处理
        $rst = $this->formateOmsDataByAftermarketType($rst, $request_data, $order_info,'online') ;
        
        return $rst ;
    }
    
    /**
     *获取维修问题描述
     * @param  array   $after_market_info [description]
     * @return string
     */
    private function getProblemDescFromRepair($after_market_info){
        
        $rst = '故障说明：'.$after_market_info['description'].'|产品名称：'.$after_market_info['product_model'].'|来源单号：'.$after_market_info['repair_no'];
        
        return $rst ;
    }
    
    /**
     * 返回维修联系信息
     * @param   $doc_info  售后单信息
     * @return  string
     */
    private function getRepairContactInfo($doc_info){
        $rst = '';
        
        if($doc_info){
            
            $contact = isset($doc_info['buy_realname'])?$doc_info['buy_realname']:'';
            $mobile = isset($doc_info['mobile'])?$doc_info['mobile']:'';
            $province = isset($doc_info['province'])?$doc_info['province']:'';
            $city = isset($doc_info['city'])?$doc_info['city']:'';
            $area = isset($doc_info['area'])?$doc_info['area']:'';
            $area_obj = new Areas();
            $province = $area_obj->getAreaName($province);
            $city = $area_obj->getAreaName($city);
            $area = $area_obj->getAreaName($area);
            
            $address = isset($doc_info['address'])?$doc_info['address']:'';
            $address = $province.$city.$area.$address;
            
            $rst .= '申请人姓名：'.$contact.'，';
            $rst .= '申请人手机：'.$mobile.'，';
            $rst .= '申请人地址：'.$address;
        }

        return $rst ;
    }
    
    /**
     * 附件信息处理
     * @param  array  $data
     * @return array
     */
    private function getFileItemDtoList($data){
        $res = [];
        if(isset($data['att_list']) && !empty($data['att_list'])){
            foreach($data['att_list'] as $row){
                $res[] = [
                    'fileName' => ($row['attach_type'] == 'IMAGE' ? '图片' : '视频'),
                    'filePath' => $row['attach_url'],
                ];
            }
        }
        return $res;
    }

}
