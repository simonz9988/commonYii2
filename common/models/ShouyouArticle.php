<?php

namespace common\models;

use Yii;
use common\components\QiniuCdn;

/**
 * This is the model class for table "sea_shouyou_article".
 *
 * @property string $id
 * @property string $cate_key 分类key值
 * @property string $real_cate_id 对应网站的分类ID
 * @property string $cate_key_name 分类显示名称
 * @property string $resource_url 来源地址
 * @property string $resource_name 来源网站名称
 * @property string $title 标题
 * @property string $seo_title seo标题
 * @property string $seo_keywords seo关键字
 * @property string $seo_description seo描述
 * @property string $cover_img_url 封面图片url
 * @property string $tags 标签，多标签逗号拼接
 * @property string $attr 属性(多属性的集合，逗号拼接)
 * @property string $attr_string 属性的文字说明，逗号拼接
 * @property string $source_name 来源名称
 * @property string $source_url 来源地址
 * @property int $rq_total 人气数据总
 * @property int $rq_month
 * @property int $rq_week
 * @property string $content 文章内容
 * @property string $publish_time 发布时间
 * @property int $sort 排序
 * @property string $status
 * @property int $is_open 是否有效1-有效0-无效
 * @property int $is_deal_images 是否已经处理好图片
 * @property string $create_time 创建时间
 * @property int $create_admin_id
 * @property string $update_time 更新时间
 * @property int $update_admin_id
 */
class ShouyouArticle extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_shouyou_article';
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
            [['seo_keywords', 'seo_description', 'content'], 'required'],
            [['rq_total', 'rq_month', 'rq_week', 'sort', 'is_open', 'is_deal_images', 'create_admin_id', 'update_admin_id'], 'integer'],
            [['content'], 'string'],
            [['publish_time', 'create_time', 'update_time'], 'safe'],
            [['cate_key', 'real_cate_id', 'status'], 'string', 'max' => 50],
            [['cate_key_name', 'resource_url', 'resource_name', 'title', 'seo_title', 'seo_keywords', 'seo_description', 'cover_img_url', 'tags', 'attr', 'attr_string', 'source_name', 'source_url'], 'string', 'max' => 255],
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
            'real_cate_id' => 'Real Cate ID',
            'cate_key_name' => 'Cate Key Name',
            'resource_url' => 'Resource Url',
            'resource_name' => 'Resource Name',
            'title' => 'Title',
            'seo_title' => 'Seo Title',
            'seo_keywords' => 'Seo Keywords',
            'seo_description' => 'Seo Description',
            'cover_img_url' => 'Cover Img Url',
            'tags' => 'Tags',
            'attr' => 'Attr',
            'attr_string' => 'Attr String',
            'source_name' => 'Source Name',
            'source_url' => 'Source Url',
            'rq_total' => 'Rq Total',
            'rq_month' => 'Rq Month',
            'rq_week' => 'Rq Week',
            'content' => 'Content',
            'publish_time' => 'Publish Time',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_open' => 'Is Open',
            'is_deal_images' => 'Is Deal Images',
            'create_time' => 'Create Time',
            'create_admin_id' => 'Create Admin ID',
            'update_time' => 'Update Time',
            'update_admin_id' => 'Update Admin ID',
        ];
    }

    /**
     * 处理封面和正式内容
     * @param $id
     */
    public function initContent($id){

        if($id){
            $params['cond'] = 'id=:id';
            $params['args'] = [':id'=>$id] ;
        }else{
            $params['cond'] = 'is_deal_images=:is_deal_images';
            $params['args'] = [':is_deal_images'=>0] ;
        }
        $params['fields'] = 'id,content';
        $params['limit'] = 1 ;
        $listInfo = $this->findAllByWhere($this->tableName(),$params,self::getDb());

        if ($listInfo) {
            $qiniu_obj = new QiniuCdn();
            foreach ($listInfo as $v) {
                var_dump($v['id']);
                $content = $v['content'];

                $from_domain = 'http://www.91danji.com';
                $new_static_domain ='http://new.v6399.com';
                $is_deal_water = true;
                $content_image_arr = $qiniu_obj->dealContent($content,$from_domain,$new_static_domain,$is_deal_water);
                $content = $content_image_arr['content'];
                $cover_img_url = $content_image_arr['cover_img_url'];

                if (!$cover_img_url) {
                    $where_str = 'id = :id';
                    $where_arr[':id'] = $v['id'];
                    $updateData['is_deal_images'] = 1;
                    $this->baseUpdate($this->tableName(), $updateData, $where_str, $where_arr,'db_newyxcms');
                    continue;
                }

                $updateData['is_deal_images'] = 1;

                $updateData['content'] = $content;
                $updateData['update_time'] = date('Y-m-d H:i:s');
                $updateData['cover_img_url'] = $cover_img_url;

                $where_str = 'id = :id';
                $where_arr[':id'] = $v['id'];
                $this->baseUpdate($this->tableName(), $updateData, $where_str, $where_arr,'db_newyxcms');
            }
        }

    }

    public function doPublish(){

        $hour = date('H');
        if($hour<8 || $hour >19){
            return false ;
        }
        $site_obj = new SiteConfig();
        $limit = $site_obj->getByKey('common_shouyou_article_publish_num_per_min');

        $params['cond'] = 'is_deal_images =:is_deal_images and status=:status ';
        $params['args'] = [':is_deal_images'=>1,':status'=>'disabled'];
        $params['limit'] = $limit?$limit:2;


        $list = $this->findAllByWhere($this->tableName(),$params,self::getDb());

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
