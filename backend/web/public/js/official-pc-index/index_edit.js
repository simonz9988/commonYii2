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
    })

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
            url:'/official-pc-index/save',
            type:'POST',
            dataType:'JSON',
            data:$('#index_form').serialize(),
            success:function(rs){
                if(rs['code']==1)
                {
                    location.href="/official-pc-index/list";
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
    htm +='<td><input type="text" name="banner_title[]" class="col-sm-12"/></td>';
    htm +='<td><input type="text" name="banner_url[]" class="col-sm-12" /></td>';
    htm +='<td><select name="banner_upload_type[]" class="banner_upload_type" data-no='+id+'><option value="image">图片</option><option value="video">视频</option></select></td>';
    htm +='<td><div style="display: none" id="upload_video_type_'+id+'"><input type="text" name="banner_video[]" class="col-sm-12" /></div><div><input type="hidden" name="banner_img[]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><select name="banner_target[]"><option value="_blank">新窗口</option><option value="_self">当前窗口</option></select></td>';
    htm +='<td><input type="text" name="banner_sort[]"/><label> 0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
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
    htm +='<td><input type="text" name="middle_banner_title[]" class="col-sm-12"/></td>';
    htm +='<td><input type="text" name="middle_banner_introduce[]" class="col-sm-12"/></td>';
    htm +='<td><input type="text" name="middle_banner_url[]" class="col-sm-12" /></td>';
    htm +='<td><input type="text" name="middle_banner_alt[]" class="col-sm-12"/></td>';
    htm +='	<td><div><input type="hidden" name="middle_banner_img[]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id="'+id+'"><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><select name="middle_banner_target[]"><option value="_blank">新窗口</option><option value="_self">当前窗口</option></select></td>';
    htm +='<td><input type="text" name="middle_banner_sort[]"/><label> 0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#middle_banner_table').append(htm);
    init_upload(id, 'official_pc_index_img');
}

// 添加顶部导航
function addNav(){

    // 键值
    var key_val = $('.nav_key:last').val();
    key_val = parseInt(key_val) + 1;

    if(isNaN(key_val)){
        key_val = 0;
    }

    var id=(new Date()).valueOf();
    id='nav_product_img_upload_btn_'+id;

    var htm='';
    htm +='<table class="table table-bordered table-hover table-responsive" style="margin-bottom: 20px"><tbody><tr>';
    htm +='<td><input type="hidden" class="nav_key" value="'+key_val+'"><div class="form-group row"><div class="col-md-6 col-xs-7 "><label class="col-md-2 col-xs-1 text-left">导航名称</label><input name="nav_name['+key_val+']" class="input-sm form-control" type="text"></div></div><div class="form-group row"><div class="col-md-6  col-xs-7 "><label class="col-md-2 col-xs-1 text-left">频道地址</label><input name="nav_url['+key_val+']" class="input-sm form-control" type="text"></div></div>'
    htm +='<div class="form-group row"><div class="col-md-6  col-xs-7 "><label class="col-md-2 col-xs-1 text-left">是否追踪</label><select name="nav_nofollow['+key_val+']" class="input-sm form-control"><option value="false">是</option><option value="true">否</option></select></div></div>'
    htm +='<div class="form-group row"><div class="col-md-6  col-xs-7 "><label class="col-md-2 col-xs-1 text-left">打开方式</label><select name="nav_target['+key_val+']" class="input-sm form-control"><option value="">当前窗口</option><option value="_blank">新窗口</option></select></div></div>'
    htm +='<div class="form-group row"><div class="col-md-6  col-xs-7 "><label class="col-md-2 col-xs-1 text-left">图片链接地址</label><input name="nav_img_link['+key_val+']" class="input-sm form-control" type="text"></div></div>';
    htm +='<div class="form-group row"><div class="col-md-6 col-xs-7 "><label class="col-md-2 col-xs-1 text-left">图片</label><input type="hidden" name="nav_img['+key_val+']" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="nav_product_img_upload_btn" id="'+id+'"><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></div>';
    htm +='<div class="form-group row"><div class="col-md-6  col-xs-7 "><label class="col-md-2 col-xs-1 text-left">排序</label><input name="nav_sort['+key_val+']" class="input-sm form-control" type="text"></div></div>';
    htm +='<div style="float: right"><a href="javascript:void(0)" class="del_table_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></div></td></tr>';
    htm +='<tr><td><label class="text-right">机型 / 服务左侧</label><div style="margin:5px 0 20px 0;width:100%;height:1px;background-color:#ddd;"></div><div class="form-group row"><div class="col-md-6  col-xs-7 "><label class="col-md-1 col-xs-1 text-left">标题</label><input name="nav_product_title['+key_val+']" class="input-sm form-control" type="text"></div></div><table class="table table-striped table-bordered table-hover table-responsive">';
    htm +='<thead><tr><th>名称</th><th>链接地址</th><th>是否追踪</th><th>打开方式</th><th>排序</th><th width="50px">操作</th></tr></thead>';
    htm +='<tbody id="nav_product_table_'+key_val+'">';
    htm +='<tr>';
    htm +='<td><input type="text" name="nav_product_name['+key_val+'][]" class="col-sm-12" /></td><td><input type="text" name="nav_product_url['+key_val+'][]" class="col-sm-12" /></td>';
    htm +='<td><select name="nav_product_nofollow['+key_val+'][]" class="input-sm form-control"><option value="false">是</option><option value="true">否</option></select></td>';
    htm +='<td><select name="nav_product_target['+key_val+'][]" class="input-sm form-control"><option value="">当前窗口</option><option value="_blank">新窗口</option></select></td>';
    htm +='<td><input type="text" name="nav_product_sort['+key_val+'][]" /> <label>0~99 整数值</label></td><td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除机型</a></td>';
    htm +='</tr>';
    htm +='</tbody><tfoot>';
    htm +='<tr><td colspan="6"><a href="javascript:void(0)" class="btn btn-primary btn-sm no-border btn-add" onclick="addNavProduct('+key_val+');"><i class="ace-icon glyphicon glyphicon-plus"></i>添加机型</a></td></tr>';
    htm +='</tfoot></table></td></tr>';
    htm +='<tr><td>';

    htm +='<label class="text-right">配件 / 服务右侧</label><div style="margin:5px 0 20px 0;width:100%;height:1px;background-color:#ddd;"></div><div class="form-group row"><div class="col-md-6  col-xs-7 "><label class="col-md-1 col-xs-1 text-left">标题</label><input name="nav_peijian_title['+key_val+']" class="input-sm form-control" type="text"></div></div><table class="table table-striped table-bordered table-hover table-responsive"><thead><tr><th>标题 </th><th>链接地址</th><th>是否追踪</th><th>打开方式</th><th>排序</th><th width="50px">操作</th></tr></thead>';
    htm +='<tbody id="nav_peijian_table_'+key_val+'"><tr>';
    htm +='<td><input type="text" name="nav_peijian_name['+key_val+'][]" class="col-sm-12" /></td>';
    htm +='<td><input type="text" name="nav_peijian_url['+key_val+'][]" class="col-sm-12" /></td>';
    htm +='<td><select name="nav_peijian_nofollow['+key_val+'][]" class="input-sm form-control"><option value="false">是</option><option value="true">否</option></select></td>';
    htm +='<td><select name="nav_peijian_target['+key_val+'][]" class="input-sm form-control"><option value="">当前窗口</option><option value="_blank">新窗口</option></select></td>';
    htm +='<td><input type="text" name="nav_peijian_sort['+key_val+'][]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除配件</a></td>';
    htm +='</tr></tbody><tfoot><tr><td colspan="6"><a href="javascript:void(0)" class="btn btn-primary btn-sm no-border btn-add" onclick="addNavPeijian('+key_val+');"><i class="ace-icon glyphicon glyphicon-plus"></i>添加配件</a>';
    htm +='</td></tr></tfoot></table></td></tr></tbody></table><div style="margin-bottom:20px;width:100%;height:4px;background-color:#2b72ae;"></div>';
    $('#nav_table').append(htm);
    init_upload(id, 'official_nav_img');
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
    // 删除分割线
    obj.next('div').remove();

    obj.remove();

})
	
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