$(function() {


    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            name: {required: true},
            backend_name: {required: true},
            unique_key: {required: true},
            link_url: {required: true},
            img_url: {required: true},
            led_current_set: {
                required: true,
                range:[0.15,10]
            },
            charge_port_num:{
                required: true,
                digits:true,
                range:[0,9999]
            }

        },
        messages: {
            name: {required: "必填"},
            backend_name: {required: "必填"},
            unique_key: {required: "必填"},
            link_url: {required: "必填"},
            img_url: {required: "必填"},
            led_current_set: {
                required: "Required",
                range:"只允许填写0.15~10"

            },
            charge_port_num: {
                required: "Required",
                digits:"Only the integer ranging from 0 to 9999 is allowed",
                range:"Only the integer ranging from 0 to 9999 is allowed"

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
                url: '/sunny-device-category/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/sunny-device-category/index';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});