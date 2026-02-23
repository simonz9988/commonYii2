<?php
	error_reporting(0);
	set_time_limit(0);
	include_once('config.php');
	include_once("function.php");
	// 梦网短信平	
	include_once("Client.php");
	
	$V=$_REQUEST;//E($V);
	if (!isset($V['type']) || !isset($V['method']))
	{
		echo "参数有误！！";
		return;
	}
	$result = array();
	$smsInfo['userId'] = $username;
	$smsInfo['password'] = $password;
	$smsInfo['pszSubPort'] = $V['port'];
	$action = $pageurl;
	if ($V['method'] >= 0 && $V['method'] < count($pginface))
	{
		if ($V['self'] == 4)
		{
			$action.="/".$pginface[4];
			$smsInfo['multixmt'] = ' ';
			$smsInfo['flownum'] = $V['flownum'];
		}
		else
			$action.="/".$pginface[$V['type']];
	}
	$sms = new Client($action);	
	$strRet = '';
	switch($V['type'])
	{
		//发送信息
		case 0:
		
		$smsInfo['pszMsg'] = $V['msg'];
		if ($V['phones'] == '')
			$mobiles = array();
		else
			$mobiles = explode(',', $V['phones']);
			
		$result = $sms->sendSMS($smsInfo, $mobiles);
		
		//错误
		if (($strRet = GetCodeMsg($result, $statuscode)) != '')
			break;			
		
		$len = strLength($V['msg']) + $signlens;
		$strsigns = '';
		if ($len <= 70)
		{
			//单条短信，生成消息ID
			$strsigns = singleMsgId($result[0], $mobiles, ';');
		}
		else
		{
			//长短信，生成消息ID
			$nlen = ceil($len/67);			
			$strsigns = longMsgId($result[0], $mobiles, $nlen, ';');
		}
		$strRet = $strsigns;		
		break;
		
		//获取上行或状态报告
		case 1:
		$result = $sms->GetMoSMS($smsInfo);
		if (!$result)
		{
			$strRet = '无任何上行信息';
			break;		
		}
		//错误
		if (($strRet = GetCodeMsg($result, $statuscode)) != '')
			break;
		
		//返回上行信息
		//日期,时间,上行源号码,上行目标通道号,*,信息内容
		$strRet = implode(';', $result);
		
		break;
		
		//获取状态报告
		case 2:
		$result = $sms->GetRpt($smsInfo);
		if (!$result)
		{
			$strRet = '无任何状态报告';
			break;		
		}
		//错误
		if (($strRet = GetCodeMsg($result, $statuscode)) != '')
			break;
			
		//返回状态报告
		//日期,时间,信息编号,*,状态值,详细错误原因  状态值（0 接收成功，1 发送暂缓，2 发送失败）
		$strRet = implode(';', $result);		
		
		break;
		
		//获取余额
		case 3:
		$result = $sms->GetMoney($smsInfo);
		//错误
		if (($strRet = GetCodeMsg($result, $statuscode)) != '')
			break;		
		//返回余额	
		$strRet = $result[0];
		break;	
		
		default:
			$strRet = "没有匹配的业务类型";
		break;	
	}
			
	echo($strRet);
	
?>