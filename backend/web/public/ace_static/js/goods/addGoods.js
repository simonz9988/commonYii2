$(function() {
	init_uploader('img_path','goods');
	init_goods_image_uploader('div_add_img','goods_image');
	init_uploader('header_img','goods');
});



//保存新增的参数分类
function saveParamsCat(params_id){

	var name    = $.trim($("#addInputName").val());
	var sort    = $.trim($("#addInputSort").val());
	var dom_num = parseInt($("#addParamsCat").attr("dom_num"));

	if(dom_num ==1){
		if(name =='' || sort==''){
			//请将内容填写完整
			alert('Please fill out the complete contents !');
			return false ;
		}
		$.post(
			'/goods/ajaxSaveParamsCat',
			{name:name,sort:sort,params_id:params_id},
			function(data){
				try{
			  		var arr = eval('('+data+')');
			  		var code = arr.code ;
			  		var goods_params_id = arr.params_id ;
			  		if(code==1){
			  			alert('add success!');
			  			var url =  arr.url ; 
			  			location.href=url;
			  			
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
	}else{
		
	}
}

/**
 * 动态渲染需要弹出的内容
 */
function showAddAttr(a){

	var params_id = $(a).attr("params_id");
	var attr_id = $(a).attr("attr_id");
	if(params_id){
		$.post('/goods/ajaxGetTplAtrr',{params_id:params_id,attr_id:attr_id},function(data){
			$("#modal_attr").empty().html(data);
			//加载相对应的提示
		    form_input_notice("#doAttrDomForm input[name='name']",'td');
		    form_input_notice("#doAttrDomForm input[name='explanation']",'td');
		    form_input_notice("#doAttrDomForm input[name='sort']",'td');
			
			add_params_attr_form();
		    

			return true ;
		});
	}else{
		//未保存信息
		//给出相对应的提示
		//请先保存参数模型基本信息 翻译待定
		alert('Please save the parameter model base information');
	}
}

//删除参数模版相关选项
function  removeTplOpt(a){
	
	var p = $(a).parent().parent();
	p.remove();
} 


//添加参数模版相关选项
function addTplOpt(a){
	var rand_num =  new Date().getTime();
	var str = '<tr><td class="center">';
	 str += '<input name="options_name_'+rand_num+'" class="options_input_name" required/><label class="form-control-static reg_tip"></label>';
	 str +='</td><td>';
	 str +='<input name="options_sort_'+rand_num+'" class="options_input_sort" required/><label class="form-control-static reg_tip"></label>';								
	 str +='<label class="col-sm-12 form-control-static reg_default">';
	str +='·You can change the ranking number to determine the ranking position of the current content.';
	str +='·An integer ranging from 1 to 9999 is allowed. An item that has a smaller ranking number is displayed earlier.';
	str +='</label>';
	 str +='</td><td>';							
	 str +='<a href="javascript:void(0);" option_id="0" onclick="removeTplOpt(this)">delete</a>';									
	 str +='</td><tr>';								
	
	var p = $("#table-2 tbody") ;

	p.append(str)	;

	add_params_attr_form();					
								
}


//保存参数模版的名称以及是否开启
function saveParamsTpl(id){
	var name    = $("#tpl_name").val();
	//var status = $("#tpl_is_open").val();
	var name_obj = $("#tpl_name").next().children(".reg_tip");
	if(name ==''){
		name_obj.html('Required');
        name_obj.addClass('red');
		//alert('Please fill out the complete contents !');
	}else if(name.length >50){
		name_obj.html('A maximum of 50 characters can be contained');
        name_obj.addClass('red');
	}else{
		$.post(
			'/goods/ajaxSaveParamsTpl',
			{name:name,id:id},
			function(data){
				try{
			  		var arr = eval('('+data+')');
			  		var code = arr.code ;
			  		if(code==1){
			  			var id = arr.detail;
			  			name_obj.html('');
    					name_obj.removeClass('red');
			  			alert('save success!!');
			  			var url = arr.url ;
			  			location.href =url;
			  			
			  		}else{
			  			name_obj.html(arr.msg);
    					name_obj.addClass('red');
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
}


function createVideoUrl(a){
	$.post(
		'/goods/ajaxCreateVideoUrl',
		{id:1},
		function(data){
			try{
		  		var arr = eval('('+data+')');
		  		var code = arr.code ;
		  		if(code==1){
		  			var detail = arr.detail;
		  			$(a).prev().val(detail);
		  		}else{
		  			alert(arr.msg);
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

//动态渲染选择商品模型选项
function showGoodsModel(a){
	var init_model_id = $(a).attr("init_model_id");
	var init_model_img = $(a).attr("init_model_img");
	var init_goods_model_name =$(a).attr("init_goods_model_name");
	$.post(
		'/goods/ajaxShowGoodsModel',
		{init_model_id:init_model_id,init_model_img:init_model_img,init_goods_model_name:init_goods_model_name},function(data){
			$("#modal_model").empty().html(data);
			return true ;
		}
	);
}

//筛选模型信息
function doModelfilter(a){

	var name              = $("#search_name").val();
	var goods_cate_id     = $("#search_goods_cate_id").val();
	var goods_series_id   = $("#search_goods_series_id").val();
	var is_help_filter    = $(a).attr("is_help_filter");
	var is_faq            = $(a).attr("is_faq");
	var faq_all_model_ids = $(a).attr("faq_all_model_ids");
	$.post(
		'/goods/ajaxGetFilterModels',
		{request_type:'ajax',name:name,goods_cate_id:goods_cate_id,goods_series_id:goods_series_id,is_help_filter:is_help_filter,is_faq:is_faq,faq_all_model_ids:faq_all_model_ids},
		function(data){
			$("#modelDivDom").empty().html(data);
		}
	);
}

//保存模型信息
function saveModelInfo(a){
	var id = $(a).attr("attr_id");
	var url = $(a).attr("attr_url");
	var init_goods_model_name = $(a).attr('init_goods_model_name');
	$("#input_goods_model_id").val(id);
	$("#img_goods_model_id").attr("src",url);
	$("#span_goods_model_name").empty().html(init_goods_model_name);
	$("#close_btn").click();

}

//获取模型参数属性的具体内容
function getParamAttrDetail(a,goods_id){
	var params_id = $(a).val();
	if(params_id){
		$.post(
			'/goods/ajaxGetParamAttrDom',
			{params_id:params_id,goods_id:goods_id},function(data){
				$("#addAttrForm2").empty().html(data);
			}
		);
	}
}

//删除商品相关图片
function delGoodImages(a){

	var image_id  = $(a).attr("image_id");
	var goods_id  = $(a).attr("goods_id");
	if(confirm("delete a picture ?")){
		$.post(
			'/goods/delGoodsImage',
			{image_id:image_id,goods_id:goods_id},
			function(data){
				try{
			  		var arr = eval('('+data+')');
			  		var code = arr.code ;
			  		if(code==1){
			  			alert('delete success!');
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
	

}



function changeGoodImages(a){
	var image_id = $(a).attr("image_id");
	var is_init = $("#div_edit_img").attr("is_init");
	$("#div_edit_img").attr("image_id",image_id);
	if(is_init == 1 ){
		init_goods_image_uploader('div_edit_img','goods_image');
		$("#div_edit_img").attr("is_init",0);
	}
	return $("#div_edit_img").click();
}


function saveImageSort(a,image_id){
	var sort = $(a).attr("attr");
	var val = $(a).val();
	if(val != sort){
		$.post(
			'/goods/ajaxSaveImageSort',
			{sort:val,image_id:image_id},
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
}


//增加商品卖点DOM
function addSellPointDom(a){

	//step1 选择需要增加的模版
	
	$(a).next().click();
	//添加是否为新增模版的标志
	$("#submitTplBtn").attr("is_add",1);

	return false ;


}

//保存选择模版的信息
function saveTplInfo(a){
	var tpl_id        = $(a).attr('tpl_id');
	var tpl_name      = $(a).attr('tpl_name');
	var sell_point_id = $(a).attr('sell_point_id');
	var is_add        = $(a).attr("is_add") ;

	if(tpl_id ==0){

		alert('请选择模版！');

	}else{
		
		if(is_add ==1){
			var tmp_id = new Date().getTime();
			sell_point_id = tmp_id ;
			$(a).attr("sell_point_id",sell_point_id);
			$.post(
				'/goods/ajaxGetSellPointDom',
				{tmp_id:tmp_id},
				function(data){
					$("#submitDom").before(data);
					
					$("#input_"+sell_point_id).val(tpl_id);
					$("#tpl_"+sell_point_id).val(tpl_id);
					$("#lable_tpl_id_"+sell_point_id+" span ").empty().html(tpl_id);
					$("#lable_tpl_name_"+sell_point_id+" span ").empty().html(tpl_name);
					$("#example_img_"+sell_point_id).attr("src",admin_assets_url+"/images/sale_point_"+tpl_id+".png")
					$(".close").click();

					add_sell_point_form();


				}
			);

		}

		$("#input_"+sell_point_id).val(tpl_id);
		$("#tpl_"+sell_point_id).val(tpl_id);
		$("#lable_tpl_id_"+sell_point_id+" span ").empty().html(tpl_id);
		$("#lable_tpl_name_"+sell_point_id+" span ").empty().html(tpl_name);
		$("#example_img_"+sell_point_id).attr("src",admin_assets_url+"/images/sale_point_"+tpl_id+".png")
		$(".close").click();
		
	}
}

//最终保存模版信息
function find_save_tpl(a){
	var tpl_id = $(a).attr("tpl_id");
	$('#submitTplBtn').attr('tpl_id',tpl_id);
	$('#submitTplBtn').attr('tpl_name','Template'+tpl_id);


	var tpl_id        = $('#submitTplBtn').attr('tpl_id');
	var tpl_name      = $('#submitTplBtn').attr('tpl_name');
	var sell_point_id = $('#submitTplBtn').attr('sell_point_id');
	var is_add        = $('#submitTplBtn').attr("is_add") ;

	if(tpl_id ==0){

		alert('请选择模版！');

	}else{
		
		if(is_add ==1){
			var tmp_id = new Date().getTime();
			sell_point_id = tmp_id ;
			$('#submitTplBtn').attr("sell_point_id",sell_point_id);
			$.post(
				'/goods/ajaxGetSellPointDom',
				{tmp_id:tmp_id},
				function(data){
					$("#submitDom").before(data);
					
					$("#input_"+sell_point_id).val(tpl_id);
					$("#tpl_"+sell_point_id).val(tpl_id);
					$("#lable_tpl_id_"+sell_point_id+" span ").empty().html(tpl_id);
					$("#lable_tpl_name_"+sell_point_id+" span ").empty().html(tpl_name);
					$("#example_img_"+sell_point_id).attr("src",admin_assets_url+"/images/sale_point_"+tpl_id+".png")
					$(".close").click();

					add_sell_point_form();


				}
			);

		}

		$("#input_"+sell_point_id).val(tpl_id);
		$("#tpl_"+sell_point_id).val(tpl_id);
		$("#lable_tpl_id_"+sell_point_id+" span ").empty().html(tpl_id);
		$("#lable_tpl_name_"+sell_point_id+" span ").empty().html(tpl_name);
		$("#example_img_"+sell_point_id).attr("src",admin_assets_url+"/images/sale_point_"+tpl_id+".png")
		$(".close").click();
		
	}
}

//更改卖点背景图片
function changeSellPointImages(a){
	var image_id = $(a).prev().attr("id");
	
	var is_init = $("#div_edit_img").attr("is_init");
	$("#div_edit_img").attr("image_id",image_id);
	if(is_init == 1 ){
		init_selling_point_uploader('div_edit_img','selling_point',image_id);
		$("#div_edit_img").attr("is_init",0);
	}
	return $("#div_edit_img").click();
}

//删除卖点
function delSellPoint(a){
	var tmp_id = $(a).attr("tmp_id");
	var sell_id = $(a).attr("real_id");
	var div_id1 = "#row1_"+tmp_id;
	var div_id2 = "#row2_"+tmp_id;
	
	$(div_id1).remove();
	$(div_id2).remove();
	
}