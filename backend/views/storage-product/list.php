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
            <a href="<?=url('/storage-product/edit')?>" class="btn btn-primary btn-sm no-border J_checkauth" data-auth="/storage-product/edit" ><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/storage-product/index" method="get">

                    <div class="form-group">
                        <label>日期：</label>
                        <input name="from_date" value="<?=$searchArr?$searchArr['from_date']:''?>" onClick="WdatePicker({dateFmt:'yyyy/MM/dd', startDate: '%y/%M/%d'})" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>名称：</label>
                        <select name="name" class="input-sm  form-control">
                            <option value="">请选择</option>
                            <?php foreach($name_list as $k=>$v):?>
                                <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['name']==$v['id']?'selected="selected"':''?>><?=$v['detail']?></option>
                            <?php endforeach;?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>原炉号：</label>
                        <input name="ylh" value="<?=$searchArr?$searchArr['ylh']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>新炉号：</label>
                        <input name="xlh" value="<?=$searchArr?$searchArr['xlh']:''?>" class="input-sm  form-control" type="text">
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
                        <label>规格：</label>
                        <input name="diameter" value="<?=$searchArr?$searchArr['diameter']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>数量：</label>
                        <input name="amount" value="<?=$searchArr?$searchArr['amount']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>重量KG：</label>
                        <input name="weight" value="<?=$searchArr?$searchArr['weight']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>是否为代工：</label>
                        <select name="is_daigong" class="input-sm  form-control">
                            <option value="">请选择</option>

                                <option value="Y" <?=$searchArr&&$searchArr['is_daigong']=='Y'?'selected="selected"':''?>>是</option>
                                <option value="N" <?=$searchArr&&$searchArr['is_daigong']=='N'?'selected="selected"':''?>>否</option>

                        </select>
                    </div>

                    <div class="form-group">
                        <label>月份：</label>
                        <input name="month" value="<?=$searchArr?$searchArr['month']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>袋号：</label>
                        <input name="bag_no" value="<?=$searchArr?$searchArr['bag_no']:''?>" class="input-sm  form-control" type="text">
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

                <table id="table-1" class="table table-striped table-bordered table-hover" >
                    <thead>

                        <tr>

                            <th>日期</th>
                            <th>名称</th>
                            <th>原炉号</th>
                            <th>新炉号</th>
                            <th>牌号</th>
                            <th>规格（mm)</th>
                            <th>数量</th>
                            <th>重量KG</th>
                            <th>是否为代工</th>
                            <th>月份</th>
                            <th>袋号</th>
                            <th>备注</th>
                            <th>修改时间</th>
                            <th style="width: 200px;">编辑</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['from_date']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['ylh']?></td>
                                <td><?=$v['xlh']?></td>
                                <td><?=$v['paihao']?></td>
                                <td><?=$v['diameter']?></td>
                                <td><?=$v['amount']?></td>
                                <td><?=$v['weight']?></td>
                                <td><?=$v['is_daigong']=='Y'?'是':'否'?></td>
                                <td><?=$v['month']?></td>
                                <td><?=$v['bag_no']?></td>
                                <td><?=$v['note']?></td>
                                <td><?=$v['modify_time']?></td>
                                <td>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/storage-product/modify" href="<?=url('/storage-product/modify?id='.$v['id'])?>"> <i class="ace-icon fa fa-edit bigger-100"></i> 修改</a>
                                    <a class="btn btn-primary btn-xs J_checkauth" data-auth="/storage-product/view" href="<?=url('/storage-product/view?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i> 浏览</a>

                                    <a class="btn btn-xs btn-danger cus-btn-del J_checkauth" data-auth="/storage-product/del" href="javascript:void(0);" onclick="delModel('/storage-sfl/del?id=<?=$v['id']?>')">
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
                                <td><?=$v['amount']?></td>
                                <td><?=$v['weight']?></td>
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
