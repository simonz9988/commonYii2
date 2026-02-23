$(function() {
	
    //添加js的验证规则
	$.validator.addMethod("isAllowedStr", function (value, element) {
        return this.optional(element) || ( /^[\w\s\/]{1,}$/.test(value));
    }, " The content you entered contains characters that are not allowed .");


	//新增广告页面表带验证
	$("#addNavAddForm").validate({
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
        maxlength:25,
        isAllowedStr:false,
        messages: {
                    required:'Required',
                    maxlength:'A maximum of 25 characters can be contained'
                    } 
    });

    $("input[name='data[subname]']").rules("add", { 
        maxlength:30,
        isAllowedStr:false,
        messages: {
                    maxlength:'A maximum of 30 characters can be contained'
                    } 
    });

    //由于页面的name值特殊定义，validate无法进行元素选择 故需要进行手动添加规则
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

    /*
    $("input[name='data[subname]']").rules("add", { 
        required: true  ,
        maxlength:255,
        messages: {
                    required:'Required',
                    maxlength:'A maximum of 255 characters can be contained'
                    } 
    });
    */
    $("input[name='data[url]']").rules("add", { 
        required: true  ,
        messages: {
                    required:'Required'
                    } 
    });
    $("input[name='data[sort]']").rules("add", { 
        required: true  ,
        messages: {
                    required:'Required'
                    } 
    });


    form_input_notice("input[name='data[name]']",'input');
    form_input_notice("input[name='data[sort]']",'input');
    form_input_notice("input[name='data[subname]']",'input');
    form_input_notice("select[name='data[type]']",'input');
    form_input_notice("input[name='data[url]']",'input');
    form_input_notice("select[name='data[goods_cate_id]']",'input');
}); 



