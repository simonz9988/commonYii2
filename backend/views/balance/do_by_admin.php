<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->selectedLevel0Name?></a> </li>
                <li class="active"><?=$this->selectedLevel1Name?></li>
            </ul>
            <!-- /.breadcrumb -->
        </div>
    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">
    <div class="page-header">

        <div class="row">
            <div class="col-xs-6">
                <h1>手动核销
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/adminPage/exchange/doAddGoods" method="post">

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 卡号</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['card_no']:''?>"name="card_no" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 密码</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['card_password']:''?>"name="card_password" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 姓名</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['name']:''?>"name="name" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 手机号码</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['mobile']:''?>"name="mobile" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 座机号码</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['telphone']:''?>"name="telphone" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 省市区: </label>

                    <div class="col-sm-5 con-input">
                        <select class="col-xs-12 col-sm-3" id="province" name="province" onchange="onChangeProvince(this)" required>
                            <option value="">--请选择--</option><option value="110000">北京</option><option value="120000">天津</option><option value="130000">河北省</option><option value="140000">山西省</option><option value="150000">内蒙古自治区</option><option value="210000">辽宁省</option><option value="220000">吉林省</option><option value="230000">黑龙江省</option><option value="310000">上海</option><option value="320000">江苏省</option><option value="330000">浙江省</option><option value="340000">安徽省</option><option value="350000">福建省</option><option value="360000">江西省</option><option value="370000">山东省</option><option value="410000">河南省</option><option value="420000">湖北省</option><option value="430000">湖南省</option><option value="440000">广东省</option><option value="450000">广西壮族自治区</option><option value="460000">海南省</option><option value="500000">重庆</option><option value="510000">四川省</option><option value="520000">贵州省</option><option value="530000">云南省</option><option value="540000">西藏自治区</option><option value="610000">陕西省</option><option value="620000">甘肃省</option><option value="630000">青海省</option><option value="640000">宁夏回族自治区</option><option value="650000">新疆维吾尔自治区</option><option value="710000">台湾</option><option value="810000">香港特别行政区</option><option value="820000">澳门特别行政区</option><option value="900000">钓鱼岛</option>
                        </select>
                        <select class="col-xs-12 col-sm-3" id="city" name="city" onchange="onChangeCity(this)" required>
                        </select>
                        <select class="col-xs-12 col-sm-3" id="area" name="area" required>
                        </select>
                        <script>

                            function changeProvince(province_id){
                                $.post(
                                    '/site/ajaxGetCityOption',
                                    {id:province_id},function(data){
                                        $("#city").html(data);
                                        var city_id = $("#city").children(":eq(0)").attr("value") ;
                                        $.post(
                                            '/site/ajaxGetCityOption',
                                            {id:city_id},function(data){
                                                $("#area").html(data);
                                            }
                                        );
                                    }
                                );
                            }

                            function changeCity(city_id){

                                $.post(
                                    '/site/ajaxGetCityOption',
                                    {id:city_id},function(data){
                                        $("#area").html(data);
                                    }
                                );
                            }
                            changeProvince(110000) ;
                            function onChangeProvince(self){
                                var province_id = $(self).val();
                                changeProvince(province_id);
                            }

                            function onChangeCity(self){
                                var city_id = $(self).val();
                                changeCity(city_id);
                            }
                        </script>
                        <span class="middle reg_tip "></span>
                    </div>

                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip" id="url_reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">
                        </label>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 详细地址</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['address']:''?>"name="address" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 邮编</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['zip']:''?>"name="zip" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">时段</label>

                    <div class="col-sm-5">
                        <select name="tq_time" class="input-sm  col-xs-12 col-sm-9">
                            <option value="am" <?=$info&&$info['tq_time']=='am'?'selected="selected"':''?>>上午</option>
                            <option value="pm" <?=$info&&$info['tq_time']=='pm'?'selected="selected"':''?>>下午</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 日期: </label>

                    <div class="col-sm-5 con-input">
                        <select id="tq_year" name="tq_year" class="col-xs-12 col-sm-3" >
                            <?php $max_year = date("Y")+10 ;?>
                            <?php for($i = date('Y');$i<=$max_year;$i++):?>
                                <option value="<?=$i?>"><?=$i?></option>
                            <?php endfor ; ?>

                        </select>
                        <select id="tq_month"  name="tq_month"class="col-xs-12 col-sm-3" onchange="getDay(this)">
                            <?php for($i = 1;$i<=12;$i++):?>
                                <option value="<?=$i?>"><?=$i?></option>
                            <?php endfor ; ?>
                        </select>
                        <script>
                            function getDay(self){
                                var month = $(self).val();
                                var year = $("#tq_year").val();
                                $.post(
                                    '/site/getDay',
                                    {month:month,year:year},
                                    function(data){
                                        $('#tq_day').html(data);
                                    }
                                );
                            }
                        </script>
                        <select id="tq_day" name="tq_day" class="col-xs-12 col-sm-3">
                            <?php for($i = 1;$i<=31;$i++):?>
                                <option value="<?=$i?>"><?=$i?></option>
                            <?php endfor ; ?>
                        </select>

                        <span class="middle reg_tip "></span>
                    </div>

                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip" id="url_reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 备注</label>

                    <div class="col-sm-6">

                        <input value="<?=$info?$info['note']:''?>"name="note" class="col-xs-12 col-sm-12" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="clearfix form-actions">
                    <div class="col-md-offset-2 col-md-9">
                        <button class="btn btn-info" type="submit" id="submitButton">
                            <i class="ace-icon fa fa-check bigger-110"></i> 提交保存
                        </button>
                        <a class="btn btn-info" type="button" href="javascript:history.back()">
                            <i class="ace-icon fa fa-repeat bigger-110"></i> 返回
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
