$(function(){

    $("#addMenuForm").validate({
      rules: {
        controller:{required:true},
        function:{required:true},
        name:{required:true}
      },
      messages: {

        controller:{required:"必填"},
        function:{required:"必填"},
        name:{required:"必填"}
      },
      errorPlacement: function(error, element) {
        var errplace = element.siblings('.reg_tip');
        errplace.html(error.text());
        errplace.addClass('reg_error');

      },
      success:function(label,element){
        var obj = $(element).siblings('.reg_tip');
        obj.html('');
        obj.removeClass('reg_error');

      },
      submitHandler:function(form){
        
        form.submit();
      }

    });
});