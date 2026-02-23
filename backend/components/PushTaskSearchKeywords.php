<?php
/**
 * 关键字词典生成
 */
namespace backend\components;

use common\models\Order;
use Yii ;
use yii\base\Exception;
use yii\db\Expression;

use common\models\PushTask;
use common\models\Areas;
use common\models\TradeInApply;
use common\models\TradeInAfterMarketDoc;
use backend\components\PushTaskCommon;


class PushTaskSearchKeywords extends PushTaskCommon
{
    /**
     * 关键字词典生成任务
     *
     * @param  string $push_task_status 未推送：NOPUSH 已推送：PUSHED 推送失败：FAILED 已关闭：CLOSED
     * @return bool
     */
    public function doPushTask($push_task_status){

        // 获取未推送过的维修信息 (不用动)
        $push_task_model = new PushTask();
        $push_task_list = $push_task_model->getPushTaskList('SEARCH_KEYWORDS', $push_task_status);
        if(!$push_task_list){
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            //生成txt文件
            $params['cond'] = 'status=:status AND is_deleted =:is_deleted';
            $params['args'] = [':status'=>'ENABLED','is_deleted'=>'N'] ;
            $params['fields'] = 'id';
            $total_num =  $push_task_model->findCountByWhere('sdb_search_keywords',$params);
            $limit = 5000;
            $page = ceil($total_num/$limit) ;
            $string = "";
            for ($i=0;$i<$page;$i++){
                $db = new \yii\db\Query();
                $query = $db ->select("*")->from("sdb_search_keywords");
                $query->where('status=:status AND is_deleted =:is_deleted',[':status'=>'ENABLED','is_deleted'=>'N']);
                $offset = $i*$limit  ;

                $list = $query->offset($offset)
                    ->limit($limit)
                    ->all();
                if($list){
                    foreach($list as $v){

                        $string .= $v['keyword']."\t".$v['tf']."\t".$v['idf']."\t".$v["attr"]."\n";
                    }
                }

            }
            $file = dirname(__FILE__).'/../../backend/runtime/dict.txt';

            file_put_contents ($file, $string);
            //执行脚本
            $sh_file = dirname(__FILE__).'/../../backend/runtime/dict.utf8.xdb.sh ';
            exec($sh_file);

            // 更新任务为已推送
            $push_task_model->baseUpdate('sdb_push_task',['status' => 'PUSHED', 'modify_time' => date('Y-m-d H:i:s')],'business_type=:business_type',[":business_type"=>'SEARCH_KEYWORDS']);

            // 提交事务
            $transaction->commit();

            return true;
        }catch (Exception $e) {

            //回滚事务
            $transaction->rollback();

            return false;
        }

    }
    

}
