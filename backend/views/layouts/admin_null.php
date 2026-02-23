<?php
$admin_base_url = '/static/admin/';
$admin_assets_url = '/ace_static/';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta charset="utf-8">
    <title><?=WEBSITE_NAME?></title>
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
    if(isset($this->resource['css'] )){
        foreach($this->resource['css'] as $v){
            ?>
            <link href="<?php echo $admin_assets_url; ?>/css/<?php echo $v ;?>.css" rel="stylesheet" />
        <?php }}?>

    <?php
    if(isset($this->resource['plugin_css'] )){
        foreach($this->resource['plugin_css'] as $v){
            ?>
            <link href="<?php echo $admin_assets_url; ?>/plugins/<?php echo $v ;?>.css" rel="stylesheet" />
        <?php }}?>



    <?php
    if(isset($this->resource['header_js'] )){
        foreach($this->resource['header_js'] as $v){
            ?>
            <script src="<?php echo $admin_assets_url; ?>/js/<?php echo $v ;?>.js?v=<?=JS_VERSION?>"></script>
        <?php }}?>
    <script src="<?php echo $admin_assets_url; ?>/js/base_function.js?v=<?=JS_VERSION?>"></script>
    <script type="text/javascript">
        //文件上传地址
        var upload_url = '<?php echo Base::model()->uploadUrl() ;?>';
        //上传images 允许类型
        var image_allowed_extensions = '<?php $arr= Base::model()->uploadFileProp("images") ; echo $arr["allow"][0] ?>';
        //允许上传的压缩包的类型
        var zip_allowed_extensions = '<?php $arr= Base::model()->uploadFileProp("zip") ; echo $arr["allow"][0] ?>';
        //允许上传的pdf
        var pdf_allowed_extensions = '<?php $arr= Base::model()->uploadFileProp("pdf") ; echo $arr["allow"][0] ?>';
        //允许上传的文件大小
        var max_file_size = '<?php $arr=Base::model()->uploadFileSizeProp("maxsize") ; echo $arr["js"]?>';

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

    </script>
    <style>
        #navbar a{display:block;text-align: center;line-height:65px;height:100%;margin:auto 0 ;color: #ffffff;font-size: 24px;font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif;}
    </style>

</head>

<body class="no-skin" screen_capture_injected="true">
<div id="navbar" class="navbar navbar-default  navbar-collapse  h-navbar navbar-fixed-top">
   <a  href="javascript:void(0);"><?=$this->content_title?></a>
    <!-- /.navbar-container -->
</div>

<div class="main-container" id="main-container">

    <!-- #EndLibraryItem -->
    <div class="main-content">
        <?=$content?>
    </div>

    <!-- /.main-content -->
    <div class="footer">
        <div class="footer-inner">
            <div class="footer-content"> <span class=" footer_font"> Copyright @ 2018 <?=WEBSITE_NAME?>版权所有! <a target="_blank" href="http://www.miibeian.gov.cn">粤ICP备18046118号-1</a></span> </div>
        </div>
    </div>
    <a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse"> <i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i></a> <!-- #EndLibraryItem --></div>
<!-- /.main-container -->

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
                                    <p class="form-control-static"></p>
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
if(isset($this->resource['plugin_js'])){
    foreach($this->resource['plugin_js']  as $v){
        ?>
        <script src="<?php echo $admin_assets_url; ?>/plugins/<?php echo $v ;?>.js?v=<?=JS_VERSION?>"></script>
        <?php
    }
}
?>

<?php
if(isset($this->resource['footer_js'])){
    foreach($this->resource['footer_js']  as $v){


        ?>
        <script src="<?php echo $admin_assets_url; ?>/js/<?php echo $v ;?>.js?v=<?=JS_VERSION?>"></script>
        <?php
    }
}
?>
</body>
</html>
