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
        <div class="col-md-4 text-right" style="display: none ;">
            <a href="javascript:void(0);" onclick="close_all_trade()" class="btn btn-danger btn-sm no-border">关闭所有交易</a>
            <a href="javascript:void(0);" class="fake-sync-button btn btn-default btn-xs" onclick="sync_all_order()" title="Sync"><i class="ace-icon fa fa-paper-plane bigger-120"></i>同步所有订单</a>
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
                        <th>备注</th>
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
                                <td><?=$v['note']?></td>
                                <td><?=formatHiddenString($v['api_key'],4,22)?></td>
                                <td><?=formatHiddenString($v['api_secret'],4,22)?></td>
                                <td><?=$v['coin']?></td>
                                <td><?=$v['base_coin']=='USDT'?'USDT':'币本位'?></td>
                                <td><?=$v['is_start_buy_up']=='Y'?'是':'<span style="color: red;">否</span>'?>
                                    <?php if(checkAdminPrivilege('/all-api-key/ajax-add-mark')):?>
                                    <a class="btn btn-primary btn-xs" href="javascript:void(0);" onclick="add_mark('up',<?=$v['id']?>)">新增</a>
                                    <?php endif ;?>
                                </td>
                                <td><?=$v['is_start_trade_up']=='Y'?'是':'<span style="color: red;">否</span>'?></td>
                                <td><?=$v['is_start_buy_down']=='Y'?'是':'<span style="color: red;">否</span>'?>
                                    <?php if(checkAdminPrivilege('/all-api-key/ajax-add-mark')):?>
                                    <a class="btn btn-primary btn-xs" href="javascript:void(0);" onclick="add_mark('down',<?=$v['id']?>)">新增</a>
                                    <?php endif ;?>
                                </td>
                                <td><?=$v['is_start_trade_down']=='Y'?'是':'<span style="color: red;">否</span>'?></td>
                                <td><?=$v['status']=='ENABLED'?'启用':'禁用'?></td>
                                <td></td>
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