
console.log("Script Connected");
if ($('#jqueryconnector')) {
	console.log("JQuery Loaded Successfully");
};

let map;


var waitForEl = function (selector, callback) {
	if (jQuery(selector).length) {
		callback();
	} else {
		setTimeout(function () {
			waitForEl(selector, callback);
		}, 100);
	}
};

const successCallback = (position) => {
	map = L.map('map').setView([position.coords.latitude, position.coords.longitude], 16);
	L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 19,
		attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
	}).addTo(map);
	let marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
}

const errorCallback = (error) => {
	console.log(error);
}

waitForEl('#map', function () {
	navigator.geolocation.getCurrentPosition(successCallback, errorCallback, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
	var circle = new ProgressBar.Path('#stepsBar', {
		color: '#FCB03C',
		strokeWidth: 5,
		trailWidth: 1,
		text: {
			value: '1'
		}
	});
	circle.set(0.5);
})

