$(function() {
	

	
	
	//表单验证内容
	$("#addUrlForm").validate({
        rules: {
            url: {
                required: true,
                maxlength:255
            },
            mapped_url: {
                required: true,
                maxlength:255
            }

        },
        messages: {
            url: {
                required: "Required",
                maxlength:'A maximum of 255 characters can be contained'
            },
            mapped_url: {
                required: "Required",
                maxlength:'A maximum of 255 characters can be contained'
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
        	
            form.submit();
        }
    });

    

    //加载相对应的提示性函数
    form_input_notice("input[name='url']",'input');
    form_input_notice("input[name='mapped_url']",'input');
    //form_input_notice("select[name='lang_category']",'input');
    
   
}); 


