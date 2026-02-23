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
                <h1>编辑客户信息
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/sunny-company/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">所属国家</label>

                    <div class="col-sm-5">
                        <select name="country_id" class="input-sm  col-xs-12 col-sm-6">
                            <option value="">请选择</option>
                            <?php foreach($country_list as $v){?>
                                <option value="<?=$v['id']?>" <?php if($info && $info['country_id'] ==$v['id']){echo 'selected="selected"' ;}?> ><?=$v['name']?></option>
                            <?php } ?>
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
                    <label class="col-sm-2 control-label no-padding-right">省</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['province']:''?>"name="province" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">市</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['city']:''?>"name="city" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">区</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['area']:''?>"name="area" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">注册邀请码</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['unique_key']:''?>"name="unique_key" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">统一客户名称</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['company_name']:''?>"name="company_name" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">Logo</label>

                    <div class="col-sm-5">
                        <img id="img_img_url" src="<?=$info&&$info['img_url']  ? ($info['img_url']) : ''?>" class="<?=$info&&$info['img_url'] ? '' : 'hide'?>" <?=getPreviewStyle()?> >

                        <input class="col-xs-12 col-sm-10" type="hidden" name="img_url" id="input_img_url" value="<?=$info ? $info['img_url'] : ''?>"/>
                        <a class="btn btn-danger btn-minier no-border" id="img_url">
                            <i class="fa fa-upload"></i>
                            <span class="bigger-120">上传图片</span>
                        </a>
                        <span class="middle reg_tip "></span>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <?php if($language_item_list):?>
                    <?php foreach($language_item_list as $v):?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label no-padding-right">多语言公司名称(<?=$v['language_name']?>)</label>

                            <div class="col-sm-5">
                                <input value="<?=$v['name']?>"name="language_item_list[<?=$v['language_id']?>][name]" class="col-xs-12 col-sm-6 input_name" type="text">
                            </div>
                            <div class="col-sm-4">
                                <label class="col-sm-12 form-control-static reg_tip">
                                </label>
                                <label class="col-sm-12 form-control-static reg_default">

                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label no-padding-right">多语言公司地址(<?=$v['language_name']?>)</label>

                            <div class="col-sm-5">
                                <input value="<?=$v['address']?>"name="language_item_list[<?=$v['language_id']?>][address]" class="col-xs-12 col-sm-6 input_name" type="text">
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

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">是否有效</label>

                    <div class="col-sm-5">
                        <select name="status" class="input-sm  col-xs-12 col-sm-6">
                            <option value="ENABLED" <?=$info&&$info['status']=='ENABLED'?'selected="selected"':''?>>是</option>
                            <option value="DISABLED"  <?=$info&&$info['status']=='DISABLED'?'selected="selected"':''?>>否</option>
                        </select>
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

