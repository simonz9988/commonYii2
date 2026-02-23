$(function() {

    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            password: {required: true},
            cash_password: {required: true},
            mobile: {required: true},
            img_url: {required: true},
            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            password: {required: "必填"},
            cash_password: {required: "必填"},
            mobile: {required: "必填"},
            img_url: {required: "必填"},

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
            $('#systeamAddMenu :submit').prop("disabled", false);
            $.ajax({
                type: 'post',
                url: '/member/save1',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/member/list';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});