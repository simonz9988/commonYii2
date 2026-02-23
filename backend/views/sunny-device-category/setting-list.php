<script src="/public/js/layer/layer.js"></script>
<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-4 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->params['selectedLevel0Name']?></a> </li>
                <li class="active"><?=$this->params['selectedLevel1Name']?></li>
            </ul>
            <!-- /.breadcrumb -->
        </div>
        <div class="col-md-8 text-right" >
            <a href="javascript:void(0);" onclick="setting_category_switch('ON')" class="btn btn-success btn-sm no-border"> 打开开关</a>
            <a href="javascript:void(0);" onclick="setting_category_switch('OFF')" class="btn btn-danger btn-sm no-border"> 关闭开关</a>
            <a href="javascript:void(0);" onclick="setting_category_light()" class="btn btn-warning btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 设置亮度</a>
            <a href="javascript:void(0);" onclick="setting_others()" class="btn btn-inverse btn-sm no-border">亮灯分段设置</a>
            <a href="javascript:void(0);" onclick="setting_battery_params()" class="btn btn-info btn-sm no-border">蓄电池参数</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/sunny-device-category/setting-list" method="get">


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
                        <th>选择</th>
                        <th>ID</th>
                        <th>父类名称</th>
                        <th>分类名称</th>
                        <th>设备名称</th>
                        <th>设备编码</th>
                        <th>亮度</th>
                        <th>开关状态</th>
                        <th>修改时间</th>
                        <th>分段信息</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if($list):?>
                        <?php foreach($list as $v):?>
                            <tr>
                                <td><input type="checkbox" name="row_ids[]" value="<?=$v['id']?>"></td>
                                <td><?=$v['id']?></td>
                                <td><?=$v['parent_category_name']?></td>
                                <td><?=$v['category_name']?></td>
                                <td><?=$v['device_name']?></td>
                                <td><?=$v['qr_code']?></td>
                                <td><?=$v['brightness']?$v['brightness']:0?>%</td>
                                <td><?=$v['switch_status']=='Y'?'开':'关'?></td>
                                <td><?=$v['modify_time']?></td>
                                <td>
                                    <a class="btn btn-success btn-xs" href="<?=url('/sunny-device/time-list?id='.$v['id'])?>"> <i class="ace-icon fa fa-eye bigger-100"></i>查看时段</a>
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
    function setting_category_light(){


        layer.confirm('是否确认设置亮度?', function(index){

            var selected_product_ids  = '';
            $('input[name*=\'row_ids\']').each(function(){
                // 判断是否选中
                if($(this).is(":checked")){
                    selected_product_ids += $(this).val()+"_";
                }
            });

            if(selected_product_ids ==''){
                layer.alert("请选择需要设置的分类");
                return false ;
            }

            layer.prompt({title:'请输入亮度数值'},function(val, index){
                layer.close(index);

                $.post(
                    '/sunny-device-category/setting-save',
                    {content:val,type:"light",ids:selected_product_ids},function(data){
                        var arr = eval('('+data+')');
                        if(arr.code ==1){
                            alert("设置成功")
                            window.location.reload();
                        }else{
                            alert(arr.msg);
                        }
                    }
                );
            });



        });
        return false ;
    }

    function setting_category_switch(type){
        var alert_msg = "";
        if(type =="ON"){
            alert_msg = "是否确认打开开关?";
        }else{
            alert_msg = "是否确认关闭开关?";
        }
        layer.confirm('是否确认设置开关状态?', function(index){

            var selected_product_ids  = '';
            $('input[name*=\'row_ids\']').each(function(){
                // 判断是否选中
                if($(this).is(":checked")){
                    selected_product_ids += $(this).val()+"_";
                }
            });

            if(selected_product_ids ==''){
                layer.alert("请选择需要设置的分类");
                return false ;
            }
            layer.close(index);

            layer.prompt({title: '请输入时长(单位：分)'}, function (val, index) {
                layer.close(index);

                $.post(
                    '/sunny-device-category/setting-save',
                    {content: type, type: "switch", minute: val, ids: selected_product_ids}, function (data) {
                        var arr = eval('(' + data + ')');
                        if (arr.code == 1) {
                            alert("设置成功")
                            window.location.reload();
                        } else {
                            alert(arr.msg);
                        }
                    }
                );
            });

        });
        return false ;
    }

    function setting_others(){
        var alert_msg = "";

        layer.confirm('是否确认设置?', function(index){

            var selected_product_ids  = '';
            $('input[name*=\'row_ids\']').each(function(){
                // 判断是否选中
                if($(this).is(":checked")){
                    selected_product_ids += $(this).val()+"_";
                }
            });

            if(selected_product_ids ==''){
                layer.alert("请选择需要设置的设备");
                return false ;
            }

            location.href ="/sunny-device/setting-other?ids="+selected_product_ids

        });
        return false ;
    }

    function setting_battery_params(){
        var alert_msg = "";

        layer.confirm('是否确认设置?', function(index){

            var selected_product_ids  = '';
            $('input[name*=\'row_ids\']').each(function(){
                // 判断是否选中
                if($(this).is(":checked")){
                    selected_product_ids += $(this).val()+"_";
                }
            });

            if(selected_product_ids ==''){
                layer.alert("请选择需要设置的设备");
                return false ;
            }

            location.href ="/sunny-device/setting-battery-params?ids="+selected_product_ids

        });
        return false ;
    }


</script>