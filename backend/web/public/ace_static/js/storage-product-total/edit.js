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
            from_date: {required: true},
            name: {required: true},
            diameter: {max:mh_max_num, minNumber:$("input[name=diameter]").val() },
            long: {max:mh_max_num, minNumber:$("input[name=long]").val() },
            amount: {max:mh_max_num, minNumber:$("input[name=amount]").val() },
            weight: {max:mh_max_num, minNumber:$("input[name=weight]").val() },
            pay_name: {required: true}

        },
        messages: {
            from_date: {required: "必填"},
            name: {required: "必填"},
            diameter: {max:mh_max_num_notice,min:mh_min_num_notice},
            long: {max:mh_max_num_notice,min:mh_min_num_notice},
            amount: {max:mh_max_num_notice,min:mh_min_num_notice},
            weight: {max:mh_max_num_notice,min:mh_min_num_notice},
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
                url: '/storage-product-total/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/storage-product-total/index';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});