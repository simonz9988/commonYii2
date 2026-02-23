/**
 * Created by joker.huang on 2017/4/12.
 */

var SelectGoods = function($_id, $id, $buttonID, $queryPath, $goodsType, $multi, $is_del, $callback, $categoryList) {
    // 数据初始化
    this._id = $_id;
    this.id = $id;
    this.buttonID = $buttonID;
    this.queryPath = $queryPath;
    this.goodsType = $goodsType;
    this.multi = $multi;
    this.is_del = $is_del;
    this.callback = $callback;
    this.categoryList = $categoryList;
    this.goodsList = null; // 全部商品列表
    this.selectedGoodsList = [];
    var parentObj = null;
    var _this = this;

    // 获取页面上的默认数据
    this.defaultSelect = function() {
        var _this = this;

        _this.selectedGoodsList = [];

        if (_this.multi) {
            // get array
            $("input[name^='" + _this.id + "']").each(function(i, n){
                _this.selectedGoodsList.push(parseInt($(n).val()));
            });
        } else {
            // get single
            _this.selectedGoodsList.push(parseInt($("input[name='" + _this.id + "']").val()));
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

        return $.inArray(i, _this.selectedGoodsList);
    }

    // 初始化全部商品
    this.initTotalGoods = function() {
        var _this = this;

        if (_this.goodsList != null) {
            return false;
        }

        $.getJSON(
            _this.queryPath,
            function(data) {
                _this.goodsList = data.data;
            }
        );
    };

    // 初始化页面数据
    this.initPageData = function() {
        _this = this;

        // 初始化数据
        var html = _this._html.replace(/{{id}}/g, this._id);

        $("body").append(html);

        parentObj = $('#modal-select-goods-container-' + _this._id);

        if (!_this.multi) {
            $(".btn-purple", parentObj).hide();
        }

        // 分类赋值
        var categoryHtml = "";
        $.each(this.categoryList, function(i, n){
            categoryHtml += '<option value="' + n.id + '">' + n.name + '</option>';
        });

        if (categoryHtml) {
            $('#category_id_' + _this._id, parentObj).html('<option value="">请选择</option>' + categoryHtml);
        } else {
            $('#category_id_' + _this._id, parentObj).closest('div').remove();
        }

        // 商品类型过滤
        $('#goods_type_' + _this._id + ' option', parentObj).each(function(i, n){
            if ($(n).val() == '' || _this.goodsType == null) {
                return true;
            }

            var optionId = parseInt($(n).val());

            if ($.inArray(optionId, _this.goodsType) != -1) {
                return true;
            }

            $(n).remove();
        });

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
                        _this.selectedGoodsList.splice(_this.inList($(n).val()), 1);
                    }
                });

                $(this).removeClass('selectAll').children('span').text('全选');
            } else {
                $("tbody input", parentObj).prop("checked", true);

                $("tbody input:visible", parentObj).each(function(i, n){
                    _this.selectedGoodsList.push(parseInt($(n).val()));
                });
                _this.selectedGoodsList = _this.unique(_this.selectedGoodsList);

                $(this).addClass('selectAll').children('span').text('全不选');
            }
        });

        // 点击按钮展示弹层
        $('#' + _this.buttonID).on('click', function(){
            _this.defaultSelect();
            _this.renderList();
            parentObj.modal('show');
        });

        // 查询按钮
        $('form button', parentObj).on('click', function(){
            _this.renderList();

            $(".btn-purple", parentObj).removeClass('selectAll').children('span').text('全选');
        });

        // 只看选中
        $('input[name="OnlySelected"]', parentObj).on('click', function(){
            if ($(this).is(":checked")) {
                $("table input", parentObj).not(":checked").closest('tr').hide();
            } else {
                $("table input", parentObj).closest('tr').show();
            }
        });

        // 选中选项
        $(document).on('click', '#modal-select-goods-container-' + _this._id + ' tbody input', function(){
            if ($(this).is(":checked")) {
                if (_this.multi) {
                    _this.selectedGoodsList.push(parseInt($(this).val()));
                } else {
                    _this.selectedGoodsList = [parseInt($(this).val())];
                }
            } else if (_this.inList($(this).val()) != -1) {
                _this.selectedGoodsList.splice(_this.inList($(this).val()), 1);
            }
        });

        // 确认按钮
        $('.btn-success', parentObj).on('click', function(){
            var data = [];
            $.each(_this.goodsList, function(i, n){
                if (_this.inList(n.id) != -1) {
                    data.push(n);
                }
            });

            parentObj.modal('hide');

            var func = eval(_this.callback);
            func(data, _this.id);
        });
    };

    // 产品列表根据条件渲染
    this.renderList = function() {
        var _this = this;

        $('input[name="OnlySelected"]', parentObj).prop('checked', false);

        $('tbody', parentObj).empty();

        var inputString = '';
        var checkString = '';
        var displayString = '';
        var tdHtml = '';

        // 商品列表数据
        if (_this.goodsList.length > 0) {
            // 商品类型
            var goodsTypeArray = [];
            $('#goods_type_' + _this._id + ' option', parentObj).each(function(i, n){
                if ($(n).val() == '') {
                    return true;
                }

                if ($(n).is(':selected')) {
                    goodsTypeArray = [$(n).val()];
                    return false;
                }

                goodsTypeArray.push($(n).val());
            });

            // 商品分类
            var goodsCategoryId = null;
            if ($('#category_id_' + _this._id + ' option:selected', parentObj).length > 0) {
                goodsCategoryId = $('#category_id_' + _this._id + ' option:selected', parentObj).val();

                if (goodsCategoryId == '') {
                    goodsCategoryId = null;
                }
            }

            // 商品名称
            var goodsName = null;
            var _goodsName = $.trim($('#name_' + _this._id, parentObj).val());
            if (_goodsName == "") {
                goodsName = null;
            } else {
                goodsName = new RegExp(_goodsName, 'i');
            }

            $.each(_this.goodsList, function(i, n) {
                displayString = '';

                // 过滤商品类型
                if ($.inArray(n.goods_type, goodsTypeArray) == -1) {
                    displayString = ' class="hide"';
                }

                // 过滤分类
                if (goodsCategoryId != null && n.category_id != goodsCategoryId) {
                    displayString = ' class="hide"';
                }

                // 商品名称
                if (goodsName != null && !goodsName.test(n.name)) {
                    displayString = ' class="hide"';
                }

                // 过滤上下架状态
                if (_this.is_del != null && _this.is_del != n.is_del) {
                    displayString = ' class="hide"';
                }

                if (_this.inList(n.id) != -1) {
                    checkString = " checked";
                } else {
                    checkString = "";
                }

                if (_this.multi) {
                    inputString = '<label><input' + checkString + ' class="ace" type="checkbox" name="goods_selected_' + _this._id + '[]" value="' + n.id + '" goods-no="' + n.ecovacs_goods_no + '" goods-name="' + n.name + '" goods-price="' + n.sell_price + '" goods-store="' + n.store_nums + '" goods-type="' + n.goods_type_label + '"><span class="lbl">&nbsp;</span></label>';
                } else {
                    inputString = '<label><input' + checkString + ' class="ace" type="radio" name="goods_selected_' + _this._id + '" value="' + n.id + '" goods-no="' + n.ecovacs_goods_no + '" goods-name="' + n.name + '" goods-price="' + n.sell_price + '" goods-store="' + n.store_nums + '" goods-type="' + n.goods_type_label + '"><span class="lbl">&nbsp;</span></label>';
                }

                tdHtml += '\
                        <tr' + displayString + '>\
                            <td>' + inputString + '</td>\
                            <td>' + n.id + '</td>\
                            <td>' + n.name + '</td>\
                            <td><img src="' + n.absolute_img + '" style="width: 50px; height: 50px;" /></td>\
                            <td>' + n.sell_price + '</td>\
                        </tr>';
            });
        }

        if (tdHtml == '') {
            tdHtml = '<tr><td colspan="5" class="text-center">根据条件查询不到相关记录</td></tr>';
        }

        $('tbody', parentObj).append(tdHtml);
    };

    // 基础数据
    // 模板
    this._html = '<!-- 选择商品代码段 -->\
            <div id="modal-select-goods-container-{{id}}" class="modal fade" tabindex="-1">\
                <div class="modal-dialog modal-lg">\
                    <div class="modal-content">\
                        <div class="modal-header" style="cursor: move; padding: 5px;">\
                            <button type="button" class="close" style="font-size: 22px;" data-dismiss="modal" aria-label="Close">\
                                <span aria-hidden="true">×</span>\
                            </button>\
                            <h4 class="modal-title">商品选择</h4>\
                        </div>\
                        <div class="modal-body">\
                            <div class="row">\
                                <div class="col-xs-12">\
                                    <div class="page-search">\
                                        <form class="form-inline" action="/" method="post" onsubmit="return false;">\
                                            <input type="hidden" name="page">\
                                            <div class="form-group">\
                                                <label>商品类型:</label> <select id="goods_type_{{id}}" class="input-sm form-control">\
                                                                                <option value="">请选择</option>\
                                                                                <option value="0">常规商品</option>\
                                                                                <option value="2">赠品</option>\
                                                                                <option value="3">DIY商品</option>\
                                                                                <option value="4">配件</option>\
                                                                                <option value="5">虚拟商品</option>\
                                                                            </select>\
                                            </div>\
                                            <div class="form-group">\
                                                <label>商品分类:</label> <select id="category_id_{{id}}" class="input-sm form-control"></select>\
                                            </div>\
                                            <div class="form-group">\
                                                <label>商品名称:</label> <input id="name_{{id}}" class="input-sm  form-control" value="" type="text">\
                                            </div>\
                                            <div class="form-group">\
                                                <div class="checkbox no-padding-left">\
                                                    <label>\
                                                        <input name="OnlySelected" type="checkbox" class="ace" value="1">\
                                                        <span class="lbl"> 只看已选择</span>\
                                                    </label>\
                                                </div>\
                                            </div>\
                                            <div class="form-group navbar-right">\
                                                <button type="button" class="btn btn-info btn-xs no-border">\
                                                    <i class="ace-icon fa fa-search bigger-120"></i> <span class="bigger-120">搜索</span>\
                                                </button>\
                                            </div>\
                                        </form>\
                                    </div>\
                                    <div class="space-4"></div>\
                                    <div style="height: 420px; overflow-y: scroll;">\
                                        <table class="table table-striped table-bordered table-hover">\
                                            <thead>\
                                                <tr>\
                                                    <th>&nbsp;</th>\
                                                    <th>商品ID</th>\
                                                    <th>商品名称</th>\
                                                    <th>商品图片</th>\
                                                    <th>销售价</th>\
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
            <!-- 选择商品代码段结束 -->';

    // 初始化功能
    this.initTotalGoods();
    this.initPageData();
}