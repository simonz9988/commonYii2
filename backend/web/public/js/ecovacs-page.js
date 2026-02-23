/**
 * Created with IntelliJ IDEA.
 * User: peter.zhu
 * Date: 15-4-17
 * Time: 下午11:26
 * To change this template use File | Settings | File Templates.
 */

function postAjaxPage(dynamicalID, url,callBackFunction,data) {
    $.ajax({
        type: "POST",
        url: url,
        data: data,
        cache: false,
        dataType: "json",
        success: function (jsonData) {
            eval(callBackFunction)(jsonData);
            initPage(jsonData, dynamicalID, data);
        },
        error: function (jsonData) {
            console.log("获取分页数据出错");
			//GLOBAL.error("获取分页数据出错。");
            //GLOBAL.error(jsonData);
        }
    });
}

/**动态初始化组件*/
function initPage(page, dynamicalID, data) {
    if(!page || !page.totalElements && !page.totalElements===0) {
        G.error("pager is null, from ecovacs-page.js");
        return;
    }
    //var currentPage = parseInt(page.number)+1;
	var currentPage = parseInt(page.number);
    $("#_totalPages" + dynamicalID).val(page.totalPages);
    $("#_currentPage" + dynamicalID).val(currentPage);
    $("#_data" + dynamicalID).val(JSON.stringify(data));

    var start = parseInt(page.number-1)*parseInt(page.size)+1;
    var pageInfo = "当前第";
    if(page.numberOfElements > 1){
        pageInfo += start +"-"+(start+page.numberOfElements-1)+"条";
    }else{
        pageInfo += page.numberOfElements + "条";
    }
    pageInfo +="共"+page.totalPages+"页,"+page.totalElements+"条";
    $("#_pageInfo"+dynamicalID).text(pageInfo).show();
    if(page.numberOfElements == 0){
        $("#_pageInfo"+dynamicalID).hide();
    }

    if(page.totalPages>1 && currentPage>1){
        $("#_page"+dynamicalID).find(".prev").show();
    }else{
        $("#_page"+dynamicalID).find(".prev").hide();
    }

    if(page.totalPages>1 && currentPage <page.totalPages){
        $("#_page"+dynamicalID).find(".next").show();
    }else{
        $("#_page"+dynamicalID).find(".next").hide();
    }
    if(page.totalPages>1 ){
        $("#_page"+dynamicalID).find(".j_goto").show();
    }else{
        $("#_page"+dynamicalID).find(".j_goto").hide();
    }
    $("._page_num" + dynamicalID).each(function(index){
        if(page.totalPages<=0){
            $(this).hide();
            return true;
        }
        if(page.totalPages==1){
            if(index==0){
                $(this).html(currentPage);
                $(this).show();
            }else{
                $(this).hide();
            }
        }else if(page.totalPages==2){
            if(index<2){
                $(this).html(index+1);
                $(this).show();
            }else{
                $(this).hide();
            }
        }else{
            if(currentPage==1){
                $(this).html(currentPage+index).show();
            }else if(currentPage==page.totalPages){
                if(index==0){
                    $(this).html(currentPage-2).show();
                }
                if(index==1){
                    $(this).html(currentPage-1).show();
                }
                if(index==2){
                    $(this).html(currentPage).show();
                }
            }else{
                if(index==0){
                    $(this).html(currentPage-1).show();
                }
                if(index==1){
                    $(this).html(currentPage).show();
                }
                if(index==2){
                    $(this).html(currentPage+1).show();
                }
            }

        }
        if(currentPage==$(this).html()*1){
            $(this).parent().addClass("active");
        }else{
            $(this).parent().removeClass("active");
        }
    });

    $("#_page"+dynamicalID).show();
}