$(function() {
    

    //增加悬浮提示
    form_input_notice("input[name='title']",'input');
    form_input_notice("input[name='sort']",'input');
    form_input_notice("#icon",'input');
    //form_input_notice("textarea[name='intro']",'input');
    form_input_notice("textarea[name='content']",'input');
    form_input_notice("select[name='homepage']",'input');
    form_input_notice("input[name='recommended_title']",'input');
    //form_input_notice("textarea[name='recommended_summary']",'input');

    var homepage_val = $("input[name='homepage']").val() ;
    get_homepage_div(homepage_val);

}); 


function getHomepageDiv(a){
    if( $(a).is( ":checked" )){
        get_homepage_div('yes');
        $(a).next().val('yes');
    }else{
        get_homepage_div('no');
        $(a).next().val('no');
    }
	
	
}



