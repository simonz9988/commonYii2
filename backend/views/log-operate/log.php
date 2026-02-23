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
        <div class="col-md-4 text-right" style="">

        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/log-operate/index" method="get">

                    <div class="form-group">
                        <label>主键ID：</label>
                        <input name="redundancy_id" value="<?=$searchArr?$searchArr['redundancy_id']:''?>"  class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>类型：</label>
                        <select name="action">
                            <option value="">请选择</option>
                            <?php foreach($action_type_list as $k=>$v):?>
                                <option value="<?=$k?>" <?=$searchArr&&$searchArr['action']==$k?'selected="selected"':''?>><?=$v?></option>
                            <?php endforeach ;?>
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

        <div class="clearfix"></div>
    </div>
    <!-- /.page-header -->



    <div class="row tables-wrapper">
        <div class="col-xs-12" style="overflow: auto">
            <!-- PAGE CONTENT BEGINS  -->
            <div class="col-md-4 text-left">

            </div>

            <div class="table-responsive form-group row">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>操作人员</th>
                        <th>关联主键ID</th>
                        <th>行为</th>
                        <th>操作时间</th>
                        <th>IP</th>
                        <th width=150>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?foreach($list as $row):?>
                        <tr>
                            <td><?=$row['id']?></td>
                            <td><?=$row['operate_user_name']?></td>
                            <td><?=$row['redundancy_id']?></td>
                            <td><?=$row['action']?></td>
                            <td><?=$row['operate_time']?></td>
                            <td><?=$row['ip']?></td>
                            <td>
                                <button class="btn btn-xs btn-<?=($row['old_content'] == $row['new_content'] ? 'green':'warning')?> btn-compare J_checkauth" data-auth="/log/compare"  link="/log-operate/compare?ids=<?=$row['id']?>">
                                    <i class="ace-icon fa fa-road bigger-120"></i>
                                    Compare
                                </button>
                            </td>
                        </tr>
                    <?endforeach;?>
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

<script type="text/javascript">

    $(function(){
        $(".btn-compare").click(function(){
            var link = $(this).attr("link");
            layer.open({
                type: 2,
                title: '比对',
                shadeClose: true,
                shade: 0.8,
                area: ['90%', '80%'],
                content: link
            });
        });

        // 只能选择两条比对
        $(".compare_id").click(function(){
            var i = 0;
            $(".compare_id").each(function() {
                if ($(this).is( ":checked" )) {
                    i++;

                }
            });

            if(i > 2){
                $(this).attr("checked", false);
                alert("只能选择两条比对！");
            }
        });

        // 多条比对
        $(".btn-compare-multiple").click(function(){
            var link = $(this).attr("link");
            $('.compare_id').each(function() {
                if ($(this).is( ":checked" )) {
                    link += $(this).val() + ',';
                }
            });

            layer.open({
                type: 2,
                title: '比对',
                shadeClose: true,
                shade: 0.8,
                area: ['90%', '80%'],
                content: link
            });

        });

    });

</script>
