$(function() {
	init_uploader('icon','infolist');
	
	//新增广告页面表带验证
	$("#infolistAddForm").validate({
        rules: {
            title: {
                required: true,
                maxlength:100
            },
            intro:{
            	required: true
            },
            content:{
                required: true
            },
            recommended_title:{
                required: true,
                maxlength:100
            },
            recommended_summary:{
                required:true
            },
            sort:{
                digits:true,
                required: true,
                range:[1,9999]
            },
            seo_title:{
                required: false
            },
            seo_keywords:{
                required: false
            },
            seo_description:{
                required: false
            }

        },
        messages: {
            title: {
                required: "Required",
                maxlength:'A maximum of 100 characters can be contained'

            },
            intro: {
                required: "Required"
            },
            content:{
                required: "Required"
            },
            recommended_title:{
                required:  "Required",
                maxlength:'A maximum of 100 characters can be contained'
            },
            recommended_summary:{
                required:  "Required"
            },
            sort:{
                digits:"Only the integer ranging from 1 to 9999 is allowed",
                required: "Required",
                range:"Only the integer ranging from 1 to 9999 is allowed"
            },
            seo_title: {
                required: "Required"
            },
            seo_keywords:{
                required: "Required"
            },
            seo_description:{
                required:  "Required"
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
            var input_icon = $("#input_icon").val();
            if(!input_icon){
                $("#icon_reg_tip").addClass('red').html('Required');

                return false ;
            }
            form.submit();
        }
    });

	//新增页面管理表单验证
	$("#pagelistaddForm").validate({
        rules: {
            title: {
                required: true,
                maxlength:100
            },
            url:{
                required: true
            },
            seo_title:{
                required: false
            },
            seo_keywords:{
                required: false
            },
            seo_description:{
                required: false
            }

        },
        messages: {
            title: {
                required: "Required",
                maxlength:'A maximum of 100 characters can be contained'
			},
            url: {
                required: "Required"
            },
            seo_title: {
                required: "Required"
            },
            seo_keywords:{
                required: "Required"
            },
            seo_description:{
                required:  "Required"
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
            form.submit();
        }
    });

    //视频新增编辑页面
    $("#videolistAddForm").validate({
        rules: {
            title: {
                required: true,
                maxlength:100
            },
            intro:{
            	required: true
            },
            video_url:{
            	required: true
            },
            sort:{
                digits:true,
                required: true,
                range:[1,9999]
            },
            seo_title:{
                required: false
            },
            seo_keywords:{
                required: false
            },
            seo_description:{
                required: false
            }

        },
        messages: {
            title: {
                required: "Required",
                maxlength:'A maximum of 100 characters can be contained'
			},
			intro: {
                required: "Required"
            },
            video_url:{
            	required: "Required"
            },
            sort:{
                digits:"Only the integer ranging from 1 to 9999 is allowed",
                required: "Required",
                range:"Only the integer ranging from 1 to 9999 is allowed"
            },
            seo_title: {
                required: "Required"
            },
            seo_keywords:{
                required: "Required"
            },
            seo_description:{
                required:  "Required"
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

            var input_icon = $("#input_icon").val();
            if(!input_icon){
                $("#icon_reg_tip").addClass('red').html('Required');

                return false ;
            }
            form.submit();
        }
    });
    
}); 

function get_homepage_div(homepage){

	if(homepage=='yes'){
		$(".recommended").show();
		$(".recommended input").attr("required",'true');
		$("input[name='recommended_title']").rules("add", { required: true  ,maxlength:100,messages: {required:'Required',maxlength:'A maximum of 100 characters can be contained'} });
		$("textarea[name='recommended_summary']").rules("add", { required: true  ,messages: {required:'Required'} });
	}else{
		$(".recommended").hide();
		$(".recommended input").removeAttr("required");
		$("input[name='recommended_title']").rules("add", { required: false,maxlength:false});
		$("textarea[name='recommended_summary']").rules("add", { required: false,maxlength:false});
	}
}




