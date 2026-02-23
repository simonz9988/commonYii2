

<style>
    .fl{float:left;margin-top: 10px;}
</style>
<div class="page-content">


    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/sunny-device/save" method="post">

                <div class="page-header">

                    <div class="row">
                        <div class="col-xs-12">
                            <h1>系统字段
                            </h1>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix ">

                    <?php foreach($enum_list['system'] as $k=>$v):?>
                        <div class="fl col-sm-2" >
                            <input type="hidden" name="user_fields[]" value="<?=in_array($k,$fields_list)?$k:''?>">
                            <a class="user_fields_btn btn <?=in_array($k,$fields_list)?"btn-success":''?>" attr_value="<?=$k?>" onclick="sel_fields(this)"><?=$v?></a>
                        </div>
                    <?php endforeach;?>
                </div>


                <div class="page-header">

                    <div class="row">
                        <div class="col-xs-12">
                            <h1>实时数据
                            </h1>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix ">

                    <?php foreach($enum_list['current'] as $k=>$v):?>
                        <div class="fl col-sm-2" >
                            <input type="hidden" name="user_fields[]" value="<?=in_array($k,$fields_list)?$k:''?>">
                            <a class="user_fields_btn btn <?=in_array($k,$fields_list)?"btn-success":''?>" attr_value="<?=$k?>" onclick="sel_fields(this)"><?=$v?></a>
                        </div>
                    <?php endforeach;?>
                </div>

                <div class="page-header">

                    <div class="row">
                        <div class="col-xs-12">
                            <h1>历史数据
                            </h1>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix ">

                    <?php foreach($enum_list['history'] as $k=>$v):?>
                        <div class="fl col-sm-2" >
                            <input type="hidden" name="user_fields[]" value="<?=in_array($k,$fields_list)?$k:''?>">
                            <a class="user_fields_btn btn <?=in_array($k,$fields_list)?"btn-success":''?>" attr_value="<?=$k?>" onclick="sel_fields(this)"><?=$v?></a>
                        </div>
                    <?php endforeach;?>
                </div>

                <div class="clearfix form-actions">
                    <div class="col-md-offset-2 col-md-9">
                        <button class="btn btn-info" type="submit" id="submitButton">
                            <i class="ace-icon fa fa-check bigger-110"></i> 提交保存
                        </button>

                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<script>
    function sel_fields(self){
        var content = $(self).prev().val();
        if(content == ""){
            $(self).prev().val($(self).attr("attr_value"));
            $(self).addClass("btn-success");
        }else{
            $(self).prev().val("");
            $(self).removeClass("btn-success");
        }
    }


    $(function() {


        //表单验证内容
        $("#systeamAddMenu").validate({
            rules: {
                name: {required: true}

            },
            messages: {
                name: {required: "必填"}
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
                $('#systeamAddMenu :submit').prop("disabled", false);
                $.ajax({
                    type: 'post',
                    url: '/sunny-device/user-fields-save',
                    data: $("form").serialize(),
                    success: function(data) {
                        // your code
                        var arr = eval('('+data+')')
                        if(arr.code == 1){
                            alert(arr.msg);
                            parent.location.reload();

                        }else{
                            alert(arr.msg);
                        }
                    }
                });
                return false ;
            }
        });

    });
</script>

