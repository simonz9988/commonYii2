<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->params['selectedLevel0Name']?></a> </li>
                <li class="active"><?=$this->params['selectedLevel1Name']?></li>
            </ul>
            <!-- /.breadcrumb -->
        </div>
    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->


<div class="page-content">
    <div class="page-header" >

        <div class="row">
            <div class="col-xs-6">
                <h1>请先下载批量代付模板,按照模板编辑,每笔小于<?=$cash_out_min_amount?>,暂只支持人民币批量代付,其他币种请到单笔代付
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/cash/save-batch-out" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 模板文件</label>

                    <div class="col-sm-5">
                        <a href="/public/file/template.xls" class="btn btn-danger btn-sm no-border"><i class="ace-icon glyphicon glyphicon-download"></i>点击下载模板</a>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 上传文件</label>

                    <div class="col-sm-5">
                        <input value="" name="file_name" id="input_upload_file"  class="col-xs-4 col-sm-6" type="text">
                        <button type="button" class="btn btn-purple btn-sm inline upload-image" id="upload_file" >
                            <i class="ace-icon fa fa-upload bigger-110"></i>上传文件
                        </button>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 验证码</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['code']:''?>"name="code" class="col-xs-12 col-sm-6" type="text">
                        <a href="javascript:void(0);" onclick="sendCode('CASH_BATCH_OUT')" class="sendVerifyCode btn btn-primary btn-sm no-border">发送验证码</a>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="clearfix form-actions">
                    <div class="col-md-offset-2 col-md-9">
                        <button class="btn btn-info" type="submit" id="submitButton">
                            <i class="ace-icon fa fa-check bigger-110"></i> 提交保存
                        </button>
                        <a class="btn btn-info" type="button" href="javascript:history.back()">
                            <i class="ace-icon fa fa-repeat bigger-110"></i> 返回
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<script>
    var cash_out_min_amount = <?=$cash_out_min_amount?>;
    var cash_out_max_amount = <?=$cash_out_max_amount?>;
    function sendCode(type){
        $.post(
            '/cash/send-code',
            {type:type},function(data){
                var arr = eval('('+data+')');

                if(arr.code  !=1){
                    alert('发送验证码失败')
                }else{
                    var $button = $(".sendVerifyCode");
                    var number = 60;
                    var countdown = function(){
                        if (number == 0) {
                            $button.attr("disabled",false);
                            $button.html("发送验证码");
                            number = 60;
                            return;
                        } else {
                            $button.attr("disabled",true);
                            $button.html(number + "秒 重新发送");
                            number--;
                        }
                        setTimeout(countdown,1000);
                    }
                    setTimeout(countdown,1000)
                }
            }
        );
    }

</script>


