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
                <h1>编辑销售合同
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/contract-sales/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">
                <input type="hidden" name="add_extra" value="<?=$add_extra?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">合同编号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['contract_no']:''?>" <?php if($add_extra =='y'):?>readonly="true" <?php endif ; ?> name="contract_no" class="col-xs-12 col-sm-6" type="text">
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
                        <input value="<?=$info?$info['customer_name']:''?>" <?php if($add_extra =='y'):?>readonly="true" <?php endif ; ?> name="customer_name" class="col-xs-12 col-sm-6" type="text">
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
                    <label class="col-sm-2 control-label no-padding-right">产品形态</label>

                    <div class="col-sm-5">
                        <select name="product_form" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($product_form_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['product_form']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">尺寸规格(mm)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['diameter']:''?>"name="diameter"  class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="col-sm-2 control-label no-padding-right">尺寸规格长度(L)</label>

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
                    <label class="col-sm-2 control-label no-padding-right">单价(元/kg/pcs)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['danjia']:''?>"name="danjia" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">订单量(kg/pcs)</label>

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
                    <label class="col-sm-2 control-label no-padding-right">实际出货量（kg/pcs）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['sjchl']:''?>"name="sjchl" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">客诉重量（kg/pcs）</label>

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
                    <label class="col-sm-2 control-label no-padding-right">实际出货量类型</label>

                    <div class="col-sm-5">
                        <select name="sjchl_type" class="input-sm  col-xs-12 col-sm-6">
                            <option value="kg" <?=$info&&$info['sjchl_type']=='kg'?'selected="selected"':''?>>kg</option>
                            <option value="pcs"  <?=$info&&$info['sjchl_type']=='pcs'?'selected="selected"':''?>>pcs</option>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none ;">
                    <label class="col-sm-2 control-label no-padding-right">应收款（元）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['yinskze']:''?>"name="yinskze" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">实际收款（元）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['shijishk']:''?>"name="shijishk" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display:none ;">
                    <label class="col-sm-2 control-label no-padding-right">收款差异（元）</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['skcy']:''?>"name="skcy" class="col-xs-12 col-sm-6" type="text">
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
                        <select name="jiaohzt" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($jhzt_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['jiaohzt']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">接单日期</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['jdrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" name="jdrq" class="col-xs-12 col-sm-6" type="text">
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
                        <input value="<?=$info?$info['htjq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" name="htjq" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">实际出货日期</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['sjchrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" name="sjchrq" class="col-xs-12 col-sm-6" type="text">
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
                        <?php endif ;?>
                        <a class="btn btn-info" type="button" href="javascript:history.back()">
                            <i class="ace-icon fa fa-repeat bigger-110"></i> 返回
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

