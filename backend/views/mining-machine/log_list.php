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
                        <th>活动ID</th>
                        <th>活动名称</th>
                        <th>开始时间</th>
                        <th>结束时间</th>
                        <th>总算力</th>
                        <th>每天释放算力</th>
                        <th>冻结期数</th>
                        <th>每T产生的收益</th>
                        <th>操作时间</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['activity_id']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['start_time']?></td>
                                <td><?=$v['end_time']?></td>
                                <td><?=$v['total']?></td>
                                <td><?=$v['daily_add']?></td>
                                <td><?=$v['frozen']?></td>
                                <td><?=$v['unit_earn']?></td>
                                <td><?=$v['create_time']?></td>

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