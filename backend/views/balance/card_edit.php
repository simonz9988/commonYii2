<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->selectedLevel0Name?></a> </li>
                <li class="active"><?=$this->selectedLevel1Name?></li>
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
                <h1>新增/编辑商品
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/adminPage/exchange/doAddGoods" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 卡号</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['card_no']:''?>"name="card_no" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 密码</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['card_password']:''?>"name="card_password" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 商品类型: </label>

                    <div class="col-sm-5 con-input">
                        <select class="col-xs-12 col-sm-9" name="goods_id" required>
                            <?php if($goods_list):?>
                                <?php foreach($goods_list as $k=>$v):?>
                                    <option value="<?=$v['id']?>" <?=$info && $info['goods_id']==$v['id']?'selected="selected"':''?>><?=$v['goods_desc']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">是否激活</label>

                    <div class="col-sm-5">
                        <select name="is_activited" class="input-sm  col-xs-12 col-sm-9">
                            <option value="N"  <?=$info&&$info['is_activited']=='N'?'selected="selected"':''?>>否</option>
                            <option value="Y" <?=$info&&$info['is_activited']=='Y'?'selected="selected"':''?>>是</option>
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
