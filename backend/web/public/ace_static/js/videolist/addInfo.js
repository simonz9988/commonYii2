$(function() {
    

    //增加悬浮提示
    form_input_notice("input[name='title']",'input');
    form_input_notice("input[name='sort']",'input');
    form_input_notice("#icon",'input');
    //form_input_notice("#intro",'input');
    form_input_notice("input[name='video_url']",'input');
    form_input_notice("select[name='homepage1']",'input');


});



//判断是否推荐到首页 
function check_is_homepage(a){
	if( $(a).is( ":checked" )){
		$(a).next().val('yes');
	}else{
		$(a).next().val('no');
	}
}



