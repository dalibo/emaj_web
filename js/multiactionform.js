//
// Various js functions to let multiactions tables and table filters work
//

//
// Functions used to handle the multi-actions tables
//

function checkSelect(action, form_id) {

	var inputs = document.getElementById(form_id).getElementsByTagName('input');

	for (var i=0; i<inputs.length; i++) {
		if (inputs[i].type == 'checkbox') {
			if (action == 'all') inputs[i].checked = true;
			if (action == 'filtered') inputs[i].checked = !(inputs[i].parentNode.parentNode.classList.contains("filtered"));
			if (action == 'none') inputs[i].checked = false;
			if (action == 'invert') inputs[i].checked = !(inputs[i].checked);
		}
	}
}

function countChecked(form_id) {

	// count checked
	var inputs = document.getElementById(form_id).getElementsByTagName('input');
	var cnt = 0;
	for (var i=0; i<inputs.length; i++) {
		if (inputs[i].type == 'checkbox' && inputs[i].checked) {
			cnt++;
		}
	}
	// insert the counter into the selectcounter th of the form
	var textTitle = document.getElementById(form_id).getElementsByTagName('th');
	for (var i=0; i<textTitle.length; i++) {
		if (textTitle[i].id == 'selectedcounter') {
			textTitle[i].innerHTML = textTitle[i].innerHTML.replace(/\(\d+\)/,"("+cnt+")");
		}
	}
	// if the counter is 0, disable all buttons of the multiactions table
	var buttons = document.getElementById(form_id).getElementsByTagName('button');
	for (var i=0; i<buttons.length; i++) {
		if (! buttons[i].classList.contains('reset_'+form_id)) {
			buttons[i].disabled = (cnt == 0);
		}
	}
}

//
// JQuery functions for the tablesorter filter feature.
// It allows to:
//   - display or hide the filter row on demand
//   - have a Reset button on the filter row
//

function addFilterResetButton(form_id) {
	// Add the Reset button in the first column of the filter rows once it is generated
	$("#" + form_id + " .tablesorter-filter-row td:first").html(
		'<button type="button" class="filterreset tablesorter-filter reset_' + form_id + '" onclick="resetFilter(\'' + form_id + '\')">Reset</button>');
}

function addFilterEvent(form_id) {
	// Add a filterStart event that disables the filter hide capability
	$("#" + form_id + " table").on('filterStart', function(ev, filters){
		// determine whether any filter is currently used
		var filtersUsed = false;
		if (filters.length > 0) {
			for (var i = 0; i < filters.length; i++) {
				if (filters[i] != undefined && filters[i] != '') filtersUsed = true;
			}
		}
		// Depending on the result, set the action or the noaction class to the filter icon
		if (filtersUsed)
			$("#" + form_id + " table th img").removeClass('action').addClass('noaction');
		else
			$("#" + form_id + " table th img").removeClass('noaction').addClass('action');
	});
}

function resetFilter(form_id) {
	$("#" + form_id + " table").trigger('filterReset');
}

function showHideFilterRow(form_id) {
	// Hide or Display the filter row, if the filter icon is active (has the 'action' class).

	if ($('#' + form_id + ' table th img').hasClass('action'))
		if ($('#' + form_id + ' .tablesorter-filter-row').hasClass('hideme'))
		// display the filter row
			$('#' + form_id + ' .tablesorter-filter-row').removeClass('hideme');
		else
		// hide the filter row
			$('#' + form_id + ' .tablesorter-filter-row').addClass('hideme');
}
