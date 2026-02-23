$(function() {
	init_uploader('icon','channel');
	init_uploader('shop_img','channel');

	
	
	//表单验证内容
	$("#addChanForm").validate({
        rules: {
            name: {
                required: true,
                maxlength:100
            },
            sort:{
                required: false  ,
		        digits:true,
		        range:[1,9999]
            },
            remark:{
                required: false
            }

        },
        messages: {
            name: {
                required: "Required",
                maxlength:'A maximum of 100 characters can be contained'
            },
            sort: {
                required:'Required',
                digits:"Only the integer ranging from 1 to 9999 is allowed",
                range:"Only the integer ranging from 1 to 9999 is allowed"
            },
            remark:{
                required: "Required"
            }
        },
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
        	var channel_type  = $("select[name='type']").val();
        	if(channel_type =='online'){
        		var input_icon = $("#input_icon").val();
	            if(!input_icon){
	                $("#icon_reg_tip").addClass('red').html('Required');

	                return false ;
	            }
        	}else{
        		var input_shop_img = $("#input_shop_img").val();
	            if(!input_shop_img){
	                $("#shop_img_reg_tip").addClass('red').html('Required');

	                return false ;
	            }
        	}
            form.submit();
        }
    });

    show_detail_div(channel_type);

    //加载相对应的提示性函数
    form_input_notice("input[name='name']",'input');
    form_input_notice("input[name='sort']",'input');
    form_input_notice("select[name='type']",'input');
    form_input_notice("select[name='function']",'input');
    form_input_notice("#icon",'input');
    form_input_notice("input[name='shop_url']",'input');
    form_input_notice("#shop_img",'input');
	form_input_notice("textarea[name='address']",'input');
    form_input_notice("textarea[name='remark']",'input');
   
}); 



function change_channel_type(a){
	var type = $(a).val();
	show_detail_div(type);
}

	//动态展示线上线下的div内容以及input 相对应的验证规则
	function show_detail_div(type){
		if(type =='online'){
			$(".online-div").show();
			$(".offline-div").hide();
			$(".offline-div input").removeAttr("required");
			$("textarea").removeAttr("required");
			$(".online-div input").attr("required",'true');
			$(".online-div input[type='file']").attr("required",'true');

			$("input[name='dealer']").rules("add", {  required: false   });
			$("input[name='contact']").rules("add", {  required: false   });
			$("input[name='tel']").rules("add", {  required: false   });
			$("input[name='email']").rules("add", {  required: false   });
			$("textarea[name='address']").rules("add", {  required: false   });
			$("input[name='work_time']").rules("add", {  required: false   });
			$("input[name='shop_url']").rules("add", { 
		        required: true  ,
		        messages: {
		                    required:'Required',
		                    } 
		    });


		}else{
			$(".online-div").hide();
			$(".offline-div").show();
			$(".offline-div input").attr("required",'true');
			$(".offline-div input[type='file']").attr("required",'true');
			$("textarea").attr("required",'true');
			$(".online-div input").removeAttr("required");
			
			//增加属于自己特定的验证规则
			$("input[name='dealer']").rules("add", { 
		        required: true  ,
		        messages: {
		                    required:'Required',
		                    } 
		    });
		    $("input[name='contact']").rules("add", { 
		        required: true  ,
		        messages: {
		                    required:'Required',
		                    } 
		    });
		    $("input[name='tel']").rules("add", { 
		        required: true  ,
		        messages: {
		                    required:'Required',
		                    } 
		    });
		    $("input[name='email']").rules("add", { 
		        required: true  ,
		        messages: {
		                    required:'Required',
		                    } 
		    });
		    $("textarea[name='address']").rules("add", { 
		        required: true  ,
		        messages: {
		                    required:'Required',
		                    } 
		    });
	    	$("input[name='work_time']").rules("add", { 
		        required: true  ,
		        messages: {
		                    required:'Required',
		                    } 
		    });

	    	$("input[name='shop_url']").rules("add", {  required: false   });
		}

		$("input[type='file']").removeAttr("required");
		$("#input_icon").attr("required",'false');
		$("#input_shop_img").attr("required",'false');
	}