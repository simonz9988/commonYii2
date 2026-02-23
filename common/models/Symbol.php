<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sdb_symbol".
 *
 * @property string $id
 * @property string $key
 * @property int $is_open
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class Symbol extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sdb_symbol';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_okex');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_open'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['key'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'is_open' => 'Is Open',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    /**
     * 判断下跌预警
     */
    public function checkDownNotice($symbol){
        $site_config_model = new SiteConfig();
        $num =$site_config_model->getByKey('down_amount_minutes');
        $num = intval($num);

        $check_symbol_arr = $site_config_model->getByKey('check_down_symbol_arr');
        $check_symbol_arr = explode(',',$check_symbol_arr);
        if(in_array($symbol,$check_symbol_arr)){
            $params['cond'] = 'symbol =:symbol';
            $params['args'] = [':symbol'=>$symbol];
            $params['orderby'] = ' id DESC ' ;
            $params['limit'] = $num ;
            $list = $this->findAllByWhere('sdb_symbol_price',$params,self::getDb());
            $price_arr = array();
            if($list){
                foreach($list as $v){
                    $price_arr[] = $v['price'];
                }
            }

            $max = max($price_arr);
            $min = min($price_arr);
            $percent = $max/$min;
            $down_notice_percent = $site_config_model->getByKey('down_notice_percent');
            $down_notice_percent_rst = 1+$down_notice_percent/100;
            if($percent >=$down_notice_percent_rst && $price_arr[0]<$price_arr[1]){
                $message = $symbol.':已经下跌超过百分之'.$down_notice_percent;
                $message .= '最高价:'.$max;
                $message .= '最低价:'.$min;
                $message .= '最新价:'.$price_arr[0];
                $down_notice_send_messages_times = $site_config_model->getByKey('down_notice_send_messages_times');
                do_send_dingding_by_time($message,$down_notice_send_messages_times);
            }

            //每隔15分钟推送所有
        }
    }
}
