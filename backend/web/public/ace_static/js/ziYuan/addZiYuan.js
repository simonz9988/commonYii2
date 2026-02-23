$(function() {

    init_attachments_uploader('url', 'attachments');

    $("#addZiYuanForm").validate({
        rules: {
            url: {
                required: true
            },
            file_name:{
                required: true
            },
            alt:{
                required: true
            },
            sort:{
                digits:true,
                required: true,
                range:[1,9999]
            }
        },
        messages: {
            url: {
                required: "必填"
            },
            file_name: {
                required: "必填"
            },
            alt:{
                required: "必填"
            },
            sort:{
                digits:"1~9999",
                required: "必填",
                range:"1~9999"
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
            var ad_type  = $("#sel_type").val() ;
            if(ad_type ==1){
                var input_img_url = $("#input_img_url").val() ;
                if(!input_img_url){
                    $("#img_url_reg_tip").addClass('red');
                    $("#img_url_reg_tip").empty().html('Required!');
                    return false ;
                }

            }
            form.submit();
        }
    });

});