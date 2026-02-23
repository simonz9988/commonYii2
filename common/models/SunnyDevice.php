<?php

namespace common\models;

use common\components\MyRedis;
use Yii;

/**
 * This is the model class for table "sea_sunny_device".
 *
 * @property int $id
 * @property int $parent_id 父级ID
 * @property int $customer_id 绑定的管理客户信息
 * @property int $company_id 公司ID(客户绑定完设备会自动填入)
 * @property int $level 当前分类级别
 * @property string $light_level 亮度等级(LOW/HIGH等)
 * @property string $device_no 当前分类编号(顶级分类没有)
 * @property string $qr_code 二维码内容(设备类型+8位年月日+当天流水号.并用MD5加密)
 * @property int $port_num 充电口数量
 * @property string $bind_image_url 客户绑定设备是上传的图片信息
 * @property string $longitude 经度
 * @property string $latitude 纬度
 * @property string $note 客户上传的备注信息
 * @property string $status 状态(ENABLED-启用 DISABLED-禁用)
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDevice extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'customer_id', 'company_id', 'level', 'port_num'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['light_level', 'status'], 'string', 'max' => 50],
            [['device_no', 'qr_code', 'bind_image_url', 'longitude', 'latitude', 'note'], 'string', 'max' => 255],
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
            'parent_id' => 'Parent ID',
            'customer_id' => 'Customer ID',
            'company_id' => 'Company ID',
            'level' => 'Level',
            'light_level' => 'Light Level',
            'device_no' => 'Device No',
            'qr_code' => 'Qr Code',
            'port_num' => 'Port Num',
            'bind_image_url' => 'Bind Image Url',
            'longitude' => 'Longitude',
            'latitude' => 'Latitude',
            'note' => 'Note',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    public function getInfoByQrcode($qr_code){

        $params['cond'] = 'qr_code= :qr_code ';
        $params['args'] = [':qr_code'=>$qr_code];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @param $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){

        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @param $company_id
     * @param $fields
     * @return mixed
     */
    public function getInfoByIdAndCustomerId($id,$company_id,$fields='*'){

        $params['cond'] = 'id=:id AND company_id=:company_id';
        $params['args'] = [':id'=>$id,':company_id'=>$company_id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取亮度级别
     * @param $deviceNo
     * @return mixed
     */
    public function getLightLevel($deviceNo){
        $device_info = $this->getInfoByQrcode($deviceNo);
        if(!$device_info){
            return  0 ;
        }


        return $device_info ? $device_info['brightness'] : 0 ;
    }

    public function getSwitchStatus($deviceNo){
        $device_info = $this->getInfoByQrcode($deviceNo);
        if(!$device_info){
            return  "N";
        }
        return $device_info ? $device_info['switch_status'] : "N";
    }

    /**
     * 设置地址信息
     * @param $device_info
     * @param $longitude
     * @param $latitude
     * @return mixed
     */
    public function setPosition($device_info,$longitude,$latitude){

        if(!$device_info){
            return false ;
        }

        $update_data['longitude'] = $longitude ;
        $update_data['latitude'] = $latitude ;
        $update_data['modify_time'] = date('Y-m-d H:i:s') ;
        return $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$device_info['id']]);
    }

    /**
     * 获取最新一条信息
     * @return mixed
     */
    public function getLastedInfo(){
        $params['cond'] = "id>:id";
        $params['args'] = [':id'=>0];
        $params['orderby'] ='id desc';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 获取存在错误设备列表
     */
    /**
     * @param $customer_id
     * @param $page
     * @param $page_num
     * @param $is_fault
     * @return mixed
     */
    public function getFaultList($customer_id,$page,$page_num,$is_fault){

        if($is_fault =="Y"){
            $params['cond'] = 'customer_id=:customer_id AND is_fault=:is_fault AND is_deleted=:is_deleted AND status=:status';
            $params['args'] = [':customer_id'=>$customer_id,':is_fault'=>'Y',':is_deleted'=>'N',':status'=>'ENABLED'];
        }else{
            $params['cond'] = 'customer_id=:customer_id  AND is_deleted=:is_deleted AND status=:status';
            $params['args'] = [':customer_id'=>$customer_id,':is_deleted'=>'N',':status'=>'ENABLED'];

        }
        $params['fields'] = ' count(1) as total' ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        $total_num = $info && !is_null($info['total']) ? $info['total'] :0 ;
        $res['total_page'] = ceil($total_num/$page_num);

        $params['fields'] = '*';

        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;
        $temp_list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        $list = [] ;

        $category_name_list = [] ;
        $category_obj = new SunnyDeviceCategory();
        $record_obj = new  SunnyDeviceStatusRecord();
        if($temp_list){
            foreach($temp_list as $v){

                $temp['id'] = $v['id'];
                $category_id = $v['category_id'];
                if(isset($category_name_list[$category_id])){
                    $category_name = $category_name_list[$category_id] ;
                }else{
                    $category_name = $category_obj->getCategoryName($category_id);
                    $category_name_list[$category_id] = $category_name ;
                }

                $temp['category_id'] = $category_id;
                $temp['category_name'] = !is_null($category_name)?$category_name:'';
                $temp['device_name'] = !is_null($v['device_name'])?$v['device_name']:'';
                $temp['longitude'] = $v['longitude'];
                $temp['latitude'] = $v['latitude'];
                $temp['is_fault'] = $v['is_fault'] =="Y" ?"Y":"N";
                $temp['fault_list'] = $record_obj->getFaultShowNameList($v['id']);

                $list[] = $temp ;
            }
        }

        $res['list'] = $list ;

        return $res ;
    }

    /**
     * 根据项目ID返回设别总数目
     * @param $project_id
     * @param $road_id
     * @param $filter_type
     * @return mixed
     */
    public function getTotalNumByProjectId($project_id,$road_id=0,$filter_type=''){


        $cond[] = 'project_id =:project_id ';
        $cond[] = 'is_deleted =:is_deleted ';
        $params['args'][':project_id'] = $project_id ;
        $params['args'][':is_deleted'] = 'N' ;

        if($road_id){
            $cond[] = 'road_id=:road_id';
            $params['args'][':road_id'] = $road_id ;
        }

        if($filter_type){
            //fault-告警 offline-离线 off-灭灯
            if($filter_type=='fault'){
                $cond[] = 'is_fault=:is_fault';
                $params['args'][':is_fault'] ='Y';
            }

            if($filter_type=='offline'){
                $cond[] = 'is_offline=:is_offline';
                $params['args'][':is_offline'] ='Y';
            }

            if($filter_type=='off'){
                $cond[] = 'switch_status=:switch_status';
                $params['args'][':switch_status'] ='N';
            }
        }

        $params['cond'] =  implode(' AND ',$cond);
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据项目ID获取
     * @param $project_id
     * @return mixed
     */
    public function getTotalOnlineNumByProjectId($project_id){

        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted AND is_offline=:is_offline';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N',':is_offline'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据项目ID返回所有报错设备数目
     * @param $project_id
     * @param $road_id
     * @return mixed
     */
    public function getTotalFaultNumByProjectId($project_id,$road_id =0 ){
        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted AND is_fault=:is_fault';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N',':is_fault'=>'Y'];
        if($road_id){
            $params['cond'] = 'road_id =:road_id AND project_id=:project_id AND is_deleted=:is_deleted AND is_fault=:is_fault';
            $params['args'] = [':road_id'=>$road_id,':project_id'=>$project_id,':is_deleted'=>'N',':is_fault'=>'Y'];
        }
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据项目ID返回所有灭灯设备
     * @param $project_id
     * @param $road_id
     * @return mixed
     */
    public function getTotalSwitchOffNumByProjectId($project_id,$road_id=0){
        /*
        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted AND switch_status=:switch_status';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N',':switch_status'=>'N'];
        if($road_id){
            $params['cond'] = 'road_id =:road_id AND project_id=:project_id AND is_deleted=:is_deleted AND is_fault=:is_fault';
            $params['args'] = [':road_id'=>$road_id,':project_id'=>$project_id,':is_deleted'=>'N',':is_fault'=>'Y'];
        }*/

        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted AND  (is_offline=:is_offline OR brightness <=0)';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N',':is_offline'=>'Y'];
        if($road_id){
            $params['cond'] = 'road_id =:road_id AND project_id=:project_id AND is_deleted=:is_deleted (is_offline=:is_offline OR brightness <=0)';
            $params['args'] = [':road_id'=>$road_id,':project_id'=>$project_id,':is_deleted'=>'N',':is_offline'=>'Y'];
        }
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 获取负载为关的总数目
     * @param $project_id
     * @return int
     */
    public function getSwitchOffNumByProjectId($project_id){
        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted AND switch_status=:switch_status';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N',':switch_status'=>'N'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据用户Id返回redisKey
     * @param $user_id
     * @param $company_id
     * @param $project_id
     * @return mixed
     */
    public function returnPositionRedisKey($user_id,$company_id=0,$project_id=0){
        $redis_key = 'Redis:position:user';
        $redis_key = $redis_key.':'.$user_id;
        $arr['user'] = $redis_key ;

        if($company_id){
            $redis_key = 'Redis:position:company';
            $redis_key = $redis_key.':'.$company_id;
            $arr['company'] = $redis_key ;
        }

        if($project_id){
            $redis_key = 'Redis:position:project';
            $redis_key = $redis_key.':'.$project_id;
            $arr['project'] = $redis_key ;
        }

        return $arr;
    }

    /**
     * 批量新增redis的GEO信息
     * @param $redis_key_arr
     * @param $longitude
     * @param $latitude
     * @param $device_id
     * @return mixed
     */
    public function addPositionRedis($redis_key_arr,$longitude,$latitude,$device_id){

        $redis_obj = new MyRedis();

        foreach($redis_key_arr as $redis_key){
            $redis_obj->geoadd($redis_key,$longitude,$latitude,$device_id);
        }

        return true ;
    }

    /**
     * 根据客户ID返回所有列表信息，由于数量不多，此处不进行分页
     * @param $customer_id
     * @param string $fields
     * @return mixed
     */
    public function getListByCustomerId($customer_id,$fields='*'){
        $params['cond'] = 'customer_id=:customer_id AND is_bind=:is_bind AND is_deleted=:is_deleted';
        $params['args'] = [':customer_id'=>$customer_id,':is_bind'=>'Y',':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据客户ID返回所有列表信息，由于数量不多，此处不进行分页
     * @param $company_id
     * @param string $fields
     * @return mixed
     */
    public function getListByCompanyId($company_id,$fields='*'){
        $params['cond'] = 'company_id=:company_id AND is_bind=:is_bind AND is_deleted=:is_deleted';
        $params['args'] = [':company_id'=>$company_id,':is_bind'=>'Y',':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据设备ID重置地图坐标信息
     * @param $device_id
     * @return mixed
     */
    public function resetPosition($device_id){

        $device_info = $this->getInfoById($device_id);

        $customer_id = $device_info['customer_id'];
        $company_id = $device_info['company_id'];
        $project_id = $device_info['project_id'];

        $redis_key_arr = $this->returnPositionRedisKey($customer_id,$company_id,$project_id);
        $redis_obj = new MyRedis();
        foreach($redis_key_arr as $v){
            $redis_obj->del($v);
        }

        // 查询用户是所有的设备 有用单一项目一定和项目绑定，所以不需要再单独进行查询
        $customer_list = $this->getListByCustomerId($customer_id,'id,longitude,latitude');
        if($customer_list){
            foreach($customer_list as $v){
                $redis_key = $redis_key_arr['user'];
                $company_redis_key = $redis_key_arr['company'];
                $longitude = $v['longitude'];
                $latitude = $v['latitude'];
                if($longitude > 180 || $latitude > 90 ){
                    continue ;
                }
                $redis_obj->geoadd($redis_key,$longitude,$latitude,$v['id']);

                if($project_id == $v['project_id'] && $project_id){
                    $redis_key = $redis_key_arr['project'];
                    $redis_obj->geoadd($redis_key,$longitude,$latitude,$v['id']);

                }
            }
        }


        // 查询
        $company_list = $this->getListByCompanyId($company_id,'id,longitude,latitude');
        if($company_list){
            foreach($company_list as $v){
                $redis_key = $redis_key_arr['company'];
                $longitude = $v['longitude'];
                $latitude = $v['latitude'];

                if($longitude > 180 || $latitude > 90 ){
                    continue ;
                }
                $redis_obj->geoadd($redis_key,$longitude,$latitude,$v['id']);
            }
        }

        return true ;
    }

    /**
     * 根据客户ID返回设备总数量
     * @param $company_id
     * @param $project_id
     * @return mixed
     */
    public function getTotalNumByCustomerId($company_id,$project_id){

        $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted';
        $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N'];
        if($project_id){
            $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted AND project_id=:project_id';
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':project_id'=>$project_id];
        }
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据客户ID返回设备总数量
     * @param $company_id
     * @return mixed
     */
    public function getTotalFaultNumByCustomerId($company_id,$project_id){

        $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted AND is_fault=:is_fault';
        $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':is_fault'=>'Y'];
        if($project_id){
            $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted AND is_fault=:is_fault AND project_id=:project_id';
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':is_fault'=>'Y',':project_id'=>$project_id];
        }
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据客户ID返回设备总数量
     * @param $company_id
     * @param $switch_status
     * @param $project_id
     * @return mixed
     */
    public function getTotalNumByCustomerIdAndSwitchStatus($company_id,$switch_status,$project_id){

        if($switch_status =="Y"){
            // is_online 而且亮度大于0
            $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted AND is_offline=:is_offline AND brightness > 0 ';
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':is_offline'=>'N'];
            if($project_id){
                $params['cond'] = 'project_id=:project_id AND company_id=:company_id AND is_deleted=:is_deleted AND is_offline=:is_offline AND brightness > 0 ';
                $params['args'] = [':project_id'=>$project_id,':company_id'=>$company_id,':is_deleted'=>'N',':is_offline'=>'N'];
            }
        }else{
            $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted AND ( is_offline ="Y" OR brightness <= 0 )';
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N'];
            if($project_id){
                $params['cond'] = 'project_id=:project_id AND company_id=:company_id AND is_deleted=:is_deleted AND ( is_offline ="Y" OR brightness <= 0 )';
                $params['args'] = [':project_id'=>$project_id,':company_id'=>$company_id,':is_deleted'=>'N'];
            }

        }

        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据客户ID返回设备总数量
     * @param $company_id
     * @param $is_offline
     * @param $project_id
     * @return mixed
     */
    public function getTotalNumByCustomerIdAndIsOffline($company_id,$is_offline,$project_id){

        $params['cond'] = 'company_id=:company_id AND is_deleted=:is_deleted AND is_offline=:is_offline';
        $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':is_offline'=>$is_offline];
        if($project_id){
            $params['cond'] = 'project_id=:project_id AND company_id=:company_id AND is_deleted=:is_deleted AND is_offline=:is_offline';
            $params['args'] = [':project_id'=>$project_id,':company_id'=>$company_id,':is_deleted'=>'N',':is_offline'=>$is_offline];
        }
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据项目 ID返回对应的列表信息
     * @param $project_id
     * @param $page
     * @param $page_rows
     * @param $sort
     * @return mixed
     */
    public function getListByProjectId($project_id,$page,$page_rows,$sort,$road_id,$filter_type =''){

        $cond[] = 'project_id =:project_id';
        $cond[] = 'is_deleted =:is_deleted ';
        $params['args'][':project_id'] = $project_id ;
        $params['args'][':is_deleted'] = 'N' ;

        if($road_id){
            $cond[] = 'road_id=:road_id';
            $params['args'][':road_id'] = $road_id ;
        }

        if($filter_type){
            //fault-告警 offline-离线 off-灭灯
            if($filter_type=='fault'){
                $cond[] = 'is_fault=:is_fault';
                $params['args'][':is_fault'] ='Y';
            }

            if($filter_type=='offline'){
                $cond[] = 'is_offline=:is_offline';
                $params['args'][':is_offline'] ='Y';
            }

            if($filter_type=='off'){
                $cond[] = 'switch_status=:switch_status';
                $params['args'][':switch_status'] ='N';
            }
        }

        $params['cond'] =  implode(' AND ',$cond);

        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_rows ;
        $params['orderby'] = 'id desc';
        if($sort =='desc'){
            $params['orderby'] = 'qr_code desc';
        }else if($sort =='asc'){
            $params['orderby'] = 'qr_code asc';
        }
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if(!$list){
            return  [] ;
        }
        $res = [];
        $record_obj = new SunnyDeviceStatusRecord();
        $status_info_obj = new SunnyDeviceStatusInfo();
        foreach($list as $v){
            $item['name'] = $v['device_name'];
            $item['qr_code'] = $v['qr_code'];
            $item['longitude'] = $v['longitude'];
            $item['latitude'] = $v['latitude'];

            // 查询最新的status_info
            $record_info = $record_obj->getLastedInfoByDeviceId($v['id']);
            $item['brightness'] = !is_null($v['brightness'])?intval($v['brightness']): 0;
            //$item['brightness'] = !is_null($record_info['brightness'])?intval($record_info['brightness']): 0;
            $item['is_offline'] = !is_null($v['is_offline'])?$v['is_offline']: 'Y';
            if($item['is_offline'] =='Y'){
                $item['brightness'] = 0 ;
            }
            $status_info = $status_info_obj->getInfoByDeviceId($v['id']);


            $item['modify_time'] = $record_info ? $record_info['create_time'] :'';
            $item['cumulative_charge'] = $record_info ? $record_info['cumulative_charge']:'';
            $item['charging_power'] = $record_info ? $record_info['charging_power']:'';
            $item['battery_voltage'] = $record_info ? $record_info['battery_voltage']:'';
            $item['battery_temperature'] = $record_info ? $record_info['battery_temperature']:'';
            $item['battery_temperature'] = $record_info ? $record_info['battery_temperature']:'';
            $item['charge_status'] = $status_info ? $status_info_obj->getChargeStatusName($status_info['charge_status']):'';
            $item['battery_panel_charging_voltage'] = $status_info ? ($status_info['battery_panel_charging_voltage']):'';
            $item['is_fault'] = $v['is_fault'];
            $item['fault_list'] = $record_obj->getFaultShowNameList($v['id']);
            $item['id'] = $v['id'];
            $res[] = $item ;
        }
        return $res  ;

    }

    /**
     * 获取项目页的详情信息
     * @param $device_info
     * @return mixed
     */
    public function getDetailFromProject($device_info){

        $res['id'] = $device_info['id'];
        //项目名称
        $project_id = $device_info['project_id'];
        $project_obj = new SunnyProject();
        $project_info = $project_obj->getInfoById($project_id);
        $res['project_name'] = $project_info ? $project_info['name'] :'';

        //联网方式
        $res['link_network_type'] ='';
        // 路段
        $res['group'] = '';
        // qr code
        $res['qr_code'] = $device_info['qr_code'];
        $record_obj = new SunnyDeviceStatusRecord();
        $record_info = $record_obj->getLastedInfoByDeviceId($device_info['id']);
        $res['modify_time'] = $record_info?$record_info['create_time'] : $device_info['modify_time'];
        // 经纬度
        $res['longitude'] = $device_info['longitude'];
        $res['latitude'] = $device_info['latitude'];

        // 设备名称
        $res['device_name'] = $device_info['device_name'];

        // 设备编号
        $res['mark_no'] = $device_info['mark_no'];

        // 路段信息
        $road_obj = new SunnyRoad();
        $res['road_name'] = $road_obj->getNameById($device_info['road_id']);
        $res['road_id'] = $device_info['road_id'];

        // 信息
        $status_info_obj = new SunnyDeviceStatusInfo();
        $status_info = $status_info_obj->getInfoByDeviceId($device_info['id']);
        $res['charge_status'] = $status_info ? $status_info_obj->getChargeStatusName($status_info['charge_status']):0;

        // 是否存在故障
        $res['is_fault'] = $device_info['is_fault'];
        $res['fault_list'] = $record_obj->getFaultShowNameList($device_info['id']);

        // 故障详情

        // 路灯电压 电流 状态 功率
        /*
        $res['load_status'] = $record_info ? $record_info['load_status'] : 'N';// 状态
        // 假如离线需要将状态手动置为offline
        if($device_info['is_offline'] =='Y'){
            $res['load_status'] = 'N';
        }*/
        $res['load_status'] = $device_info['is_offline'] == "N" && $device_info['brightness'] > 0 ?'Y':'N';

        $res['load_dc_power'] = $record_info ? $record_info['load_dc_power'] : '';//电压
        $res['charging_current'] = $record_info ? $record_info['charging_current'] : '';//电流
        $res['cumulative_charge'] = $record_info ? $record_info['cumulative_charge'] : '';//功率

        // 电池板  电压 电流 功率
        $res['battery_panel_charging_voltage'] = $record_info ? $record_info['battery_panel_charging_voltage'] : '';// 电压
        $res['battery_panel_charging_current'] = $record_info ? $record_info['battery_panel_charging_current'] : '';// 电流
        $res['charging_power'] = $record_info ? $record_info['charging_power'] : '';// 功率

        // 蓄电池 电压 电流 温度 最高电压 最高温度 当前温度 最低温度 当前电压 最低电压
        $res['battery_voltage'] =  $record_info ? $record_info['battery_voltage'] : '';// 电压
        $res['battery_volume'] =  $record_info ? $record_info['battery_volume'] : '';// 电流
        $res['battery_temperature'] =  $record_info ? $record_info['battery_temperature'] : '';// 电池温度
        $res['battery_charging_current'] =  $record_info ? $record_info['battery_charging_current'] : '';// 充电电流

        $today_obj = new SunnyDeviceStatusToday();
        $today_info = $today_obj->checkTodayIsExists($device_info['id']);

        // 最高电压
        $res['max_battery_voltage'] = $record_obj->getMaxFieldByDeviceId($device_info['id'],'battery_voltage','desc');
        $res['max_battery_voltage'] = $today_info ? $today_info['bat_max_volt_today']: 0 ;
        // 最高温度
        $res['max_battery_temperature'] = $record_obj->getMaxFieldByDeviceId($device_info['id'],'battery_temperature','desc');
        $res['max_battery_temperature'] = $today_info ? $today_info['bat_highest_temper']: 0 ;
        // 最低电压
        $res['min_battery_voltage'] = $record_obj->getMaxFieldByDeviceId($device_info['id'],'battery_voltage','asc');
        $res['min_battery_voltage'] = $today_info ? $today_info['bat_min_volt_today']: 0 ;
        // 最低温度
        $res['min_battery_temperature'] = $record_obj->getMaxFieldByDeviceId($device_info['id'],'battery_temperature','asc');
        $res['min_battery_temperature'] = $today_info ? $today_info['bat_lowest_temper']: 0 ;
        // 亮灯时间
        $total_obj = new SunnyDeviceStatusTotal();
        $total_info = $total_obj->checkExistsByDeviceId($device_info['id']);
        $res['load_total_work_time'] = $total_info ? $total_info['load_total_work_time']:0;// 总亮灯时长


        //当天亮灯时间 当前亮灯是取的有人时间，所以特殊处理，二者相加
        $res['led_sensor_on_time'] = $today_info? $today_info['led_sensor_on_time'] +   $today_info['led_sensor_off_time'] : 0 ;
        $res['led_sensor_off_time'] = $today_info? $today_info['led_sensor_off_time'] : 0 ;
        $res['sys_health_index'] = $today_info? $today_info['sys_health_index'] : 0 ;
        // 总充电时长
        $res['bat_charge_time'] =  $today_obj->getTotalBatChargeTime($device_info['id']);
        $res['bat_charge_time'] =  $today_info ? $today_info['bat_charge_time'] : 0 ;;


        $res['bat_charge_ah_today'] =  $today_info ? $today_info['bat_charge_ah_today'] : 0 ; //当日充电安时树
        $res['bat_discharge_ah_today'] =  $today_info ? $today_info['bat_discharge_ah_today'] : 0 ;////当日放电安时
        /*
        $res['bat_charge_ah_today'] =  $total_info ? $total_info['bat_charge_an_total'] : 0 ; //总充电安时树
        $res['bat_discharge_ah_today'] =  $total_info ? $total_info['bat_discharge_an_total'] : 0 ;////总放电安时
        */
        ///
        // 放电电流  负载功率/蓄电池电压
        $discharge_volt = $status_info['battery_voltage'] ? $status_info['cumulative_charge'] / $status_info['battery_voltage'] :0;
        $res['discharge_volt'] = $discharge_volt?numberSprintf($discharge_volt,2):0;


        $category_obj = new SunnyDeviceCategory();
        $category_info = $category_obj->getInfoById($device_info['category_id']);
        $res['controller_model'] =  $category_info ? $category_info['controller_model']:'';
        $res['battery_vol'] =  $category_info ? $category_info['battery_vol']:'';
        $res['battery_model'] =  $category_info ? $category_info['battery_model']:'';
        $res['panel_power'] =  $category_info ? $category_info['panel_power']:'';
        $res['panel_model'] =  $category_info ? $category_info['panel_model']:'';
        $res['light_power'] =  $category_info ? $category_info['light_power']:'';
        $res['light_model'] =  $category_info ? $category_info['light_model']:'';

        return $res ;

    }

    /**
     * 更新地址信息
     * @param $deviceNo
     * @param $longitude
     * @param $latitude
     * @return mixed
     */
    public function updatePosition($deviceNo,$longitude,$latitude){
        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoByQrcode($deviceNo);
        if(!$device_info){
            $this->setError('100075');
            return false ;
        }

        // 地址有变更才进行更新
        if(floatval($device_info['longitude']) !=floatval($longitude) || floatval($device_info['latitude']) !=floatval($latitude)){
            //更新经纬度信息
            $update_data['longitude'] = $longitude ;
            $update_data['latitude'] = $latitude ;
            $update_data['modify_time'] = date('Y-m-d H:i:s') ;
            $this->baseUpdate($device_obj::tableName(),$update_data,'id=:id',[':id'=>$device_info['id']]);

            // 新增计划任务
            $task_obj = new PushTask();
            $task_obj->addPositionTask($device_info['id']);
        }
        return  true ;
    }

    public function returnSettingEnumList($is_history=false){

        $system['project_id'] = '项目名称';
        $system['device_name'] = '设备名称';

        $system['qr_code'] = 'PN';

        if(!$is_history){

            $system['mark_no'] = '路灯编号';
            $system['road_id'] = '路段';

            $system['is_bind'] = '是否绑定';
            $system['note'] = '备注';
            //$system[''] = '信号状态';
            //$system[''] = '更新时间';
            $system['status'] = '设备状态';
            //$system[''] = '联网方式';
            $system['sim_code'] = 'SIM卡号';
            //$system[''] = '套餐剩余';
            //$system[''] = '信号强度';
            $system['imei'] = 'IMEI';
            $system['iccid'] = 'ICCID';
            $system['longitude'] = '经度';
            $system['latitude'] = '纬度';
            //$system[''] = '网络名称';
            //$system[''] = '信道';
            //$system[''] = '灯头额定功率(W)';
            //$system[''] = '太阳能板类型';
            //$system['charging_power'] = '太阳能板额定功率';
            //$system['load_dc_power'] = '系统电压(V)';
            //$system['charging_current'] = '系统电流(A)';
            //$system[''] = '控制器温度(℃)';

            $system['battery_type'] = '蓄电池类型';



            // 实时内容可以全部对应上
            $current['brightness'] = '路灯亮度';
            $current['battery_voltage'] = '蓄电池电压(V)';
            $current['battery_charging_current'] = '蓄电池充电电流(A)';
            $current['charging_current'] = '蓄电池功率(W)';
            $current['charge_status'] = '蓄电池充电状态';
            //$current[''] = '蓄电池状态';
            $current['battery_temperature'] = '蓄电池温度(℃)';
            $current['battery_volume'] = '蓄电池电量SOC(%)';
            $current['battery_panel_charging_voltage'] = '太阳能板电压(V)';
            $current['battery_panel_charging_current'] = '太阳能板电流(A)';
            $current['charging_power'] = '太阳能板功率(W)';
            $current['battery_charging_current'] = '蓄电池电流(A)';
            $current['load_dc_power'] = '路灯电压(V)';
            $current['charging_current'] = '路灯电流(A)';
            $current['cumulative_charge'] = '路灯功率(W)';
            $current['light_temp'] = '灯头温度(℃)';
            $current['fault_list'] = '故障信息';
            $current['switch_status'] = '供电状态';
            //$current[''] = '实时经度';
            //$current[''] = '实时纬度';

            $history['load_total_work_time'] = '负载总工作时间';
            $history['work_days_total'] = '运行天数';
            $history['bat_over_discharge_time'] = '蓄电池总过放次数';
            $history['bat_over_charge_time'] = '蓄电池总充满次数';
            $history['bat_charge_an_total'] = '蓄电池总充电安时数(AH)';
            $history['bat_discharge_an_total'] = '蓄电池总放电安时数(AH)';
            $history['generat_energy_total'] = '累计发电量(kWh)';
            $history['used_energy_total'] = '累计用电量(kWh)';

        }

        // 历史
        $history['bat_min_volt_today'] = '当天最低电压(V)';
        $history['bat_max_volt_today'] = '当天最高电压(V)';
        $history['bat_charge_ah_today'] = '当天充电最大电流(A)';
        $history['bat_discharge_ah_today'] = '当天放电最大电流(A)';
        $history['bat_max_charge_power_today'] = '当天充电最大功率(W)';
        $history['bat_max_discharge_power_today'] = '当天放电最大功率(W)';
        $history['bat_charge_ah_today'] = '当天充电安时数(AH)';
        $history['bat_discharge_ah_today'] = '当天放电安时数(AH)';
        $history['generat_energy_today'] = '当天发电量(Wh)';
        $history['used_energy_today'] = '当天用电量(Wh)';
        $history['bat_highest_temper'] = '当天蓄电池最高温度(℃)';
        $history['bat_lowest_temper'] = '当天蓄电池最低温度(℃)';
        $history['led_light_on_index'] = '亮灯指数';
        $history['power_save_index'] = '能耗指数';
        $history['sys_health_index'] = '健康指数';

        $history['bat_charge_time'] = '当天充电时间';

        $history['load_total_work_time'] = '负载总工作（累计亮灯）';
        $history['led_sensor_off_time'] = '当天亮灯时间';



        if($is_history){
            $history['bat_max_chg_current_today'] = '当天充电最大电流';
            $history['bat_max_discharge_current_today'] = '当天放电最大电流';
            $history['led_sensor_on_time'] = '当天亮灯时间 （有人';
            $history['led_sensor_off_time'] = '当天亮灯时间 （无人）';
            $history['night_length'] = '夜晚长度';
        }

        return compact('system','current','history');
    }

    /**
     * 根据项目ID返回所有报错设备数目
     * @param $company_ids
     * @return mixed
     */
    public function getTotalNumByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted ';
        $params['args'] = [':is_deleted'=>'N'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据项目ID返回所有报错设备数目
     * @param $company_ids
     * @return mixed
     */
    public function getTotalOnlineNumByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted AND is_offline=:is_offline ';
        $params['args'] = [':is_deleted'=>'N',':is_offline'=>'N'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 获取所有离线项目
     * @param $company_ids
     * @return int
     */
    public function getTotalOfflineNumByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted AND is_offline=:is_offline ';
        $params['args'] = [':is_deleted'=>'N',':is_offline'=>'Y'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 获取所有离线项目
     * @param $project_id
     * @return int
     */
    public function getTotalOfflineNumByProjectId($project_id){

        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted AND is_offline=:is_offline ';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N',':is_offline'=>'Y'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 获取公司对应的负载开的总设备数
     * @param $company_ids
     * @return mixed
     */
    public function getTotalSwitchOnNumByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted AND switch_status=:switch_status ';
        $params['args'] = [':is_deleted'=>'N',':switch_status'=>'Y'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 获取公司对应的负载开的总设备数
     * @param $project_id
     * @return mixed
     */
    public function getTotalSwitchOnNumByProjectId($project_id){

        $params['cond'] = 'project_id=:project_id AND is_deleted=:is_deleted AND switch_status=:switch_status ';
        $params['args'] = [':project_id'=>$project_id,':is_deleted'=>'N',':switch_status'=>'Y'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }



    /**
     * 获取公司对应的负载开的总设备数
     * @param $company_ids
     * @return mixed
     */
    public function getTotalSwitchOffNumByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted AND switch_status=:switch_status ';
        $params['args'] = [':is_deleted'=>'N',':switch_status'=>'N'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 获取公司对应的负载开的总设备数
     * @param $company_ids
     * @return mixed
     */
    public function getTotalFaultNumByCompanyIds($company_ids){
        if(!$company_ids){
            return  0 ;
        }
        $params['cond'] = 'company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted AND is_fault=:is_fault ';
        $params['args'] = [':is_deleted'=>'N',':is_fault'=>'Y'];
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    private function returnBaseParamsByProjectIdAndFilterStatus($project_id ,$status){
        $cond[] = 'project_id=:project_id';
        $cond[] = 'is_deleted=:is_deleted';
        $params['args'][':project_id'] = $project_id ;
        $params['args'][':is_deleted'] = 'N' ;

        if($status =='ON'){
            $cond[] = 'switch_status=:switch_status';
            $params['args'][':switch_status'] = "Y" ;
        }

        if($status =='OFF'){
            $cond[] = 'switch_status=:switch_status';
            $params['args'][':switch_status'] = "N" ;
        }

        if($status =='OFF'){
            $cond[] = 'switch_status=:switch_status';
            $params['args'][':switch_status'] = "N" ;
        }

        if($status =='OFFLINE'){
            $cond[] = 'is_offline=:is_offline';
            $params['args'][':is_offline'] = "Y" ;
        }

        if($status =='ERROR'){
            $cond[] = 'is_fault=:is_fault';
            $params['args'][':is_fault'] = "Y" ;
        }

        $params['cond'] = implode(' AND  ',$cond);
        return $params ;
    }

    /**
     * 根据项目ID和状态获取总数
     * @param $project_id
     * @param $status
     * @return mixed
     */
    public function getTotalNumByProjectIdAndFilterStatus($project_id,$status){

        $params = $this->returnBaseParamsByProjectIdAndFilterStatus($project_id,$status);
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据状态嗨哟分页数返回指定的信息
     * @param $project_id
     * @param $status
     * @param $page
     * @param $page_num
     * @return mixed
     */
    public function getListByProjectIdAndFilterStatus($project_id,$status,$page,$page_num){

        $params = $this->returnBaseParamsByProjectIdAndFilterStatus($project_id,$status);
        $params['curr_page']['page'] = $page;
        $params['curr_page']['page_num'] = $page_num;
        $list= $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return  [];
        }

        $res = [] ;
        $info_obj = new SunnyDeviceStatusInfo();
        foreach($list as $v){

            //设备ID、设备名称、设备编号、设备状态、亮度、告警、PN
            //路灯功率、电池板电压、电池板功率、蓄电池电压
            $item['id'] = $v['id'];
            $item['device_name'] = $v['device_name'];
            $item['qr_code'] = $v['qr_code'];
            $item['is_offline'] = $v['is_offline'];
            $item['brightness'] = $v['brightness'];
            $item['is_fault'] = $v['is_fault'];
            $item['mark_no'] = $v['mark_no'];
            $item['longitude'] = $v['longitude'];
            $item['latitude'] = $v['latitude'];

            $info = $info_obj->getInfoByDeviceId($v['id']);
            $item['cumulative_charge'] = $info ? $info['cumulative_charge']:0;
            $item['battery_panel_charging_voltage'] = $info ? $info['battery_panel_charging_voltage']:0;
            $item['charging_power'] = $info ? $info['charging_power']:0;
            $item['battery_voltage'] = $info ? $info['battery_voltage']:0;
            $item['modify_time'] = $v['modify_time'];
            $res[] = $item ;
        }

        return $res ;
    }


}
