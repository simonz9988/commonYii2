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
            <a href="<?=url('/mining-machine/activity-edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/mining-machine/activity-list" method="get">

                    <div class="form-group">
                        <label>活动名称：</label>
                        <input name="name" value="<?=$searchArr?$searchArr['name']:''?>" class="input-sm  form-control" type="text">
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
            <div class="col-md-4 text-left">

            </div>

            <div class="table-responsive">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>名称</th>
                        <th>开始时间</th>
                        <th>结束时间</th>
                        <th>总算力</th>
                        <th>剩余总算力</th>
                        <th>有效总算力比例</th>
                        <th>累计有效算力</th>
                        <th>冻结期数</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['start_time']?></td>
                                <td><?=$v['end_time']?></td>
                                <td><?=$v['total']?></td>
                                <td><?=$v['left_total']?></td>
                                <td><?=$v['useful_percent']?>%</td>
                                <td><?=$v['total_supply']?></td>
                                <td><?=$v['frozen']?></td>
                                <td><?=$v['status']=='ENABLED'?'是':'否'?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs" href="<?=url('/mining-machine/activity-edit?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-xs btn-danger cus-btn-del" href="javascript:void(0);" onclick="delModel('/mining-machine/activity-del?id=<?=$v['id']?>')">
                                        <i class="ace-icon fa fa-trash-o"></i>删除
                                    </a>
                                    <a class="btn btn-xs btn-warning " href="/mining-machine/log-list?id=<?=$v['id']?>">
                                        <i class="ace-icon fa fa-eye-o"></i>查看日志
                                    </a>

                                    <a class="btn btn-xs btn-success " href="javascript:void(0);" onclick="send_eran(<?=$v['id']?>)">
                                        <i class="ace-icon fa fa-eye-o"></i>发放收益
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach ; ?>
                        <?php endif ;?>
                    </tbody>
                </table>
                <script>

                </script>

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

<!-- 弹出窗口添加会员start -->
<div class="modal fade" id="select-log-panel" tabindex="-1" role="select-log-panel" aria-hidden="true" style="display: none;height: 400px;">
    <input type="hidden" id="select_activity_id" value="0">
    <div class="modal-dialog modal-lg">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                    <h4 class="modal-title" id="myModalLabel">
                        设置发放参数
                    </h4>
                </div>
                <div class="modal-body ">
                    <label>有效算力增量:</label><input id="input_daily_add" placeholder="" value="">
                </div>
                <div class="modal-body ">
                    <label>单位算力收益:</label><input id="input_unit_data" placeholder=" " value="">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭
                    </button>
                    <button type="button" class="btn btn-primary saveId" onclick="do_send_earn()">
                        确认发放
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- 弹出窗口添加商品end -->
<script>
    $(function () {
        memberDialog = $("#select-log-panel").modal({
            keyboard : false,
            show : false
        });

    });

    function send_eran(activity_id){

        // 有效算力增量
        // 单位算力
        $("#select_activity_id").val(activity_id) ;
        memberDialog.modal('show');
        return false ;

    }

    function do_send_earn(){


        layer.msg('处理中', {
            icon: 16
            ,shade: 0.1
        });

        var activity_id = $("#select_activity_id").val();
        var unit_data =  $("#input_unit_data").val();
        var daily_add =  $("#input_daily_add").val();
        $.post(
            '/mining-machine/do-send-earn',
            {activity_id:activity_id,unit_data:unit_data,daily_add:daily_add},
            function(data){
                var arr = eval('('+data+')');
                if(arr.code ==1){
                    layer.alert("处理成功",function () {
                        window.location.reload();
                    });
                }else{
                    layer.alert(arr.msg);
                }
            }
        );


    }

</script>
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