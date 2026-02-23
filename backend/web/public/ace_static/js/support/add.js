$(function() {
	
	init_uploader('android_app_download','android_app');
	init_uploader('apple_app_download','apple_app');
	init_uploader('app_example','app_example');
	init_rar_uploader("rar_upload","instructions_upload");
});

function uploadImage(a){
	var id_str = $(a).attr("attr_id");
	$("#div_add_img").attr("dom_id",id_str);

	var is_init  = $("#div_add_img").attr('is_init');
	if(is_init == 1 ){
		init_help_uploader(id_str,'div_add_img','help_menu_image');
		$("#div_add_img").attr("is_init",0);
	}
	$("#div_add_img").click();
} 

function addDom(){
	$.post(
		'/support/ajaxGetMenuDom',
		function(data){
			$("#menuTbody").append(data);
			$(".input_class").each(function(){
		        $(this).rules("add", { 
		            required: true  ,
		            messages: {
		                        required:'Required',
		                      } 
		        }); 
		        form_input_notice($(this),'list_td');
		    });

		    $(".upload_img").each(function(){
		      form_input_notice($(this),'list_td');
		    });
		}
	);
	
}

//动态渲染选择商品模型选项
function showFilterGoodsModel(a){
	var init_model_id = $(a).attr("init_model_id");
	var init_model_img = $(a).attr("init_model_img");
	$.post(
		'/goods/ajaxShowGoodsModel',
		{init_model_id:init_model_id,init_model_img:init_model_img,is_help_filter:1},function(data){
			$("#modal_model").empty().html(data);
			$('#img_goods_model_id').removeClass("hide");
			return true ;
		}
	);
}



//展示上传apps类型
function show_upload_div(a){
	var  v = $(a).val();
	if(v==0){
		$("#div_apple_upload").hide();
		$("#div_android_upload").hide();
	}else if(v=='apple'){
		$("#div_apple_upload").show();
		$("#div_android_upload").hide();
	}else{
		$("#div_apple_upload").hide();
		$("#div_android_upload").show();
	}
}


//动态渲染选择商品模型选项
function showFaqGoodsModel(a){
	var init_model_id = 0;
	var init_model_img = 0;
	var faq_all_model_ids = '';
	$(":input[name='goods_model_id[]']").each(function(){
		faq_all_model_ids += $(this).val()+",";
	});
	$.post(
		'/goods/ajaxShowGoodsModel',
		{init_model_id:init_model_id,init_model_img:init_model_img,is_faq:1,faq_all_model_ids:faq_all_model_ids},function(data){
			$("#modal_model").empty().html(data);
			$('#img_goods_model_id').removeClass("hide");
			return true ;
		}
	);
}

//保存faq相关的模版信息
function saveModelInfo(a){
	
	$("#goods_model_div .goods_model_detail").remove();
	var str = '';
	var is_faq = $(a).attr("is_faq");
	var is_help_filter = $(a).attr("is_help_filter");
	if(is_help_filter==1){
		var name = 'goods_model_id';
	}else{
		var name = 'goods_model_id[]';
	}
	
	$(".model_input_val").each(function(){
		if($(this).is(':checked')){

			str += '<div class="goods_model_detail">';

			str += '<input type="hidden" name="'+name+'" value="'+$(this).val()+'">';
			str += '<img  id="img_goods_model_id" src="'+$(this).attr("ftp_img_path")+'" width="80">';
			str += '<span>'+$(this).attr('goods_model_name')+'</span>';
			str += '<a href="javascript:void(0)" onclick="$(this).parent().remove();">delete</a>';
			str += '</div>';

		}
	});
	
	var p_p = $("#goods_model_a").prev().prev();
	if(p_p.attr("id")=='img_goods_model_id'){
		p_p.remove();
	}
	$("#goods_model_a").prev().remove();
	
	$("#goods_model_a").before(str);
	//读取所有选择的模版id
	$("#close_btn").click();

}


//删除使用说明相关的上传文件
function del_download(a){
	$(a).parent().parent().remove();
}

//上移文件
function  forward_move(a){
	var p = $(a).parent().parent();
	var before = p.prev();
	var before_class = before.attr("class");
	if( before_class == 'download_tr'){
		//排除最顶部的DOM 元素
		before.before(p);
	}
}
//下移文件
function back_move(a){
	var p = $(a).parent().parent();
	var next = p.next();
	var next_class = next.attr("class");
	if( next_class == 'download_tr'){
		//排除最顶部的DOM 元素
		next.after(p);
	}
}

