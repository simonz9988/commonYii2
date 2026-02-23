<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_remote_news".
 *
 * @property int $id
 * @property int $remote_id 主业务ID
 * @property string $title 标题
 * @property string $type 类型(NEWS-快讯)
 * @property string $content 内容
 * @property int $issuetime 实际内容发生的时间
 * @property int $goodhits 利好
 * @property int $badhits 利空
 * @property int $lookonhits 观望
 * @property string $sourceurl 来源地址
 * @property string $shareurl 分享地址
 * @property string $create_time 创建时间
 * @property string $modify_time 更新时间
 */
class RemoteNews extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_remote_news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remote_id', 'issuetime', 'goodhits', 'badhits', 'lookonhits'], 'integer'],
            [['content'], 'string'],
            [['create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['title', 'type', 'sourceurl', 'shareurl'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'remote_id' => 'Remote ID',
            'title' => 'Title',
            'type' => 'Type',
            'content' => 'Content',
            'issuetime' => 'Issuetime',
            'goodhits' => 'Goodhits',
            'badhits' => 'Badhits',
            'lookonhits' => 'Lookonhits',
            'sourceurl' => 'Sourceurl',
            'shareurl' => 'Shareurl',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

     public function checkExistsByRemoteId($remote_id,$type){

        $params['cond'] = ' remote_id =:remote_id AND type=:type';
        $params['args'] = [':remote_id'=>$remote_id,':type'=>$type];
        $params['fields'] = 'id';
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ? true: false ;
     }

    /**
     * 根据列表和类型下单新闻信息
     * @param $list
     * @return mixed
     */
    public function downloadQuickNews($list){
        if(!$list){
            return false ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');

        $type = 'QUICK_NEWS';

        foreach($list as $v){

            // 判断是否已存在
            $check_exists = $this->checkExistsByRemoteId($v['id'],($type)) ;
            if($check_exists){
                continue ;
            }

            $add_data['remote_id'] = $v['id'] ;
            $add_data['type'] = ($type) ;
            $add_data['title'] = $v['title'];
            $add_data['content'] = $v['content'];
            $add_data['issuetime'] = $v['issuetime'];
            $add_data['goodhits'] = $v['goodhits'];
            $add_data['badhits'] = $v['badhits'];
            $add_data['lookonhits'] = $v['lookonhits'];
            $add_data['sourceurl'] = $v['sourceurl'];
            $add_data['shareurl'] = $v['shareurl'];
            $add_data['create_time'] = $now;
            $add_data['modify_time'] = $now;
            $this->baseInsert(self::tableName(),$add_data);
        }
        return true ;
    }

    /**
     * 根据类型返回指定分页的内容
     * @param $type
     * @param $curr_page
     * @param $page_num
     * @param $fields
     * @return mixed
     */
    public function getListByType($type,$curr_page,$page_num,$fields="*"){

        $params['cond'] = ' type=:type';
        $params['args'] = [':type'=>$type];
        $params['page']['curr_page'] = $curr_page ;
        $params['page']['page_num'] = $page_num ;
        $params['orderby'] = 'issuetime DESC';
        $params['fields'] = $fields ;
        $list  = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 同步平台公告信息
     * @param $list
     * @return mixed
     */
    public function downloadPlatformPublic($list){

        if(!$list){
            return false ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');

        $type = 'PLATFORM_PUBLIC';

        foreach($list as $v){
            // 判断是否已存在
            $check_exists = $this->checkExistsByRemoteId($v['id'],$type) ;
            if($check_exists){
                continue ;
            }

            $add_data['remote_id'] = $v['id'] ;
            $add_data['type'] = ($type) ;
            $add_data['title'] = $v['title'];
            $add_data['summary'] = $v['summary'];
            $add_data['exchangecode'] = $v['exchangecode'];
            $add_data['avatar'] = $v['avatar'];
            $add_data['exchangename'] = $v['exchangename'];
            $add_data['issuetime'] = $v['issuetime'];
            $add_data['content'] = '';
            $add_data['goodhits'] = 0;
            $add_data['badhits'] = 0;
            $add_data['lookonhits'] = 0;
            $source_url = 'https://m.feixiaohao.com/news/'.$v['id'].'.html';
            $add_data['sourceurl'] = $source_url;
            $add_data['shareurl'] = $source_url;
            $add_data['create_time'] = $now;
            $add_data['modify_time'] = $now;
            $this->baseInsert(self::tableName(),$add_data);
        }
    }


    /**
     * 同步平台公告信息
     * @param $list
     * @return mixed
     */
    public function downloadNews($list){

        if(!$list){
            return false ;
        }

        // 当前时间
        $now = date('Y-m-d H:i:s');

        $type = 'NEWS';

        foreach($list as $v){
            // 判断是否已存在
            $check_exists = $this->checkExistsByRemoteId($v['id'],$type) ;
            if($check_exists){
                continue ;
            }

            $add_data['remote_id'] = $v['id'] ;
            $add_data['type'] = ($type) ;
            $add_data['title'] = $v['title'];
            $add_data['coverurl'] = $v['coverurl'];
            $add_data['content'] = $v['content'];
            $add_data['issuetime'] = $v['issuetime'];
            $add_data['goodhits'] = $v['goodhits'];
            $add_data['badhits'] = $v['badhits'];
            $add_data['lookonhits'] = $v['lookonhits'];
            $source_url = 'https://m.feixiaohao.com/news/'.$v['id'].'.html';
            $add_data['sourceurl'] = $source_url;
            $add_data['shareurl'] = $source_url;
            $add_data['author_name'] = $v['username'];
            $add_data['create_time'] = $now;
            $add_data['modify_time'] = $now;

            $this->baseInsert(self::tableName(),$add_data);
        }
    }
}
