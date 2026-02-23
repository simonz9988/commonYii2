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
                <h1>编辑账户信息
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/api-key/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">编号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['admin_user_id']:''?>"name="admin_user_id" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">币种</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['coin']:''?>"name="coin" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">基础购买数量</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['base_buy_num']:''?>"name="base_buy_num" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">盈利比例</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['earn_percent']:'0.002'?>"name="earn_percent" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">补仓间隔</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['add_distance']:'0.003'?>"name="add_distance" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">购买类型</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['buy_num_type']:'FEIBO'?>"name="buy_num_type" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">最大层级</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['max_level']:'6'?>"name="max_level" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">本位货币</label>

                    <div class="col-sm-5">
                        <select name="base_coin" class="input-sm  col-xs-12 col-sm-6">
                            <option value="USD" <?=$info&&$info['base_coin']=='USD'?'selected="selected"':''?>>币本位</option>
                            <option value="USDT"  <?=$info&&$info['base_coin']=='USDT'?'selected="selected"':''?>>USDT本位</option>
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
                    <label class="col-sm-2 control-label no-padding-right"> api key</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['api_key']:''?>"name="api_key" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> Secret</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['api_secret']:''?>"name="api_secret" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 支付密码</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['passphrase']:''?>"name="passphrase" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">基本购买数量</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['base_buy_num']:''?>"name="base_buy_num" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> leverage</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['leverage']:''?>"name="leverage" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">备注</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['note']:''?>"name="note" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 排序</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['sort']:''?>"name="sort" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">开启多单购买</label>

                    <div class="col-sm-5">
                        <select name="is_start_buy_up" class="input-sm  col-xs-12 col-sm-6">
                            <option value="Y" <?=$info&&$info['is_start_buy_up']=='Y'?'selected="selected"':''?>>是</option>
                            <option value="N"  <?=$info&&$info['is_start_buy_up']=='N'?'selected="selected"':''?>>否</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">开启多单交易</label>

                    <div class="col-sm-5">
                        <select name="is_start_trade_up" class="input-sm  col-xs-12 col-sm-6">
                            <option value="Y" <?=$info&&$info['is_start_trade_up']=='Y'?'selected="selected"':''?>>是</option>
                            <option value="N"  <?=$info&&$info['is_start_trade_up']=='N'?'selected="selected"':''?>>否</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">开启空单购买</label>

                    <div class="col-sm-5">
                        <select name="is_start_buy_down" class="input-sm  col-xs-12 col-sm-6">
                            <option value="Y" <?=$info&&$info['is_start_buy_down']=='Y'?'selected="selected"':''?>>是</option>
                            <option value="N"  <?=$info&&$info['is_start_buy_down']=='N'?'selected="selected"':''?>>否</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">开启空单交易</label>

                    <div class="col-sm-5">
                        <select name="is_start_trade_down" class="input-sm  col-xs-12 col-sm-6">
                            <option value="Y" <?=$info&&$info['is_start_trade_down']=='Y'?'selected="selected"':''?>>是</option>
                            <option value="N"  <?=$info&&$info['is_start_trade_down']=='N'?'selected="selected"':''?>>否</option>
                        </select>
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

