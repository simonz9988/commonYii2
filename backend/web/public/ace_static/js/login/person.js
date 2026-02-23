var countdown=60;
function sendMail(){
    var obj = $("#send_btn");

    var mobile = $('input[name="mobile"]').val();
    var type = 'signup';
    $.post(
        '/sms/sendCode',
        {'mobile':mobile,'type':type,'user_type':'person'},
        function(rs){
            var arr = eval('('+rs+')');
            if(arr.code != 1){
                alert(arr.msg);
            }else{
                settime(obj);
            }
        }
    );

}
function settime(obj) { //发送验证码倒计时
    if (countdown == 0) {
        obj.attr('disabled',false);
        obj.removeClass('layui-btn-disabled').addClass('layui-btn-normal');
        //obj.removeattr("disabled");
        obj.text("免费获取验证码");
        countdown = 60;
        return;
    } else {
        obj.attr('disabled',true);
        obj.removeClass('layui-btn-normal').addClass('layui-btn-disabled');
        obj.text("重新发送(" + countdown + ")");
        countdown--;
    }
    setTimeout(function() {
            settime(obj) }
        ,1000)
}


$(function(){
       jQuery.validator.addMethod("isphoneNum", function(value, element) {
           var length = value.length;
           var mobile = /^1[3|5|7|8]{1}[0-9]{9}$/;
           return this.optional(element) || (length == 11 && mobile.test(value));
       }, "请正确填写您的手机号码");

       $("#personForm").validate({
           rules: {
               mobile: {
                   required:true,
                   isphoneNum:true,
                   remote:{
                       type:"POST",
                       url:'/login/ajaxCheckPersonMobile',
                       data: {  //要传递的数据
                           mobile: function() {
                               return $("#mobile").val();
                           }
                       }
                   }
               },
               password: {
                   required: true,
                   minlength: 5
               },
               confirm_password: {
                   required: true,
                   minlength: 5,
                   equalTo: "#password"
               },
               inviter_username: {
                   required: true,
                   isphoneNum:true
               },
               true_name:{
                   required:true
               },
               id_card: {
                   required: false
               },
               province:{
                   required:false
               },
               city:{
                   required:false
               },
               area:{
                   required:false
               },
               address:{
                   required:false
               }
           },
           messages: {
               mobile: {
                   required: '请输入手机号码',
                   isphoneNum: '请输入正确的手机号码',
                   remote:"此手机已经注册过，请换用其它号码。"
               },
               password: {
                   required: '请输入密码',
                   minlength: '密码长度不能小于5个字符'
               },
               confirm_password: {
                   required: '请输入确认密码',
                   minlength: '密码长度不能小于5个字符',
                   equalTo: "两次密码输入不一致"
               },
               inviter_username: {
                   required: '请输入推荐人姓名',
                   isphoneNum: '请输入正确的手机号码'
               },
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
                   '/login/doRegister',
                   $('#personForm').serialize(),
                   function(rs){
                       var arr = eval('('+rs+')');
                       if(arr.code == '1'){
                           alert("注册成功");
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