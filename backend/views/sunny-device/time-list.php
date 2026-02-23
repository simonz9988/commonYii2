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
        <div class="col-md-4 text-right" style="display: none ;">
            <a href="<?=url('/sunny-company/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">

            </div>

            <!-- /.page-search -->
        </div>
        <!-- /.row page-search -->

        <div class="clearfix"></div>
    </div>
    <!-- /.page-header -->



    <div class="row tables-wrapper">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/sunny-device/setting-other-save" method="post">

            <!-- PAGE CONTENT BEGINS  -->
            <div class="form-group">
                <label class="col-sm-2 control-label no-padding-right">蓄电池类型</label>
                <div class="col-sm-5">
                    <input readonly="true" value="<?=$battery_type?>" name="led_current_set" class="col-xs-12 col-sm-6" type="text">

                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label no-padding-right">系统电压</label>
                <div class="col-sm-5">
                    <input readonly="true"  value="<?=$battery_rate_volt?>"  name="led_current_set" class="col-xs-12 col-sm-6" type="text">
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label no-padding-right">负载电流设置</label>
                <div class="col-sm-5">
                    <input  readonly="true"  value="<?=$led_current_set?>" name="led_current_set" class="col-xs-12 col-sm-6" type="text">

                </div>

            </div>

            <div class="form-group">
                <label class="col-sm-2 control-label no-padding-right">智能功率</label>
                <div class="col-sm-5">
                    <input readonly="true"  value="<?=$auto_power_set?>" name="led_current_set" class="col-xs-12 col-sm-6" type="text">
                </div>
            </div>

            <div class="form-group row">
                <div class="table-responsive">

                    <table id="table-1" class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>所属时段</th>
                            <th>亮灯时间(分钟)</th>
                            <th>有人功率</th>
                            <th>无人功率</th>

                        </tr>
                        </thead>
                        <tbody>
                            <?php if($list):?>
                                <?php foreach($list as $v):?>
                                <tr>
                                    <?php if($v['time_end'] ==10):?>
                                        <td>晨光时段</td>
                                    <?php else:?>
                                        <td>第<?=$v['time_end']?>时段</td>
                                    <?php endif;?>
                                    <td><?=$v['minutes']?></td>
                                    <td><?=$v['load_sensor_on_power']?></td>
                                    <td><?=$v['load_sensor_off_power']?></td>
                                </tr>
                                <?php endforeach ; ?>
                            <?php endif ;?>
                        </tbody>
                    </table>


                </div>
            </div>
            <!-- /.row -->

            <!-- /.page-paging 开始 分页 -->

            <div class="row page-paging">
                <!---分页start -->
                <?php echo $this->renderFile('@app/views/common/pagenation.php',array('page_data'=>$page_data))?>
            </div>
            <!-- /.page-paging 结束 -->
        </div>
    </form>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>
<script>

    // 添加标记
    function add_mark(type,id){

        layer.confirm('确认新增标记?', function(index){

            layer.close(index);

            $.post(
                '/all-api-key/ajax-add-mark',
                {id:id,type:type},function(data){
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