<?php
namespace backend\controllers;
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
use common\models\AdPosition;
use common\models\Areas;
use common\models\CashIn;
use common\models\CashOut;
use common\models\Country;
use common\models\EmailCode;
use common\models\SiteConfig;
use common\models\SunnyCompany;
use common\models\SunnyManager;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class SunnyManagerController extends BackendController
{
    public function actionIndex(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $country_id = isset($_GET['country_id']) ? $_GET['country_id'] : '' ;

        if($country_id){
            $params['like_arr']['country_id'] = $country_id;
        }
        $searchArr['country_id'] = $country_id ;

        $email = isset($_GET['email']) ? $_GET['email'] : '' ;

        if($email){
            $params['like_arr']['email'] = $email;
        }
        $searchArr['email'] = $email ;

        $is_download = isset($_GET['is_download']) ? $_GET['is_download'] : '' ;


        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new SunnyManager() ;
        $country_obj = new Country();

        $role_type_list = $model->returnRoleTypeList();

        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $company_obj = new SunnyCompany();
        if($list){
            foreach($list as $k=>$v){
                $company_info = $company_obj->getInfoById($v['company_id']);
                $list[$k]['country_name'] = $country_obj->getNameById($v['country_id']) ;
                $list[$k]['company_name'] = $company_info ? $company_info['unique_key']:'';
                $list[$k]['role_type'] = isset($role_type_list[$v['role_type']]) ? $role_type_list[$v['role_type']] :'';
            }
        }
        $renderData['list'] =$list;

        if($searchArr && $is_download){
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

        $renderData['country_list'] = $country_obj->getList();
        return $this->render('list',$renderData) ;
    }

    // 编辑广告位
    public function actionEdit(){
        $id= $this->getParam('id');
        $obj = new SunnyManager();
        $info = $obj->getInfoById($id) ;
        $renderData['info'] = $info ;

        $renderData['role_type_list'] = $obj->returnRoleTypeList();

        $company_obj = new SunnyCompany();
        $renderData['company_list'] = $company_obj->getAllAllowed();

        $country_obj = new Country();
        $renderData['country_list'] = $country_obj->getList();
        $this->loadResource('sunny-manager','actionEdit');
        return $this->render('edit',$renderData) ;
    }

    // 保存广告位
    public function actionSave(){
        $id = $this->postParam('id');
        $role_type = $this->postParam('role_type');
        $email = $this->postParam('email');
        $password = $this->postParam('password');


        $obj = new SunnyManager();
        if($id){
            $old_info = $obj->getInfoById($id);
            if($old_info['email'] !=$email){
                // 判断unique_key是否重复
                $exist_info = $obj->checkRepeatKey($email,$id);
                if($exist_info){
                    return $this->returnJson(['code'=>'200045','msg'=>getErrorDictMsg(200045)]);
                }

            }

            if($password){
                $update_data['password']=  md5($password);
            }
        }else{

            $password  = $password? $password:123456;
            $update_data['password']=  md5($password);
            $exist_info = $obj->checkRepeatKey($email,$id);
            if($exist_info){
                return $this->returnJson(['code'=>'200045','msg'=>getErrorDictMsg(200045)]);
            }
            $add_data['create_time']  = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';

        }

        $obj = new SunnyManager();
        $update_data['role_type'] = $role_type ;
        $update_data['company_id'] = $this->postParam('company_id') ;
        $update_data['country_id'] = $this->postParam('country_id') ;
        $update_data['init_zoom'] = $this->postParam('init_zoom') ;
        $update_data['note'] = $this->postParam('note') ;
        $update_data['username'] = $this->postParam('username') ;
        $update_data['init_longitude'] = $this->postParam('init_longitude') ;
        $update_data['init_latitude'] = $this->postParam('init_longitude') ;
        $update_data['email'] = $email ;
        $update_data['modify_time'] = date("Y-m-d H:i:s") ;
        if($id){
            $obj->baseUpdate($obj::tableName(),$update_data,'id=:id',[':id'=>$id]);
        }else{
            $obj->baseInsert($obj::tableName(),$update_data);
        }


        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

    // 删除广告位信息
    public  function actionDel(){
        $id = $this->getParam('id') ;
        $obj = new AdPosition();
        $add_data['is_deleted'] ='Y';
        $add_data['modify_time'] =date('Y-m-d H:i:s');
        $obj->baseUpdate($obj::tableName(),$add_data,'id=:id',[":id"=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }


    private function doExport($list){


        $export_data[] = [
            'ID',
            '邮箱',
            '客户编码',
            '国家',
            '角色',
            '最后登陆时间',

        ];

        if ($list) {

            foreach ($list as $v) {
                $data = [] ;
                $data[] = $v['id'];
                $data[] = $v['email'];
                $data[] = $v['company_name'];
                $data[] = $v['country_name'];
                $data[] = $v['role_type'];
                $data[] = $v['last_login_time'];


                $export_data[] = $data;

                unset($data);
            }
        }

        $export_obj = new ExportFile();
        $file_name = '所有客户数据-'.date('Y-m-d').'.csv';
        $export_obj->download($export_data,$file_name);

    }


}
