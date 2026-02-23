<?php
namespace common\components;



class CsvHandler
{

    /**
     * 下载远程文件到本地
     * @param $url
     * @param $dir
     * @param string $filename
     * @return bool|string
     */
    function downRemoteFile($url, $dir, $filename=''){
        if(empty($url)){
            return false;
        }
        $ext = strrchr($url, '.');
        if($ext != '.csv'){
            echo "格式不支持！";
            return false;
        }

        $dir = realpath($dir);
        //目录+文件
        $filename = (empty($filename) ? '/'.time().''.$ext : '/'.$filename);
        $filename = $dir . $filename;

        //开始捕捉
        ob_start();
        readfile($url);
        $content = ob_get_contents();

        ob_end_clean();
        $size = strlen($content);
        $fp2 = fopen($filename , "a");
        fwrite($fp2, $content);
        fclose($fp2);

        return $filename;
    }

    /**
     * 读取csv指定的行
     * @param $csvfile
     * @param $lines
     * @param int $offset
     * @return array|bool
     */
    function csvGetLines($csvfile, $lines, $offset = 0) {
        if(!$fp = fopen($csvfile, 'r')) {
            return false;
        }
        $i = $j = 0;
        while (false !== ($line = fgets($fp))) {
            if($i++ < $offset) {
                continue;
            }
            break;
        }
        $data = array();
        while(($j++ < $lines) && !feof($fp)) {
            $data[] = fgetcsv($fp);
        }
        fclose($fp);
        return $data;
    }

    /**
     * 获取csv总行数
     * @param $csvfile
     * @return int
     */
    function csvGetTotalLine($csvfile){
        $file = fopen($csvfile,'r');
        $row = 0;
        while ($data = fgetcsv($file)) {
            $row++;
        }

        return $row;
    }


}
