$(function() {
	
	//表单验证内容
	$("#changePassword").validate({
        rules: {
            password: {
                required: true
            },
            newPassword:{
                required: true 
            },
            confirmNewPassword:{
                required: true
            }

        },
        messages: {
            password: {
                required: "Required"
            },
            newPassword: {
                required:'Required'
            },
            confirmNewPassword:{
                required: "Required"
            }
        },
        errorPlacement: function (error, element) {
            var errplace = element.parent().next().children('.reg_tip');
            errplace.html(error.text());
            errplace.addClass('red');
            
        },
        success: function (label, element) {
            var obj = $(element).parent().next().children('.reg_tip');
            obj.html('');
            obj.removeClass('red');
        },
        submitHandler: function (form) {

			var oldPassword    = $("input[name='password']").val();
			var newPassword    = $("input[name='newPassword']").val();
			var repeatPassword = $("input[name='confirmNewPassword']").val();

			if(newPassword !=repeatPassword){
				alert('The new password and confirm password must be consistent！');
				return  false;
			}

			$.post(
				'/privilege/checkOldPassword',
				{oldPassword:oldPassword},
				function(data){
					var arr = eval('('+data+')');
					if(arr.code ==1){
						$.post(
							'/privilege/ajaxSaveUserPassword',
							{newPassword:newPassword},
							function(data1){
								
						  		var arr1 = eval('('+data1+')');
						  		var code1 = arr1.code ;
						  		if(code1==1){
						  			alert('change password success!');
						  			var url1 =  arr1.url ; 
						  			location.href=url1;
						  			
						  		}else{
						  			alert(arr.msg)
						  		}
						  	
							}
						);
					}else{
						alert(arr.msg);
					}
					 
					
				}
				
			);
           
        }
    });

    
   
}); 



