<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_sunny_device_fault".
 *
 * @property int $id
 * @property int $category_id 设备分类ID
 * @property int $project_id 项目ID
 * @property int $parent_id 父级ID
 * @property int $customer_id 绑定的管理客户信息
 * @property int $company_id 公司ID(客户绑定完设备会自动填入)
 * @property string $fault_id 对应的错误ID
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $status 状态(UNDEAL-未处理 DEALED-已处理)
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class SunnyDeviceFault extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_sunny_device_fault';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'project_id', 'parent_id', 'customer_id', 'company_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['fault_id', 'status'], 'string', 'max' => 50],
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
            'project_id' => 'Project ID',
            'parent_id' => 'Parent ID',
            'customer_id' => 'Customer ID',
            'company_id' => 'Company ID',
            'device_id' => 'Device ID',
            'fault_id' => 'Fault ID',
            'is_deleted' => 'Is Deleted',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 获取设备指定状态列列表信息
     * @param $device_id
     * @param $status
     * @return mixed
     */
    public function getListByStatus($device_id,$status){

        $params['cond'] = 'device_id=:device_id AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':device_id'=>$device_id,':status'=>$status,':is_deleted'=>'N'];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 处理设备错误信息
     * @param $device_info
     * @param $device_id
     * @return mixed
     */
    public function dealFault($fault_list,$device_info){

        $device_id = $device_info['id'];

        // 查询当前设备已存在的错误列表
        $list  = $this->getListByStatus($device_id,'UNDEAL');

        if(!$fault_list){
            // 说明错误已经全部解决 将所有的错误标记为
            if($list){
                foreach($list as $v){
                    $update_data['status'] = 'DEALED';
                    $update_data['modify_time'] = date('Y-m-d H:i:s');
                    $this->baseUpdate(self::tableName(),$update_data,'id=:id',[':id'=>$v['id']]);
                }
            }
        }else{

            $exists_fault_ids = [] ;
            $exists_fault_ids_list = [] ;
            if($list){
                foreach($list as $v){
                    $exists_fault_ids[] = $v['fault_id'];
                    $exists_fault_ids_list[$v['fault_id']] = $v['id'];
                }
            }



            if(!$exists_fault_ids){
                // 直接插入
                foreach($fault_list as $v){
                    $add_data['category_id'] = $device_info['category_id'] ;
                    $add_data['project_id'] = $device_info['project_id'] ;
                    $add_data['qr_code'] = $device_info['qr_code'] ;
                    $add_data['parent_id'] = $device_info['parent_id'] ;
                    $add_data['customer_id'] = $device_info['customer_id'] ;
                    $add_data['company_id'] = $device_info['company_id'] ;
                    $add_data['device_id'] = $device_id ;
                    $add_data['fault_id'] = $v ;
                    $add_data['is_deleted'] = 'N' ;
                    $add_data['status'] = 'UNDEAL' ;
                    $add_data['create_time'] = date('Y-m-d H:i:s') ;
                    $add_data['modify_time'] = date('Y-m-d H:i:s') ;
                    $this->baseInsert(self::tableName(),$add_data);
                }
            }else{

                foreach($fault_list as $v){
                    if(!in_array($v,$exists_fault_ids)){
                        // 为新增的
                        $add_data['category_id'] = $device_info['category_id'] ;
                        $add_data['project_id'] = $device_info['project_id'] ;
                        $add_data['parent_id'] = $device_info['parent_id'] ;
                        $add_data['customer_id'] = $device_info['customer_id'] ;
                        $add_data['company_id'] = $device_info['company_id'] ;
                        $add_data['device_id'] = $device_id ;
                        $add_data['fault_id'] = $v ;
                        $add_data['is_deleted'] = 'N' ;
                        $add_data['status'] = 'UNDEAL' ;
                        $add_data['create_time'] = date('Y-m-d H:i:s') ;
                        $add_data['modify_time'] = date('Y-m-d H:i:s') ;
                        $this->baseInsert(self::tableName(),$add_data);
                    }else{
                       unset($exists_fault_ids_list[$v]);
                    }
                }

                if($exists_fault_ids_list){
                    // 剩余的不在错误列表中 需要更新为已完成
                    foreach($exists_fault_ids_list as $v){
                        $this->baseUpdate(self::tableName(),['status'=>'DEALED','deal_date'=>date('Y-m-d H:i:s'),'modify_time'=>date('Y-m-d H:i:s')],'id=:id',[':id'=>$v]);
                    }
                }
            }
        }

        return true ;
    }

    /**
     * 返回基础查询条件
     * @param $customer_id
     * @param $status
     * @param $fault_id
     * @param $start_date
     * @param $end_date
     * @param $is_work_order
     * @return mixed
     */
    public function returnBaseParams($company_id,$status,$fault_id,$start_date,$end_date,$is_work_order){
        $cond = '';
        $status = strtoupper($status);
        if($status && $status != 'ALL'){
            $cond[] = ' status=:status ';
            $params['args'][':status'] = $status ;
        }

        if($fault_id){
            $cond[] = ' fault_id=:fault_id ';
            $params['args'][':fault_id'] = $fault_id ;
        }

        if($is_work_order){
            $cond[] = 'is_work_order=:is_work_order';
            $params['args'][':is_work_order'] = "Y" ;
        }

        if($start_date){
            $cond[] = 'create_time >=:create_time' ;
            $params['args'][':create_time'] = date('Y-m-d 00:00:00',strtotime($start_date));
        }

        if($end_date){
            $cond[] = 'create_time <=:end_time' ;
            $params['args'][':end_time'] = date('Y-m-d 23:59:59',strtotime($end_date));
        }

        $cond[] = ' company_id=:company_id AND fault_id > 0 ';
        $params['args'][':company_id'] = $company_id ;

        $params['cond'] = implode(' AND ',$cond);
        return $params ;
    }

    /**
     * 返回总数目
     * @param $company_id
     * @param $status
     * @param $fault_id
     * @param $start_date
     * @param $end_date
     * @param $is_work_order
     * @return mixed
     */
    public function getTotalNum($company_id,$status,$fault_id,$start_date,$end_date,$is_work_order=false){

        $params = $this->returnBaseParams($company_id,$status,$fault_id,$start_date,$end_date,$is_work_order);
        $params['fields'] = ' count(1) as total';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info && !is_null($info['total']) ? $info['total'] : 0 ;
    }

    /**
     * 获取列表信息
     * @param $company_id
     * @param $status
     * @param $fault_id
     * @param $start_date
     * @param $end_date
     * @param $page
     * @param $page_num
     * @param $is_work_order
     * @return array
     */
    public function getListByPage($company_id,$status,$fault_id,$start_date,$end_date,$page,$page_num,$is_work_order=false){
        $params = $this->returnBaseParams($company_id,$status,$fault_id,$start_date,$end_date,$is_work_order);

        $params['page']['curr_page'] = $page;
        $params['page']['page_num'] = $page_num;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        if(!$list){
            return  [] ;
        }

        $device_list = [];
        $device_obj = new SunnyDevice();

        $project_list = [];
        $project_obj = new SunnyProject();

        $road_obj = new SunnyRoad();
        $road_list =  [];

        // 状态记录
        $status_record_obj = new SunnyDeviceStatusRecord();
        $fault_name_list = $status_record_obj->getFaultNameList();

        //名称 项目 路段 原因 发生原因 状态
        $res = [] ;
        $site_config_obj = new SiteConfig();
        $static_url = $site_config_obj->getByKey('static_url');

        foreach($list as $v){
            $item['id'] = $v['id'];
            $device_id = $v['device_id'];
            $item['device_id'] = $device_id ;

            $device_info = isset($device_list[$v['device_id']])?$device_list[$v['device_id']]:[];
            if(!$device_info){
                $device_info = $device_obj->getInfoById($device_id);
                $device_list[$device_id] = $device_info ;
            }

            $item['device_name'] =  $device_info?$device_info['device_name']:'';

            $project_id = $v['project_id'];
            $project_info = isset($project_list[$project_id])?$project_list[$project_id]:[];

            if(!$project_info){
                $project_info = $project_obj->getInfoById($project_id);
                $project_list[$project_id] = $project_info ;
            }
            $item['project_name'] =  $project_info?$project_info['name']:'';

            $road_id = $device_info['road_id'];
            $road_info = isset($road_list[$road_id])?$road_list[$road_id]:[];

            if(!$road_info){
                $road_info = $road_obj->getInfoById($road_id);
                $road_list[$road_id] = $road_info ;
            }
            $item['road_name'] =  $road_info?$road_info['name']:'';

            $fault_id = $v['fault_id'];
            $item['fault_name'] = isset($fault_name_list[$fault_id]) ? $fault_name_list[$fault_id] : '';

            $item['create_time'] = $v['modify_time'];
            $item['status'] = $v['status'];
            $item['note'] = $v['note'];
            $item['success_note'] = $v['success_note'];
            $image_list = unserialize($v['image_url']);
            if($image_list){
                foreach($image_list as $img_k=>$img_v){
                    $image_list[$img_k] = $static_url.'/'.$img_v ;
                }
            }
            $item['image_url'] = $image_list;


            $success_image_list = unserialize($v['success_img_url']);
            if($success_image_list){
                foreach($success_image_list as $img_k=>$img_v){
                    $success_image_list[$img_k] = $static_url.'/'.$img_v ;
                }
            }
            $item['success_img_url'] = $success_image_list;

            // 成功后，字段覆盖
            if($item['status'] =='DEALED'){
            //    $item['image_url'] = $item['success_img_url'] ;
              //  $item['note'] = $item['success_note'] ;
            }

            $manager_id = $v['manager_id'];
            $manager_obj = new SunnyManager();
            $manager_info = $manager_obj->getInfoById($manager_id);
 if($manager_info){
                $item['manager_email'] = $manager_info['username'] ? $manager_info['email'].'['.$manager_info['username'].']':$manager_info['email'];
            }else{
                $item['manager_email'] = '';
            }
            $deal_manager_id = $v['deal_manager_id'];
            $manager_info = $manager_obj->getInfoById($deal_manager_id);
		if($manager_info) {
                $item['deal_manager_email'] = $manager_info['username'] ? $manager_info['email'] . '[' . $manager_info['username'] . ']' : $manager_info['email'];
            }else{
                $item['deal_manager_email'] = '';
            }
            $item['future_deal_date'] = $v['future_deal_date'];
            $item['deal_date'] = $v['deal_date'];
            $item['modify_time'] = $v['modify_time'];
            $res[] = $item ;
        }

        return $res ;
    }
}

