<?php $admin_base_url = '/public/admin/';?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>后台系统</title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- basic styles -->

    <link href="<?=$admin_base_url?>assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/font-awesome.min.css" />

    <!--[if IE 7]>
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/font-awesome-ie7.min.css" />
    <![endif]-->

    <!-- page specific plugin styles -->

    <!-- fonts -->

    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/cyrillic.css" />

    <!-- ace styles -->

    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/ace.min.css" />
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/ace-rtl.min.css" />

    <!--[if lte IE 8]>
    <link rel="stylesheet" href="<?=$admin_base_url?>assets/css/ace-ie.min.css" />
    <![endif]-->

    <!-- inline styles related to this page -->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->

    <!--[if lt IE 9]>
    <script src="<?=$admin_base_url?>assets/js/html5shiv.js"></script>
    <script src="<?=$admin_base_url?>assets/js/respond.min.js"></script>
    <![endif]-->
</head>

<body class="login-layout">
<div class="main-container">
    <div class="main-content">
        <div class="row">
            <div class="col-sm-10 col-sm-offset-1">
                <div class="login-container">
                    <div class="center">
                        <h1>
                            <i class="icon-leaf green"></i>
                            <span class="red"></span>
                            <span class="white">后台管理系统</span>
                        </h1>
                        <h4 class="blue"></h4>
                    </div>

                    <div class="space-6"></div>

                    <div class="position-relative">
                        <div id="login-box" class="login-box visible no-border">
                            <div class="widget-body">
                                <div class="toolbar clearfix">
                                    <div>

                                    </div>

                                    <div>

                                    </div>
                                </div>
                                <div class="widget-main">
                                    <h4 class="header blue lighter bigger">
                                        <i class="icon-coffee green"></i>
                                        请输入用户名和密码
                                    </h4>

                                    <div class="space-6"></div>

                                    <form action="/site/dologin" method="post" id="form">
                                        <fieldset>
                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="text" name="username" class="form-control" placeholder="用户名" />
															<i class="icon-user"></i>
														</span>
                                            </label>

                                            <label class="block clearfix">
														<span class="block input-icon input-icon-right">
															<input type="password" name="password" class="form-control" placeholder="密码" />
															<i class="icon-lock"></i>
														</span>
                                            </label>



                                            <div class="space"></div>

                                            <div class="clearfix">
                                                <label class="inline">
                                                    <input type="checkbox" style="display:none"/>
                                                    <span class="lbl"> </span>
                                                </label>

                                                <button type="submit" id="submit"class="width-35 pull-right btn btn-sm btn-primary">
                                                    <i class="icon-key"></i>
                                                    登陆
                                                </button>
                                            </div>

                                            <div class="space-4"></div>
                                        </fieldset>
                                    </form>


                                </div><!-- /widget-main -->


                            </div><!-- /widget-body -->
                        </div><!-- /login-box -->
                    </div><!-- /position-relative -->
                </div>
            </div><!-- /.col -->
        </div><!-- /.row -->
    </div>
</div><!-- /.main-container -->

<!-- basic scripts -->

<!--[if !IE]> -->

<script src="<?=$admin_base_url?>assets/js/jquery-2.0.3.min.js"></script>

<!-- <![endif]-->

<!--[if IE]>
<script src="<?=$admin_base_url?>assets/js/jquery-1.10.2.min.js"></script>
<![endif]-->

<!--[if !IE]> -->

<script type="text/javascript">
    window.jQuery || document.write("<script src='<?=$admin_base_url?>assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!-- <![endif]-->

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='<?=$admin_base_url?>assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='<?=$admin_base_url?>assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<!-- inline scripts related to this page -->

<script type="text/javascript">
    function show_box(id) {
        jQuery('.widget-box.visible').removeClass('visible');
        jQuery('#'+id).addClass('visible');
    }

    $(function () {
        $('#form').bind('submit',function () {  //给form标签绑定submit事件
            var i=0;
            $("input").each(function(){  //遍历input标签，判断是否有内容未填写
                var vl=$(this).val();
                if(vl==""){
                    i=1;
                }
            });
            var t=$('textarea').val();  //判断textarea标签是否填写
            if (t=='') {
                i=1;
            }
            if (i==1) {  //如果有未填写的，则return false阻止提交
                alert('请将信息填写完整');
                return false;
            }
        });

    });
</script>
</body>
</html>
