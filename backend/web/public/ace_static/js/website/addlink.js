$(function() {
    init_uploader('icon','infolist');
	//友情链接编辑页面
    $("#addLinkForm").validate({
        
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

    $("input[name='data[name]']").rules("add", { 
        required: true  ,
        messages: {
                    required:'Required',
                    } 
    });

    $("input[name='data[url]']").rules("add", { 
        required: true  ,
        messages: {
                    required:'Required',
                    } 
    });

    $("input[name='data[sort]']").rules("add", { 
        required: true  ,
        messages: {
                    required:'Required',
                    } 
    });

    //增加悬浮提示
    form_input_notice("input[name='data[name]']",'input');
    form_input_notice("#icon",'input');
    form_input_notice("input[name='data[url]']",'input');
    form_input_notice("input[name='data[status]']",'input');


}); 



