//
// js functions specific for the emajrollbacks.php page
//
let autorefreshTimeout;

//
// Function to start and stop the page automatic refresh
// At autorefresh start time, it sets a cookie containing the rlbk_id of the displayed rollback
//
function toggleAutoRefresh(input, rlbkid, url) {
	if (input.checked) {
		document.cookie = "autorefresh_rlbkid=" + rlbkid + "; SameSite=Strict";
		window.location.replace(url);
	} else {
		clearTimeout(autorefreshTimeout);
		deleteARCookie();
	}
}

//
// Function to schedule the page reload
//
function schedulePageReload(timer, url) {
	autorefreshTimeout = setTimeout(function() {window.location.replace(url);}, timer * 1000);
}

//
// Function do delete the autorefresh_rlbkid cookie
//
function deleteARCookie() {
	document.cookie = "autorefresh_rlbkid=; expires=Thu, 01 Jan 1970 00:00:00 UTC; SameSite=Strict";
}
