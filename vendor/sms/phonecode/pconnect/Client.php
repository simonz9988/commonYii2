<?php

include_once('mysocket.php');

/**
 梦网短信平台
 */
class Client
{
	
	var $url;	
	var $clt;	
	
	function Client($url)
	{		
		$this->url = $url;			
		$this->clt = new mysocket(); 
	}		
	
	function setParam(&$V)
	{
		$arr = $V;
		$V = null;
		$V['userId'] = $arr['userId'];
		$V['password'] = $arr['password'];
		if (isset($arr['multixmt']))
		{
			$mutilstr = '';
			$mos = explode(',', $arr['pszMobis']);
			$arr['pszMsg'] = iconv('UTF-8', 'GB2312', $arr['pszMsg']);
			for($i = 0; $i < $arr['iMobiCount']; ++$i)
			{
				$mutilstr.=",".$arr['flownum']."|".$arr['pszSubPort']."|".$mos[$i]."|".base64_encode($arr['pszMsg']);
			}			
			$V['multixmt'] = substr($mutilstr, 1);
			return;
		}		
		$V['pszMobis'] = $arr['pszMobis'];
		$V['pszMsg'] = $arr['pszMsg'];
		$V['iMobiCount'] = $arr['iMobiCount'];
		$V['pszSubPort'] = $arr['pszSubPort'];		
	}	
	
	function GetData($arr)
	{		
		//POST方式
		$urlinfo = parse_url($this->url);		
		if (isset($arr['multixmt']))
			$postdata = "userId=".$arr['userId']."&password=".$arr['password']."&multixmt=".$arr['multixmt'];
		else
			$postdata = UrlUnion($arr);
		$senddata = "POST {$urlinfo['path']} HTTP/1.1\r\nHost: {$urlinfo['host']}\r\nContent-type: application/x-www-form-urlencoded\r\nConnection: Keep-Alive\r\nContent-Length: ".strlen($postdata)."\r\n\r\n{$postdata}";
		return $senddata;
	}
	
	/**
	 * 发送短信
	 * @return int 操作结果状态码
	*/
	function sendSMS($arrInfo, $arrMobile)
	{	
		$smsInfo = $arrInfo;
		if (empty($arrInfo["pszSubPort"]))
		{
			$smsInfo["pszSubPort"] = '*';
		}		
		$smsInfo["pszMobis"] = implode(",", $arrMobile);
		$smsInfo["iMobiCount"] = count($arrMobile);
		
		$this->setParam($smsInfo);
		$senddata = $this->GetData($smsInfo);
		$this->clt->SendData($senddata);
		while (($retstr= $this->clt->GetData()) == '')
		{
			usleep(2);
		}			
		//提取信息
		//POST		
		$result = GetArrXmlKey('string', $retstr);		
		return $result;
	}	
	
	function setTwoParam(&$V)
	{
		$arr = $V;
		$V = null;
		$V['userId'] = $arr['userId'];
		$V['password'] = $arr['password'];		
	}
	
	function GetMoSMS($arrInfo)
	{		
		$this->setTwoParam($arrInfo);
		$senddata = $this->GetData($arrInfo);
		$this->clt->SendData($senddata);
		while (($retstr= $this->clt->GetData()) == '')
		{
			usleep(10);
		}			
		//提取信息
		//POST
		$result = GetArrXmlKey('string', $retstr);		
		return $result;		
	}	
	
	function GetRpt($arrInfo)	
	{
		return $this->GetMoSMS($arrInfo);
	}
	
	function GetMoney($arrInfo)	
	{
		$this->setTwoParam($arrInfo);
		$senddata = $this->GetData($arrInfo);
		$this->clt->SendData($senddata);
		while (($retstr= $this->clt->GetData()) == '')
		{
			usleep(10);
		}				
		//提取信息
		//POST			
		$retstr = str_replace("string", "int", $retstr);	
		$result = GetArrXmlKey('int', $retstr);		
		return $result;	
	}
	
}
?>
