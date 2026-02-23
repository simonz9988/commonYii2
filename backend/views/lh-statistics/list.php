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
            <a href="<?=url('/lh-process/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/lh-statistics/index" method="get">

                    <div class="form-group">
                        <label>炉号：</label>
                        <input name="lh_no" value="<?=$searchArr?$searchArr['lh_no']:''?>"  class="input-sm  form-control" type="text">
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
                            <th>炉号</th>
                            <?php foreach($process_list as $k=>$v):?>
                            <th><?=$v['name']?><?php if($v['type']!='INPUT'):?>(
                                <?php if($total_rows > 0 ){
                                    echo numberSprintf($process_list_total[$k]/$total_rows,2).'%';
                                    }?>
                                <?php endif;?>)</th>
                            <?php endforeach;?>
                            <th>备注</th>
                            <th style="width: 150px;">编辑</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>

                            <tr>
                                <td><?=$v['lh_no']?></td>
                                <?php $detail_other = $v['detail_other'] ;?>
                                <?php foreach($v['detail'] as $k_k=>$v_v):?>
                                    <td <?php if($detail_other[$k_k]['is_warning']):?>style="color: red;"<?php endif ;?>><?=$v_v?></td>
                                <?php endforeach;?>
                                <td><?=$v['note']?></td>
                                <td>
                                    <?php if($v['is_warning'] && !$v['is_final']):?>
                                    <a class="btn btn-warning btn-xs J_checkauth" data-auth="/lh-statistics/edit" href="<?=url('/lh-statistics/edit?lh_no='.$v['lh_no'])?>"> <i class="ace-icon fa fa-warning bigger-100"></i> 预警备注</a>
                                    <?php endif ;?>
                                    <?php if(!$v['is_final']):?>
                                    <a class="btn btn-success btn-xs J_checkauth" data-auth="/lh-statistics/final-save" onclick="add_mark('<?=$v['lh_no']?>')"> <i class="ace-icon fa fa-success bigger-100"></i> 结案</a>
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
                <?php echo $this->renderFile('@app/views/common/pagenation.php',array('page_data'=>$page_data))?>
            </div>
            <!-- /.page-paging 结束 -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>
<script>
    // 添加标记
    function add_mark(lh_no){

        layer.confirm('确认结案?', function(index){

            layer.close(index);

            $.post(
                '/lh-statistics/final-save',
                {lh_no:lh_no},function(data){
                    var arr = eval('('+data+')');

                    if(arr.code == 1){
                        layer.alert(arr.msg,function(){
                            window.location.reload();
                        }) ;

                    }else{
                        layer.alert(arr.msg) ;
                    }
                }
            );



        });
        return false ;
    }
</script>
