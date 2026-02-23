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
        <div class="col-md-4 text-right" style="display: none;">
            <a href="/coin-user/edit" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>
    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/coin-user/charge-list" method="get">

                    <div class="form-group">
                        <label>hash：</label>
                        <input name="hash" value="<?=$searchArr?$searchArr['hash']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>开始时间：</label>
                        <input name="start_time" onclick="WdatePicker({dateFmt: 'yyyy-MM-dd HH:mm:ss', startDate: '%y-%M-%d 00:00:00', alwaysUseStartDate: true})" value="<?=$searchArr?$searchArr['start_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>结束时间：</label>
                        <input name="end_time" onclick="WdatePicker({dateFmt: 'yyyy-MM-dd HH:mm:ss', startDate: '%y-%M-%d 23:59:59', alwaysUseStartDate: true})" value="<?=$searchArr?$searchArr['end_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-xs no-border">
                            <i class="ace-icon fa fa-search bigger-120"></i>
                            <span class="bigger-120">搜索</span>
                        </button>
                    </div>

                </form>
            </div>
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
                        <th>用户ID</th>
                        <th>hash</th>
                        <th>类型</th>
                        <th>数量</th>
                        <th>创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['user_id']?></td>
                                <td><?=$v['hash']?></td>
                                <td><?=$v['type'] =='IN' ? '入金':'出金'?></td>
                                <td><?=$v['point']?></td>
                                <td><?=date('Y-m-d H:i:s',$v['timestamp'])?></td>

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