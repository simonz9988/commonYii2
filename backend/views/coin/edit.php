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
                <h1>编辑币种信息
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/coin/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">名称</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['name']:''?>"name="name" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">别名</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['alias']:''?>"name="alias" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">唯一识别符</label>

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

                <div class="form-group" style="display: none ;">
                    <label class="col-sm-2 control-label no-padding-right">最低购买数量</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['min_qty']:'1'?>"name="min_qty" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">最大网格数</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['max_block']:''?>"name="max_block" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">初始价格</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['start_price']:'0'?>"id="start_price" name="start_price" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">USDT最低建仓点</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['usdt_buying_points']:'0'?>"id="input_usdt_buying_points" name="usdt_buying_points" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">USDT最高建仓点</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['usdt_max_buying_points']:'0'?>"id="input_usdt_max_buying_points" name="usdt_max_buying_points" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">BTC最低建仓点</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['btc_buying_points']:'0'?>"id="input_btc_buying_points" name="btc_buying_points" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">BTC最高建仓点</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['btc_max_buying_points']:'0'?>"id="input_btc_max_buying_points" name="btc_max_buying_points" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">ETH最低建仓点</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['eth_buying_points']:'0'?>"id="input_eth_buying_points" name="eth_buying_points" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">ETH最高建仓点</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['eth_max_buying_points']:'0'?>"id="input_eth_max_buying_points" name="eth_max_buying_points" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">USDT 交易深度</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['usdt_depth']:'0.01'?>"id="input_usdt_depth" name="usdt_depth" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">ETH 交易深度</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['eth_depth']:'0.01'?>"id="input_eth_depth" name="eth_depth" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">BTC 交易深度</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['btc_depth']:'0.01'?>"id="input_btc_depth" name="btc_depth" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">止盈下限(%)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['earn_low_percent']:'1'?>"id="input_earn_low_percent" name="earn_low_percent" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">止盈上限(%)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['earn_high_percent']:'1'?>"id="input_earn_high_percent" name="earn_high_percent" class="col-xs-12 col-sm-6" type="text">
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
                    <label class="col-sm-2 control-label no-padding-right">所属平台</label>

                    <div class="col-sm-5">
                        <select name="platform" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($bit_all_platform as $k=>$v):?>
                                <option value="<?=$k?>" <?=$info&&$info['platform']==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach;?>
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
                    <label class="col-sm-2 control-label no-padding-right">盈利类型</label>

                    <div class="col-sm-5">
                        <select name="earn_type" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($earn_type_list as $k=>$v):?>
                                <option value="<?=$k?>" <?=$info&&$info['earn_type']==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach;?>
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
                    <label class="col-sm-2 control-label no-padding-right">匹配法币</label>

                    <div class="col-sm-5">

                        <?php foreach($legal_coin_list as $k=>$v):?>
                            <input   type="checkbox" name="legal_coin_list[]" value="<?=$k?>" <?php if($info && in_array($k,$select_legal_coin_list)):?>checked="checked"<?php endif ;?> ><?=$v?>

                        <?php endforeach; ?>

                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">是否私有</label>

                    <div class="col-sm-5">
                        <select name="is_private" class="input-sm  col-xs-12 col-sm-6">
                            <option value="Y" <?=$info&&$info['is_private']=='Y'?'selected="selected"':''?>>是</option>
                            <option value="N"  <?=$info&&$info['is_private']=='N'?'selected="selected"':''?>>否</option>
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

