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


    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/cash/save-batch-out" method="post">

                <input type="hidden" name="type" value="<?=$type?>">
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 模板文件</label>

                    <div class="col-sm-5">
                        <a href="/public/file/upload_template_<?=$type?>.xls" class="btn btn-danger btn-sm no-border"><i class="ace-icon glyphicon glyphicon-download"></i>点击下载模板</a>
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


