$(function(){

  <!--
    //点击树进行页面跳转
    function detailClick(event, treeId, treeNode) {
      var id =treeNode.id ;
      //页面跳转
      window.location.href='/privilege/addPrivilege/id/'+id;
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
  //-->
  
  
});


function save_role_privilege(role_id){
    //获取所有选择的
    var zTree = $.fn.zTree.getZTreeObj("treeDemo");
    var str =  '';
    $.each(zTree.getCheckedNodes(true), function (index, node) {
         str+=node.id+'@' ;
    });
    $.post(
        '/privilege/ajaxSaveRolePrivilege',
        {role_id:role_id,privileges_str:str},
        function(data){
          var arr  = eval('('+data+')') ;
          if(arr.info =='succ'){
            location.href =arr.url ;
          }else{
            alert('保存失败');
          }
        }
    );
    


}

