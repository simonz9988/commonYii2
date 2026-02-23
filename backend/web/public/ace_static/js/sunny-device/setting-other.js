$(function() {


    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            name: {required: true},
            unique_key: {required: true},
            link_url: {required: true},
            led_current_set: {
                required: true,
                range:[0.02,10]
            },
            seconds:{
                required: true,
                digits:true,
                range:[0,54000]
            },
            load_sensor_on_power:{
                required: true,
                digits:true,
                range:[0,100]
            }

        },
        messages: {
            name: {required: "必填"},
            unique_key: {required: "必填"},
            link_url: {required: "必填"},
            led_current_set: {
                required: "Required",
                range:"只允许填写0.15~10"

            },
            seconds: {
                required: "Required",
                digits:"只允许填写0~54000",
                range:"只允许填写0~54000"

            },
            load_sensor_on_power: {
                required: "Required",
                digits:"只允许填写0~100",
                range:"只允许填写0~100"

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
                url: '/sunny-device/setting-other-save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/sunny-device-category/setting-list';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });


    $(".input_seconds").each(function(){
        $(this).rules("add", {
            required: "Required",

            digits:true,
            range:[0,900],
            messages: {
                required: "必填",
                digits:"只允许填写0~900",
                range:"只允许填写0~900"
            }
        });
    });

});