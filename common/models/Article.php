<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_article".
 *
 * @property int $id
 * @property string $title 标题
 * @property int $article_cate_id 文章分类ID
 * @property string $article_cate_key 文章分类key值
 * @property string $intro 简介
 * @property string $cover_img_url 封面图片
 * @property string $content 内容
 * @property string $seo_title
 * @property string $seo_keywords
 * @property string $seo_description
 * @property int $sort 排序
 * @property string $status ENABLED DISABLED
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class Article extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_article';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'article_cate_id'], 'required'],
            [['article_cate_id', 'sort'], 'integer'],
            [['intro', 'content'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['title', 'cover_img_url', 'seo_title', 'seo_keywords', 'seo_description', 'status'], 'string', 'max' => 255],
            [['article_cate_key'], 'string', 'max' => 50],
            [['is_deleted'], 'string', 'max' => 1],
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
            'article_cate_id' => 'Article Cate ID',
            'article_cate_key' => 'Article Cate Key',
            'intro' => 'Intro',
            'cover_img_url' => 'Cover Img Url',
            'content' => 'Content',
            'seo_title' => 'Seo Title',
            'seo_keywords' => 'Seo Keywords',
            'seo_description' => 'Seo Description',
            'sort' => 'Sort',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回行级信息
     * @param $id
     * @param string $fields
     * @return mixed
     */
    public function getInfoById($id,$fields='*'){

        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 根据文章分类KEY值返回文章列表
     * @param $cate_key
     * @param $fields
     * @param $limit
     * @return mixed
     */
    public function getListByCateKey($cate_key,$fields='*',$limit =20){

        $params['cond'] = 'article_cate_key=:article_cate_key AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':article_cate_key'=>$cate_key,':status'=>'ENABLED',':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $params['limit'] = $limit ;
        $params['orderby'] = 'sort DESC,id DESC' ;
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据文章分类ID返回单条文章信息
     * @param $cate_key
     * @param string $fields
     * @return mixed
     */
    public function getRowByCateKey($cate_key,$fields='*'){

        $params['cond'] = 'article_cate_key=:article_cate_key AND status=:status AND is_deleted=:is_deleted';
        $params['args'] = [':article_cate_key'=>$cate_key,':status'=>'ENABLED',':is_deleted'=>'N'];
        $params['fields'] = $fields ;
        $params['orderby'] = 'sort DESC,id DESC' ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }
}
