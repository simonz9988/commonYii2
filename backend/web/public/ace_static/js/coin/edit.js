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

    jQuery.validator.addMethod("minNumber6",function(value, element){
        var returnVal = true;
        inputZ=value;
        var ArrMen= inputZ.split(".");    //截取字符串
        if(ArrMen.length==6){
            if(ArrMen[1].length>6){    //判断小数点后面的字符串长度
                returnVal = false;
                return false;
            }
        }
        return returnVal;
    },"小数点后最多为六位");         //验证错误信息

    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            name: {required: true},
            alias: {required: true},
            unique_key: {required: true},
            city: {required: true},
            amount: {required: true,min: 100,max:50000, minNumber:$("#input_amount").val() },
            earn_high_percent: {required: true,min: 0.01,max:50000, minNumber:$("#input_earn_high_percent").val() },
            earn_low_percent: {required: true,min: 0.01,max:50000, minNumber:$("#input_earn_low_percent").val() },
            usdt_buying_points: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_usdt_buying_points").val() },
            usdt_max_buying_points: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_usdt_max_buying_points").val() },
            btc_buying_points: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_btc_buying_points").val() },
            btc_max_buying_points: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_btc_max_buying_points").val() },
            eth_buying_points: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_eth_buying_points").val() },
            eth_max_buying_points: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_eth_max_buying_points").val() },
            usdt_depth: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_usdt_depth").val() },
            eth_depth: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_eth_depth").val() },
            btc_depth: {required: true,min: 0.00001,max:50000, minNumber6:$("#input_ebtc_depth").val() },

            bank_account: {required: true},
            min_qty: {required: true},
            bank_name: {required: true},
            pay_name: {required: true},
            code: {required: true},
            max_block:{
                required: true,
                digits:true,
                range:[1,9999]
            },
            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            name: {required: "必填"},
            alias: {required: "必填"},
            unique_key: {required: "必填"},
            city: {required: "必填"},
            amount: {required: "必填",min:"必须为大于等于100数字",max:"须为小于等于50000数字"},
            earn_high_percent: {required: "必填",min:"必须为大于等于0.01数字",max:"须为小于等于50000数字"},
            earn_low_percent: {required: "必填",min:"必须为大于等于0.01数字",max:"须为小于等于50000数字"},
            usdt_buying_points: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            usdt_max_buying_points: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            btc_buying_points: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            btc_max_buying_points: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            eth_buying_points: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            eth_max_buying_points: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            usdt_depth: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            eth_depth: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            btc_depth: {required: "必填",min:"必须为大于等于0.00001数字",max:"须为小于等于50000数字"},
            bank_account: {required: "必填"},
            min_qty: {required: "必填"},
            pay_time: {required: "必填"},
            bank_name: {required: "必填"},
            bank_detail: {required: "必填"},
            code: {required: "必填"},
            max_block: {
                required: "Required",
                digits:"Only the integer ranging from 1 to 9999 is allowed",
                range:"Only the integer ranging from 1 to 9999 is allowed"

            },
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
                url: '/coin/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        window.location.reload();

                    }else{
                        layer.alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });
}); 

