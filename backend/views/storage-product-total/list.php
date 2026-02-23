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
            <a href="<?=url('/storage-product/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/storage-product-total/index" method="get">

                    <div class="form-group">
                        <label>名称：</label>
                        <select name="name">
                            <option value="">请选择</option>
                            <?php foreach($name_list as $v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['name']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>

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

                <table id="table-1" class="table table-striped table-bordered table-hover" >
                    <thead>

                        <tr>
                            <th>日期</th>
                            <th>名称</th>
                            <th>数量</th>
                            <th>重量（kg）</th>
                            <th>月份</th>
                            <th>备注</th>
                            <th style="width: 150px;">编辑</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['from_date']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['total_amount']?></td>
                                <td><?=$v['total_weight']?></td>
                                <td><?=$v['month']?></td>
                                <td><?=$v['note']?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/storage-product-total/edit" href="<?=url('/storage-product-total/edit?name='.$v['name_id'].'&from_date_timestamp='.$v['from_date_timestamp'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>

                                </td>
                            </tr>
                            <?php endforeach ; ?>
                            <tr style="color: blue;">
                                <td>汇总</td>
                                <td></td>
                                <td><?=$total_info['total_amount']?></td>
                                <td><?=$total_info['total_weight']?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>

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
