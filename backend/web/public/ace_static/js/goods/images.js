$(function() {

	$("#div_add_img").on("mouseover mouseout",function(event){
          if(event.type == "mouseover"){
            //鼠标悬浮
            $(".div_add_img_label").show();
          }else if(event.type == "mouseout"){
            //鼠标离开
            $(".div_add_img_label").hide();
          }
      });

    form_input_notice("input[name='name']",'input');
    form_input_notice("input[name='sort']",'input');
    form_input_notice("select[name='goods_params_id']",'input');
});

