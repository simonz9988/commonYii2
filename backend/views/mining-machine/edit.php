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
                <h1>编辑矿机信息
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/mining-machine/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">所选币种</label>

                    <div class="col-sm-5">
                        <select name="coin" class="input-sm  col-xs-12 col-sm-6">
                            <option value="">请选择</option>
                            <?php foreach($mining_coin_list as $k=>$v){?>
                                <option value="<?=$k?>" <?php if($info && $info['coin'] ==$k){echo 'selected="selected"' ;}?> ><?=$v['name']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">所属活动</label>

                    <div class="col-sm-5">
                        <select name="activity_id" class="input-sm  col-xs-12 col-sm-6">
                            <option value="">请选择</option>
                            <?php foreach($activity_list as $k=>$v){?>
                                <option value="<?=$v['id']?>" <?php if($info && $info['activity_id'] ==$v['id']){echo 'selected="selected"' ;}?> ><?=$v['name']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">标题</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['title']:''?>"name="title" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">副标题</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['sub_title']:''?>"name="sub_title" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">算力(单位:T)</label>

                    <div class="col-sm-5">
                        <input id="calc_power"value="<?=$info?$info['calc_power']:''?>"name="calc_power" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">价格(USDT)</label>

                    <div class="col-sm-5">
                        <input id="input_price"value="<?=$info?$info['price']:''?>"name="price" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">结算周期(天)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['period']:''?>"name="period" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">合约期限(天)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['limit_day']:''?>"name="limit_day" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">库存(台)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['store_num']:''?>"name="store_num" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">是否预售</label>

                    <div class="col-sm-5">
                        <select name="is_pre_sell" class="input-sm  col-xs-12 col-sm-6">
                            <option value="Y" <?=$info&&$info['is_pre_sell']=='Y'?'selected="selected"':''?>>是</option>
                            <option value="N"  <?=$info&&$info['is_pre_sell']=='N'?'selected="selected"':''?>>否</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">服务费(%，最低2位小数，最大99.99)</label>

                    <div class="col-sm-5">
                        <input id="input_fee" value="<?=$info?$info['fee']:''?>"name="fee" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">图片</label>

                    <div class="col-sm-5">
                        <img id="img_cover_img_url" src="<?=$info&&$info['cover_img_url']  ? ($info['cover_img_url']) : ''?>" class="<?=$info&&$info['cover_img_url'] ? '' : 'hide'?>" <?=getPreviewStyle()?> >

                        <input class="col-xs-12 col-sm-10" type="hidden" name="cover_img_url" id="input_cover_img_url" value="<?=$info ? $info['cover_img_url'] : ''?>"/>
                        <a class="btn btn-danger btn-minier no-border" id="cover_img_url">
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


                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">内容</label>

                    <div class="col-sm-5">
                        <textarea class="col-xs-6 col-sm-9" id="content1"name="content" ><?=$info?$info['content']:''?></textarea>
                        <script>
                            $(function() {
                                var ue = UE.getEditor('content1');
                                ue.setContent('<?=isset($info['content']) ? $info['content'] : ""?>');
                            });

                        </script>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">SEO title</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['seo_title']:''?>"name="seo_title" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">SEO keywords</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['seo_keywords']:''?>"name="seo_keywords" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">SEO description</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['seo_description']:''?>"name="seo_description" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">排序</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['sort']:'99'?>"name="sort" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

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

