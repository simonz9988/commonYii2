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
                <h1>编辑委外加工合同
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/contract-entrust/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">
                <input type="hidden" name="add_extra" value="<?=$add_extra?>">
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">合同编号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['contract_no']:''?>"  <?php if($add_extra =='y'):?>readonly="true" <?php endif ; ?> name="contract_no" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">客户名称</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['customer_name']:''?>"  <?php if($add_extra =='y'):?>readonly="true" <?php endif ; ?> name="customer_name" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">产品别</label>

                    <div class="col-sm-5">
                        <select name="chanpin_bie" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($chanpin_bie_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['chanpin_bie']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">出料形态</label>

                    <div class="col-sm-5">
                        <select name="chuliao_xingtai" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($chuliao_xingtai_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['chuliao_xingtai']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">出料尺寸(mm)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['chuliao_diameter']:''?>"name="chuliao_diameter"  class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="col-sm-2 control-label no-padding-right">出料尺寸长度(L)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['chuliao_long']:''?>"name="chuliao_long" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">委外加工尺寸(mm)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['weituo_diameter']:''?>"name="weituo_diameter" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="col-sm-2 control-label no-padding-right">委外加工尺寸长度(L)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['weituo_long']:''?>"name="weituo_long" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">委外加工状态</label>

                    <div class="col-sm-5">
                        <select name="weituo_zhuangtai" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($weituo_zhuangtai_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['weituo_zhuangtai']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">订单量(kg/pc)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['dingdl']:''?>"name="dingdl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">订单量类型</label>

                    <div class="col-sm-5">
                        <select name="dingdl_type" class="input-sm  col-xs-12 col-sm-6">
                            <option value="kg" <?=$info&&$info['dingdl_type']=='kg'?'selected="selected"':''?>>kg</option>
                            <option value="pcs"  <?=$info&&$info['dingdl_type']=='pcs'?'selected="selected"':''?>>pcs</option>
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
                    <label class="col-sm-2 control-label no-padding-right">出料重量（kg/pc）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['clzl']:''?>"name="clzl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">客诉重量（kg/pc）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['kszl']:''?>"name="kszl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">出料重量类型</label>

                    <div class="col-sm-5">
                        <select name="clzl_type" class="input-sm  col-xs-12 col-sm-6">
                            <option value="kg" <?=$info&&$info['clzl_type']=='kg'?'selected="selected"':''?>>kg</option>
                            <option value="pcs"  <?=$info&&$info['clzl_type']=='pcs'?'selected="selected"':''?>>pcs</option>
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
                    <label class="col-sm-2 control-label no-padding-right">加工单价(元/kg/pc)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['jiagongdj']:''?>"name="jiagongdj" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="col-sm-2 control-label no-padding-right">加工金额（元）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['jiagongje']:''?>"name="jiagongje" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">委外日期</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['wwrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})"name="wwrq" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">出料日期</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['clrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})"name="clrq" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">合同交期</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['htjq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})"name="htjq" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">实际交期</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['sjjq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})"name="sjjq" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">成品回厂量（kg）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['cphcl']:''?>"name="cphcl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">残料回厂量（kg）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['clhcl']:''?>"name="clhcl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">未加工返回重量（kg）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['wjgfhzl']:''?>"name="wjgfhzl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="form-group" style="display: none ;">
                    <label class="col-sm-2 control-label no-padding-right">成材率</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['ccl']:''?>"name="ccl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none ;">
                    <label class="col-sm-2 control-label no-padding-right">返材率</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['fcl']:''?>"name="fcl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">交货状态</label>

                    <div class="col-sm-5">
                        <select name="jhzt" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($jhzt_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['jhzt']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">付款状态</label>

                    <div class="col-sm-5">
                        <select name="fkzt" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($fkzt_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['fkzt']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">出货年份</label>

                    <div class="col-sm-5">
                        <select name="chnf">
                            <option value="">请选择</option>
                            <?php for($i=$start_year;$i<=$end_year;$i++):?>
                                <option value="<?=$i?>" <?=$info&&$info['chnf']==$i?'selected="selected"':''?>><?=$i?></option>
                            <?php endfor ;?>

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
                    <label class="col-sm-2 control-label no-padding-right">出货月份</label>

                    <div class="col-sm-5">
                        <select name="chyf">
                            <option value="">请选择</option>
                            <?php for($i=1;$i<=12;$i++):?>
                                <option value="<?=$i?>" <?=$info&&$info['chyf']==$i?'selected="selected"':''?>><?=$i?></option>
                            <?php endfor ;?>

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
                    <label class="col-sm-2 control-label no-padding-right"> 备注</label>

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
                        <?php endif ; ?>
                        <a class="btn btn-info" type="button" href="javascript:history.back()">
                            <i class="ace-icon fa fa-repeat bigger-110"></i> 返回
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

