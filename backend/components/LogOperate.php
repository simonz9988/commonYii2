<?php
namespace common\components;

use Yii;

/**
 *
 * 日志操作
 * @author haigang.chen
 * @date   2017-03-20 17:59:00
 */

class LogOperate
{
    /*
     * 后台日志操作
     * @param  array   $data         参数
     * @todo  $data  固定值：action|class_name|function_name|old_content|new_content|  冗余字段：redundancy_id 分别对应 order_id/goods_id或者其他值
     * @return bool
    */
    public function insert( $data)
    {
        $table_name = 'sea_operate_log';
        // 排序
        if($data['old_content']){
            ksort($data['old_content']);

        }
        if($data['new_content']){
            ksort($data['new_content']);
        }


        // 获取用户名
        $user_info = (isset(Yii::$app->session) ? Yii::$app->session->get('login_user') : []);
        $name = isset($user_info['username']) ? $user_info['username'] : '';

        // 数据重组
        $data['operate_user_name'] = $name;
        $data['operate_time'] = date("Y-m-d H:i:s");

        // 文件内容
        $file_data = array(
            'old_content' => $data['old_content'],
            'new_content' => $data['new_content'],
        );

        // 原数据库中的字段置为空
        $data['old_content'] = '';
        $data['new_content'] = '';

        $data['ip'] = $this->getClientIP();
        $db = Yii::$app->db->createCommand();
        $db->insert($table_name, $data)->execute();
        $insert_id = Yii::$app->db->getLastInsertID();

        // 目录
        $dir = '/runtime/operate_log/'.date("Y/m/d").'/';

        // 文件路径
        $file_path = $dir.$insert_id.'.txt';

        // 更新文件路径
        $db->update($table_name, ['file_path' => $file_path], "id='{$insert_id}'")->execute();

        // 文件目录
        $file_dir = Yii::getAlias('@backend').$dir;
        if(!is_dir($file_dir)){
            // 创建目录
            mkdir($file_dir, 0777, true);
            // 修改目录权限
            chmod($file_dir, 0777);
        }

        // 内容写入文件
        file_put_contents(Yii::getAlias('@backend').$file_path, json_encode($file_data, JSON_UNESCAPED_UNICODE));

    }

    private function getClientIP(){
        $ip_address = '0.0.0.0';
        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif (isset($_SERVER['HTTP_CDN_SRC_IP']))
        {
            $ip_address = $_SERVER['HTTP_CDN_SRC_IP'];
        }
        elseif (isset($_SERVER['REMOTE_ADDR']) AND isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (isset($_SERVER['REMOTE_ADDR']))
        {
            $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }

        if ($ip_address === 'Unknown')
        {
            $ip_address = '0.0.0.0';
            return $ip_address;
        }
        if (strpos($ip_address, ',') !== 'Unknown')
        {
            $x = explode(',', $ip_address);
            $ip_address = trim(end($x));
        }
        return $ip_address;
    }

}