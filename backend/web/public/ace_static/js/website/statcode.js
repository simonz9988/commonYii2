$(function() {
	
	
	
	
	
	//新增广告页面表带验证
	$("#addChanForm").validate({
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

    //由于页面的name值特殊定义，validate无法进行元素选择 故需要进行手动添加规则
	$("#statcode").rules("add", { 
        required: true  ,
        messages: {
                    required:'Required',
                    } 
    });

 	//增加悬浮提示
    form_input_notice("#statcode",'input');
}); 



