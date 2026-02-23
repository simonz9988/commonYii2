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
    <div class="page-header" style="display: none;">

        <div class="row">
            <div class="col-xs-6">
                <h1>新增/编辑出金
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/cash/save-out" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">
                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 币种</label>

                    <div class="col-sm-5">
                        <select name="coin_type" class="input-sm  col-xs-12 col-sm-6">
                            <option value="">请选择</option>
                            <?php foreach($coin_type_list as $k=>$v){?>
                                <option value="<?=$k?>"><?=$v?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 出金金额</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['amount']:''?>"name="amount" placeholder="请填写出金金额,最少100,不能大于等于50000" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 银行卡号</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['bank_no']:''?>"name="bank_no" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 开户姓名</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['bank_account']:''?>"name="bank_account" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 银行名称</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['bank_name']:''?>"name="bank_name" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 开户行地址</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['bank_detail']:''?>"name="bank_detail" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 所在城市</label>

                    <div class="col-sm-5">
                        <select name="province" class="input-sm  col-xs-3 col-sm-6" onchange="getCityList(this)">
                            <?php foreach($province_list as $k=>$v){?>
                                <option value="<?=$v['area_id']?>"><?=$v['area_name']?></option>
                            <?php } ?>
                        </select>
                        <script>
                            function getCityList(self){
                                var province_id = $(self).val();
                                $.post(
                                    '/cash/get-city-list',
                                    {province_id:province_id},
                                    function(data){
                                        var arr = eval('('+data+')');
                                        var str = arr.data ;
                                        $("#select_city").empty().html(str);
                                    }
                                );
                            }
                        </script>

                        <select name="city" id="select_city" class="input-sm  col-xs-3 col-sm-6">
                            <?php foreach($city_list as $k=>$v){?>
                                <option value="<?=$v['area_id']?>"><?=$v['area_name']?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 验证码</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['code']:''?>"name="code" class="col-xs-12 col-sm-6" type="text">
                        <a href="javascript:void(0);" onclick="sendCode('CASH_OUT')" class="sendVerifyCode btn btn-primary btn-sm no-border">发送验证码</a>
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
<script>
    function sendCode(type){
        $.post(
            '/cash/send-code',
            {type:type},function(data){
                var arr = eval('('+data+')');

                if(arr.code  !=1){
                    alert('发送验证码失败')
                }else{
                    var $button = $(".sendVerifyCode");
                    var number = 60;
                    var countdown = function(){
                        if (number == 0) {
                            $button.attr("disabled",false);
                            $button.html("发送验证码");
                            number = 60;
                            return;
                        } else {
                            $button.attr("disabled",true);
                            $button.html(number + "秒 重新发送");
                            number--;
                        }
                        setTimeout(countdown,1000);
                    }
                    setTimeout(countdown,1000)
                }
            }
        );
    }

</script>


