//
// js functions specific for the sqledit.php page
//

//
// Functions used to handle the options for the sql generation of changes dumps
//

/// initOptions() is called just after the form has been displayed

function initOptions(hasPk) {

// Disable the emaj_tuple column checkbox
	$('input:checkbox[name="emajCols[]"][value="emaj_tuple"]').prop('disabled',true);

// When submitting the form, remove the disabled attribute on the emaj_tuple checkbox so that this value is passed anyway
	$("form").submit(function() {
		$('input:checkbox[name="emajCols[]"][value="emaj_tuple"]').prop('disabled',false);
	});

// Set the defaults values
	validationSelect('NONE');

// If the table has no PK, disable the impacted options
	if (! hasPk) {
		$('input[name="consolidation"]').prop('disabled', true);
		$('input[name="colsOrder"]').prop('disabled', true);
		$('input[name="orderBy"]').prop('disabled', true);
	}
}


// validationSelect() process options depending on the selected validation level

function validationSelect(level) {

	switch(level) {
		case 'NONE':
			$('input:checkbox[name="emajCols[]"][value="emaj_gid"]').prop('disabled',true);
			$('input:checkbox[name="emajCols[]"][value="emaj_gid"]').prop('checked',true);
			emajColsSelect('ALL');
			$('input:radio[name="colsOrder"][value="LOG_TABLE"]').prop('checked', true);
			$('input:radio[name="orderBy"][value="TIME"]').prop('checked', true);
			$('input:checkbox[name="verbs[]"]').each( function() {
				$(this).prop('disabled', false);
			});
			$('#allVerbs').removeClass('disabled');
			$('input:text[name="roles"]').prop('disabled', false);
			$('input:text[name="roles"]').removeClass('disabled');
			$('#allRoles').removeClass('disabled');
			$('#clearRoles').removeClass('disabled');
			break;
		case 'PARTIAL':
		case 'FULL':
			$('input:checkbox[name="emajCols[]"][value="emaj_gid"]').prop('disabled',false);
			emajColsSelect('MIN');
			$('input:radio[name="colsOrder"][value="PK"]').prop('checked', true);
			$('input:radio[name="orderBy"][value="PK"]').prop('checked', true);
			$('input:checkbox[name="verbs[]"]').each( function() {
					$(this).prop('disabled', true);
			});
			$('#allVerbs').addClass('disabled');
			$('input:text[name="roles"]').prop('disabled', true);
			$('input:text[name="roles"]').addClass('disabled');
			$('#allRoles').addClass('disabled');
			$('#clearRoles').addClass('disabled');
			break;
		default:
			alert('Illegal Value for consolidation level' + level);
	}
}

// allVerbs() set the checked attribute to all SQL verbs

function allVerbs() {

	$('input:checkbox[name="verbs[]"]').each( function() {
		$(this).prop('checked', true);
	});
}

// setRoles() fills the roles text input.

function setRoles(list) {

	$('input:text[name="roles"], textarea').val(list);

}

// emajColsSelect() performs both all and min actions triggered by both links on the emaj columns list

function emajColsSelect(action) {

	switch(action) {
		case 'ALL':
			// check all columns
			$('input:checkbox[name="emajCols[]"]').each( function() {
				$(this).prop('checked', true);
			});
			break;
		case 'MIN':
			// uncheck all not disabled columns (emaj_tuple and emaj_gid if the consolidation level is PARTIAL or FULL)
			$('input:checkbox[name="emajCols[]"]').each( function() {
				if ($(this).prop('disabled') == false)
					$(this).prop('checked', false);
			});
			break;
		default:
			alert('Illegal Value for action ' + action);
	}
}
