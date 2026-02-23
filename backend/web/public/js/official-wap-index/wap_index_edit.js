/**
 * Created by dean.zhang on 2017/09/26.
 */
/**
 *后台WAP首页设置 相关js代码
 *
 *
 */

$(function(){
	
    $('a.img_upload_btn').each(function(){
        var id=$(this).attr('id');
        init_upload(id);
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
        layer.load(1,{
            shade: [0.3,'#000'] //背景
        });
        $.ajax({
            url:'/official-wap-index/save',
            type:'POST',
            dataType:'JSON',
            data:$('#index_form').serialize(),
            success:function(rs){
                if(rs['code']==1)
                {
                    location.href="/official-wap-index/list";
                }else{
                    layer.closeAll();
                    layer.msg(rs.msg);
                }
            },
            error:function(){

            }
        })
    })
})

//上传文件类实例化
function init_upload(id)
{
    var uploader = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id, // you can pass in id...
        url : '/file/do-image-upload?file_type=official_wap_index_img',
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
    htm +='<td><input type="text" name="banner_url[]" class="width-500" /></td>';
    htm +='<td><select name="banner_upload_type[]" class="banner_upload_type" data-no='+id+'><option value="image">图片</option><option value="video">视频</option></select></td>';
    htm +='<td><div style="display: none" id="upload_video_type_'+id+'"><input type="text" name="banner_video[]" class="col-sm-12" /></div><div id="upload_img_type_'+id+'"><input type="hidden" name="banner_img[]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><select name="banner_target[]"><option value="_blank">新窗口</option><option value="_self">当前窗口</option></select></td>';
    htm +='<td><input type="text" name="banner_sort[]"/><label> 0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#banner_table').append(htm);
    init_upload(id);

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
    htm +='<td><div><input type="hidden" name="middle_banner_img[]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><select name="middle_banner_target[]"><option value="_blank">新窗口</option><option value="_self">当前窗口</option></select></td>';
    htm +='<td><input type="text" name="middle_banner_sort[]"/><label> 0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#middle_banner_table').append(htm);
    init_upload(id);
}

//删除行
$(document).on('click','a.del_tr_btn',function(){
    $(this).parent().parent().remove();
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