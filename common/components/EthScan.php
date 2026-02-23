<?php
namespace common\components;

use common\models\SiteConfig;

class EthScan
{
    public function init(){
        #TODO
    }

    public function returnApiKey(){
        $site_config_obj = new SiteConfig();
        $str = $site_config_obj->getByKey('ETH_SCAN_SECRET_KEY');
        return  $str;
    }

    /**
     * 获取通用信息组件
     * @param $from_key
     * @param $action
     * @param int $start_block
     * @param int $end_block
     * @param int $page 分页起始页是从1开始，而不是从0开始
     * @param int $offset 偏移量
     * @param string $sort
     * @return array
     */
    private function getCommonInfo($from_key,$action,$start_block=0,$end_block=9999999999,$page=1,$offset= 1000,$sort='desc'){

        //  通过私钥，返回地址信息
        $wallet_model = new EthWallet();
        $address = $wallet_model->getAddressByPrivateKey($from_key);

        $url = 'http://api.etherscan.io/api?module=account&action='.$action;
        $url .='&address='.$address;
        $url .='&startblock='.$start_block.'&endblock='.$end_block.'&page='.$page.'&offset='.$offset.'&sort='.$sort.'&apikey='.$this->returnApiKey();

        $list = curlGo($url);
        $res = @json_decode($list,true);
        $status = isset($res['status']) ? $res['status'] : 0 ;
        if($status !=1){
            return  [];
        }

        $result = isset($res['result']) ?$res['result']:[] ;
        return $result ;
    }


    /**
     * 获取通用信息组件
     * @param $address
     * @param $action
     * @param int $start_block
     * @param int $end_block
     * @param int $page 分页起始页是从1开始，而不是从0开始
     * @param int $offset 偏移量
     * @param string $sort
     * @return array
     */
    private function getCommonInfoByAddress($address,$action,$start_block=0,$end_block=9999999999,$page=1,$offset= 1000,$sort='desc'){

        $site_config_obj = new SiteConfig();
        $host = $site_config_obj->getByKey('eth_tx_query_address') ;
        $url = $host.'/api?module=account&action='.$action;
        $url .='&address='.$address;
        $url .='&startblock='.$start_block.'&endblock='.$end_block.'&page='.$page.'&offset='.$offset.'&sort='.$sort.'&apikey='.$this->returnApiKey();

        $list = curlGo($url);
        $res = @json_decode($list,true);
        $status = isset($res['status']) ? $res['status'] : 0 ;
        if($status !=1){
            return  [];
        }

        $result = isset($res['result']) ?$res['result']:[] ;
        return $result ;
    }

    /**
     * 获取通用信息组件
     * @param $address
     * @return array
     */
    private function getCommonContractInfoByAddress($address){

        $site_config_obj = new SiteConfig();
        $host = $site_config_obj->getByKey('eth_tx_query_address') ;
        $url = $host.'/api?module=account&action=tokentx';

        $url .= '&address='.$address ;
        $url .= '&startblock=0&endblock=999999999&sort=desc&&apikey='.$this->returnApiKey();
        $list = curlGo($url);
        $res = @json_decode($list,true);
        $status = isset($res['status']) ? $res['status'] : 0 ;
        if($status !=1){
            return  [];
        }

        $result = isset($res['result']) ?$res['result']:[] ;
        return $result ;
    }

    /**
     * 发送合约交易
     * @param $hex
     * @return mixed
     */
    public function sendContract($hex){
        $url = 'https://api-cn.etherscan.com/api?module=proxy&action=eth_sendRawTransaction&apikey='.$this->returnApiKey() ;
        $post_data['hex'] = $hex ;
        $res = curlGo($url,$post_data);

        return $res ;
    }

    /**
     * 根据hash获取geth相关的信息
     * @param $hash
     * @param $action
     * @return mixed
     * Note:eth_blockNumber eth_getBlockByNumber  eth_gasPrice等信息
     */
    public function getProxyByAction($hash,$action){

        $site_config= new SiteConfig();
        if($action =='eth_gasPrice'){
            $url=$site_config->getByKey('eth_tx_query_address').'/api?module=proxy&action=eth_gasPrice&apikey='.$this->returnApiKey();

        }else{
            $url=$site_config->getByKey('eth_tx_query_address').'/api?module=proxy&action='.$action.'&txhash='.$hash.'&apikey='.$this->returnApiKey();

        }

        $res = curlGo($url);
        $res = json_decode($res,true);
        return $res ;
    }

    public function getTransactionCount($address,$tag='pending'){
        $url='https://api.etherscan.io/api?module=proxy&action=eth_getTransactionCount&address='.$address.'&tag='.$tag.'&apikey='.$this->returnApiKey();
        $res = curlGo($url);
        $res = json_decode($res,true);
        return $res ;
    }
    /**
     * 根据hash获取交易状态相关的信息
     * @param $hash
     * @param $action
     * @return mixed
     * Note:getstatus gettxreceiptstatus
     */
    public function getTransByAction($hash,$action='getstatus'){
        $url='https://api.etherscan.io/api?module=transaction&action='.$action.'&txhash='.$hash.'&apikey='.$this->returnApiKey();
        $res = curlGo($url);
        return $res ;
    }

    public function getListBalance($address_arr){

        $address_arr = implode(',',$address_arr) ;
        $url = 'https://api.etherscan.io/api?module=account&action=balancemulti&address='.$address_arr.'&tag=latest&apikey='.$this->returnApiKey();

        $res = curlGo($url);
        $res = json_decode($res,true);
        $return  = isset($res['result']) ? $res['result'] : [] ;
        $total = 0 ;
        if($return){

            foreach($return as $v){
                $total += $v['balance'] ;
            }
        }
        return $total ;
    }

    /**
     * 返回普通交易的列表新
     * @param $from_key
     * @return mixed
     */
    public function  getTxList($from_key){

        $list = $this->getCommonInfo($from_key,'txlist',0,9999999999,1,1000,'asc') ;
        return $list ;

    }


    /** 返回普通交易的列表新
    * @param $to_address
    * @return mixed
    */
    public function  getTxListByAddress($to_address){

        $list = $this->getCommonInfoByAddress($to_address,'txlist',0,9999999999,1,1000,'asc') ;
        return $list ;

    }

    /**
     * 获取所有的usdt的转账交易记录信息
     * @param $to_address
     * @return array
     */
    public function  getUsdtTxListByAddress($to_address){

        $list = $this->getCommonContractInfoByAddress($to_address) ;

        if(!$list){
            return false ;
        }
        $res = [];

        foreach($list as $v){
            if($v['tokenSymbol'] =='USDT'){
                $res[] = $v ;
            }
        }

        return $res ;

    }


    public function getUsdtBalanceByAddress($address){
        $site_config_obj = new SiteConfig();
        $host = $site_config_obj->getByKey('eth_tx_query_address') ;
        $url = $host.'/api?module=account&action=tokenbalance&';
        $url .='contractaddress=0xdac17f958d2ee523a2206206994597c13d831ec7&address='.$address.'&tag=latest&apikey=';
        $url .= $this->returnApiKey();

        $list = curlGo($url);
        $res = @json_decode($list,true);
        $status = isset($res['status']) ? $res['status'] : 0 ;
        if($status !=1){
            return  [];
        }

        $result = isset($res['result']) ?$res['result']:[] ;
        $result = $result/1000000 ;
        return $result ;
    }



}