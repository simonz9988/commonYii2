$(function() {

});

//保存语言包的信息
function save_lang_info(a){
	var self = $(a) ;

	var id       = parseInt(self.attr("lang_id")) ;
	var lang_key =$("#edit_lang_key").val();
	var cn       =$("#edit_cn").val();
	var en       =$("#edit_en").val();
	var category = $("#edit_category").val();
	$.post(
		'/adminLanguage/ajaxSaveLangInfo',
		{id:id,en:en,cn:cn,lang_key:lang_key,category:category},
		function(data){
			try {
			 	var arr = eval('('+data+')')
			} catch (e) {
			 	alert('系统错误');
			 	return ;
			}

			if(arr.msg =='succ'){
				alert('保存成功	');
				location.reload();
			}else if(arr.msg=='repeat'){
				alert('不能重复添加');
			}else{
				alert('操作失败');
				
			}
		}
	);

} 

function edit_lang(id){
	$.post(
		'/adminLanguage/ajaxGetLangInfo',
		{id:id},
		function(data){
			try {
			 	var arr = eval('('+data+')')
			} catch (e) {
			 	return ;
			}

			if(arr.msg =='succ'){
				var detail = arr.detail ;
				$("#save_lang_info").attr("lang_id",detail.id);
				$("#edit_lang_key").val(detail.lang_key);
				$("#edit_cn").val(detail.cn);
				$("#edit_en").val(detail.en);
				$("#edit_category").val(detail.category);
			}else{
				return ;
			}
		}
	);
}