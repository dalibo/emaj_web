//
// js functions specific for the emajrollbacks.php page
//
let autorefreshTimeout;

//
// Function to start and stop the page automatic refresh
//
function toggleAutoRefresh(input, url) {
	if (input.checked) {
		window.location.replace(url);
	} else {
		clearTimeout(autorefreshTimeout);
	}
}

//
// Function to schedule the page reload
//
function schedulePageReload(timer, url) {
	autorefreshTimeout = setTimeout(function() {window.location.replace(url);}, timer * 1000);
}
