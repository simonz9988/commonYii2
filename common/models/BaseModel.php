<?php
namespace common\models;
use common\components\Ecosession;
use Yii;
use yii\db\Query;

/**
 * 针对所有数据数据表进行操作
 */
class BaseModel extends \yii\db\ActiveRecord
{

    public $error_data = [];

    /**
     * 写入数据库
     * @return mixed
     */
    public function writeDb($db = 'db'){
        return Yii::$app->$db->createCommand();
    }

    /**
     * 查询数据库
     * @return mixed
     */
    public function selectDb($db = 'db'){
        return Yii::$app->$db->createCommand();
    }

    /**
     * 获取数据库连接资源
     * @param string $db
     * @return mixed
     */
    public function getConnection($db = 'db'){
        return Yii::$app->$db;
    }


    /**
     * 插入数据
     * @param string $table_name 带前缀sdb
     * @param $data
     * @param string $db
     * @param string $return_type
     * @return string
     */
    public function baseInsert($table_name,$data, $db='db',$return_type='insert_id'){
        $res =  $this->writeDb($db)->insert($table_name ,$data)->execute();
        if($res){
            if($return_type=='insert_id'){
                return Yii::$app->$db->getLastInsertID() ;
            }
        }
        return $res;
    }

    /**
     * 批量插入数据
     * @param $table_name 带前缀sdb
     * @param $fields
     * @param $data
     * @param string $db
     * @return bool
     */
    public function baseBatchInsert($table_name,$fields,$data, $db='db'){
        $rst = $this->writeDb($db)->batchInsert($table_name ,$fields,$data)->execute();
        return $rst;
    }


    /**
     * 更新数据
     * @param $table_name
     * @param $data
     * @param string $conditions
     * @param array $params
     * @param string $db
     * @return mixed
     */
    public function  baseUpdate($table_name,$data,$conditions='', $params=array(),$db='db'){
        $rst =  $this->writeDb($db)->update($table_name, $data, $conditions, $params)->execute();
        return $rst;
    }

    /**
     * 删除数据
     * @param $table_name
     * @param string $conditions
     * @param array $params
     * @param string $db
     * @return mixed
     */
    public function  baseDelete($table_name,$conditions='', $params=array(),$db='db'){
        $rst =  $this->writeDb($db)->delete($table_name, $conditions, $params)->execute();
        return $rst;
    }

    /**
     * 根据条件获取记录信息
     * @param $table
     * @param $params
     * @return array|bool
     */
    public function findOneByWhere($table,$params,$conn=null){
        $fields = isset($params['fields']) ? $params['fields'] : '*';
        $cond = isset($params['cond']) ? $params['cond'] : '';
        $args = isset($params['args']) ? $params['args'] : '';
        $orderby = isset($params['orderby']) ? $params['orderby'] : '';
        $group_by = isset($params['group_by']) ? $params['group_by'] : '';
        $db = new Query();
        $query = $db ->select($fields)->from($table);

        if($orderby){
            $query->orderBy($orderby);
        }

        if($group_by){
            $query->groupBy($group_by) ;
        }

        $info = [];
        if($cond && $args){
            $info = $query->where($cond,$args)->limit(1)->one($conn);
        }
        return $info;
    }

    /**
     * 获取列表信息
     * @param $table
     * @param $params
     * @return array
     */
    public function findAllByWhere($table,$params,$conn = null){
        $fields = isset($params['fields']) ? $params['fields'] : '*';
        $cond = isset($params['cond']) ? $params['cond'] : '';
        $args = isset($params['args']) ? $params['args'] : '';
        $orderby = isset($params['orderby']) ? $params['orderby'] : '';
        $limit = isset($params['limit']) ? $params['limit'] : 0;
        $group_by = isset($params['group_by']) ? $params['group_by'] : '';
        $db = new Query();
        $query = $db ->select($fields)->from($table);

        if($orderby){
           $query->orderBy($orderby);
        }

        //分页
        $page      = isset($params['page'])?$params['page']:array() ;
        $curr_page = isset($page['curr_page'])?$page['curr_page']:1;
        if(intval($curr_page) > 0){
            $curr_page = $curr_page;
        }else{
            $curr_page =  1 ;
        }
        $page_num  = isset($page['page_num'])?$page['page_num']:10;

        if($page){
            $offset = ($curr_page-1)*$page_num ;
            $query->limit($page_num) ;
            $query->offset($offset);
        }

        if($limit){
            $query->limit($limit);
        }

        if($group_by){
            $query->groupBy($group_by);
        }

        if($cond && $args){
            $info = $query->where($cond,$args)->all($conn);
        }else{
            $info = $query->all($conn);
        }
        return $info;
    }

    /**
     * 获取数量
     * @param $table
     * @param $params
     * @return int
     */
    public function findCountByWhere($table,$params,$conn = null){

        $cond = isset($params['cond']) ? $params['cond'] : '';
        $args = isset($params['args']) ? $params['args'] : '';
        $group_by = isset($params['group_by']) ? $params['group_by'] : '';

        $db = new Query();
        $query = $db->select("count(1) as total")->from($table);

        if($group_by){
            $query->groupBy($group_by);
        }

        if($cond && $args){
            $info = $query->where($cond,$args)->one($conn);
        }else{
            $info = $query->one($conn);
        }

        $count = isset($info['total']) ? $info['total'] : 0;
        return $count;
    }

    /**
     * 计算2天相差的天数
     * @param $start_time
     * @param $end_time
     * @return  integer ;
     */
    public function getExtByTime($start_time,$end_time){

        $ext = ( strtotime($end_time) - strtotime($start_time) ) /86400 ;
        return $ext ;
    }

    /**
     * 根据限定条件，返回结果
     * @param  string $table_name   [表名]
     * @param  array  $params    [限定条件]
     * @param  string $db           [所选择的数据库]
     * @return 返回值依据$params['return_type']进行判断 'all' 返回所有 'num' 返回数目  以后扩充
     * $params['return_type']          string 返回值类型 all / num  / row
     * $params['return_field']         string 返回字段 * / id etc...
     * $params['where_arr']            array  对应的限制条件 eg:array('id'=>'1') 查询id为1 的结果
     * $params['greater_where_arr']    array  大于等于限制条件
     * $params['lesser_where_arr']     array  小于等于限制条件
     * $params['order_by']             string  排序条件  eg: " id desc "
     * $params['page']                 array  分页内容 $params['page']['curr_page']   当前页
     *                                                 $params['page']['page_num']    每页数目
     * $params['like_arr']             array  模糊查询条件
     * $params['order_by']             string  排序字段
     * $params['group_by']             string  分类字段
     * $params['limit']                int    返回结果数目数   该选项不能 $params['page'] 共用
     * @return mixed
     */
    public function findByWhere($table_name,$params,$db='db'){

        $c = new Query();

        $return_type       = isset($params['return_type'])?$params['return_type']:'all' ;
        $return_field      = isset($params['return_field'])?$params['return_field']:'*' ;
        $where_arr         = isset($params['where_arr'])?$params['where_arr']:array() ;
        $greater_where_arr = isset($params['greater_where_arr'])?$params['greater_where_arr']:array() ;
        $lesser_where_arr  = isset($params['lesser_where_arr'])?$params['lesser_where_arr']:array() ;
        $in_where_arr      = isset($params['in_where_arr'])?$params['in_where_arr']:array() ;
        $not_where_arr     = isset($params['not_where_arr'])?$params['not_where_arr']:array() ;
        $like_arr          = isset($params['like_arr'])?$params['like_arr']:array() ;
        $order_by          = isset($params['order_by'])?$params['order_by']:'' ;
        $group_by          = isset($params['group_by'])?$params['group_by']:'' ;
        $limit             = isset($params['limit'])?$params['limit']:'' ;
        $c->select($return_field)->from($table_name) ;

        if($order_by && $return_type!='num'){
            $c->orderBy($order_by)  ;
        }

        //分页
        $page      = isset($params['page'])?$params['page']:array() ;
        $curr_page = isset($page['curr_page'])?$page['curr_page']:1;
        if(intval($curr_page) <= 0){
           $curr_page =  1 ;
        }

        $page_num  = isset($page['page_num'])?$page['page_num']:0;

        if($page_num){
            $c->limit($page_num) ;
        }

        if($page){
            $offset = ($curr_page-1)*$page_num ;
            $c->offset($offset);
        }

        $condition_arr = [];
        $args_arr = [];
        $i = 0 ;
        if($where_arr){
            foreach($where_arr as $k=>$v){
                $condition_arr[] = $k.'  =:'.$k.'_'.$i ;
                $args_arr[':'.$k.'_'.$i] = $v ;
                $i++ ;
            }
        }

        if($greater_where_arr){
            foreach($greater_where_arr as $k=>$v){

                $condition_arr[] = $k.' >=:'.$k.'_'.$i ;
                $args_arr[':'.$k.'_'.$i] = $v ;
                $i++ ;
            }
        }

        if($lesser_where_arr){
            foreach($lesser_where_arr as $k=>$v){

                $condition_arr[] = $k.' <=:'.$k.'_'.$i ;
                $args_arr[':'.$k.'_'.$i] = $v ;
                $i++ ;
            }
        }

        if($not_where_arr){
            foreach($not_where_arr as $k=>$v){

                $condition_arr[] = $k.' !=:'.$k.'_'.$i ;
                $args_arr[':'.$k.'_'.$i] = $v ;
                $i++ ;
            }
        }

        if($in_where_arr){
            foreach($in_where_arr as $k=>$v){

                $condition_arr[] = $k.' in ( '.implode(',',$v).' )' ;

            }
          }

        if($like_arr){
            foreach($like_arr as $k=>$v){

                $condition_arr[] = $k.' like :'.$k.'_'.$i ;
                $args_arr[':'.$k.'_'.$i] = '%'.$v.'%' ;
                $i++ ;
            }
        }

        if($condition_arr){
            $c->where( implode(' AND ',$condition_arr),$args_arr) ;
        }

        if($group_by){
            $c->groupBy($group_by);
        }

        if($limit){

            $c->limit($limit) ;
        }

         if($return_type =='all'){
            $res = $c->all(self::getDb());
            //setRedis($redisKey,serialize($res));
            return $res ;
        }else if($return_type =='row'){
            $res = $c->one(self::getDb());
            //setRedis($redisKey,serialize($res));
            return $res  ;
        }else if($return_type == 'num'){
            $c->select("count(1) as total") ;
             $res = $c->one(self::getDb());
            return $res ? $res['total'] : 0 ;
         }
        return [];
    }

    /**
     * 获取插入数据公共内容
     * @return array [返回要插入的数值 数组形式]
     */
    public function returnBaseInsertArr(){

        $return_arr = array() ;

        $session_model = new Ecosession() ;
        $adminUser = $session_model->get('adminUser');

        $creater = 0 ;
        $create_time =  date('Y-m-d H:i:s',time());
        if($adminUser){
            $creater = $adminUser['id'] ;
        }

        $return_arr['creater'] = $creater ;
        $return_arr['create_time'] = $create_time ;
        return $return_arr ;

    }

    /**
     * 设置返回的错误信息
     * @param int $code
     * @param string $msg
     * @param array  $data
     */
    public function setError($code=1,$msg='', $data = array()){
        if(!$msg){
            $msg = getErrorDictMsg($code);
        }
        $this->error_data = ['code'=>$code,'msg'=>$msg,'data'=>$data] ;
    }





}
