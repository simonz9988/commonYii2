$(function() {
	

	$("#addGoodsForm").validate({
        rules: {
            new_id:{
                required: true
            },
            name: {
                required: true,
                maxlength:50
            },
            selling_point: {
                required: true,
                maxlength:100
            },
            video_guide: {
                required: false,
                maxlength:50
            },
            video_url: {
                required: false
            },
            page_title: {
                required: false
            },
            page_keyword: {
                required: false
            },
            page_description: {
                required: false
            },
            sort: {
                required: true,
                digits:true,
                range:[1,9999]
            }
        },
        messages: {
            new_id: {
                required: "Required"
            },
            name: {
                required: "Required",
                maxlength:'A maximum of 50 characters can be contained'
            },
            selling_point: {
                required: "Required",
                maxlength:'A maximum of 100 characters can be contained'
            },
            video_guide: {
                required: "Required",
                maxlength:'A maximum of 50 characters can be contained'
            },
            video_url: {
                required: "Required"
            },
            page_title: {
                required: "Required"
            },
            page_keyword: {
                required: "Required"
            },
            page_description: {
                required: "Required"
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

            var input_header_img = $("#input_header_img").val();
            if(!input_header_img){
                $("#header_img_reg_tip").addClass('red').html('Required');

                return false ;
            }
            form.submit();
        }
    });

    form_input_notice("input[name='name']",'input');
    form_input_notice("input[name='sort']",'input');
    form_input_notice("#modal_model_a",'input');
    form_input_notice("input[name='selling_point']",'input');
    form_input_notice("select[name='goods_tags_id']",'input');
    form_input_notice("input[name='video_guide']",'input');
    form_input_notice("input[name='video_url']",'input');
});

