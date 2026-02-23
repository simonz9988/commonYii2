<?php
namespace common\components;

use common\models\TradeInApply;

class SfExpress
{
    private $custcode = 'KWS';
    //private $checkword = '2CtyUmzjptBhmzzf0KWSSe4ZnumHZ';
    private $checkword = '2CtyUmzAHjptBhmzKWS0qYoSe4ZnumHZ';
    private $deliveryContact = '沈程飞';
    private $deliveryMobile = '0512-66384331';
    private $deliveryAddress = '江苏省苏州市吴中区越溪镇天鹅荡路47号（尊品电商产业园）2楼收发仓 ';

    /**
     * 逆向物流订单接口
     * @param $params
     * @return bool
     */
    public function reverseExpressOrder($params)
    {
        $content = [];
        $content['orderId'] = $this->createOrderId($params);
        $content['shipperContact'] = $params['shipperContact'];
        $content['shipperMobile'] = $params['shipperMobile'];
        $content['shipperAddress'] = $params['shipperAddress'];
        $content['deliveryContact'] = $this->deliveryContact;
        $content['deliveryMobile'] = $this->deliveryMobile;
        $content['deliveryAddress'] = $this->deliveryAddress;
        if($params['goods']){
            foreach($params['goods'] as $g){
                $temp = [];
                $temp['name'] = $g['name'];
                $content['cargos'][] = $temp;
            }
        }

        $parameter['content'] = ["custCode" => $this->custcode, "intCode" => "O2S_REVSERSE", "content" => json_encode($content,JSON_UNESCAPED_UNICODE)];
        $parameter['content'] = json_encode( $parameter['content'],JSON_UNESCAPED_UNICODE );
        $sign = base64_encode(md5($parameter['content'].$this->checkword,true));
        $parameter['sign'] = $sign;
        $url = $this->getPostUrl();

        $result = curlGo($url,$parameter,1);

        //记录回调日志
        $trade_in_apply_obj = new TradeInApply();
        $log = ['apply_id'=>$params['apply_id'],'push_url'=>$url,'post_data'=>json_encode($parameter,JSON_UNESCAPED_UNICODE),'return_data'=>$result,'create_time'=>date("Y-m-d H:i:s")];
        $log_id = $trade_in_apply_obj->baseInsert('sf_reverse_express_callback_log', $log, 'db_log');

        $result = urldecode($result);
        return $result;
    }

    /**
     * 生成订单id
     * @param $params
     * @return string
     */
    private function createOrderId($params){
        $user_id = $params['userId'];
        $len = strlen($user_id);
        $end =  intval(str_pad( "9" ,  11 - $len ,  "9" ,  STR_PAD_RIGHT ));
        $order_id = $user_id.str_pad(mt_rand(1,$end),11 - $len , "0", STR_PAD_LEFT);

        return $order_id;
    }

    /**
     * 请求地址
     * @return string
     */
    private function  getPostUrl(){
        if(YII_ENV == 'pro'){
            $url = 'http://www.sffix.cn/swx/swxOmsService.html';
        }else{
            $url = 'http://58.251.162.147:1111/swx/swxOmsService.html';
        }

        return $url;
    }

}
