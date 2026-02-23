$(function(){

    $("#addInstructionsForm").validate({
      rules: {
          video_url: {
              required: false
          },
          page_title:{
              required: false
          },
          page_keyword:{
              required: false
          },
          page_description:{
              required: false
          },
          sort:{
              required: true,
              digits:true,
              range:[1,9999]
          }

      },
      messages: {
          video_url: {
              required: "Required"
          },
          page_title: {
              required: "Required"
          },
          page_keyword: {
              required: "Required"
          },
          page_description: {
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
        
        //判断是否选择型号
        var  model_val = $("#img_goods_model_id").prev().val();
        if(model_val){

          //判断是否已经上传文件
          var i = 0 ;
          $("input[name='download[]']").each(function(){
            i++;
          });


          if(i>0){
             form.submit();
          }else{
            $("#upload_tip").html('Required');
            $("#upload_tip").addClass("reg_error");
          }
        }else{
          $("#img_reg_tip").html('Required');
          $("#img_reg_tip").addClass("reg_error");
        }

       
      }

    });

    
    form_input_notice("#rar_upload","form_a");
    form_input_notice("input[name='sort']",'input');

});