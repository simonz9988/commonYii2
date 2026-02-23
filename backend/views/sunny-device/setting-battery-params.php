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
            <div class="col-xs-12">

                <div class="tabbable">
                    <ul class="nav nav-tabs padding-12 tab-color-blue background-blue" id="myTab4">
                        <li class="<?=$is_other?'active':''?>">
                            <a  href="<?=$is_other?'javascript:void(0);':'/sunny-device/setting-other?ids='.$ids?>" aria-expanded="false">负载参数</a>
                        </li>

                        <li class="<?=$is_battery?'active':''?>">
                            <a  href="<?=$is_battery?'javascript:void(0);':'/sunny-device/setting-battery-params?ids='.$ids?>" aria-expanded="true">蓄电池参数</a>
                        </li>


                    </ul>


                </div>
            </div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/sunny-device/setting-battery-params-save" method="post">

                <input type="hidden" name="ids" value="<?=$ids?>">
                <input type="hidden" id="is_save_template"name="is_save_template" value="0">
                <input type="hidden" id="save_template_name"name="save_template_name" value="">



                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">模板</label>

                    <div class="col-sm-5">
                            <select name="template_id" id="select_template_id" class="input-sm  col-xs-12 col-sm-6" onchange="selectTemplate(this)">
                                <option value="0">请选择</option>
                                <?php if($template_list):?>
                                    <?php foreach($template_list as $k=>$v){?>
                                        <option value="<?=$v['id']?>" <?php if($info && $info['template_id'] ==$v['id']){echo 'selected="selected"' ;}?> ><?=$v['name']?></option>
                                    <?php } ?>
                                <?php endif;?>
                            </select>
                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">蓄电池类型</label>

                    <div class="col-sm-5">
                        <select name="battery_type" class="input-sm  col-xs-12 col-sm-6">
                            <?php if($battery_type_list):?>
                                <?php foreach($battery_type_list as $k=>$v){?>
                                    <option value="<?=$k?>" <?php if($info && $info['battery_type'] ==$k){echo 'selected="selected"' ;}?> ><?=$v?></option>
                                <?php } ?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">锂电池类型</label>

                    <div class="col-sm-5">
                        <select name="li_type" class="input-sm  col-xs-12 col-sm-6">
                            <?php if($li_battery_type_list):?>
                                <?php foreach($li_battery_type_list as $k=>$v){?>
                                    <option value="<?=$k?>" <?php if($info && $info['li_type'] ==$k){echo 'selected="selected"' ;}?> ><?=$v?></option>
                                <?php } ?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">系统电压</label>
                    <div class="col-sm-5">
                        <select name="battery_rate_volt" class="input-sm  col-xs-12 col-sm-6">
                            <option value="3" <?=$device_info['battery_rate_volt']==3?'selected="selected"':''?>>3V</option>
                            <option value="6" <?=$device_info['battery_rate_volt']==6?'selected="selected"':''?>>6V</option>
                            <option value="12" <?=$device_info['battery_rate_volt']==12?'selected="selected"':''?>>12V</option>
                            <option value="24"<?=$device_info['battery_rate_volt']==24?'selected="selected"':''?>>24V</option>
                            <option value="36" <?=$device_info['battery_rate_volt']==36?'selected="selected"':''?>>36V</option>
                            <option value="48" <?=$device_info['battery_rate_volt']==48?'selected="selected"':''?>>48V</option>
                        </select>
                    </div>
                </div>

                 <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">负载电流设置(超压电压)</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_over_volt']:'15.5'?>"  name="bat_over_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                     <div class="col-sm-1">
                         <label class="col-sm-12 form-control-static reg_tip">
                         </label>
                         <label class="col-sm-12 form-control-static reg_default">

                         </label>
                     </div>
                </div>

                <div class="form-group  col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">充电限制电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_charge_limit_volt']:'15'?>"  name="bat_charge_limit_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group  col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">均衡充电电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_const_charge_volt']:'14.6'?>"  name="bat_const_charge_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group  col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">提升充电电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_improve_charge_volt']:'14.4'?>"  name="bat_improve_charge_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group  col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">浮充充电电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_float_charge_volt']:'13.8'?>"  name="bat_float_charge_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group  col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">提升充电返回电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_improve_charge_back_volt']:'13.2'?>"  name="bat_improve_charge_back_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group  col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">过放返回电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_over_discharge_back_volt']:'12.6'?>"  name="bat_over_discharge_back_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">欠压警告电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_under_volt']:'12'?>"  name="bat_under_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group  col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">过放电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_over_discharge_volt']:'11'?>"  name="bat_over_discharge_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">充电上限温度</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['charge_max_temper']:'60'?>"  name="charge_max_temper" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">充电下限温度</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['charge_min_temper']:'-30'?>"  name="charge_min_temper" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">放电上限温度</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['discharge_max_temper']:'60'?>"  name="discharge_max_temper" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">放电下限温度</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['discharge_min_temper']:'-30'?>"  name="discharge_min_temper" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">光控电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['light_control_volt']:'5'?>"  name="light_control_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">放电限制电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_discharge_limit_volt']:'10.6'?>"  name="bat_discharge_limit_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">充电截止 SOC,放电截 止 SOC</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_stop_soc']:'25610'?>"  name="bat_stop_soc" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">过放延时时间</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_over_discharge_delay']:'5'?>"  name="bat_over_discharge_delay" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">均衡充电时间</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_const_charge_time']:'120'?>"  name="bat_const_charge_time" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">提升充电时间</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_improve_charge_time']:'120'?>"  name="bat_improve_charge_time" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">均衡充电间隔</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_const_charge_gap_time']:'30'?>"  name="bat_const_charge_gap_time" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">温度补偿系数</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['coeff_temper_compen']:'5'?>"  name="coeff_temper_compen" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">(锂电)加热启动电池温 度</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['heat_bat_start_temper']:'-10'?>"  name="heat_bat_start_temper" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">(锂电)加热停止电池温</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['heat_bat_stop_temper']:'-5'?>"  name="heat_bat_stop_temper" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">市电切换电压</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['bat_switch_dc_volt']:'11.5'?>"  name="bat_switch_dc_volt" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">(锂电) 停止充电电流</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['stop_charge_current_set']:'0'?>"  name="stop_charge_current_set" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">直流负载工作模式</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['dc_load_mode']:'0'?>"  name="dc_load_mode" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">光控延时时间</label>
                    <div class="col-sm-5">
                        <input value="<?=$info? $info['light_control_delay_time']:'0'?>"  name="light_control_delay_time" class="col-xs-12 col-sm-6" type="text">

                    </div>
                    <div class="col-sm-1">
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

                        <a class="btn btn-warning" type="button" href="javascript:void(0);" onclick="saveTemp(this)">
                            <i class="ace-icon fa fa-download bigger-110"></i> 保存模板
                        </a>

                        <a class="btn btn-info" type="button" href="javascript:history.back()">
                            <i class="ace-icon fa fa-repeat bigger-110"></i> 返回
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
<script>
    // 保存模板
    function saveTemp(self){

        $("#is_save_template").val("1");

        var select_template_id = $("#select_template_id").val();
        console.log(select_template_id);
        if(select_template_id  <= 0){

            layer.prompt({"title":'请输入模板名称'},function(val, index){
                $("#save_template_name").val(val) ;
                $("#systeamAddMenu").submit();
            });
        }else{
            $("#systeamAddMenu").submit();
        }

    }

    // 切换模板
    function selectTemplate(self){

        var template_id = parseInt($(self).val());
        if(template_id > 0 ){
            location.href = '/sunny-device/setting-battery-params?ids=<?=$ids?>&template_id='+template_id;
        }
    }
</script>

