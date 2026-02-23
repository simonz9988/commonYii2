/**
 * Created by dean.zhang on 2017/4/26.
 */
/**
 *后台首页设置 相关js代码
 *
 *
 */

$(function(){

    $('#sub_btn').click(function(){

        //各种表单验证

        $.ajax({
            url:'/official-index-nav/save',
            type:'POST',
            dataType:'JSON',
            data:$('#index_form').serialize(),
            success:function(rs){
                if(rs['code']==1)
                {
                    location.href="/official-index-nav/list";
                }else{
                    layer.msg(rs['msg']);
                }
            },
            error:function(){

            }
        })
    })
}) ;

function addNavBlock(){
    // 键值
    var key_val = $('.nav_key:last').val();
    key_val = parseInt(key_val) + 1;

    if(isNaN(key_val)){
        key_val = 0;
    }

    $.post(
        '/official-index-nav/return-partial',
        {type:"nav_base",nav_id:key_val},
        function(data){

            var arr = eval("("+data+")") ;
            if(arr.code !=1){
                bootbox.alert(arr.msg);
            }else{
                var tpl = arr.data.tpl ;

                $('#nav_table').append(tpl);

            }

        }
    );
}

// 添加导航
function addNavItem(nav_id,series_id){

    var htm='';
    htm +='<tr>';
    htm +='<td>';
    htm +='<input type="text" name="nav_name['+nav_id+'][]" class="col-sm-12" />';
    htm +='   </td>';
    htm +='   <td>';
    htm +='    <input type="text" name="nav_url['+nav_id+'][]" class="col-sm-12" />';
    htm +='    </td>';
    htm +='    <td>';
    htm +='    <select name="nav_nofollow['+nav_id+'][]" class="input-sm form-control">';
    htm +='    <option value="false">是</option>';
    htm +='    <option value="true">否</option>';
    htm +='    </select>';
    htm +='    </td>';
    htm +='    <td>';
    htm +='    <select name="nav_target['+nav_id+'][]" class="input-sm form-control">';
    htm +='    <option value="">当前窗口</option>';
    htm +='   <option value="_blank">新窗口</option>';
    htm +='    </select>';
    htm +='    </td>';
    htm +='   <td>';
    htm +='   <input type="text" name="nav_sort['+nav_id+'][]" />';
    htm +='   <label>0~99 整数值</label>';
    htm +='</td>';
    htm +='<td>';
    htm +='<a href="javascript:void(0)" class="del_table_td btn btn-danger btn-xs">';
    htm +='   <i class="ace-icon fa fa-trash-o bigger-120"></i>删除';
    htm +='   </a>';
    htm +='    </td>';
    htm +='    </tr>';
    $('#nav_no_series_product_table_'+nav_id+"_"+series_id).append(htm);

    //删除表格
    $(".del_table_td").click(function(){
        $(this).parent().parent().remove();
    });

}


//删除行
$(document).on('click','a.del_tr_btn',function(){
    $(this).parent().parent().remove();
})
//删除表格
$(document).on('click','a.del_table_btn',function(){

    var obj = $(this).closest('table');
    // 删除分割线
    obj.next('div').remove();

    obj.remove();

});

//删除表格
$(".del_table_td").click(function(){
    $(this).parent().parent().remove();
});

function del_series_goods_td(self){
    $(self).parent().parent().remove();
}

// 显示导航类型
function change_nav_type(self,nav_id){
    var type = $(self).val() ;
    $(".nav_type_tr_"+nav_id).hide();
    $(".nav_type_"+type+"_tr_"+nav_id).show();
}
	
