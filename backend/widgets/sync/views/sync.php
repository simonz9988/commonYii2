<style>
    .sync-dialog {
        text-align: left;
        line-height: normal;
    }

    .sync-dialog-processing-list {
        height: 350px;
    }

    .sync-dialog-processing-list .separator {
        height: 240px;
        width: 1px;
        border-right: 1px solid #ccc;
        display: inline-block;
        margin-top: 30px;
        box-shadow: 1px 1px 5px #ccc;
    }

    .sync-dialog-processing-list .sync-record-block {
        width: 175px;
        height: 38px;
        display: inline-block;
        border: 1px solid #ccc;
        margin: 3px 5px;
        padding: 10px;
    }

    .sync-dialog-processing-list .sync-record-block span {
        text-shadow: 2px 2px 3px #ccc;
        text-overflow: ellipsis;
        white-space:nowrap;
        overflow: hidden;
        line-height: 16px;
        width: 130px;
        display: inline-block;
    }

    .sync-dialog-processing-list .sync-record-block i {
        font-size: 15px;
        float: right;
        text-shadow: none;
    }

    .sync-dialog-processing-list .sync-record-block.red {
        font-color: red;
    }

    .sync-dialog-processing-list .sync-record-block.green {
        font-color: green;
    }

    .sync-dialog-processing-list .sync-languages-block {
        display: inline-block;
        width: 49%;
        height: 300px;
        float: left;
        overflow-x: hidden;
        overflow-y: auto;
    }

    .sync-dialog-processing-list .sync-countries-block {
        display: inline-block;
        width: 49%;
        float: right;
        overflow-x: hidden;
        overflow-y: auto;
        height: 300px;
    }

    .sync-dialog-processing-list .sync-languages-block>label:first-child, .sync-dialog-processing-list .sync-countries-block>label:first-child {
        padding: 5px 2px;
        font-weight: bold;
        font-size: 15px;
    }

    .sync-dialog-processing-list .sync-countries-block>label.select_all {
        margin-left: 20px;
    }

    .sync-dialog-processing-list .sync-buttons {
        display: block;
        width: 100%;
        text-align: right;
        clear: both;
        padding-top: 10px;
    }
</style>
<button class="btn btn-default btn-sm no-border sync-trigger-button J_checkauth" data-auth="/sync/prepare" type="button"><i class="ace-icon fa fa-paper-plane"></i> Sync</button>

<div class="sync-dialog modal" tabindex="-1" style="display: none; padding-right: 17px;">
    <div class="modal-dialog" style="width: 600px;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" aria-hidden="true">×</button>
                <h3 class="smaller lighter blue no-margin">Confirm your synchronization</h3>
            </div>

            <div class="modal-body">
                <div class="sync-dialog-processing-list"></div>
            </div>
        </div><!-- /.modal-content -->
    </div>
</div>

<script type="text/javascript">
    $(function(){
        $(".sync-dialog").modal({
            keyboard: false,
            backdrop: "static",
            show: false
        });
    });

    $(document).on("click", ".sync-trigger-button", function() {
        $(".sync-dialog").modal("hide");
        var dialog = $(this).next(".sync-dialog");

        // 获取全部需要同步的记录 ID
        var ids = [];
        $("input[name^=<?=$field?>]:checked").each(function(k, v){
            ids.push($(v).val());
        });

        if (ids.length == 0) {
            layer.alert("Please select the records you want sync to.")
            return ;
        }

        $(".sync-dialog-processing-list", dialog).html("");

        dialog.modal("show");

        $.getJSON("/sync/prepare", {ids: ids.join(","), table: "<?=$table?>", key: "<?=$key?>"}, function(data) {
            if (data.code != 1) {
                layer.alert(data.msg);
                return ;
            }

            // 语言
            var language_html = "<label style='padding-bottom: 0px;'>Choose language</label><label style='font-size: 11px; color: gray; margin-bottom: 10px; margin-left: 2px;'>Only show the intersection languages of records</label>";
            for (var i in data.data.language) {
                language_html += "<div><label><input type=\"radio\" name=\"sync-language\" value=\""+ data.data.language[i].id +"\" />"+ data.data.language[i].name +"</label></div>";
            }

            // 国家
            var country_html = "<label>Choose Countries</label><label class=\"select_all\"><input type=\"checkbox\" class=\"sync-country-all\" />Select All</label>";
            for (var i in data.data.country) {
                country_html += "<div><label><input type=\"checkbox\" name=\"sync-country[]\" value=\""+ data.data.country[i].country + "-" + data.data.country[i].language + "\" />"+ data.data.country[i].country + " - " + data.data.country[i].language + "</label></div>";
            }

            var html = "";
            html += "<div class=\"sync-languages-block\">" + language_html + "</div>";
            html += "<div class=\"separator\"></div>";
            html += "<div class=\"sync-countries-block\">" + country_html + "</div>";
            html += "<div class=\"sync-buttons\"><button class=\"btn btn-primary btn-sm no-border sync-button-confirm\" type=\"button\"><i class=\"ace-icon fa fa-paper-plane\"></i> Confirm</button></div>";

            $(".sync-dialog-processing-list", dialog).html(html);
        });
    });

    // 选中全部国家
    $(document).on("click", ".sync-country-all", function(){
        var checked = false;
        if ($(this).prop("checked")) {
            checked = true;
        }

        $("input[name^=sync-country]").prop("checked", checked);
    });

    // 关闭功能
    // 为了保证功能的稳定，在同步开始后，禁止关闭窗口
    $(document).on("click", ".sync-dialog .close", function() {
        if ($(".sync-dialog-processing-list .fa-clock-o, .sync-dialog-processing-list .fa-spinner").length > 0) {
            layer.alert("We are processing the synchronized data!<br /><br />Sorry, please do not close this window until it is processed.");
        } else {
            $(this).closest(".sync-dialog").modal("hide");
        }
    });

    // 确认开始同步
    $(document).on("click", ".sync-button-confirm", function(){
        var dialog = $(this).closest(".sync-dialog");
        $(this).prop("disabled", true);

        // 获得语言项
        var language = $("input[name=sync-language]:checked", dialog).val();

        // 获得国家项
        var countries = [];
        $("input[name^=sync-country]:checked", dialog).each(function(i, n){
            countries.push($(n).val());
        });

        if (typeof language == "undefined") {
            layer.alert("Please choose language.");

            $(this).prop("disabled", false);
            return ;
        }

        if (countries.length == 0) {
            layer.alert("Please select the country and the language that you want sync to.");

            $(this).prop("disabled", false);
            return ;
        }

        // 获取全部需要同步的记录 ID
        var ids = [];
        var titles = [];
        $("input[name^=<?=$field?>]:checked").each(function(k, v){
            ids.push($(v).val());

            _title = $(v).data("title");

            if (typeof _title == "undefined" || _title == "") {
                titles.push("Record ID: " + $(v).val())
            } else {
                titles.push(_title)
            }
        });

        if (ids.length == 0) {
            $(this).prop("disabled", false);

            return ;
        }

        var html = "";
        for (var i in ids) {
            html += "<div class=\"sync-record-block\" id=\"sync-record-"+ ids[i] +"\"><span>" + titles[i] + "</span><i class=\"fa fa-clock-o\" aria-hidden=\"true\"></i></div>";
        }

        $(".sync-dialog-processing-list", dialog).html(html);

        // 同步处理同步请求
        recursiveSync(ids, language, countries);
    });

    // 同步的函数，主要用于解决逐条执行的问题
    function recursiveSync(ids, language, countries) {
        if (ids.length == 0) {
            return ;
        }

        var id = ids.shift();

        $.when(function() {
            var dtd = $.Deferred()
            $.ajax({
                url: "/sync/process",
                data: {record_id: id, model: "<?=$model?>", language: language, countries: countries},
                type: "POST",
                dataType: "JSON",
                beforeSend: function(xhr) {
                    $("#sync-record-" + id + " i").removeClass("fa-clock-o fa-check fa-times").addClass("fa-spinner");
                },
                success: function(data) {
                    dtd.resolve(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    dtd.rejectWith();
                }
            })

            return dtd.promise();
        }()).done(function(data) {
            if (data.code == 1) {
                $("#sync-record-" + id + " i").removeClass("fa-clock-o fa-spinner fa-times").addClass("fa-check");
                $("#sync-record-" + id).addClass("green")
            } else {
                $("#sync-record-" + id + " i").removeClass("fa-clock-o fa-spinner fa-check").addClass("fa-times");
                $("#sync-record-" + id).addClass("red")
            }

            recursiveSync(ids, language, countries);
        }).fail(function() {
            $("#sync-record-" + id + " i").removeClass("fa-clock-o fa-spinner fa-check").addClass("fa-times");
            $("#sync-record-" + id).addClass("red")

            recursiveSync(ids, language, countries);
        });
    }
</script>