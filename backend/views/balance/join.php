
<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->selectedLevel0Name?></a> </li>
                <li class="active"><?=$this->selectedLevel1Name?></li>
            </ul>
            <!-- /.breadcrumb -->
        </div>
        <div class="col-md-4 text-right">
            <a href="<?=url('/adminPage/exchange/addGoods')?>" style="display: none ;" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/adminPage/exchange/join" method="get">

                    <div class="form-group">
                        <label>姓名：</label>
                        <input name="name" value="<?=$searchArr?$searchArr['name']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>手机号码：</label>
                        <input name="mobile" value="<?=$searchArr?$searchArr['mobile']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>邮箱：</label>
                        <input name="email" value="<?=$searchArr?$searchArr['email']:''?>" class="input-sm  form-control" type="text">
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
                        <th>ID</th>
                        <th>姓名</th>
                        <th>手机</th>
                        <th>邮箱</th>
                        <th>IP</th>
                        <th>留言</th>
                        <th>申请时间</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['mobile']?></td>
                                <td><?=$v['email']?></td>
                                <td><?=$v['ip']?></td>
                                <td><?=$v['note']?></td>
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
                <?php $this->renderPartial('/layouts/page',array('page'=>$page));?>

            </div>



            <!-- /.page-paging 结束 -->

        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>