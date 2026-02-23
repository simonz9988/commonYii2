$(function() {
    //表单验证内容
    $("#siteConfigForm").validate({
        rules: {
            config_key: {
                required: true
            },
            config_desc: {
                required: true
            },
            config_value: {
                required: true
            }

        },
        messages: {
            config_key: {
                required: "必填"
            },
            config_desc: {
                required: "必填"
            },
            config_value: {
                required: "必填"
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
}); 

