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
                    <label class="col-sm-2 control-label no-padding-right"> 名称</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['name']:''?>"name="goods_desc" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 商品金额</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['sell_price']:''?>"name="amount" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 商品图片: </label>

                    <div class="col-sm-5 con-input">
                        <img id="img_goods_img_url" src="<?=$infoData&&$infoData['goods_img_url']  ? ftp_url($infoData['img_url']) : ''?>" class="<?=$infoData&&$infoData['img_url'] ? '' : 'hide'?>" <?=getPreviewStyle()?> >

                        <input class="col-xs-12 col-sm-10" type="hidden" name="goods_img_url" id="input_goods_img_url" value="<?=$info ? $info['goods_img_url'] : ''?>"/>
                        <a class="btn btn-danger btn-minier no-border" id="goods_img_url">
                            <i class="fa fa-upload"></i>
                            <span class="bigger-120">上传图片</span>
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

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 开始时间</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['start_time']:''?>"name="start_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 结束时间</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['end_time']:''?>"name="end_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd HH:mm:ss'})" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 备注</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['note']:''?>"name="note" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 提示信息</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['notice']:''?>"name="notice" class="col-xs-12 col-sm-12" type="text">
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
