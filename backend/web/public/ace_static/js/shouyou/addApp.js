$(function() {


});

//选择封面图片
function selectCoverImage(self){
    var url = $(self).attr("url");
    $(":input[name=cover_img_url]").val(url);
    $("#modalcoverclose").click();
}