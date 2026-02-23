// dom_name 代表id值 file_type 文件类型 也代表文件目录
function init_import_uploader(dom_name,file_type){

    var id_str = dom_name ;
    var dom_name = new plupload.Uploader({
        runtimes : 'html5,html4',
        browse_button : id_str, // you can pass in id...
        url : "/file/upload-import-order-file?file_type="+file_type,
        flash_swf_url : '../js/Moxie.swf',
        silverlight_xap_url : '../js/Moxie.xap',
        filters : {
            max_file_size : max_file_size,
            mime_types: [
                {title : "Image files", extensions : image_allowed_extensions},
                {title : "Zip files", extensions : zip_allowed_extensions},
                {title:  "PDF Files",extensions :pdf_allowed_extensions},
                {title:  "Excel Files",extensions :'xls'}
            ]
        },
        init:{
            FileUploaded:function(uploader,file,responseObject){

                //上传成功之后的提示
                var file_info = responseObject.response ;
                var file_obj = eval('('+file_info+')');

                if(typeof file_obj =='object'){
                    if(file_obj.status =='succ'){
                        //$("#input_"+id_str).val(file_obj.detail) ;
                        //$("#img_"+id_str).attr("src",file_obj.detail);
                        //$("#img_"+id_str).removeClass("hide");
                        alert("上传成功")
                        window.location.reload();
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

$(function() {
    init_import_uploader('import_btn','goods');


    //表单验证内容
    $("#systeamAddMenu").validate({
        rules: {
            goods_id1: {required: true}
        },
        messages: {
            goods_id1: {required: "必填"}
        },
        errorPlacement: function (error, element) {
            var errplace = element.parent().next().children('.reg_tip');
            errplace.html(error.text());
            errplace.addClass('red');

        },
        success: function (label, element) {
            var obj = $(element).parent().next().children('.reg_tip');
            obj.html('');
            obj.removeClass('red');
        },
        submitHandler: function (form) {

            $.ajax({
                cache: true,
                type: "POST",
                url:'/balance/do-import',
                data:$('#systeamAddMenu').serialize(),// 你的formid
                async: false,
                error: function(request) {
                    alert("Connection error");
                },
                success: function(data) {
                    var arr =eval('('+data+')');
                    if(arr.code ==1){
                        location.href = '/balance/list' ;
                    }else{
                        alert(arr.msg) ;
                    }
                }
            });

            return false ;

        }
    });
});
