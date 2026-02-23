<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<meat name="viewport" content="maximum-scale=1.0,minimum-scale=1.0,user-scalable=0,width=device-width,initial-scale=1.0" />
		<style>
			body{margin: 0; background-color:#f5f5f5;font-size: 18px;}
			head{}
			.top{margin: 5% 5% 5% 5%; background-color: #ffffff;box-shadow:4px 4px 4px #d3d3d3;border-radius:5px ;}
			.padding{ padding: 0 0 0 4%;}
			.top-1{padding:3% 0% 3% 0;font-size: 3em; color: #281f1d;font-size: ;font-weight: bold;border-bottom: 1px solid #D3D3D3;}
			.top-2{color: #281f1d;font-size: ;}
			.top-3{color: #281f1d;font-size: ;font-weight: bold;font-size: 3em;}
			.top-4{padding:0 3% 1% 0;color: #281f1d;font-size: ;color: #77787b;}
			.top-5{font-size: 1.5em;}
			.odform-tit{font-weight:normal;font-size:1.5em;color:#595757;line-height:2em;text-align:center;border-bottom:1px solid #c9cacb;margin:0;padding:5% 0}
			.odform-tit img{height:40px;vertical-align:middle;margin-right:1em}
			.odform{padding:5%}
			.input-group{margin-bottom:2%;position:relative}
			.input-group label{padding:1% 0;position:absolute;color:#009ad6;font-size: 1.5em;}
			.input-group input{margin-left:20%;padding:3% 1%;box-sizing:border-box;background:#efeff0;border:0;border-radius:5px;color:#595757;width:50%;font-size: 1.5em;}
			.dem{background:#009ad6;color:#fff;text-align:center;border:0;border-radius:10px;padding:3%;width:100%;font-size:2em}
			.button-group{ background-color: #009AD6;border: none;font-size: 2em;color: #FFFFFF;margin-left:30px ;}
		</style>
		<title>wepay</title>
	</head>
	<body>
		<div class="top">
			<div class="padding">
			<p class="top-1">
				wepay
			</p>
			<span class="top-2">交易金额（人民币）</span>
			<span class="top-3">50000.00</span>
			<br />
			<p class="top-4">阁下的转账金额必须与这里显示的交易金额（人民币）完全一致，否则导致资金无法到账。</p>			
			</div>	
		<div class="odform">
		 <form action="#">
		  <div class="input-group">
		   <label for="wdname">账户名称</label>
		   <input type="text" class="xl" id="wdname" placeholder="请输入您的账户名">
		   <button class="button-group" onclick="myFunction()">复制</button>
		  </div>
		  <div class="input-group">
		   <label for="khname">账户号码</label>
		   <input type="text" id="khname" placeholder="请输入您的账户号码">
		   <button class="button-group">复制</button>
		  </div>
		  <div class="input-group">
		   <label for="khname">银行名称</label>
		   <input type="text" id="khname" placeholder="请输入您的银行名称">
		   <button class="button-group"onclick="myFunction()">复制</button>
		  </div>
		  <div class="input-group">
		   <label for="khname">银行支行</label>
		   <input type="text" id="khname" placeholder="请输入您的支行名称">
		   <button class="button-group"onclick="myFunction()">复制</button>
		  </div>
		  <div class="input-group">
		   <label for="khname">省</label>
		   <input type="text" class="cal" id="khname" placeholder="请输入您所在省">
		   <button class="button-group"onclick="myFunction()">复制</button>
		  </div>
		  <div class="input-group">
		   <label for="khname">市</label>
		   <input type="text" id="khname" placeholder="请输入您所在市">
		   <button class="button-group"onclick="myFunction()">复制</button>
		  </div>
		  <div class="input-group">
		   <label for="khname">摘要/附言</label>
		   <input type="text" id="khname" placeholder="请输入您要留的信息">
		   <button class="button-group"onclick="myFunction()">复制</button>
		  </div>
		  <button class="dem">确认</button>
		 </form>
		</div>
	</body>
	<script>

	</script>
</html>
