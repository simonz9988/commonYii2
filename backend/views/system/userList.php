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
            <a href="/system/add-user" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->


<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/system/user-list" method="get">

                    <div class="form-group">
                        <label>用户名：</label>
                        <input name="username" value="<?=$searchArr?$searchArr['username']:''?>" class="input-sm  form-control" type="text">
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

            <div class="table-responsive">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>

                        <th>用户名</th>
                        <th>角色名称</th>
                        <th>是否有效</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['username']?></td>
                                <td><?=$v['role_name']?></td>
                                <td><?=$v['is_open']==1?'是':'否'?></td>
                                <td><?=$v['create_time']?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs" href="<?=url('/system/add-user?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-yellow btn-xs" href="<?=url('/system/edit-role-privilege?role_id='.$v['role_id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 编辑权限</a>
                                    <a style="display: none;"class="btn btn-gray btn-xs" href="<?=url('/system/edit-role-order?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 订单用户权限</a>
                                    <img style="display: none;" width="80" height="80" src="<?=$v['google_img']?>">
                                    <a style="display: none;" class="btn btn-gray btn-xs" href="<?=url('/system/edit-user-bank?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 用户基本信息</a>

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