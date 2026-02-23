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
            <a href="<?=url('/turnover-draft/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/turnover-draft/index" method="get">

                    <div class="form-group">
                        <label>合同编号：</label>
                        <input name="contract_no" value="<?=$searchArr?$searchArr['contract_no']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>票号：</label>
                        <input name="piaohao" value="<?=$searchArr?$searchArr['piaohao']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>日期：</label>
                        <input name="from_date" value="<?=$searchArr?$searchArr['from_date']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>付款公司名称：</label>
                        <input name="fukuan_company" value="<?=$searchArr?$searchArr['fukuan_company']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>收款公司名称：</label>
                        <input name="shoukuan_company" value="<?=$searchArr?$searchArr['shoukuan_company']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>部门：</label>
                        <select name="department" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($department_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['department']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>



                    <div class="form-group">
                        <label>项目：</label>
                        <select name="project" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($project_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['project']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>收入/支出：</label>
                        <select name="type" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($type_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['type']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>票面金额：</label>
                        <input name="amount" value="<?=$searchArr?$searchArr['amount']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>票号：</label>
                        <input name="piaohao" value="<?=$searchArr?$searchArr['piaohao']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>承兑人名称：</label>
                        <input name="chendui_name" value="<?=$searchArr?$searchArr['chendui_name']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>备注：</label>
                        <input name="note" value="<?=$searchArr?$searchArr['note']:''?>" class="input-sm  form-control" type="text">
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

                <table id="table-1" class="table table-striped table-bordered" >
                    <thead>
                    <tr>
                        <th>序号</th>
                        <th>合同编号</th>
                        <th>日期</th>
                        <th>付款公司名称</th>
                        <th>收款公司名称</th>
                        <th>部门</th>
                        <th>项目</th>
                        <th>收入/支出</th>
                        <th>票面金额</th>
                        <th style="width: 120px;">票号</th>
                        <th>承兑人名称</th>
                        <th>备注</th>
                        <th>修改时间</th>
                        <th style="width: 200px;">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['contract_no']?></td>
                                <td><?=$v['from_date']?></td>
                                <td><?=$v['fukuan_company']?></td>
                                <td><?=$v['shoukuan_company']?></td>
                                <td><?=$v['department']?></td>
                                <td><?=$v['project']?></td>
                                <td><?=$v['type']?></td>
                                <td><?=$v['amount']?></td>
                                <td><span><?=$v['piaohao']?></span><a class="btn btn-warning btn-xs J_checkauth" data-auth="/turnover-draft/edit" href="<?=url('/turnover-draft/edit?id='.$v['id'].'&add_extra=y')?>"> <i class="ace-icon fa fa-warning bigger-100"></i> 补充</a>
                                </td>
                                <td><?=$v['chendui_name']?></td>
                                <td><?=$v['note']?></td>
                                <td><?=$v['modify_time']?></td>
                                <td>

                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/turnover-draft/modify" href="<?=url('/turnover-draft/modify?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/turnover-draft/view" href="<?=url('/turnover-draft/view?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i> 浏览</a>

                                    <a class="btn btn-xs btn-danger cus-btn-del J_checkauth" data-auth="/turnover-draft/del" href="javascript:void(0);" onclick="delModel('/turnover-draft/del?id=<?=$v['id']?>')">
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
                                <td><?=$total_info['amount']?></td>
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
