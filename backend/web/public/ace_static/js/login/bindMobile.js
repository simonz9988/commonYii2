var countdown=60;
function sendMail(){
    var obj = $("#send_btn");

    var mobile = $('input[name="mobile"]').val();
    var type = 'bindMobile';
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
                       url:'/login/ajaxCheckBindMobile',
                       data: {  //要传递的数据
                           mobile: function() {
                               return $("#mobile").val();
                           }
                       }
                   }
               }
           },
           messages: {
               mobile: {
                   required: '请输入手机号码',
                   isphoneNum: '请输入正确的手机号码',
                   remote:"该手机号码未注册"
               }
       },
           submitHandler: function(form)
           {
               $.post(
                   '/login/doBindMobile',
                   $('#personForm').serialize(),
                   function(rs){
                       var arr = eval('('+rs+')');
                       if(arr.code == '1'){
                           alert("绑定成功");
                           location.href =arr.url;
                       }else{
                           alert(arr.msg);
                       }
                   }
               );
           }
       });
   })