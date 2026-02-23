$(function() {


});

//保存语言包的内容
function save_lang_info(a){

	var lang_id  = $(a).attr('lang_id');
	
	var lang_key = $.trim( $("#edit_lang_key").val() );
	var category = $.trim( $("#edit_category").val() );
	var desc     = $.trim( $("#edit_desc").val() );
	var cn       = $.trim( $("#edit_cn").val() );
	var en       = $.trim( $("#edit_en").val() );
	var tw       = $.trim( $("#edit_tw").val() );
	var de       = $.trim( $("#edit_de").val() );
	var sp       = $.trim( $("#edit_sp").val() );
	var fr       = $.trim( $("#edit_fr").val() );
	var jp       = $.trim( $("#edit_jp").val() );

	$.post(
		'/siteLanguage/ajaxSaveLangInfo',
		{lang_id:lang_id,lang_key:lang_key,category:category,desc:desc,cn:cn,en:en,tw:tw,de:de,sp:sp,fr:fr,jp:jp},
		function(data){
			try{
		  		var arr = eval('('+data+')');
		  		var code = arr.code ;
		  		if(code==1){
		  			window.location.reload();
		  			
		  		}else{
		  			alert(arr.msg)
		  		}
		  	}
			catch(err)
		  	{
				txt="There was an error on this page.\n\n";
				txt+="Error description: " + err.message + "\n\n";
				txt+="Click OK to continue.\n\n";
	  			alert(txt);
		  	}
		}
	);

}


//编辑前台语言包的内容
function edit_lang_info(a){
	var lang_id = $(a).attr('lang_id');
	$.post(
		'/siteLanguage/ajaxGetLangInfo',
		{lang_id:lang_id},
		function(data){
			try{
		  		var arr = eval('('+data+')');
		  		var code = arr.code ;
		  		if(code==1){
		  			//填充内容
		  			var detail = arr.detail ;
		  			var id  = detail.id;

		  			var lang_key = detail.lang_key ;
					var category = detail.category ;
					var desc     = detail.desc ;
					var cn       = detail.cn ;
					var en       = detail.en ;
					var tw       = detail.tw ;
					var de       = detail.de ;
					var sp       = detail.sp ;
					var fr       = detail.fr ;
					var jp       = detail.jp ;

					$("#edit_lang_key").val(lang_key) ;
					$("#edit_category").val(category) ;
					$("#edit_desc").val(desc) ;
					$("#edit_cn").val(cn) ;
					$("#edit_en").val(en) ;
					$("#edit_tw").val(tw) ;
					$("#edit_de").val(de) ;
					$("#edit_sp").val(sp) ;
					$("#edit_fr").val(fr) ;
					$("#edit_jp").val(jp) ;

					$("#save_lang_info").attr('lang_id',id);

		  		}else{
		  			alert(arr.msg)
		  		}
		  	}
			catch(err)
		  	{
				txt="There was an error on this page.\n\n";
				txt+="Error description: " + err.message + "\n\n";
				txt+="Click OK to continue.\n\n";
	  			alert(txt);
		  	}
		}
	);
}