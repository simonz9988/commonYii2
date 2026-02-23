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
            <?php if(checkAdminPrivilege('/member/edit1')):?>
            <a href="<?=url('/member/edit1')?>" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
            <?php endif ;?>
        </div>
    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="page-header">

        <div class="row">
            <div class="col-xs-12 page-search">
                <form class="form-inline" id="shippingOrderSearchForm" action="/member/list" method="get">

                    <div class="form-group">
                        <label>用户手机：</label>
                        <input name="mobile" value="<?=$searchArr?$searchArr['mobile']:''?>" class="input-sm  form-control" type="text">
                    </div>
                    <div class="form-group">
                        <label>用户邮箱：</label>
                        <input name="mobile" value="<?=$searchArr?$searchArr['email']:''?>" class="input-sm  form-control" type="text">
                    </div>

                    <div class="form-group">
                        <label>用户类型：</label>
                        <select name="type">
                            <option value="">请选择</option>
                            <option value="PERSON" <?=$searchArr&&$searchArr['type']=='PERSON'?'selected="selected"':''?>>普通用户</option>
                            <option value="SPECIAL" <?=$searchArr&&$searchArr['type']=='SPECIAL'?'selected="selected"':''?>>特殊用户</option>
                        </select>
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
                        <th>用户名</th>
                        <th>手机号</th>
                        <th>邮箱</th>
                        <th>邀请码</th>
                        <th>邀请人</th>
                        <th>用户层级</th>
                        <th>注册时间</th>
                        <th>账户余额</th>
                        <th>冻结余额</th>
                        <th>可用代币数量</th>
                        <th>最近登录时间</th>
                        <th>注册来源</th>
                        <th width="250px;">USDT操作</th>
                        <th width="150px;">USDT冻结</th>
                        <th width="150px;">TOKEN操作</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if($list):?>
                            <?php foreach($list as $v):?>
                            <tr>
                                <td><?=$v['id']?></td>
                                <td><?=$v['username']?></td>
                                <td><?=$v['mobile']?></td>
                                <td><?=$v['email']?></td>
                                <td><?=$v['invite_code']?></td>
                                <td><?=$v['inviter_username']?></td>
                                <td><?=$v['user_level']?></td>
                                <td><?=$v['create_time']?></td>
                                <td><?=$v['balance']?>USDT</td>
                                <td><?=$v['frozen_balance']?>USDT</td>
                                <td><?=$v['robot_balance']?></td>
                                <td><?=$v['last_login']?></td>
                                <td><?=$v['reg_from']=='FRONT'?'前台':'后台'?></td>
                                <td>
                                    <?php if($v['type'] =='SPECIAL'):?>
                                    <a class="btn small btn-primary btn-xs" href="javascript:void(0);" onclick="set_user_type(<?=$v['id']?>,'N')"> <i class="ace-icon fa fa-edit bigger-100"></i> 设为普通</a>
                                    <?php else:?>
                                    <a class="btn small btn-xs btn-danger cus-btn-del" href="javascript:void(0);" onclick="set_user_type(<?=$v['id']?>,'Y')"">
                                        <i class="ace-icon fa fa-trash-o"></i>设为特殊
                                    </a>
                                    <?php endif ;?>

                                    <a class="btn btn-xs btn-success" href="javascript:void(0);" onclick="add_balance(<?=$v['id']?>,'add')"">
                                     增加余额
                                    </a>

                                    <a class="btn btn-xs btn-warning" href="javascript:void(0);" onclick="add_balance(<?=$v['id']?>,'reduce')"">
                                    扣减余额
                                    </a>
                                </td>

                                <td>
                                    <a class="btn btn-xs btn-success" href="javascript:void(0);" onclick="add_frozen_balance(<?=$v['id']?>,'add')"">
                                    增加冻结
                                    </a>
                                    <a class="btn btn-xs btn-warning" href="javascript:void(0);" onclick="add_frozen_balance(<?=$v['id']?>,'reduce')"">
                                    释放冻结
                                    </a>
                                </td>

                                <td>
                                    <a class="btn btn-xs btn-success" href="javascript:void(0);" onclick="add_token_balance(<?=$v['id']?>,'add')"">
                                    增加余额
                                    </a>
                                    <a class="btn btn-xs btn-warning" href="javascript:void(0);" onclick="add_token_balance(<?=$v['id']?>,'reduce')"">
                                    扣减余额
                                    </a>
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
    function close_all_trade(){

         $.post(
            '/api-key/ajax-close-all',
            {},function(data){
               var arr = eval('('+data+')');
               if(arr.code ==1){
                   window.location.reload();
               }
            }
        );
    }

    function add_balance(user_id,type){
        layer.prompt(function(val, index){
            $.post(
                '/member/add-balance',
                {user_id:user_id,total:val,type:type},function(data){
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

    function add_token_balance(user_id,type){
        layer.prompt(function(val, index){
            $.post(
                '/member/add-token-balance',
                {user_id:user_id,total:val,type:type},function(data){
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

    // 增加冻结
    function add_frozen_balance(user_id,type){
        layer.prompt(function(val, index){
            $.post(
                '/member/add-frozen-balance',
                {user_id:user_id,total:val,type:type},function(data){
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
    function set_user_type(user_id,is_special){


        var r=confirm("确认设置?");
        if (r==true)
        {
            $.post(
                '/member/save-special',
                {user_id:user_id,is_special:is_special},function(data){
                    var arr = eval('('+data+')');
                    if(arr.code ==1){
                        window.location.reload();
                    }
                }
            );
        }
        else
        {

        }

    }

    function sync_order(admin_user_id){

        layer.msg('加载中', {
            icon: 16
            ,shade: 10
        });

        $.post(
            '/api-key/ajax-sync-order',
            {admin_user_id:admin_user_id},function(data){
                var arr = eval('('+data+')');
                if(arr.code ==1){
                    window.location.reload();
                }
            }
        );
    }
</script>