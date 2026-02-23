$(function() {
	//视频新增编辑页面
    $("#addUseremails").validate({
        rules: {
            usersuggest_subject: {
                required: true
            },
            usersuggest:{
                required: true
            },
            adminupdate_subject:{
                required: false
            },
            adminupdate:{
                required: false
            }

        },
        messages: {
            usersuggest_subject: {
                required: "Required"
            },
            usersuggest: {
                required: "Required"
            },
            adminupdate_subject:{
                required: "Required"
            },
            adminupdate:{
                required: "Required"
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
            form.submit();
        }
    });

   

}); 



