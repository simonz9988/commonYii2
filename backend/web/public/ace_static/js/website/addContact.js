$(function() {
    $.validator.addMethod("isAllow", function (value, element) {
        return this.optional(element) || ( /^[\d\s\+\-\,]{8,}$/.test(value));
    }, "Format of a phone number is incorrect.");
	//视频新增编辑页面
    $("#addContactForm").validate({
        rules: {
            name: {
                required: true,
                maxlength:150
            },
            pre_sales_tel:{
                required: true,
                isAllow:true
            },
            pre_sales_time:{
                required: true
            },
            pre_sales_email:{
                required: true
            },
            customer_tel:{
                required: false,
                isAllow:true
            },
            customer_time:{
                required: false
            },
            customer_email:{
                required: false
            },
            customer_address:{
                required: false
            },
            sort:{
                required: false,
                digits:false,
                range:[1,9999]
            }

        },
        messages: {
            name: {
                required: "Required",
                maxlength:'A maximum of 150 characters can be contained'
            },
            pre_sales_tel: {
                required: "Required"
            },
            pre_sales_time:{
                required: "Required"
            },
            pre_sales_email:{
                required: "Required"
            },
            customer_tel: {
                required: "Required"
            },
            customer_time:{
                required: "Required"
            },
            customer_email:{
                required:  "Required"
            },
            customer_address:{
                required:  "Required"
            },
            sort:{
                required:  "Required",
                digits:"Only the integer ranging from 1 to 9999 is allowed",
                range:"Only the integer ranging from 1 to 9999 is allowed"
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
             var input_icon = $("#input_icon").val();
            if(!input_icon){
                $("#icon_reg_tip").addClass('red').html('Required');

                return false ;
            }
            form.submit();
        }
    });

    //增加悬浮提示
    form_input_notice("input[name='name']",'input');
    form_input_notice("#icon",'input');
    form_input_notice("input[name='pre_sales_tel']",'input');
    form_input_notice("input[name='pre_sales_time']",'input');
    form_input_notice("input[name='pre_sales_email']",'input');
    form_input_notice("input[name='customer_tel']",'input');
    form_input_notice("input[name='customer_time']",'input');
    form_input_notice("input[name='customer_email']",'input');
    form_input_notice("input[name='customer_address']",'input');
    form_input_notice("input[name='sort']",'input');


}); 



