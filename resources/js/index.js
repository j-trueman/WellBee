
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

}

waitForEl('#map', function () {
	createProgressBars();
	map = L.map('map').setView(["55.7594255", "-4.1497845"], 16);
	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);
	function onMapClick(e) {
		alert("You clicked the map at " + e.latlng);
	}

	map.on('click', onMapClick);
	// getCurrentPosition();
})

