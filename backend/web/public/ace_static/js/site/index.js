$(function(){



    jQuery.validator.addMethod("isphoneNum", function(value, element) {
        var length = value.length;
        var mobile = /^1[3|5|7|8]{1}[0-9]{9}$/;
        return this.optional(element) || (length == 11 && mobile.test(value));
    }, "请正确填写您的手机号码");

    $("#loginForm").validate({
        rules: {

            address:{
                required:true
            }
        },
        messages: {

            address: {
                required: '请输入详细地址'
            }
        },
        submitHandler: function(form)
        {
            var submit_url = $(form).attr("action");
            $.post(
                submit_url,
                $('#loginForm').serialize(),
                function(rs){
                    var arr = eval('('+rs+')');
                    if(arr.code == '1'){
                        var url  =arr.url ;
                        location.href =url;
                    }else{
                        alert(arr.msg);
                    }
                }
            );
        }
    });
});