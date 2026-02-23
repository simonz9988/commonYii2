<script src="/public/js/layer/layer.js"></script>
<script src="https://cdn.staticfile.org/echarts/4.3.0/echarts.min.js" type="application/javascript"></script>

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
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-device/working" method="get">

                    <input type="hidden" name="id" value="<?=$id?>">
                    <div class="form-group" style="display:none;">
                        <label>类型：</label>
                        <select name="show_time" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($show_time_list as $k=>$v):?>
                                <option value="<?=$k?>" <?=$searchArr&&$searchArr['show_time']==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>时间：</label>
                        <select name="range_type" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($range_type_list as $k=>$v):?>
                                <option value="<?=$k?>" <?=$searchArr&&$searchArr['range_type']==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>开始时间：</label>
                        <input name="start_time" id="start_time" onClick="WdatePicker({startDate:'%y-%M-%D',dateFmt:'yyyy-MM-dd'})" value="<?=$searchArr?$searchArr['start_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>结束时间：</label>
                        <input name="end_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate: '%y-%M-%d',minDate:'#F{$dp.$D(\'start_time\')}'})" value="<?=$searchArr?$searchArr['end_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-xs no-border">
                            <i class="ace-icon fa fa-search bigger-120"></i>
                            <span class="bigger-120">搜索</span>
                        </button>
                    </div>

                </form>
            </div>

            <!-- /.page-search -->
        </div>
        <!-- /.row page-search -->

        <div class="clearfix"></div>
    </div>
    <!-- /.page-header -->



    <div class="row tables-wrapper">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS  -->


            <div style="width: 100%; height: 800px;">
                <div id="main" style="width: 40%;height: 400px; float: left;">
                </div>
                <div id="main1" style="width: 40%;height: 400px;float: left;">
                </div>

                <div id="main2" style="width: 40%;height: 400px;float: left;">
                </div>

                <div id="main3" style="width: 40%;height: 400px;float: left;">
                </div>
            </div>


            <!-- /.row -->

        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>
<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('main'));

    // 指定图表的配置项和数据
    var option = {
        title: {
            text: '<?=$json_title?>'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: <?=$json_field_list?>
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            //data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
            data: <?=$time_list_str?>
        },
        yAxis: {
            type: 'value'
        },
        series:
        <?=$series_arr?>
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
</script>


<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart1 = echarts.init(document.getElementById('main1'));

    // 指定图表的配置项和数据
    var option1 = {
        title: {
            text: '<?=$dianliang_json_title?>'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: <?=$dianliang_json_field_list?>
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            //data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
            data: <?=$dianliang_time_list_str?>
        },
        yAxis: {
            type: 'value'
        },
        series:
        <?=$dianliang_series_arr?>
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart1.setOption(option1);
</script>


<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart2 = echarts.init(document.getElementById('main2'));

    // 指定图表的配置项和数据
    var option2 = {
        title: {
            text: '<?=$battery_json_title?>'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: <?=$battery_json_field_list?>
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            //data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
            data: <?=$battery_time_list_str?>
        },
        yAxis: {
            type: 'value'
        },
        series:
        <?=$battery_series_arr?>
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart2.setOption(option2);
</script>

<script type="text/javascript">
    // 基于准备好的dom，初始化echarts实例
    var myChart3 = echarts.init(document.getElementById('main3'));

    // 指定图表的配置项和数据
    var option3 = {
        title: {
            text: '<?=$panel_json_title?>'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: <?=$panel_json_field_list?>
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            //data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
            data: <?=$panel_time_list_str?>
        },
        yAxis: {
            type: 'value'
        },
        series:
        <?=$panel_series_arr?>
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart3.setOption(option3);
</script>