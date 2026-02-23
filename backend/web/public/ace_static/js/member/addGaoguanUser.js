$(function(){
    jQuery.validator.addMethod("isphoneNum", function(value, element) {
        var length = value.length;
        var mobile = /^1[3|5|7|8]{1}[0-9]{9}$/;
        return this.optional(element) || (length == 11 && mobile.test(value));
    }, "请正确填写您的手机号码");

    $("#systeamAddMenu").validate({
        rules: {
            mobile: {
                required:true
            },
            nickname: {
                required: true
            },
            sort: {
                required: true
            }
        },
        messages: {
            mobile: {
                required: '请输入手机号码'

            },
            nickname: {
                required: '请输入昵称'
            },
            sort: {
                required: '排序'
            }
        },
        submitHandler: function(form)
        {
            $.post(
                '/adminPage/member/doAddGaoguanUser',
                $('#systeamAddMenu').serialize(),
                function(rs){
                    var arr = eval('('+rs+')');
                    if(arr.code == '1'){
                        alert("保存成功");
                        location.reload();
                    }else{
                        alert(arr.msg);
                    }
                }
            );
        }
    });
});