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
            <a href="<?=url('/sunny-company/edit')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-device/fault-list" method="get">


                    <div class="form-group">
                        <label>分类名称：</label>
                        <select name="parent_id" class="input-sm  form-control" onchange="select_category_id(this)">
                            <option value="">请选择</option>
                            <?php if($filter_category_list):?>
                                <?php foreach($filter_category_list as $v):?>
                                    <option value="<?=$v['id']?>" <?=$searchArr&&$searchArr['parent_id']==$v['id']?'selected="selected"':''?>><?=$v['name']?></option>
                                <?php endforeach ; ?>
                            <?php endif ;?>
                        </select>

                        <select name="category_id" class="input-sm  form-control" id="search_category_id">
                            <option value="">请选择</option>
                            <?php if($filter_category_list):?>
                                <?php foreach($filter_category_list as $v):?>
                                    <?php if($v['son_list']):?>
                                        <?php foreach($v['son_list'] as $son_v):?>
                                            <option id="category-option-<?=$son_v['id']?>"class=" category-option category-option-<?=$v['id']?>" value="<?=$son_v['id']?>" <?=$searchArr&&$searchArr['category_id']==$son_v['id']?'selected="selected"':''?>><?=$son_v['name']?></option>
                                        <?php endforeach ; ?>
                                    <?php endif ;?>
                                <?php endforeach ; ?>
                            <?php endif ;?>
                        </select>
                    </div>
                    <script>
                        function select_category_id(self){
                            $(".category-option").hide();
                            $('#search_category_id option').prop("selected",'');
                            $(".category-option-"+$(self).val()).show();
                        }

                        <?php if($searchArr&&$searchArr['parent_id']):?>
                        $(function() {
                            $(".category-option").hide();
                            $(".category-option-<?=$searchArr['parent_id']?>").show();
                            <?php if($searchArr&&$searchArr['category_id']):?>
                            $("#category-option-<?=$searchArr['category_id']?>").attr("selected",true);
                            <?php endif ;?>
                        });
                        <?php else:?>
                        $(function() {
                            $(".category-option").hide();
                        });
                        <?php endif ;?>
                    </script>

                    <div class="form-group">
                        <label>设备编号：</label>
                        <input name="qr_code" value="<?=$searchArr?$searchArr['qr_code']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>开始时间：</label>
                        <input name="start_time" id="start_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate: '%y-%M-%d'})" value="<?=$searchArr?$searchArr['start_time']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>结束时间：</label>
                        <input name="end_time" onClick="WdatePicker({dateFmt:'yyyy-MM-dd', startDate: '%y-%M-%d',minDate:'#F{$dp.$D(\'start_time\')}'})" value="<?=$searchArr?$searchArr['end_time']:''?>" class="input-sm  form-control" type="text">
                    </div>


                    <input type="hidden" name="is_download" value="<?=$searchArr?$searchArr['is_download']:'0'?>">

                    <div class="form-group">
                        <button type="submit" class="btn btn-info btn-xs no-border" onclick="doSubmit()">
                            <i class="ace-icon fa fa-search bigger-120"></i>
                            <span class="bigger-120">搜索</span>
                        </button>

                        <button type="submit" class="btn btn-warning btn-xs no-border"onclick="doDownload()" style="display: none;">
                            <i class="ace-icon fa fa-download bigger-120"></i>
                            <span class="bigger-120">导出</span>
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
        <!-- /.row page-search -->

        <div class="clearfix"></div>
    </div>
    <!-- /.page-header -->



    <div class="row tables-wrapper">
        <div class="col-xs-12">
            <!-- PAGE CONTENT BEGINS  -->
            <div class="col-md-4 text-left">

            </div>

            <div class="table-responsive">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>设备编号</th>
                        <th>所属父级分类</th>
                        <th>所属分类</th>
                        <th>绑定客户</th>
                        <th>所属公司</th>
                        <th>具体错误原因</th>
                        <th>故障时间</th>
                        <th>是否处理</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['qr_code']?></td>
                                <td><?=$v['parent_id']?></td>
                                <td><?=$v['category_id']?></td>
                                <td><?=$v['customer_id']?></td>
                                <td><?=$v['company_id']?></td>
                                <td><?=$v['fault_list']?></td>
                                <td><?=$v['create_time']?></td>
                                <td><?=$v['is_deal']=='Y'?'是':'否'?></td>

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
    function add_mark(type,id){

        layer.confirm('确认新增标记?', function(index){

            layer.close(index);

            $.post(
                '/all-api-key/ajax-add-mark',
                {id:id,type:type},function(data){
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

    function close_all_trade(){

         $.post(
            '/all-api-key/ajax-close-all',
            {},function(data){
               var arr = eval('('+data+')');
               if(arr.code ==1){
                   window.location.reload();
               }
            }
        );
    }



    // 同步所有订单
    function sync_all_order(){

        layer.msg('加载中', {
            icon: 16
            ,shade: 10
        });

        $.post(
            '/all-api-key/ajax-sync-all-order',
            {admin_user_id:1},function(data){
                var arr = eval('('+data+')');
                if(arr.code ==1){
                    window.location.reload();
                }
            }
        );
    }

</script>