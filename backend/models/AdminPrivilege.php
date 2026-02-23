<?php

namespace backend\models;

use backend\components\Privilege;
use Yii;

/**
 * This is the model class for table "sea_admin_privilege".
 *
 * @property string $id
 * @property string $creater 创建人员
 * @property string $create_time 创建时间
 * @property string $modifier 修改人员
 * @property string $modify_time 修改时间
 * @property string $category 所属类别(menu)
 * @property string $controller 控制器名称
 * @property string $function 方法名
 * @property string $name 中文释义
 * @property string $key 字段唯一识别字段(controller_function)
 * @property int $parent_id 父类ID(为0的则为顶级分类)
 * @property int $menu_cate_id 菜单分类ID
 * @property string $menu_cate_name 菜单分类名称
 * @property string $menu_cate_unique_key 菜单分类唯一值
 * @property int $level 级别(顶级为0,依次递推)
 * @property int $sort 排序(值越小，排序越靠前)
 * @property int $is_open 是否有效(1-有效 0-无效 用作删除的时候使用)
 */
class AdminPrivilege extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_admin_privilege';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['category', 'controller', 'function', 'name', 'key'], 'required'],
            [['parent_id', 'menu_cate_id', 'level', 'sort', 'is_open'], 'integer'],
            [['creater', 'modifier', 'menu_cate_name', 'menu_cate_unique_key'], 'string', 'max' => 50],
            [['category', 'controller'], 'string', 'max' => 200],
            [['function'], 'string', 'max' => 1000],
            [['name', 'key'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'creater' => 'Creater',
            'create_time' => 'Create Time',
            'modifier' => 'Modifier',
            'modify_time' => 'Modify Time',
            'category' => 'Category',
            'controller' => 'Controller',
            'function' => 'Function',
            'name' => 'Name',
            'key' => 'Key',
            'parent_id' => 'Parent ID',
            'menu_cate_id' => 'Menu Cate ID',
            'menu_cate_name' => 'Menu Cate Name',
            'menu_cate_unique_key' => 'Menu Cate Unique Key',
            'level' => 'Level',
            'sort' => 'Sort',
            'is_open' => 'Is Open',
        ];
    }

    /**
     * 判断当前权限是否存在
     * @param $controller
     * @param $function
     * @return mixed
     */
    public function checkExists($controller,$function){

        $params['cond'] = 'controller=:controller AND function=:function AND is_open=:is_open';
        $params['args'] = [':controller'=>$controller,':function'=>$function,':is_open'=>1];
        $params['fields'] = 'id';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 通过菜单信息
     * @param  integer $level 等级 等级为0代表顶级分类
     * @return [type]         [description]
     */
    public function getMenuByLevel($level=0)
    {

        $params['cond'] = 'level=:level AND is_open=:is_open';
        $params['args'] = [':level'=>$level,':is_open'=>1];
        $params['orderby'] = '  sort asc ';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 获取所有的子菜单
     * @param  int $parent_id  父菜单对应的ID
     * @return array           返回所有的子菜单
     */
    public function getMenuByParentId($parent_id){

        if(!$parent_id){
            return [] ;
        }
        $params['cond'] = 'parent_id=:parent_id AND is_open=:is_open';
        $params['args'] = [':parent_id'=>$parent_id,':is_open'=>1];
        $params['orderby'] = '  sort asc ';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;

    }

     /**
     * 根据管理员的权限获取所有的菜单
     * @param  array $adminUser 后台管理人员的相关信息(session读取)
     * @return array 权限列表
     * Note:目前菜单列表只有两集
     */
    public function getAllMenuByUser(){

        $adminUser = $adminUserInfo = Yii::$app->session->get('login_user');
        $return_arr = array() ;

        #TODO 读取方式放入缓存 未处理
        if($adminUser['username']=='admin' ){
            //admin 获取全部
            $level0_menu = $this->getMenuByLevel(0) ;
            $return_arr = $level0_menu ;
            foreach($return_arr as $k=>$v){
                //获取其所有的儿子节点
                $return_arr[$k]['all_sun'] = $this->getMenuByParentId($v['id']);
            }


        }else{

            $role_id = $adminUser['role_id'];

            //后台人员所包含权限ID
            $pri_id_arr = array() ;
            if($role_id){
                $role_privilege_model = new AdminRolePrivilege();
                $user_privileges = $role_privilege_model->getListByRoleId($role_id);
                foreach($user_privileges as $v){
                    $pri_id_arr[] = $v['privilege_id'];
                }

            }
            $level0_menu = $this->getMenuByLevel(0) ;
            foreach($level0_menu as $k=>$v){
                if(in_array($v['id'], $pri_id_arr) ){
                    $return_arr[$k] = $v ;
                    $sun_arr = $this->getMenuByParentId($v['id']);
                    foreach($sun_arr as $kk=>$vv){
                        if(!in_array($vv['id'],$pri_id_arr)){
                            unset($sun_arr[$kk]);
                        }
                    }
                    $return_arr[$k]['all_sun'] = $sun_arr ;
                }
            }


        }
        return $return_arr ;
    }

    /**
     * 获取指定控制器和方法名的权限信息
     * @param $controller_name
     * @param $function_name
     * @return mixed
     */
    public function getRow($controller_name,$function_name){

        $params['cond'] = 'controller=:controller AND function=:function AND is_open=:is_open';
        $params['args'] = [':controller'=>$controller_name,':function'=>$function_name,':is_open'=>1];
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    public function getRowInfoById($id,$return_field='*'){
        $params['where_arr']['id'] = $id ;
        $params['return_type'] = 'row';
        $params['return_field'] = $return_field ;
        $rst = $this->findByWhere($this->tableName(),$params);
        return $rst ;
    }



    /**
     * 获取一级和二级菜单的key值信息
     * @param $info
     */
    public function getLevelInfo($info){
        $level0Key  = '';
        $level0MenuUniqueKey  = '';
        $level1Key  = '';
        $level0Name  = '';
        $level1Name  = '';

        if($info){
            $level = $info['level'];
            if($level==0){
                $level0Key = $info['controller'].'_'.$info['function'];
                $level0MenuUniqueKey = $info['menu_cate_unique_key'];
                $level0Name = $info['name'];
            }

            if($level ==1){
                $parent_id = $info['parent_id'];
                $parent_info = $this->getRowInfoById($parent_id);
                $level0Key = $parent_info['controller'].'_'.$parent_info['function'];
                $level0MenuUniqueKey = $parent_info['menu_cate_unique_key'];
                $level0Name = $parent_info['name'];
                $level1Key = $info['controller'].'_'.$info['function'];
                $level1Name = $info['name'];
            }

            if($level ==2){
                $parent_id = $info['parent_id'];
                $parent_info = $this->getRowInfoById($parent_id);
                $parent_parent_info = $this->getRowInfoById($parent_info['parent_id']);
                $level0Key = $parent_parent_info['controller'].'_'.$parent_parent_info['function'];
                $level0MenuUniqueKey = $parent_parent_info['menu_cate_unique_key'];
                $level0Name = $parent_parent_info['name'];
                $level1Key = $parent_info['controller'].'_'.$parent_info['function'];
                $level1Name = $info['name'];
            }
        }
        return compact('level0Key','level1Key','level0Name','level1Name','level0MenuUniqueKey');
    }

    /**
     * 获取节点树相关的数 Ztree   js插件需要
     * @return mixxed
     */
    public function allNode($tag='json'){

        $menus_and_pri = $this->allPrivileges();
        $return_arr = array() ;

        foreach($menus_and_pri as $v){
            $tmp_arr = array() ;
            $tmp_arr['id'] = $v['id'] ;
            $tmp_arr['pId'] = $v['parent_id'] ;
            $tmp_arr['name'] = $v['name'] ;
            $return_arr[] = $tmp_arr ;
            if(isset($v['son']) && $v['son']){
                foreach($v['son'] as $s_v){
                    $return_arr[] = array(
                        'id' => $s_v['id'] ,
                        'pId' => $s_v['parent_id'] ,
                        'name' => $s_v['name'] ,
                    );
                    if(isset($s_v['grandson'] ) && $s_v['grandson'] ){
                        foreach($s_v['grandson'] as $g_v){
                            $return_arr[] = array(
                                'id' => $g_v['id'] ,
                                'pId' => $g_v['parent_id'] ,
                                'name' => $g_v['name'] ,
                            );
                            if(isset($g_v['grandson_son'])&&$g_v['grandson_son'] ){
                                foreach($g_v['grandson_son'] as $g_s_v){
                                    $return_arr[] = array(
                                        'id' => $g_s_v['id'] ,
                                        'pId' => $g_s_v['parent_id'] ,//都放在二级菜单下面 假如继续分级 改为$g_s_v['parent_id']
                                        'name' => $g_s_v['name'] ,
                                    );
                                }
                            }
                        }
                    }

                }
            }

        }

        if($tag=='json'){
            return json_encode($return_arr);
        }else if($tag =='arr'){
            return $return_arr ;
        }
    }


    /**
     * 获取所有的权限
     * @param boolean $is_refresh 是否刷新缓存
     * Note:获取 level <=3 的内容
     * return 格式 array(
     * 						'privlilege_index'=>array(
     * 													'id'=>1,
     * 													'son'=>array(
     * 															'id'=>'1',
     * 															'grandson'=>array()
     * 															)
     * 												   )
     * 				     )
     */
    public function allPrivileges($is_refresh=true){

        /*$all_info = Yii::app()->redis->getClient()->get("adminAllPrivileges");
        if($is_refresh){
            $all_info = array();
        }*/
        $all_info = array();

        if($all_info){
            return json_decode($all_info,true) ;
        }else{
            $return_arr = array() ;
            $db = 'db' ;
            $tableName = 'sea_admin_privilege';
            $params['where_arr']['is_open'] = 1  ;
            $params['where_arr']['level'] = 0  ;
            $level0 = $this->findByWhere($tableName,$params,$db) ;
            foreach($level0 as $k0=>$v0){
                //儿子节点
                $params['where_arr']['level'] = 1  ;
                $params['where_arr']['parent_id'] = $v0['id']  ;
                $l0_sun= $this->findByWhere($tableName,$params,$db) ;
                foreach($l0_sun as $k1=>$v1){
                    //获取孙子节点
                    $params['where_arr']['level'] = 2  ;
                    $params['where_arr']['parent_id'] = $v1['id']  ;
                    $l0_sun_sun = $this->findByWhere($tableName,$params,$db) ;
                    foreach($l0_sun_sun as $k2=>$v2){
                        //获取孙子的儿子节点
                        $params['where_arr']['level'] = 3  ;
                        $params['where_arr']['parent_id'] = $v2['id']  ;
                        $l0_sun_sun_sun = $this->findByWhere($tableName,$params,$db) ;
                        $l0_sun_sun[$k2]['grandson_son'] = $l0_sun_sun_sun ;
                    }
                    $l0_sun[$k1]['grandson']  = $l0_sun_sun ;
                }
                $level0[$k0]['son'] = $l0_sun ;
                $return_arr[$v0['key']] = $v0;
                $return_arr[$v0['key']]['son'] = $l0_sun ;
            }
            //Yii::app()->redis->getClient()->set("adminAllPrivileges",json_encode($return_arr));
            return $return_arr ;
        }

    }

    /**
     * 获取角色具有的权限
     * @param
     * @return [type] [description]
     */
    public function rolePrivileges($role_id){

        if($role_id){
            $params['where_arr']['role_id'] = $role_id ;
            return $this->findByWhere('sea_admin_role_privilege',$params,'db');
        }else{
            return array() ;
        }
    }

    public function setRolePrivilegesRedis($role_id){

        $allPrivileges = $this->rolePrivileges($role_id);
        $return_arr = array();
        $params['return_type'] = 'row';
        foreach($allPrivileges as $v){
            $params['where_arr']['id'] = $v['privilege_id'];
            $pri_info  = $this->findByWhere($this->tableName(),$params,'db');

            $return_arr[] = $pri_info['key'].'_'.$pri_info['category'] ;
        }
        setRedis('adminUserAllPrivileges'.$role_id,$return_arr,180,'array');
    }

    /**
     * 保存角色的权限
     * @param  int $role_id         角色ID
     * @param  array $privilege_arr 所选择的权限 一维数组
     * @return boolean
     * Note:只根据前台的数据进行保存，不做后台校验
     */
    public function saveRolePrivileges($role_id,$privilege_arr){
        //step1:删除原有的
        $this->baseDelete('sea_admin_role_privilege', "role_id = :role_id",array(":role_id"=>$role_id) , 'db');
        $base_insert_arr = $this->returnBaseInsertArr();
        foreach($privilege_arr as $v){
            $insert_arr                 = array();
            $insert_arr                 = $base_insert_arr ;
            $insert_arr['role_id']      = $role_id ;
            $insert_arr['privilege_id'] = $v ;
            $this->baseInsert('sea_admin_role_privilege',$insert_arr, 'db');
        }

        //step2设置redis
        //$this->setRolePrivilegesRedis($role_id);
        return true ;
    }

    //更新用户角色
    public function updateUserRole($user_id,$role_id){

        $this->baseDelete('admin_user_role', "user_id = :user_id",array(":user_id"=>$user_id) , 'db');
        $base_insert_arr = Base::model()->returnBaseInsertArr();
        $insert_arr                 = array();
        $insert_arr                 = $base_insert_arr ;
        $insert_arr['role_id']      = $role_id ;
        $insert_arr['user_id'] = $user_id ;
        $this->baseInsert('admin_user_role',$insert_arr, 'db');


        return true ;
    }

    /**
     * 新增菜单/权限
     * @param [array] $addData [新增菜单的相关信息]
     * return int
     * Note:正确返回    insert_id > 0
     * 		错误值说明  -1 : 代表值是重复的
     */
    public function addPrivilege($addData,$type='menu'){
        //获取得到基本的插入值
        $baseInsertArr = $this->returnBaseInsertArr() ;
        $parent_id  = $addData['parent_id'] ;
        $controller = $addData['controller'] ;
        $function   = $addData['function'] ;


        //判断添加的内容是否存在
        $exists_where_data = array('is_open'=>1,'parent_id'=>$parent_id,'controller'=>$controller,'function'=>$function);
        $check_exists_res = $this->findOneByWhere('sea_admin_privilege',$exists_where_data);
        if($check_exists_res){
            return -1 ;

        }
        //判断层级
        $this->getLevel($tableName='{{admin_privilege}}',$parent_id,$level);

        $addData['level'] = $level;
        $addData['category'] = $this->getCatNameByLevel($level) ;



        $addData['key'] = $addData['controller'].'_'.$addData['function'] ;
        $finalData = array_merge($baseInsertArr,$addData);

        return $this->baseInsert('sea_admin_privilege',$finalData);

    }

    /**
     * 更新菜单/权限
     * @param   $addData  需要更新的数据
     * @param   $id
     * @param   $type     类型(菜单/权限)
     * @return id
     */
    public function updatePrivilege($addData,$id,$type='menu'){

        $parent_id = $addData['parent_id'];
        $name = $addData['name'];

        $params['where_arr'] = array('id' => $id);
        $params['return_type'] = 'row';
        $old_info = $this->findByWhere('sea_admin_privilege',$params) ;
        $this->getLevel($tableName='{{admin_privilege}}',$parent_id,$level);
        $addData['level'] = $level ;
        $addData['category'] = $this->getCatNameByLevel($level);
        $addData['key'] = $addData['controller'].'_'.$addData['function'] ;


        $update_result = $this->baseUpdate('sea_admin_privilege',$addData, "id = :id",array(":id"=>$id) , 'db');

        return $id ;
    }

    /**
     * 获取数据所属的level
     * @param  [string] $table_name   [表名]
     * @param  [int] $parent_id       [父类ID]
     * @param  [引用] $level          [最终返回的层级]
     */
    public function getLevel($table_name,$parent_id,&$level){

        if($parent_id){

            $params['where_arr'] = array('id' => $parent_id);
            $params['return_type'] = 'row';
            $retrun_one = $this->findByWhere('sea_admin_privilege',$params) ;

            if($retrun_one){
                $parent_id = $retrun_one['parent_id'] ;

                $level++ ;
                $this->getLevel($table_name,$parent_id,$level);
            }
        }else{
            if(!$level){
                $level = 0 ;
            }
        }
    }

    /**
     * 根据根据菜单的等级返回相对应的分类名称
     * @param  int $level   菜单等级
     * @return string       返回菜单分类相对应的字段值
     */
    public function getCatNameByLevel($level){
        if($level <=1){
            $category = 'menu';
        }else{
            $category = 'operation';
        }


        return $category ;
    }

}
