
<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->params['selectedLevel0Name']?></a> </li>
                <li class="active"><?=$this->params['selectedLevel1Name']?></li>
            </ul>
            <!-- /.breadcrumb -->
        </div>
        

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">

    <div class="row tables-wrapper">
        <div class="col-xs-12">
            
			<a href="#" style="width:200px;" class="btn btn-app btn-primary no-radius">
				<div>可用余额</div>
				<?=$total_left?>
				
			</a>
			
			<a href="#" style="width:200px;" class="btn btn-app btn-success no-radius">
				<div>今日成交总额</div>
				<?=$today_in_total?>
				
			</a>
			
			<a href="#" style="width:200px;" class="btn btn-app btn-warning no-radius">
				<div>今日出金总额</div>
				<?=$today_out_total?>
			</a>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

</div>

