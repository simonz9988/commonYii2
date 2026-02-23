<?php
error_reporting(0);
include_once('config.php');
include_once('mysocket.php');

$V = $_REQUEST;
if (!$V)
{
	exit;
}

switch ($V['service'])
{
	case 0:
		$myso = new mysocket();	
		$myso->Close();
	break;
	
	case 1:
		$arrinfo = parse_url($pageurl);
		$myso = new mysocket();
		$myso->Init($arrinfo['host'], $arrinfo['port']);
		$myso->Run();
	break;
}
?>