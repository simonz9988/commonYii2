<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <title>异步加载地图</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <style>
        body,
        html,
        #container {
            overflow: hidden;
            width: 100%;
            height: 100%;
            margin: 0;
            font-family: "微软雅黑";
        }
    </style>
</head>
<body>
<div id="container"></div>
</body>
</html>
<script>
    function loadJScript() {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = '//api.map.baidu.com/api?type=webgl&v=1.0&ak=L9rOCGc4oPqUdQE36EAg2lXykagA3zMA&callback=init';
        document.body.appendChild(script);
    }
    function init() {
        var map = new BMapGL.Map('container'); // 创建Map实例
        var point = new BMapGL.Point(<?=$device_info['longitude']?>, <?=$device_info['latitude']?>); // 创建点坐标
        map.centerAndZoom(point, 10);
        map.enableScrollWheelZoom(); // 启用滚轮放大缩小


        var content = "<?=$device_info['device_name']?>";
        var label = new BMapGL.Label(content, {       // 创建文本标注
            position: point,                          // 设置标注的地理位置
            offset: new BMapGL.Size(10, 20)           // 设置标注的偏移量
        })
        map.addOverlay(label);

    }
    window.onload = loadJScript; // 异步加载地图


</script>