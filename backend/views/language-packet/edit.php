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
                <h1>编辑语言包信息
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/language-packet/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">



                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">语言项key</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['item_key']:''?>"name="item_key"  <?php if($info):?>readonly="true"<?php endif ;?>class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">描述</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['description']:''?>"name="description" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">页面KEY</label>

                    <div class="col-sm-5">
                        <select name="page_key" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($page_list as $k=>$v):?>
                            <option value="<?=$k?>" <?=$info&&$info['page_key']==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach ;?>
                        </select>
                    </div>
                </div>

                <?php if($language_item_list):?>
                    <?php foreach($language_item_list as $v):?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label no-padding-right">展示内容(<?=$v['language_name']?>)</label>

                            <div class="col-sm-5">
                                <input value="<?=$v['desc_content']?>"name="language_item_list[<?=$v['language_id']?>]>" class="col-xs-12 col-sm-6" type="text">
                            </div>
                            <div class="col-sm-4">
                                <label class="col-sm-12 form-control-static reg_tip">
                                </label>
                                <label class="col-sm-12 form-control-static reg_default">

                                </label>
                            </div>
                        </div>

                    <?php endforeach ;?>
                <?php endif ; ?>

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

