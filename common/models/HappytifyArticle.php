<?php

namespace common\models;

use Yii;
//七牛处理组件
use common\components\QiniuCdn;

/**
 * This is the model class for table "sea_happytify_article".
 *
 * @property string $id
 * @property string $title
 * @property string $cate_id
 * @property string $content
 * @property string $og_title
 * @property string $og_img_url
 * @property string $og_description
 * @property string $og_type
 * @property int $is_deal_content
 * @property int $is_deal_og_img
 * @property string $seo_title
 * @property string $seo_keywords
 * @property string $seo_description
 * @property int $sort 排序
 * @property int $is_open
 * @property string $status enabled/disabled
 * @property string $publish_time 发布时间
 * @property string $create_time
 * @property int $create_admin_id
 * @property string $update_time
 * @property int $update_admin_id
 */
class HappytifyArticle extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_happytify_article';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_happytify');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content', 'seo_description'], 'required'],
            [['content', 'seo_description'], 'string'],
            [['is_deal_content', 'is_deal_og_img', 'sort', 'is_open', 'create_admin_id', 'update_admin_id'], 'integer'],
            [['publish_time', 'create_time', 'update_time'], 'safe'],
            [['title', 'cate_id', 'og_title', 'og_img_url', 'og_description', 'og_type', 'seo_title', 'seo_keywords'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'cate_id' => 'Cate ID',
            'content' => 'Content',
            'og_title' => 'Og Title',
            'og_img_url' => 'Og Img Url',
            'og_description' => 'Og Description',
            'og_type' => 'Og Type',
            'is_deal_content' => 'Is Deal Content',
            'is_deal_og_img' => 'Is Deal Og Img',
            'seo_title' => 'Seo Title',
            'seo_keywords' => 'Seo Keywords',
            'seo_description' => 'Seo Description',
            'sort' => 'Sort',
            'is_open' => 'Is Open',
            'status' => 'Status',
            'publish_time' => 'Publish Time',
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
    public function initContent($id){

        if($id){
            $params['cond'] = 'id=:id';
            $params['args'] = [':id'=>$id] ;
        }else{
            $params['cond'] = 'is_deal_content=:is_deal_content';
            $params['args'] = [':is_deal_content'=>0] ;
        }
        $params['fields'] = 'id,content';

        $rowInfo = $this->findOneByWhere($this->tableName(),$params,self::getDb());

        var_dump($rowInfo['id']);

        $content = $rowInfo['content'];
        $from_domain = 'http://happytify.cc';
        $new_static_domain = 'http://new.v6399.com';
        $is_deal_water = false;//是否处理水印
        $qiniu_obj = new QiniuCdn();
        $content_image_arr = $qiniu_obj->dealContent($content,$from_domain,$new_static_domain,$is_deal_water);
        $content = $content_image_arr['content'];
        $cover_img_url = $content_image_arr['cover_img_url'];

        if (!$cover_img_url) {
            $where_str = 'id = :id';
            $where_arr[':id'] = $rowInfo['id'];
            $updateData['is_deal_content'] = 1;
            $this->baseUpdate($this->tableName(), $updateData, $where_str, $where_arr,'db_happytify');

        }

        $updateData['is_deal_content'] = 1;

        $updateData['content'] = $content;
        $updateData['update_time'] = date('Y-m-d H:i:s');

        $where_str = 'id = :id';
        $where_arr[':id'] = $rowInfo['id'];
        $this->baseUpdate($this->tableName(), $updateData, $where_str, $where_arr,'db_happytify');
    }

    public function dealOgImage($id){
        if($id){
            $params['cond'] = 'id=:id';
            $params['args'] = [':id'=>$id] ;
        }else{
            $params['cond'] = 'is_deal_og_img=:is_deal_og_img';
            $params['args'] = [':is_deal_og_img'=>0] ;
        }
        $params['fields'] = 'id,og_img_url';
        $info = $this->findOneByWhere($this->tableName(),$params,self::getDb());

        if($info){
            $url = $info['og_img_url'];
            $qiniu_obj = new QiniuCdn();
            $from_domain = 'http://happytify.cc';
            $new_static_domain = 'http://new.v6399.com';
            $new_url = $qiniu_obj->saveByUrl($url,$from_domain,$new_static_domain,false);
            $this->baseUpdate($this->tableName(),array('og_img_url'=>$new_url,'is_deal_og_img'=>1),'id=:id',array(':id'=>$info['id']),'db_happytify');
        }

        var_dump($info['id']);
    }

    public function doPublish(){
        $hour = date('H');

        if($hour<10 || $hour >19){
            return false ;
        }
        $site_obj = new SiteConfig();
        $limit = $site_obj->getByKey('common_happytify_article_publish_num_per_min');

        $params['cond'] = 'is_deal_content=:is_deal_content and is_deal_og_img=:is_deal_og_img  and status=:status';
        $params['args'] = [':is_deal_content'=>1,':is_deal_og_img'=>1,':status'=>'disabled'];
        $params['limit'] = $limit ;
        $list  = $this->findAllByWhere($this->tableName(),$params,self::getDb());

        if($list){
            foreach($list as $info){
                var_dump($info['id']);
                $num = mt_rand(10,40);
                $time = time()+$num;
                $update_time = date('Y-m-d H:i:s',$time);
                $status = 'enable';
                $publish_time = $update_time ;
                $update_data = compact('update_time','status','publish_time');

                $this->baseUpdate($this->tableName(),$update_data,'id=:id',array(':id'=>$info['id']),'db_happytify');
            }
        }
    }
}
