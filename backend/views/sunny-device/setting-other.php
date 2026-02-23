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
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/sunny-device/setting-other-save" method="post">

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
                                    <option value="<?=$v['id']?>" <?php if($device_info && $device_info['template_id'] ==$v['id']){echo 'selected="selected"' ;}?> ><?=$v['name']?></option>
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

                <div class="form-group" style="display: none ;">
                    <label class="col-sm-2 control-label no-padding-right">蓄电池类型</label>
                    <div class="col-sm-5">
                        <select name="battery_type" class="input-sm  col-xs-12 col-sm-6">
                            <option value="10" <?=$device_info['battery_type']==10?'selected="selected"':''?>>铅酸电池</option>
                            <option value="11" <?=$device_info['battery_type']==11?'selected="selected"':''?>>锂电池</option>
                        </select>
                    </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">负载电流设置</label>
                    <div class="col-sm-5">
                        <input value="<?=$device_info['led_current_set']?>" placeholder="请输入0.15~10" name="led_current_set" class="col-xs-12 col-sm-6" type="text">

                    </div>
                     <div class="col-sm-4">
                         <label class="col-sm-12 form-control-static reg_tip">
                         </label>
                         <label class="col-sm-12 form-control-static reg_default">

                         </label>
                     </div>
                </div>

                <div class="form-group col-sm-4">
                    <label class="col-sm-4 control-label no-padding-right">智能功率</label>
                    <div class="col-sm-5">
                        <select name="auto_power_set" class="input-sm  col-xs-12 col-sm-6">
                            <option value="0" <?=$device_info['auto_power_set']==0?'selected="selected"':''?>>关闭</option>
                            <option value="1" <?=$device_info['auto_power_set']==1?'selected="selected"':''?>>低</option>
                            <option value="2" <?=$device_info['auto_power_set']==2?'selected="selected"':''?>>中</option>
                            <option value="3" <?=$device_info['auto_power_set']==3?'selected="selected"':''?>>高</option>
                            <option value="4" <?=$device_info['auto_power_set']==4?'selected="selected"':''?>>自动</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-11 col-xs-11 ">
                        <table id="simple-table" class="table  table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>亮灯时间段</th>
                                <th>亮灯时长</th>
                                <th>功率</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php

                            for($i=1;$i<=9;$i++){?>
                            <tr>
                                <td>第<?=$i?>时段</td>
                                <td><input value="<?=$time_list[$i-1]['minutes']?>" class ="input_seconds"name="minutes[<?=$i?>]" class="input-sm form-control" type="text"></td>
                                <td><input value="<?=$time_list[$i-1]['load_sensor_on_power']?>"  class ="input_load_sensor_on_power" name="load_sensor_on_power[<?=$i?>]" class="input-sm form-control" type="text"></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td>晨亮</td>
                                <td><input value="<?=$time_list[9]['minutes']?>" class ="input_seconds" name="minutes[10]" class="input-sm form-control" type="text"></td>
                                <td><input value="<?=$time_list[9]['load_sensor_on_power']?>" class ="input_load_sensor_on_power"name="load_sensor_on_power[10]" class="input-sm form-control"  type="text"></td>
                            </tr>
                            </tbody>
                        </table>
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
            location.href = '/sunny-device/setting-other?ids=<?=$ids?>&template_id='+template_id;
        }
    }
</script>

