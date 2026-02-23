$(function() {
	init_uploader('img_url','ad');
    $("#url").click();
    //新增广告页面表带验证
	$("#addMenuForm").validate({
        rules: {
            name: {
                required: true,
                maxlength:100
            },
            ad_position_id:{
            	required: true
            },
            suggest_size:{
                required: true
            },
            sort:{
                digits:true,
                required: true,
                range:[1,9999]
            },
            start_time:{
                required: true
            },
            end_time:{
                required: true
            }
        },
        messages: {
            name: {
                required: "Required",
                maxlength:'A maximum of 100 characters can be contained'

            },
            ad_position_id: {
                required: "Required"
            },
            suggest_size:{
                required: "Required"
            },
            sort:{
                digits:"Only the integer ranging from 1 to 9999 is allowed",
                required: "Required",
                range:"Only the integer ranging from 1 to 9999 is allowed"
            },
            start_time: {
                required: "Required"
            },
            end_time:{
                required: "Required"
            }
        },
        errorPlacement: function (error, element) {
            var errplace = element.parent().next().children('.reg_tip');
            errplace.html(error.text());
            errplace.addClass('red');
            
        },
        success: function (label, element) {
            var obj = $(element).parent().next().children('.reg_tip');
            obj.html('');
            obj.removeClass('red');
        },
        submitHandler: function (form) {
            var ad_type  = $("#sel_type").val() ;
            if(ad_type ==1){
                var input_img_url = $("#input_img_url").val() ;
                if(!input_img_url){
                    $("#img_url_reg_tip").addClass('red');
                    $("#img_url_reg_tip").empty().html('Required!');  
                    return false ;  
                }
                
            }
            form.submit();
        }
    });


    var img_type = $("#sel_type").val();
    change_valid_rules(img_type);

    //增加悬浮效果
    form_input_notice("input[name='name']",'input');
    form_input_notice("select[name='ad_position_id']",'input');
    form_input_notice("select[name='sel_type']",'input');
    form_input_notice("input[name='sort']",'input');
    form_input_notice("input[name='intro']",'input');
    form_input_notice("#img_url",'input');
    form_input_notice("input[name='link']",'input');
    form_input_notice("input[name='start_time']",'input');
    form_input_notice("input[name='end_time']",'input');
}); 

/**
 * 变更validform的验证规则
 * @param  string   type 上传的广告类型
 */
function change_valid_rules(type){
    if(type ==1){
        $("input[name='link']").rules("add", { required: false  ,messages: {required:'Required'} });
    }else if(type==3){
        $("input[name='intro']").rules("add", { required: true  ,messages: {required:'Required'} });
        $("input[name='link']").rules("add", { required: false  ,messages: {required:'Required'} });
    }else if(type ==5){
        $("input[name='link']").rules("add", { required: false  });
        $("input[name='intro']").rules("add", { required: false  });
    }
}

function change_div_by_type(v){
    //切换需要编辑的广告类型 显示不通的DIV
        var type_str = 'img';
        if(v ==1){
            $(".img_url_div").show();
            $(".content_div").hide();
            $(".type_div").hide();
            $(".link_div").show();
            type_str = 'img';
        }else if(v==3){
            $(".type_div").show();
            $(".img_url_div").hide();
            $(".content_div").hide();
            $(".link_div").show();
            type_str = 'text';
        }else if(v ==5){
            $(".content_div").show();
            $(".img_url_div").hide();
            $(".type_div").hide();
            $(".link_div").hide();
            type_str = 'rich text';
        }
        change_valid_rules(v);

        $("input[name='type1']").val(type_str);

        var ad_position_id = $("select[name='ad_position_id']").val();
        var type = v ;
        $.post(
            '/cms/ajaxGetAdSuggest',
            {ad_position_id:ad_position_id,type:type},
            function(data){
                try{
                    var arr = eval('('+data+')');
                    var code = arr.code ;
                    if(code==1){
                        $("input[name='suggest_size1']").val(arr.msg);
                        $("input[name='suggest_size']").val(arr.msg);
                    }else{
                        alert(arr.msg)
                    }
                }
                catch(err)
                {
                    txt="There was an error on this page.\n\n";
                    txt+="Error description: " + err.message + "\n\n";
                    txt+="Click OK to continue.\n\n";
                    alert(txt);
              }
            }
        );
    
} 


//测试demo
function add_dom(a){
	$("#aaaa").append("<a id='asdasd'>asdasd</a>");
	init_uploader("asdasd");
}

function get_suggest(a){
    var ad_position_id = $(a).val();
    $.post(
        '/cms/ajaxGetAdSuggest',
        {ad_position_id:ad_position_id},
        function(data){
            try{
                var arr = eval('('+data+')');
                var code = arr.code ;
                if(code==1){
                    $("input[name='suggest_size1']").val(arr.msg);
                    $("input[name='suggest_size']").val(arr.msg);
                    var type = arr.type ;
                    $("input[name='type']").val(type) ;
                    change_div_by_type(type);
                    //变更验证规则 已经不同DOM的显示
                }else{
                    alert(arr.msg)
                }
            }
            catch(err)
            {
                txt="There was an error on this page.\n\n";
                txt+="Error description: " + err.message + "\n\n";
                txt+="Click OK to continue.\n\n";
                alert(txt);
          }
        }
    );
}