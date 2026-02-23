
$(function() {
    init_uploader('goods_img_url','goods');

    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            goods_desc: {required: true},
            amount:{required: true},
            start_time:{required: true},
            end_time:{required: true},
            note:{required: true},
            notice:{required: true}
        },
        messages: {
            goods_desc: {required: "必填"},
            amount: {required: "必填"},
            start_time: {required: "必填"},
            end_time: {required: "必填"},
            note: {required: "必填"},
            notice: {required: "必填"}
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

            var img_url = $("#input_goods_img_url").val();
            if(!img_url){
                $("#goods_img_urll_reg_tip").addClass('red').html('必填');
                return false ;
            }

            $.ajax({
                cache: true,
                type: "POST",
                url:'/adminPage/exchange/ajaxSaveGoods',
                data:$('#systeamAddMenu').serialize(),// 你的formid
                async: false,
                error: function(request) {
                    alert("Connection error");
                },
                success: function(data) {
                    var arr =eval('('+data+')');
                    if(arr.code ==1){
                        location.href = '/adminPage/exchange/goodsList' ;
                    }else{
                        alert(arr.msg) ;
                    }
                }
            });

            return false ;

        }
    });
});
