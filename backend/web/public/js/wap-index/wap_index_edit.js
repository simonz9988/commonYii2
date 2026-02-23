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
            url:'/wap-index/save',
            type:'POST',
            dataType:'JSON',
            data:$('#index_form').serialize(),
            success:function(rs){
                if(rs['code']==1)
                {
                    location.href="/wap-index/list";
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
        url : '/file/do-image-upload?file_type=wap_index_img',
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
function addSlide()
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

//导航栏添加
function addMenu(){
    var htm='';
    var id=(new Date()).valueOf();
    htm +='<tr>';
    htm +='<td><input type="text" name="icon[]" class="col-sm-12" /></td>';
    htm +='<td><textarea class="form-control" rows="3" name="menu_url[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="menu_title[]"></textarea></td>';
    htm +='<td><input type="text" name="menu_sort[]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#menu_table').append(htm);
    init_upload(id);
}

//E热点添加
function addHotNews(){
    var htm = '';
    htm +='<tr>';
    htm +='<td><textarea class="form-control" rows="3" name="hotnews_url[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="hotnews_title[]"></textarea></td>';
    htm +='<td><input type="text" name="hotnews_sort[]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#hotnews_table').append(htm);
}

//今日值得买
function addTodayBuy(){
    var htm='';
    var id=(new Date()).valueOf();
    id='today_buy'+id;
    htm +='<tr>';
    htm +='<td><div><input type="hidden" name="today_buy_img[]"  class="hidden_img_url" /> <div class="img_demo"> </div> <a href="javascript:void(0)" class="img_upload_btn" id="'+id+'"> <button type="button" class="btn btn-purple btn-sm" style="z-index: 1;"> <i class="ace-icon fa fa-upload bigger-110"></i>上传 </button> </a> </div> </td>';
    htm +='<td><textarea class="form-control" rows="3" name="today_buy_url[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="today_buy_title[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="today_buy_sell_point[]"></textarea></td>';
    htm +='<td>￥<input class="ace show_sign_btn" type="checkbox" name="show_sign[]" /> <span class="lbl"></span> <input type="hidden" name="today_buy_show_sign[]" value="n" class="goods_show_sign" /> +<input type="text" name="today_buy_price[]"/></td>';
    htm +='<td><input type="text" name="today_buy_sort[]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#today_buy_table').append(htm);
    init_upload(id)
}

//产品广告添加
function addGoodsAd(){
    var htm='';
    var id=(new Date()).valueOf();
    id='goodsad_img_upload_btn_'+id;
    htm +='<tr>';
    htm +='<td><div><input type="hidden" name="goods_ad_img[]"  class="hidden_img_url" /> <div class="img_demo"> </div> <a href="javascript:void(0)" class="img_upload_btn" id="'+id+'"> <button type="button" class="btn btn-purple btn-sm" style="z-index: 1;"> <i class="ace-icon fa fa-upload bigger-110"></i>上传 </button> </a> </div> </td>';
    htm +='<td><textarea class="form-control" rows="3" name="goods_ad_url[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="goods_ad_title[]"></textarea></td>';
    htm +='<td><input type="text" name="goods_ad_sort[]"><label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#goodsad_table').append(htm);
    init_upload(id);
}

//明星产品添加
function addStarGoods(){
    var htm='';
    var id=(new Date()).valueOf();
    id='star_goods'+id;
    htm +='<tr>';
    htm +='<td><div><input type="hidden" name="star_goods_img[]"  class="hidden_img_url" /> <div class="img_demo"> </div> <a href="javascript:void(0)" class="img_upload_btn" id="'+id+'"> <button type="button" class="btn btn-purple btn-sm" style="z-index: 1;"> <i class="ace-icon fa fa-upload bigger-110"></i>上传 </button> </a> </div> </td>';
    htm +='<td><textarea class="form-control" rows="3" name="star_goods_url[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="star_goods_title[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="star_goods_sell_point[]"></textarea></td>';
    htm +='<td>￥<input class="ace show_sign_btn" type="checkbox" name="show_sign[]" /> <span class="lbl"></span> <input type="hidden" name="star_goods_show_sign[]" value="n" class="goods_show_sign" /> +<input type="text" name="star_goods_price[]"/></td>';
    htm +='<td><input type="text" name="star_goods_sort[]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#goods_table').append(htm);
    init_upload(id);
}

//猜你喜欢添加
function addLikeGoods(){
    var htm='';
    var id=(new Date()).valueOf();
    id='like_goods'+id;
    htm +='<tr>';
    htm +='<td><div><input type="hidden" name="like_goods_img[]"  class="hidden_img_url" /> <div class="img_demo"> </div> <a href="javascript:void(0)" class="img_upload_btn" id="'+id+'"> <button type="button" class="btn btn-purple btn-sm" style="z-index: 1;"> <i class="ace-icon fa fa-upload bigger-110"></i>上传 </button> </a> </div> </td>';
    htm +='<td><textarea class="form-control" rows="3" name="like_goods_url[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="like_goods_title[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="like_goods_sell_point[]"></textarea></td>';
    htm +='<td>￥<input class="ace show_sign_btn" type="checkbox" name="show_sign[]" /> <span class="lbl"></span> <input type="hidden" name="like_goods_show_sign[]" value="n" class="goods_show_sign" /> +<input type="text" name="like_goods_price[]"/></td>';
    htm +='<td><input type="text" name="like_goods_sort[]" /> <label>0~99 整数值</label></td>';
    htm +='<td><a href="javascript:void(0)" class="del_tr_btn btn btn-danger btn-xs"><i class="ace-icon fa fa-trash-o bigger-120"></i>删除</a></td>';
    htm +='</tr>';
    $('#like_goods_table').append(htm);
    init_upload(id);
}

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
    htm +='<td><textarea class="form-control" rows="3" name="vip_middel_bottom_title[]"></textarea></td>';
    htm +='<td><textarea class="form-control" rows="3" name="vip_middel_bottom_url[]"></textarea></td>';
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
    htm += '<td> <textarea class="form-control" rows="3" name="huodong_goods_title[]"></textarea> </td>';
    htm += '<td> <textarea class="form-control" rows="3" name="huodong_goods_sell_point[]"></textarea> </td>';
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

//删除行
$(document).on('click','a.del_tr_btn',function(){
    $(this).parent().parent().remove();
})