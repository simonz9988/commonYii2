
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
                <form class="form-inline" id="shippingOrderSearchForm" action="/robot-balance/cash-out" method="get">

                    <div class="form-group">
                        <label>地址：</label>
                        <input name="address" value="<?=$searchArr?$searchArr['address']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>手机号码：</label>
                        <input name="mobile" value="<?=$searchArr?$searchArr['mobile']:''?>" class="input-sm  form-control" type="text">
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
                        <th>用户ID</th>
                        <th>手机号码</th>
                        <th>金额</th>
                        <th>手续费</th>
                        <th>币种</th>
                        <th>地址</th>
                        <th>状态</th>
                        <th>操作</th>

                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['user_id']?></td>
                                <td><?=$v['mobile']?></td>
                                <td><?=$v['total']?></td>
                                <td><?=$v['fee']?></td>
                                <td><?=$v['coin']?></td>
                                <td><?=$v['address']?></td>
                                <td><?=$v['status_show']?></td>
                                <td>
                                    <?php if($v['status']=='UNDEAL'):?>
                                    <a class="btn btn-primary btn-xs" href="javascript:void(0);"  onclick="audit_status(<?=$v['id']?>,'Y')">
                                        <i class="ace-icon fa fa-edit bigger-100"></i> 通过
                                    </a>
                                    <a class="btn btn-xs btn-danger cus-btn-del" href="javascript:void(0);" onclick="audit_status(<?=$v['id']?>,'N')">
                                        <i class="ace-icon fa fa-trash-o"></i>拒绝
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
    function audit_status(id,audit_status){
        var message = '确认审核该条记录？';


        bootbox.confirm({
            size:'small',
            message: message,
            buttons: {
                cancel: {
                    label: '<i class="fa fa-times"></i> 取消'
                },
                confirm: {
                    label: '<i class="fa fa-check"></i> 确定'
                }
            },
            callback: function (result) {
                if(result){
                    $.post(
                        '/robot-balance/ajax-audit-cash-out',
                        {id:id,audit_status:audit_status},
                        function(data){
                            var arr = eval('('+data+')');
                            if(arr.code == 1){
                                window.location.reload();
                            }else{
                                alert(arr.msg);
                            }
                        }
                    );
                }else{
                    return true;
                }
            }
        });
    }
</script>