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
            url:'/official-wap-index2/save',
            type:'POST',
            dataType:'JSON',
            data:$('#index_form').serialize(),
            success:function(rs){
                if(rs['code']==1)
                {
                    location.href="/official-wap-index2/list";
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
    htm +='   <td>';
    htm +='   <input type="text" name="banner_url[]" class="col-sm-12" />';
    htm +='   </td>';
    htm +='   <td>';
    htm +='   <select name="banner_upload_type[]" class="banner_upload_type" data-no="1">';
    htm +='  <option value="image">图片</option>';
    htm +='   <option value="video">视频</option>';
    htm +='  </select>';
    htm +='    </td>';
    htm +='  <td>';
    htm +='   <div style="display: none" id="upload_video_type_1">';
    htm +='   <input type="text" name="banner_video[]" class="col-sm-12" />';
    htm +='  </div>';

    htm +='    <div id="upload_img_type_1">';
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
    htm +='<input type="text" name="middle_bottom_banner_introduce[]" class="col-sm-12" />';
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
    init_upload(id, 'official_pc_index_img');
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