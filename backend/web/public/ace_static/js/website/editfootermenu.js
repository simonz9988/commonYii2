$(function() {
	
	
	//新增广告页面表带验证
	$("#addFootermenuForm").validate({
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
	$("input[name='data[name]']").rules("add", { 
        required: true  ,
        maxlength:80,
        messages: {
                    required:'Required',
                    maxlength:'A maximum of 80 characters can be contained'
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
        digits:true,
        range:[1,9999],
        messages: {
                    required:'Required',
                    digits:"Only the integer ranging from 1 to 9999 is allowed",
                    range:"Only the integer ranging from 1 to 9999 is allowed"
                    } 
    });


    form_input_notice("select[name='data[page_cate_id]']",'input');
    form_input_notice("input[name='data[name]']",'input');
    form_input_notice("input[name='data[url]']",'input');
    form_input_notice("input[name='data[sort]']",'input');
}); 



