<?php
namespace backend\controllers;
use backend\components\MyRedis;
use backend\models\Admin;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminRolePrivilege;
use backend\models\AdminTotalApiKey;
use backend\models\AdminUserApiKey;
use common\components\ExportFile;
use common\components\GoogleAuthenticator;
use common\components\PHPGangsta_GoogleAuthenticator;
use common\models\AdminMappingCompany;
use common\models\AdPosition;
use common\models\Areas;
use common\models\CashIn;
use common\models\CashOut;
use common\models\Country;
use common\models\EmailCode;
use common\models\SiteConfig;
use common\models\SunnyCompany;
use common\models\SunnyCustomer;
use common\models\SunnyDevice;
use common\models\SunnyDeviceBatteryParams;
use common\models\SunnyDeviceCategory;
use common\models\SunnyDeviceFault;
use common\models\SunnyDeviceLoadTime;
use common\models\SunnyDevicePostitionDetail;
use common\models\SunnyDeviceStatusInfo;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceStatusToday;
use common\models\SunnyDeviceStatusTotal;
use common\models\SunnyDeviceTemplate;
use common\models\SunnyDeviceTotal;
use common\models\SunnyManager;
use common\models\SunnyProject;
use common\models\SunnyRoad;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class SunnyDeviceController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $country_id = isset($_GET['country_id']) ? $_GET['country_id'] : '' ;

        if($country_id){
            $params['like_arr']['country_id'] = $country_id;
        }
        $searchArr['country_id'] = $country_id ;



        $searchArr = $this->returnSearchArr();

        $like_key = ['longitude','latitude'] ;
        $where_key = ['country_id','is_bind','category_id','parent_id','project_id'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';

        if(isset($searchArr['start_time']) && $searchArr['start_time']){
            $params['greater_where_arr']['create_time'] = date("Y-m-d 00:00:00",strtotime($searchArr['start_time']));
        }

        if(isset($searchArr['end_time']) && $searchArr['end_time']){
            $params['lesser_where_arr']['create_time'] = date("Y-m-d 23:59:59",strtotime($searchArr['end_time']));
        }

        if($this->adminUserInfo['company_type'] !='ALL'){
            $mapping_obj = new AdminMappingCompany();
            $company_ids = $mapping_obj->getCompanyIdsByAdminId($this->adminUserInfo['id']);
            if(!$company_ids){
                $params['where_arr']['id'] = 0;
            }else{
                $params['in_where_arr']['company_id'] = $company_ids ;
            }

        }


        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new SunnyDevice() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $category_obj  = new SunnyDeviceCategory();
        $customer_obj = new SunnyManager();
        $company_obj = new SunnyCompany();
        $status_info_obj = new SunnyDeviceStatusInfo();
        $record_obj = new SunnyDeviceStatusRecord();
        $today_obj = new SunnyDeviceStatusToday();
        $total_obj = new SunnyDeviceStatusTotal();
        $road_obj = new SunnyRoad();
        $project_obj = new  SunnyProject();
        foreach($list as $k=>$v){
            $list[$k]['category_id'] = $category_obj->getNameById($v['category_id']);
            $list[$k]['project_id'] = $project_obj->getNameById($v['project_id']);
            $list[$k]['project_id'] = $category_obj->getNameById($v['category_id']);
            $list[$k]['road_id'] = $road_obj->getNameById($v['road_id']);
            $list[$k]['parent_id'] = $category_obj->getNameById($v['parent_id']);
            $customer_info = $customer_obj->getInfoById($v['customer_id']);
            $company_info = $company_obj->getInfoById($v['company_id']);
            $list[$k]['customer_id'] = $customer_info ? $customer_info['email']:'';
            $list[$k]['company_id'] = $company_info ? $company_info['unique_key']:'';
            $list[$k]['status_info'] = $status_info_obj->getInfoByDeviceId($v['id']);
            $list[$k]['fault_list'] = $record_obj->getFaultShowNameList($v['id']);
            $list[$k]['today_info'] = $today_obj->checkTodayIsExists($v['id']);
            $list[$k]['total_info'] = $total_obj->checkExistsByDeviceId($v['id']);
            $list[$k]['battery_type'] = $v['battery_type'] =='10' ? '铅酸电池':'锂电池';
        }
        $renderData['list'] =$list;

        if($searchArr && $searchArr['is_download']){
            //导出文件
            return $this->doExport($list);
        }
        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;
        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;

        $adminUserInfo = $this->adminUserInfo;
        $user_id = $adminUserInfo['id'];
        $redis_key = "AdminUserFields:".$user_id;
        $redis_obj = new MyRedis();
        $redis_info = $redis_obj->get($redis_key);
        $fields_list = $redis_info ? unserialize($redis_info) : [] ;

        $renderData['fields_list'] = $fields_list ;

        $this->loadResource('cms','actionAddAd');
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new SunnyDevice();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $this->loadResource('sunny-device','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $update_data['status'] = $this->postParam('status');
        $update_data['modify_time'] = date('Y-m-d H:i:s');

        $obj = new SunnyDevice();
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new SunnyDevice();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }



    public function actionStatusList(){
        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['device_no'] ;
        $where_key = ['device_id','category_id','parent_id'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';

        if(isset($searchArr['start_time']) && $searchArr['start_time']){
            $params['greater_where_arr']['create_time'] = date("Y-m-d 00:00:00",strtotime($searchArr['start_time']));
        }

        if(isset($searchArr['end_time']) && $searchArr['end_time']){
            $params['lesser_where_arr']['create_time'] = date("Y-m-d 23:59:59",strtotime($searchArr['end_time']));
        }

        $is_not_group_by = isset($_GET['not_group_by']) ? $_GET['not_group_by'] : "N" ;
        $renderData['not_group_by'] = $is_not_group_by ;

        if($is_not_group_by =="N"){
            $params['group_by'] = 'device_id';
        }
        $model = new SunnyDeviceStatusRecord() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());


        $category_obj  = new SunnyDeviceCategory();
        $customer_obj = new SunnyManager();
        $company_obj = new SunnyCompany();
        $category_list = [];
        $company_list = [];
        foreach($list as $k=>$v){

            if($is_not_group_by =="N"){
                $list[$k] = $model->getLastedInfoByDeviceId($v['device_id']);
            }

            $category_info  = isset($category_list[$v['category_id']]) ? $category_list[$v['category_id']] : [];
            if(!$category_info){
                $category_info = $category_obj->getNameById($v['category_id']);
                $category_list[$v['category_id']] = $category_info ;
            }
            $list[$k]['category_id'] = $category_info;
            $company_info = isset($company_list[$v['company_id']]) ? $company_list[$v['company_id']] : [] ;
            if(!$company_info){
                $company_info = $company_obj->getInfoById($v['company_id']);
                $company_list[$v['company_id']] = $company_info ;
            }

            $list[$k]['company_id'] = $company_info ? $company_info['company_name']:'';
        }

        if($searchArr && $searchArr['is_download']){
            //导出文件
            return $this->doExportStatusList($list);
        }

        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'count(1) as total';
        $list = $model->findOneByWhere($model::tableName(),$params, $model::getDb());
        $total = $list && !is_null($list['total']) ? $list['total']: 0 ;

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;

        $this->loadResource('cms','actionAddAd');
        return $this->render('status-list',$renderData) ;
    }

    // 执行导出
    private function doExport($list){

        $export_data[] = [
            '一级分类',
            '二级分类',
            '设备编号'
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['parent_id'];
                $data[] = $v['category_id'];
                $data[] = $v['qr_code'];

                $export_data[] = $data;

                unset($data);
            }
        }

        $export_obj = new ExportFile();
        $file_name = '设备列表-'.date('Y-m-d').'.csv';
        $export_obj->download($export_data,$file_name);

    }

    private function doExportStatusList($list){


        $export_data[] = [
            'ID',
            '所属公司',
            '所属分类',
            '设备编号',
            '电池电量',
            '电池电压',
            '电池充电电流',
            '环境温度',
            '电池面板充电电压',
            '充电电流',
            '累计充电电量(KW时)',
            '负载状态(有/无)',
            '开关状态',
            '亮度%',
            '最新同步时间',
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['id'];
                $data[] = $v['company_id'];
                $data[] = $v['category_id'];
                $data[] = $v['device_no'];
                $data[] = $v['battery_volume'];
                $data[] = $v['battery_voltage'];
                $data[] = $v['battery_charging_current'];
                $data[] = $v['ambient_temperature'];
                $data[] = $v['battery_panel_charging_voltage'];
                $data[] = $v['charging_current'];
                $data[] = $v['cumulative_charge'];
                $data[] = $v['load_status'];
                $data[] = $v['switch_status'];
                $data[] = $v['brightness'];
                $data[] = $v['create_time'];

                $export_data[] = $data;

                unset($data);
            }
        }

        $export_obj = new ExportFile();
        $file_name = '设备列表-'.date('Y-m-d').'.csv';
        $export_obj->download($export_data,$file_name);

    }

    // 针对汇总筛选
    public function actionTradeList(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $country_id = isset($_GET['country_id']) ? $_GET['country_id'] : '' ;

        if($country_id){
            $params['like_arr']['country_id'] = $country_id;
        }
        $searchArr['country_id'] = $country_id ;

        $searchArr = $this->returnSearchArr();

        $like_key = ['longitude','latitude'] ;
        $where_key = ['country_id','is_bind','category_id','parent_id'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';

        if(isset($searchArr['start_time']) && $searchArr['start_time']){
            $params['greater_where_arr']['create_time'] = date("Y-m-d 00:00:00",strtotime($searchArr['start_time']));
        }

        if(isset($searchArr['end_time']) && $searchArr['end_time']){
            $params['lesser_where_arr']['create_time'] = date("Y-m-d 23:59:59",strtotime($searchArr['end_time']));
        }

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new SunnyDevice() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $category_obj  = new SunnyDeviceCategory();
        $customer_obj = new SunnyManager();
        $company_obj = new SunnyCompany();
        foreach($list as $k=>$v){
            $list[$k]['category_id'] = $category_obj->getNameById($v['category_id']);
            $list[$k]['parent_id'] = $category_obj->getNameById($v['parent_id']);
            $customer_info = $customer_obj->getInfoById($v['customer_id']);
            $company_info = $company_obj->getInfoById($v['company_id']);
            $list[$k]['customer_id'] = $customer_info ? $customer_info['email']:'';
            $list[$k]['company_id'] = $company_info ? $company_info['company_name']:'';
        }
        $renderData['list'] =$list;

        if($searchArr && $searchArr['is_download']){
            //导出文件
            return $this->doExport($list);
        }
        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;
        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;

        $this->loadResource('cms','actionAddAd');

        // 获取选中的设备ID
        $adminUserInfo = $this->adminUserInfo;
        $redis_key = "Admin:select_device_id:".$adminUserInfo['id'];
        $redis_obj = new MyRedis();
        $redis_info = $redis_obj->get($redis_key);

        $select_ids_arr = unserialize($redis_info);
        $renderData['select_ids_arr'] = $select_ids_arr ;
        return $this->render('trade-list',$renderData) ;
    }

    public function actionAjaxSelectId(){
        $id = $this->postParam('id');
        $type = $this->postParam('type');
        $adminUserInfo = $this->adminUserInfo;
        $redis_key = "Admin:select_device_id:".$adminUserInfo['id'];
        $redis_obj = new MyRedis();
        $redis_info = $redis_obj->get($redis_key);

        $select_ids_arr = unserialize($redis_info);
        if($type == 'add'){
            if(!in_array($id,$select_ids_arr)){
                $select_ids_arr[] = $id ;
                $redis_obj->set($redis_key,serialize($select_ids_arr),1800);
            }
        }else{

            foreach ($select_ids_arr as $key=>$value)
            {
                if ($value === $id)
                    unset($select_ids_arr[$key]);
            }

            asort($select_ids_arr);
            $redis_obj->set($redis_key,serialize($select_ids_arr),1800);
        }

        return $this->returnJson(['code'=>1]);
    }

    /**
     * 设备其他设置
     */
    public function actionSettingOther(){
        $ids = $this->getParam('ids');
        $data['ids'] = $ids ;
        $ids_arr = explode('_',$ids);
        $first = isset($ids_arr[0]) ? $ids_arr[0] : 0 ;
        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($first);

        $obj = new SunnyDeviceLoadTime();
        $list = $obj->getListByDeviceId($first);


        $template_id = $this->getParam('template_id');
        if($template_id){
            $template_obj = new SunnyDeviceTemplate();
            $template_info = $template_obj->getDetailInfoById($template_id);

            if($device_info){
                foreach($device_info as $k=>$v){
                    if(isset($template_info[$k])){
                        $device_info[$k] = $template_info[$k] ;
                    }
                }
            }else{
                $device_info = $template_info ;
            }

            $device_info['template_id'] = $template_id ;


            $minutes = $template_info['minutes'];
            $load_sensor_on_power = $template_info['load_sensor_on_power'];
            $list = [] ;
            foreach($minutes as $k=>$v){
                $item['minutes'] = $v;
                $item['load_sensor_on_power'] = $load_sensor_on_power[$k];
                $item['load_sensor_off_power'] =0;
                $item['time_end'] = $k+1;
                $list[] = $item ;
            }
        }

        $data['time_list'] = $list ;

        $data['device_info'] = $device_info;
        $data['is_other'] = true;
        $data['is_battery'] = false;

        $template_obj = new SunnyDeviceTemplate() ;
        $data['template_list'] = $template_obj->getListByType('LOAD','id,name');

        // 查询时段信息
        $this->loadResource('sunny-device','actionSettingOther');
        return $this->render('setting-other',$data);
    }

    // 其他设置保存
    public function actionSettingOtherSave(){

        $ids = $this->postParam('ids');

        $load_obj = new SunnyDeviceLoadTime();
        $res = $load_obj->savePostData($ids,$_POST);
        if(!$res){
            return $this->returnJson($load_obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg('1')]);
    }

    /**
     * 故障设备列表
     */
    public function actionFaultList(){

        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $searchArr = $this->returnSearchArr();
        $renderData['searchArr'] =$searchArr ;

        $like_key = [] ;
        $where_key = ['category_id','parent_id','qr_code'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $params['where_arr']['is_deleted'] = 'N';

        if(isset($searchArr['start_time']) && $searchArr['start_time']){
            $params['greater_where_arr']['create_time'] = date("Y-m-d 00:00:00",strtotime($searchArr['start_time']));
        }

        if(isset($searchArr['end_time']) && $searchArr['end_time']){
            $params['lesser_where_arr']['create_time'] = date("Y-m-d 23:59:59",strtotime($searchArr['end_time']));
        }

        if($this->adminUserInfo['company_type'] !='ALL') {
            $mapping_obj = new AdminMappingCompany();
            $company_ids = $mapping_obj->getCompanyIdsByAdminId($this->adminUserInfo['id']);
            if (!$company_ids) {
                $params['where_arr']['id'] = 0;
            } else {
                $params['in_where_arr']['company_id'] = $company_ids;
            }
        }

        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $this->page_rows);
        $params['order_by'] = ' modify_time desc ';

        $model = new SunnyDeviceFault() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $category_obj  = new SunnyDeviceCategory();
        $customer_obj = new SunnyManager();
        $company_obj = new SunnyCompany();
        $record_obj = new SunnyDeviceStatusRecord();
        $fault_list = $record_obj->getFaultNameList();
        foreach($list as $k=>$v){
            $list[$k]['category_id'] = $category_obj->getNameById($v['category_id']);
            $list[$k]['parent_id'] = $category_obj->getNameById($v['parent_id']);
            $customer_info = $customer_obj->getInfoById($v['customer_id']);
            $company_info = $company_obj->getInfoById($v['company_id']);
            $list[$k]['customer_id'] = $customer_info ? $customer_info['email']:'';
            $list[$k]['company_id'] = $company_info ? $company_info['company_name']:'';
            $list[$k]['fault_list'] = isset($fault_list[$v['fault_id']])?$fault_list[$v['fault_id']]:'';
        }
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$this->page_rows);
        $renderData['page_data'] = $page_data;
        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;

        $this->loadResource('cms','actionAddAd');
        return $this->render('fault-list',$renderData) ;
    }

    // 初始化设置
    public function  actionInitSetting(){
        $renderData['info'] = [] ;
        $site_config = new SiteConfig();
        $renderData['init_zoom'] = $site_config->getByKey('init_zoom');
        $renderData['init_longitude'] = $site_config->getByKey('init_longitude');
        $renderData['init_latitude'] = $site_config->getByKey('init_latitude');
        $renderData['init_hop'] = $site_config->getByKey('init_hop');
        $renderData['init_his'] = $site_config->getByKey('init_his');

        $id = $this->getParam('id');
        if($id){
            $manager_obj = new SunnyManager();
            $info  = $manager_obj->getInfoById($id);
            $renderData['init_zoom'] = $info['init_zoom'];
            $renderData['init_longitude'] = $info['init_longitude'];
            $renderData['init_latitude'] = $info['init_latitude'];
            $renderData['init_hop'] = $info['init_hop'];
            $renderData['init_his'] = $info['init_his'];
        }
        $this->loadResource('sunny-device','actionInitSetting');
        $renderData['id'] = intval($id) ;
        return $this->render('init-setting',$renderData) ;
    }

    public function actionSaveInitSetting(){

        $id = $this->postParam('id');
        if($id){

            $manager_obj = new SunnyManager();
            $info  = $manager_obj->getInfoById($id);
            $update_data['init_zoom'] = $this->postParam('init_zoom');
            $update_data['init_longitude'] = $this->postParam('init_longitude');
            $update_data['init_latitude'] = $this->postParam('init_latitude');
            $update_data['modify_time'] = date('Y-m-d H:i:s');
            if($info){
                $manager_obj->baseUpdate($manager_obj::tableName(),$update_data,'id=:id',[':id'=>$id]);
            }
        }else{
            $site_config = new SiteConfig();
            $site_config->saveByKey('init_zoom',$this->postParam('init_zoom'));
            $site_config->saveByKey('init_longitude',$this->postParam('init_longitude'));
            $site_config->saveByKey('init_latitude',$this->postParam('init_latitude'));
            $site_config->saveByKey('init_hop',$this->postParam('init_hop'));
            $site_config->saveByKey('init_his',$this->postParam('init_his'));
        }



        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 瞬时数据
    public function actionCurrentData(){
        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['device_no'] ;
        $where_key = ['category_id','parent_id'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);


        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new SunnyDeviceStatusInfo() ;
        $status_today_obj = new SunnyDeviceStatusToday();
        $list = $status_today_obj->findByWhere($status_today_obj::tableName(),$params, $model::getDb());
        $category_obj = new SunnyDeviceCategory();
        $device_obj = new SunnyDevice();
        $position_obj = new SunnyDevicePostitionDetail();
        $company_obj = new SunnyCompany();
        $status_obj = new SunnyDeviceStatusRecord();


        $charge_list = $status_obj->returnChargeStatusList();
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['category_name'] = $category_obj->getNameById($v['category_id']);
                $device_info = $device_obj->getInfoById($v['device_id']);
                $list[$k]['device_info'] = $device_info ;
                $list[$k]['position_info'] = $position_obj->getInfoByDeviceId($v['device_id']) ;
                $list[$k]['company_info'] = $company_obj->getInfoById($v['company_id']) ;
                $list[$k]['status_record'] = $status_obj->getLastedInfoByDeviceId($v['device_id']) ;
                $list[$k]['charge_status_name'] = isset($charge_list[$v['charge_status']])?$charge_list[$v['charge_status']]:'' ;
            }
        }

        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        // 查询筛选的分类数据列表
        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;
        return $this->render('current-data',$renderData) ;
    }

    // 历史信息
    public function actionTotalHistory(){
        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['device_no'] ;
        $where_key = ['category_id','parent_id'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);


        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';

        if($this->adminUserInfo['company_type'] !='ALL') {
            $mapping_obj = new AdminMappingCompany();
            $company_ids = $mapping_obj->getCompanyIdsByAdminId($this->adminUserInfo['id']);
            if (!$company_ids) {
                $params['where_arr']['id'] = 0;
            } else {
                $params['in_where_arr']['company_id'] = $company_ids;
            }
        }

        $model = new SunnyDeviceStatusTotal() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $category_obj = new SunnyDeviceCategory();
        $device_obj = new SunnyDevice();
        $position_obj = new SunnyDevicePostitionDetail();
        $company_obj = new SunnyCompany();
        $status_obj = new SunnyDeviceStatusRecord();

        if($list){
            foreach($list as $k=>$v){
                $list[$k]['category_name'] = $category_obj->getNameById($v['category_id']);
                $device_info = $device_obj->getInfoById($v['device_id']);
                $list[$k]['device_info'] = $device_info ;
                $list[$k]['position_info'] = $position_obj->getInfoByDeviceId($v['device_id']) ;
                $list[$k]['company_info'] = $company_obj->getInfoById($v['company_id']) ;
                $list[$k]['status_record'] = $status_obj->getLastedInfoByDeviceId($v['device_id']) ;
            }
        }

        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        // 查询筛选的分类数据列表
        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;
        return $this->render('total-history',$renderData) ;
    }

    //时段信息
    public function  actionTimeList(){
        $device_id = $this->getParam('id');
        $obj = new SunnyDeviceLoadTime();
        $list = $obj->getListByDeviceId($device_id);
        $renderData['list'] = $list ;

        //查询设别基本信息
        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($device_id);
        $battery_type = $device_info['battery_type'] == 10 ? '铅酸电池':'锂电池';
        $battery_rate_volt = $device_info['battery_rate_volt'].'V';
        $led_current_set = $device_info['led_current_set'].'A';
        $auto_power_set_arr = ['关闭','低','中','高','自动'] ;
        $auto_power_set = $device_info['auto_power_set'];
        $auto_power_set = isset($auto_power_set_arr[$auto_power_set]) ? $auto_power_set_arr[$auto_power_set] :'';

        $renderData['battery_type'] = $battery_type;
        $renderData['battery_rate_volt'] = $battery_rate_volt;
        $renderData['led_current_set'] = $led_current_set;
        $renderData['auto_power_set'] = $auto_power_set;
        return $this->render('time-list',$renderData) ;
    }

    // 设备工况
    public function actionWorking(){

        $searchArr = $this->returnSearchArr();


        $device_id = $this->getParam('id') ;
        $renderData['id'] = $device_id ;
        $range_type = $this->getParam('range_type') ;
        $range_type = $range_type ? $range_type:'HOUR';
        $searchArr['range_type'] = $range_type ;
        $range_type_list = ['HALF_HOUR'=>'半小时','HOUR'=>'小时','DAY'=>'天','WEEK'=>'周'];
        $renderData['range_type_list'] = $range_type_list ;
        $start_time = $this->getParam('start_time');
        $end_time = $this->getParam('end_time');
        $start_time = $start_time  ? $start_time : date('Y-m-d',time()-3*86400);
        $end_time  = $end_time?$end_time:date('Y-m-d');
        $searchArr['start_time'] = $start_time ;
        $searchArr['end_time'] = $end_time ;

        $start_time = date("Y-m-d 00:00:00",strtotime($start_time));
        $end_time = date("Y-m-d",strtotime($end_time)).' '.date("H:i:s");

        $total_obj = new SunnyDeviceTotal();
        $params['cond'] = 'device_id =:device_id AND time_type =:time_type AND  timestamp >=:start_time AND timestamp <=:end_time' ;
        $params['args'] =[':device_id'=>$device_id,':time_type'=>$range_type,':start_time'=>strtotime($start_time),':end_time'=>strtotime($end_time)];
        $list = $total_obj->findAllByWhere($total_obj::tableName(),$params,$total_obj::getDb());

        $show_time = $this->getParam('show_time');
        $show_time = $show_time?$show_time:'light';

        $searchArr['show_time'] = $show_time ;

        $show_time_list = ['light'=>'路灯','dianliang'=>'充放电量','battery'=>'蓄电池','panel'=>'太阳能电板'] ;
        $renderData['show_time_list'] = $show_time_list;
        $renderData['searchArr'] =$searchArr ;

        $return_info  = $this->getJsonFieldAndSeries('light',$range_type,$start_time,$end_time,$list,$show_time_list);
        // 设置返回图表信息
        $renderData['series_arr'] = json_encode($return_info['series_arr']);
        $renderData['time_list_str'] = json_encode($return_info['time_list_arr']) ;
        $renderData['json_field_list'] = $return_info['json_field_list'] ;
        $renderData['json_title'] = $return_info['json_title'] ;


        $dianliang_return_info  = $this->getJsonFieldAndSeries('dianliang',$range_type,$start_time,$end_time,$list,$show_time_list);
        // 设置返回图表信息
        $renderData['dianliang_series_arr'] = json_encode($dianliang_return_info['series_arr']);
        $renderData['dianliang_time_list_str'] = json_encode($dianliang_return_info['time_list_arr']) ;
        $renderData['dianliang_json_field_list'] = $dianliang_return_info['json_field_list'] ;
        $renderData['dianliang_json_title'] = $dianliang_return_info['json_title'] ;

        $battery_return_info  = $this->getJsonFieldAndSeries('battery',$range_type,$start_time,$end_time,$list,$show_time_list);
        // 设置返回图表信息
        $renderData['battery_series_arr'] = json_encode($battery_return_info['series_arr']);
        $renderData['battery_time_list_str'] = json_encode($battery_return_info['time_list_arr']) ;
        $renderData['battery_json_field_list'] = $battery_return_info['json_field_list'] ;
        $renderData['battery_json_title'] = $battery_return_info['json_title'] ;


        $panel_return_info  = $this->getJsonFieldAndSeries('panel',$range_type,$start_time,$end_time,$list,$show_time_list);
        // 设置返回图表信息
        $renderData['panel_series_arr'] = json_encode($panel_return_info['series_arr']);
        $renderData['panel_time_list_str'] = json_encode($panel_return_info['time_list_arr']) ;
        $renderData['panel_json_field_list'] = $panel_return_info['json_field_list'] ;
        $renderData['panel_json_title'] = $panel_return_info['json_title'] ;

        $this->loadResource('sunny-device','actionWorking');
        return $this->render('working',$renderData) ;
    }

    private function getJsonFieldAndSeries($show_time,$range_type,$start_time,$end_time,$list,$show_time_list){

        $series_arr = [] ;
        $json_field_list = '';
        $time_list_arr = '';

        if($show_time =='light'){
            $json_field_list = "['电压', '电流', '功率', '亮度']";
            $series_arr[0]['name'] = '电压';
            $series_arr[1]['name'] = '电流';
            $series_arr[2]['name'] = '功率';
            $series_arr[3]['name'] = '亮度';

        }else if($show_time =='dianliang'){
            $json_field_list = "['放电', '充电']";
            $series_arr[0]['name'] = '放电';
            $series_arr[1]['name'] = '充电';
        }else if($show_time =='battery'){
            $json_field_list = "['电压', '电流','温度']";
            $series_arr[0]['name'] = '电压';
            $series_arr[1]['name'] = '电流';
            $series_arr[2]['name'] = '温度';
        }else if($show_time =='panel'){
            $json_field_list = "['电压', '电流','功率']";
            $series_arr[0]['name'] = '电压';
            $series_arr[1]['name'] = '电流';
            $series_arr[2]['name'] = '温度';
        }

        foreach($series_arr as $k=>$v){
            $series_arr[$k]['type'] = 'line';
            $series_arr[$k]['symbol'] = 'none';
            $series_arr[$k]['smooth'] = true;
            $series_arr[$k]['stack'] = '总量';
            $series_arr[$k]['label']['normal']['show'] = true;
        }

        $time_list_arr = [];
        $timestamp_list_arr = [];
        $step_seconds =  3600;
        $format_time_string =  "m-d H:00";
        if($range_type =='HALF_HOUR'){
            $step_seconds = 1800;
            $format_time_string = date("i")<30 ? "m-d H:00":"m-d H:30";

        }else if($range_type =='HOUR'){
            $step_seconds = 3600;
            $format_time_string =  "m-d H:00";
        }else if($range_type =='DAY'){
            $step_seconds = 86400;
            $format_time_string = "m-d";
        }else if($range_type =='WEEK'){
            $step_seconds = 86400*7;
            $format_time_string = "m-d";
        }

        for($i=strtotime($start_time);$i<=strtotime($end_time);$i=$i+$step_seconds){
            $time_list_arr[] = date($format_time_string,$i);
            $timestamp_list_arr[] = $i;
        }

        $data_list = [] ;
        foreach($list as $v){
            $data_list[$v['timestamp']] = $v ;
        }

        foreach($timestamp_list_arr as $v){

            if(isset($data_list[$v])){

                $data_info = $data_list[$v];
                if($show_time =='light'){

                    $series_arr[0]['data'][] = $data_info['load_dc_power'] ;
                    $series_arr[1]['data'][] = $data_info['charging_current'] ;
                    $series_arr[2]['data'][] = $data_info['cumulative_charge'] ;
                    $series_arr[3]['data'][] = $data_info['brightness'] ;
                }else if($show_time =='dianliang'){
                    $series_arr[0]['data'][] = $data_info['charging_current'] ;
                    $series_arr[1]['data'][] = $data_info['battery_charging_current'] ;

                }else if($show_time =='battery'){

                    $series_arr[0]['data'][] = $data_info['battery_voltage'] ;
                    $series_arr[1]['data'][] = $data_info['battery_charging_current'] ;
                    $series_arr[2]['data'][] = $data_info['battery_temperature'] ;

                }else if($show_time =='panel'){

                    $series_arr[0]['data'][] = $data_info['battery_panel_charging_voltage'] ;
                    $series_arr[1]['data'][] = $data_info['battery_panel_charging_current'] ;
                    $series_arr[2]['data'][] = $data_info['charging_power'] ;
                }
            }else{

                if($show_time =='light'){
                    $series_arr[0]['data'][] = 0 ;
                    $series_arr[1]['data'][] = 0 ;
                    $series_arr[2]['data'][] = 0 ;
                    $series_arr[3]['data'][] = 0 ;
                }else if($show_time =='dianliang'){

                    $series_arr[0]['data'][] = 0 ;
                    $series_arr[1]['data'][] = 0 ;

                }else if($show_time =='battery'){
                    $series_arr[0]['data'][] = 0 ;
                    $series_arr[1]['data'][] = 0 ;
                    $series_arr[2]['data'][] = 0 ;
                }else if($show_time =='panel'){
                    $series_arr[0]['data'][] = 0 ;
                    $series_arr[1]['data'][] = 0 ;
                    $series_arr[2]['data'][] = 0 ;
                }

            }


        }

        $json_title = $show_time_list[$show_time];
        return compact('series_arr','json_field_list','time_list_arr','json_title');
    }

    // 单一设备信息汇总
    public function actionDetailShow(){

        $device_id = $this->getParam('id');
        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($device_id);
        $renderData['device_info'] = $device_info ;

        $device_info = $device_obj->getInfoById($device_id);
        $battery_type = $device_info['battery_type'] == 10 ? '铅酸电池':'锂电池';
        $battery_rate_volt = $device_info['battery_rate_volt'].'V';
        $led_current_set = $device_info['led_current_set'].'A';
        $auto_power_set_arr = ['关闭','低','中','高','自动'] ;
        $auto_power_set = $device_info['auto_power_set'];
        $auto_power_set = isset($auto_power_set_arr[$auto_power_set]) ? $auto_power_set_arr[$auto_power_set] :'';

        $renderData['battery_type'] = $battery_type;
        $renderData['battery_rate_volt'] = $battery_rate_volt;
        $renderData['led_current_set'] = $led_current_set;
        $renderData['auto_power_set'] = $auto_power_set;

        // 查询72小时之内的对应的设备状态的记录信息
        $start_time = date('Y-m-d H:i:s',time()-72*3600);
        $end_time = date('Y-m-d H:i:s');
        $status_record_obj = new SunnyDeviceStatusRecord();
        $record_params['cond'] = 'device_id=:device_id AND create_time >=:start_time AND create_time <=:end_time';
        $record_params['args'] = [':device_id'=>$device_id,':start_time'=>$start_time,':end_time'=>$end_time];
        $record_params['limit'] = 10  ;
        $record_params['orderby'] = 'id desc';
        $status_record_list = $status_record_obj->findAllByWhere($status_record_obj::tableName(),$record_params,$status_record_obj::getDb());
        $renderData['status_record_list'] = $status_record_list ;

        $today_obj = new SunnyDeviceStatusToday();
        $today_list = $today_obj->findAllByWhere($today_obj::tableName(),$record_params,$today_obj::getDb());
        $renderData['today_list'] = $today_list;

        return $this->render('detail-show',$renderData);
    }

    public function actionEmptyFrame(){
        $this->layout = 'empty';
        $device_id = $this->getParam('device_id');
        $device_obj = new SunnyDevice();
        $device_info = $device_obj->getInfoById($device_id);
        $renderData['device_info'] = $device_info ;
        return $this->render('empty-frame',$renderData);
    }


    /**
     * 设备其他设置
     */
    public function actionSettingBatteryParams(){
        $ids = $this->getParam('ids');
        $data['ids'] = $ids ;
        $ids_arr = explode('_',$ids);
        $first = isset($ids_arr[0]) ? $ids_arr[0] : 0 ;
        $obj = new SunnyDeviceBatteryParams();
        $info = $obj->getInfoByDeviceId($first);
        $device_obj =  new SunnyDevice();
        $device_info = $device_obj->getInfoById($first);
        $data['device_info'] = $device_info ;

        $template_id = $this->getParam('template_id');
        if($template_id){
            $template_obj = new SunnyDeviceTemplate();
            $template_info = $template_obj->getDetailInfoById($template_id);

            if($info){
                foreach($info as $k=>$v){
                    if(isset($template_info[$k])){
                        $info[$k] = $template_info[$k] ;
                    }
                }
            }else{
                $info = $template_info ;
            }

            $info['template_id'] = $template_id ;
        }

        $data['info'] = $info;

        $data['is_other'] = false;
        $data['is_battery'] = true;

        $record_obj = new SunnyDeviceStatusRecord();
        $data['battery_type_list'] = $record_obj->returnBatteryTypeList();
        $data['li_battery_type_list'] = $record_obj->returnLiBatteryTypeList();

        $template_obj = new SunnyDeviceTemplate() ;
        $data['template_list'] = $template_obj->getListByType('BATTERY','id,name');

        // 查询时段信息
        $this->loadResource('sunny-device','actionSettingBatteryParams');
        return $this->render('setting-battery-params',$data);
    }

    // 其他设置保存
    public function actionSettingBatteryParamsSave(){

        $ids = $this->postParam('ids');

        $load_obj = new SunnyDeviceBatteryParams();
        $res = $load_obj->savePostData($ids,$_POST);
        if(!$res){
            return $this->returnJson($load_obj->error_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg('1')]);
    }

    // 用户展示字段
    public function actionUserFields(){
        $adminUserInfo = $this->adminUserInfo;
        \Yii::$app->view->params['isEmpty'] = true;
        $user_id = $adminUserInfo['id'];
        $redis_key = "AdminUserFields:".$user_id;
        $redis_obj = new MyRedis();
        $redis_info = $redis_obj->get($redis_key);
        $fields_list = $redis_info ? unserialize($redis_info) : [] ;
        $data['fields_list'] = $fields_list ;
        // 展示的字段 列表
        $device_obj  = new SunnyDevice();
        $enum_list = $device_obj->returnSettingEnumList();
        $data['enum_list'] = $enum_list ;

        $this->loadResource('sunny-device','actionUserFields');

        return  $this->render('user-fields',$data);
    }

    // 用户展示字段
    public function actionUserFieldsSave(){
        $adminUserInfo = $this->adminUserInfo;

        $user_fields = $_POST['user_fields'];
        $res = [];
        if($user_fields){
            foreach($user_fields as $v){
                if($v){
                    $res[] = $v ;
                }
            }
        }
        $user_id = $adminUserInfo['id'];
        $redis_key = "AdminUserFields:".$user_id;
        $redis_obj = new MyRedis();
        $redis_obj->set($redis_key,serialize($res));
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg('1')]);
    }

    // 用户展示字段
    public function actionUserFieldsHistory(){
        $adminUserInfo = $this->adminUserInfo;
        \Yii::$app->view->params['isEmpty'] = true;
        $user_id = $adminUserInfo['id'];
        $redis_key = "AdminUserHistoryFields:".$user_id;
        $redis_obj = new MyRedis();
        $redis_info = $redis_obj->get($redis_key);
        $fields_list = $redis_info ? unserialize($redis_info) : [] ;
        $data['fields_list'] = $fields_list ;
        // 展示的字段 列表
        $device_obj  = new SunnyDevice();
        $enum_list = $device_obj->returnSettingEnumList(true);
        $data['enum_list'] = $enum_list ;

        $this->loadResource('sunny-device','actionUserFields');

        return  $this->render('user-fields-history',$data);
    }

    // 用户展示字段
    public function actionUserFieldsHistorySave(){
        $adminUserInfo = $this->adminUserInfo;

        $user_fields = $_POST['user_fields'];
        $res = [];
        if($user_fields){
            foreach($user_fields as $v){
                if($v){
                    $res[] = $v ;
                }
            }
        }
        $user_id = $adminUserInfo['id'];
        $redis_key = "AdminUserHistoryFields:".$user_id;
        $redis_obj = new MyRedis();
        $redis_obj->set($redis_key,serialize($res));
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg('1')]);
    }

    // 测试导出
    public function actionDoExport1(){

        $json_string = '';
        $json_string = json_decode($json_string,true);

        $list = $json_string['data'];

        $export_data[] = [
            '创建时间',
            '购买人',
            '购买人手机号码',
            '产品名称'
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = date('Y-m-d H:i:s',$v['create_time']);
                $receiver_info = $v['receiver_info'];
                $data[] = $receiver_info['post_receiver'];
                $data[] = $receiver_info['post_tel'];

                $product_item = $v['product_item'][0];
                $data[] = $product_item['product_name'];

                $export_data[] = $data;

                unset($data);
            }
        }

        $export_obj = new ExportFile();
        $file_name = '订单列表-'.date('Y-m-d').'.csv';
        $export_obj->download($export_data,$file_name);

    }

    // 实时历史记录信息  根据设备本身做删减
    public function actionTodayHistory(){

        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['device_no'] ;
        $where_key = ['category_id','parent_id'];
        $params = $this->returnParams($searchArr,$where_key,$like_key);

        $qr_code = $this->getParam('qr_code');
        $device_obj = new SunnyDevice();
        if($qr_code){
            $device_info = $device_obj->getInfoByQrcode($qr_code);
            if(!$device_info){
                $params['where_arr']['device_id'] = 0 ;
            }else{
                $params['where_arr']['device_id'] = $device_info['id'] ;
            }
        }
        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';

        if(isset($searchArr['start_time']) && $searchArr['start_time']){
            $params['greater_where_arr']['date'] = $searchArr['start_time'];
        }

        if(isset($searchArr['end_time']) && $searchArr['end_time']){
            $params['lesser_where_arr']['date'] = $searchArr['start_time'];
        }

        if($this->adminUserInfo['company_type'] !='ALL') {
            $mapping_obj = new AdminMappingCompany();
            $company_ids = $mapping_obj->getCompanyIdsByAdminId($this->adminUserInfo['id']);
            if (!$company_ids) {
                $params['where_arr']['id'] = 0;
            } else {
                $params['in_where_arr']['company_id'] = $company_ids;
            }
        }

        $model = new SunnyDeviceStatusToday() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $category_obj = new SunnyDeviceCategory();

        $position_obj = new SunnyDevicePostitionDetail();
        $company_obj = new SunnyCompany();
        $status_obj = new SunnyDeviceStatusRecord();
        $customer_obj = new SunnyManager();
        $project_obj = new SunnyProject();
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['project_id'] = $project_obj->getNameById($v['project_id']);
                $list[$k]['category_id'] = $category_obj->getNameById($v['category_id']);
                $list[$k]['parent_id'] = $category_obj->getNameById($v['parent_id']);
                $device_info = $device_obj->getInfoById($v['device_id']);
                $list[$k]['device_info'] = $device_info ;
                $list[$k]['position_info'] = $position_obj->getInfoByDeviceId($v['device_id']) ;
                $customer_info = $customer_obj->getInfoById($v['customer_id']);
                $company_info = $company_obj->getInfoById($v['company_id']);
                $list[$k]['customer_id'] = $customer_info ? $customer_info['email']:'';
                $list[$k]['company_id'] = $company_info ? $company_info['unique_key']:'';
                $list[$k]['status_record'] = $status_obj->getLastedInfoByDeviceId($v['device_id']) ;
            }
        }

        $renderData['list'] =$list;

        if($searchArr && $searchArr['is_download']){
            //导出文件
            return $this->doExportToday($list);
        }

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;

        // 查询筛选的分类数据列表
        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;

        $adminUserInfo = $this->adminUserInfo;
        $user_id = $adminUserInfo['id'];
        $redis_key = "AdminUserHistoryFields:".$user_id;
        $redis_obj = new MyRedis();
        $redis_info = $redis_obj->get($redis_key);
        $fields_list = $redis_info ? unserialize($redis_info) : [] ;

        $renderData['fields_list'] = $fields_list ;

        $this->loadResource('cms','actionAddAd');

        return $this->render('today-history',$renderData) ;
    }

    private function doExportToday($list){


        $export_data[] = [
            'ID',
            '所属父级分类',
            '项目名称',
            '所属分类',
            '绑定客户',
            '所属公司',
            '日期',
            '蓄电池当天最低电',
            '蓄电池当天最高电',
            '当天放电最大电流',
            '当天充电最大功率',
            '当天放电最大功率',
            '当天充电安时数',
            '当天放电安时数',
            '当天发电量',
            '当天用电量',
            '当天电池最高温度',
            '当天电池最低温度',
            '当天亮灯时间 （有人',
            '当天亮灯时间 （无人）',
            '亮灯指数',
            '能耗指数',
            '健康指数',
            '当天充电时间',
            '夜晚长度',
            '最后同步时间',
        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['id'];
                $data[] = $v['parent_id'];
                $data[] = $v['project_id'];
                $data[] = $v['category_id'];
                $data[] = $v['customer_id'];
                $data[] = $v['company_id'];
                $data[] = $v['date'];
                $data[] = $v['bat_min_volt_today'];
                $data[] = $v['bat_max_volt_today'];
                $data[] = $v['bat_max_chg_current_today'];
                $data[] = $v['bat_max_charge_power_today'];
                $data[] = $v['bat_max_discharge_power_today'];
                $data[] = $v['bat_charge_ah_today'];
                $data[] = $v['bat_discharge_ah_today'];
                $data[] = $v['generat_energy_today'];
                $data[] = $v['used_energy_today'];
                $data[] = $v['bat_highest_temper'];
                $data[] = $v['bat_lowest_temper'];
                $data[] = $v['led_sensor_on_time'];
                $data[] = $v['led_sensor_off_time'];
                $data[] = $v['led_light_on_index'];
                $data[] = $v['power_save_index'];
                $data[] = $v['sys_health_index'];
                $data[] = $v['bat_charge_time'];
                $data[] = $v['night_length'];
                $data[] = $v['modify_time'];

                $export_data[] = $data;

                unset($data);
            }
        }

        $export_obj = new ExportFile();
        $file_name = '设备当日统计数据-'.date('Y-m-d').'.csv';
        $export_obj->download($export_data,$file_name);

    }

    public function actionGisMap(){
        $site_config = new SiteConfig();
        $total_map_url = $site_config->getByKey('total_map_url');
        $adminUserInfo = $this->adminUserInfo ;
        $total_map_url .= '?admin_user_id='.$adminUserInfo['id'];
        $renderData['total_map_url'] = $total_map_url ;
        return $this->render('gis-map',$renderData) ;
    }

    public function actionGis(){
        $site_config = new SiteConfig();
        $total_map_url = $site_config->getByKey('total_map_url');
        $adminUserInfo = $this->adminUserInfo ;
        $total_map_url .= '?admin_user_id='.$adminUserInfo['id'];
        $renderData['total_map_url'] = $total_map_url ;
        //$this->view->params['isEmpty'] = true;
        return $this->render('gis-map',$renderData) ;
    }

}
