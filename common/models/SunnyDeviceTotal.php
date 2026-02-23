<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_total".
 *
 * @property int $id
 * @property int $category_id 设备分类ID
 * @property int $parent_id 父级ID
 * @property int $customer_id 绑定的管理客户信息
 * @property int $company_id 公司ID
 * @property string $device_id 设备ID
 * @property string $time_type 时间类型(DAY/WEEK/MONTH/YEAR)
 * @property string $timestamp 时间戳
 * @property string $cumulative_charge 累计充电电量(KW时)--相当于发电量
 * @property int $lighting_time 配合switch_statusON来进行计算
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceTotal extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_total';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'parent_id', 'customer_id', 'company_id', 'lighting_time'], 'integer'],
            [['cumulative_charge'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['device_id', 'time_type', 'timestamp'], 'string', 'max' => 255],
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
            'category_id' => 'Category ID',
            'parent_id' => 'Parent ID',
            'customer_id' => 'Customer ID',
            'company_id' => 'Company ID',
            'device_id' => 'Device ID',
            'time_type' => 'Time Type',
            'timestamp' => 'Timestamp',
            'cumulative_charge' => 'Cumulative Charge',
            'lighting_time' => 'Lighting Time',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 同步快照信息
     * @param $device_info
     * @param $post_data
     * @return mixed
     */
    public function addRecordByPostData($device_info,$post_data){

        $hour_time = date('Y-m-d H:00:00');
        $minute = date('i');
        if($minute >=0 && $minute <30){
            $half_hour_time = date('Y-m-d H:00:00');
        }else{
            $half_hour_time = date('Y-m-d H:30:00');
        }

        //1分钟，5分钟，10分钟，30分钟，60分钟，1天，一周，一年

        $day_time = date('Y-m-d 00:00:00');
        $week_time  = date('Y-m-d 00:00:00', strtotime("this week"));
        $month_time = date("Y-m-01 00:00:00");
        $year_time = date("Y-01-01 00:00:00");
        $minute_time = date('Y-m-d H:i:00');

        $five_minute_time  = floor(date('i')/5) *5 ;
        $five_minute_time = date('Y-m-d H:'.$five_minute_time.':00');

        $ten_minute_time  = floor(date('i')/10) *10 ;
        $ten_minute_time = date('Y-m-d H:'.$ten_minute_time.':00');

        $this->dealByTime($minute_time,'MINUTE',$device_info,$post_data);
        $this->dealByTime($five_minute_time,'FIVE_MINUTE',$device_info,$post_data);
        $this->dealByTime($ten_minute_time,'TEN_MINUTE',$device_info,$post_data);
        $this->dealByTime($hour_time,'HOUR',$device_info,$post_data);
        $this->dealByTime($half_hour_time,'HALF_HOUR',$device_info,$post_data);
        $this->dealByTime($day_time,'DAY',$device_info,$post_data);
        $this->dealByTime($week_time,'WEEK',$device_info,$post_data);
        $this->dealByTime($month_time,'MONTH',$device_info,$post_data);
        $this->dealByTime($year_time,'YEAR',$device_info,$post_data);
    }

    /**
     * 根据时间类型处理信息
     * @param $time
     * @param $time_type
     * @param $device_info
     * @param $post_data
     * @return mixed
     */
    public function dealByTime($time,$time_type,$device_info,$post_data){

        // 查询记录是否存在
        $timestamp = strtotime($time);
        $params['cond'] = 'device_id=:device_id AND timestamp=:timestamp AND time_type=:time_type';
        $params['args'] = [':device_id'=>$device_info['id'],':timestamp'=>$timestamp,':time_type'=>$time_type];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());

        // 充电电量
        $cumulative_charge = $post_data['cumulative_charge'];

        $lighting_time = 0 ;
        $record_obj = new SunnyDeviceStatusRecord();
        $record_info = $record_obj->getLastedInfoByDeviceId($device_info['id']);
        if($record_info){

            if($info && $record_info['switch_status'] =="Y" && $post_data['switchStatus'] =="Y"){
                $ext = time()-strtotime($record_info['create_time']);
                $lighting_time = $info['lighting_time'] + $ext ;
            }
        }

        if($info){
            $update_data['battery_volume'] = $post_data['battery_volume'] ;
            $update_data['battery_voltage'] = $post_data['battery_voltage'] ;
            $update_data['battery_charging_current'] = $post_data['battery_charging_current'] ;
            $update_data['ambient_temperature'] = $post_data['ambient_temperature'] ;
            $update_data['battery_panel_charging_voltage'] = $post_data['battery_panel_charging_voltage'] ;
            $update_data['charging_current'] = $post_data['charging_current'] ;
            $update_data['charging_power'] = $post_data['charging_power'] ;
            $update_data['load_status'] = $post_data['load_status'] ;
            $update_data['brightness'] = $post_data['brightness'] ;
            $update_data['cumulative_charge'] = $cumulative_charge ;
            $update_data['lighting_time'] = $lighting_time ;
            $update_data['modify_time'] = date('Y-m-d H:i:s') ;

            $update_data['battery_temperature'] = $post_data['battery_temperature'] ;
            $update_data['load_dc_power'] = $post_data['load_dc_power'] ;
            $update_data['battery_panel_charging_current'] = $post_data['battery_panel_charging_current'] ;

            $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$info['id']]);
        }else{

            $add_data['category_id']= $device_info['category_id'];
            $add_data['project_id']= $device_info['project_id'];
            $category_obj = new SunnyDeviceCategory();
            $category_info = $category_obj->getInfoById($device_info['category_id']);
            $add_data['parent_id']= $category_info ? $category_info['parent_id']:0;
            $add_data['customer_id'] = $device_info['customer_id'] ;
            $add_data['company_id'] = $device_info['company_id'] ;
            $add_data['device_id'] = $device_info['id'] ;
            $add_data['time_type'] = $time_type;
            $add_data['timestamp'] = $timestamp;
            $add_data['cumulative_charge'] = $cumulative_charge;
            $add_data['lighting_time'] = $lighting_time ;
            $add_data['create_time'] = date('Y-m-d H:i:s') ;
            $add_data['modify_time'] = date('Y-m-d H:i:s') ;

            $add_data['battery_volume'] = $post_data['battery_volume'] ;
            $add_data['battery_voltage'] = $post_data['battery_voltage'] ;
            $add_data['battery_charging_current'] = $post_data['battery_charging_current'] ;
            $add_data['ambient_temperature'] = $post_data['ambient_temperature'] ;
            $add_data['battery_panel_charging_voltage'] = $post_data['battery_panel_charging_voltage'] ;
            $add_data['charging_current'] = $post_data['charging_current'] ;
            $add_data['charging_power'] = $post_data['charging_power'] ;
            $add_data['load_status'] = $post_data['load_status'] ;
            $add_data['brightness'] = $post_data['brightness'] ;
            $add_data['battery_temperature'] = $post_data['battery_temperature'] ;
            $add_data['load_dc_power'] = $post_data['load_dc_power'] ;
            $add_data['battery_panel_charging_current'] = $post_data['battery_panel_charging_current'] ;

            $this->baseInsert(self::tableName(),$add_data);
        }


    }

    /**
     * 获取最近七天记录
     * @param $customer_id
     * @param int $device_id
     * @param int $num
     * @return array
     */
    public function getListRangByDay($customer_id,$device_id ,$num=7){

        $params['cond'] = 'customer_id=:customer_id AND time_type=:time_type';
        $params['args'] = [':customer_id'=>$customer_id,':time_type'=>"DAY"];
        if($device_id){

            $params['cond'] = $params['cond'].' AND device_id=:device_id';
            $params['args'][':device_id'] = $device_id ;
            $params['fields'] = ' cumulative_charge as total ,timestamp';
        }else{
            $params['group_by'] = 'device_id';
            $params['fields'] = ' sum(cumulative_charge) as total ,timestamp';
        }
        $params['limit'] = $num;
        $params['orderby'] = 'id desc';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 获取累计发电量
     * @param $company_id
     * @param $time_type
     * @param int $project_id
     * @param string $date
     * @param int $num
     * @return mixed
     */
    public function getCumulativeListByTimeTypeAndNum($company_id,$time_type='HALF_HOUR',$project_id,$date='',$num=20){

        $minute = date("i");
        if($date && $date != date("Y-m-d")){
            $start_time = date("Y-m-d 00:00:00",strtotime($date));
            $end_time = date("Y-m-d 23:59:59",strtotime($date));

        }else{
            if($minute < 30 ){
                $end_time = date("Y-m-d H:00:00");
            }else{
                $end_time = date("Y-m-d H:30:00");
            }

            $start_time = strtotime($end_time) - 20*1800 ;
            $start_time = date("Y-m-d H:i:00",$start_time);
        }

        // 开始时间
        $start_time = strtotime($start_time);
        // 结束时间
        $end_time = strtotime($end_time);

        $params['cond'] = 'company_id=:company_id AND time_type=:time_type AND timestamp >=:start_time AND timestamp <=:end_time';
        $params['args'] = [':company_id'=>$company_id,':time_type'=>'HALF_HOUR',':start_time'=>$start_time ,':end_time'=>$end_time];

        if($project_id){
            $params['cond'] = 'project_id=:project_id AND company_id=:company_id AND time_type=:time_type AND timestamp >=:start_time AND timestamp <=:end_time';
            $params['args'] = [':project_id'=>$project_id,':company_id'=>$company_id,':time_type'=>'HALF_HOUR',':start_time'=>$start_time ,':end_time'=>$end_time];

        }
        $temp_list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        $list = [];
        if($temp_list){
            foreach($temp_list as $v){
                $list[$v['timestamp']] = $v ;
            }
        }


        $res = [] ;
        $is_start = false ; ;
        $start_timestamp = 0 ;
        for($i=$start_time;$i<=$end_time;$i=$i+1800){

            $value = isset($list[$i])?$list[$i]['charging_power'] : 0 ;

            if($value >0){
                $is_start = true ;
            }

            if($is_start){
                $start_timestamp = $i ;
                $res[] = [
                    'time'=>date('H:i',$i),
                    'value'=>$value
                ];
            }

        }

        if($res){

            $max_ext = ($start_timestamp - $start_time)/1800;
            $max_ext = $max_ext >3 ? 3 :$max_ext;
            //插入最多三行
            if($max_ext >0 ){
                for($i=1;$i<=$max_ext;$i++){

                    $prev_timestamp = $start_timestamp - 1800*$i ;
                    $add_data['time'] =date('H:i',$prev_timestamp) ;
                    $add_data['value'] =0 ;
                    array_unshift($res,$add_data);
                }
            }
        }else{
            for($i=0;$i<3;$i++){

                $prev_timestamp = $end_time - 1800*$i ;
                $add_data['time'] =date('H:i',$prev_timestamp) ;
                $add_data['value'] =0 ;
                array_unshift($res,$add_data);
            }
        }

        return $res ;
    }
}
