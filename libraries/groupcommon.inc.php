<?php

/********************************************************************************************************
 * Elementary functions used by action functions located into groupactions.inc.php.
 *******************************************************************************************************/

	/**
	 * Callback functions.
	 */

	// Callback function to dynamicaly modify the group state column content
	// It replaces the database value by an icon
	function renderGroupState($val) {
		global $misc, $lang;

		if ($val == 'IDLE') {
			$icon = $misc->icon('EmajIdle');
			$alt = $lang['stridle'];
		} else {
			$icon = $misc->icon('EmajLogging');
			$alt = $lang['strlogging'];
		}
		return "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
	}

	// Callback function to dynamicaly translate a boolean column into an icon
	function renderBooleanIcon($val) {
		global $misc;

		if ($val == 't') {
			$icon = 'Checkmark';
		} else {
			$icon = 'RedX';
		}
		return "<img src=\"" . $misc->icon($icon) . "\" class=\"cellicon\" />";
	}

	// Callback function to dynamicaly add an icon to each rollback execution report
	function renderRlbkExecSeverity($val) {
		global $misc;

		if ($val == 'Notice') {
			$icon = 'Checkmark';
		} else {
			$icon = 'Warning';
		}
		return "<img src=\"{$misc->icon($icon)}\" alt=\"$val\" class=\"cellicon\"/>";
	}

	// Callback function to dynamicaly transform a message severity level into an icon
	function renderMsgSeverity($val) {
		global $misc;

		if ($val == '1' || $val == '2') {
			$icon = 'RedX';
			$alt = 'Error';
		} elseif ($val == '3') {
			$icon = 'Warning';
			$alt = 'Warning';
		} elseif ($val == '4') {
			$icon = 'Checkmark';
			$alt = 'OK';
		} else {
			return '?';
		}
		return "<img src=\"{$misc->icon($icon)}\" alt=\"$val\" class=\"cellicon\"/>";
	}

	/**
	 * Process the click on the <cancel> button.
	 */
	function processCancelButton() {
		global $misc;

		if (isset($_POST['cancel'])) {
			doDefault();
			$misc->printFooter();
			exit();
		}
	}

	/**
	 * Check that there is at least one selected group when multiaction is performed.
	 * This should always be the case thanks to the JS code in pages.
	 */
	function checkGroupsSelected() {
		global $lang;

		if (!isset($_REQUEST['ma'])) {
			doDefault('', $lang['strnoselectedgroup']);
			return;
		}
	}

	/**
	 * Transforms a groups array into a groups list, separated by ', '
	 */
	function groupsArray2list($groups) {

		$list = '';
		foreach($groups as $elem) {
			$a = unserialize(htmlspecialchars_decode($elem, ENT_QUOTES));
			$list .= $a['group'] . ', ';
		}
		$list = substr($list, 0, strlen($list) - 2);
		return($list);
	}

	/**
	 * Check that groups still exists and, optionaly, are still in the required logging or idle state
	 */
	function recheckGroups($groupsList, $errMsgAction, $requiredLoggingState = null) {
		global $lang, $emajdb, $_reload_browser, $misc;

		// Check the groups existence
		$missingGroups = $emajdb->missingGroups($groupsList);
		if ($missingGroups->fields['nb_groups'] > 0) {
			if ($missingGroups->fields['nb_groups'] == 1)
				// One group doesn't exist anymore
				$errMsg = sprintf($lang['strgroupmissing'], htmlspecialchars($missingGroups->fields['groups_list']));
			else
				// Several groups do not exist anymore
				$errMsg = sprintf($lang['strgroupsmissing'], $missingGroups->fields['nb_groups'], htmlspecialchars($missingGroups->fields['groups_list']));
			groupDropped('', $errMsgAction, $errMsg);
			$_reload_browser = true;
			$misc->printFooter();
			exit();
		}

		if (! is_null($requiredLoggingState)) {
			// The groups state has to be checked

			if ($requiredLoggingState == 'IDLE') {
				$errorGroups = $emajdb->loggingGroups($groupsList);
				$langMsgSuffix = 'stopped';
			} else {
				$errorGroups = $emajdb->idleGroups($groupsList);
				$langMsgSuffix = 'started';
			}
			$errorMessage = '';
			if ($errorGroups->fields['nb_groups'] == 1) {
				// One group is not in the expected state anymore
				$errorMessage = sprintf($lang['strgroupnot'.$langMsgSuffix], htmlspecialchars($errorGroups->fields['groups_list']));
			}
			if ($errorGroups->fields['nb_groups'] > 1) {
				// Several groups are not in the expected state anymore
				$errorMessage = sprintf($lang['strgroupsnot'.$langMsgSuffix], $errorGroups->fields['nb_groups'], htmlspecialchars($errorGroups->fields['groups_list']));
			}
			if ($errorMessage != '') {
				doDefault('', $errMsgAction . '<br>' . $errorMessage);
				$misc->printFooter();
				exit();
			}
		}
	}

	/**
	 * Check that one or several marks still exist for a group. The group has been rechecked just before.
	 */
	function recheckMarksGroup($group, $marksList, $errMsgAction) {
		global $lang, $emajdb, $misc;

		// Check the marks existence
		$missingMarks = $emajdb->missingMarksGroup($group, $marksList);
		if ($missingMarks->fields['nb_marks'] > 0) {
			if ($missingMarks->fields['nb_marks'] == 1)
				// One mark doesn't exist anymore
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strmarkmissing'], htmlspecialchars($missingMarks->fields['marks_list'])));
			else
				// Several marks do not exist anymore
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strmarksmissing'], $missingMarks->fields['nb_marks'], htmlspecialchars($missingMarks->fields['marks_list'])));
			$misc->printFooter();
			exit();
		}
	}

	/**
	 * Check that a mark still exist for several groups. The group has been rechecked just before.
	 */
	function recheckMarkGroups($groupList, $mark, $errMsgAction) {
		global $lang, $emajdb, $misc;

		// Check the mark existence for the groups
		$missingGroups = $emajdb->missingMarkGroups($groupList, $mark);
		if ($missingGroups->fields['nb_groups'] > 0) {
			if ($missingGroups->fields['nb_groups'] == 1)
				// The mark doesn't exist anymore for one group
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strmissingmarkgroup'], htmlspecialchars($missingGroups->fields['groups_list'])));
			else
				// The mark doesn't exist anymore for several groups
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strmissingmarkgroups'], $missingGroups->fields['nb_groups'], htmlspecialchars($missingGroups->fields['groups_list'])));
			$misc->printFooter();
			exit();
		}
	}

	/**
	 * Check that a supplied mark name is valid for one or several groups.
	 * It returns the mark name, modified with resolved % characters, if any.
	 * If the mark is not valid or is already known by any groups, it directly branches to the calling page with an error message.
	 */
	function checkNewMarkGroups($groupsList, $mark, $errMsgAction, $duplicateCheck = 1) {
		global $emajdb, $lang, $misc;

		// Check the forbidden values.
		if ($mark == '' or $mark == 'EMAJ_LAST_MARK') {
			$errorMessage = sprintf($lang['strinvalidmark'], htmlspecialchars($mark));
			doDefault('', $errMsgAction . '<br>' . $errorMessage);
			$misc->printFooter();
			exit();
		}

		// Resolve the mark name. Replace the % characters by the time of day, in format 'HH24.MI.SS.MS'.
		$finalMarkName = str_replace('%', date('H.i.s.') . substr(microtime(),2,3), $mark);

		// Check the new mark doesn't already exist for the groups, if requested.
		if ($duplicateCheck) {
			$errorGroups = $emajdb->knownMarkGroups($groupsList, $finalMarkName);
			$errorMessage = '';
			if ($errorGroups->fields['nb_groups'] == 1) {
				// The mark already exists for one group
				$errorMessage = sprintf($lang['strduplicatemarkgroup'], htmlspecialchars($mark), htmlspecialchars($errorGroups->fields['groups_list']));
			}
			if ($errorGroups->fields['nb_groups'] > 1) {
				// The mark already exist for several groups
				$errorMessage = sprintf($lang['strduplicatemarkgroups'], htmlspecialchars($mark),
										$errorGroups->fields['nb_groups'], htmlspecialchars($errorGroups->fields['groups_list']));
			}
			if ($errorMessage != '') {
				doDefault('', $errMsgAction . '<br>' . $errorMessage);
				$misc->printFooter();
				exit();
			}
		}

		return $finalMarkName;
	}

	/**
	 * Check an ADO return code. In case of error, display an error message and exit the current function.
	 */
	function checkADOReturnCode($AdoRetCode, $errMsgAction) {
		global $lang, $misc;

		if ($AdoRetCode < 0) {
			$errorMessage = sprintf($lang['stradoreturncode'], htmlspecialchars($AdoRetCode));
			doDefault('', $errMsgAction . '<br>' . $errorMessage);
			$misc->printFooter();
			exit();
		}

		return;
	}

?>