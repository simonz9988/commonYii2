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
            <a href="javascript:void(0);" onclick="close_all_trade()" class="btn btn-danger btn-sm no-border">关闭所有交易</a>
            <a href="javascript:void(0);" class="fake-sync-button btn btn-default btn-xs" onclick="sync_all_order()" title="Sync"><i class="ace-icon fa fa-paper-plane bigger-120"></i>同步所有订单</a>

        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search" style="display: none ;">
                <form class="form-inline" id="shippingOrderSearchForm" action="/balance/list" method="get">

                    <div class="form-group">
                        <label>地址：</label>
                        <input name="card_no" value="<?=$searchArr?$searchArr['address']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>批次：</label>
                        <input name="batch_num" value="<?=$searchArr?$searchArr['batch_num']:''?>" class="input-sm  form-control" type="text">
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
                        <th>Apikey</th>
                        <th>Secret</th>
                        <th>币种</th>
                        <th>本位货币</th>
                        <th>多单买入</th>
                        <th>多单交易开启</th>
                        <th>空单买入</th>
                        <th>空单交易开启</th>
                        <th>状态</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['api_key']?></td>
                                <td><?=$v['api_secret']?></td>
                                <td><?=$v['coin']?></td>
                                <td><?=$v['base_coin']=='USDT'?'USDT':'币本位'?></td>
                                <td><?=$v['is_start_buy_up']=='Y'?'是':'<span style="color: red;">否</span>'?>
                                    <a class="btn btn-primary btn-xs" href="<?=url('/api-key/add-mark?type=up&id='.$v['id'])?>">新增</a>
                                </td>
                                <td><?=$v['is_start_trade_up']=='Y'?'是':'<span style="color: red;">否</span>'?></td>
                                <td><?=$v['is_start_buy_down']=='Y'?'是':'<span style="color: red;">否</span>'?>
                                    <a class="btn btn-primary btn-xs" href="<?=url('/api-key/add-mark?type=down&id='.$v['id'])?>">新增</a>
                                </td>
                                <td><?=$v['is_start_trade_down']=='Y'?'是':'<span style="color: red;">否</span>'?></td>
                                <td><?=$v['status']=='ENABLED'?'启用':'禁用'?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs" href="<?=url('/api-key/edit?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a href="javascript:void(0);" class="fake-sync-button btn btn-default btn-xs" onclick="sync_order(<?=$v['id']?>)" title="Sync"><i class="ace-icon fa fa-paper-plane bigger-120"></i>同步订单</a>
                                    <a href="javascript:void(0);" class="fake-sync-button btn btn-default btn-xs" onclick="sync_ledger(<?=$v['id']?>)" title="Sync"><i class="ace-icon fa fa-paper-plane bigger-120"></i>同步流水</a>
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

    // 同步所有订单
    function sync_all_order(){

        layer.msg('加载中', {
            icon: 16
            ,shade: 10
        });

        $.post(
            '/api-key/ajax-sync-all-order',
            {admin_user_id:1},function(data){
                var arr = eval('('+data+')');
                if(arr.code ==1){
                    window.location.reload();
                }
            }
        );
    }

    function sync_ledger(admin_user_id){

        layer.msg('加载中', {
            icon: 16
            ,shade: 10
        });

        $.post(
            '/api-key/ajax-sync-ledger',
            {admin_user_id:admin_user_id},function(data){
                var arr = eval('('+data+')');
                if(arr.code ==1){
                    window.location.reload();
                }
            }
        );
    }
</script>