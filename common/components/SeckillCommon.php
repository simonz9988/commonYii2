<?php
namespace common\components;

use common\models\Seckill;
use common\models\SeckillGoods;
use common\models\SeckillRecord;
use yii\db\Expression;
use yii;

/**
 * 秒杀相关公用类
 */

class SeckillCommon
{
    /**
     * 秒杀库存回滚
     * @param int $seckill_id 秒杀活动ID
     * @param int $user_id 用户ID
     * @param int $rollback_status 秒杀记录回滚状态值 0 => '未回滚',1 => '订单未支付回滚',2 => '未及时下单回滚',3 => '订单支付完成',4 => '前台订单取消库存回滚',5 => '后台订单取消库存回滚'
     * @param string $operate_user_name 操作人用户名(脚本执行时需要传值)
     * @return boolean
     */

    public function stockRollBack($seckill_id,$user_id,$rollback_status,$operate_user_name=""){


        //允许回滚库存的状态合集
        $allow_rollback_status = [1,2,4,5];

        if(!in_array($rollback_status,$allow_rollback_status)){
            return false;
        }



        //step1 查看当前秒杀活动是否还在进行
        $now_time = time();
        $seckill_obj = new Seckill();
        $seckill_info = $seckill_obj->getInfoById($seckill_id);

        if(!$seckill_info){
            return false;
        }

        //step2 查看活动是否在进行中
        if((strtotime($seckill_info['start_time']) > $now_time) || (strtotime($seckill_info['end_time']) < $now_time)){
            return false;
        }

        //step3 获取秒杀商品信息
        $seckill_goods_obj = new SeckillGoods();
        $seckill_goods_info = $seckill_goods_obj->findOneByWhere('sdb_seckill_goods',['cond'=>'seckill_id = :id','args'=>[':id'=>$seckill_id]]);

        if(!$seckill_goods_info){
            return false;
        }

        $goods_id = $seckill_goods_info['goods_id'];

        //step4 秒杀商品剩余库存回滚

        $result = $seckill_goods_obj->baseUpdate('sdb_seckill_goods',['surplus_stock'=> new Expression("surplus_stock + 1")],'seckill_id = :seckill_id AND goods_id =:goods_id AND surplus_stock < total_stock',[':seckill_id'=>$seckill_id,':goods_id'=>$goods_id]);

        if(!$result){
            return false;
        }

        //查看秒杀商品库存队列
        $seckill_goods_stock_queue_key='seckill_'.$seckill_id.'_goods_'.$goods_id;
        $queue_length = Yii::$app->MyRedis->llen($seckill_goods_stock_queue_key);

        //如果库存队列长度已超过秒杀商品总库存数 则队列不增加
        if($seckill_goods_info['total_stock'] < ($queue_length + 1)){
            return false;
        }
        //库存队列加一
        $redis_result = Yii::$app->MyRedis->lpush($seckill_goods_stock_queue_key,1);
        if(!$redis_result){
            return false;
        }

        //更新对应的秒杀记录
        $seckill_record_obj = new SeckillRecord();
        $search = [
            'seckill_id' => $seckill_id,
            'goods_id' => $goods_id,
            'user_id' => $user_id
        ];
        $rs = $seckill_record_obj->updateSeckillRecord($search,$rollback_status);

        if($rs){

            $old_data = [
                'seckill_id'=>$seckill_id,
                'goods_id'=>$goods_id,
                'user_id'=>$user_id,
                'status'=>'enabled',
                'rollback_status'=>0
            ];
            $new_data = [
                'seckill_id'=>$seckill_id,
                'goods_id'=>$goods_id,
                'user_id'=>$user_id,
                'status'=>'disabled',
                'rollback_status'=>$rollback_status
            ];
            //记录日志
            $seckill_record_obj->insertLogData($old_data,$new_data,$seckill_id,$rollback_status,$operate_user_name);

        }

    }
}