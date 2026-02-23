<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_postition_detail".
 *
 * @property int $id
 * @property int $category_id 设备分类ID
 * @property int $parent_id 父级ID
 * @property int $customer_id 绑定的管理客户信息
 * @property int $company_id 公司ID(客户绑定完设备会自动填入)
 * @property int $device_id 设备ID 
 * @property string $country 亮度等级(LOW/HIGH等)
 * @property string $country_code 当前分类编号(顶级分类没有)
 * @property string $country_code_iso 二维码内容(设备类型+8位年月日+当天流水号.并用MD5加密)
 * @property string $country_code_iso2 充电口数量
 * @property string $province 客户绑定设备是上传的图片信息
 * @property string $city 经度
 * @property string $city_level 纬度
 * @property string $district 安装地点
 * @property string $town 客户上传的备注信息
 * @property string $town_code
 * @property string $adcode 状态(ENABLED-启用 DISABLED-禁用)
 * @property string $street
 * @property string $street_number
 * @property string $direction
 * @property string $distance
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDevicePostitionDetail extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_postition_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'parent_id', 'customer_id', 'company_id', 'device_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['country', 'adcode'], 'string', 'max' => 50],
            [['country_code', 'country_code_iso', 'country_code_iso2', 'province', 'city', 'city_level', 'district', 'town', 'town_code', 'street', 'street_number', 'direction', 'distance'], 'string', 'max' => 255],
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
            'country' => 'Country',
            'country_code' => 'Country Code',
            'country_code_iso' => 'Country Code Iso',
            'country_code_iso2' => 'Country Code Iso2',
            'province' => 'Province',
            'city' => 'City',
            'city_level' => 'City Level',
            'district' => 'District',
            'town' => 'Town',
            'town_code' => 'Town Code',
            'adcode' => 'Adcode',
            'street' => 'Street',
            'street_number' => 'Street Number',
            'direction' => 'Direction',
            'distance' => 'Distance',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据设备ID返回指定的信息
     * @param $device_id
     * @return mixed
     */
    public function getInfoByDeviceId($device_id){

        $params['cond'] = 'device_id=:device_id AND is_deleted=:is_deleted';
        $params['args'] = [':device_id'=>$device_id,':is_deleted'=>'N'] ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据设备信息更新地址信息
     * @param $device_info
     * @return mixed
     */
    public function updateDataByDeviceId($device_info){

        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($device_info['id']) ;

        $longitude = $device_info['longitude'];
        $latitude = $device_info['latitude'];
        $url = "http://api.map.baidu.com/geocoder/v2/?ak=L9rOCGc4oPqUdQE36EAg2lXykagA3zMA&location=".$latitude.",".$longitude."&output=json&pois=1";
        $res = curlGo($url);
        $res = @json_decode($res,true);
        $status = isset($res['status'])?$res['status']:-1 ;
        if($status !=0){
            return false ;
        }

        $address_data= $res['result']['addressComponent'];
        $add_data['category_id'] = $device_info['category_id'] ;
        $add_data['parent_id'] = $device_info['parent_id'] ;
        $add_data['customer_id'] = $device_info['customer_id'] ;
        $add_data['company_id'] = $device_info['company_id'] ;
        $add_data['device_id'] = $device_info['id'] ;
        $add_data['country'] = isset($address_data['country'])?$address_data['country']:'';
        $add_data['country_code'] = isset($address_data['country_code'])?$address_data['country_code']:'';
        $add_data['country_code_iso'] = isset($address_data['country_code_iso'])?$address_data['country_code_iso']:'';
        $add_data['country_code_iso2'] = isset($address_data['country_code_iso2'])?$address_data['country_code_iso2']:'';
        $add_data['province'] = isset($address_data['province'])?$address_data['province']:'';
        $add_data['city'] = isset($address_data['city'])?$address_data['city']:'';
        $add_data['city_level'] = isset($address_data['city_level'])?$address_data['city_level']:'';
        $add_data['district'] = isset($address_data['district'])?$address_data['district']:'';
        $add_data['town'] = isset($address_data['town'])?$address_data['town']:'';
        $add_data['town_code'] = isset($address_data['town_code'])?$address_data['town_code']:'';
        $add_data['adcode'] = isset($address_data['adcode'])?$address_data['adcode']:'';
        $add_data['street'] = isset($address_data['street'])?$address_data['street']:'';
        $add_data['street_number'] = isset($address_data['street_number'])?$address_data['street_number']:'';
        $add_data['direction'] = isset($address_data['direction'])?$address_data['direction']:'';
        $add_data['distance'] = isset($address_data['distance'])?$address_data['distance']:'';

        $detail_info = $this->getInfoByDeviceId($device_info['id']);
        $add_data['modify_time'] = date('Y-m-d H:i:s');
        if($detail_info){
            return $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$detail_info['id']]);
        }else{
            $add_data['is_deleted'] = "N";
            $add_data['create_time'] = date('Y-m-d H:i:s');
            return $this->baseInsert(self::tableName(),$add_data);
        }
    }
}
