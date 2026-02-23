/**
 * jquery 公共方法文件
 */

function delModel(link){
    // 允许第二个参数作为修改提示语的功能
    var message = 'Confirm delete this record？';
    if (arguments.length == 2) {
        message = arguments[1];
    }

    bootbox.confirm({
        size:'small',
        message: message,
        buttons: {
            cancel: {
                label: '<i class="fa fa-times"></i> CANCEL'
            },
            confirm: {
                label: '<i class="fa fa-check"></i> OK'
            }
        },
        callback: function (result) {
            if(result){
                $.ajax({
                    url:link,
                    type:'POST',
                    dataType:'json',
                    success:function(rs){
                        if(rs.code == '1'){
							//bootbox.alert(rs.msg);
                            //window.location.reload();
							layer.msg(rs.msg,{icon: 1},function(){ window.location.reload()});		
                        }else{
                            //bootbox.alert(rs.msg);
							layer.msg(rs.msg,{icon: 2});
                        }
                    },
                    error:function(){
                        bootbox.alert("通讯失败，请检查接口地址是否正确！");
                    }
                })
            }else{
                return true;
            }
        }
    });
}


// 两个浮点数求和  
function bcadd(num1,num2){  
   var r1,r2,m;  
   try{  
	   r1 = num1.toString().split('.')[1].length;  
   }catch(e){  
	   r1 = 0;  
   }  
   try{  
	   r2=num2.toString().split(".")[1].length;  
   }catch(e){  
	   r2=0;  
   }  
   m=Math.pow(10,Math.max(r1,r2));  
   // return (num1*m+num2*m)/m;  
   return Math.round(num1*m+num2*m)/m;  
}  
  
// 两个浮点数相减  
function bcsub(num1,num2){  
   var r1,r2,m;  
   try{  
	   r1 = num1.toString().split('.')[1].length;  
   }catch(e){  
	   r1 = 0;  
   }  
   try{  
	   r2=num2.toString().split(".")[1].length;  
   }catch(e){  
	   r2=0;  
   }  
   m=Math.pow(10,Math.max(r1,r2));  
   n=(r1>=r2)?r1:r2;  
   return (Math.round(num1*m-num2*m)/m).toFixed(n);  
}  

// 两数相除  
function bcdiv(num1,num2){  
   var t1,t2,r1,r2;  
   try{  
	   t1 = num1.toString().split('.')[1].length;  
   }catch(e){  
	   t1 = 0;  
   }  
   try{  
	   t2=num2.toString().split(".")[1].length;  
   }catch(e){  
	   t2=0;  
   }  
   r1=Number(num1.toString().replace(".",""));  
   r2=Number(num2.toString().replace(".",""));  
   return (r1/r2)*Math.pow(10,t2-t1);  
}  
  
function bcmul(num1,num2){  
   var m=0,s1=num1.toString(),s2=num2.toString();   
	try{m+=s1.split(".")[1].length}catch(e){};  
	try{m+=s2.split(".")[1].length}catch(e){};  
	return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m);  
}  

// 处理导航显示
function handleMenuActive(strUrl)
{
    $('ul.nav-list a[href=' + strUrl + ']').closest('li').addClass('active').parentsUntil('ul.nav-list').addClass('active open');
}

var common_selected_language = [];

// 展示所有语言的展示框
function showAllLanguage(self,type,id){
    var url = $(self).attr('data-auth') ;

    id = parseInt(id) ;

    if(id > 0 ){

        $.post(
            '/language/common-select',
            {type:type,id:id},
            function(data){
                var arr = eval('('+data+')');
                if(arr.code !=1){
                    alert(arr.msg) ;
                }else{
                    common_selected_language = arr.data.selected_language ;
                    var str = '<div id="select_language_div" style="float: left ;">';

                    str +='<span>Edited Language</span>';

                    for(var j = 0; j < common_selected_language.length; j++) {
                        var item = common_selected_language[j] ;
                        var selected_style = '';
                        if(j==0){
                            selected_style = 'checked="true"';
                        }
                        str +='<div>';
                        str += '<input onclick="select_done_language(this)" type="radio" '+selected_style+' name="language_id" is_edited="1" value="'+item.id+'">'+item.name;
                        str +='</div>';
                    }

                    str += '<div></div>';
                    str +='<span>UnEdit Language</span>';
                    for(var j = 0; j < arr.data.undone_language.length; j++) {

                        var item = arr.data.undone_language[j] ;

                        str +='<div>';
                        str += '<input type="radio"  name="language_id" is_edited="0"  onclick="select_undo_language(this,'+id+',\''+url+'\')" name="select_undo_language" value="'+item.id+'">'+item.name;
                        str +='</div>';
                    }


                    str +='</div>';

                    str+='<div id="select_copy_language_div" style="margin-left:20px;float: left;">';
                    str+='</div>' ;

                    // 异步加载所有的语言信息
                    layer.confirm(str, {
                        btn: ['Confirm'], //按钮
                        title: "Select Language"
                    }, function(){

                        var select_language_is_edited = parseInt($("input[name=language_id]:checked").attr("is_edited"));

                        var select_language_id = $("input[name=language_id]:checked").val();
                        select_language_id = parseInt(select_language_id);

                        if(select_language_is_edited == 1){
                            // 跳转
                            location.href = url+'?id='+id+'&language_id='+select_language_id ;
                        }else{

                            var copy_language_id = $("input[name=select_copy_language]:checked").val();
                            // 新增
                            location.href = url+'?id='+id+'&language_id='+select_language_id+'&copy_language_id='+copy_language_id ;
                        }

                    });
                    return false ;
                }
            }
        );

    }else{
        $.post(
            '/language/common-select',
            {type:type,id:id},
            function(data){
                var arr = eval('('+data+')');
                if(arr.code !=1){
                    alert(arr.msg) ;
                }else{

                    var str = '<div id="select_language_div" style="float: left ;">';
                    str +='<span>Select Edit Language</span>';
                    for(var j = 0; j < arr.data.undone_language.length; j++) {
                        var item = arr.data.undone_language[j] ;
                        var selected_style = '';
                        if(j==0){
                            selected_style = 'checked="true"';
                        }
                        str +='<div>';
                        str += '<input type="radio" '+selected_style+' onclick="select_undo_language(this,'+id+',\''+url+'\')" name="select_undo_language" value="'+item.id+'">'+item.name;
                        str +='</div>';
                    }


                    str +='</div>';

                    str+='<div id="select_copy_language_div" style="margin-left:20px;float: left;">';
                    str+='</div>' ;

                    //已编辑语言赋值
                    common_selected_language =  arr.data.selected_language ;

                    // 异步加载所有的语言信息
                    layer.confirm(str, {
                        btn: ['Confirm'], //按钮
                        title: "Select Language",
                        success:function(){
                            // 假如存在已编辑的语言， 需要默认点击第一个未编辑的语言
                            if(common_selected_language.length > 0 ){
                                $("input[name=select_undo_language]:eq(0)").click();
                            }
                        }
                    }, function(){

                        var selected_language_id = $("input[name=select_undo_language]:checked").val();

                        if(id == 0){

                            location.href = url+'?id=0&language_id='+selected_language_id ;
                        }else{

                            var select_copy_language_id = $("input[name=select_copy_language]:checked").val();
                            select_copy_language_id = parseInt(select_copy_language_id);
                            if(select_copy_language_id ==0){
                                alert('Please Select Copy Language !');
                            }else{
                                location.href = url+'?id='+id+'&language_id='+selected_language_id+'&copy_language_id='+select_copy_language_id ;
                            }
                        }

                    });
                    return false ;
                }
            }
        );
    }


}

function select_done_language(self){
    $("#select_copy_language_div").hide();
}
function select_undo_language(self,id,url){

    if(id !=0 ){
        // 右侧新增div
        // 渲染要复制的语言的信息
        var step2_str = '' ;

        step2_str +='<span>Select Copy Language</span>';

        step2_str +='<div>';
        step2_str += '<input type="radio" checked="true" name="select_copy_language" value="0">No choice';
        step2_str +='</div>';

        for(var j = 0; j < common_selected_language.length; j++) {
            var item = common_selected_language[j] ;

            step2_str +='<div>';
            step2_str += '<input type="radio"  name="select_copy_language" value="'+item.id+'">'+item.name;
            step2_str +='</div>';
        }

        $("#select_copy_language_div").empty().html(step2_str).show();

    }

}


function showExistsLanguage(self,type,id){
    var url = $(self).attr('data-auth');
    id = parseInt(id) ;
    $.post(
        '/language/common-select',
        {type:type,id:id},
        function(data){
            var arr = eval('('+data+')');
            if(arr.code !=1){
                alert(arr.msg) ;
            }else{
                common_selected_language = arr.data.selected_language ;
                var str = '<div id="select_language_div" style="float: left ;">';

                str +='<span>Edited Language</span>';

                for(var j = 0; j < common_selected_language.length; j++) {
                    var item = common_selected_language[j] ;
                    var selected_style = '';
                    if(j==0){
                        selected_style = 'checked="true"';
                    }
                    str +='<div>';
                    str += item.name;
                    str +='</div>';
                }

                str += '<div></div>';
                str +='<span>UnEdit Language</span>';
                for(var j = 0; j < arr.data.undone_language.length; j++) {

                    var item = arr.data.undone_language[j] ;

                    str +='<div>';
                    str += item.name;
                    str +='</div>';
                }


                str +='</div>';

                str+='<div id="select_copy_language_div" style="margin-left:20px;float: left;">';
                str+='</div>' ;

                // 异步加载所有的语言信息
                layer.confirm(str, {
                    btn: ['Confirm'], //按钮
                    title: "Select Language"
                }, function(){

                    layer.closeAll();

                });
                return false ;
            }
        }
    );
}
