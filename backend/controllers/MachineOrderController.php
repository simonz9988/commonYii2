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
use common\models\Ad;
use common\models\AdPosition;
use common\models\Areas;
use common\models\CashIn;
use common\models\CashOut;
use common\models\EmailCode;
use common\models\Member;
use common\models\MiningMachine;
use common\models\MiningMachineOrder;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';

/**
 * Cash
 */
class MachineOrderController extends BackendController
{
    public function actionList(){
        $searchArr = array();

        $page_num = $this->page_rows ;

        $machine_id = isset($_GET['machine_id']) ? $_GET['machine_id'] : '' ;

        if($machine_id){
            $params['where_arr']['machine_id'] = $machine_id;
        }
        $searchArr['machine_id'] = $machine_id ;

        $status = isset($_GET['status']) ? $_GET['status'] : '' ;

        if($status){
            $params['where_arr']['status'] = $status;
        }
        $searchArr['status'] = $status ;

        $mobile= $this->getParam('mobile');
        $member_obj = new Member();
        if($mobile){

            $user_ids = $member_obj->getUserIdsByMobile($mobile);
            if($user_ids){
                $params['in_where_arr']['user_id'] = $user_ids ;
            }else{
                $params['where_arr']['user_id'] = 0 ;
            }

        }
        $searchArr['mobile'] = $mobile ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new MiningMachineOrder() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());

        // 所有状态列表
        $status_list = $model->returnAllType();
        if($list){
            $all_machine_list = [];
            $mining_machine_obj = new MiningMachine();

            $mobile_list = [] ;
            $member_obj = new Member();
            foreach($list as $k=>$v){
                $machine_info = isset($all_machine_list[$v['machine_id']]) ?$all_machine_list[$v['machine_id']]:'';
                if(!$machine_info){
                    $machine_info = $mining_machine_obj->getInfoById($v['machine_id']);
                    $all_machine_list[$v['machine_id']] = $machine_info ;
                }

                $user_id = $v['user_id'];
                $mobile = isset($mobile_list[$user_id]) ? $mobile_list[$user_id] : '';
                if(!$mobile){
                    $mobile = $member_obj->getMobileByUserId($user_id);
                    $mobile_list[$user_id] = $mobile ;
                }
                $list[$k]['mobile'] = $mobile ;
                $list[$k]['machine_name'] = $machine_info ? $machine_info['title'] : '';
                $list[$k]['status_text'] = isset($status_list[$v['status']]) ? $status_list[$v['status']] : '';
            }
        }
        $renderData['list'] =$list;

        // 返回总记录记录数
        unset($params['page']) ;
        $params['fields'] = 'id';
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        $total = count($list);

        $page_data = $this->getPageData($total,[],'p',$page_num);
        $renderData['page_data'] = $page_data;

        // 获取所有机器列表
        $mining_machine_obj = new MiningMachine();
        $renderData['machine_list'] = $mining_machine_obj->getAll();
        $renderData['status_list'] =$status_list ;
        return $this->render('list',$renderData) ;
    }

    public function actionSetting(){}


}
