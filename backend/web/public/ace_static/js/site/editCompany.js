


$(function(){

        $("#companyForm").validate({
           rules: {

               corporation: {
                   required: true
               },
               corporation_contact: {
                   required: true
               },
               head_name: {
                   required: true
               },
               head_contact: {
                   required: true
               },
               industry: {
                   required: true
               },
               registered_capital: {
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
               },
               nsrsbh:{
                   required:true
               }
           },
           messages: {

               corporation: {
                   required: '请输入法人姓名'
               },
               corporation_contact: {
                   required: '请输入法人联系方式'
               },
               head_name: {
                   required: '请输入负责人姓名'
               },
               head_contact: {
                   required: '请输入负责人联系方式'
               },
               industry: {
                   required: '请选择所属行业'
               },
               registered_capital: {
                   required: '请输入公司注册资本'
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
               },
               nsrsbh: {
                   required: '请输入统一社会信用代码'
               }
           },
           submitHandler: function(form)
           {
               $.post(
                   '/companySite/doEditCompany',
                   $('#companyForm').serialize(),
                   function(rs){
                       var arr = eval('('+rs+')');
                       if(arr.code == '1'){
                           alert("修改成功");
                           location.href ='/companySite/index';
                       }else{
                           alert(arr.msg);
                       }
                   }
               );
           }
       });
   })