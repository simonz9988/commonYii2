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
                <h1>新增/编辑手册
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/shouce/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 文件名称</label>

                    <div class="col-sm-5">

                        <input value="<?=$info?$info['file_name']:''?>"name="file_name" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 父类文件: </label>

                    <div class="col-sm-5 con-input">
                        <select class="col-xs-12 col-sm-9" name="parent_id">
                            <option value="0">请选择</option>
                            <?php if($parent_list):?>
                                <?php foreach($parent_list as $k=>$v):?>
                                    <option value="<?=$v['id']?>" <?=$info && $info['parent_id']==$v['id']?'selected="selected"':''?>><?=$v['file_name']?></option>
                                <?php endforeach ;?>
                            <?php endif ;?>

                        </select>
                        <span class="middle reg_tip "></span>
                    </div>

                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip" id="url_reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 所属飞机</label>

                    <div class="col-sm-5 con-input">
                        <select class="col-xs-12 col-sm-9" name="airplane_id" required>
                            <option value="0">请选择</option>
                            <?php if($airplane_list):?>
                                <?php foreach($airplane_list as $k=>$v):?>
                                    <option value="<?=$v['id']?>" <?=$info && $info['airplane_id']==$v['id']?'selected="selected"':''?>><?=$v['name']?></option>
                                <?php endforeach ;?>
                            <?php endif ;?>

                        </select>
                        <span class="middle reg_tip "></span>
                    </div>

                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip" id="url_reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 文件类型: </label>

                    <div class="col-sm-5 con-input">
                        <select class="col-xs-12 col-sm-9" name="file_type" required>
                            <?php if($type_list):?>
                                <?php foreach($type_list as $k=>$v):?>
                                    <option value="<?=$v?>" <?=$info && $info['file_type']==$v?'selected="selected"':''?>><?=$v?></option>
                                <?php endforeach ;?>
                            <?php endif ;?>

                        </select>
                        <span class="middle reg_tip "></span>
                    </div>

                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip" id="url_reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 上传文件: </label>

                    <div class="col-sm-5 con-input">
                        <span id="span_file_path"><?=$info?$info['file_name']:''?></span>
                        <input class="col-xs-12 col-sm-10" type="hidden" name="file_path" id="input_file_path" value="<?=$info ? $info['file_path'] : ''?>"/>
                        <a class="btn btn-danger btn-minier no-border" id="file_path">
                            <i class="fa fa-upload"></i>
                            <span class="bigger-120">上传</span>
                        </a>
                        <span class="middle reg_tip "></span>
                    </div>

                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip" id="goods_img_urll_reg_tip">
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
