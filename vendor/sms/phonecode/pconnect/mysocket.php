<?php
//长连接
// 建立客户端的socet连接  
set_time_limit(0);
include_once('memcached.php');

class mysocket
{
	var $mem ;
	var $socket;
	var $startnum = 0;
	var $endnum = 0;	
	
	function __construct($ip = '127.0.0.1', $port = 11211)
	{
		$this->mem = new memcached($ip, $port);				
	}
	
	function Init($ip = '127.0.0.1', $port = 8088)
	{
		if ($this->mem->Get('socket'))
		{			
			return;
		}
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);  
		socket_connect($this->socket, $ip, $port) or die("can not connection server.");    //连接服务器端socket  
		$this->mem->Set('socket', 1);				
	}
	
	function GetRun()
	{
		return $this->mem->Get('socket');
	}
	
	function Push($data)
	{
		$this->mem->Set('getdata', $data);	
	}
	
	function Pop()
	{	
		if (!($data = $this->mem->Get('getdata')))
			return '';
		$this->mem->Del('getdata');
		return $data;
	}
	
	function Run()
	{
		while ($this->GetRun())
		{
			if (($senddata = $this->mem->Get('senddata')) == '')
			{
				usleep(10);
				continue;				
			}
			$this->mem->Del('senddata');		
			if ($this->socket)
			{
				socket_send($this->socket, $senddata, strlen($senddata), 0);			
				$recvdata = socket_read($this->socket, 8 * 1024, PHP_BINARY_READ);							
				$this->Push($recvdata);				
			}				 
		}	
		$this->mem->Close();			
	}
	
	function GetData()
	{
		if (!$this->GetRun())
		{			
			return '<string>-10055</string>';
		}			
		return $this->Pop();
	}
	
	function SendData($data)
	{
		if (!$this->GetRun())
		{			
			return;
		}
		$this->mem->Set('senddata', $data);							
	}	
		
	function Close()
	{	
		$this->mem->Del('socket');
		$this->mem->Del('startnum');
		$this->mem->Del('endnum');		
		$this->mem->Del('senddata');
		$this->mem->Del('getdata');					
		if ($this->socket)
		{
			socket_close($this->socket);
		}	
	}
}

?>