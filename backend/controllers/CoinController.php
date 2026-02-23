<?php
namespace backend\controllers;
use backend\models\Admin;
use backend\models\AdminApiKey;
use backend\models\AdminMenuCate;
use backend\models\AdminPrivilege;
use backend\models\AdminRole;
use backend\models\AdminTotalApiKey;
use backend\models\CoinAddressValue;
use backend\models\OkexLedger;
use backend\models\OkexTotalOrder;
use backend\models\PlatformTotalOrder;
use common\components\CoinBalance;
use common\components\PlatformTradeCommonV4;
use common\models\Coin;
use common\models\Member;
use common\models\SiteConfig;
use TencentCloud\Cws\V20180312\Models\Site;

/**
 * System
 */
class CoinController extends BackendController
{
    public function actionList(){

        $site_config = new SiteConfig() ;
        $bit_all_platform = $site_config->getByKey('bit_all_platform','json');
        $earn_type_list = $site_config->getByKey('coin_earn_type_list','json');
        $renderData['bit_all_platform'] = $bit_all_platform ;
        $renderData['earn_type_list'] = $earn_type_list ;


        $searchArr = array();

        $page_num = $this->page_rows ;

        $platform = isset($_GET['platform']) ? $_GET['platform'] : '' ;

        if($platform){
            $params['where_arr']['platform'] = $platform;
        }
        $searchArr['platform'] = $platform ;

        $earn_type = isset($_GET['earn_type']) ? $_GET['earn_type'] : '' ;

        if($earn_type){
            $params['where_arr']['earn_type'] = $earn_type;
        }
        $searchArr['earn_type'] = $earn_type ;

        $renderData['searchArr'] =$searchArr ;
        $curr_page =  $this->getParam('page');
        $curr_page = $curr_page ? $curr_page : 1;

        $params['where_arr']['is_deleted'] = 'N';
        $params['page'] = array('curr_page'=>$curr_page,'page_num'=> $page_num);
        $params['order_by'] = ' id desc ';


        $model = new Coin() ;
        $list = $model->findByWhere($model::tableName(),$params, $model::getDb());
        if($list){
            foreach($list as $k=>$v){
                $list[$k]['earn_type_name'] = isset($earn_type_list[$v['earn_type']])?$earn_type_list[$v['earn_type']]:'';
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

    // 编辑内容
    public function actionEdit(){
        $id = $this->getParam('id');
        $api_key_obj = new Coin() ;
        $info = $api_key_obj->getInfoById($id);

        $select_legal_coin_list = $info&& $info['legal_coin_list'] ? explode(',',$info['legal_coin_list']) : [];
        $render_data['select_legal_coin_list'] = $select_legal_coin_list ;
        $render_data['info'] = $info ;
        $this->loadResource('coin','actionEdit') ;

        // 查询所有平台
        $site_config = new SiteConfig();
        $bit_all_platform = $site_config->getByKey('bit_all_platform','json');
        $earn_type_list = $site_config->getByKey('coin_earn_type_list','json');
        $legal_coin_list = $site_config->getByKey('legal_coin_list','json');
        $render_data['bit_all_platform'] = $bit_all_platform ;
        $render_data['earn_type_list'] = $earn_type_list ;
        $render_data['legal_coin_list'] = $legal_coin_list ;
        return $this->render('edit',$render_data);
    }

    public function actionSave(){

        $id = $this->postParam('id');
        $name = $this->postParam('name');
        $alias = $this->postParam('alias');
        $unique_key = $this->postParam('unique_key');
        $platform = $this->postParam('platform');
        $earn_type = $this->postParam('earn_type');
        $sort = $this->postParam('sort');
        $earn_high_percent = $this->postParam('earn_high_percent');
        $earn_low_percent = $this->postParam('earn_low_percent');
        $max_block = $this->postParam('max_block');
        $min_qty = $this->postParam('min_qty');
        $usdt_buying_points = $this->postParam('usdt_buying_points');
        $usdt_max_buying_points = $this->postParam('usdt_max_buying_points');
        $btc_buying_points = $this->postParam('btc_buying_points');
        $btc_max_buying_points = $this->postParam('btc_max_buying_points');
        $eth_buying_points = $this->postParam('eth_buying_points');
        $eth_max_buying_points = $this->postParam('eth_max_buying_points');
        $usdt_depth = $this->postParam('usdt_depth');
        $btc_depth = $this->postParam('btc_depth');
        $eth_depth = $this->postParam('eth_depth');
        $is_private = $this->postParam('is_private');
        $start_price = $this->postParam('start_price');
        $legal_coin_list = $_POST['legal_coin_list'];
        $legal_coin_list  = $legal_coin_list?implode(',',$legal_coin_list):'';
        $modify_time = date('Y-m-d H:i:s');
        $add_data = compact('start_price','is_private','usdt_max_buying_points','btc_max_buying_points','eth_max_buying_points','usdt_depth','btc_depth','eth_depth','usdt_buying_points','btc_buying_points','eth_buying_points','min_qty','max_block','earn_high_percent','earn_low_percent','sort','name','alias','unique_key','platform','modify_time','earn_type','legal_coin_list');

        $model = new Coin();
        if($id){
            $model->baseUpdate($model::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }else{
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $model->baseInsert($model::tableName(),$add_data);
        }

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);

    }

    // 执行删除操作
    public function actionDel(){
        $id = $this->getParam('id');
        $model = new Coin();
        $update_data['is_deleted'] = 'Y';
        $update_data['modify_time'] = date('Y-m-d H:i:s');
        $model->baseUpdate($model::tableName(),$update_data,'id=:id',[':id'=>$id]);

        return $this->returnJson(['code'=>1,'msg'=>getErrorDictMsg(1)]);
    }

}
