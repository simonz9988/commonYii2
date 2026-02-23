

$(function(){

       $("#personForm").validate({
           rules: {

               true_name:{
                   required:true
               },
               id_card: {
                   required: true
               },
               province:{
                   required:true
               },
               city:{
                   required:true
               },
               area:{
                   required:true
               },
               address:{
                   required:true
               }
           },
           messages: {

               true_name: {
                   required: '请输入姓名'
               },
               id_card: {
                   required: '请输入身份证号'
               },
               province: {
                   required: '请选择省'
               },
               city: {
                   required: '请选择市'
               },
               area: {
                   required: '请选择区'
               },
               address: {
                   required: '请输入详细地址'
               }
       },
           submitHandler: function(form)
           {
               $.post(
                   '/site/doEditPerson',
                   $('#personForm').serialize(),
                   function(rs){
                       var arr = eval('('+rs+')');
                       if(arr.code == '1'){
                           alert("修改成功");
                           if(arr.open_id){
                               location.href ='/site/index';
                           }else{
                               location.href ='/login/index';
                           }

                       }else{
                           alert(arr.msg);
                       }
                   }
               );
           }
       });
   })