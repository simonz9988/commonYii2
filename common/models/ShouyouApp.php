<?php

namespace common\models;

use Yii;
//七牛处理组件
use common\components\QiniuCdn;
use common\models\SiteConfig ;

/**
 * This is the model class for table "sea_shouyou_app".
 *
 * @property string $id
 * @property string $cate_key 分类key值
 * @property string $resource_url 来源地址
 * @property string $resource_name 来源名称
 * @property string $real_cate_id 对应于采集站的ID
 * @property string $cate_key_name 分类显示名称
 * @property string $title 标题
 * @property string $old_name 原名
 * @property string $icon_img_url 图标
 * @property string $seo_title seo标题
 * @property string $seo_keywords seo关键字
 * @property string $seo_description seo描述
 * @property string $tags 标签，多标签逗号拼接
 * @property string $price 价格
 * @property string $pingtai 平台
 * @property string $banben 版本
 * @property string $daxiao 大小
 * @property string $attr 属性(多属性的集合，逗号拼接)
 * @property string $attr_string 属性的文字说明，逗号拼接
 * @property int $rq_total 人气数据总
 * @property int $rq_month
 * @property int $rq_week
 * @property int $fenshu 分数
 * @property int $renshu 人数
 * @property string $zhuanti_url 专题地址
 * @property string $youxixiazai_url 游戏下载地址
 * @property string $tuiguangxiazai_url 推广下载地址
 * @property string $is_use_tuiguang 是否使用推广下载地址yes no
 * @property string $images_domain 图片资源域名
 * @property int $is_deal_images 是否已经处理内容中图片
 * @property int $is_deal_cover_images 处理封面图片
 * @property int $is_deal_jietu 是否处理好截图
 * @property string $tgb_id 推广包ID
 * @property string $jietu 截图地址
 * @property string $content 文章内容
 * @property string $tese 游戏特色
 * @property string $publish_time 发布时间
 * @property int $sort 排序
 * @property string $status
 * @property int $is_open 是否有效1-有效0-无效
 * @property string $create_time 创建时间
 * @property int $create_admin_id
 * @property string $update_time 更新时间
 * @property int $update_admin_id
 */
class ShouyouApp extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_shouyou_app';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_newyxcms');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['seo_keywords', 'seo_description', 'jietu', 'content', 'tese'], 'required'],
            [['rq_total', 'rq_month', 'rq_week', 'fenshu', 'renshu', 'is_deal_images', 'is_deal_cover_images', 'is_deal_jietu', 'sort', 'is_open', 'create_admin_id', 'update_admin_id'], 'integer'],
            [['jietu', 'content', 'tese'], 'string'],
            [['publish_time', 'create_time', 'update_time'], 'safe'],
            [['cate_key', 'real_cate_id', 'status'], 'string', 'max' => 50],
            [['resource_url', 'resource_name', 'cate_key_name', 'title', 'old_name', 'icon_img_url', 'seo_title', 'seo_keywords', 'seo_description', 'tags', 'price', 'pingtai', 'banben', 'daxiao', 'attr', 'attr_string', 'zhuanti_url', 'youxixiazai_url', 'tuiguangxiazai_url', 'is_use_tuiguang', 'images_domain', 'tgb_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cate_key' => 'Cate Key',
            'resource_url' => 'Resource Url',
            'resource_name' => 'Resource Name',
            'real_cate_id' => 'Real Cate ID',
            'cate_key_name' => 'Cate Key Name',
            'title' => 'Title',
            'old_name' => 'Old Name',
            'icon_img_url' => 'Icon Img Url',
            'seo_title' => 'Seo Title',
            'seo_keywords' => 'Seo Keywords',
            'seo_description' => 'Seo Description',
            'tags' => 'Tags',
            'price' => 'Price',
            'pingtai' => 'Pingtai',
            'banben' => 'Banben',
            'daxiao' => 'Daxiao',
            'attr' => 'Attr',
            'attr_string' => 'Attr String',
            'rq_total' => 'Rq Total',
            'rq_month' => 'Rq Month',
            'rq_week' => 'Rq Week',
            'fenshu' => 'Fenshu',
            'renshu' => 'Renshu',
            'zhuanti_url' => 'Zhuanti Url',
            'youxixiazai_url' => 'Youxixiazai Url',
            'tuiguangxiazai_url' => 'Tuiguangxiazai Url',
            'is_use_tuiguang' => 'Is Use Tuiguang',
            'images_domain' => 'Images Domain',
            'is_deal_images' => 'Is Deal Images',
            'is_deal_cover_images' => 'Is Deal Cover Images',
            'is_deal_jietu' => 'Is Deal Jietu',
            'tgb_id' => 'Tgb ID',
            'jietu' => 'Jietu',
            'content' => 'Content',
            'tese' => 'Tese',
            'publish_time' => 'Publish Time',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_open' => 'Is Open',
            'create_time' => 'Create Time',
            'create_admin_id' => 'Create Admin ID',
            'update_time' => 'Update Time',
            'update_admin_id' => 'Update Admin ID',
        ];
    }

    /**
     * 处理正文内容
     * @param $id
     */
    public function initAppContent($id){

        if($id){
            $params['cond'] = 'id=:id';
            $params['args'] = [':id'=>$id] ;
        }else{
            $params['cond'] = 'is_deal_images=:is_deal_images';
            $params['args'] = [':is_deal_images'=>0] ;
        }
        $params['fields'] = 'id,content,images_domain';

        $rowInfo = $this->findOneByWhere($this->tableName(),$params,self::getDb());


        $content = $rowInfo['content'];
        $from_domain = 'http://www.91danji.com';
        $new_static_domain = 'http://new.v6399.com';
        $is_deal_water = true;//是否处理水印
        $qiniu_obj = new QiniuCdn();
        $content_image_arr = $qiniu_obj->dealContent($content,$from_domain,$new_static_domain,$is_deal_water);
        $content = $content_image_arr['content'];
        $cover_img_url = $content_image_arr['cover_img_url'];

        if (!$cover_img_url) {
            $where_str = 'id = :id';
            $where_arr[':id'] = $rowInfo['id'];
            $updateData['is_deal_images'] = 1;
            $this->baseUpdate($this->tableName(), $updateData, $where_str, $where_arr,'db_newyxcms');

        }

        $updateData['is_deal_images'] = 1;

        $updateData['content'] = $content;
        $updateData['update_time'] = date('Y-m-d H:i:s');

        $where_str = 'id = :id';
        $where_arr[':id'] = $rowInfo['id'];
        $this->baseUpdate($this->tableName(), $updateData, $where_str, $where_arr,'db_newyxcms');
    }

    /**
     * 处理封面图片
     * @param $id
     */
    public function dealIconCoverImage($id){

        if($id){
            $params['cond'] = 'id=:id';
            $params['args'] = [':id'=>$id] ;
        }else{
            $params['cond'] = 'is_deal_cover_images=:is_deal_cover_images';
            $params['args'] = [':is_deal_cover_images'=>0] ;
        }
        $params['fields'] = 'id,icon_img_url';
        $info = $this->findOneByWhere($this->tableName(),$params,self::getDb());

        if($info){
            $url = $info['icon_img_url'];
            $qiniu_obj = new QiniuCdn();
            $from_domain = 'http://www.91danji.com';
            $new_static_domain = 'http://new.v6399.com';
            $new_url = $qiniu_obj->saveByUrl($url,$from_domain,$new_static_domain,true);
            $this->baseUpdate($this->tableName(),array('icon_img_url'=>$new_url,'is_deal_cover_images'=>1),'id=:id',array(':id'=>$info['id']),'db_newyxcms');
        }
    }

    /**
     * 处理截图
     * @param $id
     */
    public function dealJietu($id){

        if($id){
            $params['cond'] = 'id=:id';
            $params['args'] = [':id'=>$id] ;
        }else{
            $params['cond'] = 'is_deal_jietu=:is_deal_jietu';
            $params['args'] = [':is_deal_jietu'=>0] ;
        }
        $params['fields'] = 'id,jietu';
        $info = $this->findOneByWhere($this->tableName(),$params,self::getDb());

        if($info){

            $jietu = $info['jietu'];
            $jietu_arr =explode('|',$jietu);
            $new_jietu_arr = array();
            if($jietu_arr){
                foreach($jietu_arr as $v){

                    $qiniu_obj = new QiniuCdn();
                    $from_domain = 'http://www.91danji.com';
                    $new_static_domain = 'http://new.v6399.com';
                    $new_url = $qiniu_obj->saveByUrl($v,$from_domain,$new_static_domain,false);

                    $new_jietu_arr[] = $new_url ;
                }
                $new_jietu = implode('|',$new_jietu_arr);
            }

            $this->baseUpdate($this->tableName(),array('jietu'=>$new_jietu,'is_deal_jietu'=>1),'id=:id',array(':id'=>$info['id']),'db_newyxcms');
            var_dump($info['id']);
        }
    }

    /**
     * 发布内容
     */
    public function doPublish(){

        $hour = date('H');

        if($hour<8 || $hour >19){
            return false ;
        }
        $site_obj = new SiteConfig();
        $limit = $site_obj->getByKey('common_shouyou_app_publish_num_per_min');

        $params['cond'] = 'is_deal_jietu=:is_deal_jietu and is_deal_cover_images=:is_deal_cover_images and is_deal_images=:is_deal_images and status=:status';
        $params['args'] = [':is_deal_jietu'=>1,':is_deal_cover_images'=>1,':is_deal_images'=>1,':status'=>'disabled'];
        $params['limit'] = $limit ;
        $list  = $this->findAllByWhere($this->tableName(),$params,self::getDb());

        if($list){
            foreach($list as $info){
                $num = mt_rand(10,40);
                $time = time()+$num;
                $update_time = date('Y-m-d H:i:s',$time);
                $status = 'enable';
                $publish_time = $update_time ;
                $update_data = compact('update_time','status','publish_time');

                $this->baseUpdate($this->tableName(),$update_data,'id=:id',array(':id'=>$info['id']),'db_newyxcms');
            }
        }
    }
}
