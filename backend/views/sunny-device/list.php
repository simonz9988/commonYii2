<script src="/public/js/layer/layer.js"></script>
<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->params['selectedLevel0Name']?></a> </li>
                <li class="active"><?=$this->params['selectedLevel1Name']?></li>
            </ul>
            <!-- /.breadcrumb -->
        </div>
        <div class="col-md-4 text-right">
            <a href="javascript:void(0);"  onclick="select_fileds()" class="btn btn-warning btn-sm no-border">字段设置</a>

        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-device/index" method="get">

                    <input type="hidden" name="project_id" value="<?=$searchArr?$searchArr['project_id']:'' ?>">
                    <div class="form-group">
                        <label>分类名称：</label>
                        <select name="parent_id" class="input-sm  form-control" onchange="select_category_id(this)">
                            <option value="">请选择</option>
                            <?php if($filter_category_list):?>
                                <?php foreach($filter_category_list as $v):?>
                                    <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['parent_id']==$v['id']?'selected="selected"':''?>><?=$v['name']?></option>
                                <?php endforeach ; ?>
                            <?php endif ;?>
                        </select>

                        <select name="category_id" class="input-sm  form-control" id="search_category_id">
                            <option value="">请选择</option>
                            <?php if($filter_category_list):?>
                                <?php foreach($filter_category_list as $v):?>
                                    <?php if($v['son_list']):?>
                                        <?php foreach($v['son_list'] as $son_v):?>
                                            <option id="category-option-<?=$son_v['id']?>"class=" category-option category-option-<?=$v['id']?>" value="<?=$son_v['id']?>" <?=$searchArr&&$searchArr['category_id']==$son_v['id']?'selected="selected"':''?>><?=$son_v['name']?></option>
                                        <?php endforeach ; ?>
                                    <?php endif ;?>
                                <?php endforeach ; ?>
                            <?php endif ;?>
                        </select>
                    </div>
                    <script>
                        function select_category_id(self){
                            $(".category-option").hide();
                            $('#search_category_id option').prop("selected",'');
                            $(".category-option-"+$(self).val()).show();
                        }

                        <?php if($searchArr&&$searchArr['parent_id']):?>
                        $(function() {
                            $(".category-option").hide();
                            $(".category-option-<?=$searchArr['parent_id']?>").show();
                            <?php if($searchArr&&$searchArr['category_id']):?>
                            $("#category-option-<?=$searchArr['category_id']?>").attr("selected",true);
                            <?php endif ;?>
                        });
                        <?php else:?>
                        $(function() {
                            $(".category-option").hide();
                        });
                        <?php endif ;?>
                    </script>
                    <div class="form-group">
                        <label>经度：</label>
                        <input name="longitude" value="<?=$searchArr?$searchArr['longitude']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>纬度：</label>
                        <input name="latitude" value="<?=$searchArr?$searchArr['latitude']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>是否绑定：</label>
                        <select name="is_bind">
                            <option value="">请选择</option>

                            <option value="Y" <?=$searchArr && $searchArr['is_bind']!= '' && $searchArr['is_bind'] =='Y'?'selected="selected"':''?>>是</option>
                            <option value="N" <?=$searchArr && $searchArr['is_bind']!= '' && $searchArr['is_bind'] =='N'?'selected="selected"':''?>>否</option>

                        </select>
                    </div>

                    <div class="form-group">
                        <label>开始时间：</label>
                        <input name="start_time" id="start_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate: '%y-%M-%d'})" value="<?=$searchArr?$searchArr['start_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>结束时间：</label>
                        <input name="end_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate: '%y-%M-%d',minDate:'#F{$dp.$D(\'start_time\')}'})" value="<?=$searchArr?$searchArr['end_time']:''?>" class="input-sm  form-control" type="text">
                    </div>



                    <input type="hidden" name="is_download" value="<?=$searchArr?$searchArr['is_download']:'0'?>">

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-xs no-border" onclick="doSubmit()">
                            <i class="ace-icon fa fa-search bigger-120"></i>
                            <span class="bigger-120">搜索</span>
                        </button>

                        <button type="submit" class="btn btn-warning btn-xs no-border"onclick="doDownload()">
                            <i class="ace-icon fa fa-download bigger-120"></i>
                            <span class="bigger-120">导出</span>
                        </button>
                    </div>
                    <script>
                        function doSubmit(){
                            $("input[name=is_download]").val(0);
                        }
                        function doDownload(){
                            $("input[name=is_download]").val(1);
                        }
                    </script>

                </form>
            </div>

            <!-- /.page-search -->
        </div>
        <!-- /.row page-search -->

        <div class="clearfix"></div>
    </div>
    <!-- /.page-header -->

    <style>
        #table-1 th{
            width: 50px;
        }
        .td-info{
            display: none;
        }
    </style>

    <div class="row tables-wrapper no-tab">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS  -->
            <div class="table-responsive" style="overflow-x: scroll;">
                <table id="table-1" class="table table-striped table-bordered table-hover table-responsive" >
                    <thead>
                    <tr>

                        <th>ID</th>
                        <th>所属父级分类</th>
                        <th  class="td-info td-info-project_id">项目名称</th>
                        <th>所属分类</th>
                        <th>绑定客户</th>
                        <th>所属公司</th>
                        <th  class="td-info td-info-device_name">设备名称</th>
                        <th  class="td-info td-info-qr_code" style="width: 200px;">二维码内容</th>
                        <th  class="td-info td-info-longitude">实时经度</th>
                        <th  class="td-info td-info-latitude">实时纬度</th>
                        <th  class="td-info td-info-note">备注</th>
                        <th  class="td-info td-info-is_bind">是否绑定</th>
                        <th  class="td-info td-info-status">状态</th>
                        <th  class="td-info td-info-road_id">路段</th>
                        <th  class="td-info td-info-mark_no">路灯编号</th>
                        <th  class="td-info td-info-sim_code">SIM卡</th>
                        <th  class="td-info td-info-imei">IMEI</th>
                        <th  class="td-info td-info-iccid">ICCID</th>
                        <th  class="td-info td-info-battery_type">蓄电池类型</th>

                        <th class="td-info td-info-brightness">路灯亮度</th>
                        <th class="td-info td-info-battery_voltage">蓄电池电压(V)</th>
                        <th class="td-info td-info-battery_charging_current">蓄电池充电电流(A)</th>
                        <th class="td-info td-info-charging_current">蓄电池功率(W)</th>
                        <th class="td-info td-info-charge_status">蓄电池充电状态</th>
                        <th class="td-info td-info-battery_volume">蓄电池剩余电量</th>
                        <th class="td-info td-info-battery_temperature">蓄电池温度(℃)</th>
                        <th class="td-info td-info-battery_panel_charging_voltage">太阳能板电压(V)</th>
                        <th class="td-info td-info-battery_panel_charging_current">太阳能板电流(A)</th>
                        <th class="td-info td-info-charging_power">太阳能板功率(W)</th>
                        <th class="td-info td-info-battery_charging_current">蓄电池电流(A)</th>
                        <th class="td-info td-info-load_dc_power">路灯电压(V)</th>
                        <th class="td-info td-info-charging_current">路灯电流(A)</th>
                        <th class="td-info td-info-cumulative_charge">路灯功率(W)</th>
                        <th class="td-info td-info-light_temp">灯头温度(℃)</th>
                        <th class="td-info td-info-fault_list">故障信息</th>
                        <th class="td-info td-info-switch_status">供电状态</th>

                        <th class="td-info td-info-bat_min_volt_today">当天最低电压(V)</th>
                        <th class="td-info td-info-bat_max_volt_today">当天最高电压(V)</th>
                        <th class="td-info td-info-bat_charge_ah_today">当天充电最大电流(A)</th>
                        <th class="td-info td-info-bat_discharge_ah_today">当天放电最大电流(A)</th>
                        <th class="td-info td-info-bat_max_charge_power_today">当天充电最大功率(W)</th>
                        <th class="td-info td-info-bat_max_discharge_power_today">当天放电最大功率(W)</th>
                        <th class="td-info td-info-bat_charge_ah_today">当天充电安时数(AH)</th>
                        <th class="td-info td-info-bat_discharge_ah_today">当天放电安时数(AH)</th>
                        <th class="td-info td-info-generat_energy_today">当天发电量(Wh)</th>
                        <th class="td-info td-info-used_energy_today">当天用电量(Wh)</th>
                        <th class="td-info td-info-bat_highest_temper">当天蓄电池最高温度(℃)</th>
                        <th class="td-info td-info-bat_lowest_temper">当天蓄电池最低温度(℃)</th>
                        <th class="td-info td-info-load_total_work_time">负载总工作（累计亮灯）</th>
                        <th class="td-info td-info-led_sensor_off_time">当天亮灯时间</th>
                        <th class="td-info td-info-bat_charge_time">当天充电时间</th>
                        <th class="td-info td-info-led_light_on_index">亮灯指数</th>
                        <th class="td-info td-info-power_save_index">能耗指数</th>
                        <th class="td-info td-info-sys_health_index">健康指数</th>
                        <th class="td-info td-info-work_days_total">运行天数</th>
                        <th class="td-info td-info-bat_over_discharge_time">蓄电池总过放次数</th>
                        <th class="td-info td-info-bat_over_charge_time">蓄电池总充满次数</th>
                        <th class="td-info td-info-bat_charge_an_total">蓄电池总充电安时数(AH)</th>
                        <th class="td-info td-info-bat_discharge_an_total">蓄电池总放电安时数(AH)	</th>
                        <th class="td-info td-info-generat_energy_total">累计发电量(kWh)</th>
                        <th class="td-info td-info-used_energy_total">累计用电量(kWh)</th>
                        <th class="td-info td-info-load_total_work_time">负载总工作时间</th>

                        <th style="width: 140px;">创建时间</th>
                        <th style="width: 280px;">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['parent_id']?></td>
                                <td class="td-info td-info-project_id"><?=$v['project_id']?></td>
                                <td><?=$v['category_id']?></td>
                                <td><?=$v['customer_id']?></td>
                                <td><?=$v['company_id']?></td>
                                <td class="td-info td-info-device_name"><?=$v['device_name']?></td>
                                <td class="td-info td-info-qr_code"><?=$v['qr_code']?></td>
                                <td class="td-info td-info-longitude"><?=$v['longitude']?></td>
                                <td class="td-info td-info-latitude"><?=$v['latitude']?></td>
                                <td class="td-info td-info-note"><?=$v['note']?></td>
                                <td class="td-info td-info-is_bind"><?=$v['is_bind']=='Y'?'是':'否'?></td>
                                <td class="td-info td-info-status"><?=$v['status']=='ENABLED'?'有效':'无效'?></td>
                                <td class="td-info td-info-road_id"><?=$v['road_id']?></td>
                                <td class="td-info td-info-mark_no"><?=$v['mark_no']?></td>
                                <td class="td-info td-info-sim_code"><?=$v['sim_code']?></td>
                                <td class="td-info td-info-imei"><?=$v['imei']?></td>
                                <td class="td-info td-info-iccid"><?=$v['iccid']?></td>
                                <td class="td-info td-info-battery_type"><?=$v['battery_type']?></td>


                                <td class="td-info td-info-brightness"><?=$v['brightness']?></td>
                                <td class="td-info td-info-battery_voltage"><?=$v['status_info']?$v['status_info']['battery_voltage']:''?></td>
                                <td class="td-info td-info-battery_charging_current"><?=$v['status_info']?$v['status_info']['battery_charging_current']:''?></td>
                                <td class="td-info td-info-charging_current"><?=$v['status_info']?$v['status_info']['charging_current']*$v['status_info']['load_dc_power']:''?></td>
                                <td class="td-info td-info-charge_status"><?=$v['status_info']?$v['status_info']['charge_status']:''?></td>
                                <td class="td-info td-info-battery_volume"><?=$v['status_info']?$v['status_info']['battery_volume']:''?></td>
                                <td class="td-info td-info-battery_temperature"><?=$v['status_info']?$v['status_info']['battery_temperature']:''?></td>
                                <td class="td-info td-info-battery_panel_charging_voltage"><?=$v['status_info']?$v['status_info']['battery_panel_charging_voltage']:''?></td>
                                <td class="td-info td-info-battery_panel_charging_current"><?=$v['status_info']?$v['status_info']['battery_panel_charging_current']:''?></td>
                                <td class="td-info td-info-charging_power"><?=$v['status_info']?$v['status_info']['charging_power']:''?></td>
                                <td class="td-info td-info-battery_charging_current"><?=$v['status_info']?$v['status_info']['battery_charging_current']:''?></td>
                                <td class="td-info td-info-load_dc_power"><?=$v['status_info']?$v['status_info']['load_dc_power']:''?></td>
                                <td class="td-info td-info-charging_current"><?=$v['status_info']?$v['status_info']['charging_current']:''?></td>
                                <td class="td-info td-info-cumulative_charge"><?=$v['status_info']?$v['status_info']['cumulative_charge']:''?></td>
                                <td class="td-info td-info-light_temp">未知</td>
                                <td class="td-info td-info-fault_list"><?=$v['fault_list']?></td>
                                <td class="td-info td-info-switch_status"><?=$v['status_info']?($v['status_info']['switch_status']=="Y"?'开':'关'):''?></td>

                                <td class="td-info td-info-bat_min_volt_today"><?=$v['today_info']?$v['today_info']['bat_min_volt_today']:''?></td>
                                <td class="td-info td-info-bat_max_volt_today"><?=$v['today_info']?$v['today_info']['bat_max_volt_today']:''?></td>
                                <td class="td-info td-info-bat_charge_ah_today"><?=$v['today_info']?$v['today_info']['bat_charge_ah_today']:''?></td>
                                <td class="td-info td-info-bat_discharge_ah_today"><?=$v['today_info']?$v['today_info']['bat_discharge_ah_today']:''?></td>
                                <td class="td-info td-info-bat_max_charge_power_today"><?=$v['today_info']?$v['today_info']['bat_max_charge_power_today']:''?></td>
                                <td class="td-info td-info-bat_max_discharge_power_today"><?=$v['today_info']?$v['today_info']['bat_max_discharge_power_today']:''?></td>
                                <td class="td-info td-info-bat_charge_ah_today"><?=$v['today_info']?$v['today_info']['bat_charge_ah_today']:''?></td>
                                <td class="td-info td-info-bat_discharge_ah_today"><?=$v['today_info']?$v['today_info']['bat_discharge_ah_today']:''?></td>
                                <td class="td-info td-info-generat_energy_today"><?=$v['today_info']?$v['today_info']['generat_energy_today']:''?></td>
                                <td class="td-info td-info-used_energy_today"><?=$v['today_info']?$v['today_info']['used_energy_today']:''?></td>
                                <td class="td-info td-info-bat_highest_temper"><?=$v['today_info']?$v['today_info']['bat_highest_temper']:''?></td>
                                <td class="td-info td-info-bat_lowest_temper"><?=$v['today_info']?$v['today_info']['bat_lowest_temper']:''?></td>
                                <td class="td-info td-info-load_total_work_time"><?=$v['total_info']?$v['total_info']['load_total_work_time']:''?></td>
                                <td class="td-info td-info-led_sensor_off_time"><?=$v['today_info']?$v['today_info']['led_sensor_off_time']:''?></td>
                                <td class="td-info td-info-bat_charge_time"><?=$v['today_info']?$v['today_info']['bat_charge_time']:''?></td>
                                <td class="td-info td-info-led_light_on_index"><?=$v['today_info']?$v['today_info']['led_light_on_index']:''?></td>
                                <td class="td-info td-info-power_save_index"><?=$v['today_info']?$v['today_info']['power_save_index']:''?></td>
                                <td class="td-info td-info-sys_health_index"><?=$v['today_info']?$v['today_info']['sys_health_index']:''?></td>
                                <td class="td-info td-info-work_days_total"><?=$v['total_info']?$v['total_info']['work_days_total']:''?></td>
                                <td class="td-info td-info-bat_over_discharge_time"><?=$v['total_info']?$v['total_info']['bat_over_discharge_time']:''?></td>
                                <td class="td-info td-info-bat_over_charge_time"><?=$v['total_info']?$v['total_info']['bat_over_charge_time']:''?></td>
                                <td class="td-info td-info-bat_charge_an_total"><?=$v['total_info']?$v['total_info']['bat_charge_an_total']:''?></td>
                                <td class="td-info td-info-bat_discharge_an_total"><?=$v['total_info']?$v['total_info']['bat_discharge_an_total']:''?></td>
                                <td class="td-info td-info-generat_energy_total"><?=$v['total_info']?$v['total_info']['generat_energy_total']:''?></td>
                                <td class="td-info td-info-used_energy_total"><?=$v['total_info']?$v['total_info']['used_energy_total']:''?></td>
                                <td class="td-info td-info-load_total_work_time"><?=$v['total_info']?$v['total_info']['load_total_work_time']:''?></td>

                                <td><?=$v['create_time']?></td>
                                <td>
                                    <?php if($v['status']=='ENABLED'):?>
                                        <a class="btn btn-warning btn-xs" href="javascript:void(0);" onclick="add_mark(<?=$v['id']?>,'DISABLED')">禁用</a>
                                    <?php else :?>
                                        <a class="btn btn-success btn-xs" href="javascript:void(0);" onclick="add_mark(<?=$v['id']?>,'ENABLED')">启用</a>
                                    <?php endif ; ?>
                                    <?php if(!$v['customer_id']):?>
                                        <a class="btn btn-xs btn-danger cus-btn-del" href="javascript:void(0);" onclick="delModel('/sunny-device/del?id=<?=$v['id']?>')">
                                            <i class="ace-icon fa fa-trash-o"></i>删除
                                        </a>
                                    <?php endif ; ?>

                                    <a class="btn btn-success btn-xs" href="<?=url('/sunny-device/detail-show?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i>查看状态</a>

                                    <a class="btn btn-warning btn-xs" href="<?=url('/sunny-device/working?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i>工况</a>

                                </td>
                            </tr>
                        <?php endforeach ; ?>
                    <?php endif ;?>
                    </tbody>
                </table>


            </div>
            <!-- /.row -->

            <!-- /.page-paging 开始 分页 -->

            <div class="row page-paging">
                <!---分页start -->
                <?php echo $this->renderFile('@app/views/common/pagenation.php',array('page_data'=>$page_data))?>
            </div>
            <!-- /.page-paging 结束 -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>
<script>

    // 添加标记
    function add_mark(id,type){

        layer.confirm('确认变更状态?', function(index){

            layer.close(index);

            $.post(
                '/sunny-device/save',
                {id:id,status:type},function(data){
                    var arr = eval('('+data+')');

                    if(arr.code == 1){
                        layer.alert(arr.msg,function(){
                            window.location.reload();
                        }) ;

                    }else{
                        layer.alert(arr.msg) ;
                    }
                }
            );



        });
        return false ;
    }

    function close_all_trade(){

        $.post(
            '/all-api-key/ajax-close-all',
            {},function(data){
                var arr = eval('('+data+')');
                if(arr.code ==1){
                    window.location.reload();
                }
            }
        );
    }



    // 同步所有订单
    function sync_all_order(){

        layer.msg('加载中', {
            icon: 16
            ,shade: 10
        });

        $.post(
            '/all-api-key/ajax-sync-all-order',
            {admin_user_id:1},function(data){
                var arr = eval('('+data+')');
                if(arr.code ==1){
                    window.location.reload();
                }
            }
        );
    }

    // 设置字段
    function select_fileds(){
        layer.open({
            type: 2,
            area: ['700px', '450px'],
            fixed: false, //不固定
            maxmin: true,
            content: '/sunny-device/user-fields'
        });
    }

    $(function() {
        <?php if($fields_list):?>
        <?php foreach($fields_list as $v):?>
        $(".td-info-<?=$v?>").show();
        <?php endforeach;?>
        <?php endif ;?>
    });

</script>