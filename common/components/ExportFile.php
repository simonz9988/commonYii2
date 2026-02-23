<?php

namespace common\components;
use yii;

/**
 * 根据传入的内容导出excel或csv文件
 * User: dean.zhang
 * Date: 2017/6/29
 * Time: 9:48
 */

class ExportFile
{


    public function download($data, $fileName)
    {
        $fileType = explode('.',$fileName)[1];
        $fileName = $this->_charset($fileName);
        if( $fileType == 'xls' || $fileType == 'xlsx')
        {
            header("Content-Type: application/vnd.ms-excel; charset=GB2312");
            header("Content-Disposition: attachment; filename=\"" . $fileName. "\"");
            $str = '';
            foreach ($data as $row) {
                $str_arr = array();
                foreach ($row as $column) {
                    $str_arr[] = '"' . str_replace('"', '""', $column) . '"';
                }
                $str.=implode(chr(9), $str_arr) . PHP_EOL;
            }
            echo $this->_charset($str);
        }else if($fileType == 'csv')
        {
            header("Content-Type: application/vnd.ms-excel; charset=GB2312");
            header("Content-Disposition: attachment;filename=".$fileName);
            $str = '';
            foreach ($data as $row) {
                $str_arr = array();
                foreach ($row as $column) {
                    $str_arr[] = '"' . str_replace('"', '""', $column) . '"';
                }
                $str.=implode(',', $str_arr) . PHP_EOL;
            }
            echo $this->_charset($str);
        }else
        {
            exit("Not supported".$fileType);
        }

    }

    private function _charset($data)
    {
        if(!$data)
        {
            return false;
        }
        if(is_array($data))
        {
            foreach($data as $k=>$v)
            {
                $data[$k] = $this->_charset($v);
            }
            return $data;
        }
        return mb_convert_encoding($data,'GB2312','utf-8');
        //return iconv('utf-8', 'GB2312', $data);//utf-8转化为gbk
    }

}