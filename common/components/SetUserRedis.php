<?php
namespace common\components;

/**
 * 设置所有用的redis信息
 * Class SetUserRedis
 * @package common\components\
 * Note:
 * 需要设置的redis信息
 * DayBalance:$user_id
 */
class SetUserRedis
{
    public function init(){
        #TODO
    }

    /**
     * 设置redis
     * @param $key
     * @param $value
     * @param $expire
     * @return mixed
     */
    private function setRedisByKey($key,$value,$expire=0){

        $model = new MyRedis() ;
        $expire = intval($expire) ;
        return  $model->set($key,$value,$expire) ;

    }

    /**
     * 获取redis的信息
     * @param $key
     * @param string $type
     * @return bool|float
     */
    private function getRedisByKey($key,$type='float'){
        $model = new MyRedis();
        $res = $model->get($key) ;

        if($type =='float'){
            $res = floatval($res) ;
        }
        return $res ;
    }

    /**
     * 计算redis的值并进行存储
     * @param string  $key  redis key值
     * @param float   $amount  传入需要操作的数值
     * @param integer $expire  需要设置的缓存有效期
     * @param string  $operate_type  操作类型 add-相加 reduce -相减
     * @param string  $amount_type   传入的数值类型
     * @param string $type
     */
    public function calcRedisByKey($key,$amount,$expire=0,$operate_type='add',$amount_type ='float'){

        $redis_info = $this->getRedisByKey($key,$amount_type) ;
        if($operate_type =='add'){
            $amount = $redis_info + $amount ;
        }

        return $this->setRedisByKey($key,$amount,$expire) ;

    }

    /**
     * 设置用户余额redis
     * @param $user_id
     * @param $amount
     * @return mixed
     */
    public function setDayBalance($user_id,$amount){
        $redis_key = "DayBalance:".$user_id ;
        $amount = floatval($amount) ;
        return $this->calcRedisByKey($redis_key,$amount) ;
    }

    /**
     * 设置用户余额redis
     * @param $user_id
     * @param $amount
     * @param $date
     * @return mixed
     */
    public function setDayBalanceByDate($user_id,$amount,$date){
        $redis_key = "DateDayBalance:".$user_id.':'.$date ;
        $amount = floatval($amount) ;
        return $this->setRedisByKey($redis_key,$amount) ;
    }

    /**
     * 根据用户ID返回用户余额
     * @param $user_id
     * @return bool|float
     */
    public function getDayBalance($user_id){
        $redis_key = "DayBalance:".$user_id ;
        return $this->getRedisByKey($redis_key) ;
    }

    /**
     * 根据用户ID返回用户余额
     * @param $user_id
     * @param $date
     * @return bool|float
     */
    public function getDayBalanceByDate($user_id,$date){
        $redis_key = "DateDayBalance:".$user_id.':'.$date ;
        return $this->getRedisByKey($redis_key) ;
    }

    /**
     * 设置用户基础的redis信息
     * @param $user_id
     * @param $user_root_path
     * @param $inviter_user_id
     * @param $inviter_username
     * @param $user_level
     * @param $extra_info
     * @param $user_level
     */
    public function setUserInfo($user_id,$user_root_path,$inviter_user_id,$inviter_username,$user_level,$extra_info){
        $redis_key = "UserBaseInfo:".$user_id ;
        $user_info['user_root_path'] = $user_root_path ;
        $user_info['inviter_user_id'] = $inviter_user_id ;
        $user_info['inviter_username'] = $inviter_username ;
        $user_info['user_level'] = $user_level ;
        $user_info['user_type'] = $extra_info['user_type'] ;
        $user_info['is_super'] = $extra_info['is_super'] ;
        $user_info = serialize($user_info) ;
        return  $this->setRedisByKey($redis_key,$user_info) ;
    }

    /**
     * 获取用户的信息
     * @param $user_id
     * @return mixed
     */
    public function getUserInfo($user_id){
        $redis_key = "UserBaseInfo:".$user_id ;
        $redis_info = $this->getRedisByKey($redis_key,'string') ;
        $user_info = @unserialize($redis_info);
        return $user_info ;
    }

    /**
     * 设置用户的总收益值
     * @param $user_id
     * @param $earn
     * @return mixed
     */
    public function setUserEarnInfo($user_id,$earn){
        $redis_key = "UserEarnTotal:".$user_id ;
        return $this->calcRedisByKey($redis_key,$earn);
    }

    /**
     * 获取用户总收益值
     */
    public function getUserEarnInfo($user_id){
        $redis_key = "UserEarnTotal:".$user_id ;
        return $this->getRedisByKey($redis_key);
    }

    /**
     * 设置用户的总收益值归零
     * @param $user_id
     * @return mixed
     */
    public function setUserEarnInfoZero($user_id){
        $redis_key = "UserEarnTotal:".$user_id ;
        return $this->setRedisByKey($redis_key,0);
    }

    /**
     * 设置指定完成类型的标志
     * @param $type
     * @param $date
     * @return mixed
     */
    public function setTagByType($type,$date){
        $redis_key = "Tag:".$type.":".$date ;
        //设置统一有效期
        return $this->setRedisByKey($redis_key,1,86400) ;
    }

    /**
     * 获取指定类型的标志是否存在
     * @param $type
     * @param $date
     * @return bool|float
     */
    public function getTagByType($type,$date){
        $redis_key = "Tag:".$type.":".$date ;
        return $this->getRedisByKey($redis_key) ;
    }


    /**
     * 设置用户的总收益值
     * @param $user_id
     * @param $amount
     * @return mixed
     */
    public function setUserSuperInfo($user_id,$amount){
        $redis_key = "UserSuper:".$user_id ;
        return $this->calcRedisByKey($redis_key,$amount);
    }


    /**
     * 设置用户的总收益值
     * @param $user_id
     * @param $amount
     * @return mixed
     */
    public function getUserSuperInfo($user_id){
        $redis_key = "UserSuper:".$user_id ;
        return $this->getRedisByKey($redis_key);
    }

    /**
     * 设置用户赛季信息
     * @param $user_id
     * @param $season
     * @param $amount
     * @return mixed
     */
    public function setUserSeasonInfo($user_id,$season,$amount){
        $amount = $amount > 0 ? $amount : 0 ;
        $redis_key = "UserSeason:".$user_id.':'.$season ;
        return $this->calcRedisByKey($redis_key,$amount);
    }

    public function getUserSeasonInfo($user_id,$season){
        $redis_key = "UserSeason:".$user_id.':'.$season ;
        return $this->getRedisByKey($redis_key) ;
    }

}