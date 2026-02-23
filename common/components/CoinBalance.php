<?php
namespace common\components;

use common\components\EthTools\Callback;
use common\components\EthTools\Credential;
use common\models\Nonce;
use Elliptic\EC;
use GuzzleHttp\Client;
use kornrunner\Keccak;
use Web3\Eth;
use Web3\Web3;
use xtype\Ethereum\Utils;

class CoinBalance
{
    private $eth_host = "http://127.0.0.1";
    private $eth_port = '7545';

    private $btc_scheme = 'http';
    private $btc_host = "localhost" ;
    private $btc_username = 'bitcoin';
    private $btc_password = 'Rpx450OcAKn2c';
    private $btc_port = '5888';

    public function init()
    {

    }

    /**
     * 获取eth服务请求地址
     * @return string
     */
    private function returnEthServiceUrl(){
        return $this->eth_host.':'.$this->eth_port;
    }

    /**
     * 获取btc协议地址
     * @return string
     */
    private function returnBtcServiceUrl(){
        return $this->btc_scheme.'://'.$this->btc_username.':'.$this->btc_password.'@'.$this->btc_host.':'.$this->btc_port ;
    }

    /**
     * 获取指定指定类型的余额信息
     * @param $address string
     * @param $type  string
     * @param $token_detail 只有在type为OTHER_TOEKN的时候才能使用到
     * @return mixed
     */
    public function getBalanceByType($address,$type,$token_detail){

        if($type=='ETH'){

            $web3 = new Web3($this->returnEthServiceUrl());
            $cb = new Callback;
            $web3->eth->getBalance($address,'latest',$cb);
            $str = (string) $cb->result ;

            return $str/1000000000000000000 ;

        }else if($type =='BTC'){

            $client = new Client();

            $opts = [
                'json' => [
                    'jsonrpc' => '1.0',
                    'method' => 'getbalance',
                    'params' => [$address],
                    'id' => time()+mt_rand(10000,99999)
                ]
            ];

            $rsp = $client->post($this->returnBtcServiceUrl(),$opts);
            return  $rsp->getBody() ;

        }else if($type =='OMNI_USDT'){

            $client = new Client();

            $opts = [
                'json' => [
                    'jsonrpc' => '1.0',
                    'method' => 'getbalance',
                    'params' => [$address,31],// 资产ID默认设置为31
                    'id' => time()+mt_rand(10000,99999)
                ]
            ];

            $rsp = $client->post($this->returnBtcServiceUrl(),$opts);
            $res = $rsp->getBody() ;
            $res = json_decode($res,true);
            return isset($res['balance']) ? $res['balance']:0;

        }else if($type =='ERC20_USDT'){

        }else if($type =='TRC20_USDT'){
            #TODO

        }else if($type =='OTHER_TOEKN'){

            // 需要使用到具体的token
            $token_detail = 0 ;
        }
    }
}