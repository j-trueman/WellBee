
console.log("Script Connected");
if ($('#jqueryconnector')) {
	console.log("JQuery Loaded Successfully");
};

const sock = new WebSocket('ws://192.168.1.69:8080/ws');

sock.addEventListener("message", async (event) => {
	positionData = event.data.split(',');
	console.log(`${positionData[0]},${positionData[1]}`);
	map.setView([positionData[0], positionData[1]]);
});

function getCurrentPosition() {
	sock.send("request");
}

var waitForEl = function (selector, callback) {
	if (jQuery(selector).length) {
		callback();
	} else {
		setTimeout(function () {
			waitForEl(selector, callback);
		}, 100);
	}
};

function createProgressBars() {
	stepsBar = new ldBar('.stepsBar', {
		"stroke": "#f5bb26",
		"stroke-width": "10",
		"value": "20",
		"path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
	});
	document.querySelector('.stepsBar .ldBar-label').innerHTML = "350/1000<br>STEPS";
	caloriesBar = new ldBar('.caloriesBar', {
		"stroke": "#f5bb26",
		"stroke-width": "10",
		"value": "60",
		"path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
	});
	document.querySelector('.caloriesBar .ldBar-label').innerHTML = "467/870<br>CALORIES";
	milesBar = new ldBar('.milesBar', {
		"stroke": "#f5bb26",
		"stroke-width": "10",
		"value": "90",
		"path": "M3.999999999999999 84.870489570875Q0 77.94228634059948 3.999999999999999 71.01408311032397L41 6.92820323027551Q45 0 53 0L127 0Q135 0 139 6.92820323027551L176 71.01408311032397Q180 77.94228634059948 176 84.870489570875L139 148.95636945092346Q135 155.88457268119896 127 155.88457268119896L53 155.88457268119896Q45 155.88457268119896 41 148.95636945092346Z"
	});
	document.querySelector('.milesBar .ldBar-label').innerHTML = "1.98/2.00<br>MILES";
}

waitForEl('#map', function () {
	createProgressBars();
	map = L.map('map').setView(["55.7594255", "-4.1497845"], 16);
	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);
	getCurrentPosition();
})

