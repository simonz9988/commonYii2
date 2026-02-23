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
            <a href="<?=url('/contract-sales/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/contract-sales/index" method="get">

                    <div class="form-group">
                        <label>合同编号：</label>
                        <input name="contract_no" value="<?=$searchArr?$searchArr['contract_no']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>客户名称：</label>
                        <input name="customer_name" value="<?=$searchArr?$searchArr['customer_name']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>产品别：</label>
                        <select name="chanpin_bie" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($chanpin_bie_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['chanpin_bie']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>牌号：</label>
                        <select name="paihao" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($paihao_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['paihao']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>产品形态：</label>
                        <select name="product_form" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($product_form_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['product_form']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>尺寸规格：</label>
                        <input name="diameter" value="<?=$searchArr?$searchArr['diameter']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>单价：</label>
                        <input name="danjia" value="<?=$searchArr?$searchArr['danjia']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>订单量：</label>
                        <input name="dingdl" value="<?=$searchArr?$searchArr['dingdl']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>实际出货量：</label>
                        <input name="sjchl" value="<?=$searchArr?$searchArr['sjchl']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>客诉重量：</label>
                        <input name="kszl" value="<?=$searchArr?$searchArr['kszl']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>应收款：</label>
                        <input name="yinskze" value="<?=$searchArr?$searchArr['yinskze']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>实际收款：</label>
                        <input name="shijishk" value="<?=$searchArr?$searchArr['shijishk']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>收款差异：</label>
                        <input name="skcy" value="<?=$searchArr?$searchArr['skcy']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>交货状态：</label>
                        <select name="jiaohzt" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($jhzt_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['jiaohzt']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>接单日期：</label>
                        <input name="jdrq" value="<?=$searchArr?$searchArr['jdrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>合同交期：</label>
                        <input name="htjq" value="<?=$searchArr?$searchArr['htjq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>实际出货日期：</label>
                        <input name="sjchrq" value="<?=$searchArr?$searchArr['sjchrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>出货年份：</label>
                        <input name="chnf" value="<?=$searchArr?$searchArr['chnf']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>出货月份：</label>
                        <input name="chyf" value="<?=$searchArr?$searchArr['chyf']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <input type="hidden" name="is_download" value="<?=$searchArr?$searchArr['is_download']:'0'?>">

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-xs no-border" onclick="doSubmit()">
                            <i class="ace-icon fa fa-search bigger-120"></i>
                            <span class="bigger-120">搜索</span>
                        </button>

                        <button type="submit" class="btn btn-warning btn-xs no-border"onclick="doDownload()">
                            <i class="ace-icon fa fa-download bigger-120"></i>
                            <span class="bigger-120">下载</span>
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

        <div class="clearfix"></div>
    </div>
    <!-- /.page-header -->



    <div class="row tables-wrapper">
        <div class="col-xs-12" style="overflow: auto">
            <!-- PAGE CONTENT BEGINS  -->
            <div class="col-md-4 text-left">

            </div>

            <div class="table-responsive form-group row">

                <table id="table-1" class="table table-striped table-bordered" style="min-width: 2300px;table-layout: fixed;">
                    <thead>
                    <tr>
                        <th></th>
                        <th>合同编号</th>
                        <th>客户名称</th>
                        <th>产品别</th>
                        <th>牌号</th>
                        <th>产品形态</th>
                        <th>尺寸规格(mm)</th>
                        <th>单价(元/kg/pcs)</th>
                        <th>订单量(kg/pcs)</th>
                        <th>实际出货量（kg/pcs）</th>
                        <th>客诉重量（kg/pcs）</th>
                        <th>应收款（元）</th>
                        <th>实际收款（元）</th>
                        <th>收款差异（元）</th>
                        <th>交货状态</th>
                        <th>接单日期</th>
                        <th>合同交期</th>
                        <th>实际出货日期</th>
                        <th>出货年份</th>
                        <th>出货月份</th>
                        <th>备注</th>
                        <th>修改时间</th>
                        <th style="width: 250px;">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['contract_no']?></td>
                                <td><?=$v['customer_name']?></td>
                                <td><?=$v['chanpin_bie']?></td>
                                <td><?=$v['paihao']?></td>
                                <td><?=$v['product_form']?></td>
                                <td><?=$v['diameter']?></td>
                                <td><?=$v['danjia']?></td>
                                <td><?=$v['dingdl'].$v['dingdl_type']?></td>
                                <td><?=$v['sjchl'].$v['sjchl_type']?></td>
                                <td><?=$v['kszl'] ? $v['kszl'].$v['sjchl_type']:''?></td>
                                <td><?=$v['yinskze']?></td>
                                <td><?=$v['shijishk']?></td>
                                <td><?=$v['skcy']?></td>
                                <td><?=$v['jiaohzt']?></td>
                                <td><?=$v['jdrq']?></td>
                                <td><?=$v['htjq']?></td>
                                <td><?=$v['sjchrq']?></td>
                                <td><?=$v['chnf']?></td>
                                <td><?=$v['chyf']?></td>
                                <td><?=$v['note']?></td>
                                <td><?=$v['modify_time']?></td>
                                <td>
                                    <a class="btn btn-warning btn-xs J_checkauth" data-auth="/contract-sales/edit" href="<?=url('/contract-sales/edit?id='.$v['id'].'&add_extra=y')?>"> <i class="ace-icon fa fa-warning bigger-100"></i>补充</a>

                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/contract-sales/modify" href="<?=url('/contract-sales/modify?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/contract-sales/view" href="<?=url('/contract-sales/view?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i> 浏览</a>

                                    <a class="btn btn-xs btn-danger cus-btn-del J_checkauth" data-auth="/contract-sales/del" href="javascript:void(0);" onclick="delModel('/contract-sales/del?id=<?=$v['id']?>')">
                                        <i class="ace-icon fa fa-trash-o"></i>删除
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach ; ?>

                            <tr style="color: blue;">
                                <td>汇总</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><?=$total_info['danjia']?></td>
                                <td><?=$total_info['dingdl']?></td>
                                <td><?=$total_info['sjchl']?></td>
                                <td><?=$total_info['kszl']?></td>
                                <td><?=$total_info['yinskze']?></td>
                                <td><?=$total_info['shijishk']?></td>
                                <td><?=$total_info['skcy']?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
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
