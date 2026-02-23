//卖点处理的相关js
  function saveTmpSellPointStatus(a){
    if($(a).is(':checked')) {
      $(a).next().val("enabled");
    }else{
      $(a).next().val("disabled");
    }
  }
  //后台表单的公共提示方法
  function form_input_notice(field,type){
    if(type=='input'){
      $(field).on("mouseover mouseout",function(event){
          if(event.type == "mouseover"){
            //鼠标悬浮
            $(field).parent().next().children(".reg_default").show();
          }else if(event.type == "mouseout"){
            //鼠标离开
            $(field).parent().next().children(".reg_default").hide();
          }
      });
    }else if(type=='td'){
      $(field).on("mouseover mouseout",function(event){
        if(event.type == "mouseover"){
          //鼠标悬浮
          $(field).next().next().show();
        }else if(event.type == "mouseout"){
          //鼠标离开
          $(field).next().next().hide();
        }
      });
    }else if(type=="list_td"){
      $(field).on("mouseover mouseout",function(event){
        if(event.type == "mouseover"){
          //鼠标悬浮
          $(field).next().children(".reg_default_td").show();
        }else if(event.type == "mouseout"){
          //鼠标离开
          $(field).next().children(".reg_default_td").hide();
        }
      });
    }else if(type =='form_a'){
      $(field).on("mouseover mouseout",function(event){
        if(event.type == "mouseover"){
          //鼠标悬浮
          $(".form_a").children(".reg_default").show();
        }else if(event.type == "mouseout"){
          //鼠标离开
           $(".form_a").children(".reg_default").hide();
        }
      });
    }
  }