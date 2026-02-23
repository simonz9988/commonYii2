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
use common\models\SunnyDevice;
use common\models\SunnyDeviceBatteryParams;
use common\models\SunnyDeviceCategory;
use common\models\SunnyDeviceLoadTime;
use common\models\SunnyDevicePostitionDetail;
use common\models\SunnyDeviceStatusInfo;
use common\models\SunnyDeviceStatusRecord;
use common\models\SunnyDeviceStatusToday;
use common\models\SunnyDeviceStatusTotal;
use common\models\SunnyDeviceTotal;
use common\models\SunnyManager;
use common\models\SunnyProject;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * 项目列表
 */
class SunnyProjectController extends BackendController
{
    public function actionIndex(){

        $page_num = $this->page_rows ;
        $searchArr = $this->returnSearchArr();

        $like_key = ['name'] ;
        $where_key = [];
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


        $model = new SunnyProject() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $customer_obj = new SunnyManager();
        $company_obj = new SunnyCompany();
        $device_obj = new SunnyDevice();



        foreach($list as $k=>$v){
            $customer_info = $customer_obj->getInfoById($v['customer_id']);
            $company_info = $company_obj->getInfoById($v['company_id']);
            $list[$k]['customer_id'] = $customer_info ? $customer_info['email']:'';
            $list[$k]['company_id'] = $company_info ? $company_info['company_name']:'';
            $list[$k]['device_num'] = $device_obj->getTotalNumByProjectId($v['id']);
            $list[$k]['fault_num'] = $device_obj->getTotalFaultNumByProjectId($v['id']);
        }
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'page',$page_num);
        $renderData['page_data'] = $page_data;
        $this->loadResource('cms','actionAddAd');
        return $this->render('list',$renderData) ;
    }

    // 编辑项目
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new SunnyProject();
        $info = $obj->getInfoById($id) ;
        $customer_obj = new SunnyManager();
        $company_obj = new SunnyCompany();

        if($info){

            $customer_info = $customer_obj->getInfoById($info['customer_id']);
            $company_info = $company_obj->getInfoById($info['company_id']);

            $info['customer_id'] = $customer_info ? $customer_info['email']:'';
            $info['company_id'] = $company_info ? $company_info['company_name']:'';
        }


        $renderData['info'] = $info ;

        // 获取国家列表
        $country_obj = new Country();
        $renderData['country_list'] = $country_obj->getList();
        $this->loadResource('sunny-project','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存项目
    public function actionSave(){
        $id = $this->postParam('id');
        $update_data['status'] = $this->postParam('status');
        $update_data['name'] = $this->postParam('name');
        $update_data['time_zone'] = $this->postParam('time_zone');
        $update_data['country_code'] = $this->postParam('country_code');

        // 读取国家信息
        $country_obj = new Country();
        $country_info= $country_obj->getInfoById($update_data['country_code']);
        $update_data['country'] = $country_info?$country_info['name']:'';
        $update_data['province'] = $this->postParam('province');
        $update_data['city'] = $this->postParam('city');
        $update_data['area'] = $this->postParam('area');
        $update_data['address'] = $this->postParam('address');
        $update_data['longitude'] = $this->postParam('longitude');
        $update_data['init_zoom'] = $this->postParam('init_zoom');
        $update_data['init_hop'] = $this->postParam('init_hop');
        $update_data['init_his'] = $this->postParam('init_his');
        $update_data['latitude'] = $this->postParam('latitude');
        $update_data['modify_time'] = date('Y-m-d H:i:s');

        $obj = new SunnyProject();
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除
    public function actionDel(){
        $id= $this->getParam('id');
        $obj = new SunnyProject();
        $update_data['is_deleted']  =  'Y';
        $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$id]);
        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


}
