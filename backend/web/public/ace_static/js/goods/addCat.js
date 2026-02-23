$(function() {

    init_uploader('compare_img_path','goods_category');
    //添加js的验证规则
    $.validator.addMethod("isAllowedStr", function (value, element) {
        return this.optional(element) || ( /^[\w\s\/\uff0c\u3001\u3002\u201c\u201d\u2018\u2019\u007e\uff08\uff09\u005b\u005d\u2026\u2026\uff1a\uff01\u300a\u300b\uff0d\uff03\uff05\u0026\u006c\u0074\u003b\u0026\u0067\u0074\u003b\u007b\u007d\u0040\uff1f\uff1b\u0026\u0023\u0031\u0036\u0033\u003b\u0026\u0023\u0031\u0036\u0035\u003b\u20ae\u20a9\u20aa\u09f3\u20a8\u20b1\u20b0\u20a4\u20a3\u20ac\u20af\u20a2\u0e3f\u20b4\u0053\u0024\u004b\u0072\u062f\u002e\u0625\u0026\u0023\u0031\u0036\u0033\u003b\uffe5\u0024\u0024\u2c60]{1,}$/.test(value));
    }, " The content you entered contains characters that are not allowed .");

	//表单验证内容
	$("#addAddCatForm").validate({
        rules: {
            name: {
                required: true,
                maxlength:50,
                isAllowedStr:false
            },
            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            name: {
                required: "Required",
                maxlength:'A maximum of 50 characters can be contained'
            },
            sort: {
                required: "Required",
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
            var input_compare_img_path = $("#input_compare_img_path").val();
            if(!input_compare_img_path){
                $("#compare_img_path_reg_tip").addClass('red').html('Required');

                return false ;
            }
            form.submit();
        }
    });

    form_input_notice("input[name='name']",'input');
    form_input_notice("input[name='sort']",'input');
    form_input_notice("select[name='goods_params_id']",'input');
});

