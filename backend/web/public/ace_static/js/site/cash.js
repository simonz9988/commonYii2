

$(function(){

       $("#cashForm").validate({
           rules: {

               bank_name:{
                   required:true
               },
               account_no: {
                   required: true
               },
               nickname:{
                   required:true
               },

               mobile:{
                   required:true
               }
           },
           messages: {

               bank_name: {
                   required: '请输入开户行'
               },
               account_no: {
                   required: '请输入银行账户'
               },
               nickname: {
                   required: '请输入收款人姓名'
               },
               mobile: {
                   required: '请输入预留电话'
               }
       },
           submitHandler: function(form)
           {
               $.post(
                   '/site/doEditCash',
                   $('#cashForm').serialize(),
                   function(rs){
                       var arr = eval('('+rs+')');
                       if(arr.code == '1'){
                            alert("添加成功");
                            location.href ='/site/index';
                       }else{
                           alert(arr.msg);
                       }
                   }
               );
           }
       });
   })