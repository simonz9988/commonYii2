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
                <h1>编辑公账流水
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/turnover-public/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">日期</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['from_date']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})"name="from_date" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">合同编号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['contract_no']:''?>"name="contract_no" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">部门</label>

                    <div class="col-sm-5">
                        <select name="department" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($department_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['department']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">款项</label>

                    <div class="col-sm-5">
                        <select name="kuanxiang" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($kuanxiang_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['kuanxiang']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">明细</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['mingxi']:''?>"name="mingxi" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">收入/支出</label>

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
                    <label class="col-sm-2 control-label no-padding-right">金额</label>

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
                    <label class="col-sm-2 control-label no-padding-right">账户余额</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['balance']:''?>"name="balance" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display:none;">
                    <label class="col-sm-2 control-label no-padding-right">发票状态</label>

                    <div class="col-sm-5">
                        <select name="invoice_status" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($invoice_status_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['invoice_status']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">报销方式</label>

                    <div class="col-sm-5">
                        <select name="baoxiao_fangshi" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($baoxiao_fangshi_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['baoxiao_fangshi']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">报销状态</label>

                    <div class="col-sm-5">
                        <select name="baoxiao_zhuangtai" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($baoxiao_zhuangtai_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$info&&$info['baoxiao_zhuangtai']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right">发票编号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['invoice_no']:''?>"name="invoice_no" class="col-xs-12 col-sm-6" type="text">
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

