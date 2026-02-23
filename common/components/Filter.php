<?php
namespace common\components;

/**
 *
 * comment:Filter类
 * author: wangyao
 * time: 2011-10-17
 * version: 1.0
 *
 * * Filter 类
 *
 * 概述：过滤一切用户的输入操作
 *
 * 服务：
 * - 过滤GET、POST提交的数据；
 *
 * 过滤返回数据分为两类：BOOL值（true | false） 或 过滤后的数据
 *
 * 过滤(替换)函数前缀 F_ 验证合法性C_
 *
 * 方法列表：
 * 		检查变量类型 	C_var_type($var, $type = '')
 * 		检查名称（中文、字母、数字）	C_name($var)
 * 		检查是否为大于等于0整数C_int($var)
 * 		检查字符串长度	C_strlen($string, $minlen = 0, $maxlen = 0)
 * 		检查数字大小	C_intsize($number, $minsize, $maxsie)
 * 		检查string只能为中文	C_onlyzh($string)
 * 		检查邮箱	C_email($var, $minlen = 1, $maxlen = 20)
 * 		检查日期	C_date($var, $len = 10)	默认格式****-**-**
 * 		日期大小比较	C_compare_date($start_date, $end_date)
 * 		检查固定电话号码	C_telphone($var, $minlen = 11, $maxlen = 12)
 * 		检查移动电话号码	C_mobile($var)
 * 		检查QQ号码	C_qq($var)
 * 		检查身份证	C_creditNo($var)
 * 		过滤敏感词	C_rep_illegal_words($var, $isRep = false)
 * 		检查是否是爬虫	C_isSpider()
 * 		检查允许IP	C_allowIP($ip, $iparray=array())
 * 		转义特殊字符	F_conFilter($var)
                $list = array(
                    '<' 	=> '&#60;',
                    '>' 	=> '&#62;',
                    "'" 	=> '&#39;',
                    '"' 	=> '&#34;',
                    ',' 	=> '&#44;',
                    '(' 	=> '&#40;',
                    ')' 	=> '&#41;',
                    '?'		=> '&#63;',
                    '\\' 	=> '&#92;',
                );
 *		 转换特殊字符	F_htmlspecialchars($var)
                $list = array(
                    '&' => '&amp;'
                  '"' => '&quot;'
                  ''' => '&#039;'
                  '<' => '&lt;'
                  '>' => '&gt;'
               )
 *		转换 空格和换行和制表符	F_nlSpaceSwitch($var)
                $list = array(
                    '\t' => '&nbsp;',
                    ' ' => '&nbsp;',
                    '\\' => '&#92;',
                );
 *		过滤危险的HTML内容	F_unsafeHtmlFilter($var)
                '<script[^>]*?>.*?<\/script>',
                '<html[^>]*?>.*?<body[^>]*?>',
                '<\/body>.*?<\/html>'si",
                '<style[^>]*?>.*?<\/style>',
                '<link[^>]*?\s*[\/]?>'si",
                '<iframe[^>]*?>.*?<\/iframe>',
                '<form[^>]*?>(.*?)<\/form>',
                '<textarea[^>]*?>.*?<\/textarea>',
                '\s*id\s*=\s*[\"|\'].*?[\"|\']',
                '\s* clas\s*s\s*=\s*[\"|\'].*?[\"|\']',
                '<!--.*?-->'si,
 * 		去除标签	F_htmlWebFilter($var)
                去除或编码特殊字符。 例如: <br>等
 *		 特殊内容过滤（以上方法的集合）F_contentFilter($type, $var)
                switch ($type) {
                    case '#s_z': // 如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &#60; 】
                        if ($this->C_var_type($var, 'string')) {
                            return $this->F_conFilter($var);
                        }
                        break;
                    case '#s_t': // 如果存在HTML标签，剔除所有标签，保留其中文本，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt; 】
                        if ($this->C_var_type($var, 'string')) {
                            return $this->F_htmlWebFilter($var);
                        }
                        break;
                    case '#s_zb':// 如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt; 】, 并且把换行转换成<br>
                        if ($this->C_var_type($var, 'string')) {
                            return $this->F_nlSpaceSwitch($this->_conFilter($var));
                        }
                        break;
                    case '#s_fh':// 如果存在HTML标签，过滤掉有威胁的HTML标签【适用于所见即所得的BLOG等】
                        if ($this->C_var_type($var, 'string')) {
                            return $this->F_unsafeHtmlFilter($var);
                        }
                        break;
                    default:
                        return $var;
                        break;
                }
 */

class Filter
{
    public function init()
    {

    }
    /**
     * 检查变量类型
     * 如果type为空，返回$type, 否则返回bool
     * @return bool
     */
    public function C_var_type($var, $type = '')
    {
        if(empty($type)) return gettype($var);
        if(gettype($var) !== $type) return false;
        return true;
    }

    /**
     * 检查名称（中文、字母、数字）
     * 经过测试，老的代码只检测了中文字符，但是并未隔离中文标点等特殊字符，故换了一个正则只允许汉字
     *
     * @return bool
     */
    public function C_name($var)
    {
        // return preg_match('/^([A-Za-z0-9\x80-\xff\w])+$/', $var);
        return preg_match('/^[\x{4e00}-\x{9fa5}A-Za-z0-9]+$/u', $var);
    }

    /**
     * 检查字符串（字母、数字）
     *
     * @return bool
     */
    public function C_tring($var)
    {
        return preg_match('/^([A-Za-z0-9])+$/', $var);
    }

    /**
     * 检查是否为大于0整数
     *
     * @return bool
     */
    public function C_int($var)
    {
        if(empty($var) || !preg_match('/^[1-9]+[0-9]*$/', $var)) return false;
        return true;
    }

    /**
     * 检查正整数
     * @param $var mixed
     * @return bool
     */
    public function C_intWithZero($var)
    {
        if(!preg_match('/^[0-9]+$/', $var)) return false;
        return true;
    }

    /**
     * 检查整数,包含负整数、零、正整数
     * @param $var mixed
     * @return bool
     */
    public function C_integer($var)
    {
        if(!preg_match('/^\-?[0-9]*$/', $var)) return false;
        return true;
    }

    /**
     * 检查字符串长度
     * @param $string string 变量值
     * @param $minlen int 最小长度
     * @param $maxlen int 最大长度
     * @param $encoding string 字符串编码
     * @param $covert int 是否需要转码
     * @param $tocoding string 目标编码
     *
     * @return bool
     */
    public function C_strlen($string, $minlen = 0, $maxlen = 0, $encoding='utf-8', $convert=0, $tocoding='gb2312')
    {
        if ($convert) {
            $string = iconv($encoding,$tocoding,$string);
            $strlen = strlen($string);
        } else {
            $strlen = strlen($string);
        }

        if(!$this->C_var_type($string, 'string') || ($strlen < $minlen || $strlen > $maxlen)) return false;
        return true;
    }

    /**
     * 检查数字大小
     * minsize maxsize 不为空
     * @return bool
     */
    public function C_intsize($number, $minsize, $maxsize)
    {
        if (intval($number) > $maxsize || intval($number) < $minsize) return false;
        return true;
    }

    /**
     * 检查string只能为中文
     *
     * @return bool
     */
    public function C_onlyzh($var)
    {
        return preg_match('/^([\x80-\xff\w])+$/', $var);
    }

    /**
     * 检查邮箱
     *
     * @return bool
     */
    public function C_email($var)
    {
        if (!filter_var($var, FILTER_VALIDATE_EMAIL)) return false;
        return true;
    }

    /**
     * 检查日期
     * 默认格式****-**-**
     * @return bool
     */
    public function C_date($var, $len = 10)
    {
        if (!$this->C_strlen($var, $len, $len) || !preg_match('/^([1-2][0,9]{3}[-][0-1][0-9][-][0-2][0-9])$/', $var)) return false;
        return true;
    }

    /**
     * 日期大小比较
     * 默认格式****-**-**
     * @return bool
     */
    public function C_compare_date($start_date, $end_date)
    {
        if (strtotime($end_date) < $start_date) return false;
        return true;
    }

    /**
     * 检查固定电话号码
     * 默认格式****-********
     * @return bool
     */
    public function C_telphone($var, $minlen = 11, $maxlen = 12)
    {
        if(!$this->C_strlen($var, $minlen, $maxlen) || !preg_match('/^[0-9]{3,4}-?[0-9]{7,8}$/', $var)) return false;
        return true;
    }

    /**
     * 检查移动电话号码
     * 默认格式13568955689
     * @return bool
     */
    public function C_mobile($var)
    {
        if(!preg_match('/^1(3|4|5|7|8|9)\d{9}$/', $var)) return false;
        return true;
    }

    /**
     * 检查QQ号码
     * 默认格式1356895
     * @return bool
     */
    public function C_qq($var)
    {
        return preg_match('/^[1-9]\d{4,12}$/', $var);
    }

    /**
     * 检查价格
     * @return bool
     */
    public function C_price($var) {
        return preg_match('/^(\d\d{0,9})(\.\d{1,2})?$/', $var);
    }

    /**
     * 检查身份证
     * 默认格式2112521511224
     * @return bool
     */
    public function C_creditNo($var,$flag=false)
    {
        $vCity = array(
            '11','12','13','14','15','21','22',
            '23','31','32','33','34','35','36',
            '37','41','42','43','44','45','46',
            '50','51','52','53','54','61','62',
            '63','64','65','71','81','82','91'
        );

        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $var)) return false;

        if (!in_array(substr($var, 0, 2), $vCity)) return false;

        $var = preg_replace('/[xX]$/i', 'a', $var);
        $vLength = strlen($var);

        if ($vLength == 18)
        {
            $vBirthday = substr($var, 6, 4) . '-' . substr($var, 10, 2) . '-' . substr($var, 12, 2);
        } else {
            $vBirthday = '19' . substr($var, 6, 2) . '-' . substr($var, 8, 2) . '-' . substr($var, 10, 2);
        }

        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($vLength == 18)
        {
            $vSum = 0;

            for ($i = 17 ; $i >= 0 ; $i--)
            {
                $vSubStr = substr($var, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
            }

            if($vSum % 11 != 1) return false;
        }
        if($flag) return date('Y')-substr($var, 6, 4);

        return true;
    }

    /**
     * 过滤敏感词
     * 默认格式 $isRep 是否替换，默认不替换
     * @return bool | string
     */
    public function C_rep_illegal_words($var, $isRep = false)
    {
        $words = $this->getIllWords();
        if($isRep) {
            return str_replace($words,"***",$var);
        } else {
            if(in_array($var, $words)) return false;
            return true;
        }
    }

    /**
     *
     * 对字符串数据进行过滤，将数据中的非法字符从数据中剔除 ...
     * @param array $params
     * @return Array;
     */
    public function F_illegal_words($params){
        $words = $this->getIllWords();
        return array_diff((array)$params, $words);
    }

    /**
     * 检查是否是爬虫
     * 返回值：true→爬虫 false→不是爬虫
     * @return bool
     */
    public function C_isSpider() {
        if(!defined('IS_SPIDER')) {
            $kw_spiders = 'bot|crawl|spider|slurp|sohu-search|lycos|robozilla';
            $kw_browsers = 'MSIE|Netscape|Opera|Konqueror|Mozilla';
            if(!strexists($_SERVER['HTTP_USER_AGENT'], 'http://') && preg_match("/($kw_browsers)/i", $_SERVER['HTTP_USER_AGENT'])) {
                define('IS_SPIDER', FALSE);
            } elseif(preg_match("/($kw_spiders)/i", $_SERVER['HTTP_USER_AGENT'])) {
                define('IS_SPIDER', TRUE);
            } else {
                define('IS_SPIDER', FALSE);
            }
        }
        return IS_SPIDER;
    }

    /**
     * 检查允许IP
     *
     * @return bool
     */
    public function C_allowIP($ip, $iparray=array())
    {
        return in_array($ip, $iparray);
    }

    /**
     * C_password($pwd)
     * 验证密码格式
     * 需求为"情输入6-32个字符，必须是字母、数字或符号的组合"，且必须同时满足两种格式。
     * 这里的逻辑为反向判断，只要满足全字母、全数字、全符号即为假，反则即为真
     *
     * @param string $pwd 密码
     * @return bool
     */
    public function C_password($pwd) {
        if (!$this->C_strlen($pwd, 6, 32)) {
            return false;
        }

        if (preg_match("/^(\d+|[a-zA-Z]+|[^a-zA-Z0-9]+)$/", $pwd)) {
            return false;
        }

        return true;
    }

    /**
     * 转义特殊字符
     *
     * @param  string	$var 需要转义的内容
     *
     * @return string
     */
    public function F_conFilter($var)
    {
        $list = array(
            '<' 	=> '&#60;',
            '>' 	=> '&#62;',
            "'" 	=> '&#39;',
            '"' 	=> '&#34;',
            ',' 	=> '&#44;',
            '(' 	=> '&#40;',
            ')' 	=> '&#41;',
            '?'		=> '&#63;',
            '\\' 	=> '&#92;',
        );
        return strtr($var, $list);
    }

    /**
     * 转换特殊字符
     *
     * '&' => '&amp;'
     * '"' => '&quot;'
     * ''' => '&#039;'
     * '<' => '&lt;'
     * '>' => '&gt;'
     * @param string $var
     * @return string
     */
    public function F_htmlspecialchars($var)
    {
        return htmlspecialchars($var,ENT_QUOTES,'utf-8');
    }

    /**
     * 替换特殊字符
     *
     * '&' => '&amp;'
     * '"' => '&quot;'
     * ''' => '&#039;'
     * '<' => '&lt;'
     * '>' => '&gt;'
     * @param string $var
     * @return string
     */
    public function F_replaceChar($var)
    {
        //$search = array ("<",">","@","#","%","^","&","*","(",")","~",";");
        $search = array ("/</","/>/",'/\$/',"/!/","/%/","/\^/","/&/","/\*/","/\(/","/\)/","/~/","/;/","/\|/","/\[/", "/\]/", "/\+/",  "/\?/", "/\\\/", "/\{/", "/\}/");
        $replace = array ('');
        return preg_replace ($search, $replace, $var);
    }

    /**
     * 转换 空格和换行和制表符
     *
     * @param  string	$var 需要转换的内容
     *
     * @return string
     */
    public function F_nlSpaceSwitch($var)
    {
        $list = array(
            '\t' => '&nbsp;',
            ' ' => '&nbsp;',
            '\\' => '&#92;',
            '\n' => '',
            '\r\n' => '',
        );
        return nl2br(strtr($var, $list));
    }

    /**
     * 过滤危险的HTML内容
     *
     * @param  string	$var 需要过滤的内容
     * @return string
     */
    public function F_unsafeHtmlFilter($var)
    {
        /*
        $search = array (
            "'<script[^>]*?>.*?<\/script>'si",
            "'<html[^>]*?>.*?<body[^>]*?>'si",
            "'<\/body>.*?<\/html>'si",
            "'<style[^>]*?>.*?<\/style>'si",
            "'<link[^>]*?\s*[\/]?>'si",
            "'<iframe[^>]*?>.*?<\/iframe>'si",
            "'<form[^>]*?>(.*?)<\/form>'si",
            "'<textarea[^>]*?>.*?<\/textarea>'si",
            "'<input[^>]*?>'si",
            "'\s*id\s*=\s*[\"|\'].*?[\"|\']'si",
            "'\s* clas\s*s\s*=\s*[\"|\'].*?[\"|\']'si",
            "'<!--.*?-->'si",
        );
        */
        $search = array (
            "'<html[^>]*?>.*?<body[^>]*?>'si",
            "'<\/body>.*?<\/html>'si",
            "'<iframe[^>]*?>.*?<\/iframe>'si",
            "'<form[^>]*?>(.*?)<\/form>'si",
            "'<textarea[^>]*?>.*?<\/textarea>'si",
            "'<input[^>]*?>'si",
            "'\s*id\s*=\s*[\"|\'].*?[\"|\']'si",
            "'<!--.*?-->'si",
        );

        $replace = array ('', '', '', '', '', '', '', '', '', '', '');
        $var = preg_replace ($search, $replace, $var);

        $trans = array(
            '?' => '&#63;',
            '\\' => '&#92;',
        );
        return strtr($var, $trans);
    }

    /**
     * 去除标签
     *
     * @param  string	$var 去除标签，去除或编码特殊字符。 例如: <br>等
     *
     * @return string
     */
    public function F_htmlWebFilter($var)
    {
        return filter_var($var, FILTER_SANITIZE_STRING);
    }

    /**
     * 特殊内容过滤（以上方法的集合）
     *
     * @param string $type 过滤类型
     * @param string $var  过滤内容
     *
     * @return string
     */
    public function F_contentFilter($type, $var)
    {
        switch ($type) {
            case '#s_z': // 如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &#60; 】
                if ($this->C_var_type($var, 'string')) {
                    return $this->F_conFilter($var);
                }
                break;
            case '#s_t': // 如果存在HTML标签，剔除所有标签，保留其中文本，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt; 】
                if ($this->C_var_type($var, 'string')) {
                    return $this->F_htmlWebFilter($var);
                }
                break;
            case '#s_zb':// 如果存在HTML标签，将作为文本输出，并且转换一些特殊字符成其他形式【浏览器可识别形式 &lt; 】, 并且把换行转换成<br>
                if ($this->C_var_type($var, 'string')) {
                    return $this->F_nlSpaceSwitch($this->F_conFilter($var));
                }
                break;
            case '#s_fh':// 如果存在HTML标签，过滤掉有威胁的HTML标签【适用于所见即所得的BLOG等】
                if ($this->C_var_type($var, 'string')) {
                    return $this->F_unsafeHtmlFilter($var);
                }
                break;
            default:
                return $var;
                break;
        }

        return false;
    }
}