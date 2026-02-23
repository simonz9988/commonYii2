<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_robot_orders".
 *
 * @property int $orderid
 * @property string $ordersn 订单编号
 * @property int $userid 用户编号
 * @property int $productid 关联商品编号
 * @property string $productname 商品名称
 * @property string $productinfo 商品附加信息
 * @property int $res_qty 购买点数量
 * @property string $umoney 实付U币
 * @property string $coin 实付代币
 * @property int $paytime 付款时间
 * @property int $createtime 下单时间
 */
class RobotOrders extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_robot_orders';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userid', 'productid', 'res_qty', 'paytime', 'createtime'], 'integer'],
            [['productname'], 'required'],
            [['umoney', 'coin'], 'number'],
            [['ordersn'], 'string', 'max' => 30],
            [['productname'], 'string', 'max' => 60],
            [['productinfo'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'orderid' => 'Orderid',
            'ordersn' => 'Ordersn',
            'userid' => 'Userid',
            'productid' => 'Productid',
            'productname' => 'Productname',
            'productinfo' => 'Productinfo',
            'res_qty' => 'Res Qty',
            'umoney' => 'Umoney',
            'coin' => 'Coin',
            'paytime' => 'Paytime',
            'createtime' => 'Createtime',
        ];
    }

    /**
     * 根据用户ID返回总数目
     * @param $user_id
     * @return mixed
     */
    public function getTotalNumByUserId($user_id){
        $params['cond'] = 'userid=:userid';
        $params['args'] = [':userid'=>$user_id];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total'])? $info['total'] : 0 ;
    }

    /**
     * 判断是否只购买了一个一级
     * @param $user_id
     * @return mixed
     */
    public function checkIsOnlyBuyLevelOne($user_id){

        $site_config = new SiteConfig();
        $check_only_buy_level_one = $site_config->getByKey('check_only_buy_level_one');
        if($check_only_buy_level_one == "N"){
            // 不需要进行判断
            return false ;
        }

        $params['cond'] = 'userid=:userid';
        $params['args'] = [':userid'=>$user_id];
        $params['orderby'] = 'umoney DESC';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(count($list) >1 ){
            return false ;
        }

        $info = isset($list[0]) ? $list[0] : [] ;

        if(!$info){
            return  true;
        }

        $total_amount = $info['umoney']*$info['res_qty'] ;
        return $total_amount > 100 ? false : true ;

    }
}
