$(function(){
    $("#addMenusForm").validate({

      errorPlacement: function(error, element) {
        var errplace = element.next().children('.reg_tip');
        errplace.html(error.text());
        errplace.addClass('reg_error');

      },
      success:function(label,element){
        var obj = $(element).next().children('.reg_tip');
        obj.html('');
        obj.removeClass('reg_error');

      },
      submitHandler:function(form){
        
        form.submit();
      }

    });

    $(".input_class").each(function(){
        $(this).rules("add", { 
            required: true  ,
            messages: {
                        required:'Required',
                      } 
        }); 

        form_input_notice($(this),'list_td');
    });

    

    $(".input_name").each(function(){
        $(this).rules("add", { 
            maxlength: 100  ,
            messages: {
                        maxlength:'A maximum of 100 characters can be contained'
                      } 
        }); 

        form_input_notice($(this),'list_td');
    });

    $(".upload_img").each(function(){
      form_input_notice($(this),'list_td');
    });
});