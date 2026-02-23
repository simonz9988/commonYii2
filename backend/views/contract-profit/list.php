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
            <a href="<?=url('/contract-profit/edit')?>" class="btn btn-primary btn-sm no-border J_checkauth" data-auth="/contract-profit/edit"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/contract-profit/index" method="get">

                    <div class="form-group">
                        <label>炉号编号：</label>
                        <input name="contract_no" value="<?=$searchArr?$searchArr['contract_no']:''?>" class="input-sm  form-control" type="text">
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
                        <th rowspan="2">炉号编号</th>
                        <th colspan="3" style="text-align: center ;">销售收入（元）</th>
                        <th colspan="5" style="text-align: center ;">成本支出（元）</th>
                        <th rowspan="2" >成材率</th>
                        <th rowspan="2" >毛利（元）</th>
                        <th rowspan="2" >毛利率</th>
                        <th rowspan="2" >净利（元）</th>
                        <th rowspan="2" >净利率</th>
                        <th rowspan="2" >备注</th>
                        <th rowspan="2" >修改时间</th>
                        <th rowspan="2" style="width: 200px;">操作</th>
                    </tr>
                    <tr>

                        <th>成品收入</th>
                        <th>改制品收入</th>
                        <th>废料收入</th>
                        <th>原料费用</th>
                        <th>外协加工费用</th>
                        <th>运输费用</th>
                        <th>生产费用</th>
                        <th>客诉费用</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['contract_no']?></td>
                                <td><?=$v['cpsr']?></td>
                                <td><?=$v['gzpsr']?></td>
                                <td><?=$v['flsr']?></td>
                                <td><?=$v['ylfy']?></td>
                                <td><?=$v['wxjgfy']?></td>
                                <td><?=$v['ysfy']?></td>
                                <td><?=$v['scfy']?></td>
                                <td><?=$v['ksfy']?></td>
                                <td><?=$v['ccl']?></td>
                                <td><?=$v['maoli']?></td>
                                <td><?=$v['maolilv']?>%</td>
                                <td><?=$v['jingli']?></td>
                                <td><?=$v['jinglilv']?>%</td>
                                <td><?=$v['note']?></td>
                                <td><?=$v['modify_time']?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/contract-profit/modify" href="<?=url('/contract-profit/modify?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/contract-profit/view" href="<?=url('/contract-profit/view?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i> 浏览</a>

                                    <a class="btn btn-xs btn-danger cus-btn-del J_checkauth" data-auth="/contract-profit/del" href="javascript:void(0);" onclick="delModel('/contract-profit/del?id=<?=$v['id']?>')">
                                        <i class="ace-icon fa fa-trash-o"></i>删除
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach ; ?>
                            <tr style="color: blue;">
                                <td>汇总</td>
                                <td><?=$total_info['cpsr']?></td>
                                <td><?=$total_info['gzpsr']?></td>
                                <td><?=$total_info['flsr']?></td>
                                <td><?=$total_info['ylfy']?></td>
                                <td><?=$total_info['wxjgfy']?></td>
                                <td><?=$total_info['ysfy']?></td>
                                <td><?=$total_info['scfy']?></td>
                                <td><?=$total_info['ksfy']?></td>
                                <td><?=$total_info['ccl']?></td>
                                <td><?=$total_info['maoli']?></td>
                                <td><?=$total_info['maolilv']?>%</td>
                                <td><?=$total_info['jingli']?></td>
                                <td><?=$total_info['jinglilv']?>%</td>
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
