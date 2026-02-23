<link href="/public/js/zTree_v3/css/zTreeStyle/zTreeStyle.css" rel="stylesheet" />
<script>
    var zNodes_str ='<?=$zNodes_str?>';
    var zNodes = eval('('+zNodes_str+')');
</script>
<div class="add-menu page-content" style="margin-top: 20px;margin-left: 20px;">
    <div class="page-header">
        <div class="row">
            <!-- 搜索 start -->


            <!-- 搜索 end  -->
        </div>
        <div class="clearfix">
        </div>
        <div class="row tables-wrapper">
            <div class="col-xs-12">

                <!-- 树形结构 start -->
                <div class="content_wrap">
                    <div class="zTreeDemoBackground left">
                        <ul id="treeDemo" class="ztree"></ul>
                    </div>
                </div>
                <!-- 树形结构 end -->
            </div>
            <!-- /.col -->


        </div>

        <div class="clearfix form-actions">
            <div class="col-md-offset-3 col-md-9">
                <button class="btn btn-info" type="submit" onclick="window.history.back(-1);">
                    <i class="icon-undo bigger-110"></i>
                    上一步
                </button>
                &nbsp; &nbsp; &nbsp;
                <button class="btn btn-info" type="submit" onclick="save_role_privilege(<?=$admin_user_id?>)">
                    <i class="icon-ok bigger-110"></i>
                    保存
                </button>


            </div>
        </div>

    </div>
</div>

<script src="/public/ace_static/plugins/zTree_v3//js/jquery.ztree.core-3.5.js"></script>
<script src="/public/ace_static/plugins/zTree_v3//js/jquery.ztree.excheck-3.5.js"></script>
<script>
    $(function(){


            //点击树进行页面跳转
            function detailClick(event, treeId, treeNode) {
                var id =treeNode.id ;
                //页面跳转
                window.location.href='/privilege/add-privilege?id='+id;
            }
        var setting = {
            view: {
                selectedMulti: false
            },
            check: {
                enable: true,
                //父节点子节点点击不关联
                chkboxType :{  "Y" : "ps", "N" : "s"  }
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                //onCheck: onCheck,
                onClick:detailClick
            }
        };

        var clearFlag = false;
        function onCheck(e, treeId, treeNode) {
            count();
            if (clearFlag) {
                clearCheckedOldNodes();
            }
        }
        function clearCheckedOldNodes() {
            var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
                nodes = zTree.getChangeCheckedNodes();
            for (var i=0, l=nodes.length; i<l; i++) {
                nodes[i].checkedOld = nodes[i].checked;
            }
        }
        function count() {
            var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
                checkCount = zTree.getCheckedNodes(true).length,
                nocheckCount = zTree.getCheckedNodes(false).length,
                changeCount = zTree.getChangeCheckedNodes().length;
            $("#checkCount").text(checkCount);
            $("#nocheckCount").text(nocheckCount);
            $("#changeCount").text(changeCount);

        }
        function createTree() {
            $.fn.zTree.init($("#treeDemo"), setting, zNodes);
            //count();
            //clearFlag = $("#last").attr("checked");
        }

        $(document).ready(function(){
            createTree();
            //$("#init").bind("change", createTree);
            //$("#last").bind("change", createTree);
        });



    });


    function save_role_privilege(admin_user_id){
        //获取所有选择的
        var zTree = $.fn.zTree.getZTreeObj("treeDemo");
        var str =  '';
        $.each(zTree.getCheckedNodes(true), function (index, node) {
            str+=node.id+'@' ;
        });
        $.post(
            '/system/ajax-save-role-api-key',
            {admin_user_id:admin_user_id,privileges_str:str},
            function(data){
                var arr  = eval('('+data+')') ;
                if(arr.info =='succ'){
                    alert('保存成功');
                }else{
                    alert('保存失败');
                }
            }
        );



    }


</script>
