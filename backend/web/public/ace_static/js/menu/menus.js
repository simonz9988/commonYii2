$(function(){
    $("#addMenusForm").validate({

      errorPlacement: function(error, element) {
        var errplace = element.parent().next().children('.reg_tip');
        errplace.html(error.text());
        errplace.addClass('reg_error');

      },
      success:function(label,element){
        var obj = $(element).parent().next().children('.reg_tip');
        obj.html('');
        obj.removeClass('reg_error');

      },
      submitHandler:function(form){
        
        form.submit();
      }

    });

    $(".input_sort").each(function(){
         $(this).rules("add", { 
            required: true  ,
            messages: {
                        required:'Required',
                        } 
        }); 
    });
});