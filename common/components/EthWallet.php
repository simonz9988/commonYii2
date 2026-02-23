<?php
namespace common\components;

use common\components\EthTools\Callback;
use common\components\EthTools\Credential;
use common\models\Nonce;
use Elliptic\EC;
use GuzzleHttp\Client;
use kornrunner\Keccak;
use Web3\Web3;
use xtype\Ethereum\Utils;

class EthWallet
{
    public function init(){
        #TODO
    }

    /**
     * 返回服务地址
     * @return string
     */
    public function returnServerUrl(){

        return SEND_RAW_SERVICE_URL ;
    }

    /**
     * 返回主链ID
     * @return string
     */
    public function returnChainId(){
        if(YII_ENV =='dev'){
            return  '10';
        }else{
            return  '1';
        }
    }

    /**
     * 返回正式环境的私钥信息
     * @return string
     */
    public function returnProSecret(){
        return  INFURA_SECRET;
    }

    /**
     * 正式环境推送curl
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function sendPostFromPro($method,$params=[]){

        $opts = [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => time().mt_rand(10000,99999),
        ];
        $aHeader= ['user:'.$this->returnProSecret()];
        $server_url = $this->returnServerUrl() ;

        $res = curl_post_https($server_url,json_encode($opts),$aHeader) ;

        $rst = @json_decode($res,true) ;
        return $rst ;

    }

    /**
     * 将16进制转换为10进制
     * @param string $value 16进制数值
     * @return integer 最终结果转换为10进制
     */
    private function hexToInt($value){
        $value = strtolower($value) ;
        $num = str_replace('0x','',$value);
        $num = hexdec($num);
        // 假如是科学计数法转换为浮点型

        if (stripos($num,'e')===false) return $num;
        $num = trim(preg_replace('/[=\'"]/','',$num,1),'"');//出现科学计数法，还原成字符串
        $result = "";
        while ($num > 0){
            $v = $num - floor($num / 10)*10;
            $num = floor($num / 10);
            $result   =   $v . $result;
        }
        return $result ;
    }

    /**
     * 根据私钥获取地址信息
     * @param $key
     * @return string
     */
    public function getAddressByPrivateKey($key){
        // 恢复地址
        $ec = new EC('secp256k1') ;
        //  导入私钥
        $keyPair = $ec->keyFromPrivate($key);
        $prvKey = $keyPair->getPrivate()->toString(16,2);
        $pubKey = $keyPair->getPublic()->encode('hex');
        $address = '0x' . substr(\kornrunner\Keccak::hash(substr(hex2bin($pubKey), 1), 256), 24);
        return $address ;
    }

    /**
     * 设置重试次数
     * @param $web3
     * @param $txhash
     * @param int $timeout
     * @param int $interval
     * @return mixed
     */
    public function waitForReceipt($web3,$txhash,$timeout=60,$interval=1){
        $cb = new Callback;
        $t0 = time();
        while(true){
            $web3->eth->getTransactionReceipt($txhash,$cb);
            if($cb->result) break;
            $t1 = time();
            if(($t1 - $t0) > $timeout) break;
            sleep($interval);
        }
        return $cb->result;
    }

    /**
     * 获取交易的日志信息
     * @return string
     */
    public function getSendRowData(){
        return  '0x' . bin2hex('Send Raw Trans at :'.date('Y-m-d H:i:s'));
    }

    /**
     * 获取合约需要的数据
     * @param $to_address
     * @param $num
     * @return string
     */
    public function getContractSendRowData($to_address,$num){
        //SHA-3，之前名为Keccak算法，是一个加密杂凑算法。
        $hash = Keccak::hash("transfer(address,uint256)",256);
        $hash_sub = mb_substr($hash,0,8,'utf-8');
        //接收地址
        $fill_from = $this->fill0(Utils::remove0x($to_address));
        //转账金额
        $num10 = Utils::ethToWei($num);
        $num16 = Utils::decToHex($num10);
        $fill_num16 = $this->fill0(Utils::remove0x($num16));

        return "0x" . $hash_sub . $fill_from . $fill_num16;
    }

    /**
     * 执行裸交易
     * @param $from_key
     * @param $to_address
     * @param $send_eth_num 需要发送的以太坊的数据
     * @param $start_nonce 传入的nonce值
     * @return mixed 返回交易地址的hash信息
     */
    public function sendRow($from_key,$to_address,$send_eth_num,$start_nonce =0){

        // 需要将发送的数量转换为字符串
        $send_eth_num = (string)$send_eth_num ;

        $server_url = $this->returnServerUrl() ;
        $web3 = new Web3($server_url);

        $cb = new Callback;

        $Utils_model = new \Web3\Utils() ;

        $ec = new EC('secp256k1') ;

        $keyPair = $ec->keyFromPrivate($from_key);
        $credential = new Credential($keyPair);

        $walletAddress = $this->getAddressByPrivateKey($from_key);

        if(!$start_nonce){
            // 获取nonce信息
            if(YII_ENV=='dev'){
                $web3->eth->getTransactionCount($walletAddress,'latest',$cb);
                $nonce =  $cb->result;
            }else{
                $nonce_params= [$walletAddress,'latest'] ;
                $nonce_arr = $this->sendPostFromPro('eth_getTransactionCount',$nonce_params) ;
                $nonce_result = isset($nonce_arr['result']) ?  $nonce_arr['result'] : '0x1';
                $nonce = $this->hexToInt($nonce_result) ;
            }
        }else{
            $nonce = $start_nonce ;
        }


        // 实时读取消耗的gas的数量
        if(YII_ENV=='dev') {
            $gasPriceInfo = $web3->eth->gasPrice($cb);
            $gasPriceInfo = object_array($gasPriceInfo);
            $gas_price = isset($gasPriceInfo['value']) ? $gasPriceInfo['value'] : ('0x' . $Utils_model::toWei('20', 'gwei')->toHex());
        }else{
            $gas_price_arr = $this->sendPostFromPro('eth_gasPrice',[]) ;
            $gas_price = isset($gas_price_arr['result']) ?  $gas_price_arr['result'] : '0x1';

        }

        $raw = [
            'nonce' => $Utils_model::toHex($nonce,true),
            'gasPrice' => $gas_price,
            'gasLimit' => '0x76c0',
            'to' => $to_address,
            'value' => $Utils_model::toHex($Utils_model::toWei($send_eth_num,'ether'),true),
            'data' => $this->getSendRowData(),
            'chainId' => $this->returnChainId() ,
        ];

        $signed = $credential->signTransaction($raw);

        if(YII_ENV=='dev') {
            $web3->eth->sendRawTransaction($signed,$cb);
            $res = $this->waitForReceipt($web3,$cb->result);
            $res = object_array($res);
            $res = isset($res['transactionHash']) ? $res['transactionHash'] : '';
            return $res ;
        }else{
            $send_params= [$signed] ;
            $send_arr = $this->sendPostFromPro('eth_sendRawTransaction',$send_params) ;
            $send_result = isset($send_arr['result']) ?  $send_arr['result'] : '';
            return $send_result ;

        }
    }

    /**
     * 获取账户余额信息
     * @param $from_key
     * @return string
     */
    public function getBalance($from_key){

        //服务器地址
        $server_url = $this->returnServerUrl() ;

        // 获取地址信息
        $ec = new EC('secp256k1') ;
        $keyPair = $ec->keyFromPrivate($from_key);
        $credential = new Credential($keyPair);
        $address = $this->getAddressByPrivateKey($from_key);

        if(YII_ENV =='dev'){

            $web3 = new Web3($server_url);
            $cb = new Callback;
            $web3->eth->getBalance($address,'latest',$cb);
            $str = (string) $cb->result ;
            return $str ;

        }else{

            $arr = $this->sendPostFromPro('eth_getBalance',[$address,'latest']) ;
            $result = isset($arr['result']) ?$arr['result']:'0x' ;
            $number = $this->hexToInt($result);
            return $number ;
        }

    }

    /**
     * 字符串长度 ‘0’左补齐
     * @param string $str   原始字符串
     * @param int $bit      字符串总长度
     * @return string       真实字符串
     */
    private function fill0($str, $bit=64){
        if(!strlen($str)) return "";
        $str_len = strlen($str);
        $zero = '';
        for($i=$str_len; $i<$bit; $i++){
            $zero .= "0";
        }
        $real_str = $zero . $str;
        return $real_str;
    }

    /**
     * 读取当前地址显示实时的nonce信息
     * @param $walletAddress
     * @return int
     */
    public function getNonceFromOnline($walletAddress){

        // 获取nonce信息
        $scan_model = new EthScan();

        $nonce_arr = $scan_model->getTransactionCount($walletAddress,'pending');
        $nonce_result = isset($nonce_arr['result']) ? $nonce_arr['result'] : '0x1';
        $nonce = $this->hexToInt($nonce_result);

        if(!$nonce){
            return $this->getNonce($walletAddress) ;
        }

        return $nonce ;
    }
    /**
     * 返回系统非重复的nonce
     * @param  $walletAddress
     * @return integer
     */
    public function getNonce($walletAddress){

        $nonce_model = new Nonce();
        $lasted_info = $nonce_model->getLasted();

        if(!$lasted_info){
            return $this->getNonceFromOnline($walletAddress) ;
        }else{

            $lasted_nonce = $lasted_info['nonce'];
            $nonce  =  $lasted_nonce + 1;

            $online_nonce = $this->getNonceFromOnline($walletAddress) ;
            if($nonce <$online_nonce){
                $nonce = $online_nonce ;
            }
        }


        return $nonce ;
    }

    /**
     * 发送合约交易地址
     * @param string $from_key
     * @param string $to_address
     * @param string $contract
     * @param string $num
     * @param integer $nonce 看是否已经传入默认值
     * @return mixed
     */
    public function sendContract($from_key,$to_address,$contract,$num,$nonce=0){
        // 需要将发送的数量转换为字符串
        $num = (string)$num ;

        $server_url = $this->returnServerUrl() ;
        $web3 = new Web3($server_url);

        $cb = new Callback;

        $Utils_model = new \Web3\Utils() ;

        $ec = new EC('secp256k1') ;

        $keyPair = $ec->keyFromPrivate($from_key);
        $credential = new Credential($keyPair);

        $walletAddress = $this->getAddressByPrivateKey($from_key);

        $scan_model = new EthScan();
        if(!$nonce){
            // 获取nonce信息
            $nonce = $this->getNonce($walletAddress) ;


        }



        $gas_price_info = $scan_model->getProxyByAction('','eth_gasPrice');
        // 实时读取消耗的gas的数量
        //$gas_price_info = $this->sendPostFromPro('eth_gasPrice',[]) ;
        $gas_price = isset($gas_price_info['result']) ?  $gas_price_info['result'] : '0x1';

        $raw = [
            "from" =>$walletAddress,
            'nonce' => $Utils_model::toHex($nonce,true),
            'gasPrice' => $gas_price,
            'gasLimit' => '0xebf0',
            //'gas' => '0xebf0',
            'to' => $contract,
            'value' => '0x0',
            'data' => $this->getContractSendRowData($to_address,$num),
            'chainId' => '1',
        ];


        // 替换推送方式

        $signed = $credential->signTransaction($raw);

        /*
        $send_params= [$signed] ;
        $send_arr = $this->sendPostFromPro('eth_sendRawTransaction',$send_params) ;
        $send_result = isset($send_arr['result']) ?  $send_arr['result'] : '';
        var_dump($send_result);exit;
        return $send_result ;
        */


        $scan_model = new EthScan();
        $res = $scan_model->sendContract($signed);
        var_dump($res);exit;
        $arr = @json_decode($res,true) ;
        $send_result = isset($arr['result']) ? $arr['result'] : '';

        return $send_result ;

    }

    /**
     * 获取指定token的余额
     * @param $address
     * @param $token
     * @return mixed
     */
    public function getTokenBalance($address,$token){
        if($token =='USDT'){
            https://api-cn.etherscan.com/api?module=account&action=tokenbalance&contractaddress=0x57d90b64a1a57749b0f932f1a3395792e12e7055&address=0xe04f27eb70e025b78871a2ad7eabe85e61212761&tag=latest&apikey=YourApiKeyToken
        }

        return 0;
    }
}