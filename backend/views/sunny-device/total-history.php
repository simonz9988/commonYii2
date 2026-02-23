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
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-device/total-history" method="get">

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
                        <input name="device_no" value="<?=$searchArr?$searchArr['device_no']:''?>" class="input-sm  form-control" type="text">
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
            <div class="col-md-4 text-left">

            </div>

            <div class="table-responsive">

                <table id="table-1" class="table table-striped table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>设备类型</th>
                        <th>设备编码</th>
                        <th>设备名称</th>
                        <th>客户</th>
                        <th>总运行天数</th>
                        <th>总过放次数</th>
                        <th>总充满次数</th>
                        <th>总充电安时数</th>
                        <th>总放电安时数</th>
                        <th>累计发电量</th>
                        <th>累计用电量</th>
                        <th>负载状态</th>
                        <th>亮度</th>
                        <th>充电状态</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $v):?>
                            <tr>

                                <td><?=$v['id']?></td>
                                <td><?=$v['category_name']?></td>
                                <td><a href="/sunny-device/status-list?not_group_by=Y&device_no=<?=$v['device_info']['qr_code']?>"><?=$v['device_info']['qr_code']?></a></td>
                                <td><?=$v['device_info']['device_name']?></td>
                                <td><?=$v['company_info']?$v['company_info']['company_name']:''?></td>
                                <td><?=$v['work_days_total']?></td>
                                <td><?=$v['bat_over_discharge_time']?></td>
                                <td><?=$v['bat_over_charge_time']?></td>
                                <td><?=$v['bat_charge_an_total']?></td>
                                <td><?=$v['bat_discharge_an_total']?></td>
                                <td><?=$v['generat_energy_total']?></td>
                                <td><?=$v['used_energy_total']?></td>
                                <td><?=$v['load_status']?></td>
                                <td><?=$v['brightness']?></td>
                                <td><?=$v['charge_status']?></td>
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

    function addBatch(category_id){
        layer.prompt({"title":'请输入新增设备的数目'},function(val, index){
            $.post(
                '/sunny-device-category/batch-add',
                {category_id:category_id,total:val},function(data){
                    var arr = eval('('+data+')');
                    if(arr.code ==1){
                        window.location.reload();
                    }else{
                        alert(arr.msg);
                    }
                }
            );
        });
    }

</script>