<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_user_platform_balance".
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property string $platform 平台信息(OKEX/HUOBI)
 * @property int $user_platform_id 对应user_platform表的ID
 * @property string $instrument_id 所选币种(XRP-USDT)
 * @property string $balance 当前持仓余额
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class UserPlatformBalance extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_user_platform_balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_platform_id'], 'integer'],
            [['balance'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['platform', 'instrument_id'], 'string', 'max' => 100],
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
            'platform' => 'Platform',
            'user_platform_id' => 'User Platform ID',
            'instrument_id' => 'Instrument ID',
            'balance' => 'Balance',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 判断信息是否存在
     * @param $user_id
     * @param $platform
     * @param $instrument_id
     * @return array|bool
     */
    public function checkExistsByUserAndPlatform($user_id,$platform,$instrument_id){
        $params['cond'] = ' user_id=:user_id AND platform=:platform AND instrument_id=:instrument_id';
        $params['args'] = [':user_id'=>$user_id,':platform'=>$platform,':instrument_id'=>$instrument_id];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }
}
