$(function() {

	
	//友情链接编辑页面
    $("#addParamsCatForm").validate({
        
        errorPlacement: function (error, element) {
            var errplace = element.parent().next().children('.reg_tip');
            errplace.html(error.text());
            errplace.addClass('red');
        },
        success: function (label, element) {
            var obj = $(element).parent().next().children('.reg_tip');
            obj.html('');
            obj.removeClass('red');
        },
        submitHandler: function (form) {
        	form.submit();
        }
    });



});

//新增加添加参数分类的DOM元素
//目前限制添加一个
function addParamsCat(a){

	var dom_num = parseInt($(a).attr("dom_num"));
	
	if(dom_num ==0){
		var Tbody = $("#paramsCatTbody");
		var str = '<tr class="addParamsCat">';
		str += '<td><div class="col-sm-3" ><input id="addInputName" name="name_new"/></div>';
		str +='<div class="col-sm-3" >';
		str +='<label class="col-sm-12 form-control-static reg_tip">';
		str +='</label>';
		str +='<label class="col-sm-12 form-control-static reg_default_td left_15">';
		str +='</label>';
		str +='</div></td>';
		str +='<td><div class="col-sm-3" ><input id="addInputSort" name="sort_new"/></div>';
		str +='	<div class="col-sm-3" >';
		str +='<label class="col-sm-12 form-control-static reg_tip">';
		str +='	</label>';
		str +='<label class="col-sm-12 form-control-static reg_default_td left_15">';
		str +='</label></td>';
		str += '<td>';
		str += '<a class="btn btn-xs btn-danger cus-btn-del" href="javascript:void(0);" onclick="delAddDom(this)"><i class="ace-icon fa fa-trash-o"></i>Delete</a></td>';
		str += '</tr>';
		$(a).attr("dom_num",1);
		Tbody.append(str);

		$("input[name='sort_new']").rules("add", { 
	        required: true  ,
	        messages: {
	                    required:'Required',
	                    } 
	    });

	    $("input[name='name_new']").rules("add", { 
	        required: true  ,
	        messages: {
	                    required:'Required',
	                    } 
	    });
	}
}

//删除需要添加的DOM 元素
function delAddDom(a){
	$(a).parent().parent().remove();
	$("#addParamsCat").attr("dom_num",0);
}



function edit_params_cate(a){
	
	var id = $(a).attr("attr_id");
	var name = $(a).attr("attr_name");
	var sort = $(a).attr("attr_sort");

	var p = $(a).parent().prev().children(":eq(0)");
	var p_p = $(a).parent().prev().prev().children(":eq(0)");
	var timestamp = Date.parse(new Date());
	p.empty().append('<input type="text" class="edit_input" name="sort_edit_'+timestamp+'" value="'+sort+'"><input type="hidden" name="id_edit_'+timestamp+'" value="'+id+'">');
	p_p.empty().append('<input type="text" class="edit_input" name="name_edit_'+timestamp+'" value="'+name+'">');

	$(".edit_input").each(function(){
		$(this).rules("add", { 
	        required: true  ,
	        messages: {
	                    required:'Required',
	                    } 
	    });
	});

}

