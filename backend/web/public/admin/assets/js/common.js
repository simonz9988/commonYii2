/**
 * jquery 公共方法文件
 */

function delModel(link){
    // 允许第二个参数作为修改提示语的功能
    var message = '确认删除该条记录？';
    if (arguments.length == 2) {
        message = arguments[1];
    }

    bootbox.confirm({
        size:'small',
        message: message,
        buttons: {
            cancel: {
                label: '<i class="fa fa-times"></i> 取消'
            },
            confirm: {
                label: '<i class="fa fa-check"></i> 确定'
            }
        },
        callback: function (result) {
            if(result){
                $.ajax({
                    url:link,
                    type:'POST',
                    dataType:'json',
                    success:function(rs){
                        if(rs.code == '1'){
                            window.location.reload();
                        }else{
                            bootbox.alert(rs.msg);
                        }
                    },
                    error:function(){
                        bootbox.alert("通讯失败，请检查接口地址是否正确！");
                    }
                })
            }else{
                return true;
            }
        }
    });
}


// 两个浮点数求和  
function bcadd(num1,num2){  
   var r1,r2,m;  
   try{  
	   r1 = num1.toString().split('.')[1].length;  
   }catch(e){  
	   r1 = 0;  
   }  
   try{  
	   r2=num2.toString().split(".")[1].length;  
   }catch(e){  
	   r2=0;  
   }  
   m=Math.pow(10,Math.max(r1,r2));  
   // return (num1*m+num2*m)/m;  
   return Math.round(num1*m+num2*m)/m;  
}  
  
// 两个浮点数相减  
function bcsub(num1,num2){  
   var r1,r2,m;  
   try{  
	   r1 = num1.toString().split('.')[1].length;  
   }catch(e){  
	   r1 = 0;  
   }  
   try{  
	   r2=num2.toString().split(".")[1].length;  
   }catch(e){  
	   r2=0;  
   }  
   m=Math.pow(10,Math.max(r1,r2));  
   n=(r1>=r2)?r1:r2;  
   return (Math.round(num1*m-num2*m)/m).toFixed(n);  
}  

// 两数相除  
function bcdiv(num1,num2){  
   var t1,t2,r1,r2;  
   try{  
	   t1 = num1.toString().split('.')[1].length;  
   }catch(e){  
	   t1 = 0;  
   }  
   try{  
	   t2=num2.toString().split(".")[1].length;  
   }catch(e){  
	   t2=0;  
   }  
   r1=Number(num1.toString().replace(".",""));  
   r2=Number(num2.toString().replace(".",""));  
   return (r1/r2)*Math.pow(10,t2-t1);  
}  
  
function bcmul(num1,num2){  
   var m=0,s1=num1.toString(),s2=num2.toString();   
	try{m+=s1.split(".")[1].length}catch(e){};  
	try{m+=s2.split(".")[1].length}catch(e){};  
	return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m);  
}


$("#editPasswordForm").submit(function(e){
    var password = $.trim($("#password").val());


    var password2 = $.trim($("#password2").val());
    var repeartpassword2 = $.trim($("#repeartpassword2").val());

    if(password =='' || password2=='' || repeartpassword2==''){
        alert("密码不能为空");
    }
    if(password2!=repeartpassword2){
        alert('新密码不一致');
    }

    $.post(
        '/system/ajax-reset-password',
        {password:password,password2:password2,repeartpassword2:repeartpassword2},
        function(data){
            var arr = eval('('+data+')');
            if(arr.code==1){
                $("#modal5").click();
                alert('修改成功');
            }else{
                alert(arr.msg);
            }
        }
    );

});

