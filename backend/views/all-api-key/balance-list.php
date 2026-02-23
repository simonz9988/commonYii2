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
        <div class="col-md-4 text-right">

        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search" style="display: block ;">
                <form class="form-inline" id="shippingOrderSearchForm" action="/balance/list" method="get">

                    <div class="form-group">
                        <label style="color:red;font-size: 16px;">EOS当前市价：<?=$mark_price['eos']?></label>

                    </div>
                    <div></div>
                    <div class="form-group">
                        <label style="color:orange;font-size: 16px;">ETH当前市价：<?=$mark_price['eth']?></label>

                    </div>
                    <div></div>
                    <div class="form-group">
                        <label style="color:blue;font-size: 16px;">BTC当前市价：<?=$mark_price['btc']?></label>

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
                        <th>备注</th>
                        <th>账户权益</th>
                        <th>已实现盈亏</th>
                        <th>未实现盈亏</th>
                        <th>预估强平价</th>
                        <th>强平价格差比</th>
                        <th>空单交易开启</th>
                        <th>持仓空单数量</th>
                        <th>持仓空单可平数量</th>
                        <th>空单买入均价</th>
                        <th>多单交易开启</th>
                        <th>持仓多单数量</th>
                        <th>持仓多单可平数量</th>
                        <th>多单买入均价</th>
                        <th>基础数量</th>
                        <th>补仓点</th>
                        <th>盈利点</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['note']?></td>
                                <td><?=$v['account_info']?$v['account_info']['equity']:'' ?></td>
                                <td><?=$v['account_info']?$v['account_info']['realized_pnl']:'' ?></td>
                                <td><?=$v['account_info']?$v['account_info']['unrealized_pnl']:'' ?></td>
                                <td><?=$v['order_info']['down_order']?$v['order_info']['down_order']['liquidation_price']:'' ?></td>
                                <td  <?php if($v['distance_percent'] <=20 && $v['distance_percent'] >0):?>style="color:red;"<?php endif ;?>><?=$v['distance_percent']?>%</td>
                                <td><?=$v['is_start_trade_down']=='Y'?'是':'<span style="color: red;">否</span>'?></td>
                                <td><?=$v['order_info']['down_order']?$v['order_info']['down_order']['position']:'' ?>
                                    <?php if(checkAdminPrivilege('/all-api-key/ajax-fix-order')):?>
                                    <a class="btn btn-primary btn-xs" href="javascript:void(0);" onclick="fix_order(<?=$v['id']?>,'down')">修正</a>
                                    <?php endif ;?>
                                </td>
                                <td <?php if(isset($v['order_info']['down_order']['avail_position'])&& $v['order_info']['down_order']['avail_position'] >0):?>style="color: orange" <?php endif?>><?=$v['order_info']['down_order']?$v['order_info']['down_order']['avail_position']:'' ?></td>
                                <td><?=$v['order_info']['down_order']?$v['order_info']['down_order']['avg_cost']:'' ?></td>
                                <td><?=$v['is_start_trade_up']=='Y'?'是':'<span style="color: red;">否</span>'?></td>
                                <td><?=$v['order_info']['up_order']?$v['order_info']['up_order']['position']:'' ?>
                                    <?php if(checkAdminPrivilege('/all-api-key/ajax-fix-order')):?>
                                    <a class="btn btn-primary btn-xs" href="javascript:void(0);" onclick="fix_order(<?=$v['id']?>,'up')">修正</a>
                                    <?php endif ;?>
                                </td>
                                <td  <?php if(isset($v['order_info']['up_order']['avail_position'])&& $v['order_info']['up_order']['avail_position'] >0):?>style="color: orange" <?php endif?>><?=$v['order_info']['up_order']?$v['order_info']['up_order']['avail_position']:'' ?></td>
                                <td><?=$v['order_info']['up_order']?$v['order_info']['up_order']['avg_cost']:'' ?></td>
                                <td><?=$v['base_buy_num']?></td>
                                <td><?=$v['add_distance']?></td>
                                <td><?=$v['earn_percent']?></td>
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

    function fix_order(id,type){

        layer.confirm('确认修正?', function(index){

            layer.close(index);

                $.post(
                    '/all-api-key/ajax-fix-order',
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
</script>