/**
 * Created by dean.zhang on 2017/4/26.
 */
/**
 *后台首页设置 相关js代码
 *
 *
 */

$(function(){
	
    $('a.img_upload_btn').each(function(){
        var id=$(this).attr('id');
        init_upload(id, 'official_pc_index_img');
    });

    // 上传导航产品图片
    $('a.nav_product_img_upload_btn').each(function(){
        var id=$(this).attr('id');
        init_upload(id, 'official_nav_img');
    })

    //是否显示￥的控制
    $(document).on('click','input.show_sign_btn',function(){
        if($(this).is(':checked'))
        {
            $(this).siblings('input.goods_show_sign').val('y');
        }else{
            $(this).siblings('input.goods_show_sign').val('n');
        }
    })
    $('#sub_btn').click(function(){

        //各种表单验证

        $.ajax({
            url:'/official-pc-index2/save',
            type:'POST',
            dataType:'JSON',
            data:$('#index_form').serialize(),
            success:function(rs){
                if(rs['code']==1)
                {
                    location.href="/official-pc-index2/list";
                }else{
                    layer.msg(rs['msg']);
                }
            },
            error:function(){

            }
        })
    })
})

//上传文件类实例化
function init_upload(id, file_type)
{
    var uploader = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id, // you can pass in id...
        url : '/file/do-image-upload?file_type='+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        multi_selection:false,//是否允许选择多个文件
        max_file_count: 1,
        max_file_size : '2000kb',// 文件上传最大限制。
        chunk_size: '2000kb',
        unique_names: true,
        filters : {
            // 是否生成缩略图（仅对图片文件有效）
            resize: { width: 90, height: 90, quality: 90 },
            mime_types: [
                {title : "Image files", extensions : "jpg,jpeg,gif,png"},
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){
                var responseObj = responseObject.response
                var arr = eval('('+responseObj+')');
                var fileSrc = arr.data ;
                var htm='<img src="'+static_url+fileSrc+'" width="150" height="100">';
                $('#'+id).siblings('input.hidden_img_url').val(fileSrc);
                $('#'+id).siblings('div.img_demo').html(htm);

            },
            Error: function(up, err) {
                if(err['code']==-600)
                {
                    layer.msg("仅能上传2M以内的图片");
                }else{
                    layer.msg("上传图片失败");
                }
            }
        }
    });
    uploader.init();
    uploader.bind('FilesAdded',function(uploader,files){
        //现获取用户已经上传的图片数量
        uploader.start();
    });
}



//首页banner图相关
function add_slide()
{
    var htm='';
    var id=(new Date()).valueOf();
    id='img_upload_btn_'+id;
    htm +='<tr>';
    htm +='<td>';
    htm +='<input type="text" name="banner_title[]" class="col-sm-12" />';
    htm +='    </td>';
    htm +='    <td>';
    htm +='   <input type="text" name="banner_color[]" class="col-sm-12" />';
    htm +='   </td>';
    htm +='   <td>';
    htm +='   <input type="text" name="banner_desc[]" class="col-sm-12" />';
    htm +='   </td>';
    htm +='<td>';
    htm +='<select name="banner_desc_position[]" >';
    htm +='<option value="is-left">居左</option>';
    htm +='<option value="is-center">居中</option>';
    htm +='<option value="is-right">居右</option>';
    htm +='</select>';
    htm +='</td>';
    htm +='   <td>';
    htm +='    <input type="text" name="banner_desc_color[]" class="col-sm-12" />';
    htm +='  </td>';
    htm +='<td>';
    htm +='<input type="text" name="banner_more[]" value="" class="col-sm-12" />';
    htm +='</td>';
    htm +='<td>';
    htm +='<input type="text" name="banner_more_color[]" value="" class="col-sm-12" />';
    htm +='</td>';
    htm +='   <td>';
    htm +='   <input type="text" name="banner_url[]" class="col-sm-12" />';
    htm +='   </td>';
    htm +='   <td>';
    htm +='   <select name="banner_upload_type[]" class="banner_upload_type" data-no="'+id+'">';
    htm +='  <option value="image">图片</option>';
    htm +='   <option value="video">视频</option>';
    htm +='  </select>';
    htm +='    </td>';
    htm +='  <td>';
    htm +='   <div style="display: none" id="upload_video_type_'+id+'">';
    htm +='   <input type="text" name="banner_video[]" class="col-sm-12" />';
    htm +='  </div>';

    htm +='    <div id="upload_img_type_'+id+'">';
    htm +='   <input type="hidden" name="banner_img[]" class="hidden_img_url" />';
    htm +='  <div class="img_demo">';
    htm +='   </div>';
    htm +='   <a href="javascript:void(0)" class="img_upload_btn" id="'+id+'">';
    htm +='  <button type="button" class="btn btn-purple btn-sm" style="z-index: 1;">';
    htm +='  <i class="ace-icon fa fa-upload bigger-110"></i>上传';
    htm +='  </button>';
    htm +='  </a>';
    htm +='  </div>';

    htm +='  </td>';
    htm +=' <td>';
    htm +='  <select name="banner_nofollow[]" class="banner_nofollow">';
    htm +='  <option value="false">是</option>';
    htm +='  <option value="true">否</option>';
    htm +='  </select>';
    htm +='  </td>';
    htm +='  <td>';
    htm +='  <select name="banner_target[]" class="banner_target">';
    htm +=' <option value="_blank">新窗口</option>';
    htm +='  <option value="_self">当前窗口</option>';
    htm +='   </select>';
    htm +='  </td>';
    htm +=' <td>';
    htm +='  <input type="text" name="banner_sort[]" />';
    htm +=' <label>0~99 整数值</label>';
    htm +='</td>';
    htm +='<td>';
    htm +='<a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs">';
    htm +='   <i class="ace-icon fa fa-trash-o bigger-120"></i>删除';
    htm +='   </a>';
    htm +='    </td>';
    htm +='   </tr>';
    $('#banner_table').append(htm);
    init_upload(id, 'official_pc_index_img');
}

//首页中部banner图相关
function add_middle_slide()
{
    var htm='';
    var id=(new Date()).valueOf();
    id='middle_banner_img_upload_btn_'+id;
    htm +='<tr>';
    htm +='<td>';
    htm +='<input type="text" name="middle_banner_title[]" class="col-sm-12"  />';
    htm +='   </td>';
    htm +='    <td>';
    htm +='    <input type="text" name="middle_banner_title_color[]" class="col-sm-12"  />';
    htm +='   </td>';
    htm +='   <td>';
    htm +='   <input type="text" name="middle_banner_introduce[]" value="" class="col-sm-12" />';
    htm +='    </td>';
    htm +='    <td>';
    htm +='    <input type="text" name="middle_banner_introduce_color[]" value="" class="col-sm-12" />';
    htm +='   </td>';
    htm +='   <td>';
    htm +='   <input type="text" name="middle_banner_url[]" class="col-sm-12" />';
    htm +='   </td>';
    htm +='    <td>';
    htm +='    <input type="text" name="middle_banner_alt[]" value="" class="col-sm-12" />';
    htm +='    </td>';
    htm +='<td>';
    htm +='<select name="middle_banner_upload_type[]" class="middle_banner_upload_type" data-no="'+id+'">';
    htm +='<option value="image">图片</option>';
    htm +='<option value="video">视频</option>';
    htm +='</select>';
    htm +='</td>';
    htm +='<td>';
    htm +='<div style="display: none ;" id="middle_banner_upload_video_type_'+id+'">';
    htm +='<input type="text" name="middle_banner_video[]" value="" class="col-sm-12" />';
    htm +='</div>';
    htm +='<div  id="middle_banner_upload_img_type_'+id+'">';
    htm +='<input type="hidden" name="middle_banner_img[]" value="" class="hidden_img_url" />';
    htm +='<div class="img_demo">';

    htm +='</div>';
    htm +='<a href="javascript:void(0)" class="img_upload_btn" id="middle_banner_img_upload_btn_'+id+'">';
    htm +='<button type="button" class="btn btn-purple btn-sm" id="uploadBtn" style="z-index: 1;">';
    htm +='<i class="ace-icon fa fa-upload bigger-110"></i>上传';
    htm +='</button>';
    htm +='</a>';
    htm +='</div>';
    htm +='</td>';
    htm +='   <td>';
    htm +='  <select name="middle_banner_nofollow[]" class="middle_banner_nofollow">';
    htm +='   <option value="false">是</option>';
    htm +='    <option value="true">否</option>';
    htm +='  </select>';
    htm +='  </td>';
    htm +='  <td>';
    htm +='   <select name="middle_banner_target[]">';
    htm +='  <option value="_blank">新窗口</option>';
    htm +='  <option value="_self">当前窗口</option>';
    htm +='   </select>';
    htm +='   </td>';
    htm +='   <td>';
    htm +='   <input type="text" name="middle_banner_sort[]"/>';
    htm +='   <label>0~99 整数值</label>';
    htm +='</td>';
    htm +='<td>';
    htm +='<a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs">';
    htm +='<i class="ace-icon fa fa-trash-o bigger-120"></i>删除';
    htm +='</a>';
    htm +='</td>';
    htm +='</tr>';
    $('#middle_banner_table').append(htm);
    init_upload('middle_banner_img_upload_btn_'+id, 'official_pc_index_img');
}

//首页中下部banner图相关
function add_middle_bottom_slide()
{
    var htm='';
    htm +='<tr>';
    htm +='<td>';
    htm +='<input type="text" name="middle_bottom_banner_title[]" class="col-sm-12"  />';
    htm +='</td>';
    htm +='<td>';
    htm +='<input type="text" name="middle_bottom_banner_url[]" value="" class="col-sm-12" />';
    htm +='</td>';
    htm +='<td>';
    htm +='<input type="text" name="middle_bottom_banner_icon[]" value="" class="col-sm-12" />';
    htm +='</td>';
    htm +='<td>';
    htm +='<select name="middle_bottom_banner_nofollow[]" class="middle_banner_nofollow">';
    htm +='<option value="false">是</option>';
    htm +='<option value="true">否</option>';
    htm +='</select>';
    htm +='</td>';
    htm +='<td>';
    htm +='<select name="middle_bottom_banner_target[]">';
    htm +='<option value="_blank">新窗口</option>';
    htm +='<option value="_self">当前窗口</option>';
    htm +='</select>';
    htm +='</td>';
    htm +='<td>';
    htm +='<input type="text" name="middle_bottom_banner_sort[]"/>';
    htm +='<label>0~99 整数值</label>';
    htm +='</td>';
    htm +='<td>';
    htm +='<a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs">';
    htm +='<i class="ace-icon fa fa-trash-o bigger-120"></i>删除';
    htm +='</a>';
    htm +='</td>';
    htm +='</tr>';
    $('#middle_bottom_banner_table').append(htm);
    //init_upload(id, 'official_pc_index_img');
}

// 添加顶部导航
function addNav(){

    // 键值
    var key_val = $('.nav_key:last').val();
    key_val = parseInt(key_val) + 1;

    if(isNaN(key_val)){
        key_val = 0;
    }

    var base_id=(new Date()).valueOf();
    var id='nav_series_product_img_upload_btn_'+key_val+'_0';
    var id2='nav_no_series_product_img_upload_btn_'+key_val+'_0';

    var htm='';

    $.post(
        '/official-pc-index2/return-partial',
        {type:"nav_base",nav_id:key_val},
        function(data){

            var arr = eval("("+data+")") ;
            if(arr.code !=1){
                bootbox.alert(arr.msg);
            }else{

                htm += arr.data.tpl ;

                $('#nav_table').append(htm);
                init_upload(id, 'official_nav_img');
                init_upload(id2, 'official_nav_img');
            }

        }
    );



}

// 添加导航机型
function addNavProduct(key_val){
    var htm='';
    htm +='<tr>';
    htm +='<td><input type="text" name="nav_product_name['+key_val+'][]" class="col-sm-12" /></td>';
    htm +='<td><input type="text" name="nav_product_url['+key_val+'][]" class="col-sm-12" /></td>';
    htm +='<td><select name="nav_product_nofollow['+key_val+'][]" class="input-sm form-control"><option value="false">是</option><option value="true">否</option></select></td>';
    htm +='<td><select name="nav_product_target['+key_val+'][]" class="input-sm form-control"><option value="">当前窗口</option><option value="_blank">新窗口</option></select></td>';
    htm +='<td><input type="text" name="nav_product_sort['+key_val+'][]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除机型</a></td>';
    htm +='</tr>';
    $('#nav_product_table_'+key_val).append(htm);
}

// 添加导航机型
function addNavSeriesProduct(nav_id,series_id){

    var id=(new Date()).valueOf();
    id='nav_series_product_img_upload_btn_'+id;

    var htm='';
    htm +='<tr>';
    htm +='<td><div>';
    htm +='<input type="hidden" name="nav_series_product_img['+nav_id+']['+series_id+'][]" class="hidden_img_url"/>';
    htm +='<div class="img_demo">';
    htm +='</div>';
    htm +='<a href="javascript:void(0)" class="img_upload_btn" id="'+id+'">';
    htm +='<button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;">';
    htm +='<i class="ace-icon fa fa-upload bigger-110"></i>上传';
    htm +='</button>';
    htm +='</a>';
    htm +='</div></td>';
    htm +='<td><input type="text" name="nav_series_product_url['+nav_id+']['+series_id+'][]" class="col-sm-12" /></td>';
    htm +='<td><select name="nav_series_product_nofollow['+nav_id+']['+series_id+'][]" class="input-sm form-control"><option value="false">是</option><option value="true">否</option></select></td>';
    htm +='<td><select name="nav_series_product_target['+nav_id+']['+series_id+'][]" class="input-sm form-control"><option value="">当前窗口</option><option value="_blank">新窗口</option></select></td>';
    htm +='<td><input type="text" name="nav_series_product_sort['+nav_id+']['+series_id+'][]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" onclick="del_series_goods_td(this)" class="del_table_td btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';

    $('#nav_series_product_table_'+nav_id+'_'+series_id).append(htm);
    init_upload(id, 'official_nav_img');
}


function addNavSeriesProductBlock(nav_id,self){
    //var series_id = $('.nav_series_id:last').val();
    var series_id = (new Date()).valueOf();
    //series_id = parseInt(series_id) + 1;
    //console.log(series_id);
    var id=(new Date()).valueOf();
    id='nav_series_product_img_upload_btn_'+id;

    $.post(
        '/official-pc-index2/return-partial',
        {type:"nav_series_goods",nav_id:nav_id,nav_series_id:series_id,image_upload_id:id},
        function(data){

            var arr = eval("("+data+")") ;
            if(arr.code !=1){
                bootbox.alert(arr.msg);
            }else{
                var tpl = arr.data.tpl ;

                $(self).parent().parent().before(tpl) ;
                init_upload(id, 'official_nav_img');
            }

        }
    );
}

function addNavNoSeriesProductBlock(nav_id,self){

    //var series_id = $('.nav_series_id:last').val();
    var series_id = (new Date()).valueOf();;
    //series_id = parseInt(series_id) + 1;

    var id=(new Date()).valueOf();
    id='nav_no_series_product_img_upload_btn_'+id;

    $.post(
        '/official-pc-index2/return-partial',
        {type:"nav_no_series_goods",nav_id:nav_id,nav_series_id:series_id,image_upload_id:id},
        function(data){

            var arr = eval("("+data+")") ;
            if(arr.code !=1){
                bootbox.alert(arr.msg);
            }else{
                var tpl = arr.data.tpl ;

                $(self).parent().parent().before(tpl) ;
                init_upload(id, 'official_nav_img');
                //删除表格
                $(".del_table_td").click(function(){
                    $(this).parent().parent().remove();
                });
            }

        }
    );
}

function addNavServiceProductBlock(nav_id,self){
    var series_id = (new Date()).valueOf();
    //var series_id = $('.nav_service_id:last').val();
    //series_id = parseInt(series_id) + 1;

    var id=(new Date()).valueOf();
    id='nav_series_product_img_upload_btn_'+id;

    $.post(
        '/official-pc-index2/return-partial',
        {type:"nav_service",nav_id:nav_id,nav_series_id:series_id},
        function(data){

            var arr = eval("("+data+")") ;
            if(arr.code !=1){
                bootbox.alert(arr.msg);
            }else{
                var tpl = arr.data.tpl ;

                $(self).parent().parent().before(tpl) ;
                init_upload(id, 'official_nav_img');
            }

        }
    );
}

// 添加导航机型
function addNavNoSeriesProduct(nav_id,series_id){

    var id=(new Date()).valueOf();
    id='nav_no_series_product_img_upload_btn_'+id;

    var htm='';
    htm +='<tr>';
    htm +='<td><div>';
    htm +='<input type="hidden" name="nav_no_series_product_img['+nav_id+'][]" class="hidden_img_url"/>';
    htm +='<div class="img_demo">';
    htm +='</div>';
    htm +='<a href="javascript:void(0)" class="img_upload_btn" id="'+id+'">';
    htm +='<button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;">';
    htm +='<i class="ace-icon fa fa-upload bigger-110"></i>上传';
    htm +='</button>';
    htm +='</a>';
    htm +='</div></td>';
    htm +='<td><input type="text" name="nav_no_series_product_url['+nav_id+'][]" class="col-sm-12" /></td>';
    htm +='<td><select name="nav_no_series_product_nofollow['+nav_id+'][]" class="input-sm form-control"><option value="false">是</option><option value="true">否</option></select></td>';
    htm +='<td><select name="nav_no_series_product_target['+nav_id+'][]" class="input-sm form-control"><option value="">当前窗口</option><option value="_blank">新窗口</option></select></td>';
    htm +='<td><input type="text" name="nav_no_series_product_sort['+nav_id+'][]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" onclick="del_series_goods_td(this)" class="del_table_td btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#nav_no_series_product_table_'+nav_id+"_"+series_id).append(htm);
    init_upload(id, 'official_nav_img');
}

// 添加导航机型
function addNavService(nav_id,series_id){

    var htm='';
    htm +='<tr>';
    htm +='<tr>';
    htm +='<td>';
    htm +='<input type="text" name="nav_service_name['+nav_id+']['+series_id+'][]" class="col-sm-12" />';
    htm +='    </td>';
    htm +='    <td>';
    htm +='    <input type="text" name="nav_service_url['+nav_id+']['+series_id+'][]" class="col-sm-12" />';
    htm +='    </td>';
    htm +='    <td>';
    htm +='   <select name="nav_service_nofollow['+nav_id+']['+series_id+'][]" class="input-sm form-control">';
    htm +='   <option value="false">是</option>';
    htm +='   <option value="true">否</option>';
    htm +='   </select>';
    htm +='   </td>';
    htm +='   <td>';
    htm +='   <select name="nav_service_target['+nav_id+']['+series_id+'][]" class="input-sm form-control">';
    htm +='   <option value="">当前窗口</option>';
    htm +='   <option value="_blank">新窗口</option>';
    htm +='  </select>';
    htm +='   </td>';
    htm +='   <td>';
    htm +='   <input type="text" name="nav_service_sort['+nav_id+']['+series_id+'][]" />';
    htm +='    <label>0~99 整数值</label>';
    htm +='</td>';
    htm +='<td>';
    htm +='<a href="javascript:void(0)" class="del_table_btn btn btn-danger btn-xs">';
    htm +='   <i class="ace-icon fa fa-trash-o bigger-120"></i>删除';
    htm +='   </a>';
    htm +='   </td>';
    htm +='   </tr>';
    $('#nav_service_table_'+nav_id+"_"+series_id).append(htm);
    init_upload(id, 'official_nav_img');
}


// 添加导航配件
function addNavPeijian(key_val)
{
    var htm='';

    htm +='<tr>';
    htm +='<td><input type="text" name="nav_peijian_name['+key_val+'][]" class="col-sm-12" /></td>';
    htm +='<td><input type="text" name="nav_peijian_url['+key_val+'][]" class="col-sm-12"/></td>';
    htm +='<td><select name="nav_peijian_nofollow['+key_val+'][]" class="input-sm form-control"><option value="false">是</option><option value="true">否</option></select></td>';
    htm +='<td><select name="nav_peijian_target['+key_val+'][]" class="input-sm form-control"><option value="">当前窗口</option><option value="_blank">新窗口</option></select></td>';
    htm +='<td><input type="text" name="nav_peijian_sort['+key_val+'][]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除配件</a></td>';
    htm +='</tr>';
    $('#nav_peijian_table_'+key_val).append(htm);
}

//删除行
$(document).on('click','a.del_tr_btn',function(){
    $(this).parent().parent().remove();
})
//删除表格
$(document).on('click','a.del_table_btn',function(){

    var obj = $(this).closest('table');
    
    // 允许第二个参数作为修改提示语的功能
    var message = '确认删除？';
    if (arguments.length == 2) {
        message = arguments[1];
    }

    bootbox.confirm({
        size:'small',
        message: message,
        buttons: {
            cancel: {
                label: '<i class="fa fa-times"></i> 取消'
            },
            confirm: {
                label: '<i class="fa fa-check"></i> 确定'
            }
        },
        callback: function (result) {
            if(result){

                // 删除分割线
                obj.next('div').remove();

                obj.remove();
                return true ;
            }else{
                return true;
            }
        }
    });

    return true ;
    if(confirm("是否删除？"))
    {
        var obj = $(this).closest('table');
        // 删除分割线
        obj.next('div').remove();

        obj.remove();
    }

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
	
// 选择上传类型
$(document).on('click','.banner_upload_type',function(){
    var type = $(this).val();
    var id = $(this).attr('data-no');

    if(type == 'image'){
        $('#upload_video_type_'+id).hide();
        $('#upload_img_type_'+id).show();
    }else{
        $('#upload_video_type_'+id).show();
        $('#upload_img_type_'+id).hide();
    }
});

// 选择上传类型
$(document).on('click','.middle_banner_upload_type',function(){
    var type = $(this).val();
    var id = $(this).attr('data-no');

    if(type == 'image'){
        $('#middle_banner_upload_video_type_'+id).hide();
        $('#middle_banner_upload_img_type_'+id).show();
    }else{
        $('#middle_banner_upload_video_type_'+id).show();
        $('#middle_banner_upload_img_type_'+id).hide();
    }
});

function confirm_delete(self) {
    $(self).parent().parent().remove();
}