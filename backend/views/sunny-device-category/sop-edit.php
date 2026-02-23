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
    <div class="page-header">

        <div class="row">
            <div class="col-xs-6">
                <h1>编辑SOP信息
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/sunny-device-category/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">所属语言</label>

                    <div class="col-sm-5">
                        <select name="language_id" class="input-sm  col-xs-12 col-sm-6">
                            <?php if($language_list):?>
                                <?php foreach($language_list as $v){?>
                                    <option value="<?=$v['id']?>" ><?=$v['name']?></option>
                                <?php } ?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">所属分类</label>

                    <div class="col-sm-5">
                        <script>
                            function select_category_ids(self){
                                var  category_id = $(self).val();
                                $.post(
                                    '/sunny-device-category/ajax-get-son-list',
                                    {category_id:category_id},
                                    function(data){
                                        var arr =eval('('+data+')')
                                        if(arr.code != 1){
                                            alert(arr.msg)
                                        }else{
                                            $("#select_category_id").empty().html(arr.data);
                                        }
                                    }
                                )
                            }
                        </script>
                        <select name="parent_category_id" class="input-sm  col-xs-12 col-sm-3"  onchange="select_category_ids(this)">
                            <?php if($parent_category_list):?>
                                <?php foreach($parent_category_list as $v){?>
                                    <option value="<?=$v['id']?>" ><?=$v['name']?></option>
                                <?php } ?>
                            <?php endif;?>
                        </select>

                        <select name="category_id" id="select_category_id"class="input-sm  col-xs-12 col-sm-3">
                            <?php if($category_list):?>
                                <?php foreach($category_list as $v){?>
                                    <option value="<?=$v['id']?>" ><?=$v['name']?></option>
                                <?php } ?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">SOP</label>

                    <div class="col-sm-5">
                        <input id="input_upload_file"  value=""name="sop_url" class="col-xs-12 col-sm-6" type="text">
                        <input type="hidden"id="name_input_upload_file"  value=""name="file_name" class="col-xs-12 col-sm-6" type="text">

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
                <script>
                    $(function() {
                        init_source_uploader('upload_file','sop_url')
                    })
                </script>

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

