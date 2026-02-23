<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mining_machine".
 *
 * @property int $id
 * @property string $title 标题
 * @property string $sub_title 副标题
 * @property string $price 单价(USDT)
 * @property string $coin 币种
 * @property int $period 结算周期(T+1/2/0)
 * @property int $limit_day 服务期限
 * @property string $is_pre_sell 是否预售
 * @property string $fee 服务费
 * @property string $cover_img_url 封面图片
 * @property string $content 内容
 * @property string $seo_title
 * @property string $seo_keywords
 * @property string $seo_description
 * @property int $sort 排序
 * @property string $status ENABLED DISABLED
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MiningMachine extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price', 'fee'], 'number'],
            [['period', 'limit_day', 'sort'], 'integer'],
            [['content'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['title', 'sub_title', 'cover_img_url', 'seo_title', 'seo_keywords', 'seo_description', 'status'], 'string', 'max' => 255],
            [['coin'], 'string', 'max' => 50],
            [['is_pre_sell', 'is_deleted'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'sub_title' => 'Sub Title',
            'price' => 'Price',
            'coin' => 'Coin',
            'period' => 'Period',
            'limit_day' => 'Limit Day',
            'is_pre_sell' => 'Is Pre Sell',
            'fee' => 'Fee',
            'cover_img_url' => 'Cover Img Url',
            'content' => 'Content',
            'seo_title' => 'Seo Title',
            'seo_keywords' => 'Seo Keywords',
            'seo_description' => 'Seo Description',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回行级信息
     * @param $id
     * @param string $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){

        $params['cond'] = 'id=:id AND  is_deleted=:is_deleted';
        $params['args'] = [':id'=>$id,':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取所有信息
     * @return array
     */
    public function getAll(){
        $params['cond'] = 'is_deleted=:is_deleted';
        $params['args'] = [':is_deleted'=>'N'];
        $params['fields'] = '*' ;
        $params['orderby'] = 'id desc' ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 获取当前所有有效的产品
     * @param $is_include_out_stock
     * @return array
     */
    public function getCurrentUsefulList($is_include_out_stock =false){
        $now = date('Y-m-d H:i:s') ;
        $activity_obj = new MiningMachineActivity();
        if($is_include_out_stock){
            $activity_params['cond'] =' :now >= start_time AND :now <= end_time  AND  status=:status AND is_deleted=:is_deleted' ;
        }else{
            $activity_params['cond'] =' :now >= start_time AND :now <= end_time AND left_total >0 AND  status=:status AND is_deleted=:is_deleted' ;
        }
        $activity_params['args'] = [':now'=>$now,':status'=>'ENABLED',':is_deleted'=>'N'];
        $activity_list = $activity_obj->findAllByWhere($activity_obj::tableName(),$activity_params,$activity_obj::getDb());

        $activity_ids = [] ;
        $left_total_list = [] ;
        if($activity_list){
            foreach($activity_list as $v){
                $activity_ids[] = $v['id'] ;
                $left_total_list[$v['id']]  = $v['left_total'] ;
            }
        }

        $list = [] ;
        if($activity_ids){

            $out_stock_num = 0 ;
            $machine_obj = new MiningMachine();
            $params['cond'] = ' activity_id in('.implode(',',$activity_ids).') AND status=:status AND is_deleted=:is_deleted';
            $params['args'] = [':status'=>'ENABLED',':is_deleted'=>'N'];
            $params['orderby']  ='sort desc';
            $params['fields'] = 'id,store_num,title,sub_title,coin,period,limit_day,fee,calc_power,price,activity_id';
            $list = $machine_obj->findAllByWhere($machine_obj::tableName(),$params,$machine_obj::getDb());
            if($list){
                foreach($list as $k=>$v){
                    $left_total = isset($left_total_list[$v['activity_id']]) ? $left_total_list[$v['activity_id']] : 0 ;
                    $out_stock = $left_total > 0 ? false :true ;
                    $list[$k]['out_stock'] = $out_stock;
                    $list[$k]['out_stock_boolean'] = $out_stock ? 1: 0;

                    if($out_stock_num >=3 && $out_stock){
                        unset($list[$k]);
                    }

                    if($out_stock){
                        $out_stock_num++ ;
                    }
                }
            }
        }

        $list = array_sort($list,'out_stock_boolean');
        return $list ;
    }
}
