<?php
$admin_base_url = '/public/admin/';
$admin_assets_url = '/public/ace_static/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta charset="utf-8">
    <title>管理后台-<?=WEBSITE_NAME?></title>
    <meta name="description" content="overview &amp; stats">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <!-- bootstrap & fontawesome -->
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/font-awesome.min.css" />

    <!-- ace styles -->
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/ace.min.css">

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/ace-part2.min.css" />
    <![endif]-->
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/ace-skins.min.css">
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/ace-rtl.min.css">

    <!--[if lte IE 9]>
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/ace-ie.min.css" />
    <![endif]-->

    <!-- inline styles related to this page -->

    <!-- ace settings handler -->
    <script src="<?=$admin_base_url?>assets/js/ace-extra.min.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

    <!--[if lte IE 8]>
    <script src="<?=$admin_base_url?>assets/js/html5shiv.js"></script>
    <script src="<?=$admin_base_url?>assets/js/respond.min.js"></script>
    <![endif]-->

    <!--[if !IE]> -->
    <script src="<?=$admin_base_url?>assets/js/jquery-2.1.1.min.js"></script>

    <!-- <![endif]-->

    <!--[if IE]>
    <script src="<?=$admin_base_url?>assets/js/jquery-1.11.0.min.js"></script>
    <![endif]-->


    <?php
    if(isset($this->params['resource']['css'] )){
        foreach($this->params['resource']['css'] as $v){
            ?>
            <link href="<?php echo $admin_assets_url; ?>/css/<?php echo $v ;?>.css" rel="stylesheet" />
        <?php }}?>

    <?php
    if(isset($this->params['resource']['plugin_css'] )){
        foreach($this->params['resource']['plugin_css'] as $v){
            ?>
            <link href="<?php echo $admin_assets_url; ?>/plugins/<?php echo $v ;?>.css" rel="stylesheet" />
        <?php }}?>



    <?php
    if(isset($this->params['resource']['header_js'] )){
        foreach($this->params['resource']['header_js'] as $v){
            ?>
            <script src="<?php echo $admin_assets_url; ?>/js/<?php echo $v ;?>.js"></script>
        <?php }}?>
    <script src="<?php echo $admin_assets_url; ?>/js/base_function.js"></script>
    <script type="text/javascript">

        //文件上传地址
        var upload_url = '/file/do-upload';
        //上传images 允许类型
        var image_allowed_extensions = '<?php $arr= uploadFileProp("images") ; echo $arr["allow"][0] ?>';
        //允许上传的压缩包的类型
        var zip_allowed_extensions = '<?php $arr= uploadFileProp("zip") ; echo $arr["allow"][0] ?>';
        //允许上传的pdf
        var pdf_allowed_extensions = '<?php $arr= uploadFileProp("pdf") ; echo $arr["allow"][0] ?>';
        //允许上传的文件大小
        var max_file_size = '<?php $arr= uploadFileSizeProp("maxsize") ; echo $arr["js"]?>';
        var excel_allowed_extensions  = 'xls';

        var admin_assets_url = '<?=$admin_assets_url?>';


        <?php if(isset($this->zNodes) && $this->zNodes){
        if(isset($this->addAllNode) && $this->addAllNode){
        ?>
        var zNodes_str = '<?php echo $this->addAllNode; ?>';
        <?php }else{?>
        var zNodes_str = '<?php echo Privilege::model()->allNode();?>';
        <?php }?>
        var zNodes = eval('('+zNodes_str+')');
        <?php }?>


        var mh_max_num = 50000000;
        var mh_max_num_notice = '最大50000000';
        var mh_min_num_notice = '小数点后最多为两位';

    </script>
    <style>
        table{table-layout: fixed;}
        td{word-break: break-all; word-wrap:break-word;}
    </style>

</head>

<body class="no-skin" screen_capture_injected="true">
<?php if(!$this->params['isEmpty']):?>
    <div id="navbar" class="navbar navbar-default  navbar-collapse  h-navbar navbar-fixed-top">
        <div class="navbar-container" id="navbar-container">
            <div class="navbar-header pull-left">
                <button type="button" class="navbar-toggle menu-toggler pull-left display" id="menu-toggler" data-target="#sidebar">
                    <span class="sr-only">左侧菜单</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a href="#" class="navbar-brand">
                    <img src="<?=$admin_base_url?>assets/images/gallery/logo.png" class="hidden-xs"/>
                </a>

                <button class="pull-right navbar-toggle navbar-toggle-img collapsed" id="btn-collapsed" type="button" data-toggle="collapse" data-target=".navbar-buttons,.navbar-menu">
                    <span class="sr-only">用户菜单</span>
                    <img src="<?=$admin_base_url?>assets/avatars/user.jpg" alt="">
                    <i class="fa fa-angle-down"></i>
                </button>
            </div>

            <nav role="navigation" class="navbar-menu pull-right collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <?php if($this->params['allAllowedMenusCate']):?>
                        <?php foreach($this->params['allAllowedMenusCate'] as $k=>$v):?>
                            <li class="<?=$this->params['selectedLevel0MenuUniqueKey']==$k?'active nav_li':'nav_li'?>">
                                <?php if($k=='gismap'):?>
                                    <a href="/sunny-device/gis" target="_blank">
                                        <?=$v['menu_cate_name']?>
                                        <i class="ace-icon fa fa-angle-down bigger-110"></i>
                                    </a>
                                <?php else:?>
                                    <a href="javascript:void(0);" onclick="check_li('<?=$k?>')" class="dropdown-toggle" data-toggle="dropdown">
                                        <?=$v['menu_cate_name']?>
                                        <i class="ace-icon fa fa-angle-down bigger-110"></i>
                                    </a>
                                <?php endif ;?>

                            </li>
                        <?php endforeach ;?>
                    <?php endif;?>
                    <script>
                        function check_li(type){
                            $(".nav_li").removeClass("active");
                            $(this).parent().addClass('active');
                            $(".li_menu").hide();
                            $(".li_menu_"+type).show();
                            $(".navbar-menu").removeClass("in");
                            $("#menu-toggler").addClass("display");
                            $("#sidebar").addClass("display");
                        }
                    </script>

                    <li class="">
                        <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                            <img class="nav-user-photo hidden-xs hidden-sm" src="<?=$admin_base_url?>assets/avatars/user.jpg" alt=""><span class="visible-xs visible-sm pull-left">admin</span>
                            <i class="ace-icon fa fa-angle-down bigger-110"></i>
                        </a>

                        <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret">
                            <li>
                                <a href="#modal5" role="button" data-toggle="modal">
                                    <i class="ace-icon fa fa-cog"></i>
                                    密码修改
                                </a>
                            </li>



                            <li class="divider"></li>

                            <li>
                                <a href="/site/logout">
                                    <i class="ace-icon fa fa-power-off"></i>
                                    退出登录
                                </a>
                            </li>
                        </ul>
                    </li>

                </ul>

            </nav>
        </div>
        <!-- /.navbar-container -->
    </div>
<?php endif ;?>
<div class="main-container" id="main-container">
    <?php if(!$this->params['isEmpty']):?>
        <div id="sidebar" class="sidebar  responsive sidebar-fixed">
            <ul class="nav nav-list">
                <?php if($this->params['adminMenuList']):?>
                    <?php foreach($this->params['adminMenuList'] as $v):?>
                        <?php
                        $menus=isset($v['menus'])?$v['menus']:array();
                        $li_class_name = 'li_menu li_menu_'.$v['menu_cate_unique_key'];
                        $li_style = 'display:block';

                        if($v['menu_cate_unique_key']!=$this->params['selectedLevel0MenuUniqueKey']){
                            $li_style = 'display:none';
                        }
                        ?>

                        <li class="hsub  <?=$v['unique_key']==$this->params['selectedLevel0Key']?'active open '.$li_class_name:$li_class_name?> " style="<?=$li_style?>">
                            <a href="javascript:void(0);" class="dropdown-toggle">
                                <i class="menu-icon fa fa-users"></i>
                                <span class="menu-text"><?=$v['menuname']?></span>
                                <b class="arrow fa fa-angle-down"></b></a>
                            <b class="arrow"></b>
                            <?php if($menus):?>
                                <ul class="submenu" style="display: block;">
                                    <?php  foreach($menus as $menu_v ):?>
                                        <li  class="<?=$menu_v['unique_key']==$this->params['selectedLevel1Key']?'active':''?>">
                                            <a href="<?=$menu_v['url']?>">
                                                <i class="menu-icon fa fa-caret-right"></i><?=$menu_v['menuname']?></a>
                                            <b class="arrow"></b>
                                        </li>
                                    <?php endforeach;?>
                                </ul>
                            <?php endif ;?>
                        </li>
                    <?php endforeach;?>
                <?php endif ;?>
            </ul><!-- /.nav-list -->
            <div class="sidebar-toggle sidebar-collapse " id="sidebar-collapse" style=" cursor:pointer"> <i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i></div>
        </div>
    <?php endif ;?>
    <!-- #EndLibraryItem -->
    <div <?php if(!$this->params['isEmpty']):?>class="main-content"<?php endif ;?>>
        <?=$content?>
    </div>
    <?php if(!$this->params['isEmpty']):?>
    <!-- /.main-content -->
    <div class="footer">
        <div class="footer-inner">
            <div class="footer-content"> <span class=" footer_font"> Copyright @ 2018  </span> </div>
        </div>
    </div>
    <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse"> <i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i></a> <!-- #EndLibraryItem --></div>
    <!-- /.main-container -->
<?php endif ;?>
<!-- basic scripts -->
<!--弹出框样式05-->
<div id="modal5" class="modal">
    <div class="modal-dialog">
        <div id="login-box" class="login-box visible widget-box no-border" style="height:auto;">
            <div class="widget-body">
                <div class="widget-main">
                    <h4 class="header blue lighter bigger-130"> <i class="ace-icon fa fa-coffee "></i> 修改登录密码 </h4>
                    <form class="form-horizontal" role="form" id="editPasswordForm" onsubmit="return false ">
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right text-right" for="password">登录用户名:</label>
                            <div class="col-xs-12 col-sm-9">
                                <div class="clearfix">
                                    <p class="form-control-static"><?=$this->params['adminUserInfo']['username']?> </p>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right  text-right" for="password">原密码:</label>
                            <div class="col-xs-12 col-sm-9">
                                <div class="clearfix">
                                    <input type="password" name="password" id="password" class="col-xs-10" aria-required="true" aria-invalid="false">
                                </div>
                                <div for="password2" class="help-block"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right  text-right" for="password2">新密码:</label>
                            <div class="col-xs-12 col-sm-9">
                                <div class="clearfix">
                                    <input type="password" name="password2" id="password2" class="col-xs-10" aria-required="true" aria-invalid="true">
                                </div>
                                <div for="password2" class="help-block"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right  text-right" for="password2">确认新密码:</label>
                            <div class="col-xs-12 col-sm-9">
                                <div class="clearfix">
                                    <input type="password" name="repeartpassword2" placeholder="请再次输入密码" id="repeartpassword2" class="col-xs-10" aria-required="true" aria-invalid="true">
                                </div>
                                <div for="password2" class="help-block"></div>
                            </div>
                        </div>
                        <div class="header blue lighter "> </div>
                        <div class="form-group">
                            <label class="control-label col-xs-12 col-sm-3 no-padding-right  text-right"></label>
                            <div class="col-xs-12 col-sm-9">
                                <button class="btn btn-info width-65" type="submit" id="passwordSubmitButton"> 确认修改 <i class="ace-icon fa fa-arrow-right icon-on-right"></i> </button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.widget-main -->

            </div>
        </div>

    </div>
</div>

<!--弹出框样式05 end-->




<!--[if !IE]> -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='<?=$admin_base_url?>assets/js/jquery.min.js'>"+"<"+"/script>");
</script>

<!-- <![endif]-->

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='<?=$admin_base_url?>assets/js/jquery1x.min.js'>"+"<"+"/script>");
</script>
<![endif]-->
<script type="text/javascript">
    if('ontouchstart' in document.documentElement) document.write("<script src='<?=$admin_base_url?>assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>
<script src="<?=$admin_base_url?>assets/js/bootstrap.min.js"></script>

<!-- page specific plugin scripts -->

<!--[if lte IE 8]>
<script src="<?=$admin_base_url?>assets/js/excanvas.min.js"></script>
<![endif]-->
<script src="<?=$admin_base_url?>assets/js/jquery-ui.custom.min.js"></script>
<script src="<?=$admin_base_url?>assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="<?=$admin_base_url?>assets/js/jquery.easypiechart.min.js"></script>
<script src="<?=$admin_base_url?>assets/js/jquery.sparkline.min.js"></script>
<script src="<?=$admin_base_url?>assets/js/flot/jquery.flot.min.js"></script>
<script src="<?=$admin_base_url?>assets/js/flot/jquery.flot.pie.min.js"></script>
<script src="<?=$admin_base_url?>assets/js/flot/jquery.flot.resize.min.js"></script>

<!-- ace scripts -->
<script src="<?=$admin_base_url?>assets/js/ace-elements.min.js"></script>
<script src="<?=$admin_base_url?>assets/js/ace.min.js"></script>
<script src="<?=$admin_base_url?>assets/js/common.js"></script>
<script src="<?=$admin_base_url?>assets/js/bootbox.min.js"></script>

<!-- inline scripts related to this page -->



<?php
if(isset($this->params['resource']['plugin_js'])){
    foreach($this->params['resource']['plugin_js']  as $v){
        ?>
        <script src="<?php echo $admin_assets_url; ?>/plugins/<?php echo $v ;?>.js"></script>
        <?php
    }
}
?>

<?php
if(isset($this->params['resource']['footer_js'])){
    foreach($this->params['resource']['footer_js']  as $v){


        ?>
        <script src="<?php echo $admin_assets_url; ?>/js/<?php echo $v ;?>.js"></script>
        <?php
    }
}
?>

<style>
    .J_checkauth{
        display: none !important;
    }
</style>
<script>
    $(function () {
        var isSuper = <?=$this->params['is_super']?>;

        var authList = <?=json_encode($this->params['auth_menus'])?>;
        //示例 <button type="button" class="J_checkauth" data-auth="shop_request_sn_bind_save">删除</button>

        $(".J_checkauth").each(function () {
            var $this = $(this);
            var auth = $this.data('auth')!==undefined ? $.trim($this.data('auth')) : '';

            var auth_arr = auth.split("|");
            var has_auth = false;

            if(isSuper){
                has_auth = true;
            }else{

                var no_auth = false;
                //console.log(auth_arr);
                //console.log(authList);
                for(i in auth_arr){

                    if(authList.indexOf(auth_arr[i])<0){
                        no_auth = true;

                    }
                }

                if(!no_auth){
                    has_auth = true;
                }
            }

            if(has_auth){
                $this.removeClass('J_checkauth');
            }else{
                $this.remove();
            }

        });


        $('form').attr('autocomplete','off');
    })
</script>
</body>
</html>
