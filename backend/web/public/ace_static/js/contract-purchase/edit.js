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
            supplier_name: {required: true},
            //diameter: {max:50000000, minNumber:$("input[name=diameter]").val() },
            //long: {max:50000000, minNumber:$("input[name=long]").val() },
            purchase_volume: {max:50000000, minNumber:$("input[name=purchase_volume]").val() },
            cgl: {max:50000000, minNumber:$("input[name=cgl]").val() },
            cgdj: {max:50000000, minNumber:$("input[name=cgdj]").val() },
            yinfkze: {max:50000000, minNumber:$("input[name=yinfkze]").val() },
            yifukuanze: {max:50000000, minNumber:$("input[name=yifukuanze]").val() },
            fkce: {max:50000000, minNumber:$("input[name=fkce]").val() },
            pay_name: {required: true}

        },
        messages: {
            contract_no: {required: "必填"},
            supplier_name: {required: "必填"},
            //diameter: {max:'最大50000000',minNumber:'保留小数点后2位'},
            //long: {max:'最大50000000',minNumber:'保留小数点后2位'},
            purchase_volume: {max:'最大50000000',minNumber:'保留小数点后2位'},
            cgl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            cgdj: {max:'最大50000000',minNumber:'保留小数点后2位'},
            yinfkze: {max:'最大50000000',minNumber:'保留小数点后2位'},
            yifukuanze: {max:'最大50000000',minNumber:'保留小数点后2位'},
            fkce: {max:'最大50000000',minNumber:'保留小数点后2位'},
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
                url: '/contract-purchase/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/contract-purchase/index';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});