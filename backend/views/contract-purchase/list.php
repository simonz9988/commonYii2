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
            <a href="<?=url('/contract-purchase/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/contract-purchase/index" method="get">

                    <div class="form-group">
                        <label>合同编号：</label>
                        <input name="contract_no" value="<?=$searchArr?$searchArr['contract_no']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>供应商名称：</label>
                        <input name="supplier_name" value="<?=$searchArr?$searchArr['supplier_name']:''?>" class="input-sm  form-control" type="text">
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
                        <label>采购量：</label>
                        <input name="purchase_volume" value="<?=$searchArr?$searchArr['purchase_volume']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>实际交货量：</label>
                        <input name="cgl" value="<?=$searchArr?$searchArr['cgl']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>采购单价：</label>
                        <input name="cgdj" value="<?=$searchArr?$searchArr['cgdj']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>应付款金额：</label>
                        <input name="yinfkze" value="<?=$searchArr?$searchArr['yinfkze']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>已付款金额：</label>
                        <input name="yifukuanze" value="<?=$searchArr?$searchArr['yifukuanze']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>付款差额：</label>
                        <input name="fkce" value="<?=$searchArr?$searchArr['fkce']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>付款状态：</label>
                        <select name="fuzt" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($fuzt_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['fuzt']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>采购日期：</label>
                        <input name="cgrq" value="<?=$searchArr?$searchArr['cgrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>合同交期：</label>
                        <input name="htjq" value="<?=$searchArr?$searchArr['htjq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>实际交期：</label>
                        <input name="sjjq" value="<?=$searchArr?$searchArr['sjjq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>交货状态：</label>
                        <select name="jhzt" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($jhzt_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['jhzt']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
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
                        <th>供应商名称</th>
                        <th>产品别</th>
                        <th>牌号</th>
                        <th>产品形态</th>
                        <th>尺寸规格</th>
                        <th>采购量(kg/pcs)</th>
                        <th>实际交货量（kg/pcs）</th>
                        <th>采购单价(元/kg/pcs)</th>
                        <th>应付款金额（元）</th>
                        <th>已付款金额（元）</th>
                        <th>付款差额</th>
                        <th>付款状态</th>
                        <th>采购日期</th>
                        <th>合同交期</th>
                        <th>实际交期</th>
                        <th>交货状态</th>
                        <th>修改时间</th>
                        <th style="width: 280px;">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['contract_no']?></td>
                                <td><?=$v['supplier_name']?></td>
                                <td><?=$v['chanpin_bie']?></td>
                                <td><?=$v['paihao']?></td>
                                <td><?=$v['product_form']?></td>
                                <td><?=$v['diameter']?></td>
                                <td><?=$v['purchase_volume'].$v['purchase_volume_type']?></td>
                                <td><?=$v['cgl'].$v['cgl_type']?></td>
                                <td><?=$v['cgdj']?></td>
                                <td><?=$v['yinfkze']?></td>
                                <td><?=$v['yifukuanze']?></td>
                                <td><?=$v['fkce']?></td>
                                <td><?=$v['fuzt']?></td>
                                <td><?=$v['cgrq']?></td>
                                <td><?=$v['htjq']?></td>
                                <td><?=$v['sjjq']?></td>
                                <td><?=$v['jhzt']?></td>
                                <td><?=$v['modify_time']?></td>
                                <td>
                                    <a class="btn btn-warning btn-xs J_checkauth" data-auth="/contract-purchase/edit" href="<?=url('/contract-purchase/edit?id='.$v['id'].'&add_extra=y')?>"> <i class="ace-icon fa fa-warning bigger-100"></i>补充</a>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/contract-purchase/modify" href="<?=url('/contract-purchase/modify?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/contract-purchase/view" href="<?=url('/contract-purchase/view?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i> 浏览</a>

                                    <a class="btn btn-xs btn-danger cus-btn-del J_checkauth" data-auth="/contract-purchase/del" href="javascript:void(0);" onclick="delModel('/contract-purchase/del?id=<?=$v['id']?>')">
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
                                <td><?=$total_info['purchase_volume']?></td>
                                <td><?=$total_info['cgl']?></td>
                                <td><?=$total_info['cgdj']?></td>
                                <td><?=$total_info['yinfkze']?></td>
                                <td><?=$total_info['yifukuanze']?></td>
                                <td><?=$total_info['fkce']?></td>
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
