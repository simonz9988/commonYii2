$(function() {
	init_xml_uploader('file_path','shouce');
});



//表单验证内容
$("#systeamAddMenu").validate({
    rules: {
        file_name: {
            required: true
        },
        seo_title: {
            required: true
        },
        seo_keywords: {
            required: true
        },
        seo_description: {
            required: true
        },
        sort:{
            required: true,
            digits:true,
            range:[1,9999]
        }

    },
    messages: {
        file_name: {
            required: "必填"
        },
        seo_title: {
            required: "必填"
        },
        seo_keywords: {
            required: "必填"
        },
        seo_description: {
            required: "必填"
        },
        sort: {
            required: "必填",
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

        form.submit();
    }
});