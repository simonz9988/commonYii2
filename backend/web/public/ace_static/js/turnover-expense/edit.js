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
            username: {required: true},
            detail: {required: true},
            invoice_no: {required: true},
            expense_no: {required: true},
            baoxiao_date: {required: true},
            amount: {max:50000000, minNumber:$("input[name=diameter]").val() },
            balance: {max:50000000, minNumber:$("input[name=diameter]").val() },
            pay_name: {required: true}

        },
        messages: {
            username: {required: "必填"},
            detail: {required: "必填"},
            invoice_no: {required: "必填"},
            expense_no: {required: "必填"},
            baoxiao_date: {required: "必填"},
            amount: {max:'最大50000000',minNumber:'保留小数点后2位'},
            balance: {max:'最大50000000',minNumber:'保留小数点后2位'},
            long: {max:'最大50000000',minNumber:'保留小数点后2位'},


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
                url: '/turnover-expense/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/turnover-expense/index';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});