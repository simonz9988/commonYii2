
$(function() {
    

    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            card_no: {required: true},
            card_password:{required: true}
        },
        messages: {
            card_no: {required: "必填"},
            card_password: {required: "必填"}
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
                cache: true,
                type: "POST",
                url:'/adminPage/exchange/ajaxSaveCard',
                data:$('#systeamAddMenu').serialize(),// 你的formid
                async: false,
                error: function(request) {
                    alert("Connection error");
                },
                success: function(data) {
                    var arr =eval('('+data+')');
                    if(arr.code ==1){
                        location.href = '/adminPage/exchange/cardList' ;
                    }else{
                        alert(arr.msg) ;
                    }
                }
            });

            return false ;

        }
    });
});
