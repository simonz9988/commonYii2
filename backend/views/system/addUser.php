<div class="breadcrumbs" id="breadcrumbs">
    <div class="row">
        <div class="col-md-8 col-xs-12">
            <ul class="breadcrumb ">
                <li> <i class="ace-icon fa fa-home home-icon"></i> <a href="javascript:void(0);"><?=$this->params['selectedLevel0Name']?></a> </li>
                <li class="active"><?=$this->params['selectedLevel1Name']?></li>
            </ul>
            <!-- /.breadcrumb -->
        </div>
        <div class="col-md-4 text-right">
            <a href="/system/add-user" class="btn btn-primary btn-sm no-border"><i class="ace-icon glyphicon glyphicon-plus"></i> 新增</a>
        </div>

    </div>
</div>
<!-- PAGE CONTENT BEGINS  -->

<div class="page-content">
    <div class="page-header">

        <div class="row">
            <div class="col-xs-6">
                <h1>编辑用户
                    <small></small>
                </h1>
            </div>
            <div class="col-xs-6 text-right"></div>

        </div>
        <div class="clearfix"></div>
    </div>

    <div class="row ">
        <div class="col-xs-12">
            <form novalidate="novalidate" class="form-horizontal" role="form" id="systeamAddMenu" action="/system/do-add-user" method="post">

                <input type="hidden" name="id" value="<?=$info?$info['id']:0?>">



                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 用户名</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['username']:''?>"name="username" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 用户昵称</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['nickname']:''?>"name="nickname" class="col-xs-12 col-sm-6" type="text">
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

                    <div class="col-sm-5">
                        <input value=""name="password" class="col-xs-12 col-sm-6" type="password">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 角色</label>

                    <div class="col-sm-5">
                        <select name="role_id" class="input-sm  col-xs-12 col-sm-6">
                            <?php foreach($all_role_info as $v){?>
                                <option value="<?=$v['id']?>" <?php if($info&&$info['role_id'] ==$v['id']){echo 'selected="selected"' ;}?>><?=$v['role_value']?></option>
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
                    <label class="col-sm-2 control-label no-padding-right"> 排序</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['sort']:'99'?>"name="sort" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right"> 电子邮箱</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['email']:''?>"name="email" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" style="display: none;">
                    <label class="col-sm-2 control-label no-padding-right"> 用户验证私钥</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['secret_key']:''?>"name="secret_key" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group"  style="display: none;">
                    <label class="col-sm-2 control-label no-padding-right"> AppKey</label>

                    <div class="col-sm-5">
                        <input value="<?=$info?$info['mark_key']:''?>"name="mark_key" class="col-xs-12 col-sm-6" type="text">
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group" >
                    <label class="col-sm-2 control-label no-padding-right"> 所属公司</label>

                    <div class="col-sm-5">

                        <input value="ALL"name="company_type" onclick="select_company_type(this)" type="checkbox" <?=$info && $info['company_type']=='ALL'?'checked="true"':''?>>全部
                        <?php if($company_list):?>
                        <?php foreach($company_list as $v):?>
                            <input class="company_type_input" value="<?=$v['id']?>"name="company_id[]" <?php

                                if($info && $info['company_type']=='ALL'){
                                    echo  'checked ="true"';
                                }else{
                                    if(in_array($v['id'],$company_ids)){
                                        echo  'checked ="true"';
                                    }
                                }
                                ?> type="checkbox"><?=$v['unique_key']?>
                        <?php endforeach ;?>
                        <?php endif ;?>
                        <script>
                            function select_company_type(self){
                                if($(self).is(':checked')){
                                    $(".company_type_input").attr("checked", true);
                                }else{
                                    $(".company_type_input").attr("checked", false);
                                }
                            }
                        </script>
                    </div>
                    <div class="col-sm-4">
                        <label class="col-sm-12 form-control-static reg_tip">
                        </label>
                        <label class="col-sm-12 form-control-static reg_default">

                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label no-padding-right">是否显示</label>

                    <div class="col-sm-5">
                        <select name="is_open" class="input-sm  col-xs-12 col-sm-6">
                            <option value="0">请选择</option>
                            <option value="1" <?=$info&&$info['is_open']=='1'?'selected="selected"':''?>>是</option>
                            <option value="0"  <?=$info&&$info['is_open']=='0'?'selected="selected"':''?>>否</option>
                        </select>
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
