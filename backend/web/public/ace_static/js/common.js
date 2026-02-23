$(function() {
	$(".cus-btn-del").on('click',
	function(e) {
		var url = $(this).attr("url");
		e.preventDefault();
		$("#dialog-confirm").removeClass('hide').dialog({
			resizable: false,
			modal: true,
			title: "",
			title_html: true,
			buttons: [{
				html: "<i class='ace-icon fa fa-trash-o bigger-110'></i>&nbsp; DELETE",
				"class": "btn btn-danger btn-xs",
				click: function() {
					window.location.href = url;
				}
			},
			{
				html: "<i class='ace-icon fa fa-times bigger-110'></i>&nbsp; Cancel",
				"class": "btn btn-xs",
				click: function() {
					$(this).dialog("close");
				}
			}]
		});
	});


});

function sel_lang(lang,str){
	$("#a_sel_lang").html(str);
	var exp = new Date();  
   exp.setTime(exp.getTime() + 7 * 24 * 60 * 60 * 1000); //7天过期  
   document.cookie ="adminCurrLang=" + lang + ";expires=" + exp.toGMTString()+";path=/";
   location.reload();  
}