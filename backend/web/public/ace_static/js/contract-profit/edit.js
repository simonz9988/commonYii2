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
            contract_no: {required: true},
            cpsr: {max:50000000, minNumber:$("input[name=cpsr]").val() },
            gzpsr: {max:50000000, minNumber:$("input[name=gzpsr]").val() },
            flsr: {max:50000000, minNumber:$("input[name=flsr]").val() },
            ylfy: {max:50000000, minNumber:$("input[name=ylfy]").val() },
            wxjgfy: {max:50000000, minNumber:$("input[name=wxjgfy]").val() },
            ysfy: {max:50000000, minNumber:$("input[name=ysfy]").val() },
            scfy: {max:50000000, minNumber:$("input[name=scfy]").val() },
            ywfy: {max:50000000, minNumber:$("input[name=ywfy]").val() },
            glfy: {max:50000000, minNumber:$("input[name=glfy]").val() },
            ksfy: {max:50000000, minNumber:$("input[name=ksfy]").val() },
            ccl: {max:50000000, minNumber:$("input[name=ccl]").val() },
            pay_name: {required: true}

        },
        messages: {
            contract_no: {required: "必填"},
            cpsr: {max:'最大50000000',minNumber:'保留小数点后2位'},
            gzpsr: {max:'最大50000000',minNumber:'保留小数点后2位'},
            flsr: {max:'最大50000000',minNumber:'保留小数点后2位'},
            ylfy: {max:'最大50000000',minNumber:'保留小数点后2位'},
            wxjgfy: {max:'最大50000000',minNumber:'保留小数点后2位'},
            ysfy: {max:'最大50000000',minNumber:'保留小数点后2位'},
            scfy: {max:'最大50000000',minNumber:'保留小数点后2位'},
            ywfy: {max:'最大50000000',minNumber:'保留小数点后2位'},
            glfy: {max:'最大50000000',minNumber:'保留小数点后2位'},
            ksfy: {max:'最大50000000',minNumber:'保留小数点后2位'},
            ccl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            pay_name: {required: "必填"}


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
                url: '/contract-profit/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/contract-profit/index';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});