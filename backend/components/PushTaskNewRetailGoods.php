<?php
/**
 * 新零售商品相关推送
 */
namespace backend\components;

use common\models\Goods;
use Yii ;
use yii\base\Exception;

use common\models\PushTask;


class PushTaskNewRetailGoods extends PushTaskCommon
{

    /**
     * 新零售推送任务
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($push_task_status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('NEW_RETAIL_GOODS', $push_task_status);
        if(!$push_task_list){
            return false;
        }

        // 返回业务id (不用动)
        $business_ids = $push_task_model->getPushTaskBusinessId($push_task_list);
        
        /*** 业务相关数据(需要根据不同的业务而定) start ***/
        $goods_model = new Goods();
        $fields = 'id,name,img,ecovacs_goods_no,mobile_sell_price,sell_price,goods_type,globalapp_up_status,is_weixin_mini_show';
        $goods_push_task_list = $goods_model->getNewRetailSyncGoodsList($business_ids,$fields);
        if(!$goods_push_task_list){
            return false;
        }
        /*** 业务相关数据 end ***/

        // 数据处理 追加 push_task_id,push_url 字段
        foreach($push_task_list as $row){
            if(isset($goods_push_task_list[$row['business_id']])){
                $goods_push_task_list[$row['business_id']]['push_task_id'] = $row['id'];
                $goods_push_task_list[$row['business_id']]['push_url'] = $row['push_url'];
            }
        }

        // 将信息循环推送到门店
        foreach($goods_push_task_list as $row){
            $this->pushDataToStore($row);
        }
    }
    
    /**
     * 发送商品信息到门店
     * @param  array $request_data 请求信息
     * @return bool
     */
    public function pushDataToStore($request_data){
        // 任务id sdb_push_task 主键
        $push_task_id = $request_data['push_task_id'];
        
        $push_task_model = new PushTask();

        // 当前时间
        $now_time = date("Y-m-d H:i:s");

        // 小程序上架才会做任务推送、物料号为空的也不推送 此时日志库task_work_order_log是不产生日志的
        $is_weixin_mini_show = $request_data['is_weixin_mini_show'] ;

        $ecovacs_goods_no = $request_data['ecovacs_goods_no'] ;

        if($is_weixin_mini_show == 'Y' && $ecovacs_goods_no ){

            // 推送相关数据处理
            $push_data = $this->getPushData($request_data);

            // 发送售后信息到中台
            $response_data = $this->pushDataToNewRetailCommon($request_data, $push_data);
            if(!$response_data){
                return false;
            }
        }


        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 更新任务为已推送
            $push_task_model->updateInfo($push_task_id, ['status' => 'PUSHED', 'modify_time' => $now_time]);
            
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
     * 格式化需要推送给门店的数据
     * @param  array   $request_data
     * @return array
     */
    private function getPushData($request_data){

        // 商品物料号
        $res['materialNo'] = $request_data['ecovacs_goods_no'] ;
        // 销售价
        $res['salesPrice'] = $request_data['mobile_sell_price'] ?  $request_data['mobile_sell_price'] :  $request_data['sell_price'];
        // 商品名称
        $res['productName'] = $request_data['name'] ;
        // 是否为赠品
        $res['giftFlag'] = $request_data['goods_type'] =='2' ? 'Y':'N';

        //商品ID
        $goods_id = $request_data['id'];
        $res['gwProductId'] = $goods_id;

        //商品主图 不存在传默认图
        $img = $request_data['img'] ;
        $res['productImgUrl'] = $img ? static_url($img) : 'http://static.ecovacs.cn/p/images/nopic.gif';

        // 查询商品分类
        $model = new Goods();
        $params['cond'] = 'goods_id=:goods_id';
        $params['args'] = [':goods_id'=>$goods_id] ;
        $category_extend_info = $model->findOneByWhere('sdb_category_extend',$params) ;

        $productClassification = 'OTHER';
        if($category_extend_info){

            $top_cate_id = [1,5,9,12];
            // 商品品类 OTHER("其他"), DB("地宝") -1 ,CB("窗宝") -5,Q4B("沁宝")-9,UNIBOT("UNIBOT")-12;
            $top_cate_info = [1=>'DB',5=>'CB',9=>'Q4B',12=>'UNIBOT'];
            $category_id = $category_extend_info['category_id'];
            if(in_array($category_id,$top_cate_id)){

                $productClassification = $top_cate_info[$category_id];

            }else{

                //产需父类分类ID信息
                $parent_params['cond'] = 'id=:id';
                $parent_params['args'] = [':id'=>$category_id];
                $parent_params['fields'] = 'id,parent_id';
                $parent_info = $model->findOneByWhere('sdb_category',$parent_params);
                if( $parent_info){
                    if(in_array($parent_info['id'],$top_cate_id)){
                        $productClassification = $top_cate_info[$parent_info['id']];
                    }else{
                        if( in_array($parent_info['parent_id'],$top_cate_id) ){
                            $productClassification = $top_cate_info[$parent_info['parent_id']];
                        }
                    }
                }
            }
        }

        $res['productClassification'] = $productClassification;

        return $res ;
    }
    


}
