$(function() {
    jQuery.validator.addMethod("minNumber",function(value, element){
        var returnVal = true;
        inputZ=value;
        var ArrMen= inputZ.split(".");    //截取字符串
        if(ArrMen.length==2){
            if(ArrMen[1].length>2){    //判断小数点后面的字符串长度
                returnVal = false;
                return false;
            }
        }
        return returnVal;
    },"小数点后最多为两位");         //验证错误信息

    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            bank_no: {required: true},
            coin_type: {required: true},
            province: {required: true},
            city: {required: true},
            amount: {required: true,min: 100,max:50000, minNumber:$("#input_amount").val() },

            bank_account: {required: true},
            bank_name: {required: true},
            pay_name: {required: true},
            code: {required: true},

            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            bank_no: {required: "必填"},
            coin_type: {required: "必填"},
            province: {required: "必填"},
            city: {required: "必填"},
            amount: {required: "必填",min:"必须为大于等于100数字",max:"须为小于等于50000数字"},
            bank_account: {required: "必填"},
            pay_time: {required: "必填"},
            bank_name: {required: "必填"},
            bank_detail: {required: "必填"},
            code: {required: "必填"},

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

            $.ajax({
                type: 'post',
                url: '/cash/save-out',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        layer.alert(arr.msg,function(){
                            parent.window.location.reload();
                        });

                    }else{
                        layer.alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });
}); 

