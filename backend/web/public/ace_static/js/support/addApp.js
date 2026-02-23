$(function(){


    init_uploader('example','support');
    init_uploader('ios','support');
    init_uploader('android','support');

    $("#addAppForm").validate({
      rules: {
          name: {
              required: true,
              maxlength:100
          }
      },
      messages: {
          name: {
              required: "Required",
              maxlength:'A maximum of 100 characters can be contained'
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

    
    form_input_notice("#example","input");
    form_input_notice("#ios","input");
    form_input_notice("#android","input");

     form_input_notice("input[name='name']",'input');

});