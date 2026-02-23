/**
 * Created by joker.huang on 2017/4/1.
 */

var ImportMember = {
    uploader: null,

    // 数组去重
    customUnique: function(old) {
        var res = [];
        var json = {};

        for (var i = 0; i < old.length; i++) {
            if (!json[old[i]]) {
                res.push(old[i]);
                json[old[i]] = 1;
            }
        }

        return res;
    },

    // 初始化
    create: function($id, $buttonID) {
        var html = this._html.replace(/{{id}}/g, $id);

        $("body").append(html);

        var tmp_value = $('#' + $id).val();

        // 初始化页面效果
        if (tmp_value != '') {
            var value = tmp_value.split(',');
            $('#' + $buttonID).html('<i class="ace-icon fa fa-plus"></i>导入用户(已选' + value.length + '个)');
        }

        $('#modal-import-user-container-' + $id).modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        $('#modal-import-user-select-container-' + $id).modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        $(document).on('click', '#' + $buttonID, function(){
            $('#modal-import-user-container-' + $id).modal('show');
        });

        $(document).on('click', '#modal-import-user-container-' + $id  + ' .close', function(){
            $('#modal-import-user-container-' + $id).modal('hide');
        });

        $(document).on('click', '#excel-member-select-custom-excel-' + $id, function(){
            var value = [];
            $('#modal-import-user-select-container-' + $id + ' :checkbox:checked').each(function(i, n){
                value.push($(n).val());
            });

            $("#" + $id).val(value.join(','));
            $('#' + $buttonID).html('<i class="ace-icon fa fa-plus"></i>导入用户(已选' + value.length + '个)');

            $('#modal-import-user-select-container-' + $id).modal('hide');
        });

        this.initUpload($id, $buttonID);
    },

    // 上传文件
    initUpload: function($id, $buttonID) {
        var _this = this;
        this.uploader = new plupload.Uploader({
            runtimes: 'html5,html4',
            browse_button: 'excel-member-upload-custom-excel-' + $id,
            url: '/file/parse-excel?column=1',
            flash_swf_url: '../js/Moxie.swf',
            silverlight_xap_url: '../js/Moxie.xap',
            filters: {
                max_file_count: 1,
                multi_selection:false,//是否允许选择多个文件
                max_file_size: '1mb',// 文件上传最大限制。
                chunk_size: '500kb',
                unique_names: true,
                mime_types: [
                    {title: "Excel files", extensions: "xls,xlsx"}
                ]
            },
            init: {
                FileUploaded: function(uploader, file, responseObject){
                    var responseObj = responseObject.response;
                    var rs = eval('(' + responseObj + ')');
                    if (rs.code == '1') {
                        var old_value = $("#" + $id).val();
                        var new_value = [];

                        if (old_value == "") {
                            new_value = rs.data;
                        } else {
                            old_value = old_value + ',' + rs.data.join(',');
                            new_value = old_value.split(',');
                        }

                        // 去重
                        new_value = _this.customUnique(new_value);

                        $('#modal-import-user-container-' + $id).modal('hide');

                        $.post('/member/ajax-import-list', {ids: new_value.join(',')}, function(queryResult){
                            if (queryResult.code == 0) {
                                $("#" + $id).val("");
                                layer.msg("无法查询到该文件内的任何用户");
                                return false;
                            }

                            new_value = [];
                            $('#modal-import-user-select-container-' + $id + ' tbody').empty();

                            var tr_html = '';
                            $.each(queryResult.data, function(i, n){
                                if (!n) {
                                    return true;
                                }

                                tr_html += '<tr>\
                                                <td><input type="checkbox" checked value="' + n.id + '" /></td>\
                                                <td>' + n.id + '</td>\
                                                <td>' + n.username + '</td>\
                                                <td>' + n.mobile + '</td>\
                                                <td>' + n.email + '</td>\
                                            </tr>';

                                new_value.push(n.id);
                            });

                            $("#" + $id).val(new_value.join(','));
                            $('#' + $buttonID).html('<i class="ace-icon fa fa-plus"></i>导入用户(已选' + new_value.length + '个)');

                            $('#modal-import-user-select-container-' + $id + ' tbody').html(tr_html);

                            $('#modal-import-user-select-container-' + $id).modal('show');
                        }, 'json');
                    }
                },
                Error: function(up, err) {
                    if (err['code'] == -600) {
                        bootbox.alert("仅能上传1M以内的文件");
                    } else {
                        bootbox.alert("上传文件失败");
                    }
                }
            }
        });

        this.uploader.init();
        this.uploader.bind('FilesAdded',function(uploader, files){
            uploader.start();
        });
    },

    // 模板
    _html: '<!-- 导入用户代码段 -->\
            <div id="modal-import-user-container-{{id}}" class="modal fade" tabindex="-1">\
                <div class="modal-dialog modal-lg">\
                    <div class="modal-content">\
                        <div class="modal-header no-padding">\
                            <div class="table-header">\
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">\
                                    <span class="white">&times;</span>\
                                </button>\
                                导入用户\
                            </div>\
                        </div>\
                        <div class="modal-body no-padding">\
                            <div class="well well-lg">\
                                <h4 class="blue">注意事项</h4>\
                                    <p>1、此功能可以直接将.xls格式的会员相关信息导入该系统中</p>\
                                    <p>2、目前该系统只支持.xls格式（office2003版）的导入文件</p>\
                                    <p>3、导入的数据量必须是小于2万条</p>\
                                    <p>4、导入Excel文件中所有单元格格式必须都为文本类型，否则可能出错</p>\
                                    <p>Excel 的格式如下图:</p>\
                                    <p><img src="data:image/png;base64,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" /></p>\
                            </div>\
                        </div>\
                        <div class="modal-footer no-margin-top">\
                            <button class="btn btn-primary" id="excel-member-upload-custom-excel-{{id}}" type="button">\
                                <i class="ace-icon fa fa-glyphicon-plus align-top bigger-125"></i>\
                                上传 Excel 文件\
                            </button>\
                        </div>\
                    </div>\
                </div>\
            </div>\
            <!-- 导入用户代码段结束 -->\
            <!-- 选择用户代码段 -->\
            <div id="modal-import-user-select-container-{{id}}" class="modal fade" tabindex="-1">\
                <div class="modal-dialog modal-lg">\
                    <div class="modal-content">\
                        <div class="modal-header no-padding">\
                            <div class="table-header">\
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">\
                                    <span class="white">&times;</span>\
                                </button>\
                                导入用户\
                            </div>\
                        </div>\
                        <div class="modal-body no-padding">\
                            <div style="height: 400px; overflow-y: scroll;">\
                                <table class="table table-bordered table-striped">\
                                    <tbody>\
                                    </tbody>\
                                </table>\
                            </div>\
                        </div>\
                        <div class="modal-footer no-margin-top">\
                            <button class="btn btn-primary" id="excel-member-select-custom-excel-{{id}}" type="button">\
                                <i class="ace-icon fa fa-glyphicon-plus align-top bigger-125"></i>\
                                确认\
                            </button>\
                        </div>\
                    </div>\
                </div>\
            </div>\
            <!-- 导入用户代码段结束 -->'
};