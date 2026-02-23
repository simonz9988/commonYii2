$(function() {
    //表单验证内容
    $("#doAddSymbolForm").validate({
        rules: {
            key: {
                required: true
            },
            down_percent: {
                required: true
            }
        },
        messages: {
            name: {
                required: "必填"
            },
            down_percent: {
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

