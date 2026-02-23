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
    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/okex-order/list" method="get">

                    <div class="form-group">
                        <label>用户编号：</label>
                        <input name="admin_user_id" value="<?=$searchArr?$searchArr['admin_user_id']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>每页数目：</label>
                        <input name="page_num" value="<?=$searchArr?$searchArr['page_num']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>开始时间：</label>
                        <input name="start_time" value="<?=$searchArr?$searchArr['start_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>结束时间：</label>
                        <input name="end_time" value="<?=$searchArr?$searchArr['end_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>类型：</label>
                        <select name="type">
                            <option value="">请选择</option>
                            <?php foreach($total_type as $k=>$v):?>
                                <option value="<?=$k?>" <?=$searchArr && $searchArr['type'] ==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>状态：</label>
                        <select name="state">
                            <option value="">请选择</option>
                            <?php foreach($total_state as $k=>$v):?>
                                <option value="<?=$k?>" <?=$searchArr && $searchArr['state']!= '' && $searchArr['state'] ==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach; ?>
                        </select>
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
                        <th>用户编号</th>
                        <th>订单</th>
                        <th>币种</th>
                        <th>购买数量</th>
                        <th>成交均价</th>
                        <th>合约面值</th>
                        <th>盈利</th>
                        <th>手续费</th>
                        <th>订单类型</th>
                        <th>价格</th>
                        <th>购买数量</th>
                        <th>订单状态</th>
                        <th>强平价格</th>
                        <th>创建时间</th>
                        <th>用户备注</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php
                            $total_earn =  0;
                            $total_fee = 0 ;
                            ?>
                            <?php foreach($list as $v):?>
                            <?php
                            $total_earn += $v['earn_total'] ;
                            $total_fee +=$v['fee'] ;
                            ?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['admin_user_id']?></td>
                                <td><?=$v['order_id']?></td>
                                <td><?=$v['instrument_id']?></td>
                                <td><?=$v['filled_qty']?></td>
                                <td><?=$v['price_avg']?></td>
                                <td><?=$v['contract_val']?></td>
                                <td><?=$v['earn_total']?></td>
                                <td><?=$v['fee']?></td>
                                <td><?=$v['type_name']?></td>
                                <td><?=$v['price']?></td>
                                <td><?=$v['size']?></td>
                                <td><?=$v['state_name']?></td>
                                <td><?=$v['trigger_price']?></td>
                                <td><?=$v['timestamp']?></td>
                                <td><?=$v['admin_user_note']?></td>

                            </tr>

                            <?php endforeach ; ?>
                            <tr>
                                <td>总盈利</td>
                                <td><?=$total_earn?></td>
                                <td>手续费</td>
                                <td><?=$total_fee?></td>
                            </tr>
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
</script>