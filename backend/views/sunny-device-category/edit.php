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
                <h1>编辑分类信息
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/sunny-device-category/save" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">所属父级</label>

                    <div class="col-sm-5">
                        <select name="parent_id" class="input-sm  col-xs-12 col-sm-6">
                            <option value="0">请选择</option>
                            <?php if($parent_list):?>
                                <?php foreach($parent_list as $v){?>
                                    <option value="<?=$v['id']?>" <?php if($info && $info['parent_id'] ==$v['id']){echo 'selected="selected"' ;}?> ><?=$v['name']?></option>
                                <?php } ?>
                            <?php endif;?>
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

                <div class="form-group" style="display: none;">
                    <label class="col-sm-2 control-label no-padding-right">充电口数量</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['charge_port_num']:'1'?>"name="charge_port_num" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none ;">
                    <label class="col-sm-2 control-label no-padding-right">路灯亮度</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['light_level']:'1'?>"name="light_level" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>



                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">唯一编码</label>

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

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">蓄电池类型</label>

                    <div class="col-sm-5">
                        <select name="battery_type" class="input-sm  col-xs-12 col-sm-6">
                            <?php if($battery_type_list):?>
                                <?php foreach($battery_type_list as $k=>$v){?>
                                    <option value="<?=$k?>" <?php if($info && $info['battery_type'] ==$k){echo 'selected="selected"' ;}?> ><?=$v?></option>
                                <?php } ?>
                            <?php endif;?>
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
                    <label class="col-sm-2 control-label no-padding-right">系统电压</label>

                    <div class="col-sm-5">
                        <select name="battery_rate_volt" class="input-sm  col-xs-12 col-sm-6">
                            <?php if($bat_rate_volt_list):?>
                                <?php foreach($bat_rate_volt_list as $k=>$v){?>
                                    <option value="<?=$v?>" <?php if($info && $info['battery_rate_volt'] ==$v){echo 'selected="selected"' ;}?> ><?=$v?>V</option>
                                <?php } ?>
                            <?php endif;?>
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
                    <label class="col-sm-2 control-label no-padding-right">负载电流(A)</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['led_current_set']:'0.9'?>"name="led_current_set" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">智能功率</label>

                    <div class="col-sm-5">
                        <select name="auto_power_set" class="input-sm  col-xs-12 col-sm-6">
                            <?php if($auto_power_set_list):?>
                                <?php foreach($auto_power_set_list as $k=>$v){?>
                                    <option value="<?=$k?>" <?php if($info && $info['auto_power_set'] ==$k){echo 'selected="selected"' ;}?> ><?=$v?></option>
                                <?php } ?>
                            <?php endif;?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <?php if($language_item_list):?>
                    <?php foreach($language_item_list as $v):?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label no-padding-right">展示分类名称(<?=$v['language_name']?>)</label>

                            <div class="col-sm-5">
                                <input value="<?=$v['show_name']?>"name="show_name_list[<?=$v['language_id']?>]>" class="col-xs-12 col-sm-6" type="text">
                            </div>
                            <div class="col-sm-4">
                                <label class="col-sm-12 form-control-static reg_tip">
                                </label>
                                <label class="col-sm-12 form-control-static reg_default">

                                </label>
                            </div>
                        </div>

                    <?php endforeach ;?>
                <?php endif ; ?>

                <?php if($language_item_list):?>
                    <?php foreach($language_item_list as $v):?>
                        <div class="form-group" style="display: none;">
                            <label class="col-sm-2 control-label no-padding-right">SOP(<?=$v['language_name']?>)</label>

                            <div class="col-sm-5">
                                <input id="input_upload_file<?=$v['language_id']?>"  value="<?=$v['sop_url']?>"name="language_item_list[<?=$v['language_id']?>][sop_url]" class="col-xs-12 col-sm-6" type="text">
                                <input type="hidden"id="name_input_upload_file<?=$v['language_id']?>"  value="<?=$v['file_name']?>"name="language_item_list[<?=$v['language_id']?>][file_name]" class="col-xs-12 col-sm-6" type="text">

                                <button type="button" class="btn btn-purple btn-sm inline upload-image" id="upload_file<?=$v['language_id']?>" >
                                    <i class="ace-icon fa fa-upload bigger-110"></i>上传文件
                                </button>
                            </div>
                            <div class="col-sm-4">
                                <label class="col-sm-12 form-control-static reg_tip">
                                </label>
                                <label class="col-sm-12 form-control-static reg_default">

                                </label>
                            </div>
                        </div>
                        <script>
                            $(function() {
                                init_source_uploader('upload_file<?=$v['language_id']?>','user_wallet')
                            })
                        </script>


                    <?php endforeach ;?>
                <?php endif ; ?>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">控制器型号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['controller_model']:''?>"name="controller_model" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">蓄电池容量</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['battery_vol']:''?>"name="battery_vol" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">太阳能电板功率</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['battery_model']:''?>"name="battery_model" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">太阳能板型号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['panel_model']:''?>"name="panel_model" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">灯具功率</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['light_power']:''?>"name="light_power" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">灯具型号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['light_model']:''?>"name="light_model" class="col-xs-12 col-sm-6" type="text">
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

