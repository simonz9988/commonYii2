<?php
namespace backend\controllers;
use backend\components\LogOperate;
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
use common\models\Language;
use common\models\SiteConfig;
use common\models\SunnyCompany;
use common\models\SunnyDevice;
use common\models\SunnyDeviceCategory;
use common\models\SunnyDeviceCategoryDetail;
use common\models\SunnyDevicePostitionDetail;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceSyncTask;
use common\models\SunnyDeviceTotal;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class SunnyDeviceCategoryController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $name = isset($_GET['name']) ? $_GET['name'] : '' ;

        if($name){
            $params['like_arr']['name'] = $name;
        }
        $searchArr['name'] = $name ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new SunnyDeviceCategory() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $record_obj = new SunnyDeviceStatusRecord();

        if($list){
            foreach ($list as $k=>$v){
                $parent_id = $v['parent_id'];
                $list[$k]['parent_name'] = $parent_id ?$model->getNameById($parent_id):'-';
                $list[$k]['battery_type'] = $record_obj->getBatteryType($v['battery_type']);
                $list[$k]['auto_power_set'] = $record_obj->getAutoPowerSet($v['auto_power_set']);
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
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new SunnyDeviceCategory();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;
        $renderData['parent_list'] = $obj->getListByParentId(0,'*',$id);
        $record_obj = new SunnyDeviceStatusRecord();
        $renderData['auto_power_set_list'] = $record_obj->returnAutoPowerSetList();

        $renderData['battery_type_list'] = $record_obj->returnBatteryTypeList();
        $renderData['bat_rate_volt_list'] = $record_obj->returnBatRateVoltList();

        $renderData['language_item_list'] = $obj->getLanguageItemList($info);

        $this->loadResource('sunny-device-category','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $unique_key = $this->postParam('unique_key');


        $obj = new SunnyDeviceCategory();
        if($id){
            $old_info = $obj->getInfoById($id);
            if($old_info['unique_key'] !=$unique_key){
                // 判断unique_key是否重复
                $exist_info = $obj->checkRepeatKey($unique_key,$id);
                if($exist_info){
                    return $this->returnJson(['code'=>'200046','msg'=>getErrorDictMsg(200046)]);
                }

            }
        }else{
            $exist_info = $obj->checkRepeatKey($unique_key,$id);
            if($exist_info){
                return $this->returnJson(['code'=>'200046','msg'=>getErrorDictMsg(200046)]);
            }

        }

        $add_data['name'] = $this->postParam('name');
        $add_data['light_level'] = $this->postParam('light_level');
        $add_data['parent_id'] = $this->postParam('parent_id');
        $add_data['charge_port_num'] = $this->postParam('charge_port_num');
        $add_data['unique_key'] = $this->postParam('unique_key');

        $add_data['note'] = $this->postParam('note');
        $add_data['status'] = $this->postParam('status');
        $add_data['is_deleted'] = 'N';
        $add_data['modify_time'] = date('Y-m-d H:i:s');

        $add_data['battery_type'] = $this->postParam('battery_type');
        $add_data['battery_rate_volt'] = $this->postParam('battery_rate_volt');
        $add_data['led_current_set'] = $this->postParam('led_current_set');
        $add_data['auto_power_set'] = $this->postParam('auto_power_set');

        $add_data['controller_model'] = $this->postParam('controller_model');
        $add_data['battery_vol'] = $this->postParam('battery_vol');
        $add_data['battery_model'] = $this->postParam('battery_model');
        $add_data['panel_power'] = $this->postParam('panel_power');
        $add_data['panel_model'] = $this->postParam('panel_model');
        $add_data['light_power'] = $this->postParam('light_power');
        $add_data['light_model'] = $this->postParam('light_model');

        if($id){
            $old_content = $obj->getInfoById($id) ;
            $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $id = $obj->baseInsert($obj::tableName(),$add_data);
        }


        $new_content = $obj->getInfoById($id) ;
        $log_data = array(
            'class_name' => __CLASS__,
            'function_name' => __FUNCTION__,
            'action' => 'EDIT_CATEGORY',
            'redundancy_id' => $id,
            'old_content' => $old_content,
            'new_content' => $new_content
        );

        // 日志操作
        $log_operate_obj = new \common\components\LogOperate();
        $log_operate_obj->insert( $log_data);



        // 删除原有旧的关系
        /*
        $detail_obj = new SunnyDeviceCategoryDetail();
        $detail_obj->delDataByCategoryId($id);

        $language_item_list = isset($_POST['language_item_list']) ?$_POST['language_item_list']:[];
        if($language_item_list){
            $now = date('Y-m-d H:i:s');
            foreach($language_item_list as $language_id=>$v){
                $lang_add_data['category_id'] = $id ;
                $lang_add_data['language_id'] = $language_id ;
                $lang_add_data['sop_url'] = $v['sop_url'] ;
                $lang_add_data['file_name'] = $v['file_name'] ;
                $lang_add_data['is_deleted'] = 'N' ;
                $lang_add_data['create_time'] = $now;
                $lang_add_data['modify_time'] = $now;

                $detail_obj->baseInsert($detail_obj::tableName(),$lang_add_data);
            }
        }*/

        $show_name_list = isset($_POST['show_name_list']) ?$_POST['show_name_list']:[];
        $detail_obj = new SunnyDeviceCategoryDetail();
        $detail_obj->saveShowNameList($show_name_list,$id);

        // 更新设备关联信息
        $device_update_data['battery_type'] = $this->postParam('battery_type');
        $device_update_data['battery_rate_volt'] = $this->postParam('battery_rate_volt');
        $device_update_data['led_current_set'] = $this->postParam('led_current_set');
        $device_update_data['auto_power_set'] = $this->postParam('auto_power_set');
        $device_update_data['modify_time'] = date('Y-m-d H:i:s');
        $device_obj = new SunnyDevice();
        $device_cond = 'category_id=:category_id AND is_edit=:is_edit AND is_deleted=:is_deleted';
        $device_args = [':category_id'=>$id,':is_edit'=>"N",':is_deleted'=>'N'];
        $device_obj->baseUpdate($device_obj::tableName(),$device_update_data,$device_cond,$device_args);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new SunnyDeviceCategory();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    public function actionBatchAdd(){

        $category_id = $this->postParam('category_id');
        $total = $this->postParam('total','int');
        if($total < 0){
            return $this->returnJson(['code'=>200047,'msg'=>getErrorDictMsg(200047)]);
        }

        if($total > 99999){

            return $this->returnJson(['code'=>200048,'msg'=>getErrorDictMsg(200048)]);
        }


        $category_obj = new SunnyDeviceCategory();
        $category_info = $category_obj->getInfoById($category_id);

        $code = 'HN';
        $code .= date("Ymd");

        // 查询最新一条信息
        $device_obj = new SunnyDevice();
        $lasted_info = $device_obj->getLastedInfo();
        if($lasted_info) {
            if(strstr($lasted_info['qr_code'],$code)){
                $start_num = str_replace($code,"",$lasted_info['qr_code']);
                $start_num = intval($start_num);
                $start_num = $start_num + 1;
            }else{
                $start_num = 1 ;
            }

        }else{
            $start_num = 1 ;
        }

        // 假如总数超99999 则错误
        $left = 99999-$start_num -1 ;

        if($total > $left){
            return $this->returnJson(['code'=>200050,'msg'=>"输入数目上限为".$left]);
        }


        for($i= $start_num ; $i<=$total;$i++){
            $qr_code = $code.str_pad($i,5 , "0", STR_PAD_LEFT);

            $add_data['category_id'] = $category_id ;
            $add_data['parent_id'] = $category_info['parent_id'] ;
            $add_data['level'] = $category_info['parent_id'] > 0 ? 1: 0 ;
            $add_data['customer_id'] =  0 ;
            $add_data['company_id'] =  0 ;
            $add_data['light_level'] =  $category_info['light_level'] ;
            $add_data['qr_code'] =  $qr_code ;
            $add_data['port_num'] =  $category_info['charge_port_num'] ;
            $add_data['device_no'] =  $category_info['name'] ;
            $add_data['status'] =  'ENABLED' ;
            $add_data['is_deleted'] =  'N' ;
            $add_data['create_time'] =  date('Y-m-d H:i:s') ;
            $add_data['modify_time'] =  date('Y-m-d H:i:s') ;

            $add_data['battery_type'] = $category_info['battery_type'] ;
            $add_data['battery_rate_volt'] = $category_info['battery_rate_volt'] ;
            $add_data['led_current_set'] = $category_info['led_current_set'] ;
            $add_data['auto_power_set'] = $category_info['auto_power_set'] ;


            $device_obj->baseInsert($device_obj::tableName(),$add_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    /**
     * SOP列表
     */
    public function actionSopList(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $file_name = isset($_GET['file_name']) ? $_GET['file_name'] : '' ;

        if($file_name){
            $params['like_arr']['file_name'] = $file_name;
        }
        $searchArr['file_name'] = $file_name ;

        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '' ;

        if($category_id){
            $params['where_arr']['category_id'] = $category_id;
        }
        $searchArr['category_id'] = $category_id ;

        $parent_id = isset($_GET['parent_id']) ? $_GET['parent_id'] : '' ;

        if($parent_id){
            $params['where_arr']['parent_category_id'] = $parent_id;
        }
        $searchArr['parent_id'] = $parent_id ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new SunnyDeviceCategoryDetail() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $category_list = [] ;
        $category_obj = new SunnyDeviceCategory();
        $language_obj = new Language() ;
        $language_list  = [] ;

        if($list){
            foreach ($list as $k=>$v){
                $category_id = $v['category_id'];
                $category_info = isset($category_list[$category_id])?$category_list[$category_id]:[];
                if(!$category_info){
                    $category_info = $category_obj->getInfoById($category_id);
                    $category_list[$category_id] = $category_info ;
                }

                $list[$k]['category_name'] = $category_info ? $category_info['name']:'';

                $parent_category_id = $v['parent_category_id'];
                $parent_category_info = isset($category_list[$category_id])?$category_list[$category_id]:[];
                if(!$parent_category_info){
                    $parent_category_info = $category_obj->getInfoById($parent_category_id);
                    $category_list[$parent_category_id] = $parent_category_info ;
                }
                $list[$k]['parent_category_name'] = $parent_category_info ? $parent_category_info['name']:'';

                $language_id = $v['language_id'];
                $language_info = isset($language_list[$language_id])?$language_list[$language_id]:[];
                if(!$language_info){
                    $language_info = $language_obj->getInfoById($language_id);
                    $language_list[$language_id] = $language_info;
                }
                $list[$k]['language_name'] = $language_info['name'];

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

        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;

        return $this->render('sop-list',$renderData) ;
    }

    // sop编辑页
    public function actionSopEdit(){

        $language_obj = new Language();
        $language_list = $language_obj->getAll();

        $renderData['language_list'] = $language_list ;

        $category_obj = new SunnyDeviceCategory();
        $parent_category_list = $category_obj->getListByParentId(0);
        $renderData['parent_category_list'] = $parent_category_list ;

        $first_category_id = isset($parent_category_list[0]['id']) ? $parent_category_list[0]['id'] :0  ;
        $category_list = $category_obj->getListByParentId($first_category_id);
        $renderData['category_list'] = $category_list ;
        $renderData['info'] = [] ;
        $this->loadResource('sunny-device-category','actionSopEdit');
        return $this->render('sop-edit',$renderData) ;
    }

    /**
     * 获取分类子集列表
     */
    public function actionAjaxGetSonList(){
        $category_id = $this->postParam('category_id') ;
        $category_obj = new SunnyDeviceCategory();
        $son_list =$category_obj->getListByParentId($category_id);
        $str = '';
        if($son_list){
            foreach($son_list as $v){
                $str .='<option value="'.$v['id'].'">'.$v['name'].'</option>';
            }
        }

        return $this->returnJson(['code'=>'1','data'=>$str]) ;
    }

    // SOP 保存
    public function actionSopSave(){

        $category_id = $this->postParam('category_id') ;
        $language_id = $this->postParam('language_id') ;
        $file_name = $this->postParam('file_name') ;
        $sop_url = $this->postParam('sop_url') ;

        $obj = new SunnyDeviceCategoryDetail();
        $obj->saveSop($category_id,$language_id,$sop_url,$file_name);
        responseJson(['code'=>1]);exit;
    }

    // 设置集控列表
    public function actionSettingList(){

        $searchArr = array();

        $page_num = $this->page_rows ;

        $category_id = isset($_GET['category_id']) ? $_GET['category_id'] : '' ;

        if($category_id){
            $params['where_arr']['category_id'] = $category_id;
        }
        $searchArr['category_id'] = $category_id ;

        $parent_id = isset($_GET['parent_id']) ? $_GET['parent_id'] : '' ;

        if($parent_id){
            $params['where_arr']['parent_id'] = $parent_id;
        }
        $searchArr['parent_id'] = $parent_id ;


        $qr_code = isset($_GET['qr_code']) ? $_GET['qr_code'] : '' ;

        if($qr_code){
            $params['like_arr']['qr_code'] = $qr_code;
        }
        $searchArr['qr_code'] = $qr_code ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        // 获取所有二级分类
        $params['where_arr']['is_deleted'] = 'N';
        $params['where_arr']['is_bind'] = 'Y';
        $params['not_where_arr']['parent_id'] = 0;
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

        $model = new SunnyDevice() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        $category_list = [] ;
        $category_obj = new SunnyDeviceCategory();
        if($list){
            foreach ($list as $k=>$v){


                $parent_category_id = $v['parent_id'];
                $parent_category_info = isset($category_list[$parent_category_id])?$category_list[$parent_category_id]:[];
                if(!$parent_category_info){
                    $parent_category_info = $category_obj->getInfoById($parent_category_id);
                    $category_list[$parent_category_id] = $parent_category_info ;
                }
                $list[$k]['parent_category_name'] = $parent_category_info ? $parent_category_info['name']:'';

                $category_id = $v['category_id'];
                $category_info = isset($category_list[$category_id])?$category_list[$category_id]:[];
                if(!$category_info){
                    $category_info = $category_obj->getInfoById($category_id);
                    $category_list[$category_id] = $category_info ;
                }
                $list[$k]['category_name'] = $category_info ? $category_info['name']:'';

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

        return $this->render('setting-list',$renderData) ;
    }

    // 保存设置信息
    public function actionSettingSave(){
        $type = $this->postParam('type') ;
        $value = $this->postParam('content') ;
        $ids = $this->postParam('ids');
        $minute = $this->postParam('minute');

        $obj = new SunnyDevice() ;
        $task_obj = new SunnyDeviceSyncTask();

        if($type=='light'){

            $light_level = floatval($value);
            if($light_level < 0 || $light_level > 100){
                return $this->returnJson(['code'=>200049,'msg'=>getErrorDictMsg('200049')]);
            }
            // 设置亮度
            $id_list = explode('_',$ids);
            if($id_list){
                foreach($id_list as $v){
                    if($v){
                        // 更新亮度信息
                        $update_data['brightness'] = $light_level ;
                        $update_data['modify_time']=  date('Y-m-d H:i:s');
                        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$v]);

                    }
                }
            }
        }else{
            $switch_status  = $value=="ON"?"Y":"N" ;
            if($switch_status =='Y'){
                if($minute < 0 || $minute  >15*60){
                    return $this->returnJson(['code'=>200052,'msg'=>getErrorDictMsg('200052')]);
                }
            }
            // 设置亮度
            $id_list = explode('_',$ids);
            if($id_list){
                foreach($id_list as $v){
                    if($v){
                        // 设置开关状态
                        $update_data['switch_status'] = $switch_status ;
                        $update_data['minute'] = $minute ;
                        $update_data['modify_time']=  date('Y-m-d H:i:s');
                        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$v]);

                    }
                }
            }
        }

        // 添加任务信息
        $id_list = explode('_',$ids);
        if($id_list) {
            foreach ($id_list as $v) {
                if ($v) {
                    $device_info = $obj->getInfoById($v);
                    $task_obj->addTask($device_info);
                }
            }
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg('1')]);
    }

    /**
     * 设备信息报表
     */
    public function actionInfoReport(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $searchArr = $this->returnSearchArr();
        $like_key = ['country','province','city','district'] ;
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

        $model = new SunnyDevicePostitionDetail() ;
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
        return $this->render('info-report',$renderData) ;
    }

    // 设备趋势
    public function actionTrade(){


        $searchArr = $this->returnSearchArr();
        $renderData['searchArr']  = $searchArr ;

        $like_key = ['country','province','city','district'] ;
        $where_key = ['category_id','parent_id','time_type'];

        $params = $this->returnParams($searchArr,$where_key,$like_key);



        $adminUserInfo = $this->adminUserInfo;
        $redis_key = "Admin:select_device_id:".$adminUserInfo['id'];
        $redis_obj = new MyRedis();
        $redis_info = $redis_obj->get($redis_key);
        if($redis_info){
            $select_ids_arr = unserialize($redis_info);

            //删除缓存
            if($redis_info){
                $redis_obj->del($redis_key);
            }
        }else{
            $select_ids_arr = isset($_GET['select_ids_arr']) ? $_GET['select_ids_arr'] : '' ;
            $select_ids_arr = explode(',',$select_ids_arr);
        }

        $renderData['select_ids_arr'] = implode(",",$select_ids_arr);

        $filter_fields_list = [
            'battery_volume' =>'电池电量',
            'battery_voltage' =>'电池电压',
            'battery_charging_current' =>'电池充电电流',
            'ambient_temperature' =>'环境温度',
            'battery_panel_charging_voltage' =>'电池面板充电电压',
            'charging_current' =>'充电电流',
            'charging_power' =>'充电电压',
            'brightness' =>'亮度',
            'cumulative_charge' =>'累计充电电量(KW时)',
        ];
        $renderData['filter_fields_list'] = $filter_fields_list ;

        $start_time = date('Y-m-d 00:00:00' ,time()-7*86400) ;
        if(isset($searchArr['start_time'])){
            $start_time = $searchArr['start_time'] ;
        }
        $select_start_time = $start_time ;

        $end_time = date("Y-m-d H:i:s");
        if(isset($searchArr['end_time']) && $searchArr['end_time']){
            $end_time = $searchArr['end_time'] ;
        }
        $select_end_time = $end_time ;

        if($start_time > $end_time && $end_time){
            $temp_start = $start_time ;
            $temp_end = $end_time ;

            $start_time = $temp_end ;
            $end_time = $temp_start;
        }


        if(isset($searchArr['time_type']) && $searchArr['time_type']){
            $time_type = $searchArr['time_type'];
        }else{
            $time_type = "HALF_HOUR";
        }

        if($time_type =='HALF_HOUR'){
            $start_time = date("Y-m-d 00:00:00",strtotime($start_time));
            $end_time = date("Y-m-d H:i:s",strtotime($end_time));
            $chushu = 1800 ;
            $time_list_mark = "Y-m-d H:i:00";
        }else if($time_type =="HOUR"){
            $start_time = date("Y-m-d 00:00:00",strtotime($start_time));
            $end_time = date("Y-m-d H:i:s",strtotime($end_time));
            $chushu = 3600 ;
            $time_list_mark = "Y-m-d H:i:00";
        }else if($time_type =="DAY"){
            $start_time = date("Y-m-d 00:00:00",strtotime($start_time));
            $end_time = date("Y-m-d H:i:s",strtotime($end_time));
            $chushu = 86400 ;
            $time_list_mark = "Y-m-d";
        }else if($time_type =="MONTH"){
            $start_time = date("Y-m-01 00:00:00",strtotime($start_time));
            $end_time = date("Y-m-d H:i:s",strtotime($end_time));
            $chushu = 86400 *30;
            $time_list_mark = "Y-m";
        }else if($time_type =="YEAR"){
            $start_time = date("Y-01-01 00:00:00",strtotime($start_time));
            $end_time = date("Y-m-d H:i:s",strtotime($end_time));
            $chushu = 86400 *365;
            $time_list_mark = "Y";
        }


        $params['group_by'] = 'timestamp' ;

        $ext = (strtotime($end_time) - strtotime($start_time))/$chushu ;

        $total_obj = new SunnyDeviceTotal();
        // 筛选字段
        $time_list = [] ;
        $data_list = [] ;
        $filter_fields = isset($searchArr['filter_fields']) ?  $searchArr['filter_fields'] : 0 ;

        $device_list = [] ;
        $device_obj = new SunnyDevice();


        // 查询指定数据
        if(!$filter_fields || !$select_ids_arr || ($select_ids_arr && !$select_ids_arr[0]) || !$select_start_time ){

            $data_list[] = 0 ;
        }else{
            for($i=0;$i<=$ext;$i++){

                $timestamp = strtotime($start_time) + $i*$chushu ;


                $time_list[] = date($time_list_mark ,$timestamp) ;

                /**
                 * 'battery_volume' =>'电池电量',
                'battery_voltage' =>'电池电压',
                'battery_charging_current' =>'电池充电电流',
                'ambient_temperature' =>'环境温度',
                'battery_panel_charging_voltage' =>'电池面板充电电压',
                'charging_current' =>'充电电流',
                'charging_power' =>'充电电压',
                'brightness' =>'亮度',
                'cumulative_charge' =>'累计充电电量(KW时)',
                 */
                foreach($select_ids_arr as $k=>$device_id){
                    $params['where_arr']['timestamp'] = $timestamp ;
                    $params['where_arr']['device_id'] = $device_id ;

                    if(in_array($filter_fields,['cumulative_charge'])){
                        $params['return_field'] = " sum(".$filter_fields.") as total";
                    }else{
                        $params['return_field'] = " avg(".$filter_fields.") as total";
                    }

                    $params['return_type'] = 'row';

                    $info = $total_obj->findByWhere($total_obj::tableName(),$params,$total_obj::getDb());
                    $data_list[$k]['data_list'][] = $info && !is_null($info['total']) ? $info['total'] : 0;

                    $device_info = isset($device_list[$device_id]) ? $device_list[$device_id] : [] ;
                    if(!$device_info){
                        $device_info = $device_obj->getInfoById($device_id);
                        $device_list[$device_id] = $device_info ;
                    }
                    $data_list[$k]['label'] = $device_info ? $device_info['qr_code'] :$device_id ;
                }


            }


        }


        $kline_title = isset($filter_fields_list[$filter_fields]) ? $filter_fields_list[$filter_fields] : "汇总";
        $renderData['kline_title'] = $kline_title ;

        $datasets = [] ;
        if($data_list){
            foreach($data_list as $v){
                $color1 = mt_rand(1,255);
                $color2 = mt_rand(100,255);
                $color3 = mt_rand(1,255);

                $datasets[] = [
                    'label' => $v['label'],
                    'backgroundColor' => "rgba(152,137,193,0.3)", // 背景色
                    'borderColor' => "rgb(".$color1.",".$color2.",".$color3.")", // 线
                    'data' => $v['data_list']
                ];
            }
        }

        $format_line_data = [
            'labels' => json_encode($time_list,JSON_UNESCAPED_UNICODE),
            'datasets' => json_encode($datasets,JSON_UNESCAPED_UNICODE),
            'title' => '价格曲线'
        ];

        $renderData['format_line_data'] = $format_line_data ;

        $this->loadResource('cms','actionAddAd');

        $category_obj = new SunnyDeviceCategory();
        $renderData['filter_category_list'] = $category_obj->getFilterList() ;

        //获取所有公司列表
        $company_obj = new SunnyCompany();
        $renderData['company_list'] = $company_obj->getAllAllowed();

        $renderData['time_type_list'] = [
            'HALF_HOUR'=>'半小时',
            'HOUR'=>'小时',
            'DAY'=>'天',
            'MONTH'=>'月',
            'YEAR'=>'年',
        ];
        return $this->render('trade',$renderData) ;
    }

}
