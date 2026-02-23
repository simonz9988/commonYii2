<!-- 地图 -->
<
script >
	// 创建地图实例
	var map = map = new AMap.Map('container', {
		resizeEnable: true, // 是否监控地图容器尺寸变化（默认为false）
		zoom: 5, // 地图默认的缩放级别
		viewMode: '3D' // 使用3D视图（默认为2D）
	});

// 创建一个 success Icon 亮灯
var successIcon = new AMap.Icon({
	// 图标尺寸
	size: new AMap.Size(30, 30),
	// 图标的取图地址
	image: './img/light_normal.png',
	// 图标所用图片大小
	imageSize: new AMap.Size(30, 30)
});

// 创建一个 default icon 灭灯
var defaultIcon = new AMap.Icon({
	size: new AMap.Size(30, 30),
	image: './img/light_lampDown.png',
	imageSize: new AMap.Size(30, 30)
});

// 创建一个 warning icon 告警
var warningIcon = new AMap.Icon({
	size: new AMap.Size(30, 30),
	image: './img/light_fault.png',
	imageSize: new AMap.Size(30, 30)
});

// 创建一个 error icon 离线
var errorIcon = new AMap.Icon({
	size: new AMap.Size(30, 30),
	image: './img/light_offline.png',
	imageSize: new AMap.Size(30, 30)
});

let array = [{
	longitude: 116.35,
	latitude: 39.89,
	status: 'error'
}, {
	longitude: 117.35,
	latitude: 39.89,
	status: 'warning'
}, {
	longitude: 114.35,
	latitude: 39.89,
	status: 'success'
}, {
	longitude: 114.35,
	latitude: 34.89,
	status: 'default'
}]

// 渲染点标记
function setMarker(array, status) {

	for (let i = 0; i < array.length; i++) {

		if (status === 'default') {

			if (array[i].status === 'default') {
				marker(array[i].longitude, array[i].latitude, defaultIcon)
			} else if (array[i].status === 'success') {
				marker(array[i].longitude, array[i].latitude, successIcon)
			} else if (array[i].status === 'warning') {
				marker(array[i].longitude, array[i].latitude, warningIcon)
			} else {
				marker(array[i].longitude, array[i].latitude, errorIcon)
			}
		} else if (status === 'success') {
			if (array[i].status === 'success') {
				marker(array[i].longitude, array[i].latitude, successIcon)
			}
		} else if (status === 'warning') {
			if (array[i].status === 'warning') {
				marker(array[i].longitude, array[i].latitude, warningIcon)
			}
		} else if (status === 'error') {
			if (array[i].status === 'error') {
				marker(array[i].longitude, array[i].latitude, errorIcon)
			}
		}
	}
}

let markers = [];

function marker(longitude, latitude, icon) {
	// console.log(icon);
	// 创建一个 Marker 实例：（标记点）
	let newMarker = new AMap.Marker({
		position: new AMap.LngLat(longitude, latitude), // 经纬度对象，也可以是经纬度构成的一维数组[116.39, 39.9]
		icon: icon, // 需在点标记中显示的图标
		// 设置了 icon 以后，设置 icon 的偏移量，以 icon 的 [center bottom] 为原点
		offset: new AMap.Pixel(-15, -30),
		title: "位置标题" // 鼠标滑过点标记时的文字提示，不设置则鼠标滑过点标无文字提示
	});

	map.add([newMarker]);
	markers.push(newMarker);
}

function clickDefault() {
	map.remove(markers);
	log.success("全部路灯");
	setMarker(array, 'default');
}

function clickSuccess() {
	map.remove(markers);
	log.success("亮灯路灯");
	setMarker(array, 'success');
}

function clickWarning() {
	map.remove(markers);
	log.success("故障路灯");
	setMarker(array, 'warning');
}

function clickError() {
	map.remove(markers);
	log.success("离线路灯");
	setMarker(array, 'error');
}

// 给按钮绑定事件
document.getElementById("clickDefault").onclick = clickDefault;
document.getElementById("clickSuccess").onclick = clickSuccess;
document.getElementById("clickWarning").onclick = clickWarning;
document.getElementById("clickError").onclick = clickError;

setMarker(array, 'default');

map.on("complete", function() {
	log.success("地图加载完成！");
}); <
/script>
