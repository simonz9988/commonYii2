<?php 
	
/**
 * 根据资源的相对地址，返回资源的绝对地址
 * @param   string $url
 * @return  string
 */
function static_url($url=''){
    $url = ltrim($url,'/');
    return '/'.$url ;
}

//返回商品详情url
function goods_url($id){
    return SHOP_URL."/product-{$id}.html";
}

//返回首页预览地址
function index_url($id){
    return SHOP_URL."/site/page_preview/id/".$id;
    //return ACCOUNT_URL . "/mall/site/preview?id=" . $id;
}

//返回支付方式图标
function payment_logo($url){
    return SHOP_URL.'/plugins/'.$url;
}

//返回平台图标
function oauth_logo($url){
    return SHOP_URL.'/'.$url;
}
/**
 * 转化成为静态资源服务器的地址
 * @param   string $url
 * @return  string
 */
function covertToStaticUrl($url){
    if(strpos($url, '//')  === false){
        $url = str_replace("./upload/","upload/", $url);
        $url = str_replace("/upload/","upload/", $url);
        $url = CDN_IMAGE_URL.'/'.$url;
    }
    return $url;
}

/**
 * 相对协议url处理
 * @param  string $url
 * @return string
 */
function  scheme_url($url='')
{
    if(preg_match('/^\/\//u', $url)){
        $scheme = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '';
        $scheme = ($scheme == 'on' ? 'https' : 'http');
        $url = $scheme.':'.$url;
    }

    return  $url;
}

function v6399_article_detail($id){
    $rst = '';
    if($id){
        $rst = DOMAIN_INDEX_URL.'/game_'.$id.'.html';
    }
    return $rst ;
}


function v6399_tools_detail($id){
    $rst = '';
    if($id){
        $rst = DOMAIN_INDEX_URL.'/tools_'.$id.'.html';
    }
    return $rst ;
}

function v6399_tools_list(){
    $rst = DOMAIN_INDEX_URL.'/tools_1.html';
    return $rst;
}

function v6399_app_download($id,$title){
    return 'http://d.yuyechao.com/zhushou.apk?n='.urlencode($title);
}

function upload_url($url){
    return 'http://'.$_SERVER['HTTP_HOST'].$url;
}
