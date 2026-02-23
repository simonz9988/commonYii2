$(function(){

    $("#addMenuForm").validate({
      rules: {
          question: {
              required: true
          },
          answer:{
              required: true
          },
         sort:{
              required: true,
              digits:true,
              range:[1,9999]
          }

      },
      messages: {
          question: {
              required: "Required"
          },
          answer: {
              required: "Required"
          },
          sort: {
              required: "Required",
              digits:"Only the integer ranging from 1 to 9999 is allowed",
              range:"Only the integer ranging from 1 to 9999 is allowed"
          }
      },
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

    
    form_input_notice("#goods_model_a","input");
    form_input_notice("textarea[name='answer']",'input');
    form_input_notice("input[name='question']",'input');
    form_input_notice("input[name='sort']",'input');

});