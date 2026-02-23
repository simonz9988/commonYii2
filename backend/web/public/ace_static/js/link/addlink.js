$(function() {
    init_uploader('icon','infolist');
	//友情链接编辑页面
    $("#addLinkForm").validate({
        
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
            var input_icon = $("#input_icon").val();
            if(!input_icon){
                $("#icon_reg_tip").addClass('red').html('Required');

                return false ;
            }
            form.submit();
        }
    });

    $("input[name='data[name]']").rules("add", { 
        required: true  ,
        messages: {
                    required:'Required',
                    } 
    });

    $("input[name='data[url]']").rules("add", { 
        required: true  ,
        messages: {
                    required:'Required',
                    } 
    });

    $("input[name='data[sort]']").rules("add", { 
        required: true  ,
        digits:true,
        range:[1,9999],
        messages: {
                    required:'Required',
                    digits:"Only the integer ranging from 1 to 9999 is allowed",
                    range:"Only the integer ranging from 1 to 9999 is allowed"
                    } 
    });

    //增加悬浮提示
    form_input_notice("input[name='data[name]']",'input');
    form_input_notice("#icon",'input');
    form_input_notice("input[name='data[url]']",'input');
    form_input_notice("input[name='data[sort]']",'input');
    form_input_notice("select[name='data[status]']",'input');


}); 



