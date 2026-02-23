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
                <h1>导入
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/balance/do-import" method="post">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 导入文件: </label>

                    <div class="col-sm-5 con-input">
                        <a class="btn btn-danger btn-minier no-border" id="import_btn">
                            <i class="fa fa-upload"></i>
                            <span class="bigger-120">导入</span>
                        </a>
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
                    <label class="col-sm-2 control-label no-padding-right"> 地址类型: </label>

                    <div class="col-sm-5 con-input">
                        <select class="col-xs-12 col-sm-9" name="type" required>
                            <?php if($type_list):?>
                                <?php foreach($type_list as $k=>$v):?>
                                    <option value="<?=$k?>"><?=$v?></option>
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
                    <label class="col-sm-2 control-label no-padding-right"> Token Address: </label>

                    <div class="col-sm-5 con-input">
                        <input type="text" name="token_detail">
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
                    <table id="table-1" class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>地址</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if($import_list):?>
                            <?php foreach($import_list as $k=>$v):?>
                                <tr>
                                    <td><?=$k+1?></td>
                                    <td><?=$v[0]?></td>
                                </tr>
                            <?php endforeach ; ?>
                        <?php endif ;?>
                        </tbody>
                    </table>

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
