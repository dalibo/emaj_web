//
// Various js functions to let multiactions tables and table filters work
// It mainly uses JQuery
//

//
// Functions used to handle the multi-actions tables
//

function checkSelect(action, form_id) {

	var inputs = $("#" + form_id + " input:checkbox");

	for (var i=0; i<inputs.length; i++) {
		// general selectors
		if (action == 'all') inputs[i].checked = true;
		if (action == 'filtered') inputs[i].checked = !(inputs[i].parentNode.parentNode.classList.contains("filtered"));
		if (action == 'none') inputs[i].checked = false;
		if (action == 'invert') inputs[i].checked = !(inputs[i].checked);

		// specific selectors
		if (action == 'notassigned') {
			// look for .multi_assign_tblseq td on the same row and check if not empty
			inputs[i].checked = (inputs[i].parentNode.parentNode.getElementsByClassName('multi_assign_tblseq')[0].innerHTML != '');
		}
	}
}

function countChecked(form_id) {

	// count checked
	var cnt = $("#" + form_id + " input:checkbox:checked").length;

	// insert the counter into the selectcounter th of the form
	$("#" + form_id + " .selectedcounter").each(function(i, elem) {
		// There should be only one selected element here
		$(elem).html( $(elem).html().replace(/\(\d+\)/,"("+cnt+")") );
	});

	// for each multiaction button or icon, disable the button if:
	// - either the number of checked rows is 0
	// - or at least one checked row has its related button disabled (empty cell in the table for the associated action column
	var buttons = $("#" + form_id).find("input[type=image], button:not(.reset_" + form_id + ")").each(function(i,elem) {
		nbHiddenButtons = 0;
		if (cnt > 0) {
			nbHiddenButtons = $("#" + form_id + " input:checkbox:checked").parents("tr").find(".multi_" + $(elem).attr("value") + ":empty").length;
		}
		$(elem).attr("disabled", (cnt == 0 || nbHiddenButtons > 0));
	});
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
