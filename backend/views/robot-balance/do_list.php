
<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->selectedLevel0Name?></a> </li>
                <li class="active"><?=$this->selectedLevel1Name?></li>
            </ul>
            <!-- /.breadcrumb -->
        </div>
        <div class="col-md-4 text-right" style="display: none ;">
            <a href="<?=url('/adminPage/system/addMenu')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
            <a href="<?=url('/adminPage/exchange/import')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 导入</a>
            <a href="/static/xieka/import_card.xls" target="_blank" class="btn btn-primary btn-sm no-border">模版下载</a>

        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/adminPage/exchange/doList" method="get">

                    <div class="form-group">
                        <label>卡号：</label>
                        <input name="card_no" value="<?=$searchArr?$searchArr['card_no']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>订单状态：</label>
                        <select name="order_status"  class="input-sm  form-control" >
                            <option value="">请选择</option>
                            <option value="PENDING" <?=$searchArr&&$searchArr['order_status']=='PENDING'?'selected="selected"':''?>>代发货</option>
                            <option value="SENDED" <?=$searchArr&&$searchArr['order_status']=='SENDED'?'selected="selected"':''?>>已发货</option>
                            <option value="CLOSED" <?=$searchArr&&$searchArr['order_status']=='CLOSED'?'selected="selected"':''?>>已完成</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>兑换时间：</label>
                        <input name="start_time" value="<?=$searchArr?$searchArr['start_time']:''?>" onclick="WdatePicker({dateFmt: 'yyyy-MM-dd HH:mm:ss'})" class="input-sm  form-control" type="text">-
                        <input name="end_time" value="<?=$searchArr?$searchArr['end_time']:''?>" onclick="WdatePicker({dateFmt: 'yyyy-MM-dd HH:mm:ss'})" class="input-sm  form-control" type="text">
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

                        <th>卡号</th>
                        <th>姓名</th>
                        <th>手机号</th>
                        <th>固定电话</th>
                        <th>订单状态</th>
                        <th>时段</th>
                        <th>时间</th>
                        <th>省</th>
                        <th>市</th>
                        <th>区</th>
                        <th>具体地址</th>
                        <th>物流编号</th>
                        <th>兑换时间</th>
                        <th>备注</th>
                        <th>操作</th>

                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['card_no']?></td>
                                <td><?=$v['name']?></td>
                                <td><?=$v['mobile']?></td>
                                <td><?=$v['telphone']?></td>
                                <td><?=CardAddress::model()->getOrderStatusName($v['order_status'])?></td>
                                <td><?=$v['tq_time']=='am'?'上午':'下午'?></td>
                                <td><?=$v['tq_year'].'-'.$v['tq_month'].'-'.$v['tq_day']?></td>
                                <td><?=Areas::model()->getNameById($v['province'])?></td>
                                <td><?=Areas::model()->getNameById($v['city'])?></td>
                                <td><?=Areas::model()->getNameById($v['area'])?></td>
                                <td><?=$v['address']?></td>
                                <td>
                                    <?php if(!$v['express_code']) :?>
                                        <a href="javascript:void" onclick="add_express(this)" attr_id="<?=$v['id']?>">添加物流</a>
                                    <?php else:?>
                                        <a href="http://m.baidu.com/s?word=<?=$v['express_code']?>" target="_blank"><?=$v['express_code']?></a>
                                    <?php endif ;?>
                                </td>
                                <td><?=$v['create_time']?></td>
                                <td><?=$v['note']?></td>
                                <td>
                                    <?php if($v['order_status'] =='SENDED') :?>
                                        <a href="javascript:void" onclick="do_close(this)" attr_id="<?=$v['id']?>">已完成</a>

                                    <?php endif ;?>
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
<script>
    function add_express(self) {
        var id = $(self).attr("attr_id") ;
        layer.prompt(function(val, index){
            $.post(
                '/adminPage/exchange/ajaxSaveExpress',
                {id:id,express_code:val},
                function(data){
                    var arr =eval('('+data+')');
                    if(arr.code ==1){
                        window.location.reload();
                    }else{
                        alert(arr.msg);
                    }
                }
            );
            layer.close(index);
        });
    }

    function do_close(self){
        var id = $(self).attr("attr_id") ;
        layer.confirm('确认已完成', {
            btn: ['否', '是']
        }, function(index, layero){
            layer.close(index);
        }, function(index){
            $.post(
                '/adminPage/exchange/ajaxSaveClosed',
                {id:id},
                function(data){
                    var arr =eval('('+data+')');
                    if(arr.code ==1){
                        window.location.reload();
                    }else{
                        alert(arr.msg);
                    }
                }
            );
            layer.close(index);
        });
    }
</script>