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

    //init_uploader('cover_img_url','ad');
    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            jiangli_keyong_bili: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#jiangli_keyong_bili").val()
            },
            jiangli_dongjie_bili: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#jiangli_dongjie_bili").val()
            },
            fenxiang1_total: {
                required: true,
                digits:true,
                range:[1,9999]
            },
            fenxiang1_level1: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#fenxiang1_level1").val()
            },
            fenxiang1_level2: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#fenxiang1_level2").val()
            },

            fenxiang2_total: {
                required: true,
                digits:true,
                range:[1,9999]
            },
            fenxiang2_level1: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#fenxiang2_level1").val()
            },
            fenxiang2_level2: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#fenxiang2_level2").val()
            },

            p1_tuandui_yeji: {
                required: true,
                digits:true,
                range:[1,9999]
            },
            p1_xinzeng_yeji_bili: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#p1_xinzeng_yeji_bili").val()
            },
            p1_qita_yeji: {
                required: true,
                digits:true,
                range:[1,9999]
            },


            p2_tuandui_yeji: {
                required: true,
                digits:true,
                range:[1,9999]
            },
            p2_xinzeng_yeji_bili: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#p2_xinzeng_yeji_bili").val()
            },
            p2_qita_yeji: {
                required: true,
                digits:true,
                range:[1,9999]
            },

            p3_tuandui_yeji: {
                required: true,
                digits:true,
                range:[1,9999]
            },
            p3_xinzeng_yeji_bili: {
                required: true,
                number: true,
                min: 0.01,
                minNumber: $("#p3_xinzeng_yeji_bili").val()
            },
            p3_qita_yeji: {
                required: true,
                digits:true,
                range:[1,9999]
            }


        },
        messages: {
            jiangli_keyong_bili: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },
            jiangli_dongjie_bili: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },
            fenxiang1_total: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },
            fenxiang1_level1: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },

            fenxiang1_level2: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },

            fenxiang2_total: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },
            fenxiang2_level1: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },

            fenxiang2_level2: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },


            p1_tuandui_yeji: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },
            p1_xinzeng_yeji_bili: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },

            p1_qita_yeji: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },

            p2_tuandui_yeji: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },
            p2_xinzeng_yeji_bili: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },

            p2_qita_yeji: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },

            p3_tuandui_yeji: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
            },
            p3_xinzeng_yeji_bili: {
                required: "必填",
                number:"必须为数字",
                min:"最小0.01",
                minNumber:'最低为小数点后2位'
            },

            p3_qita_yeji: {
                required: "必填",
                digits:"必须为1~9999的整数",
                range:"必须为1~9999的整数"
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
                url: '/mining-machine/setting-save',
                data: $("form").serialize(),
                success: function(data) {
                    // your code
                    var arr = eval('('+data+')')
                    if(arr.code == 1){
                        alert(arr.msg)
                        location.href='/mining-machine/setting';

                    }else{
                        alert(arr.msg);
                    }
                }
            });
            return false ;
        }
    });

});