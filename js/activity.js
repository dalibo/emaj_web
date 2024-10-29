//
// javascript functions for activity.php
//

/// resetForm() is called by the Reset button. It resets field to their default value instead of their previous value.

function resetForm() {
	$('input[name="groups-include"]').val('');
	$('input[name="tables-include"]').val('');
	$('input[name="sequences-include"]').val('');
	$('input[name="groups-exclude"]').val('');
	$('input[name="tables-exclude"]').val('');
	$('input[name="sequences-exclude"]').val('');
	$('input[name="max-groups"]').val('5');
	$('input[name="max-tables"]').val('20');
	$('input[name="max-sequences"]').val('20');
	$('input:radio[name="sort"][value="latest-mark"]').prop('checked', true);
}

//
/// Functions used for auto-refresh
//
let autorefreshTimeout;

// Function to start and stop the page automatic refresh
function toggleAutoRefresh(input, url) {
	if (input.checked) {
		window.location.replace(url);
	} else {
		clearTimeout(autorefreshTimeout);
	}
}

// Function to schedule the page reload
function schedulePageReload(timer, url) {
	autorefreshTimeout = setTimeout(function() {window.location.replace(url);}, timer * 1000);
}

// Function to setup the onchange event on the form inputs. It is called just after the form is displayed.
function setOnchangeEvent() {
	$('#resetButton').on( "click", function() { disableAutoRefresh(); } );
	$('.form-input').on( "input", function() { disableAutoRefresh(); } );
	$('input[type=radio][name="sort"]').on("change", function() { disableAutoRefresh(); });
}

// Function do disable the autorefresh slider.
function disableAutoRefresh() {
	clearTimeout(autorefreshTimeout);
	$('input[type=checkbox][name="autorefresh"]').prop('disabled', true);
	$('span.autorefresh-label').addClass("autorefresh-disable");
}
