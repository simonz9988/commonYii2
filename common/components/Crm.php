<?php
namespace common\components;

use common\models\CrmRecord;

class Crm
{
    /**
     * 增加埋点
     * @param $type
     * @param $sub_type
     * @param array $param_data
     * @param int $user_id
     * @param null $username
     */
    public function insertDataToCrmRecord($type,$sub_type,$param_data = [],$user_id = 0,$username = '')
    {
        //允许分类的数组集合
        $allow_type = array('user','cart','favorite','stock','order','comment','customerDeed','lack');
        $allow_sub_type = array('userInfo','userInfoDetail','binding','regist','login','joinCart','cancelCart','favoriteGoods','cancelFavoriteGoods','outStock','orderInfo','comment','lack');

        //组装数据入库
        if($type && $sub_type && in_array($type,$allow_type) && in_array($sub_type,$allow_sub_type))
        {
            $data['type'] = $type;
            $data['sub_type'] = $sub_type;
            $data['user_id'] = $user_id;
            $data['username'] = $username;
            if($param_data)
            {
                $data['content'] = json_encode($param_data);
            }

            $data['create_date'] = date('Y-m-d');
            $data['create_time'] = date('Y-m-d H:i:s');

            //用户相关数据
            $data['uri'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
            $data['query_string'] = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '' ;
            $data['ip'] = ip2long(getClientIP());
            $data['useragent'] =isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';

            $crm_record_obj = new CrmRecord();
            $crm_record_obj->baseInsert('sdb_crm_record',$data);
        }
    }

}
