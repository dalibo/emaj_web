<?php

/********************************************************************************************************
 * Elementary functions used by action functions located into tblactions.inc.php and seqactions.inc.php
 *******************************************************************************************************/

	/**
	 * Process the click on the <cancel> button.
	 */
	function processCancelButton() {
		global $misc;

		// Call the schemas list display back.
		if (isset($_POST['cancel'])) {
			doDefault();
			$misc->printFooter();
			exit();
		}
	}

	/**
	 * Process the multiactions list.
	 * It returns the number of relations (tables or sequences), the simple relations list the html formatted list,
	 *   and the group names list if the groups processing is requested.
	 */
	function processMultiActions($array, $relkind, $withGroup = false) {
		global $lang;

		$nbRelations = count($array);
		$relationsList = '';
		$groupsList = '';
		$htmlList = "<div class=\"longlist\"><ul>\n";
		foreach($array as $t) {
			$a = unserialize(htmlspecialchars_decode($t, ENT_QUOTES));
			$relationsList .= $a[$relkind] . ', ';
			$htmlList .= "\t<li>";
			if ($withGroup) {
				$htmlList .= sprintf($lang['strthetblseqingroup'], $a[$relkind], $a['group']);
				if (strpos($groupsList, $a['group'] . ', ') === false) {
					$groupsList .= $a['group'] . ', ';
				}
			} else {
				$htmlList .= $a[$relkind];
			}
			$htmlList .= "</li>\n";
		}
		$groupsList = substr($groupsList, 0, strlen($groupsList) - 2);
		$relationsList = substr($relationsList, 0, strlen($relationsList) - 2);
		$htmlList .= "</ul></div>\n";
		return array($nbRelations, $relationsList, $htmlList, $groupsList);
	}

	/**
	 * Check that groups still exists
	 */
	function recheckGroups($groupsList, $errMsgAction) {
		global $lang, $emajdb, $_reload_browser, $misc;

		// Check the groups existence
		$missingGroups = $emajdb->missingGroups($groupsList);
		if ($missingGroups->fields['nb_groups'] > 0) {
			if ($missingGroups->fields['nb_groups'] == 1)
				// One group doesn't exist anymore
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupmissing'], htmlspecialchars($missingGroups->fields['groups_list'])));
			else
				// Several groups do not exist anymore
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsmissing'], $missingGroups->fields['nb_groups'], htmlspecialchars($missingGroups->fields['groups_list'])));
			$_reload_browser = true;
			$misc->printFooter();
			exit();
		}
	}

	/**
	 * Check that a supplied mark name is valid for one or several groups.
	 * It returns the mark name, modified with resolved % characters, if any.
	 * If the mark is not valid or is already known by any groups, it directly branches to the calling page with an error message.
	 */
	function checkNewMarkGroups($groupsList, $mark, $errMsgAction) {
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

		return $finalMarkName;
	}

	/**
	 * Check that a set of tables or sequences in a schema still exists.
	 * Tables and sequences that are assigned to tables group are protected by the main event trigger. But not the others.
	 */
	function checkRelations($schema, $tblSeqsList, $relKind, $errMsgAction) {
		global $emajdb, $lang, $misc;

		// Check the schema already exists
		if (! $emajdb->existsSchema($schema)) {
			$errorMessage = sprintf($lang['strschemamissing'], htmlspecialchars($schema));
			doDefault('', $errMsgAction . '<br>' . $errorMessage);
			$_reload_browser = true;
			$misc->printFooter();
			exit();
		}

		// Check all relations already exist.
		$errorMessage = '';
		$missingTblSeqs = $emajdb->missingTblSeqs($schema, $tblSeqsList, $relKind);
		if ($missingTblSeqs->fields['nb_tblseqs'] > 0) {
			if ($missingTblSeqs->fields['nb_tblseqs'] == 1)
				// One table/sequence doesn't exist anymore
				if ($relKind == 'table') {
					$errorMessage = sprintf($lang['strtablemissing'], htmlspecialchars($schema), htmlspecialchars($missingTblSeqs->fields['tblseqs_list']));
				} else {
					$errorMessage = sprintf($lang['strsequencemissing'], htmlspecialchars($schema), htmlspecialchars($missingTblSeqs->fields['tblseqs_list']));
				}
			else
				// Several tables/sequences do not exist anymore
				if ($relKind == 'table') {
					$errorMessage = sprintf($lang['strtablesmissing'], $missingTblSeqs->fields['nb_tblseqs'], htmlspecialchars($missingTblSeqs->fields['tblseqs_list']));
				} else {
					$errorMessage = sprintf($lang['strsequencesmissing'], $missingTblSeqs->fields['nb_tblseqs'], htmlspecialchars($missingTblSeqs->fields['tblseqs_list']));
				}
		}
		if ($errorMessage != '') {
			doDefault('', $errMsgAction . '<br>' . $errorMessage);
			$misc->printFooter();
			exit();
		}

		return;
	}

?>