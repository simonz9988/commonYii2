$(function() {


    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            short: {required: true},
            country_name: {required: true},
            pay_time: {required: true},
            pay_name: {required: true},


            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            short: {required: "必填"},
            country_name: {required: "必填"},
            pay_time: {required: "必填"},
            pay_name: {required: "必填"},


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
                url: '/country/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/country/index';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});