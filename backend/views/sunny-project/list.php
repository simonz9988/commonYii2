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
            <a href="<?=url('/sunny-company/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-project/index" method="get">

                    <div class="form-group">
                        <label>名称：</label>
                        <input name="name" value="<?=$searchArr?$searchArr['name']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <input type="hidden" name="is_download" value="0">

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-xs no-border" onclick="doSubmit()">
                            <i class="ace-icon fa fa-search bigger-120"></i>
                            <span class="bigger-120">搜索</span>
                        </button>


                    </div>
                    <script>
                        function doSubmit(){
                            $("input[name=is_download]").val(0);
                        }
                        function doDownload(){
                            $("input[name=is_download]").val(1);
                        }
                    </script>

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
                        <th>绑定客户</th>
                        <th>所属公司</th>
                        <th>项目名称</th>
                        <th>设备数量</th>
                        <th>告警数量</th>
                        <th>唯一编码</th>
                        <th>时区</th>
                        <th>国家</th>
                        <th>省</th>
                        <th>市</th>
                        <th>区</th>
                        <th>地址</th>
                        <th>经度</th>
                        <th>纬度</th>
                        <th>地图展示名称</th>
                        <th style="width: 140px;">创建时间</th>
                        <th style="width: 280px;">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['customer_id']?></td>
                                <td><?=$v['company_id']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['device_num']?></td>
                                <td><?=$v['fault_num']?></td>
                                <td><?=$v['unique_code']?></td>
                                <td><?=$v['time_zone']?></td>
                                <td><?=$v['country']?></td>
                                <td><?=$v['province']?></td>
                                <td><?=$v['city']?></td>
                                <td><?=$v['area']?></td>
                                <td><?=$v['address']?></td>
                                <td><?=$v['longitude']?></td>
                                <td><?=$v['latitude']?></td>
                                <td><?=$v['map_name']?></td>
                                <td><?=$v['create_time']?></td>
                                <td>
                                    <a class="btn btn-info btn-xs" href="<?=url('/sunny-project/edit?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i>编辑</a>
                                    <a class="btn btn-warning btn-xs" href="<?=url('/sunny-device/index?project_id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i>进入项目</a>
                                    <a class="btn btn-xs btn-danger cus-btn-del" href="javascript:void(0);" onclick="delModel('/sunny-project/del?id=<?=$v['id']?>')">
                                        <i class="ace-icon fa fa-trash-o"></i>删除
                                    </a>
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
    function add_mark(id,type){

        layer.confirm('确认变更状态?', function(index){

            layer.close(index);

            $.post(
                '/sunny-device/save',
                {id:id,status:type},function(data){
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