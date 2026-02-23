<?php
namespace console\controllers;

use yii\console\Controller;

/**
 * Cmd controller
 */
class CmdBaseController extends Controller
{

    /**
     * 检查脚本是否已经在执行 有且仅有一个能执行
     * @param $file_name
     */
    public function checkShell($file_name)
    {
        exec("ps aux|grep '{$file_name}'",$res);
        if($res && count($res)>3 ){
            exit;
        }
    }

    /**
     * 获取脚本action
     * @return string
     */
    public function getShellAction(){
        $shell = '/'.$this->action->controller->id . '/' . $this->action->id;
        return $shell;
    }

}
