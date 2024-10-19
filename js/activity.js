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
		disableInput(true);
	} else {
		clearTimeout(autorefreshTimeout);
		disableInput(false);
	}
}

// Disable or enable input elements of the form.
function disableInput(disable) {
	$('input[name="groups-include"]').prop('disabled', disable);
	$('input[name="tables-include"]').prop('disabled', disable);
	$('input[name="sequences-include"]').prop('disabled', disable);
	$('input[name="groups-exclude"]').prop('disabled', disable);
	$('input[name="tables-exclude"]').prop('disabled', disable);
	$('input[name="sequences-exclude"]').prop('disabled', disable);
	$('input[name="max-groups"]').prop('disabled', disable);
	$('input[name="max-tables"]').prop('disabled', disable);
	$('input[name="max-sequences"]').prop('disabled', disable);
	$('input:radio[name="sort"]').prop('disabled', disable);
	$('input[name="refresh"]').prop('disabled', disable);
	$('#resetButton').prop('disabled', disable);
}

// Function to schedule the page reload
function schedulePageReload(timer, url) {
	autorefreshTimeout = setTimeout(function() {window.location.replace(url);}, timer * 1000);
}
