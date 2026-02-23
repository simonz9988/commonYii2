$(function() {

    jQuery.validator.addMethod("minNumber",function(value, element){
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
    },"小数点后最多为6位");         //验证错误信息

    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            name: {required: true},
            start_time: {required: true},
            end_time: {required: true},
            total: {
                required: true,
                number: true,
                min: 0.000001,
                minNumber: $("#input_total").val()
            },
            useful_percent:{
                required: true,
                digits:true,
                range:[1,9999]
            },
            daily_add:{
                required: true,
                number: true,
                min: 0.000001,
                minNumber: $("#input_daily_add").val()
            },
            frozen:{
                required: true,
                number: true,
                min: 0.000001,
                minNumber: $("#input_frozen").val()
            },
            unit_earn: {
                required: true,
                number: true,
                min: 0.000001,
                minNumber: $("#input_unit_earn").val()
            },


            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            name: {required: "必填"},
            start_time: {required: "必填"},
            end_time: {required: "必填"},
            total: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.00001",
                minNumber:'最低为小数点后6位'
            },
            useful_percent: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },
            daily_add: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.00001",
                minNumber:'最低为小数点后6位'
            },
            frozen:{
                required: "必填",
                number:"必须为数字",
                min:"最小0.00001",
                minNumber:'最低为小数点后6位'
            },
            unit_earn: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.00001",
                minNumber:'最低为小数点后6位'
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
                url: '/mining-machine/activity-save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')') ;
                    if(arr.code == 1){
                        location.href='/mining-machine/activity-list';
                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});