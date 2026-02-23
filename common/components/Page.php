<?php
namespace common\components;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id: Page.class.php 2712 2012-02-06 10:12:49Z liu21st $

class Page {
    // 分页栏每页显示的页数
    public $rollPage = 3;
    // 页数跳转时要带的参数
    public $parameter  ;
    // 默认列表每页显示行数
    public $listRows = 20;
    // 起始行数
    public $firstRow	;
    // 分页总页面数
    protected $totalPages  ;
    // 总行数
    protected $totalRows  ;
    // 当前页数
    protected $nowPage    ;
    // 分页的栏的总页数
    protected $coolPages   ;
    // 分页显示定制
    protected $config  =	array('header'=>'条记录','prev'=>'上一页','next'=>'下一页','first'=>'第一页','last'=>'最后一页','theme'=>' %totalRow% %header% %nowPage%/%totalPage% 页 %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%');
    // 默认分页变量名
    protected $varPage;

    protected $_header_need = 0;

    /**
     +----------------------------------------------------------
     * 架构函数
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     * @param array $totalRows  总的记录数
     * @param array $listRows  每页显示记录数
     * @param array $parameter  分页跳转的参数
     +----------------------------------------------------------
     */
    public function init($totalRows,$listRows='',$parameter='') {
        $this->totalRows = $totalRows['totalRows'];
        $this->parameter = $totalRows['parameter'];
        $listRows = $totalRows['listRows'];

        // @author huangzongcheng
        // @brief 在有些需求中一个页面需要有多个不同的分页展示，这里将 varPage 修改成可配置
        if (isset($totalRows['pageTag']) && preg_match('/^[a-zA-Z]+$/', $totalRows['pageTag'])) {
            $this->varPage = $totalRows['pageTag'];
        } else {
            $this->varPage = 'p';
        }

        if(!empty($listRows)) {
            $this->listRows = intval($listRows);
        }
        $this->totalPages = ceil($this->totalRows/$this->listRows);     //总页数
        $this->coolPages  = ceil($this->totalPages/$this->rollPage);
        $this->nowPage  = !empty($_REQUEST[$this->varPage])?intval($_REQUEST[$this->varPage]):1;
        if(!empty($this->totalPages) && $this->nowPage>$this->totalPages) {
            $this->nowPage = $this->totalPages;
            $this->_header_need = 1;
        }
        $this->firstRow = $this->listRows*($this->nowPage-1);
    }

    public function setConfig($name,$value) {
        if(isset($this->config[$name])) {
            $this->config[$name]    =   $value;
        }
    }

    public function showResForBackstage() {
        if(0 == $this->totalRows) return '';
        $p = $this->varPage;
        $this->rollPage = 11;
        $nowCoolPage = ceil($this->nowPage/$this->rollPage);
        $parameter = '';
        if(!empty($this->parameter)){
	        foreach($this->parameter as $k => $v)
	        {
	        	$parameter .= '&'.$k.'='.$v;
	        }
        }
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$parameter;
		$params = '';
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        ($params) && $url .= "&";

        $result = array(
        				'firstpage' => 	array('row' => '1', 'href' => $url.$p."=1"),
        				'endpage' 	=> 	array('row' => $this->totalPages, 'href' => $url.$p."=".$this->totalPages),
        				'prev' 		=> 	array('row' => '', 'href' => ''),
        				'next' 		=> 	array('row' => '', 'href' => ''),
        				'page' 		=>	array(),
                        'baseurl'   =>  $parse['path'],
                        'offset'   =>  $this->firstRow
        				);
        /**
         * upRow 上一页
         * downRow 下一页
         */
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;

        if ($upRow>0) $result['prev'] = array('row' => $upRow, 'href' => $url.$p."=".$upRow);

        if ($downRow <= $this->totalPages) $result['next'] = array('row' => $downRow, 'href' => $url.$p."=".$downRow);
        // 1 2 3 4 5
        $linkPage = "";
        for($i=1;$i<=$this->totalPages;$i++) {
        	$parr[] = $i;
        }
        $yu = $this->totalPages - $this->nowPage;
        if($this->nowPage >=5 && $yu > 3) {
        	$newparr = array_slice($parr, array_search(($this->nowPage-3),$parr),5);
        } else if($this->nowPage >=5 && $yu <= 3) {
        	$newparr = array_slice($parr, -5);
        } else if($this->nowPage < 5) {
        	$newparr = array_slice($parr, 0, 5);
        }
    	for($i=$newparr[0];$i<=$newparr[count($newparr)-1];$i++){
	        $result['page'][$i] = ($i!=$this->nowPage) ? ($url.$p."=".$i) : '';
	    }
	    $result['totalpage'] = $this->totalPages;
        if ($this->_header_need) header("location:".$result['endpage']['href']);
       	return $result;
    }


    /**
     * 针对大数据量 获取分页信息（只有上一页和下一页）
     */
    public function showPageDataForBigData(){
        $p = $this->varPage;
        $this->rollPage = 11;
        $nowCoolPage = ceil($this->nowPage/$this->rollPage);
        $parameter = '';
        if(!empty($this->parameter)){
            foreach($this->parameter as $k => $v)
            {
                $parameter .= '&'.$k.'='.$v;
            }
        }
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?").$parameter;
        $params = '';
        $parse = parse_url($url);
        if(isset($parse['query'])) {
            parse_str($parse['query'],$params);
            unset($params[$p]);
            $url   =  $parse['path'].'?'.http_build_query($params);
        }
        ($params) && $url .= "&";

        $result = array(
            'prev' 		=> 	array('row' => '', 'href' => ''),
            'next' 		=> 	array('row' => '', 'href' => ''),
            'page' 		=>	array(),
            'baseurl'   =>  $parse['path'],
            'offset'   =>  $this->firstRow
        );
        /**
         * upRow 上一页
         * downRow 下一页
         */
        $upRow   = $this->nowPage-1;
        $downRow = $this->nowPage+1;

        if ($upRow>0) $result['prev'] = array('row' => $upRow, 'href' => $url.$p."=".$upRow);
        $result['next'] = array('row' => $downRow, 'href' => $url.$p."=".$downRow);
        if ($this->_header_need) header("location:".$result['endpage']['href']);
        return $result;
    }


    /**
    * @brief 获取分页相关信息
    * @param intval $page 当前页码数
    * @param intval $totalpage 当前总页码数
    * @param intval $pagelength 显示页面条数
    *
    */
    public function getPageData($page_now=1,$pagelength=10)
    {
        $totalPage=$this->totalPages;
        $firstpage=$page_now-floor($pagelength/2);
        if($firstpage<=0)$firstpage=1;
        $lastpage=$firstpage+$pagelength-1;
        if($lastpage>$totalPage)
        {
            $lastpage=$totalPage;
            $firstpage=($totalPage-$pagelength+1)>1?$totalPage-$pagelength+1:1;
        }
        $url  =  $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'],'?')?'':"?");
        $url = preg_replace('/page=\d?&/','',$url);
        $url = preg_replace('/(\?|&|\/)page(\/|=).*/i','',$url);
        if(strpos($url,'?') !== false)
        {
            $index = '&page=';
        }else{
            $index = '?page=';
        }
        $baseUrl=$url.$index;
        //上一页
        $previous_page=$page_now-1>0?$page_now-1:1;
        //下一页
        $next_page=$page_now+1>$totalPage?$totalPage:$page_now+1;
        return array(
            'baseUrl'           =>$baseUrl,
            'previous_page'     =>$previous_page,
            'next_page'         =>$next_page,
            'totalPage'         =>$totalPage,  
            'firstpage'         =>$firstpage,
            'lastpage'          =>$lastpage,
            'page_now'          =>$page_now,
        );
        
    }




}