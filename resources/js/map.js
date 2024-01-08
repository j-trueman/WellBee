
console.log("Script Connected");
if ($('#jqueryconnector')) {
	console.log("JQuery Loaded Successfully");
};

let map;

const sock = new WebSocket('ws://192.168.1.69:8080/ws');
mapShouldTrack = true;

sock.addEventListener("message", async (event) => {
	positionData = event.data.split(',');
	console.log(`Position: ${positionData[0]},${positionData[1]}`);
	console.log(`Accuracy: ${positionData[2]}`);
	console.log(`Speed: ${positionData[3]}`);
	if (mapShouldTrack) {
		map.setView([positionData[0], positionData[1]], 16);
		document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-active.svg')"
	} else {
		document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-inactive.svg')"
	}

	if (typeof circle != "undefined") {
		circle.removeFrom(map);
	}
	circle = L.circle([positionData[0], positionData[1]], {
		color: 'lightblue',
		fillColor: '#42e8f4',
		fillOpacity: 0.5,
		radius: parseInt(positionData[2])
	}).addTo(map);
});

sock.addEventListener("open", () => {
	setInterval(() => {
		sock.send("request");
	}, 5000);

})

var waitForEl = function (selector, callback) {
	if (jQuery(selector).length) {
		callback();
	} else {
		setTimeout(function () {
			waitForEl(selector, callback);
		}, 100);
	}
};

waitForEl('#map', function () {
	map = L.map('map').setView(["55.7594255", "-4.1497845"], 16);
	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);
	// recenterButton = document.getElementById('reCenterLocation');

	document.getElementById('reCenterLocation').addEventListener("click", () => {
		if (mapShouldTrack) {
			mapShouldTrack = false;
			document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-inactive.svg')"
		} else {
			mapShouldTrack = true;
			document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-active.svg')"
			if (positionData) {
				map.setView([positionData[0], positionData[1]], 16);
			}
		}
	});

	map.addEventListener("drag", (event) => {
		mapShouldTrack = false;
		document.getElementById('reCenterLocation').style.backgroundImage = "url('/resources/images/locator-inactive.svg')"
	})
})

