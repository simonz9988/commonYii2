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

        <div class="col-md-4 text-right" style="display:none ;" >
            <?php if(checkAdminPrivilege('/member/edit1')):?>
            <a href="<?=url('/member/edit1')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
            <?php endif ;?>
        </div>
    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/trade-order/list" method="get">

                    <div class="form-group">
                        <label>商品名称：</label>
                        <input name="productname" value="<?=$searchArr?$searchArr['productname']:''?>" class="input-sm  form-control" type="text">
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
                        <th>用户名</th>
                        <th>手机号</th>
                        <th>邮箱</th>
                        <th>商品名称</th>
                        <th>商品附加信息</th>
                        <th>购买点数量</th>
                        <th>付款时间</th>
                        <th>下单时间</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['username']?></td>
                                <td><?=$v['mobile']?></td>
                                <td><?=$v['email']?></td>
                                <td><?=$v['productname']?></td>
                                <td><?=$v['productinfo']?></td>
                                <td><?=$v['umoney']?></td>
                                <td><?=$v['paytime']?></td>
                                <td><?=$v['createtime']?></td>
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
    function close_all_trade(){

         $.post(
            '/api-key/ajax-close-all',
            {},function(data){
               var arr = eval('('+data+')');
               if(arr.code ==1){
                   window.location.reload();
               }
            }
        );
    }

    function add_balance(user_id){
        layer.prompt(function(val, index){
            $.post(
                '/member/add-balance',
                {user_id:user_id,total:val},function(data){
                    var arr = eval('('+data+')');
                    if(arr.code ==1){
                        window.location.reload();
                    }else{
                        alert(arr.msg);
                    }
                }
            );
        });
    }

    function set_user_type(user_id,is_special){


        var r=confirm("确认设置?");
        if (r==true)
        {
            $.post(
                '/member/save-special',
                {user_id:user_id,is_special:is_special},function(data){
                    var arr = eval('('+data+')');
                    if(arr.code ==1){
                        window.location.reload();
                    }
                }
            );
        }
        else
        {

        }

    }

    function sync_order(admin_user_id){

        layer.msg('加载中', {
            icon: 16
            ,shade: 10
        });

        $.post(
            '/api-key/ajax-sync-order',
            {admin_user_id:admin_user_id},function(data){
                var arr = eval('('+data+')');
                if(arr.code ==1){
                    window.location.reload();
                }
            }
        );
    }
</script>