<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */
use yii\helpers\Html;
?>

<div class="main-content">
    <div class="breadcrumbs" id="breadcrumbs">
        <script type="text/javascript">
            try {
                ace.settings.check('breadcrumbs', 'fixed')
            } catch (e) {
            }
        </script>
        <div class="row">
            <div class="col-md-8 col-xs-12">
                <ul class="breadcrumb ">
                    <li>
                        <i class="ace-icon fa fa-home home-icon"></i>
                        操作失败</li>
                </ul>
                <!-- /.breadcrumb -->
            </div>
            <div class="col-md-4 text-right">

            </div>
        </div>
    </div>

    <div class="page-content">
        <div class="page-content-area">
            <div class="row">
                <div class="col-xs-12">
                    <!-- PAGE CONTENT BEGINS -->

                    <!-- #section:pages/error -->
                    <div class="error-container">
                        <div class="well">
                            <h1 class="red lighter smaller">
                                <i class="ace-icon fa fa-times bigger-125"></i>
                                <?= $code?>
                            </h1>

                            <hr>
                            <h3 class="lighter smaller red"><?= nl2br(Html::encode($message)) ?></h3>

                            <div>


                                <div class="space-12"></div>
                                <h4 class="smaller ">您可以尝试如下操作:</h4>

                                <ul class="list-unstyled spaced inline bigger-110 margin-15">
                                    <li>
                                        <i class="ace-icon fa fa-hand-o-right blue"></i>
                                        刷新页面重试
                                    </li>

                                    <li>
                                        <i class="ace-icon fa fa-hand-o-right blue"></i>
                                        联系技术人员
                                    </li>


                                </ul>
                            </div>

                            <hr>
                            <div class="space"></div>

                            <div class="center">
                                <a href="javascript:history.back()" class="btn btn-grey">
                                    <i class="ace-icon fa fa-arrow-left"></i>
                                    返回
                                </a>

                                <a href="/" class="btn btn-primary">
                                    <i class="ace-icon fa fa-tachometer"></i>
                                    首页
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- /section:pages/error -->

                    <!-- PAGE CONTENT ENDS -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
    </div>
    <!-- /.page-content -->
</div>