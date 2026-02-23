/**
 * Created by dean.zhang on 2017/7/31.
 */


//曲线图设置
function setLine(id,data,options){
    var ctx = document.getElementById(id).getContext('2d');
    var myChart = new Chart(ctx,{
        type:'line',
        data:data,
        options:options
    });
}

//柱状图设置

function setBar(id,data,options){
    var ctx = document.getElementById(id).getContext('2d');
    var myChart = new Chart(ctx,{
        type:'horizontalBar',
        data:data,
        options:options
    });
}


//饼图设置
function setPie(id,data,options){
    var ctx = document.getElementById(id).getContext('2d');
    var myChart = new Chart(ctx,{
        type:"pie",
        data:data,
        options:options
    });
}
