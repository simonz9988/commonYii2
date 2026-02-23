<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "sea_mh_ht_params".
 *
 * @property int $id
 * @property string $field_key 对应的参数的key值(程序定义死)
 * @property string $detail 对应的具体内容
 * @property string $status ENABLED DISABLED
 * @property string $is_deleted 是否删除 Y-已删除 N-未删除
 * @property string $create_time 创建时间
 * @property string $modify_time 最后更新时间
 */
class MhHtParams extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_mh_ht_params';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time', 'modify_time'], 'safe'],
            [['field_key', 'detail', 'status'], 'string', 'max' => 255],
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
            'field_key' => 'Field Key',
            'detail' => 'Detail',
            'status' => 'Status',
            'is_deleted' => 'Is Deleted',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }

    /**
     * 获取枚举类型
     * @return array
     */
    public function returnTypeList(){
        return [
            'CHANPINBIE'=>'产品别',
            'PAIHAO'=>'牌号',
            'CHANPXINGTAI'=>'产品形态',
            'FUKUANZHUANGTAI'=>'付款状态',
            'JIAOHUOZHUANGTAI'=>'交货状态',
            'LAILIAOXINGTAI'=>'来料形态',
            'JIAGONGXINGTAI'=>'加工形态',
            'CHULIAOXINGTAI'=>'出料形态',
            'WEITUOXINGTAI'=>'委托形态',
            'BUMEN'=>'部门',
            'KUANXIANG'=>'款项',
            'FAPIAOZHUANGTAI'=>'发票状态',
            'LIUSHUIXIANGMU'=>'流水项目',
            'SHOURUZHICHU'=>'收入/支出',
            'XIANGMU'=>'项目',
            'BAOZHIFANGSHI'=>'报支方式',
            'ZHICHENGGONGXU'=>'制程工序',
            'SHOUFALIAOCHANPZT'=>'收发料产品状态',
            'XIAOSHOUDAIGONG'=>'销售/代工',
            'CHANCHENGPCHANPMINGCHENG'=>'产成品-产品名称',
            'BAOXIAOFANGSHI'=>'报销方式',
            'BAOXIAOZHUANGTAI'=>'报销状态',
        ];
    }

    /**
     * 根据ID返回单条信息
     * @param $id
     * @param string $fields
     * @return array|bool
     */
    public function getInfoById($id,$fields='*'){
        $params['cond'] = 'id=:id';
        $params['args'] = [':id'=>$id];
        $params['fields'] = $fields ;
        $info = $this->findOneByWhere(self::tableName(),$params,self::getDb());
        return $info ;
    }

    /**
     * 判断指定的参数值是否已经重复录入
     * @param $field_key
     * @param $detail
     * @return mixed
     */
    public function checkExistsByKeyAndDetail($field_key,$detail){

        $params['cond'] = 'field_key=:field_key AND detail=:detail AND is_deleted=:is_deleted';
        $params['args'] = [':field_key'=>$field_key,':detail'=>$detail,':is_deleted'=>'N'];
        return $this->findOneByWhere(self::tableName(),$params,self::getDb());

    }

    /**
     * 保存信息
     * @param $id
     * @param $post_data
     * @return mixed
     */
    public function savePostData($id,$post_data){

        if($id){
            $info = $this->getInfoById($id);
            if($info['detail'] !=$post_data['detail']){
                // 判断是否存在
                $check_exists = $this->checkExistsByKeyAndDetail($post_data['field_key'],$post_data['detail']);
                if($check_exists){
                    $this->setError('200043');
                    return false ;
                }
            }
        }else{

            // 判断是否存在
            $check_exists = $this->checkExistsByKeyAndDetail($post_data['field_key'],$post_data['detail']);
            if($check_exists){
                $this->setError('200043');
                return false ;
            }
        }

        $add_data['field_key'] = $post_data['field_key'];
        $add_data['detail'] = $post_data['detail'];
        $add_data['status'] = $post_data['status'];
        $add_data['modify_time'] = date('Y-m-d H:i:s');

        if(!$id){
            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['is_deleted'] = 'N';
            return $this->baseInsert(self::tableName(),$add_data);
        }else{
            return $this->baseUpdate(self::tableName(),$add_data,'id=:id',[':id'=>$id]);
        }
    }

    /**
     * 获取key 值名称
     * @param $field_key
     * @return mixed
     */
    public function getFieldKeyName($field_key){
        $list = $this->returnTypeList();
        return isset($list[$field_key]) ? $list[$field_key] : '';
    }

    /**
     * 根据key值返回所有key值列表
     * @param $field_key
     * @return mixed
     */
    public function getDetailListByKey($field_key){
        $params['cond'] = 'is_deleted=:is_deleted AND status=:status AND field_key=:field_key';
        $params['args'] = [':is_deleted'=>'N',':status'=>'ENABLED',':field_key'=>$field_key];
        $list = $this->findAllByWhere(self::tableName(),$params,self::getDb());
        return $list ;
    }

    /**
     * 根据ID返回具体的值
     * @param $id
     * @return mixed
     */
    public function getDetailById($id){

        $info = $this->getInfoById($id,'detail');
        return $info ? $info['detail'] : '';
    }

    /**
     * 批量插入信息
     * @param $type
     * @param $insert_data
     * @return mixed
     */
    public function doBatchInsert($type,$insert_data){

        $insert_key = [] ;
        $obj = new MhHtAgent();

        foreach($insert_data as $data){
            $add_data = [] ;
            foreach($insert_key as $k=>$v){
                $add_data[$v] = $data[$k];
            }

            $add_data['create_time'] = date('Y-m-d H:i:s');
            $add_data['modify_time'] = date('Y-m-d H:i:s');
            $this->baseInsert($obj::tableName(),$add_data);
        }

    }
}
