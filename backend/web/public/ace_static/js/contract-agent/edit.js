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
            customer_name: {required: true},
            //lailiao_diameter: {max:50000000, minNumber:$("input[name=lailiao_diameter]").val() },
            //lailiao_long: {max:50000000, minNumber:$("input[name=lailiao_long]").val() },
            //jiagong_diameter: {max:50000000, minNumber:$("input[name=jiagong_diameter]").val() },
            //jiagong_long: {max:50000000, minNumber:$("input[name=jiagong_long]").val() },
            dingdl: {max:50000000, minNumber:$("input[name=dingdl]").val() },
            llzl: {max:50000000, minNumber:$("input[name=llzl]").val() },
            kszl: {max:50000000, minNumber:$("input[name=kszl]").val() },
            jiagongdj: {max:50000000, minNumber:$("input[name=jiagongdj]").val() },
            jiagongje: {max:50000000, minNumber:$("input[name=jiagongje]").val() },
            cpchl: {max:50000000, minNumber:$("input[name=cpchl]").val() },
            canliaochl: {max:50000000, minNumber:$("input[name=canliaochl]").val() },
            wjgfhzl: {max:50000000, minNumber:$("input[name=canliaochl]").val() },
            ccl: {max:50000000, minNumber:$("input[name=canliaochl]").val() },
            fcl: {max:50000000, minNumber:$("input[name=canliaochl]").val() },
            pay_name: {required: true}

        },
        messages: {
            contract_no: {required: "必填"},
            customer_name: {required: "必填"},
            //lailiao_diameter: {max:'最大50000000',minNumber:'保留小数点后2位'},
            //lailiao_long: {max:'最大50000000',minNumber:'保留小数点后2位'},
            //jiagong_diameter: {max:'最大50000000',minNumber:'保留小数点后2位'},
            //jiagong_long: {max:'最大50000000',minNumber:'保留小数点后2位'},
            dingdl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            llzl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            kszl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            jiagongdj: {max:'最大50000000',minNumber:'保留小数点后2位'},
            jiagongje: {max:'最大50000000',minNumber:'保留小数点后2位'},
            cpchl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            canliaochl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            wjgfhzl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            ccl: {max:'最大50000000',minNumber:'保留小数点后2位'},
            fcl: {max:'最大50000000',minNumber:'保留小数点后2位'},
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
                url: '/contract-agent/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/contract-agent/index';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});