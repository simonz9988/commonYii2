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
            <a href="<?=url('/sunny-device-category/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-device-category/index" method="get">



                    <div class="form-group">
                        <label>分类名称：</label>
                        <input name="name" value="<?=$searchArr?$searchArr['name']:''?>" class="input-sm  form-control" type="text">
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
                        <th>名称</th>
                        <th>父类名称</th>
                        <th>关键词</th>
                        <th>蓄电池类型</th>
                        <th>系统电压</th>
                        <th>负载电流设置</th>
                        <th>智能功率</th>
                        <th>是否有效</th>
                        <th style="width: 200px;">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['parent_name']?></td>
                                <td><?=$v['unique_key']?></td>
                                <td><?=$v['battery_type']?></td>
                                <td><?=$v['battery_rate_volt']?>V</td>
                                <td><?=$v['led_current_set']?>A</td>
                                <td><?=$v['auto_power_set']?></td>
                                <td><?=$v['status']=='ENABLED'?'是':'否'?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs" href="<?=url('/sunny-device-category/edit?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-xs btn-danger cus-btn-del" href="javascript:void(0);" onclick="delModel('/sunny-device-category/del?id=<?=$v['id']?>')">
                                        <i class="ace-icon fa fa-trash-o"></i>删除
                                    </a>
                                    <?php if($v['parent_id']):?>
                                        <a class="btn btn-xs btn-success" href="javascript:void(0);" onclick="addBatch(<?=$v['id']?>)"">
                                                                                                                                      批量新增
                                        </a>
                                    <?php endif ;?>
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

    function addBatch(category_id){
        layer.prompt({"title":'请输入新增设备的数目'},function(val, index){
            $.post(
                '/sunny-device-category/batch-add',
                {category_id:category_id,total:val},function(data){
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

</script>