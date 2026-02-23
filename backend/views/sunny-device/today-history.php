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
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-device/today-history" method="get">

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
                        <label>设备编号：</label>
                        <input name="qr_code" id="qr_code" value="<?=$searchArr?$searchArr['qr_code']:''?>" class="input-sm  form-control" type="text">
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
            width: 100px;
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
                        <th  class="td-info td-info-device_name">设备名称</th>
                        <th  class="td-info td-info-qr_code" style="width: 200px;">PN</th>
                        <th>所属分类</th>
                        <th>绑定客户</th>
                        <th>所属公司</th>
                        <th>日期</th>

                        <th class="td-info td-info-bat_min_volt_today">蓄电池当天最低电压</th>
                        <th class="td-info td-info-bat_max_volt_today">蓄电池当天最高电压</th>
                        <th class="td-info td-info-bat_max_chg_current_today">当天充电最大电流</th>
                        <th class="td-info td-info-bat_max_discharge_current_today">当天放电最大电流</th>
                        <th class="td-info td-info-bat_max_charge_power_today">当天充电最大功率</th>
                        <th class="td-info td-info-bat_max_discharge_power_today">当天放电最大功率</th>
                        <th class="td-info td-info-bat_charge_ah_today">当天充电安时数</th>
                        <th class="td-info td-info-bat_discharge_ah_today">当天放电安时数</th>
                        <th class="td-info td-info-generat_energy_today">当天发电量</th>
                        <th class="td-info td-info-used_energy_today">当天用电量</th>
                        <th class="td-info td-info-bat_highest_temper">当天电池最高温度</th>
                        <th class="td-info td-info-bat_lowest_temper">当天电池最低温度</th>
                        <th class="td-info td-info-led_sensor_on_time">当天亮灯时间 （有人</th>
                        <th class="td-info td-info-led_sensor_off_time">当天亮灯时间 （无人）</th>
                        <th class="td-info td-info-led_light_on_index">亮灯指数</th>
                        <th class="td-info td-info-power_save_index">能耗指数</th>
                        <th class="td-info td-info-sys_health_index">健康指数</th>
                        <th class="td-info td-info-bat_charge_time">当天充电时间</th>
                        <th class="td-info td-info-night_length">夜晚长度</th>

                        <th style="width: 140px;">最后同步时间</th>

                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['parent_id']?></td>
                                <td class="td-info td-info-project_id"><?=$v['project_id']?></td>
                                <td class="td-info td-info-device_name"><?=$v['device_info']['device_name']?></td>
                                <td class="td-info td-info-qr_code"><?=$v['device_info']['qr_code']?></td>
                                <td><?=$v['category_id']?></td>
                                <td><?=$v['customer_id']?></td>
                                <td><?=$v['company_id']?></td>
                                <td><?=$v['date']?></td>

                                <td class="td-info td-info-bat_min_volt_today"><?=$v['bat_min_volt_today']?></td>
                                <td class="td-info td-info-bat_max_volt_today"><?=$v['bat_max_volt_today']?></td>
                                <td class="td-info td-info-bat_max_chg_current_today"><?=$v['bat_max_chg_current_today']?></td>
                                <td class="td-info td-info-bat_max_discharge_current_today"><?=$v['bat_max_discharge_current_today']?></td>
                                <td class="td-info td-info-bat_max_charge_power_today"><?=$v['bat_max_charge_power_today']?></td>
                                <td class="td-info td-info-bat_max_discharge_power_today"><?=$v['bat_max_discharge_power_today']?></td>
                                <td class="td-info td-info-bat_charge_ah_today"><?=$v['bat_charge_ah_today']?></td>
                                <td class="td-info td-info-bat_discharge_ah_today"><?=$v['bat_discharge_ah_today']?></td>
                                <td class="td-info td-info-generat_energy_today"><?=$v['generat_energy_today']?></td>
                                <td class="td-info td-info-used_energy_today"><?=$v['used_energy_today']?></td>
                                <td class="td-info td-info-bat_highest_temper"><?=$v['bat_highest_temper']?></td>
                                <td class="td-info td-info-bat_lowest_temper"><?=$v['bat_lowest_temper']?></td>
                                <td class="td-info td-info-led_sensor_on_time"><?=$v['led_sensor_on_time']?></td>
                                <td class="td-info td-info-led_sensor_off_time"><?=$v['led_sensor_off_time']?></td>
                                <td class="td-info td-info-led_light_on_index"><?=$v['led_light_on_index']?></td>
                                <td class="td-info td-info-power_save_index"><?=$v['power_save_index']?></td>
                                <td class="td-info td-info-sys_health_index"><?=$v['sys_health_index']?></td>
                                <td class="td-info td-info-bat_charge_time"><?=$v['bat_charge_time']?></td>
                                <td class="td-info td-info-night_length"><?=$v['night_length']?></td>

                                <td><?=$v['modify_time']?></td>

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
            content: '/sunny-device/user-fields-history'
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