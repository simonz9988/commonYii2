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

    init_uploader('cover_img_url','ad');
    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            coin: {required: true},
            title: {required: true},
            activity_id: {required: true},
            sub_title: {required: true},
            price: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#input_price").val()
            },
            period: {
                required: true,
                digits:true,
                range:[1,9999]
            },
            limit_day: {
                required: true,
                digits:true,
                range:[1,9999]
            },
            fee: {
                required: true,
                number: true,
                min: 0.01,
                max: 99.99,
                minNumber: $("#input_fee").val()
            },
            store_num: {
                required: true,
                digits:true,
                range:[1,999999]
            },
            link_url: {required: true},
            cover_img_url: {required: true},
            calc_power:{
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
            coin: {required: "必填"},
            title: {required: "必填"},
            activity_id: {required: "必填"},
            sub_title: {required: "必填"},
            price: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },
            period: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },
            limit_day: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },
            fee: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                max:"最大99.99",
                minNumber:'最低为小数点后2位'
            },
            store_num: {
                required: "必填",
                digits:"必须为1~999999的整数",
                range:"必须为1~999999的整数"
            },
            link_url: {required: "必填"},
            cover_img_url: {required: "必填"},
            calc_power:{
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
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
            $('#systeamAddMenu :submit').prop("disabled", false);
            $.ajax({
                type: 'post',
                url: '/mining-machine/save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/mining-machine/index';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});