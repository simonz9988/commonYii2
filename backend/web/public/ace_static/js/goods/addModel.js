$(function() {
	init_uploader('img_path','goods_model');

	$("#addGoodsModelForm").validate({
        rules: {
            name: {
                required: true,
                maxlength:100
            },
            sort:{
                required: true,
                digits:true,
                range:[1,9999],
            }
        },
        messages: {
            name: {
                required: "Required",
                maxlength:'A maximum of 100 characters can be contained'
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
	form_input_notice("select[name='goods_series_id']",'input');
	form_input_notice("#img_path",'input');

});

function getSeriesByCate(a){
	var goods_cate_id = $(a).val();
	var goods_model_id = $(a).attr("goods_model_id");

    //step1:判断是否为最底层的
    $.post(
        '/goods/ajaxCheckCateNoSon',
        {goods_cate_id:goods_cate_id},
        function(data){
            try{
                    var arr = eval('('+data+')');
                    var code = arr.code ;
                    if(code==1){
                        $.post(
                            '/goods/ajaxGetModelSeries',
                            {goods_cate_id:goods_cate_id,goods_model_id:goods_model_id},
                            function(data){
                                $("#goods_series_id_sel").empty().html(data);
                            }
                        );
                    }else{
                        alert(arr.msg)
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

