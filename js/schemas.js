//
// js functions specific for the schemas.php page
//

var numberModifiedProperties = 0;

//
// Function to enable / disable an input in the form that selects the table properties to change.
//
function toogleInput(button, id) {
	if (button.textContent == '>>') {
// Enable the input
		button.textContent = '<<';
		$("#" + id + "input").prop('disabled', false);
		$("#" + id + "value").addClass("form-value-disabled");
		numberModifiedProperties++;
	} else {
// Disable the input
		button.textContent = '>>';
		$("#" + id + "input").prop('disabled', true);
		$("#" + id + "input").val('');
		$("#" + id + "value").removeClass("form-value-disabled");
		numberModifiedProperties--;
	}
// Enable/disable the submit button depending on the number of properties to modify
	if (numberModifiedProperties > 0) {
		$("#ok").prop('disabled', false);
	} else {
		$("#ok").prop('disabled', true);
	}
}
