<?php
	if (!defined('PHONECODE')) {
	    define('PHONECODE', dirname(__FILE__) . '/phonecode/connect/');
	  //  var_dump(PHONECODE.'config.php');die;

	}

	//error_reporting(0);
	set_time_limit(0);
	include_once(PHONECODE.'config.php');
	include_once(PHONECODE."function.php");
	// 梦网短信平
	include_once(PHONECODE."Client.php");

class Phonecode{



	/**
	 * 发送短信
	 * @param  [string] $phones  [手机号多个用,隔开最多100]
	 * @param  [string] $msg     [消息内容长度不大于350个汉字]
	 * @param  string $type    [0发送信息]
	 * @param  string $port    [拓展号]
	 * @param  string $self    [是否自定义流水号,4为自定义]
	 * @param  string $flownum [一个8字节64位的大整型（INT64），格式化成的字符串。因此该字段必须为纯数字，且范围不能超过INT64的取值范围（-(2^63）……2^63-1）]
	 * @param  string $method  [0为 soap]
	 * @return array array('status' => bool, 'msg' => string)
	 */
	public static function sendPhoneCode($phones,$msg,$type='0',$port='*',$self='0',$flownum='0',$method='0'){

		$V['type']=$type;
		$V['port']=$port;
		$V['self']=$self;
		$V['flownum']=$flownum;
		$V['method']=$method;
		$V['phones']=$phones;
		$V['msg']=$msg;


		if (!isset($V['type']) || !isset($V['method']))
		{
			return array('status' => false, 'msg' => '参数不完整');
		}
		global $username;
		global $password;
		global $pageurl;
		global $statuscode;
		global $signlens ;
		$result = array();
		$smsInfo['userId'] = $username;
		$smsInfo['password'] = $password;
		$smsInfo['pszSubPort'] = $V['port'];
		$action = $pageurl;
		$defhandle = $V['type']; //设置请求接口
		if ($V['method'] == 0 && $V['self'] == 4)
		{
			$smsInfo['multixmt'] = ' ';
			$smsInfo['flownum'] = $V['flownum'];
			$defhandle = 4;
		}
		elseif ($V['method'] > 0 && $V['method'] < count($pginface))
		{
			$action.="/".$pginface[$V['type']];
		}

		$sms = new Client($action, $V['method']);

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
			if (($strRet = GetCodeMsg($result, $statuscode)) != ''){
				return array('status' => false, 'msg' => $strRet);
				break;
			}


			$len = strLength($V['msg']) + $signlens;
			$strsigns = '';
			if ($len <= 70)
			{
				//单条短信，生成消息ID
				if (0 == $V['method'])
					$strsigns = singleMsgId($result, $mobiles, ';');
				else
					$strsigns = singleMsgId($result[0], $mobiles, ';');
			}
			else
			{
				//长短信，生成消息ID
				$nlen = ceil($len/67);
				if (0 == $V['method'])
					$strsigns = longMsgId($result, $mobiles, $nlen, ';');
				else
					$strsigns = longMsgId($result[0], $mobiles, $nlen, ';');
			}
			$strRet = $strsigns;
			return array('status' => true, 'msg' => $strRet);
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
			if (is_array($result))
				$strRet = implode(';', $result);
			else
				$strRet = $result;
			break;

			//获取余额
			case 3:
			$result = $sms->GetMoney($smsInfo);
			//错误
			if (($strRet = GetCodeMsg($result, $statuscode)) != '')
				break;
			//返回余额
			if (0 == $V['method'])
				$strRet = $result;
			else
				$strRet = $result[0];
			break;
			default:
				$strRet = "没有匹配的业务类型";
			break;
		}

		//echo($strRet);
	}

}



?>
