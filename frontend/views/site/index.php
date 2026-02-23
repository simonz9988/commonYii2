<link rel="stylesheet" href="/static/zTree_v3/css/zTreeStyle/zTreeStyle.css" type="text/css">
<script type="text/javascript" src="/static/zTree_v3/js/jquery-1.4.4.min.js"></script>
<script type="text/javascript" src="/static/zTree_v3/js/jquery.ztree.core.js"></script>

<SCRIPT LANGUAGE="JavaScript">
    var zTreeObj;
    // zTree 的参数配置，深入使用请参考 API 文档（setting 配置详解）
    var setting = {
        callback:{
            onClick:chooseNode
        }
    };
    // zTree 的数据属性，深入使用请参考 API 文档（zTreeNode 节点数据详解）
    var zNodes = <?=json_encode($list)?>;
    $(document).ready(function(){
        zTreeObj = $.fn.zTree.init($("#treeDemo"), setting, zNodes);
    });

    function chooseNode(event,treeId,treeNode){

        var id = treeNode.id ;
        location.href = '/tool/read-detail?id='+id ;
    }
</SCRIPT>


<form method="post" action="/site/index">
    <input type="text" name="file_name">
    <button type="submit">Submit</button>
</form>
<div><ul id="treeDemo" class="ztree"></ul></div>