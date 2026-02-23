<?php

namespace backend\models;

include_once dirname(dirname(ROOT_PATH)) . '/vendor/excel/Classes/PHPExcel/IOFactory.php';
use Yii;

/**
 * This is the model class for table "sea_coin_address_value".
 *
 * @property string $id
 * @property string $address 地址
 * @property string $type 类型(BTC/ETH/TOKEN-需要补充具体类型)
 * @property string $value 余额
 * @property string $create_time 创建时间
 * @property string $modify_time 更新时间
 */
class CoinAddressValue extends \common\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sea_coin_address_value';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['value'], 'number'],
            [['create_time', 'modify_time'], 'safe'],
            [['address'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'address' => 'Address',
            'type' => 'Type',
            'value' => 'Value',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }


    /**
     * 获取用户导入的订单数据
     * @param $user_id
     * @param $parseColumn
     * @return array
     */
    public function getImportOrderDataByUser($user_id,$parseColumn=2){

        $file =  '/upload/import_order/' .$user_id . '_import_order.xls';
        $file_real_path = ROOT_PATH.$file;
        $data = [];

        if(file_exists($file_real_path)){
            $reader = \PHPExcel_IOFactory::createReader('Excel5'); //设置以Excel5格式(Excel97-2003工作簿)

            $PHPExcel = $reader->load($file_real_path); // 载入excel文件

            $sheet = $PHPExcel->getSheet(0); // 读取第一個工作表

            $maxRow = $sheet->getHighestRow(); // 取得总行数

            for ($i = 2; $i <= $maxRow; $i++) {
                if ($parseColumn == 1) {
                    $data[] = $sheet->getCell('A' . $i)->getValue();
                } else {
                    $tmp = [];
                    for ($ascii = 0; $ascii < $parseColumn; $ascii++) {
                        $tmp[] = $sheet->getCell(chr(65 + $ascii) . $i)->getValue();
                    }
                    $data[] = $tmp;
                }
            }
        }


        $info = ['status' => 'success', 'code' => '1', 'data' => $data,'file_path'=>$file];
        return $info ;
    }

    /**
     * 获取类型列表
     */
    public function getTypeList(){
        return [
            'BTC'=>'BTC',
            'ETH'=>'ETH',
            'OMNI_USDT'=>'Omin Usdt',
            'ERC20_USDT'=>'Erc20 Usdt',
            'TRC20_USDT'=>'Trc20 Usdt',
            'OTHER_TOEKN'=>'Other Token',
        ];
    }


    /**
     * 判断新增数据是否正确
     * @param $add_data
     * @return bool
     */
    public function checkAddData($add_data){
        $card_no = $add_data['card_no'];
        $params['where_arr']['card_no'] = $card_no ;
        $params['where_arr']['status'] = 'ENABLED';
        $params['return_field'] = 'id';
        $info = $this->findOneByWhere($this->tableName(),$params) ;
        if($info){
            return false ;
        }
        return true;
    }



    /**
     * 校验订单导入基础数据的有效性
     * @param $add_user_id
     * @param $data
     * @return array
     */
    public function checkImportBaseInfo($data){

        set_time_limit(0);

        $add_base_data = [];

        //错误数据数目
        $error_num = 0 ;

        foreach($data as $k=>$v){
            $address  = trim($v[0]);
            $card_password  = trim($v[1]);
            $add_data = compact('address','card_password');

            //判断卡号是否存在
            $add_data['is_allowed'] = true;


            $add_base_data[] = $add_data ;

        }
        $rst_data['list'] = $add_base_data ;
        $rst_data['total_num'] = count($add_base_data) ;
        $rst_data['error_num'] = $error_num ;

        return ['code'=>1,'msg'=>getErrorDictMsg(1),'data'=>$rst_data];

    }

    /**
     * 获取卡号信息
     * @param $card_no
     * @param $password
     * @return array
     */
    public function getInfoByPassword($card_no,$password){
        $params['where_arr']['card_no']  =$card_no ;
        $params['where_arr']['card_password']  = md5($password) ;
        $params['where_arr']['status']  = 'ENABLED' ;
        return  [];
    }

    /**
     * 根据批次号获取总额
     * @param $batch_num
     * @return int
     */
    public function getTotal($batch_num){
        if($batch_num){
            $params['where_arr']['batch_num'] = $batch_num ;
        }
        $params['return_type'] = 'row';
        $params['return_field'] = ' sum(value) as total' ;
        $info = $this->findByWhere(self::tableName(),$params);
        return $info ? $info['total'] : 0 ;
    }

}
