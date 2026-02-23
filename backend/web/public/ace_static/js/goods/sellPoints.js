$(function() {
	init_uploader('img_path','goods_model');
    
    add_sell_point_form();

});

function add_sell_point_form(){
    $("#doAddSellPointsForm").validate({
       errorPlacement: function (error, element) {
            var errplace = element.next();
            errplace.html(error.text());
            errplace.addClass('red');
            
        },
        success: function (label, element) {
            var obj = $(element).next();
            obj.html('');
            obj.removeClass('red');
        },
        submitHandler: function (form) {
            form.submit();
            
        }
    });

    //由于页面的name值特殊定义，validate无法进行元素选择 故需要进行手动添加规则
    //$("input[name='sort[]']").each(function(){

    $(".add_input").each(function(){
        $(this).rules("add", { 
            required: true  ,
            maxlength:100,
            messages: {
                        required:'Required',
                        maxlength:'A maximum of 100 characters can be contained'
                        } 
        });

        form_input_notice($(this),'input');
    });

    $(".add_input_content").each(function(){
        $(this).rules("add", { 
            required: false  ,
            maxlength:800,
            messages: {
                        required:'Required',
                        maxlength:'A maximum of 800 characters can be contained'
                        } 
        });

        form_input_notice($(this),'input');
    });

    $(".add_input_sort").each(function(){
        $(this).rules("add", { 
            required: true  ,
            digits:true,
            range:[1,9999],
            messages: {
                        required:'Required',
                        digits:"Only the integer ranging from 1 to 9999 is allowed",
                        range:"Only the integer ranging from 1 to 9999 is allowed"
                        } 
        });

        /*
        $(this).on("mouseover mouseout",function(event){
            if(event.type == "mouseover"){
              //鼠标悬浮
              $(this).next().next().show();
            }else if(event.type == "mouseout"){
              //鼠标离开
              $(this).next().next().hide();
            }
          });
        */
    });


    $(".cboxElement").each(function(){
        $(this).on("mouseover mouseout",function(event){
            if(event.type == "mouseover"){
              //鼠标悬浮
              $(this).parent().parent().parent().parent().next().children(".reg_default").show();
            }else if(event.type == "mouseout"){
              //鼠标离开
              $(this).parent().parent().parent().parent().next().children(".reg_default").hide();
            }
          });
    });
}

function changeTpl(a){
    var sell_point_id = $(a).attr("sell_point_id") ;
    var tpl_id = $("#input_"+sell_point_id).val();

    $.post(
        '/goods/ajaxGetSellPointTpl',
        {tpl_id:tpl_id},function(data){
            $("#modal_attr").empty().html(data);
             $('#submitTplBtn').attr('sell_point_id',sell_point_id);
            $('#submitTplBtn').attr('tpl_id',tpl_id);
            $(a).next().click();
        }
    );
   
    

    
}



