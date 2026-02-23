
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
                <form class="form-inline" id="shippingOrderSearchForm" action="/cash/in" method="get">
                    <input id="is-export" name="is_export" value="0"  type="hidden">
                    <div class="form-group">
                        <label>交易单号：</label>
                        <input name="order_no" value="<?=$searchArr?$searchArr['order_no']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>支付时间：</label>
                        <input name="pay_start_time" onclick="WdatePicker({startDate:'%y-%M-%D 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true})" value="<?=$searchArr?$searchArr['pay_start_time']:''?>" class="input-sm  form-control" type="text">-
                        <input name="pay_end_time"onclick="WdatePicker({startDate:'%y-%M-%D 00:00:00',dateFmt:'yyyy-MM-dd HH:mm:ss',alwaysUseStartDate:true})" value="<?=$searchArr?$searchArr['pay_end_time']:''?>" class="input-sm  form-control" type="text">
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
                        <th>交易单号</th>
                        <th>名称</th>
                        <th>创建时间</th>
                        <th>支付时间</th>
                        <th>支付人姓名</th>
                        <th>交易金额</th>
                        <th>币种</th>
                        <th>支付方式</th>
                        <th>支付状态</th>
                        <th>是否确认</th>
                        <th>用户端</th>
                        <th>备注</th>
                        <th>手续费</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['order_no']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['create_time']?></td>
                                <td><?=$v['pay_time']?></td>
                                <td><?=$v['pay_name']?></td>
                                <td><?=$v['amount']?></td>
                                <td><?=$v['coin_type']?></td>
                                <td><?=$v['pay_type']?></td>
                                <td><?=$v['pay_status']?></td>
                                <td <?php if($v['is_confirm']=='N'):?>style="color: red;" <?php endif ;?>><?=$v['is_confirm']=='Y'?'是':"否"?></td>
                                <td><?=$v['source']?></td>
                                <td><?=$v['note']?></td>
                                <td><?=$v['fee']?></td>
                                <td>
                                    <?php if(checkAdminPrivilege('/cash/confirm-in') ):?>
                                    <a class="btn btn-primary btn-xs" href="javascript:void(0);" onclick="confirmCashIn(<?=$v['id']?>)"> <i class="ace-icon fa  bigger-100"></i> 确认</a>
                                    <?php endif ;?>
                                </td>
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

<script>
    var cash_in_min_amount = <?=$cash_in_min_amount?>;
    $('.search-btn').click(function(){

        var form_demo = $('#shippingOrderSearchForm');
        var is_export = $(this).attr('is-export');
        $("#is-export").val(is_export);
        form_demo.submit();

    })
</script>