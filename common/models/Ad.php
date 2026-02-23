<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_ad".
 *
 * @property int $id
 * @property string $position_key 广告位关键字
 * @property int $position_id 广告位ID
 * @property string $title 名称
 * @property string $summary 简介
 * @property string $img_url 图片资源
 * @property string $link 链接地址
 * @property string $content 内容
 * @property int $sort 用户级别
 * @property string $is_del 是否删除
 * @property string $status 是否展示
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class Ad extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_ad';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['position_key', 'position_id'], 'required'],
            [['position_id', 'sort'], 'integer'],
            [['content'], 'string'],
            [['create_time', 'modify_time'], 'safe'],
            [['position_key', 'status'], 'string', 'max' => 50],
            [['title', 'summary', 'img_url', 'link'], 'string', 'max' => 255],
            [['is_del'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'position_key' => 'Position Key',
            'position_id' => 'Position ID',
            'title' => 'Title',
            'summary' => 'Summary',
            'img_url' => 'Img Url',
            'link' => 'Link',
            'content' => 'Content',
            'sort' => 'Sort',
            'is_del' => 'Is Del',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 根据ID返回广告位的信息
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
     * 根据广告位和限制数目 返回指定的广告
     * @param $position_key
     * @param $limit
     * @return mixed
     */
    public function getListByPositionKey($position_key,$limit =4){
        $params['cond'] = 'is_deleted=:is_deleted AND status=:status AND position_key=:position_key';
        $params['args'] = [':position_key'=>$position_key,':is_deleted'=>'N',':status'=>'ENABLED'];
        $params['limit'] = $limit ;
        $params['orderby'] = 'sort desc,id desc' ;
        $params['fields'] = 'id,title,img_url,link,content';
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());

        if($list){
            foreach($list as $k=>$v){
                $list[$k]['img_url'] = upload_url($v['img_url']);
            }
        }
        return $list ;

    }
}
