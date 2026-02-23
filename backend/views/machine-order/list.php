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
        <div class="col-md-4 text-right" style="display: none ;" >
            <a href="<?=url('/ad/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/machine-order/list" method="get">

                    <div class="form-group">
                        <label>手机号码：</label>
                        <input name="mobile" value="<?=$searchArr?$searchArr['mobile']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>所有机器：</label>
                        <select name="machine_id">
                            <option value="">请选择</option>
                            <?php foreach($machine_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr && $searchArr['machine_id']!= '' && $searchArr['machine_id'] ==$v['id']?'selected="selected"':''?>><?=$v['title']?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>状态：</label>
                        <select name="status">
                            <option value="">请选择</option>
                            <?php foreach($status_list as $k=>$v):?>
                                <option value="<?=$k?>" <?=$searchArr && $searchArr['status']!= '' && $searchArr['status'] ==$k?'selected="selected"':''?>><?=$v?></option>
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
                        <th>订单号</th>
                        <th>用户ID</th>
                        <th>手机号码</th>
                        <th>矿机名称</th>
                        <th>购买数量</th>
                        <th>单价</th>
                        <th>手续费</th>
                        <th>机器总费用</th>
                        <th>订单总金额</th>
                        <th>支付时间</th>
                        <th>状态</th>
                        <th>下单时间</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['order_no']?></td>
                                <td><?=$v['user_id']?></td>
                                <td><?=$v['mobile']?></td>
                                <td><?=$v['machine_name']?></td>
                                <td><?=$v['num']?></td>
                                <td><?=$v['price']?></td>
                                <td><?=$v['fee']?></td>
                                <td><?=$v['machine_amount']?></td>
                                <td><?=$v['order_amount']?></td>
                                <td><?=$v['pay_time']?></td>
                                <td><?=$v['status_text']?></td>
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