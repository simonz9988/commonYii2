<?php
namespace common\components;

use Yii;
use yii\log\Logger;
use yii\base\Exception;
class CommonLogger
{

    /**
     * 记录error日志
     * @param $e
     * @param $category
     */
    public function logError($e, $category = 'application')
    {
        $error_msg = $e instanceof Exception ? $e->getMessage() : $e;
        $post = $_POST ? Yii::$app->request->post() : [];
        $get = $_GET ? Yii::$app->request->get() : [];
        $str = '=====postData===>>>'.json_encode($post,JSON_UNESCAPED_UNICODE);
        $str .='=====getData===>>>'.json_encode( $get,JSON_UNESCAPED_UNICODE);
        $str .="=====message===>>>".$error_msg ;
        if($category != 'application') {
            Yii::getLogger()->log($str, Logger::LEVEL_ERROR, $category);
        }
        Yii::getLogger()->log($str, Logger::LEVEL_ERROR);
    }

    /**
     * 记录命令行error日志
     * @param $e
     * @param $category
     */
    public function logErrorConsole($e, $category = 'application')
    {
        $error_msg = $e instanceof Exception ? $e->getMessage() : $e;
        $str ="=====message===>>>".$error_msg ;
        if($category != 'application') {
            Yii::getLogger()->log($str, Logger::LEVEL_ERROR, $category);
        }
        Yii::getLogger()->log($str, Logger::LEVEL_ERROR);
    }

    /**
     * 记录info日志
     * @param $data
     * @param $category
     */
    public function logInfo($data, $category = 'application')
    {
        $str = '=====postData===>>>'.json_encode(Yii::$app->request->post(),JSON_UNESCAPED_UNICODE);
        $str .='=====getData===>>>'.json_encode( Yii::$app->request->get(),JSON_UNESCAPED_UNICODE);
        $str .="=====data===>>>".json_encode($data,JSON_UNESCAPED_UNICODE);
        Yii::getLogger()->log($str, Logger::LEVEL_INFO, $category);
    }

    /**
     * 记录warning日志
     * @param $data
     * @param $category
     */
    public function LogWarning($data, $category = 'application')
    {
        $str = '=====postData===>>>'.json_encode(Yii::$app->request->post(),JSON_UNESCAPED_UNICODE);
        $str .='=====getData===>>>'.json_encode( Yii::$app->request->get(),JSON_UNESCAPED_UNICODE);
        $str .="=====data===>>>".json_encode($data,JSON_UNESCAPED_UNICODE);
        Yii::getLogger()->log($str, Logger::LEVEL_WARNING, $category);
    }

    /**
     * 记录trace日志
     * @param $data
     * @param $category
     */
    public function LogTrace($data, $category = 'application')
    {
        $str = '=====postData===>>>'.json_encode(Yii::$app->request->post(),JSON_UNESCAPED_UNICODE);
        $str .='=====getData===>>>'.json_encode( Yii::$app->request->get(),JSON_UNESCAPED_UNICODE);
        $str .="=====data===>>>".json_encode($data,JSON_UNESCAPED_UNICODE);
        Yii::getLogger()->log($str, Logger::LEVEL_TRACE, $category);
    }

}
