
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
            <a href="<?=url('/adminPage/system/addSymbol')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>
    </div>
</div>



<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search" style="display:none;">
                <form class="form-inline" id="shippingOrderSearchForm" action="/adminPage/okex/orderList" method="get">

                    <div class="form-group">
                        <label>币种：</label>
                       <select name="symbol">
                            <option value="">请选择</option>
                            <?php if($symbolList):?>
                                <?php foreach($symbolList as $k=>$v):?>
                                    <option value="<?=$v['key']?>" <?=$searchArr&&$searchArr['symbol']==$v['key']?'selected="selected"':''?> ><?=$v['key']?></option>
                                <?php endforeach ;?>
                            <?php endif ;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>交易类型：</label>
                        <select name="type">
                            <option value="">请选择</option>
                            <?php if($typeList):?>
                                <?php foreach($typeList as $v):?>
                                    <option value="<?=$v?>" <?=$searchArr&&$searchArr['type']==$v?'selected="selected"':''?> ><?=$v?></option>
                                <?php endforeach ;?>
                            <?php endif ;?>
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

            <div class="table-responsive">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>币种关键字</th>
                        <th>止跌比例</th>
                        <th>创建时间</th>
                        <th>更新时间</th>
                        <th>编辑</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $k=>$v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['key']?></td>
                                <td><?=$v['down_percent']?></td>
                                <td><?=$v['create_time']?></td>
                                <td><?=$v['update_time']?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs" href="<?=url('/adminPage/system/addSymbol?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
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