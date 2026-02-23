$(function() {

	
    form_input_notice("#tpl_name",'list_td');

});

//重新添加参数属性的验证规则
function add_params_attr_form(){
    //动态添加表单验证内容
    $("#doAttrDomForm").validate({
        
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

    $("#doAttrDomForm  input[name='name']").rules("add", { 
        required: true  ,
        maxlength:50,
        messages: {
                    required:'Required',
                    maxlength:'A maximum of 25 characters can be contained'
                    } 
    });
    $("#doAttrDomForm  input[name='explanation']").rules("add", { 
        required: false  ,
        maxlength:200,
        messages: {
                    required:'Required',
                    maxlength:'A maximum of 200 characters can be contained'
                    } 
    });
    $("#doAttrDomForm  input[name='sort']").rules("add", { 
        required: true  ,
        digits:true,
        range:[1,9999],
        
        messages: {
                    required:'Required',
                    digits:"Only the integer ranging from 1 to 9999 is allowed",
                    range:"Only the integer ranging from 1 to 9999 is allowed"
                    } 
    });

    

    $(".options_input_sort").each(function(){
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

        //同时添加悬浮提示
        var  notice_obj = $(this).next().next();
        $(this).on("mouseover mouseout",function(event){
            if(event.type == "mouseover"){
              //鼠标悬浮
              notice_obj.show();
            }else if(event.type == "mouseout"){
              //鼠标离开
              notice_obj.hide();
            }
        });
    });

    $(".options_input_name").each(function(){
        $(this).rules("add", { 
            required: true  ,
            maxlength:200,
            messages: {
                        required:'Required',
                        maxlength:'A maximum of 200 characters can be contained'
                        } 
        });
    });

    
}

