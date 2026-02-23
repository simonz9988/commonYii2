<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_view_count".
 *
 * @property string $id
 * @property int $app_id 软件ID
 * @property string $app_name 软件名称
 * @property int $total 浏览总次数
 * @property string $create_time 创建时间
 * @property string $update_time 更新时间
 */
class ViewCount extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_view_count';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_ms115');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'total'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['app_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_id' => 'App ID',
            'app_name' => 'App Name',
            'total' => 'Total',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }

    public function getInfoByAppId($app_id){
        $params['cond'] = 'app_id=:app_id';
        $params['args'] = [':app_id'=>$app_id];
        return $this->findOneByWhere($this->tableName(),$params,self::getDb());
    }

    public function addByIdAndName($app_id,$app_name){

        $add_data['app_id'] = $app_id ;
        $add_data['app_name'] = $app_name ;
        $add_data['total'] = 1 ;
        $add_data['create_time'] = date('Y-m-d H:i:s');
        $add_data['update_time'] = date('Y-m-d H:i:s');
        return $this->baseInsert($this->tableName(),$add_data,'db_ms115');
    }

    public function updateTotal($row){
        if($row){
            $id = $row['id'];
            $total = $row['total'] + 1 ;
            $update_data['total'] = $total;
            $this->baseUpdate($this->tableName(),$update_data,'id=:id',[':id'=>$id],'db_ms115');
        }
    }
}
