/**
 * Created by joker.huang on 2017/4/12.
 */

var SelectMember = function($_id, $id, $buttonID, $multi, $queryPath, $callback, $group_list) {
    // 数据初始化
    this._id = $_id;
    this.id = $id;
    this.buttonID = $buttonID;
    this.queryPath = $queryPath;
    this.multi = $multi;
    this.callback = $callback;
    this.groupList = $group_list;
    this.selectedMemberList = [];
    var parentObj = null;
    var _this = this;

    // 获取页面上的默认数据
    this.defaultSelect = function() {
        var _this = this;

        _this.selectedMemberList = [];

        var defaultString = $("#" + _this.id).val();

        if (typeof defaultString != 'undefined' && defaultString != "") {
            _this.selectedMemberList = defaultString.split(',');

            if (_this.selectedMemberList.length > 0) {
                for (var i in _this.selectedMemberList) {
                    _this.selectedMemberList[i] = parseInt(_this.selectedMemberList[i]);
                }
            }
        }
    }

    // 数组去重
    this.unique = function(old) {
        var res = [];
        var json = {};

        for (var i = 0; i < old.length; i++) {
            if (!json[old[i]]) {
                res.push(old[i]);
                json[old[i]] = 1;
            }
        }

        return res;
    }

    // 判断是否在列表中
    this.inList = function(i) {
        i = parseInt(i);

        return $.inArray(i, _this.selectedMemberList);
    }

    // 初始化页面数据
    this.initPageData = function() {
        _this = this;

        // 初始化数据
        var html = _this._html.replace(/{{id}}/g, this._id);

        var group_option_html = '';
        for (var i in this.groupList) {
            group_option_html += '<option value="' + i + '">' + this.groupList[i] + '</option>';
        }
        html = html.replace(/{{group_option_html}}/g, group_option_html);

        $("body").append(html);

        parentObj = $('#modal-select-member-container-' + _this._id);

        if (!_this.multi) {
            $(".btn-purple", parentObj).hide();
        }

        // 具体功能
        parentObj.modal({
            backdrop: 'static',
            keyboard: false,
            show: false
        });

        // 全选功能
        $(".btn-purple", parentObj).on('click', function(){
            if ($(this).hasClass('selectAll')) {
                $("tbody input", parentObj).prop("checked", false);

                $("tbody input:visible", parentObj).each(function(i, n){
                    if (_this.inList($(n).val()) != -1) {
                        _this.selectedMemberList.splice(_this.inList($(n).val()), 1);
                    }
                });

                $(this).removeClass('selectAll').children('span').text('全选');
            } else {
                $("tbody input", parentObj).prop("checked", true);

                $("tbody input:visible", parentObj).each(function(i, n){
                    _this.selectedMemberList.push(parseInt($(n).val()));
                });
                _this.selectedMemberList = _this.unique(_this.selectedMemberList);

                $(this).addClass('selectAll').children('span').text('全不选');
            }
        });

        // 点击按钮展示弹层
        $('#' + _this.buttonID).on('click', function(){
            _this.defaultSelect();
            parentObj.modal('show');
        });

        // 查询按钮
        $(document).on('click', '#modal-select-member-container-' + _this._id + ' .last, #modal-select-member-container-' + _this._id + ' .first', function(){
            var page = $(this).data('page');

            $("#page_" + _this._id).val(page);

            _this.getData();

            $(".btn-purple", parentObj).removeClass('selectAll').children('span').text('全选');
        });

        // 查询按钮
        $('form button', parentObj).on('click', function(){
            $("#page_" + _this._id).val(0);

            _this.getData();

            $(".btn-purple", parentObj).removeClass('selectAll').children('span').text('全选');
        });

        // 选中选项
        $(document).on('click', '#modal-select-member-container-' + _this._id + ' tbody input', function(){
            if ($(this).is(":checked")) {
                if (_this.multi) {
                    _this.selectedMemberList.push(parseInt($(this).val()));
                } else {
                    _this.selectedMemberList = [parseInt($(this).val())];
                }
            } else if (_this.inList($(this).val()) != -1) {
                _this.selectedMemberList.splice(_this.inList($(this).val()), 1);
            }
        });

        // 确认按钮
        $('.btn-success', parentObj).on('click', function(){
            var func = eval(_this.callback);
            func(_this.selectedMemberList, _this.id);

            parentObj.modal('hide');
        });
    };

    // 查询数据
    this.getData = function() {
        var _this = this;

        $('tbody', parentObj).html('<tr><td colspan="6" class="text-center">正在查询...</td></tr>');

        var page = $("#page_" + _this._id).val();
        var username = $("#username_" + _this._id).val();
        var email = $("#email_" + _this._id).val();
        var group_id = $("#group_id_" + _this._id).val();
        var sex = $("input[name='sex_" + _this._id + "']:checked").val();

        var searchData = {search: {username: username, email: email, group_id: group_id, sex: sex}, page: page};

        var tdHtml = '';
        var checkString = '';
        var groupString = '';

        $.getJSON(
            _this.queryPath,
            searchData,
            function(data) {
                for (var i in data.data.list) {
                    var n = data.data.list[i];

                    if (_this.inList(n.user_id) != -1) {
                        checkString = " checked";
                    } else {
                        checkString = "";
                    }

                    if (_this.multi) {
                        inputString = '<label><input' + checkString + ' class="ace" type="checkbox" name="goods_selected_' + _this._id + '[]" value="' + n.user_id + '" goods-no="' + n.ecovacs_goods_no + '" goods-name="' + n.name + '" goods-price="' + n.sell_price + '" goods-store="' + n.store_nums + '" goods-type="' + n.goods_type_label + '"><span class="lbl">&nbsp;</span></label>';
                    } else {
                        inputString = '<label><input' + checkString + ' class="ace" type="radio" name="goods_selected_' + _this._id + '" value="' + n.user_id + '" goods-no="' + n.ecovacs_goods_no + '" goods-name="' + n.name + '" goods-price="' + n.sell_price + '" goods-store="' + n.store_nums + '" goods-type="' + n.goods_type_label + '"><span class="lbl">&nbsp;</span></label>';
                    }

                    if (_this.groupList.hasOwnProperty(n.group_id)) {
                        groupString = _this.groupList[n.group_id];
                    } else {
                        groupString = '';
                    }


                    tdHtml += '\
                            <tr>\
                                <td>' + inputString + '</td>\
                                <td>' + n.user_id + '</td>\
                                <td>' + n.username + '</td>\
                                <td>' + groupString + '</td>\
                                <td>' + n.email + '</td>\
                                <td>' + n.mobile + '</td>\
                            </tr>';
                }

                if (tdHtml == '') {
                    tdHtml = '<tr><td colspan="6" class="text-center">根据条件查询不到相关记录</td></tr>';
                } else {
                    var page_html = "";

                    if (data.data.prev.row) {
                        page_html += '<a href="javascript:void(0)" class="first pageprv" data-page="' + data.data.prev.row + '"><span class="pagearr">&lt;</span>上一页</a>';
                    }

                    if (data.data.next.row) {
                        page_html += '<a href="javascript:void(0)" class="last pagenxt" data-page="' + data.data.next.row + '">下一页<span class="pagearr">&gt;</span></a>';
                    }

                    if (page_html) {
                        tdHtml += '<tr><td colspan="6"><div class="m-page m-page-rt">' + page_html + '</div></td></tr>';
                    }
                }

                $('tbody', parentObj).html(tdHtml);
            }
        );

    }

    // 基础数据
    // 模板
    this._html = '<!-- 选择用户代码段 -->\
            <div id="modal-select-member-container-{{id}}" class="modal fade" tabindex="-1">\
                <div class="modal-dialog modal-lg">\
                    <div class="modal-content">\
                        <div class="modal-header" style="cursor: move; padding: 5px;">\
                            <button type="button" class="close" style="font-size: 22px;" data-dismiss="modal" aria-label="Close">\
                                <span aria-hidden="true">×</span>\
                            </button>\
                            <h4 class="modal-title">用户选择</h4>\
                        </div>\
                        <div class="modal-body">\
                            <div class="row">\
                                <div class="col-xs-12">\
                                    <div class="page-search">\
                                        <form class="form-inline" action="/" method="post" onsubmit="return false;">\
                                            <input type="hidden" id="page_{{id}}">\
                                            <div class="form-group">\
                                                <label>用户名:</label> <input id="username_{{id}}" class="input-sm form-control" value="" type="text">\
                                            </div>\
                                            <div class="form-group">\
                                                <label>邮箱:</label> <input id="email_{{id}}" class="input-sm form-control" value="" type="text">\
                                            </div>\
                                            <div class="form-group">\
                                                <label>会员组:</label> <select id="group_id_{{id}}" class="input-sm form-control">{{group_option_html}}</select>\
                                            </div>\
                                            <div class="form-group">\
                                                <label>性别:</label>\
                                                <span class="inline no-padding-left">\
                                                    <label>\
                                                        <input name="sex_{{id}}" type="radio" value="1" class="ace">\
                                                        <span class="lbl"> 男</span>\
                                                    </label>\
                                                </span>\
                                                <span class="inline">\
                                                    <label>\
                                                        <input name="sex_{{id}}" type="radio" value="2" class="ace">\
                                                        <span class="lbl"> 女</span>\
                                                    </label>\
                                                </span>\
                                            </div>\
                                            <div class="form-group navbar-right">\
                                                <button type="button" class="btn btn-info btn-xs no-border">\
                                                    <i class="ace-icon fa fa-search bigger-120"></i> <span class="bigger-120">搜索</span>\
                                                </button>\
                                            </div>\
                                        </form>\
                                    </div>\
                                    <div class="space-4"></div>\
                                    <div style="height: 467px; overflow-y: scroll;">\
                                        <table class="table table-striped table-bordered table-hover">\
                                            <thead>\
                                                <tr>\
                                                    <th>&nbsp;</th>\
                                                    <th>用户ID</th>\
                                                    <th>用户名</th>\
                                                    <th>用户组</th>\
                                                    <th>邮箱</th>\
                                                    <th>手机号码</th>\
                                                </tr>\
                                            </thead>\
                                            <tbody></tbody>\
                                        </table>\
                                    </div>\
                                </div>\
                            </div>\
                        </div>\
                        <div class="modal-footer no-margin-top">\
                            <button class="btn btn-sm btn-purple" type="button">\
                                <i class="ace-icon glyphicon glyphicon-list"></i><span>全选</span>\
                            </button>\
                            <button class="btn btn-sm btn-success" type="button">\
                                <i class="ace-icon fa fa-check"></i>确定\
                            </button>\
                        </div>\
                    </div>\
                </div>\
            </div>\
            <!-- 选择用户代码段结束 -->';

    // 初始化功能
    this.initPageData();
}