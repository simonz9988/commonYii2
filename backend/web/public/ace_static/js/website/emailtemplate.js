$(function() {
	
	//新增广告页面表带验证
	$("#addEmailtemplateForm").validate({
        rules: {
            regist_subject: {
                required: true
            },
            forgetpwd_subject:{
                required: true
            },
            resetpwd_subject:{
                required: true
            }
        },
        messages: {
            regist_subject: {
                required: "Required"
            },
            forgetpwd_subject: {
                required: "Required"
            },
            resetpwd_subject:{
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
            form.submit();
        }
    });

    
 	//增加悬浮提示
    form_input_notice("#forgetpwd_div",'div');
}); 



