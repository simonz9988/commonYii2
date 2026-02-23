$(function() {
    jQuery.validator.addMethod("minNumber4",function(value, element){
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
    },"小数点后最多为4位");         //验证错误信息

    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            mobile: {required: true},
            api_key: {required: true},
            api_secret: {required: true},
            passphrase: {required: true},
            buying_points: {required: true},
            buying_quantity: {required: true},
            gap_percent: {required: true},
            quantity: {required: true},
            min_price: {required: true},
            min_qty: {required: true},
            max_orders: {required: true},
            transaction_fee: {required: true},
            price_ceiling: {required: true},
            price_floor: {required: true},

            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            mobile: {required: "必填"},
            api_key: {required: "必填"},
            api_secret: {required: "必填"},
            buying_points: {required: "必填"},
            buying_quantity: {required: "必填"},
            quantity: {required: "必填"},
            min_price: {required: "必填"},
            min_qty: {required: "必填"},
            max_orders: {required: "必填"},
            transaction_fee: {required: "必填"},
            price_ceiling: {required: "必填"},
            price_floor: {required: "必填"},

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
                url: '/coin-user/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/coin-user/list';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });
}); 

