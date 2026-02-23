$(function() {
	init_uploader('img_path','goods_tags');

	$("#addTagsForm").validate({
        rules: {
            name: {
                required: true,
                maxlength:20
            }
        },
        messages: {
            name: {
                required: "Required",
                maxlength:'A maximum of 20 characters can be contained'
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
            var input_img_path = $("#input_img_path").val();
            if(!input_img_path){
                $("#img_path_reg_tip").addClass('red').html('Required');

                return false ;
            }
            form.submit();
        }
    });

    form_input_notice("input[name='name']",'input');
    form_input_notice("#img_path",'input');
});

