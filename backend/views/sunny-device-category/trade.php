<script src="/public/js/statistics.js"></script>
<script src="/public/js/chart/chart.js"></script>
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
            <a href="<?=url('/sunny-device-category/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-device-category/trade" method="get">

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

                    <label>所属公司：</label>
                    <select name="company_id" class="input-sm  form-control" id="search_category_id">
                        <option value="">请选择</option>
                        <?php if($company_list):?>
                            <?php foreach($company_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['company_id']==$v['id']?'selected="selected"':''?>><?=$v['unique_key']?></option>
                            <?php endforeach ; ?>
                        <?php endif ;?>
                    </select>

                    <label>字段：</label>
                    <select name="filter_fields" class="input-sm  form-control" >
                        <option value="">请选择</option>
                        <?php if($filter_fields_list):?>
                            <?php foreach($filter_fields_list as $k=>$v):?>
                                <option value="<?=$k?>" <?=$searchArr&&$searchArr['filter_fields']==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach ; ?>
                        <?php endif ;?>
                    </select>

                    <label>合并时间类型：</label>
                    <select name="time_type" class="input-sm  form-control" >
                        <option value="">请选择</option>
                        <?php if($time_type_list):?>
                            <?php foreach($time_type_list as $k=>$v):?>
                                <option value="<?=$k?>" <?=$searchArr&&$searchArr['time_type']==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach ; ?>
                        <?php endif ;?>
                    </select>

                    <div class="form-group">
                        <label>选择设备：</label>
                        <a href="javascript:void(0);" onclick="select_device_list()" class="btn btn-warning btn-sm no-border"> 选择</a>
                    </div>

                    <div class="form-group">
                        <label>开始时间：</label>
                        <input name="start_time" id="start_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate: '%y-%M-%d'})" value="<?=$searchArr?$searchArr['start_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>结束时间：</label>
                        <input name="end_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate: '%y-%M-%d',minDate:'#F{$dp.$D(\'start_time\')}'})" value="<?=$searchArr?$searchArr['end_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <input type="hidden" name="select_ids_arr" value="<?=$select_ids_arr?>">

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-xs no-border" id="submitBottomSearch">
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

            <div class="widget-body">
                <div class="widget-main" style="overflow: hidden">
                    <div class="row col-sm-12" >
                        <div class="col-sm-12">
                            <canvas id="user_eth"></canvas>
                        </div>

                    </div>


                </div><!-- /.widget-main -->
            </div><!-- /.widget-body -->


            </div>
            <!-- /.row -->


        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>
<script>

    //最近七日注册统计曲线
    var eth_line_reg_user_data ={
        labels:<?=$format_line_data['labels']?>,
        datasets:<?=$format_line_data['datasets']?>
    };
    var eth_line_reg_user_options = {
        responsive: true,
        legend: {
            position:'right'
        },
        scales: {
            yAxes: [{
                display: true,
                scaleLabel: {
                    display: true,
                    labelString: '数值'
                }
            }]
        },
        title: {
            display: true,
            text: "<?=$kline_title?>",
            position:'top'
        }
    };


    setLine('user_eth',eth_line_reg_user_data,eth_line_reg_user_options);

    function select_device_list(){
        layer.open({
            btn: ['确定'],
            type: 2,
            title: '标题库',
            shadeClose: true,
            shade: 0.8,
            area: ['1200px', '70%'],
            content: '/sunny-device/trade-list',//iframe弹层的页面地址
            yes: function (index, layero) {

                    layer.close(index);
                    $("#submitBottomSearch").click();

            }
        });

    }

</script>