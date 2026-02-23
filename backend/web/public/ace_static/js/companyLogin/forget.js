var countdown=60;
function sendMail(){
    var obj = $("#send_btn");

    var mobile = $('input[name="mobile"]').val();
    var type = 'forget';
    $.post(
        '/sms/sendCode',
        {'mobile':mobile,'type':type,'user_type':'company'},
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
                       url:'/companyLogin/ajaxCheckMobileExists',
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
               }

           },
           messages: {
               mobile: {
                   required: '请输入手机号码',
                   isphoneNum: '请输入正确的手机号码',
                   remote:"该用户不存在"
               },
               password: {
                   required: '请输入密码',
                   minlength: '密码长度不能小于5个字符'
               },
               confirm_password: {
                   required: '请输入确认密码',
                   minlength: '密码长度不能小于5个字符',
                   equalTo: "两次密码输入不一致"
               }
       },
           submitHandler: function(form)
           {
               $.post(
                   '/companyLogin/doForget',
                   $('#personForm').serialize(),
                   function(rs){
                       var arr = eval('('+rs+')');
                       if(arr.code == '1'){
                           alert("修改成功");
                           location.href ='/companyLogin/index';
                       }else{
                           alert(arr.msg);
                       }
                   }
               );
           }
       });
   })