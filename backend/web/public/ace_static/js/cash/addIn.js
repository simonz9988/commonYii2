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
            order_no: {required: true},
            name: {required: true},
            pay_time: {required: true},
            pay_name: {required: true},
            amount: {required: true,min: cash_in_min_amount, minNumber:$("#input_amount").val() },
            coin_type: {required: true},
            pay_type: {required: true},

            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            order_no: {required: "必填"},
            name: {required: "必填"},
            pay_time: {required: "必填"},
            pay_name: {required: "必填"},
            amount: {required: "必填",min:"必须为大于等于"+cash_in_min_amount+"数字"},
            coin_type: {required: "必填"},
            pay_type: {required: "必填"},

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

            form.submit();
        }
    });
}); 

