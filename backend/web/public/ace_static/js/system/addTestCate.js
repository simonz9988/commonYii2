$(function() {
    //表单验证内容
    $("#addCateForm").validate({
        rules: {
            name: {
                required: true
            },
            unique_key: {
                required: true
            },
            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            name: {
                required: "必填"
            },
            unique_key: {
                required: "必填"
            },
            
            sort: {
                required: "必填",
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

            form.submit();
        }
    });
});

