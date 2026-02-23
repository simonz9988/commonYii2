<!-- 下面为本次需要使用到的代码部分 -->
<style>
    .s3media-button-diabled {
        cursor: not-allowed;
    }

    .s3media-button {
        border: 3px solid #6fb3e0;
        padding: 1px 5px;
        font-size: 12px;
        background-color: #6fb3e0 !important;
        color: #FFF !important;
        text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25) !important;
        outline: none;
    }

    .s3media-button:hover {
        background-color: #428bca !important;
    }

    .s3media-button:active {
        background-color: #428bca !important;
        border-color: #428bca !important;
    }

    .s3media-dialog .nav-tabs li {
        cursor: pointer;
    }

    .s3media-search-container {
        float: right;
        margin-right: 10px;
        padding-top: 5px;
    }

    .s3media-search-container input {
        height: 22px;
    }

    .s3media-search-container button {
        background-color: transparent;
        border: none;
        border: 1px solid #00b7ee;
        background-color: #00b7ee;
        color: white;
        font-weight: bold;
        border-radius: 3px;
        height: 22px;
        line-height: 17px;
    }

    .s3media-image-list .image-item {
        padding: 5px;
        display: inline-block;
        margin-right: 23px;
        margin-bottom: 5px;
        border: 1px solid #c1d1d7;
    }

    .s3media-image-list .image-item:hover {
        background-color: #F9F9F9;
        text-shadow: 1px 1px 3px #cccccc;
    }

    .s3media-image-list .image-item.last {
        margin-right: 0;
    }

    .s3media-image-list .image-item img {
        width: 102px;
        height: 102px;
    }

    .s3media-image-list .image-item p {
        width: 102px;
        margin-bottom: 0px;
        overflow: hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
        text-align: center;
    }

    .s3media-file-list div.row, .s3media-video-list div.row {
        margin-bottom: 3px;
    }

    .s3media-file-list div.row:hover, .s3media-video-list div.row:hover {
        background-color: #F9F9F9;
    }

    .s3media-file-list div.row a, .s3media-video-list div.row a {
        width: 450px;
        padding-left: 10px;
        display: inline-block;
        text-overflow: ellipsis;
        white-space:nowrap;
        overflow: hidden;
        height: 21px;
        line-height: 21px;
        font-size: 15px;
    }

    .s3media-file-list div.row button, .s3media-video-list div.row button {
        float: right;
        margin-right: 10px;
        background-color: transparent;
        border: none;
        text-shadow: 1px 1px 2px #ccc;
        height: 21px;
        border-bottom: 1px solid white;
    }

    .s3media-file-list div.row button:hover, .s3media-video-list div.row button:hover {
        border-bottom: 1px solid #ccc;
    }

    .s3media-image-page, .s3media-file-page, .s3media-video-page {
        text-align: right;
    }

    .s3media-image-page span, .s3media-file-page span, .s3media-video-page span {
        margin-right: 10px;
    }

    .s3media-image-page button, .s3media-file-page button, .s3media-video-page button {
        margin: 0 5px;
    }
</style>
<div>
    <?if($display == "show"):?>
        <button type="button" class="s3media-button s3media-choose-<?=$id?>" data-for="">Choose Assets</button>
    <?else:?>
        <button type="button" class="s3media-button s3media-choose-<?=$id?>" style="display: none;" data-for="">Choose Assets</button>
    <?endif;?>
    <div class="s3media-dialog modal" tabindex="-1" style="display: none; padding-right: 17px;">
        <div class="modal-dialog" style="width: 600px;">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 class="smaller lighter blue no-margin">Choose Assets</h3>
                </div>

                <div class="modal-body">
                    <div class="tabbable">
                        <ul class="nav nav-tabs padding-12 tab-color-blue background-blue s3media-nav-<?=$id?>">
                            <li class="active">
                                <a>Upload local file</a>
                            </li>

                            <li class="">
                                <a>Image</a>
                            </li>

                            <li class="">
                                <a>Video</a>
                            </li>

                            <li class="">
                                <a>File</a>
                            </li>

                            <div class="s3media-search-container" style="display: none;">
                                <input type="text" />
                                <button type="button" onclick="s3mediaSearch('<?=$id?>', $(this).closest('.s3media-dialog'), $(this).prev('input:text').val(), 0)"><i class="fa fa-search" aria-hidden="true"></i></button>
                            </div>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active">
                                <div class="fileUpload-<?=$id?>">Select File</div>
                            </div>

                            <div class="tab-pane">
                                <!-- 图片展示区域 -->
                                <div class="s3media-image-list s3media-image-list-<?=$id?>"></div>
                                <!-- 分页功能 -->
                                <div class="s3media-image-page"></div>
                            </div>

                            <div class="tab-pane">
                                <!-- 视频展示区域 -->
                                <div class="s3media-video-list s3media-video-list-<?=$id?>"></div>
                                <!-- 分页功能 -->
                                <div class="s3media-video-page"></div>
                            </div>

                            <div class="tab-pane">
                                <!-- 文件展示区域 -->
                                <div class="s3media-file-list s3media-file-list-<?=$id?>"></div>
                                <!-- 分页功能 -->
                                <div class="s3media-file-page"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.modal-content -->
        </div>
    </div>
</div>

<script type="text/javascript">
    var webUploader_<?=$id?> = null;
    var webUploading_<?=$id?> = false;

    $(document).on("click", ".s3media-choose-<?=$id?>", function() {
        $(".s3media-dialog").modal("hide");

        // $(this).next("div").removeClass("hide")
        var dialog = $(this).next("div")

        dialog.modal("show");

        // 初始化Web Uploader
        if (webUploader_<?=$id?> == null) {
            webUploader_<?=$id?> = WebUploader.create({
                // 多选
                multiple: false,

                // 文件上传方式
                method: 'POST',

                // 选完文件后，是否自动上传。
                auto: true,

                // swf文件路径
                swf: "<?=$swf?>",

                // 文件接收服务端。
                server: '/file/upload-media?path=<?=$path?>',

                // 选择文件的按钮。可选。
                // 内部根据当前运行是创建，可能是input元素，也可能是flash.
                pick: '.fileUpload-<?=$id?>',

                // 只允许选择图片文件。
                accept: {
                    title: 'Choose File',
                    extensions: '*',
                    mimeTypes: '*/*'
                }
            });

            // 文件上传过程中。
            webUploader_<?=$id?>.on("uploadProgress", function(file, percentage) {
                $(".fileUpload-<?=$id?> .webuploader-pick").text("Uploading...");
                $(".fileUpload-<?=$id?>").addClass("s3media-button-diabled");
                webUploading_<?=$id?> = true;
            });

            // 文件上传成功。
            webUploader_<?=$id?>.on("uploadSuccess", function(file, resp) {
                if (resp.code != 1) {
                    layer.alert("Upload Failure");
                    return ;
                }

                var _func = eval("<?=$callback?>");
                _func(resp.data)

                // 选择结束之后，隐藏按钮
                dialog.modal("hide");
            });

            // 文件上传失败，显示上传出错。
            webUploader_<?=$id?>.on("uploadError", function(file, reason) {
                layer.alert("Upload Failure");
            });

            // 完成上传完了，成功或者失败，先删除进度条。
            webUploader_<?=$id?>.on("uploadComplete", function(file) {
                $(".fileUpload-<?=$id?> .webuploader-pick").text("Select File");
                $(".fileUpload-<?=$id?>").removeClass("s3media-button-diabled");

                webUploading_<?=$id?> = false;
            });

            // 很重要的判断，用来解析是否上传成功的
            webUploader_<?=$id?>.on("uploadAccept", function(file, response) {
                if (response.error) {
                    // 通过return false来告诉组件，此文件上传有错。
                    return false;
                }
            });

            webUploader_<?=$id?>.on("beforeFileQueued", function(file) {
                return !webUploading_<?=$id?>
            });
        }
    });

    $(document).on("click", ".s3media-nav-<?=$id?> li", function() {
        var _parent = $(this).closest(".tabbable")
        var _index  = $(this).index()

        $(".nav-tabs li", _parent).removeClass("active")
        $(this).addClass("active")

        $(".tab-pane", _parent).removeClass("active")
        $(".tab-pane", _parent).eq(_index).addClass("active")

        if (_index > 0) {
            $(".s3media-search-container", _parent).show();

            s3mediaSearch('<?=$id?>', _parent.closest(".s3media-dialog"), $(".s3media-search-container input:text", _parent).val(), 0);
        } else {
            $(".s3media-search-container", _parent).hide();
        }
    })

    $(document).on("click", ".s3media-image-list-<?=$id?> .image-item, .s3media-video-list-<?=$id?> .video-item, .s3media-file-list-<?=$id?> .file-item", function() {
        _url    = $(this).data("url");
        _path   = $(this).data("path");
        _type   = $(this).data("type");
        _ext    = $(this).data("extension");
        _name   = $(this).data("name");

        var _func = eval("<?=$callback?>");
        _func({url: _url, path: _path, type: _type, ext: _ext, original_file_name: _name})

        // 选择结束之后，隐藏按钮
        $(".s3media-dialog").modal("hide");
    });

    function s3mediaSearch(id, dialog, keyword, page) {
        var index = $(".s3media-nav-" + id + " li.active").index(".s3media-nav-" + id + " li");

        var type = "image"
        if (index == 1) {
            type = "image"
        } else if (index == 2) {
            type = "video"
        } else if (index == 3) {
            type = "file"
        }

        $(".s3media-image-list, .s3media-video-list, .s3media-file-list", dialog).html("Searching...");
        $(".s3media-image-page, .s3media-video-page, .s3media-file-page", dialog).html("");

        if (page <= 0) {
            page = 1;
        }

        $.when(function(){
            var dtd = $.Deferred();

            $.ajax({
                url: "/file/search",
                data: {type: type, keyword: keyword, page: page},
                type: "GET",
                dataType: "JSON",
                success: function(data) {
                    dtd.resolve(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    dtd.rejectWith();
                }
            })

            return dtd.promise();
        }()).done(function(data) {
            if (data.code != 1) {
                $(".s3media-image-list, .s3media-video-list, .s3media-file-list", dialog).html(data.msg);

                return ;
            }

            if (type == "image") {
                parseS3MediaImage(id, dialog, keyword, data.data);
            } else if (type == "video") {
                parseS3MediaVideo(id, dialog, keyword, data.data);
            } else if (type == "file") {
                parseS3MediaFile(id, dialog, keyword, data.data);
            }
        }).fail(function(){
            $(".s3media-image-list, .s3media-video-list, .s3media-file-list", dialog).html("Network error.");
            $(".s3media-image-page, .s3media-video-page, .s3media-file-page", dialog).html("");
        });
    }

    function parseS3MediaImage(id, dialog, keyword, data) {
        html = "";

        for (var item in data.list) {
            var index = parseInt(item, 10)

            if ((index+1) % 4 == 0) {
                var c = "last";
            } else {
                var c = "";
            }

            html += "<div class='image-item " + c + "' data-type='" + data.list[item].type + "' data-name='" + data.list[item].name + "' data-extension='" + data.list[item].extension + "' data-url='" + data.list[item].url + "' data-path='" + data.list[item].path + "'><img src='" + data.list[item].url + "' /><p>" + data.list[item].name + "</p></div>";
        }

        $(".s3media-image-list", dialog).html(html);

        // 处理分页
        html = parseS3MediaPage(id, keyword, data.page);

        $(".s3media-image-page", dialog).html(html);
    }

    function parseS3MediaVideo(id, dialog, keyword, data) {
        html = "";

        for (var item in data.list) {
            html += "<div class='row'><a href='" + data.list[item].url + "' target='_blank'>" + data.list[item].name + "</a><button class='video-item' data-type='" + data.list[item].type + "' data-name='" + data.list[item].name + "' data-extension='" + data.list[item].extension + "' data-url='" + data.list[item].url + "' data-path='" + data.list[item].path + "' type='button'>Select</button></div>";
        }

        $(".s3media-video-list", dialog).html(html);

        // 处理分页
        html = parseS3MediaPage(id, keyword, data.page);

        $(".s3media-video-page", dialog).html(html);
    }

    function parseS3MediaFile(id, dialog, keyword, data) {
        html = "";

        for (var item in data.list) {
            html += "<div class='row'><a href='" + data.list[item].url + "' target='_blank'>" + data.list[item].name + "</a><button class='file-item' data-type='" + data.list[item].type + "' data-name='" + data.list[item].name + "' data-extension='" + data.list[item].extension + "' data-url='" + data.list[item].url + "' data-path='" + data.list[item].path + "' type='button'>Select</button></div>";
        }

        $(".s3media-file-list", dialog).html(html);

        // 处理分页
        html = parseS3MediaPage(id, keyword, data.page);

        $(".s3media-file-page", dialog).html(html);
    }

    function parseS3MediaPage(id, keyword, page) {
        html = "";

        if (page != "" && page.count > 0) {
            html = "<span>Total result: " + page.count + "</span>";

            if (page.totalpage > 7) {
                html += "<button type='button' onclick=\"s3mediaSearch('" + id + "', $(this).closest('.s3media-dialog'), '" + keyword + "', 1)\">1</button>";
                html += "<button type='button' onclick=\"s3mediaSearch('" + id + "', $(this).closest('.s3media-dialog'), '" + keyword + "', 2)\">2</button>";
                html += "<button type='button' onclick=\"s3mediaSearch('" + id + "', $(this).closest('.s3media-dialog'), '" + keyword + "', 3)\">3</button>";
                html += "...";
                remain_count = page.totalpage - 2;
                for (remain_count; remain_count < page.totalpage; remain_count++) {
                    html += "<button type='button' onclick=\"s3mediaSearch('" + id + "', $(this).closest('.s3media-dialog'), '" + keyword + "', " + (remain_count + 1) + ")\">" + (remain_count + 1) + "</button>";
                }
            } else {
                for (var i = 0; i < page.totalpage; i++) {
                    html += "<button type='button' onclick=\"s3mediaSearch('" + id + "', $(this).closest('.s3media-dialog'), '" + keyword + "', " + (i + 1) + ")\">" + (i + 1) + "</button>";
                }
            }
        }

        return html;
    }
</script>