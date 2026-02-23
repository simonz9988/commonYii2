
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

        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/user-platform/list" method="get">
                    <input id="is-export" name="is_export" value="0"  type="hidden">
                    <div class="form-group">
                        <label>api key：</label>
                        <input name="api_key" value="<?=$searchArr?$searchArr['api_key']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-xs no-border search-btn " is-export="0">
                            <i class="ace-icon fa fa-search bigger-120"></i>
                            <span class="bigger-120">搜索</span>
                        </button>

                        <button type="button" class="btn btn-danger btn-xs no-border search-btn J_checkauth" data-auth="shop_request_trade_in_wish_list_export" is-export="1">
                            <i class="ace-icon fa fa-file bigger-120"></i>
                            <span class="bigger-120">导出</span>
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

            <div class="table-responsive">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>用户ID</th>
                        <th>所属平台</th>
                        <th>账户ID(火币)</th>
                        <th>api key</th>
                        <th>创建时间</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['user_id']?></td>
                                <td><?=$v['platform']?></td>
                                <td><?=$v['account_id']?></td>
                                <td><?=$v['api_key']?></td>
                                <td><?=$v['create_time']?></td>
                            </tr>
                            <?php endforeach ; ?>
                        <?php endif ;?>
                    </tbody>
                </table>
                <script>

                    function confirmCashIn(id) {
                        var msg = "请确认！";
                        if (confirm(msg)==true){
                            $.post(
                                '/cash/confirm-in',
                                {id: id},
                                function (data) {
                                    var arr = eval('(' + data + ')');
                                    if (arr.code == 1) {
                                        window.location.reload();
                                    } else {
                                        alert('确认失败');
                                    }
                                }
                            );
                        }

                    }

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
