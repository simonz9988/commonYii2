<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WebService接口PHP_Demo(长连接)-----【同步请求】单线程</title>
<script type="text/javascript" src="../js/jquery-1.4.2.min.js" /></script>
<style type="text/css">
input,label{cursor:pointer;}
</style>

<script language="javascript">
var gmethod = 0;
var gurl = 'DealPage.php';
var mot = 0;
var rptt = 0;
var submitt = 0;
var nType = 0;
var autosend = false;
$(document).ready(function(){
	
	$('#service').click(function(){
		if ('启动服务' == this.value){
			this.value = '停止服务';
			$.ajax({
			url:'connection.php',
			data:{service:1},
			success:function(data){if ($.trim(data) != '') alert(data);}
			});
		}
		else
		{
			this.value = '启动服务';
			$.ajax({
			url:'connection.php',
			data:{service:0},
			success:function(data){if ($.trim(data) != '') alert(data);}
			});
		}
	});
	
	$("input[name='type']").click(function(){
		gmethod = this.value;
	});
	
	$("#submit").click(function(){
		var obj = this;
		obj.disabled = true;		
		$.ajax({
		url:gurl,
		data:{type:$(obj).attr('ntype'), port:$('#extendnum').val() , self:nType, flownum:$('#flownum').val(), method:gmethod, phones:$.trim($('#phones').val()), msg:$('#msg').val(), r:Math.random()},
		error:function(){AddText('f1ret', '页面无法访问');obj.disabled = false;},
		success:function(data){ 
				AddText('f1ret', data); obj.disabled = false; 
				if (autosend)
				{
					submitt = setTimeout(function(){
						$('#msg').val('自动发送长连接3');// + Math.random().toString());
						$('#submit').trigger('click');
						}, 10);
				}
			}
		});
	});
	
	$('#autosubmit').click(function(){
		if ('自动发送' == this.value)
		{
			this.value = '关闭发送';
			$('#submit').trigger('click');
			autosend = true;
			$('#automo').val('关闭接收');
			$('#automo').trigger('click');
			$('#automo').attr('disabled', true);
			$('#autorpt').val('关闭接收');
			$('#autorpt').trigger('click');
			$('#autorpt').attr('disabled', true);
		}
		else
		{
			this.value = '自动发送';
			autosend = false;
			clearTimeout(submitt);
			$('#automo').attr('disabled', false);
			$('#autorpt').attr('disabled', false);
		}
	});
	
	$("input[name='get']").click(function(){
		this.disabled = true;
		OnSubmit(this);
	});
	
	$('#msg').bind('keyup',function(){
		var len = strlen($(this).val());
		$('#msglens').text(len);
	});
	
	$('#phones').bind('keyup',function(){		
		var pcount = 0;
		var phones = $.trim($(this).val());
		if (phones != '')
		{
			var parr = phones.split(',');
			pcount = parr.length;
			if (pcount == 100)
				alert('手机号码超出100个');
		}
		$('#pcount').text(pcount);
	});
	
	$("#automo").click(function(){
		if ('关闭接收' == this.value)
		{
			this.value = '自动接收';
			$('#getmo').attr('disabled', false);
			clearTimeout(mot);
		}
		else
		{
			this.value = '关闭接收';
			$('#getmo').attr('disabled', true);
			AutoMo();
		}
		
	});
	
	$("#autorpt").click(function(){
		if ('关闭接收' == this.value)
		{
			this.value = '自动接收';
			$('#getrpt').attr('disabled', false);
			clearTimeout(rptt);
		}
		else
		{
			this.value = '关闭接收';
			$('#getrpt').attr('disabled', true);
			AutoRpt();
		}		
	});
	
	$('#selfsend').click(function(){
		if (this.checked)
			nType = 4;
		else
			nType = 0;
	});	
}); 
 
function OnSubmit(obj)
{
	var o = $(obj).parent();
	var txtobj = $(o).children('textarea');
	$.ajax({
		url:gurl,
		data:{type:$(obj).attr('ntype'), method:gmethod, r:Math.random()},
		error:function(){AddText($(txtobj).attr('id'), '页面无法访问');obj.disabled = false;},
		success:function(data){			
			AddText($(txtobj).attr('id'), data);
			obj.disabled = false;
			}
		});
};

function isChinese(str)
{
   var lst = /[u00-uFF]/;       
   return !lst.test(str);      
};

function strlen(str) 
{
	var strlength = 0;
	for (var i=0; i < str.length; ++i)
	{
		if (isChinese(str.charAt(i)) == true)
			strlength = strlength + 1;
		else
			strlength = strlength + 1;
	}
	return strlength;
};

function AutoMo()
{
	$.ajax({
		url:gurl,
		data:{type:$('#getmo').attr('ntype'), method:gmethod, r:Math.random()},
		error:function(){AddText('f2ret', '页面无法访问');},
		success:function(data){
			AddText('f2ret', data); 
			if(strlen(data) < 20) 
			{
				mot = setTimeout('AutoMo()', 4000);
				return;
			}
			AutoMo();
		}
		});
};

function AutoRpt()
{
	$.ajax({
		url:gurl,
		data:{type:$('#getrpt').attr('ntype'), method:gmethod, r:Math.random()},
		error:function(){AddText('f3ret', '页面无法访问');},
		success:function(data){
			AddText('f3ret', data);
			if(strlen(data) < 20) 
			{
				rptt = setTimeout('AutoRpt()', 4000);
				return;
			}
			AutoRpt();
		}
		});
}

function AddText(id, txt)
{
	if ($.trim(txt) == '')
		return;
	var str = $('#' + id).val();
	if (str.length == 1000)
		str = '';	
	txt = txt.replace(/;/g, '\r\n');
	str += txt + '\r\n';
	$('#' + id).val(str);
	document.getElementById(id).scrollTop = document.getElementById(id).scrollHeight;
	if (strlen(txt) > 20)
	{
		var txtarr = txt.split('\r\n');
		var spanobj = $('#' + id).parent().children('span');
		$(spanobj).text($(spanobj).text() * 1 + txtarr.length);
	}
};

</script>
</head>
<body>
提交方式：
<input type="radio" id="type2" name="type" value="1" checked="checked" /><label for="type2">POST</label>
<input type="button" id="service" name="service" value='启动服务'/><hr /><br/>

<form name="form1" id="form1" method="post" action="" >
<div>手机号码(多个号码以<font color="#FF0000">英文半角逗号</font>分隔)&nbsp;(<label id="pcount">1</label>)：<br/>
<textarea id="phones" name="phones" cols="60" rows="5">15118024546</textarea><br/>
信息内容&nbsp;(<label id="msglens">4</label>)：<br />
<textarea id="msg" name="msg" cols="60" rows="5">&lt;长连接&gt;测试信息</textarea><br/>
<input type="button" id="submit" name="submit" ntype="0" value="发送信息">
<input type="button" id="autosubmit" name="autosubmit" ntype="0" value="自动发送">
扩展号:<input type="text" id="extendnum" name="extendnum" value="*"/>

&nbsp;&nbsp;
<input type="checkbox" id="selfsend" name="selfsend" ntype="4" /><label for="selfsend">个性化发送</label>
流水号:<input type="text" id="flownum" name="flownum" value="0"/>
<br />
返回信息(<span>0</span>):<br/><textarea id="f1ret" name="f1ret" cols="85" rows="5"></textarea>
</div></form><br/>

<form name="form2" id="form2" method="post" action="" >
<div><input type="button" id="getmo" name="get" ntype="1" value="获取上行" >
<input type="button" id="automo" name="auto" value="自动接收" ><br />
返回信息(<span>0</span>):&nbsp;日期,时间,上行源号码,上行目标通道号,*,信息内容(*为保留字段)
<br/><textarea id="f2ret" name="f2ret" cols="85" rows="5"></textarea></div></form><br/>

<form name="form2" id="form2" method="post" action="" >
<div><input type="button" id="getrpt" name="get" ntype="2" value="获取状态报告">
<input type="button" id="autorpt" name="auto" value="自动接收" ><br />
返回信息(<span>0</span>):&nbsp;日期,时间,信息编号,*,状态值,详细错误原因  状态值(0 接收成功，1 发送暂缓，2 发送失败)
<br/><textarea id="f3ret" name="f3ret" cols="85" rows="5"></textarea>
</div></form><br/>

<form name="form4" id="form3" method="post" action="" >
<div><input type="button" id="getmoney" name="get" ntype="3" value="查询余额"><br />
返回信息:<br/><textarea id="f4ret" name="f4ret" cols="85" rows="5"></textarea>
</div></form><br/>

</body>
</html>
