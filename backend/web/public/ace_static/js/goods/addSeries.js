$(function() {

	//表单验证内容
	$("#addSeriesForm").validate({
        rules: {
            name: {
                required: true,
                maxlength:50
            },
            slogan:{
                required: true,
                maxlength:70
            },
            sort:{
                required: true,
                digits:true,
                range:[1,9999]
            }

        },
        messages: {
            name: {
                required: "Required",
                maxlength:'A maximum of 50 characters can be contained'
            },
            slogan: {
                required: "Required",
                maxlength:'A maximum of 70 characters can be contained'
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
            var input_img_path = $("#input_img_path").val();
            if(!input_img_path){
                $("#img_path_reg_tip").addClass('red').html('Required');

                return false ;
            }
            form.submit();
        }
    });

    form_input_notice("input[name='name']",'input');
    form_input_notice("input[name='sort']",'input');
    form_input_notice("select[name='goods_cate_id']",'input');
    form_input_notice("#img_path",'input');
    form_input_notice("input[name='slogan']",'input');
});


function checkSelectedCate(a){
    var goods_cate_id = $(a).val();

    $.post(
        '/goods/ajaxCheckCateNoSon',
        {goods_cate_id:goods_cate_id},
        function(data){
            try{
                var arr = eval('('+data+')');
                var code = arr.code ;
                if(code!=1){
                    alert(arr.msg);
                    return false ;
                }
            }
            catch(err)
            {
                txt="There was an error on this page.\n\n";
                txt+="Error description: " + err.message + "\n\n";
                txt+="Click OK to continue.\n\n";
                alert(txt);
          }
        }
    );
}

