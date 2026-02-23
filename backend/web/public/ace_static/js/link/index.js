$(function() {
    
	//友情链接编辑页面
    $("#addLinkIndex").validate({
        
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

    $("#addLinkIndex input[name='link[linkword]']").rules("add", { 
        required: true  ,
        maxlength:50,
        messages: {
                    required:'Required',
                    maxlength:'A maximum of 50 characters can be contained'
                    } 
    });

    

    //增加悬浮提示
    form_input_notice("input[name='link[linkword]']",'input');
    form_input_notice("select[name='link[status]']",'input');


}); 



