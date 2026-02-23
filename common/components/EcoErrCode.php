<?php
namespace common\components;
class EcoErrCode
{
    //错误码字典, 格式为6位数字
    //1***** 通用错误
    //2***** 用户相关错误
    //3***** 商品相关错误
    //4***** 订单相关错误
    //5***** 优惠相关错误
    //6***** 购物车相关错误
    //9***** 其他错误

    private $ERR_CODE_DICT = array(
        //通用错误
        '1' => '成功',
        '100001' => '鉴权失败',
        '100002' => '用户尚未登录',
        '100003' => '用户认证不通过',
        '100004' => '签名参数传递不正确',
        '100005' => '请求超时，有效期为10分钟',
        '100006' => '签名不正确',


    );

    /**
     * 获取错误码
     * @param $code
     * @return string
     */
    public function get($code)
    {
         return isset($this->ERR_CODE_DICT[$code]) ? $this->ERR_CODE_DICT[$code] : "未知错误";
    }

}
