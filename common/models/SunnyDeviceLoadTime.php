<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_load_time".
 *
 * @property int $id
 * @property int $device_id 设备ID
 * @property int $company_id 公司ID
 * @property int $category_id 分类ID
 * @property int $parent_id 父级ID
 * @property string $device_no 设备编号
 * @property int $time_end 时间端1-10 ，10代表晨亮时间端
 * @property int $seconds 开始时间
 * @property string $load_sensor_on_power 有人功率
 * @property string $load_sensor_off_power 无人功率
 * @property string $is_deleted 是否删除
 * @property string $create_time 创建时间
 * @property string $modify_time 修改时间
 */
class SunnyDeviceLoadTime extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_load_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_id', 'company_id', 'category_id', 'parent_id', 'time_end', 'seconds'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['device_no', 'load_sensor_on_power', 'load_sensor_off_power'], 'string', 'max' => 50],
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
            'device_id' => 'Device ID',
            'company_id' => 'Company ID',
            'category_id' => 'Category ID',
            'parent_id' => 'Parent ID',
            'device_no' => 'Device No',
            'time_end' => 'Time End',
            'seconds' => 'Seconds',
            'load_sensor_on_power' => 'Load Sensor On Power',
            'load_sensor_off_power' => 'Load Sensor Off Power',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     *
     * @param $ids
     * @param $post_data
     * @return mixed
     */
    public function savePostData($ids,$post_data){

        $is_save_template = $post_data['is_save_template'] ;
        $template_id = $post_data['template_id'] ;
        $save_template_name = $post_data['save_template_name'] ;

        if($is_save_template){
            unset($post_data['is_save_template']) ;
            unset($post_data['template_id']) ;
            unset($post_data['save_template_name']) ;

            $template_data['battery_type'] = $post_data['battery_type'];
            //$template_data['battery_rate_volt'] = $post_data['battery_rate_volt'];
            $template_data['led_current_set'] = $post_data['led_current_set'];
            $template_data['auto_power_set'] = $post_data['auto_power_set'];
            $template_data['minutes'] = $post_data['minutes'];
            $template_data['load_sensor_on_power'] = $post_data['load_sensor_on_power'];

            $template_obj = new SunnyDeviceTemplate();
            if(!$template_id){
                // 新增模板
                $template_add_data['name'] =$save_template_name;
                $template_add_data['type'] ='LOAD';
                $template_add_data['content'] =json_encode($template_data);
                $template_add_data['create_time'] = date('Y-m-d H:i:s');
                $template_add_data['modify_time'] = date('Y-m-d H:i:s');
                $template_id = $this->baseInsert($template_obj::tableName(),$template_add_data);
            }else{
                // 更新模板
                $template_update_data['content'] = json_encode($template_data);
                $template_update_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate($template_obj::tableName(),$template_update_data,'id=:id',[":id"=>$template_id]);
            }

            return true ;
        }

        $battery_type = $post_data['battery_type'];
        //$battery_rate_volt = $post_data['battery_rate_volt'];
        $led_current_set = $post_data['led_current_set'];
        $auto_power_set = $post_data['auto_power_set'];
        $minutes = $post_data['minutes'];
        $load_sensor_on_power = $post_data['load_sensor_on_power'];

        $total_minutes = 0  ;
        if($minutes){
            foreach($minutes as $v){
                $total_minutes = $total_minutes + $v ;
            }
        }
        if($total_minutes > 1440){
            $this->setError('200051');
            return false ;
        }

        // 批量更新
        $temp_ids = explode('_',$ids);
        $ids_arr = [];
        foreach($temp_ids as $v){
            if($v){
                $ids_arr[] = $v ;
            }
        }

        $device_obj = new SunnyDevice();
        $task_obj = new SunnyDeviceSyncTask();
        if($ids_arr){

            foreach($ids_arr as $v){

                if(!$v){
                    continue ;
                }

                $task_obj->addBatteryTaskByDeviceID($v);

                $delete_data['is_deleted'] = 'Y';
                $delete_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate(self::tableName(),$delete_data,'device_id=:device_id',[':device_id'=>$v]);


                // 更新基本信息
                $device_info = $device_obj->getInfoById($v) ;
                //$device_update_data['battery_type'] = $battery_type ;
                //$device_update_data['battery_rate_volt'] = $battery_rate_volt ;
                $device_update_data['led_current_set'] = $led_current_set ;
                $device_update_data['auto_power_set'] = $auto_power_set ;
                $device_update_data['modify_time'] = date('Y-m-d H:i:s');
                $this->baseUpdate($device_obj::tableName(),$device_update_data,'id=:id',[':id'=>$v]);

                // 批量新增
                foreach($minutes as $k=>$m_v){
                    $add_data['device_id']  = $v ;
                    $add_data['project_id']  = $device_info['project_id'] ;
                    $add_data['company_id']  = $device_info['company_id'] ;
                    $add_data['category_id']  = $device_info['category_id'] ;
                    $add_data['parent_id']  = $device_info['parent_id'] ;
                    $add_data['device_no']  = $device_info['device_no'] ;
                    $add_data['time_end']  = $k;
                    $add_data['minutes']  = $m_v;
                    $add_data['load_sensor_on_power']  = isset($load_sensor_on_power[$k]) ? $load_sensor_on_power[$k] :0 ;
                    $add_data['load_sensor_off_power']  = 0 ;
                    $add_data['is_deleted']  = 'N' ;
                    $add_data['create_time']  = date('Y-m-d H:i:s');
                    $add_data['modify_time']  = date('Y-m-d H:i:s');
                    $this->baseInsert(self::tableName(),$add_data);
                }
            }
        }


        return true ;

    }

    /**
     * @param $device_id
     * @param $time_end
     */
    public function getInfoByDeviceIdAndTimeEnd($device_id,$time_end){

        $params['cond'] = 'device_id=:device_id AND time_end=:time_end AND is_deleted=:is_deleted';
        $params['args'] = [':device_id'=>$device_id,':time_end'=>$time_end,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取前台展示字段
     * @param $device_info
     * @return string
     */
    public function getFrontString($device_info){

        $str = '';
        for($i=1;$i<=10;$i++){
            $info = $this->getInfoByDeviceIdAndTimeEnd($device_info['id'],$i);

            $minutes = $info ? $info['minutes']*60 : 0;
            $minutes =  dechex($minutes);
            $minutes = str_pad($minutes, 4, "0", STR_PAD_LEFT);
            $str = $str.$minutes ;

            $load_sensor_on_power = $info ? $info['load_sensor_on_power'] : 0;
            $load_sensor_on_power =  dechex($load_sensor_on_power);
            $load_sensor_on_power = str_pad($load_sensor_on_power, 4, "0", STR_PAD_LEFT);
            $str = $str.$load_sensor_on_power ;

            $load_sensor_off_power = $info ? $info['load_sensor_off_power'] : 0;
            $load_sensor_off_power =  dechex($load_sensor_off_power);
            $load_sensor_off_power = str_pad($load_sensor_off_power, 4, "0", STR_PAD_LEFT);
            $str = $str.$load_sensor_off_power ;
        }

        return $str ;
    }

    /**
     * 根据设备ID获取时段信息
     * @param $device_id
     * @return mixed
     */
    public function getListByDeviceId($device_id){

        $params['cond'] = 'device_id=:device_id AND is_deleted=:is_deleted';
        $params['args'] = [':device_id'=>$device_id,':is_deleted'=>'N'];
        $params['orderby'] = 'time_end ASC';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;

    }

    /**
     * 通过设备ID保存亮灯时段信息
     * @param $device_id
     * @param $minutes
     * @param $load_sensor_on_power
     * @return mixed
     */
    public function saveByDeviceId($device_id,$minutes,$load_sensor_on_power){

        // 先删除
        $delete_data['is_deleted'] = 'Y';
        $delete_data['modify_time'] = date('Y-m-d H:i:s');
        $this->baseUpdate(self::tableName(),$delete_data,'device_id=:device_id',[':device_id'=>$device_id]);


        // 更新基本信息
        $device_obj = new SunnyDevice() ;
        $device_info = $device_obj->getInfoById($device_id) ;

        $exists = [];
        // 批量新增
        foreach($minutes as $k=>$m_v){
            $add_data['device_id']  = $device_id ;
            $add_data['project_id']  = $device_info['project_id'] ;
            $add_data['company_id']  = $device_info['company_id'] ;
            $add_data['category_id']  = $device_info['category_id'] ;
            $add_data['parent_id']  = $device_info['parent_id'] ;
            $add_data['device_no']  = $device_info['device_no'] ;
            $add_data['time_end']  = $k;
            $add_data['minutes']  = $m_v;
            $add_data['load_sensor_on_power']  = isset($load_sensor_on_power[$k]) ? $load_sensor_on_power[$k] :0 ;
            $add_data['load_sensor_off_power']  = 0 ;
            $add_data['is_deleted']  = 'N' ;
            $add_data['create_time']  = date('Y-m-d H:i:s');
            $add_data['modify_time']  = date('Y-m-d H:i:s');
            $this->baseInsert(self::tableName(),$add_data);

            $exists[] = $k ;
        }

        $extra_time = [];
        // 查询没有设置的时段
        for($i=1;$i<=10;$i++){
            if(!in_array($i,$exists)){
                $extra_time[]= $i ;
            }
        }

        if($extra_time){
            foreach($extra_time as $v){

                $add_data['device_id']  = $device_id ;
                $add_data['project_id']  = $device_info['project_id'] ;
                $add_data['company_id']  = $device_info['company_id'] ;
                $add_data['category_id']  = $device_info['category_id'] ;
                $add_data['parent_id']  = $device_info['parent_id'] ;
                $add_data['device_no']  = $device_info['device_no'] ;
                $add_data['time_end']  = $v;
                $add_data['minutes']  = 0;
                $add_data['load_sensor_on_power']  = 0 ;
                $add_data['load_sensor_off_power']  = 0 ;
                $add_data['is_deleted']  = 'N' ;
                $add_data['create_time']  = date('Y-m-d H:i:s');
                $add_data['modify_time']  = date('Y-m-d H:i:s');
                $this->baseInsert(self::tableName(),$add_data);

            }
        }

        return true;
    }
}
