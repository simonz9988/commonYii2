<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_project".
 *
 * @property int $id
 * @property int $customer_id 绑定的管理客户信息
 * @property int $company_id 公司ID(客户绑定完设备会自动填入)
 * @property string $name 项目名称
 * @property string $unique_code 项目编号
 * @property string $time_zone 时区
 * @property string $country 国家
 * @property string $country_code
 * @property string $province 省
 * @property string $province_code
 * @property string $city 市
 * @property string $city_code
 * @property string $area 区
 * @property string $area_code
 * @property string $address 地址
 * @property string $longitude 经度
 * @property string $latitude 纬度
 * @property string $map_name 地图展示名称
 * @property string $img_url 图片地址
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $status 状态(ENABLED-启用 DISABLED-禁用)
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyProject extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_project';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_id', 'company_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['name', 'time_zone', 'country', 'country_code', 'province', 'province_code', 'city', 'city_code', 'area', 'area_code', 'address', 'longitude', 'latitude', 'map_name', 'img_url'], 'string', 'max' => 255],
            [['unique_code', 'status'], 'string', 'max' => 50],
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
            'customer_id' => 'Customer ID',
            'company_id' => 'Company ID',
            'name' => 'Name',
            'unique_code' => 'Unique Code',
            'time_zone' => 'Time Zone',
            'country' => 'Country',
            'country_code' => 'Country Code',
            'province' => 'Province',
            'province_code' => 'Province Code',
            'city' => 'City',
            'city_code' => 'City Code',
            'area' => 'Area',
            'area_code' => 'Area Code',
            'address' => 'Address',
            'longitude' => 'Longitude',
            'latitude' => 'Latitude',
            'map_name' => 'Map Name',
            'img_url' => 'Img Url',
            'is_deleted' => 'Is Deleted',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 判断唯一编码是否存在
     * @param $unique_code
     * @return mixed
     */
    public function getInfoByUniqueCode($unique_code){
        $params['cond'] = 'unique_code=:unique_code AND is_deleted=:is_deleted';
        $params['args'] = [':unique_code'=>$unique_code,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据ID返回指定信息
     * @param $id
     * @param string $fields
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
     * @param $customer_id
     * @param string $fields
     * @return mixed
     */
    public function getInfoByIdAndCustomerId($id,$customer_id,$fields='*'){
        $params['cond'] = 'id=:id AND customer_id=:customer_id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * @param $id
     * @param $post_data
     * @return mixed
     */
    public function savePostData($id,$post_data){

        $add_data['customer_id'] = $post_data['customer_id'];
        $add_data['name'] = $post_data['name'];
        $add_data['unique_code'] = $post_data['unique_code'];
        $add_data['time_zone'] = $post_data['time_zone'];
        $add_data['country_code'] = $post_data['country_code'];

        $country_obj = new Country();
        $country_info = $country_obj->getInfoById($add_data['country_code']);
        $add_data['country'] = $country_info['name'];
        $add_data['province'] = $post_data['province'];
        $add_data['city'] = $post_data['city'];
        $add_data['area'] = $post_data['area'];
        $add_data['address'] = $post_data['address'];
        $add_data['longitude'] = $post_data['longitude'];
        $add_data['latitude'] = $post_data['latitude'];
        $add_data['map_name'] = $post_data['map_name'];
        $add_data['img_url'] = $post_data['img_url'];
        $add_data['note'] = $post_data['note'];

        if(!$id){
            // 判断唯一编码是否正确
            $unique_info = $this->getInfoByUniqueCode($add_data['unique_code']) ;
            if($unique_info){
                $this->setError('100088');
                return false ;
            }

            $manager_obj = new SunnyManager() ;
            $manager_info = $manager_obj->getInfoById($add_data['customer_id']);
            $add_data['company_id'] = $manager_info?$manager_info['company_id']:0;
            $add_data['is_deleted'] = 'N';
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');

            $this->baseInsert(self::tableName(),$add_data);

        }else{
            $info = $this->getInfoById($id);
            if($info['unique_code'] != $info['unique_code']){
                //判断唯一编码是否正确

                $unique_info = $this->getInfoByUniqueCode($add_data['unique_code']) ;
                if($unique_info){
                    $this->setError('100088');
                    return false ;
                }
            }

            $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }

        return true ;
    }

    /**
     * 根据客户ID和项目 ID判断是否存在
     * @param $company_id
     * @param $id
     * @return mixed
     */
    public function getInfoByCustomerIdAndId($company_id,$id){
        $params['cond'] = 'company_id=:company_id AND id=:id AND is_deleted=:is_deleted';
        $params['args'] = [':company_id'=>$company_id,':id'=>$id,':is_deleted'=>'N'];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据客户返回包含项目总数目
     * @param $customer_id
     * @param $name
     * @return mixede
     */
    public function getTotalNumByCustomerId($customer_id,$name=''){

        // 同一修改为关联到公司

        if($name){
            $params['cond'] = 'customer_id=:customer_id  AND is_deleted=:is_deleted AND name like :name';
            $params['args'] = [':customer_id'=>$customer_id,':is_deleted'=>'N',':name'=>'%'.$name.'%'];
        }else{
            $params['cond'] = 'customer_id=:customer_id  AND is_deleted=:is_deleted';
            $params['args'] = [':customer_id'=>$customer_id,':is_deleted'=>'N'];
        }
        $params['fields'] = ' count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根据客户返回包含项目总数目
     * @param $customer_id
     * @param $name
     * @return mixede
     */
    public function getTotalNumByCompanyId($company_id,$name=''){

        // 同一修改为关联到公司

        if($name){
            $params['cond'] = 'company_id=:company_id  AND is_deleted=:is_deleted AND name like :name';
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':name'=>'%'.$name.'%'];
        }else{
            $params['cond'] = 'company_id=:company_id  AND is_deleted=:is_deleted';
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N'];
        }
        $params['fields'] = ' count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 根绝分页返回对应的列表信息
     * @param $company_id
     * @param $page
     * @param $page_num
     * @param $name
     * @return mixed
     */
    public function getListByCustomerAndPage($company_id,$page,$page_num,$name=''){

        if($name){
            $params['cond'] = 'company_id=:company_id  AND is_deleted=:is_deleted AND name like :name';
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N',':name'=>'%'.$name.'%'];
        }else{
            $params['cond'] = 'company_id=:company_id  AND is_deleted=:is_deleted';
            $params['args'] = [':company_id'=>$company_id,':is_deleted'=>'N'];
        }
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;


        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return  [] ;
        }

        $res = [] ;

        $device_obj = new SunnyDevice();

        $site_config_obj = new SiteConfig();
        $static_url = $site_config_obj->getByKey('static_url');


        foreach($list as $v){

            // 名称
            $item['id'] = $v['id'] ;
            $item['name'] = $v['name'] ;
            $item['note'] = $v['note'] ;
            $item['unique_code'] = $v['unique_code'] ;
            $item['time_zone'] = $v['time_zone'] ;
            $item['img_url'] = $static_url.$v['img_url'] ;

            // 地点
            $item['country'] = $v['country'] ;
            $item['country_code'] = $v['country_code'] ;
            $item['province'] = $v['province'] ;
            $item['city'] = $v['city'] ;
            $item['area'] = $v['area'] ;
            $item['address'] = $v['address'] ;

            $item['longitude'] = $v['longitude'] ;// 经度
            $item['latitude'] = $v['latitude'] ;// 纬度

            // 创建时间
            $item['create_time'] = date('Y-m-d',strtotime($v['create_time']));

            // 设备数目
            $item['device_num'] = $device_obj->getTotalNumByProjectId($v['id']);

            // 告警数量
            $item['warning_num'] = $device_obj->getTotalFaultNumByProjectId($v['id']);

            // 离线数量
            $item['offline_num'] = $device_obj->getTotalOfflineNumByProjectId($v['id']);
            // 负载关

            $item['switch_off_num'] = $device_obj->getSwitchOffNumByProjectId($v['id']);
            $res[] = $item;
        }

        return $res ;

    }

    /**
     * 根据ID获取名称信息
     * @param $id
     * @return mixed
     */
    public function getNameById($id){

        $info = $this->getInfoById($id,'name');
        return $info ? $info['name'] : '';
    }

    /**
     * 根据公司ID返回项目的总数目
     * @param $company_ids
     * @param $name
     * @return mixed
     */
    public function getTotalNumByCompanyIds($company_ids,$name=''){
        if(!$company_ids){
            return 0 ;
        }

        $params['cond'] = ' company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted' ;
        $params['args'] = [':is_deleted'=>'N'];

        if($name){
            $params['cond'] = ' name like :name AND company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted' ;
            $params['args'] = [':is_deleted'=>'N',':name'=>'%'.$name.'%'];
        }
        $params['fields'] = 'count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total']:0;
    }

    /**
     * 根据公司ID返回列表信息
     * @param $company_ids
     * @param $page
     * @param $page_num
     * @param $name
     * @return mixed
     */
    public function getListByCompanyIds($company_ids,$page,$page_num,$name=''){

        if(!$company_ids){
            return  [] ;
        }
        // 分页信息
        $params['page']['curr_page'] = $page ;
        $params['page']['page_num'] = $page_num ;
        $params['cond'] = ' company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted' ;
        $params['args'] = [':is_deleted'=>'N'];
        if($name){
            $params['cond'] = ' name like :name AND company_id in('.implode(',',$company_ids).') AND is_deleted=:is_deleted' ;
            $params['args'] = [':is_deleted'=>'N',':name'=>'%'.$name.'%'];
        }
        $params['fields'] = '*';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if(!$list){
            return [] ;
        }

        $res = [] ;
        $device_obj = new SunnyDevice();
        foreach($list as $v){

            $item['id'] = $v['id'];
            $item['name'] = $v['name'];
            $item['longitude'] = $v['longitude'];
            $item['latitude'] = $v['latitude'];
            // 项目对应的设备数目
            $item['device_num'] = $device_obj->getTotalNumByProjectId($v['id']);

            $res[]  =$item;
        }
        return $res ;
    }
}
