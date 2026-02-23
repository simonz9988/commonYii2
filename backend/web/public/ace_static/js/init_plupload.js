// dom_name 代表id值 file_type 文件类型 也代表文件目录
function init_uploader(dom_name,file_type){
    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');
                console.log(file_obj);
                if(typeof file_obj =='object'){
                    if(file_obj.code == 1){
                        $("#input_"+id_str).val(file_obj.data) ;
                        $("#img_"+id_str).attr("src",file_obj.data);
                        $("#img_"+id_str).removeClass("hide");
                        alert("上传成功")
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('Unknow Error!')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                if(file_type =='instructions_upload'){
                    alert('You can upload only a PDF file whose size cannot exceed 50M');
                }else{
                    alert('Unknow Error!')
                }

            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}

//新增编辑商品图片
function init_goods_image_uploader(dom_name,file_type){
    var id_str = dom_name ;
    var goods_id = $("#"+dom_name).attr('goods_id');
    var is_edit = parseInt($("#"+dom_name).attr('is_edit'));
    var image_id = $("#"+dom_name).attr('image_id');

    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type+"&is_add_goods_image=1&goods_id="+goods_id+"&is_edit="+is_edit+"&image_id="+image_id,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        //alert("上传成功!");
                        if(is_edit==1 || goods_id){
                            location.reload();
                        }else{
                            goods_id= file_obj.goods_id ;
                            var lang_category = file_obj.lang_category ;

                            location.href="/"+lang_category+"/goods/images?goods_id="+goods_id;
                        }
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('上传失败')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                alert('上传失败')
            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}


//上传技术支持菜单
function init_help_uploader(real_id_str,dom_name,file_type){
    var dom_id = real_id_str;
    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        var op_dom_id = $("#"+id_str).attr("dom_id");
                        $("#input_"+op_dom_id).val(file_obj.detail) ;
                        $("#img_"+op_dom_id).attr("src",file_obj.detail);
                        $("#img_"+op_dom_id).removeClass("hide");
                        //alert("上传成功");
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('上传失败')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                alert('上传失败')
            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}

function init_rar_uploader(dom_name,file_type){
    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        var file_name = file_obj.file_name ;
                        var user_upload_file_name = file_obj.user_upload_file_name ;
                        var insert_dom1 = '<li>'+user_upload_file_name+'</li>';
                        insert_dom1 += '<input type="hidden" name="download[]" value="'+file_obj.detail+'">';


                        var insert_dom ='<tr class="download_tr">';
                        insert_dom += '<td> '+user_upload_file_name +' </td>';
                        insert_dom += '<td> <a href="javascript:void(0)" onclick="del_download(this)">&nbsp &nbspX</a>&nbsp &nbsp<a href="javascript:void(0)" onclick="forward_move(this)"><i class="fa fa-long-arrow-up bigger-120"></i></a>&nbsp &nbsp<a href="javascript:void(0)" onclick="back_move(this)"><i class="fa fa-long-arrow-down  bigger-120"></i></a> ';
                        insert_dom += '<input type="hidden" name="file_name[]" value="'+file_obj.user_upload_file_name+'">';
                        insert_dom += '<input type="hidden" name="download[]" value="'+file_obj.detail+'"></td>';
                        insert_dom += '</tr>';

                        $(".rar_upload_ul").append(insert_dom);
                        //alert("上传成功");
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('上传失败')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                if(file_type =='instructions_upload'){
                    alert('You can upload only a PDF file whose size cannot exceed 50M');
                }else{
                    alert('Unknow Error!')
                }
            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}


function init_pdf_uploader(dom_name,file_type){
    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        var file_name = file_obj.file_name ;
                        var user_upload_file_name = file_obj.user_upload_file_name ;
                        var insert_dom1 = '<li>'+user_upload_file_name+'</li>';
                        insert_dom1 += '<input type="hidden" name="download[]" value="'+file_obj.detail+'">';


                        var insert_dom ='<tr class="download_tr">';
                        insert_dom += '<td> '+user_upload_file_name +' </td>';
                        insert_dom += '<td> <a href="javascript:void(0)" onclick="del_download(this)">&nbsp &nbspX</a>&nbsp &nbsp';
                        insert_dom += '<input type="hidden" name="file_name[]" value="'+file_obj.user_upload_file_name+'">';
                        insert_dom += '<input type="hidden" name="download[]" value="'+file_obj.detail+'"></td>';
                        insert_dom += '</tr>';

                        $(".rar_upload_ul").append(insert_dom);
                        //alert("上传成功");
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('上传失败')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                if(file_type =='instructions_upload'){
                    alert('You can upload only a PDF file whose size cannot exceed 50M');
                }else{
                    alert('Unknow Error!')
                }
            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}

function init_selling_point_uploader(dom_name,file_type,prev_id){
    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        var image_id = $("#div_edit_img").attr("image_id");
                        $("#"+image_id).val(file_obj.detail) ;
                        $("#"+image_id+"_back").attr('src',file_obj.detail) ;
                        //alert("上传成功")
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('上传失败')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                alert('上传失败')
            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}


function init_uploader_sql(dom_name,file_type){

    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){

                        alert("上传成功");
                        $("#input_"+id_str).val(file_obj.file_name);
                        $("#label_"+id_str).empty().html(file_obj.file_name);
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('Unknow Error!')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                if(file_type =='instructions_upload'){
                    alert('You can upload only a PDF file whose size cannot exceed 50M');
                }else{
                    alert('Unknow Error!')
                }

            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}


function init_one_pdf_uploader(dom_name,file_type){
    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        var file_name = file_obj.file_name ;
                        var user_upload_file_name = file_obj.user_upload_file_name ;
                        var insert_dom1 = '<li>'+user_upload_file_name+'</li>';
                        insert_dom1 += '<input type="hidden" name="download" value="'+file_obj.detail+'">';


                        var insert_dom ='<tr class="download_tr">';
                        insert_dom += '<td> '+user_upload_file_name +' </td>';
                        insert_dom += '<td> <a href="javascript:void(0)" onclick="del_download(this)">&nbsp &nbspX</a>&nbsp &nbsp';
                        insert_dom += '<input type="hidden" name="file_name" value="'+file_obj.user_upload_file_name+'">';
                        insert_dom += '<input type="hidden" name="download" value="'+file_obj.detail+'"></td>';
                        insert_dom += '</tr>';

                        $(".rar_upload_ul").empty().html(insert_dom);
                        //alert("上传成功");
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('上传失败')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                if(file_type =='instructions_upload'){
                    alert('You can upload only a PDF file whose size cannot exceed 50M');
                }else{
                    alert('Unknow Error!')
                }
            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}


function init_more_selling_point_uploader(dom_name,file_type){
    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        var current_btn_id = $("#div_edit_more_img").attr("current_btn_id");
                        var s_v_id = $("#div_edit_more_img").attr("s_v_id");
                        var tmp_id = $("#div_edit_more_img").attr("tmp_id");
                        var s_v_more_img_path = file_obj.detail ;
                        $.post(
                            '/goods/ajaxGetMoreImgDom',
                            {s_v_id:s_v_id,tmp_id:tmp_id,s_v_more_img_path:s_v_more_img_path},
                            function(data){
                                $("#ul_"+current_btn_id).append(data);
                            }
                        );


                        //alert("上传成功")
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('上传失败')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                alert('上传失败')
            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}

//上传资源
function init_attachments_uploader(dom_name,file_type){

    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        $("#input_"+id_str).val(file_obj.detail) ;
                        $("#input_file_name").val(file_obj.user_upload_file_name) ;
                        //alert("上传成功")
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('Unknow Error!')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                if(file_type =='instructions_upload'){
                    alert('You can upload only a PDF file whose size cannot exceed 50M');
                }else{
                    alert('Unknow Error!')
                }

            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}

function init_file_uploader(dom_name,file_type){

    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        $("#input_"+id_str).val(file_obj.detail) ;
                        $("#img_"+id_str).attr("href",file_obj.detail);
                        $("#img_"+id_str).removeClass("hide");
                        $("#img_"+id_str).show();
                        alert("上传成功");
                    }else{
                        alert(file_obj.detail);
                    }
                }else{
                    alert('Unknow Error!')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                if(file_type =='instructions_upload'){
                    alert('You can upload only a PDF file whose size cannot exceed 50M');
                }else{
                    alert('Unknow Error!')
                }

            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}

function init_source_uploader(dom_name,file_type){

    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : upload_url+"?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title: "Excel files", extensions: "xls,csv,xlsx,pdf,doc"},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.code == 1){
                        $("#input_"+id_str).val(file_obj.data) ;
                        $("#name_input_"+id_str).val(file_obj.user_upload_file_name) ;

                    }else{
                        alert(file_obj.msg);
                    }
                }else{
                    alert('Unknow Error!')
                }

            },
            Error: function(up, err) {
                //上传失败之后
                //
                if(file_type =='instructions_upload'){
                    alert('You can upload only a PDF file whose size cannot exceed 50M');
                }else{
                    alert('Unknow Error!')
                }

            }
        }
    });

    dom_name.init();
    dom_name.bind('FilesAdded',function(uploader,files){
        uploader.start();
    });
}



