<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mining_machine_cash_out".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $total 资产
 * @property string $coin 所属币种
 * @property string $address 转出地址
 * @property string $status UNDEAL/DEALED/CANCEL
 * @property string $is_deleted 是否已经删除
 * @property string $create_time 下单时间
 * @property string $modify_time 修改时间
 */
class MiningMachineCashOut extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mining_machine_cash_out';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['total'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['coin', 'status'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 255],
            [['is_deleted'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'total' => 'Total',
            'coin' => 'Coin',
            'address' => 'Address',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据币种获取总数目
     * @param $user_id
     * @param $coin
     * @return int
     */
    public function getTotalByCoin($user_id,$coin){
        $params['cond'] = 'coin=:coin AND user_id=:user_id';
        $params['args'] = [':coin'=>$coin,':user_id'=>$user_id];
        $params['fields'] = 'count(1) as total_num';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total_num']) ? $info['total_num'] : 0 ;
    }

    /**
     * 获取列表信息
     * @param $user_id
     * @param $coin
     * @param $page
     * @param $page_num
     * @return mixed
     */
    public function getListByPage($user_id,$coin,$page,$page_num){
        $params['cond'] = 'coin=:coin AND user_id=:user_id';
        $params['args'] = [':coin'=>$coin,':user_id'=>$user_id];
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['status_name'] =$this->getStatusName($v['status']);
            }
        }
        return $list ;
    }

    /**
     * 获取所有状态列表
     * @return mixed
     */
    public function getStatusList(){
        $arr = [
            'UNDEAL'=>'待处理',
            'DEALED'=>'已处理',
            'CANCEL'=>'已作废',
        ] ;
        return $arr ;
    }

    /**
     * 获取状态名称
     * @param $status
     * @return mixed|string
     */
    public function getStatusName($status){
        $arr = $this->getStatusList();
        return isset($arr[$status]) ?  $arr[$status] : '';
    }
}
