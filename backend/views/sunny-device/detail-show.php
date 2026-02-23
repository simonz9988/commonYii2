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
        <div class="col-md-4 text-right" >
            <a href="javascript:void(0);" onclick="window.location.reload();" class="btn btn-success btn-sm no-border"><i class="ace-icon glyphicon glyphicon-refresh"></i> 刷新</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="row tables-wrapper">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS  -->
            <div style="width: 100%; height: 40px;">
                <div id="main" style="width: 40%;height: 300px; float: left;">
                    <table class="table table-striped table-bordered table-hover">
                        <tbody>
                            <tr>
                                <td>蓄电池类型</td>
                                <td><?=$battery_type?></td>

                            </tr>
                            <tr>
                                <td>系统电压</td>
                                <td><?=$battery_rate_volt?></td>
                            </tr>
                            <tr>
                                <td>负载电流设置</td>
                                <td><?=$led_current_set?></td>
                            </tr>
                            <tr>
                                <td>智能功率</td>
                                <td><?=$auto_power_set?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="main1" style="width: 40%;height: 300px;float: left;margin-left: 10px;margin-bottom: 10px;">
                    <iframe style="width: 100%;height: 100%;"  frameborder="no" border="0" marginwidth="0" marginheight="0" scrolling="no" src="/sunny-device/empty-frame?device_id=<?=$device_info['id']?>"></iframe>
                </div>
            </div>

            <div class="table-responsive">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th colspan="14" style="text-align: center;color: blue;">实时数据</th>
                    </tr>

                    <tr>
                        <th>负载状态</th>
                        <th>亮度%</th>
                        <th>电池电量</th>
                        <th>电池电压</th>
                        <th>电池充电电流</th>
                        <th>环境温度</th>
                        <th>蓄电池温度</th>
                        <th>直流负载电压</th>
                        <th>直流负载电流</th>
                        <th>直流负载功率</th>
                        <th>太阳能板电压</th>
                        <th>太阳能板电流</th>
                        <th>太阳能充电功率</th>
                        <th style="width: 140px;">创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($status_record_list):?>
                        <?php foreach($status_record_list as $v):?>
                            <tr>

                                <td><?=$v['load_status']?></td>
                                <td><?=$v['brightness']?></td>
                                <td><?=$v['battery_volume']?></td>
                                <td><?=$v['battery_voltage']?></td>
                                <td><?=$v['battery_charging_current']?></td>
                                <td><?=$v['ambient_temperature']?></td>
                                <td><?=$v['battery_temperature']?></td>
                                <td><?=$v['load_dc_power']?></td>
                                <td><?=$v['charging_current']?></td>
                                <td><?=$v['cumulative_charge']?></td>
                                <td><?=$v['battery_panel_charging_voltage']?></td>
                                <td><?=$v['battery_panel_charging_current']?></td>
                                <td><?=$v['charging_power']?></td>
                                <td><?=$v['create_time']?></td>

                            </tr>
                        <?php endforeach ; ?>
                    <?php endif ;?>
                    </tbody>
                </table>


            </div>
            <!-- /.row -->

        </div>
        <!-- /.col -->
        <div class="col-xs-12" style="margin-top: 10px;">
            <div class="table-responsive">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th colspan="19" style="text-align: center;color: blue;">当日统计</th>
                    </tr>

                    <tr>
                        <th>蓄电池当天最低电</th>
                        <th>蓄电池当天最高电</th>
                        <th>当天放电最大电流</th>
                        <th>当天充电最大功率</th>
                        <th>当天放电最大功率</th>
                        <th>当天充电安时数</th>
                        <th>当天放电安时数</th>
                        <th>当天发电量</th>
                        <th>当天用电量</th>
                        <th>当天电池最高温度</th>
                        <th>当天电池最低温度</th>
                        <th>当天亮灯时间 （有人)</th>
                        <th>当天亮灯时间 （无人）</th>
                        <th>亮灯指数</th>
                        <th>能耗指数</th>
                        <th>健康指数</th>
                        <th>当天充电时间</th>
                        <th>夜晚长度</th>
                        <th style="width: 140px;">创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($today_list):?>
                        <?php foreach($today_list as $v):?>
                            <tr>

                                <td><?=$v['bat_min_volt_today']?></td>
                                <td><?=$v['bat_max_volt_today']?></td>
                                <td><?=$v['bat_max_chg_current_today']?></td>
                                <td><?=$v['bat_max_charge_power_today']?></td>
                                <td><?=$v['bat_max_discharge_power_today']?></td>
                                <td><?=$v['bat_charge_ah_today']?></td>
                                <td><?=$v['bat_discharge_ah_today']?></td>
                                <td><?=$v['generat_energy_today']?></td>
                                <td><?=$v['used_energy_today']?></td>
                                <td><?=$v['bat_highest_temper']?></td>
                                <td><?=$v['bat_lowest_temper']?></td>
                                <td><?=$v['led_sensor_on_time']?></td>
                                <td><?=$v['led_sensor_off_time']?></td>
                                <td><?=$v['led_light_on_index']?></td>
                                <td><?=$v['power_save_index']?></td>
                                <td><?=$v['sys_health_index']?></td>
                                <td><?=$v['bat_charge_time']?></td>
                                <td><?=$v['night_length']?></td>
                                <td><?=$v['create_time']?></td>

                            </tr>
                        <?php endforeach ; ?>
                    <?php endif ;?>
                    </tbody>
                </table>


            </div>
        </div>
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

</script>


