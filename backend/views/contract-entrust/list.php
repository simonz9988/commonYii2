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
            <a href="<?=url('/contract-entrust/edit')?>" class="btn btn-primary btn-sm no-border J_checkauth" data-auth="/contract-entrust/edit"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/contract-entrust/index" method="get">

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
                        <label>出料形态：</label>
                        <select name="chuliao_xingtai" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($chuliao_xingtai_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['chuliao_xingtai']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>出料尺寸：</label>
                        <input name="chuliao_diameter" value="<?=$searchArr?$searchArr['chuliao_diameter']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>委外加工尺寸：</label>
                        <input name="weituo_diameter" value="<?=$searchArr?$searchArr['weituo_diameter']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>委外加工状态：</label>
                        <select name="weituo_zhuangtai" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($weituo_zhuangtai_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['weituo_zhuangtai']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>订单量：</label>
                        <input name="dingdl" value="<?=$searchArr?$searchArr['dingdl']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>出料重量：</label>
                        <input name="clzl" value="<?=$searchArr?$searchArr['clzl']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>客诉重量：</label>
                        <input name="kszl" value="<?=$searchArr?$searchArr['kszl']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>加工单价：</label>
                        <input name="jiagongdj" value="<?=$searchArr?$searchArr['jiagongdj']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>加工金额：</label>
                        <input name="jiagongje" value="<?=$searchArr?$searchArr['jiagongje']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>委外日期：</label>
                        <input name="wwrq" value="<?=$searchArr?$searchArr['wwrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>出料日期：</label>
                        <input name="clrq" value="<?=$searchArr?$searchArr['clrq']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
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
                        <label>成品回厂量：</label>
                        <input name="cphcl" value="<?=$searchArr?$searchArr['cphcl']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>残料回厂量：</label>
                        <input name="clhcl" value="<?=$searchArr?$searchArr['clhcl']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>未加工返回重量：</label>
                        <input name="wjgfhzl" value="<?=$searchArr?$searchArr['wjgfhzl']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>成材率：</label>
                        <input name="ccl" value="<?=$searchArr?$searchArr['ccl']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>返材率：</label>
                        <input name="fcl" value="<?=$searchArr?$searchArr['fcl']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
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

                    <div class="form-group">
                        <label>付款状态：</label>
                        <select name="fkzt" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($fkzt_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['fkzt']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
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

                <table id="table-1" class="table table-striped table-bordered" style="min-width: 2300px;table-layout: fixed;" >
                    <thead>
                    <tr>
                        <th></th>
                        <th>合同编号</th>
                        <th>客户名称</th>
                        <th>产品别</th>
                        <th>牌号</th>
                        <th>出料形态</th>
                        <th>出料尺寸(mm)</th>
                        <th>委外加工尺寸（mm）</th>
                        <th>委外加工状态</th>
                        <th>订单量(kg/pc)</th>
                        <th>出料重量（kg/pc）</th>
                        <th>客诉重量（kg/pc）</th>
                        <th>加工单价(元/kg/pc)</th>
                        <th>加工金额（元）</th>
                        <th>委外日期</th>
                        <th>出料日期</th>
                        <th>合同交期</th>
                        <th>实际交期</th>
                        <th>成品回厂量（kg）</th>
                        <th>残料回厂量（kg）</th>
                        <th>未加工返回重量（kg）</th>
                        <th>成材率</th>
                        <th>返材率</th>
                        <th>交货状态</th>
                        <th>付款状态</th>
                        <th>出货年份</th>
                        <th>出货月份</th>
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
                                <td><?=$v['chuliao_xingtai']?></td>
                                <td><?=$v['chuliao_diameter']?></td>
                                <td><?=$v['weituo_diameter']?></td>
                                <td><?=$v['weituo_zhuangtai']?></td>
                                <td><?=$v['dingdl'].$v['dingdl_type']?></td>
                                <td><?=$v['clzl'].$v['clzl_type']?></td>
                                <td><?=$v['kszl']> 0 ? $v['kszl'].$v['clzl_type']:''?></td>
                                <td><?=$v['jiagongdj']?></td>
                                <td><?=$v['jiagongje']?></td>
                                <td><?=$v['wwrq']?></td>
                                <td><?=$v['clrq']?></td>
                                <td><?=$v['htjq']?></td>
                                <td><?=$v['sjjq']?></td>
                                <td><?=$v['cphcl']?></td>
                                <td><?=$v['clhcl']?></td>
                                <td><?=$v['wjgfhzl']?></td>
                                <td><?=$v['ccl']?>%</td>
                                <td><?=$v['fcl']?>%</td>
                                <td><?=$v['jhzt']?></td>
                                <td><?=$v['fkzt']?></td>
                                <td><?=$v['chnf']?></td>
                                <td><?=$v['chyf']?></td>
                                <td><?=$v['modify_time']?></td>
                                <td>
                                    <a class="btn btn-warning btn-xs J_checkauth" data-auth="/contract-entrust/edit" href="<?=url('/contract-entrust/edit?id='.$v['id'].'&add_extra=y')?>"> <i class="ace-icon fa fa-warning bigger-100"></i>补充</a>

                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/contract-entrust/modify" href="<?=url('/contract-entrust/modify?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/contract-entrust/view" href="<?=url('/contract-entrust/view?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i> 浏览</a>

                                    <a class="btn btn-xs btn-danger cus-btn-del J_checkauth" data-auth="/contract-entrust/del" href="javascript:void(0);" onclick="delModel('/contract-entrust/del?id=<?=$v['id']?>')">
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
                                <td></td>
                                <td></td>
                                <td><?=$total_info['dingdl']?></td>
                                <td><?=$total_info['clzl']?></td>
                                <td><?=$total_info['kszl']?></td>
                                <td><?=$total_info['jiagongdj']?></td>
                                <td><?=$total_info['jiagongje']?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><?=$total_info['cphcl']?></td>
                                <td><?=$total_info['clhcl']?></td>
                                <td><?=$total_info['wjgfhzl']?></td>
                                <td><?=$total_info['ccl']?>%</td>
                                <td><?=$total_info['fcl']?>%</td>
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
