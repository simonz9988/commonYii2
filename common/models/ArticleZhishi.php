<?php

namespace common\models;

use Yii;
use common\components\QiniuCdn ;

/**
 * This is the model class for table "sea_article_zhishi".
 *
 * @property string $id
 * @property int $article_cate_id 分类ID
 * @property string $url 采集url
 * @property string $url_create_time 源文章的发布时间
 * @property string $notice_type 建议分类
 * @property string $article_title 文章标题
 * @property string $cover_img_url 封面图片
 * @property string $article_author 文章作者
 * @property string $summary 文章概要
 * @property string $content 文章内容
 * @property string $mobile_content
 * @property string $source_name
 * @property string $seo_title
 * @property string $seo_keywords
 * @property string $seo_description
 * @property int $status 0-待审核 1-成功 2-作废
 * @property int $sort 文章排序
 * @property int $is_open 是否有效1-有效0-删除
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 * @property int $create_admin_id 创建文章管理员ID
 * @property int $update_admin_id 更新文章管理员ID
 * @property int $is_pc_send
 * @property int $is_mip_send
 * @property string $images_domain 图片域名
 * @property int $is_deal_images 是否已经处理静态图片
 */
class ArticleZhishi extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_article_zhishi';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['article_cate_id', 'status', 'sort', 'is_open', 'create_admin_id', 'update_admin_id', 'is_pc_send', 'is_mip_send', 'is_deal_images'], 'integer'],
            [['url_create_time', 'create_time', 'update_time'], 'safe'],
            [['content', 'mobile_content'], 'string'],
            [['url', 'notice_type', 'article_title', 'cover_img_url', 'article_author', 'summary', 'source_name', 'seo_title', 'seo_keywords', 'seo_description'], 'string', 'max' => 255],
            [['images_domain'], 'string', 'max' => 250],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'article_cate_id' => 'Article Cate ID',
            'url' => 'Url',
            'url_create_time' => 'Url Create Time',
            'notice_type' => 'Notice Type',
            'article_title' => 'Article Title',
            'cover_img_url' => 'Cover Img Url',
            'article_author' => 'Article Author',
            'summary' => 'Summary',
            'content' => 'Content',
            'mobile_content' => 'Mobile Content',
            'source_name' => 'Source Name',
            'seo_title' => 'Seo Title',
            'seo_keywords' => 'Seo Keywords',
            'seo_description' => 'Seo Description',
            'status' => 'Status',
            'sort' => 'Sort',
            'is_open' => 'Is Open',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
            'create_admin_id' => 'Create Admin ID',
            'update_admin_id' => 'Update Admin ID',
            'is_pc_send' => 'Is Pc Send',
            'is_mip_send' => 'Is Mip Send',
            'images_domain' => 'Images Domain',
            'is_deal_images' => 'Is Deal Images',
        ];
    }

    public function getTableNameByType($type){
        $tableNameArr = array(
            'zhishi'=>'sea_article_zhishi',
            'jibing'=>'sea_article_jibing',
            'jiancha'=>'sea_article_jiancha',
            'zhenzhuang'=>'sea_article_zhenzhuang',
        );
        $tableName = $tableNameArr[$type];
        return $tableName ;

    }

    /**
     * 初始化内容
     */
    public function initContent($type,$id=0){
        if($id){
            $params['cond'] = 'id=:id';
            $params['args'] = [':id'=>$id];
        }else{
            $greater_create_time = '2017-07-02 00:00:00';
            $params['cond'] = 'is_deal_images=:is_deal_images and create_time >:create_time';
            $params['args'] = [':is_deal_images'=>0,':create_time'=>$greater_create_time];
        }
        $tableName = $this->getTableNameByType($type);
        $params['fields'] = 'id,content,images_domain';
        $info = $this->findOneByWhere($tableName,$params,self::getDb());

        if($info){

            $content = $info['content'];
            $images_domain = $info['images_domain'];
            $qiniu_cdn_obj = new QiniuCdn();
            $from_domain = $images_domain;
            $new_static_domain = 'http://images.120chaxun.com';
            $is_deal_water = true ;
            $content_image_arr = $qiniu_cdn_obj->dealContent($content,$from_domain,$new_static_domain,$is_deal_water);
            $cover_img_url = $content_image_arr['cover_img_url'];
            $content = $content_image_arr['content'];

            if (!$cover_img_url) {
                $where_str = 'id = :id';
                $where_arr[':id'] = $info['id'];
                $updateData['is_deal_images'] = 1;
                $this->baseUpdate($this->tableName(), $updateData, $where_str, $where_arr);

            }

            $updateData['is_deal_images'] = 1;

            $updateData['content'] = $content;
            $updateData['cover_img_url'] = $cover_img_url;
            $updateData['update_time'] = date('Y-m-d H:i:s');

            $where_str = 'id = :id';
            $where_arr[':id'] = $info['id'];
            $this->baseUpdate($tableName, $updateData, $where_str, $where_arr,'db');
            var_dump($info['id']);
        }
    }

    public function doPublish($type){

        $hour = date('H');
        $tableName = $this->getTableNameByType($type);

        if($hour >7 && $hour<20 && $tableName) {
            //查找符合条件的信息
            $greater_create_time = '2017-07-02 00:00:00';
            $params['cond'] = 'is_deal_images=:is_deal_images and status =:status and create_time > :create_time ';
            $params['args'] = [':is_deal_images'=>1,':status'=>0,':create_time'=>$greater_create_time];

            $site_obj = new SiteConfig();
            $limit = $site_obj->getByKey('common_120chaxun_article_publish_num_per_min');
            $params['limit'] =$limit?$limit:1 ;
            $listInfo = $this->findAllByWhere($tableName,$params,self::getDb());

            //自动发布
            if ($listInfo) {
                foreach ($listInfo as $v) {

                    $updateData['status'] = 1;
                    $num = mt_rand(10,40);
                    $time = time()+$num;
                    $update_time = date('Y-m-d H:i:s',$time);
                    $updateData['update_time'] = $update_time;

                    $where_str = 'id = :id';
                    $where_arr[':id'] = $v['id'];
                    $this->baseUpdate($tableName, $updateData, $where_str, $where_arr,'db');
                    var_dump($v['id']);
                }
            }
        }
    }
}
