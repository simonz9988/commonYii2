<?php

namespace common\models;

use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sea_sunny_device_sync_task".
 *
 * @property int $id
 * @property string $device_no 设备编号
 * @property int $device_id 设备ID
 * @property string $brightness 亮度
 * @property string $switch_status 开关状态
 * @property string $is_deal 是否处理
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceSyncTask extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_sync_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['device_no', 'brightness', 'switch_status'], 'string', 'max' => 255],
            [['is_deal'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'qr_code' => 'Device No',
            'device_id' => 'Device ID',
            'brightness' => 'Brightness',
            'switch_status' => 'Switch Status',
            'is_deal' => 'Is Deal',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 添加同步任务信息
     * @param $device_info
     * @return mixed
     */
    public function addTask($device_info){

        $add_data['qr_code'] = $device_info['qr_code'] ;
        $add_data['device_id'] = $device_info['id'] ;
        $add_data['brightness'] = $device_info['brightness'] ;
        $add_data['switch_status'] = $device_info['switch_status'] ;
        $add_data['is_deal'] = 'N' ;
        $add_data['create_time'] = date('Y-m-d H:i:s') ;
        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $this->baseInsert(self::tableName(),$add_data);
    }

    /**
     * 获取第一条需要处理的信息
     * @param $qr_code
     * @return mixed
     */
    public function getFirstUndealInfo($qr_code){

        $param['cond'] = 'qr_code=:qr_code AND is_deal=:is_deal';
        $param['args'] = [':qr_code'=>$qr_code,':is_deal'=>'N'];
        $param['orderby'] ='id DESC';
        $info = $this->findOneByWhere(self::tableName(),$param,self::getDb());
        return $info ;
    }

    /**
     * 判断是否需要同步，同时进行数据同步
     * @param $qr_code
     * @param $post_data
     * @param $is_sync 是否实时
     * @return mixed
     */
    public function dealNeedSync($qr_code,$post_data,$is_sync=false){

        $info = $this->getFirstUndealInfo($qr_code);
        if(!$info){

            //if($is_sync){
            if(true){
                // 修改为全部更新
                $device_obj = new SunnyDevice();
                // 实时同步状态和亮度
                $update_data['brightness'] = $post_data['brightness'];
                $update_data['switch_status'] = $post_data['switch_status'];
                $update_data['modify_time'] = date('Y-m-d H:i:s');

                $cond = 'id=:id';
                $args = [':id'=>$info['device_id']];
                $this->baseUpdate($device_obj::tableName(),$update_data,$cond,$args);
            }

            return false ;
        }

        // 全部处理好
        if($info['brightness'] == $post_data['brightness'] && $info['switch_status'] == $post_data['switch_status']){
            $update_data['is_deal'] = 'Y';
            $update_data['modify_time'] = date('Y-m-d H:i:s');

            $cond = 'device_id=:device_id AND is_deal=:is_deal';
            $args = [':device_id'=>$info['device_id'],':is_deal'=>'N'];
            $this->baseUpdate(self::tableName(),$update_data,$cond,$args);
            return false ;
        }

        return true ;
    }

    /**
     * 返回对应的redis key 值
     * @param $deviceNo
     * @return mixed
     */
    public function returnBatteryRedisKey($deviceNo){
        return "ApiTaskBattery:".$deviceNo;
    }

    /**
     * 新增负载参数和蓄电池参数的配置信息
     * @param $device_no
     * @return mixed
     */
    public function addBatteryTask($deviceNo){

        $redis_obj = new MyRedis() ;
        $redis_key = $this->returnBatteryRedisKey($deviceNo);
        $redis_obj->set($redis_key,1);
    }

    /**
     * @param $device_id
     * @return  mixed
     */
    public function addBatteryTaskByDeviceID($device_id){

        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($device_id);
        $deviceNo = $device_info ? $device_info['qr_code'] : '' ;
        return $this->addBatteryTask($deviceNo);
    }

    public function checkBatteryTaskExists($deviceNo){

        $redis_obj = new MyRedis() ;
        $redis_key = $this->returnBatteryRedisKey($deviceNo);
        $res = $redis_obj->get($redis_key);
        return $res > 0 ? true:false ;

    }

    /**
     * 删除对应的key值
     * @param $deviceNo
     * @return false
     */
    public function deleteBatteryTask($deviceNo){

        $redis_obj = new MyRedis() ;
        $redis_key = $this->returnBatteryRedisKey($deviceNo);
        return $redis_obj->del($redis_key);
    }
}
