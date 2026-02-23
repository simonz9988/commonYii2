$(function() {


    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            init_zoom: {required: true},
            init_longitude: {required: true},
            init_latitude: {required: true}

        },
        messages: {
            init_zoom: {required: "必填"},
            init_longitude: {required: "必填"},
            init_latitude: {required: "必填"}
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
                url: '/sunny-device/save-init-setting',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        window.location.reload();

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