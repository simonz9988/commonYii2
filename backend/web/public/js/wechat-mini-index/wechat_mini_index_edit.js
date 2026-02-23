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
	
	$('a.video_upload_btn').each(function(){
        var id=$(this).attr('id');
        init_upload_video(id);
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
            url:'/wechat-mini-index/save',
            type:'POST',
            dataType:'JSON',
            data:$('#index_form').serialize(),
            success:function(rs){
                if(rs['code']==1)
                {
                    location.href="/wechat-mini-index/list";
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
        url : '/file/do-image-upload?file_type=wechat_mini_img',
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

//上传视频文件类实例化
function init_upload_video(id)
{
    var uploader = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id, // you can pass in id...
        url : '/file/do-video-upload?file_type=wechat_mini_video',
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        multi_selection:false,//是否允许选择多个文件
        max_file_count: 1,
        max_file_size : '10mb',// 文件上传最大限制。
        chunk_size: '10mb',
        unique_names: true,
        filters : {
            // 是否生成缩略图（仅对图片文件有效）
            resize: { width: 90, height: 90, quality: 90 },
            mime_types: [
                {title : "Video files", extensions : "mp4,mpeg,avi,3gp,rm,rmvb,mov,wmv,flv,asf"},
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){
                var responseObj = responseObject.response
                var arr = eval('('+responseObj+')');
                var fileSrc = arr.data ;
                var htm='<img src="'+static_url+fileSrc+'.jpg" width="150" height="100">';
                $('#'+id).siblings('input.hidden_img_url').val(fileSrc);
                $('#'+id).siblings('div.img_demo').html(htm);

            },
            Error: function(up, err) {
                if(err['code']==-600)
                {
                    layer.msg("仅能上传10M以内的图片");
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
function addSlide()
{
    var htm='';
    var id=(new Date()).valueOf();
    id='img_upload_btn_'+id;
    htm +='<tr>';
    htm +='<td><input type="text" name="banner_title[]" class="col-sm-12"/></td>';
    htm +='<td><input type="text" name="banner_url[]" class="col-sm-12" /></td>';
    htm +='<td><div><input type="hidden" name="banner_img[]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><input type="text" name="banner_sort[]"/><label> 0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#banner_table').append(htm);
    init_upload(id);

}

// 底部设置
function addBottom()
{
    var htm='';
    var id=(new Date()).valueOf();
    id='img_upload_btn_'+id;
    htm +='<tr>';
    htm +='<td><input type="text" name="bottom_title[]" class="col-sm-12"/></td>';
    htm +='<td><input type="text" name="bottom_url[]" class="col-sm-12" /></td>';
    htm +='<td><div><input type="hidden" name="bottom_img[]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><input type="text" name="bottom_sort[]"/><label> 0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#bottom_table').append(htm);
    init_upload(id);

}

//删除行
$(document).on('click','a.del_tr_btn',function(){
    $(this).parent().parent().remove();
})

// 清空行
$(document).on("click", "a.clear_tr_btn", function(){
	$("img", $(this).parent().parent()).remove();
	$("input", $(this).parent().parent()).val("");
});