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
                <h1>编辑收入库流水明细
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/storage-sfl/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">日期</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['from_date']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" name="from_date" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">发料厂家</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['ffcj']:''?>"name="ffcj" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">收料厂家</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['slcj']:''?>"name="slcj" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">制程工序</label>

                    <div class="col-sm-5">
                        <select name="zcgx" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($zcgx_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['zcgx']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach ;?>
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
                    <label class="col-sm-2 control-label no-padding-right">产品状态</label>

                    <div class="col-sm-5">
                        <select name="cpzt" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($cpzt_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['cpzt']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach ;?>
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
                    <label class="col-sm-2 control-label no-padding-right">原炉号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['ylh']:''?>"name="ylh" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">新炉号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['xlh']:$xlh?>"name="xlh" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">牌号</label>

                    <div class="col-sm-5">
                        <select name="paihao" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($paihao_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['paihao']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach ;?>
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
                    <label class="col-sm-2 control-label no-padding-right">规格尺寸(mm)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['diameter']:''?>"name="diameter" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none ;">
                    <label class="col-sm-2 control-label no-padding-right">规格长度</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['long']:''?>"name="long" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">数量</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['amount']:''?>"name="amount" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">重量KG</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['weight']:''?>"name="weight" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">销售/代工</label>

                    <div class="col-sm-5">
                        <select name="type" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($type_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['type']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach ;?>
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
                    <label class="col-sm-2 control-label no-padding-right">来料/发货</label>

                    <div class="col-sm-5">
                        <select name="forward" class="input-sm  col-xs-12 col-sm-6">
                            <option value="IN" <?=$info&&$info['forward']=='IN'?'selected="selected"':''?>>来料</option>
                            <option value="OUT"  <?=$info&&$info['forward']=='OUT'?'selected="selected"':''?>>发货</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" >
                    <label class="col-sm-2 control-label no-padding-right">原料发货</label>

                    <div class="col-sm-5">
                        <select name="is_yl_send"  onclick="show_yl_send_total(this)" class="input-sm  col-xs-12 col-sm-6">
                            <option value="N"  <?=$info&&$info['is_yl_send']=='N'?'selected="selected"':''?>>否</option>
                            <option value="Y" <?=$info&&$info['is_yl_send']=='Y'?'selected="selected"':''?>>是</option>
                        </select>
                    </div>
                </div>
                <script>
                    function show_yl_send_total(self){
                        var is_send = $(self).val();
                        if(is_send =='Y'){
                            var cpzt = $("select[name=cpzt]").val();
                            var paihao = $("select[name=paihao]").val();

                            $.post(
                                '/storage-sfl/get-total',
                                {cpzt:cpzt,paihao:paihao},
                                function(data){
                                    var arr = eval('('+data+')');
                                    if(arr.code != 1){
                                        alert(arr.msg);
                                    }else{
                                        $("#yl_send_span").html(arr.data)
                                        $("#yl_send_div").show();
                                    }
                                }
                            );

                        }else{
                            $("#yl_send_div").hide();
                        }
                    }
                </script>

                <div class="form-group" id="yl_send_div"  <?php if(!$info || $info['is_yl_send']=='N'):?>style="display: none ;" <?php endif ;?>>
                    <label class="col-sm-2 control-label no-padding-right">原料发货汇总</label>

                    <div class="col-sm-5">
                        <label id="yl_send_span"><?=$total_weight?></label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">袋号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['bag_no']:''?>"name="bag_no" class="col-xs-12 col-sm-6" type="text">
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

                <div class="form-group" style="display: none;">
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
                        <?php if($is_modify):?>
                        <button class="btn btn-info" type="submit" id="submitButton">
                            <i class="ace-icon fa fa-check bigger-110"></i> 提交保存
                        </button>
                        <?php endif; ?>
                        <a class="btn btn-info" type="button" href="javascript:history.back()">
                            <i class="ace-icon fa fa-repeat bigger-110"></i> 返回
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

