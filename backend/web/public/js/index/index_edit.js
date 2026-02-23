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

        $.ajax({
            url:'/index/save',
            type:'POST',
            dataType:'JSON',
            data:$('#index_form').serialize(),
            success:function(rs){
                if(rs['code']==1)
                {
                    location.href="/index/list";
                }else{
                    layer.msg(rs['msg']);
                }
            },
            error:function(){

            }
        })
    })

    // 删除图片
    $(document).on('click', ".removePhoto", function(){
        var container = $(this).closest('.img');

        var parent = $(this).closest('.form-group');

        $(this).parent().parent().prev(".hidden_img_url").val("");

        // 样式设置
        container.remove();
    });
})

//上传文件类实例化
function init_upload(id)
{
    var uploader = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id, // you can pass in id...
        url : '/file/do-image-upload?file_type=index_img',
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
                var htm='<div class="img"><img src="'+static_url+fileSrc+'" width="150" height="100"><button class="btn btn-grey btn-xs inline help-button removePhoto" type="button"><i class="ace-icon fa fa-times fa-1x icon-only"></i></button></div>';
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
    htm +='<td><textarea class="form-control" rows="3" name="banner_title[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="banner_url[]"></textarea></td>';
    htm +='<td><div><input type="hidden" name="banner_img[]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><input type="text" name="banner_sort[]"/><label> 0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#banner_table').append(htm);
    init_upload(id);

}

// 添加左侧产品导航属性
function addLeftProductAttr(key_val){
    var htm='';
    var id=(new Date()).valueOf();
    htm +='<tr>';
    htm +='<td><textarea class="form-control" rows="3" name="left_product_attr_title['+key_val+'][]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="left_product_attr_url['+key_val+'][]"></textarea></td>';
    htm +='<td><input type="text" name="left_product_attr_sort['+key_val+'][]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#left_product_attr_table_'+key_val).append(htm);
    init_upload(id);
}

// 添加左侧产品导航型号
function addLeftProductHot(key_val){
    var htm='';
    var id=(new Date()).valueOf();
    id='left_product_hot_img_upload_btn_'+id;
    htm +='<tr>';
    htm +='<td><textarea class="form-control" rows="3" name="left_product_hot_title['+key_val+'][]"></textarea></td>';
    htm +='<td><div><input type="hidden" name="left_product_hot_img['+key_val+'][]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><textarea class="form-control" rows="3" name="left_product_hot_url['+key_val+'][]"></textarea></td>';
    htm +='<td><input type="text" name="left_product_hot_sort['+key_val+'][]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#left_product_hot_table_'+key_val).append(htm);
    init_upload(id);
}

// 添加左侧产品分类
function addLeftProduct(){

    // 键值
    var key_val = $('.left_product_key:last').val();
    key_val = parseInt(key_val) + 1;

    if(isNaN(key_val)){
        key_val = 0;
    }

    var id=(new Date()).valueOf();
    id='left_product_hot_img_upload_btn_'+id;

    var htm='';
    htm +='<table class="table table-bordered table-hover table-responsive" style="margin-bottom: 20px"><tbody><tr>';
    htm +='<td><input type="hidden" class="left_product_key" value="'+key_val+'"><div style="width:30%;float: left"><h4>产品分类名称</h4><input type="text" name="left_product_name['+key_val+']" class="col-sm-12" /></div><div style="width:30%"><h4>连接</h4><input type="text" name="left_product_url['+key_val+']" class="col-sm-12" /></div><div style="width:10%;"><h4>排序</h4><input type="text" name="left_product_sort['+key_val+']" class="col-sm-12" /></div>'
    htm +='<div style="float: right"><a href="javascript:void(0)" class="del_table_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除分类</a></div></td></tr>';
    htm +='<tr><td><label class="text-right">帮你选机</label><table class="table table-striped table-bordered table-hover table-responsive">';
    htm +='<thead><tr><th>标题 </th><th>链接地址</th><th>排序</th><th width="50px">操作</th></tr></thead>';
    htm +='<tbody id="left_product_attr_table_'+key_val+'">';
    htm +='<tr>';
    htm +='<td><textarea class="form-control" rows="3" name="left_product_attr_title['+key_val+'][]"></textarea></td><td><textarea class="form-control" rows="3" name="left_product_attr_url['+key_val+'][]"></textarea></td>';
    htm +='<td><input type="text" name="left_product_attr_sort['+key_val+'][]" /><label>0~99 整数值</label></td><td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    htm +='</tbody><tfoot>';
    htm +='<tr><td colspan=\'5\'><a href="javascript:void(0)" class="btn btn-primary btn-sm no-border btn-add" onclick="addLeftProductAttr('+key_val+');"><i class="ace-icon glyphicon glyphicon-plus"></i>添加</a></td></tr>';
    htm +='</tfoot></table></td></tr>';
    htm +='<tr><td>';
    htm +='<label class="text-right">热门型号</label><table class="table table-striped table-bordered table-hover table-responsive"><thead><tr><th>标题 </th><th>图片</th><th>链接地址</th><th>排序</th><th width="50px">操作</th></tr></thead>';
    htm +='<tbody id="left_product_hot_table_'+key_val+'"><tr>';
    htm +='<td><textarea class="form-control" rows="3" name="left_product_hot_title['+key_val+'][]"></textarea></td>';
    htm +='<td><div><input type="hidden" name="left_product_hot_img['+key_val+'][]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm +='<td><textarea class="form-control" rows="3" name="left_product_hot_url['+key_val+'][]"></textarea></td>';
    htm +='<td><input type="text" name="left_product_hot_sort['+key_val+'][]" /><label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr></tbody><tfoot><tr><td colspan="5"><a href="javascript:void(0)" class="btn btn-primary btn-sm no-border btn-add" onclick="addLeftProductHot('+key_val+');"><i class="ace-icon glyphicon glyphicon-plus"></i>添加</a>';
    htm +='</td></tr></tfoot></table></td></tr></tbody></table><div style="margin-bottom:20px;width:100%;height:4px;background-color:#2b72ae;"></div>';
    $('#left_product_table').append(htm);
    init_upload(id);
}

// 添加频道左侧导航
function addPindaoLeft(){
    var htm='';
    var id=(new Date()).valueOf();
    htm +='<tr>';
    htm +='<td><textarea class="form-control" rows="3" name="pindao_left_icon[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="pindao_left_url[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="pindao_left_title[]" ></textarea></td>';
    htm +='<td><input type="text" name="pindao_left_sort[]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#pindao_left_table').append(htm);
    init_upload(id);
}

//明星单品相关
function add_product_slide()
{
    var htm='';
    var id=(new Date()).valueOf();
    id='goods_upload_btn_'+id;
    htm +='<tr>';
    htm +='<td><input type="text" name="goods_title[]" class="col-sm-12" /></td>';
    htm +='<td><input type="text" name="goods_intro[]" class="col-sm-12"/></td>';
    htm +='<td>￥<input class="ace show_sign_btn" type="checkbox" name="show_sign[]" /> <span class="lbl"></span> <input type="hidden" name="goods_show_sign[]" value="n" class="goods_show_sign" /> + <input type="text" name="goods_price[]" class="col-sm-8"/></td>';
    htm +='<td><input type="text" name="goods_url[]" class="col-sm-12"/></td>';
    htm +='<td><div> <input type="hidden" name="goods_img[]" class="hidden_img_url"/> <div class="img_demo"></div> <a href="javascript:void(0)" class="img_upload_btn" id='+id+'> <button type="button" class="btn btn-purple btn-sm" style="z-index: 1;"> <i class="ace-icon fa fa-upload bigger-110"></i>上传 </button> </a> </div></td>';
    htm +='<td><input type="text" name="goods_sort[]" class="col-sm-6" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#goods_table').append(htm);
    init_upload(id);
}

//会员权益轮播相关
function vip_add_slide()
{
    var htm='';
    var id=(new Date()).valueOf();
    id='img_upload_btn_'+id;
    htm +='<tr>';
    htm +='<td><input type="text" name="vip_middel_bottom_title[]" class="col-sm-12" /></td>';
    htm +='<td><input type="text" name="vip_middel_bottom_url[]" class="col-sm-12" /></td>';
    htm +='<td><div><input type="hidden" name="vip_middel_bottom_img[]" class="hidden_img_url"/> <div class="img_demo"></div> <a href="javascript:void(0)" class="img_upload_btn" id='+id+'> <button type="button" class="btn btn-purple btn-sm" style="z-index: 1;"> <i class="ace-icon fa fa-upload bigger-110"></i>上传 </button> </a> </div></td>';
    htm +='<td><input type="text" name="vip_middel_bottom_sort[]" class="tiny"  pattern="int99" /><label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#vip_middel_bottom_table').append(htm);
    init_upload(id);
}

//活动专区商品设置
function huodong_add_slide(){
    var htm='';
    var id=(new Date()).valueOf();
    id='huodong_goods_'+id;
    htm += '<tr>';
    htm += '<td><div> <input type="hidden" name="huodong_goods_img[]"  class="hidden_img_url"/> <div class="img_demo"> </div> <a href="javascript:void(0)" class="img_upload_btn" id="'+id+'"> <button type="button" class="btn btn-purple btn-sm" style="z-index: 1;"> <i class="ace-icon fa fa-upload bigger-110"></i>上传 </button> </a> </div> </td>';
    htm += '<td> <textarea class="form-control" rows="3" name="huodong_goods_url[]"></textarea> </td>';
    htm += '<td> <textarea class="form-control" rows="3" name="huodong_goods_title[]"></textarea></td>';
    htm += ' <td><textarea class="form-control" rows="3" name="huodong_goods_sell_point[]"></textarea> </td>';
    htm += ' <td> <input type="text" name="huodong_goods_price[]"  class="col-sm-12" /> </td>';
    htm += ' <td> <input type="text" name="huodong_goods_sort[]"  /> <label>0~99 整数值</label> </td>';
    htm += '<td> <a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"> <i class="ace-icon fa fa-trash-o bigger-120"></i>删除 </a> </td>';
    htm += '</tr>';
    $('#huodong_goods_table').append(htm);
    init_upload(id);
}

//广告设置
function add_ad_slide(){
    var htm='';
    var id=(new Date()).valueOf();
    id='ad_upload_btn_'+id;
    htm += '<tr>';
    htm += ' <td> <div> <input type="hidden" name="ad_img[]"  class="hidden_img_url" /> <div class="img_demo"></div> <a href="javascript:void(0)" class="img_upload_btn" id="'+id+'"> <button type="button" class="btn btn-purple btn-sm" id="uploadBtn" style="z-index: 1;"> <i class="ace-icon fa fa-upload bigger-110"></i>上传 </button> </a> </div> </td>';
    htm += '<td><textarea class="form-control" rows="3" name="ad_url[]"></textarea> </td>';
    htm += '<td> <input type="text" name="ad_sort[]"/> <label>0~99 整数值</label> </td>';
    htm += '<td> <a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"> <i class="ace-icon fa fa-trash-o bigger-120"></i>删除 </a> </td>';
    htm += '</tr>';
    $('#ad_info_table').append(htm);
    init_upload(id);
}

// 导航设置
function add_nav_slide(){
    var htm='';
    var id=(new Date()).valueOf();
    var nav_id='nav_'+id;
    htm += '<tr>';
    htm += ' <td><textarea class="form-control" rows="3" name="nav_name[]"></textarea></td> ';
    htm += ' <td><textarea class="form-control" rows="3" name="nav_url[]"></textarea></td> ';
    htm += ' <td><input type="text" name="nav_word_color[]" class="col-sm-12" /></td> ';
    htm += ' <td><input type="hidden" name="nav_word_type_key[]" value="'+id+'" /><input type="checkbox" name="nav_word_type_'+id+'[0]" value="bold" />粗体<input type="checkbox" name="nav_word_type_'+id+'[1]" value="italic" /> 斜体</td> ';
    htm += ' <td><input type="text" name="nav_bg_color[]" class="col-sm-12" /></td> ';
    htm += ' <td><div><input type="hidden" name="nav_bg_img[]" class="hidden_img_url"/><div class="img_demo"></div><a href="javascript:void(0)" class="img_upload_btn" id='+nav_id+'><button type="button" class="btn btn-purple btn-sm"  style="z-index: 1;"><i class="ace-icon fa fa-upload bigger-110"></i>上传</button></a></div></td>';
    htm += ' <td> <input type="text" name="nav_sort[]"  /> <label>0~99 整数值</label> </td> ';
    htm += ' <td> <a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"> <i class="ace-icon fa fa-trash-o bigger-120"></i>删除 </a> </td>';
    htm += ' </tr>';
    $('#nav_info_table').append(htm);
    init_upload(nav_id);
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