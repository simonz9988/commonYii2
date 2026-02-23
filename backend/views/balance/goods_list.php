
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
            <a href="<?=url('/adminPage/exchange/addGoods')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/adminPage/exchange/goodsList" method="get">

                    <div class="form-group">
                        <label>商品描述：</label>
                        <input name="goods_desc" value="<?=$searchArr?$searchArr['goods_desc']:''?>" class="input-sm  form-control" type="text">
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
                        <th>商品名称</th>
                        <th>面额</th>
                        <th>开始时间</th>
                        <th>结束时间</th>
                        <th>备注</th>
                        <th>兑换提示语</th>
                        <th>编辑</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['sell_price']?></td>
                                <td><?=$v['start_time']?></td>
                                <td><?=$v['end_time']?></td>
                                <td><?=$v['note']?></td>
                                <td><?=$v['notice']?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs" href="<?=url('/adminPage/exchange/addGoods?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-xs btn-danger cus-btn-del" href="javascript:void(0);" onclick="delModel('/adminPage/exchange/delGoods?id=<?=$v['id']?>')">
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
                <?php $this->renderPartial('/layouts/page',array('page'=>$page));?>

            </div>



            <!-- /.page-paging 结束 -->

        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>