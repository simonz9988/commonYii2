<?php

class memcached
{
	var $mem = null;
	var $debug = false;
	
	function __construct($timeout = 0)
	{
		$this->mem = new Memcache;
		$this->mem->pconnect('127.0.0.1', 11211, $timeout) or die('can not connection memcache.');		
	}
	
	function Get($key)
	{		
		$get_result = $this->mem->get($key);		
		return $get_result;
	}
	
	function Set($key, $param)
	{
		$this->mem->set($key, $param);
	} 
	
	function Close()
	{
		$this->mem->close();	
	}	
	
	function Del($key)	
	{
		$this->mem->delete($key);
	}
	
	function DelAll()
	{
		$this->mem->flush();
	}
}
?>
