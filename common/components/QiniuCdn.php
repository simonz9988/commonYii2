<?php
/**
* 七牛处理图片方法
 */
namespace common\components;
use Yii;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Processing\PersistentFop;
use common\models\CdnDealContents ;

class QiniuCdn{

    /**处理正文内容
     * @param $content
     * @param $from_domain 来源地址的url eg：http://www.91danji.com
     * @param $new_static_domain 保存的静态资源的域名 eg:http://new.v6399.com http://image.120chaxun.com
     * @param $is_deal_water 是否处理水印
     * return 返回正文内容和封面图片
     */
    public function dealContent($content,$from_domain,$new_static_domain,$is_deal_water=false){

        //step1  单引号双引号判断 并替换
        $content = str_replace('\'','"',$content);

        //step2 正则获取所有的图片信息
        $imgpreg = "/<img(.*?)src=\"(.*?)\".*?>/";
        preg_match_all($imgpreg,$content,$img);
        $length = count($img);
        $img_src_arr = array();
        if($length) {
            $key = $length - 1;
            $img_src_arr = $img[$key] ;
        }

        //正文没有内容 直接返回原文
        if(!$img_src_arr){
            return array('content'=>$content,'cover_img_url'=>'');
        }

        $cover_img_url = '';
        if($img_src_arr){
            $i = 1 ;
            foreach($img_src_arr as $url){

                //判断是否为相对地址 还是绝对地址
                //判断前7个字符串
                $real_url = $this->formateUrl($url,$from_domain);

                //正则替换
                $new_url = $this->saveByUrl($real_url,$from_domain,$new_static_domain,$is_deal_water);
                if($i==1){
                    $cover_img_url = $new_url ;
                }
                $content = str_replace($url,$new_url,$content);
                $i++ ;
            }
        }

        $rst = compact('content','cover_img_url');
        return $rst;

    }


    /**
     * 远程上传图片
     * @param $url  远程链接地址带域名
     * @param $from_domain  来源的域名
     * @param $new_static_domain  返回的静态域名 http://images.120chaxun.com http://new.v6399.com
     * @param $is_deal_water 是否处理水印
     * @return string 新的链接地址
     */
    public function saveByUrl($url,$from_domain,$new_static_domain,$is_deal_water=false){

        // 需要填写你的 Access Key 和 Secret Key
        $accessKey ="DoR9P0FhjFfVELTsGcl-o0TGhDA4rGoAPoObfcSK";
        $secretKey = "CF5iF8lT5iprVdNNJd4TMzlNWMnvUFkx7sreZ8zD";

        if($new_static_domain =='http://images.120chaxun.com'){
            $bucket = "120chaxun";
            $img_water_func = '?imageView2/0/q/75|watermark/1/image/aHR0cDovL3d3dy4xMjBjaGF4dW4uY29tL3N0YXRpYy9mcm9udC9pbWFnZXMvbG9nby5qcGc=/dissolve/100/gravity/SouthEast/dx/0/dy/0';
        }

        if($new_static_domain =='http://new.v6399.com'){
            $bucket = 'newv6399com';
            $img_water_func = '?imageView2/0/q/75|watermark/1/image/aHR0cDovL3d3dy52NjM5OS5jb20vc3RhdGljL2JsdWV3aGl0ZS9pbWcvbG9nby5qcGc=/dissolve/100/gravity/SouthEast/dx/0/dy/0';

        }

        if($new_static_domain =='http://static.s115.cn'){
            $bucket = 'statics115';
            $img_water_func = '?imageView2/0/q/75|watermark/1/image/aHR0cDovL3d3dy52NjM5OS5jb20vc3RhdGljL2JsdWV3aGl0ZS9pbWcvbG9nby5qcGc=/dissolve/100/gravity/SouthEast/dx/0/dy/0|imageslim';

        }

        $url = $this->formateUrl($url,$from_domain);

        // 构建鉴权对象
        $auth = new Auth($accessKey, $secretKey);
        $bmgr = new BucketManager($auth);

        //新生成的文件名
        $key = createFilePathAndName($url,$new_static_domain);

        list($ret, $err) = $bmgr->fetch($url, $bucket, $key);

        $new_static_domain = trim($new_static_domain,'/');
        $key = trim($key,'/');
        $new_url = $new_static_domain.'/'.$key ;

        //是否处理水印
        if($is_deal_water){
            $new_url  = $new_url.$img_water_func;
        }

        $cdn_obj = new CdnDealContents();
        $cdn_add_row = ['url'=>$url,'from_domain'=>$from_domain,'new_static_domain'=>$new_static_domain,'new_url'=>$new_url];
        $cdn_obj->addRow($cdn_add_row);
        return $new_url;
    }

    /**
     * 处理静态资源地址
     * @param $url 来源url
     * @param $from_domain 来源域名
     * @return string
     */
    public function formateUrl($url,$from_domain){
        //判断是否为相对地址 还是绝对地址
        //判断前7个字符串
        $pre = substr($url,0,7);
        $pre_https = substr($url,0,8);
        $pre_other = substr($url,0,2);
        if($pre!='http://' && $pre_https!='https://' && $pre_other !='//'){
            $real_url = $from_domain.$url;
        }else{
            if($pre_other =='//'){
                $real_url = 'http:'.$url;
            }else{
                $real_url = $url ;
            }

        }

        return $real_url ;
    }



}