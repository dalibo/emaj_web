<?php

	/*
	 * Actions on tables.
	 * This file is included into schemas.php and tblproperties.php
	 */

	/**
	 * Prepare assign tables to a group: ask for properties and confirmation
	 */
	function assign_tables() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbTbl, $tablesList, $fullList) = processMultiActions($_REQUEST['ma'], 'table');
		} else {
			$nbTbl = 1;
			$tablesList = $_REQUEST['table'];
		}

		// Prepare the action part of potential error messages
		if ($nbTbl == 1)
			$errMsgAction = sprintf($lang['strassigntableerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList));
		else
			$errMsgAction = sprintf($lang['strassigntableserr'], $nbTbl, htmlspecialchars($_REQUEST['schema']));

		// Check that the schema and the tables still exist.
		checkRelations($_REQUEST['schema'], $tablesList, 'table', $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strassigntable']);

		// Get created group names.
		$knownGroups = $emajdb->getCreatedGroups();

		// Get tablespaces the current user can see.
		$knownTsp = $emajdb->getKnownTsp();

		// Get the number of application triggers held by these tables.
		$nbAppTriggers = $emajdb->getNbAppTriggers($_REQUEST['schema'], $tablesList);

		// Build the form
		if ($nbTbl > 1) {
			echo "<p>" . sprintf($lang['strconfirmassigntables'], $nbTbl, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmassigntable'], $_REQUEST['schema'], $tablesList) . "</p>\n";
		}

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"assign_tables_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbtables\" value=\"{$nbTbl}\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['strgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"group\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// priority level
		echo "\t<div class=\"form-label\">{$lang['strenterpriority']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "<input type=\"number\" name=\"priority\" class=\"priority\" min=\"0\" max=\"2147483647\" value=\"\" />";
		echo "</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strpriorityhelp']}\"/></div>\n";

		// data log tablespace
		echo "\t<div class=\"form-label\">{$lang['strenterlogdattsp']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"logdattsp\"";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option checked value=''>" . htmlspecialchars($lang['strdefaulttsp']) . "\n";
			foreach($knownTsp as $r)
				echo "\t\t<option>"  . htmlspecialchars($r['spcname']) . "\n";
		}
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// index log tablespace
		echo "\t<div class=\"form-label\">{$lang['strenterlogidxtsp']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"logidxtsp\"";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option checked value=''>" . htmlspecialchars($lang['strdefaulttsp']) . "\n";
			foreach($knownTsp as $r)
				echo "\t\t<option>" . htmlspecialchars($r['spcname']) . "\n";
		}
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"ASSIGN_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";

		echo"</div>\n";

		if ($nbAppTriggers > 0 ) {
			echo "<p>" . sprintf($lang['strtableshavetriggers'], $nbAppTriggers) . "</p>\n";
		}

		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"assigntable\" value=\"{$lang['strassign']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform tables assignment into a tables group
	 */
	function assign_tables_ok() {
		global $lang, $data, $emajdb, $_reload_browser;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbtables'] == 1)
			$errMsgAction = sprintf($lang['strassigntableerr2'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['tables']), $_POST['group']);
		else
			$errMsgAction = sprintf($lang['strassigntableserr2'], $_POST['nbtables'], htmlspecialchars($_POST['schema']), $_POST['group']);

		// Check that the schema and the tables still exist
		checkRelations($_REQUEST['schema'], $_POST['tables'], 'table', $errMsgAction);

		// Check that the tables group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction);

		// OK, process the tables assignment
		// get the list of emaj_schema before the assignment
		$emajSchemasBefore = $emajdb->getEmajSchemasList();

		$nbTables = $emajdb->assignTables($_POST['schema'], $_POST['tables'], $_POST['group'],
							$_POST['priority'], $_POST['logdattsp'], $_POST['logidxtsp'], $finalMark);

		// Check the result and exit
		if ($nbTables >= 0) {
			// if new emaj schemas have been created, reload the browser
			$emajSchemasAfter = $emajdb->getEmajSchemasList();
			if ($emajdb->getEmajSchemasList() <> $emajSchemasBefore)
				$_reload_browser = true;
			if ($nbTables > 1)
				doDefault(sprintf($lang['strassigntablesok'], $nbTables, htmlspecialchars($_POST['group'])));
			else
				doDefault(sprintf($lang['strassigntableok'], $nbTables, htmlspecialchars($_POST['group'])));
		} else {
			doDefault('', $errMsgAction);
		}
	}

	/**
	 * Prepare move tables to another group: ask for confirmation
	 */
	function move_tables() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbTbl, $tablesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'table', true);
		} else {
			$nbTbl = 1;
			$tablesList = $_REQUEST['table'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbTbl == 1)
			$errMsgAction = sprintf($lang['strmovetableerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList));
		else
			$errMsgAction = sprintf($lang['strmovetableserr'], $nbTbl, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strmovetable']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the form
		if ($nbTbl > 1) {
			echo "<p>" . sprintf($lang['strconfirmmovetables'], $nbTbl, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmmovetable'], $_REQUEST['schema'], $tablesList, $groupsList) . "</p>\n";
		}

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"move_tables_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbtables\" value=\"{$nbTbl}\" />\n";
		echo "<input type=\"hidden\" name=\"oldgroups\" value=\"", htmlspecialchars($groupsList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['strnewgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"newgroup\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"MOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo"</div>\n";

		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"movetable\" value=\"{$lang['strmove']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform tables move into another tables group
	 */
	function move_tables_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbtables'] == 1)
			$errMsgAction = sprintf($lang['strmovetableerr2'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['tables']), htmlspecialchars($_POST['oldgroups']), htmlspecialchars($_POST['newgroup']));
		else
			$errMsgAction = sprintf($lang['strmovetableserr2'], $_POST['nbtables'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['newgroup']));

		$allGroups = $_POST['oldgroups'] . ', ' . $_POST['newgroup'];

		// Check that the tables group still exists
		recheckGroups($allGroups, $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($allGroups, $_POST['mark'], $errMsgAction);

		// OK, process the tables move
		$nbTables = $emajdb->moveTables($_POST['schema'], $_POST['tables'], $_POST['newgroup'], $finalMark);

		// Check the result and exit
		if ($nbTables >= 0)
			if ($nbTables > 1)
				doDefault(sprintf($lang['strmovetablesok'], $nbTables, htmlspecialchars($_POST['newgroup'])));
			else
				doDefault(sprintf($lang['strmovetableok'], $nbTables, htmlspecialchars($_POST['newgroup'])));
		else
			doDefault('', $errMsgAction);
	}

	/**
	 * Prepare modify tables : ask for properties and confirmation
	 */
	function modify_tables() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbTbl, $tablesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'table', true);
		} else {
			$nbTbl = 1;
			$tablesList = $_REQUEST['table'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbTbl == 1)
			$errMsgAction = sprintf($lang['strmodifytableerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList));
		else
			$errMsgAction = sprintf($lang['strmodifytableserr'], $nbTbl, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strmodifytable']);

		// Get the tables properties
		$properties = $emajdb->getTablesProperties($_REQUEST['schema'],$tablesList);

		// Prepare the current values to display
		if ($properties->fields['nb_priority'] == 1)
			$currentPriority = $properties->fields['min_priority'];
		else
			$currentPriority = "<i>" . sprintf($lang['strdifferentvalues'], $properties->fields['nb_priority']) . "</i>";

		if ($properties->fields['nb_log_dat_tsp'] == 1) {
			$currentLogDatTsp = $properties->fields['min_log_dat_tsp'];
			if ($currentLogDatTsp == '')
				$currentLogDatTsp = htmlspecialchars($lang['strdefaulttsp']);
		} else {
			$currentLogDatTsp = "<i>" . sprintf($lang['strdifferentvalues'], $properties->fields['nb_log_dat_tsp']) . "</i>";
		}

		if ($properties->fields['nb_log_idx_tsp'] == 1) {
			$currentLogIdxTsp = $properties->fields['min_log_idx_tsp'];
			if ($currentLogIdxTsp == '')
				$currentLogIdxTsp = htmlspecialchars($lang['strdefaulttsp']);
		} else {
			$currentLogIdxTsp = "<i>" . sprintf($lang['strdifferentvalues'], $properties->fields['nb_log_idx_tsp']) . "</i>";
		}

		// Get tablespaces the current user can see
		$knownTsp = $emajdb->getKnownTsp();

		// Build the form
		if ($nbTbl == 1) {
			echo "<p>" . sprintf($lang['strconfirmmodifytable'], $_REQUEST['schema'], $tablesList, $groupsList) . "</p>\n";
		} else {
			echo "<p>" . sprintf($lang['strconfirmmodifytables'], $nbTbl, $_REQUEST['schema']) . "</p>\n{$fullList}";
		}
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"modify_tables_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbtables\" value=\"{$nbTbl}\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container-5c\">\n";

		// Header row
		echo "\t<div></div>\n";
		echo "\t<div></div>\n";
		echo "\t<div class=\"form-header\">{$lang['strcurrentvalue']}</div>\n";
		echo "\t<div></div>\n";
		echo "\t<div class=\"form-header\">{$lang['strnewvalue']}</div>\n";

		// priority level
		echo "\t<div class=\"form-label\">{$lang['strenterpriority']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strpriorityhelp']}\"/></div>\n";
		echo "\t<div id=\"priorityvalue\" class=\"form-value style=\"justify-content: right;\"\">$currentPriority</div>\n";
		echo "\t<div class=\"form-button\"><button type=\"button\" onclick=\"javascript:toogleInput(this, 'priority');\">&gt;&gt;</button></div>\n";
		echo "\t<div class=\"form-input\">";
		echo "<input id=\"priorityinput\" type=\"number\" name=\"priority\" class=\"priority\" min=\"0\" max=\"2147483647\" value=\"\" disabled/>";
		echo "</div>\n";

		// data log tablespace
		echo "\t<div class=\"form-label\">{$lang['strenterlogdattsp']}</div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "\t<div id=\"logdattspvalue\" class=\"form-value\">$currentLogDatTsp</div>\n";
		echo "\t<div class=\"form-button\"><button type=\"button\" onclick=\"javascript:toogleInput(this, 'logdattsp');\">&gt;&gt;</button></div>\n";
		echo "\t<div class=\"form-input\"><select id=\"logdattspinput\" name=\"logdattsp\" disabled";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option checked value=''>" . htmlspecialchars($lang['strdefaulttsp']) . "\n";
			foreach($knownTsp as $r) {
				echo "\t\t<option>", htmlspecialchars($r['spcname']), "\n";
			}
		}
		echo "\t</select></div>\n";

		// index log tablespace
		echo "\t<div class=\"form-label\">{$lang['strenterlogidxtsp']}</div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "\t<div id=\"logidxtspvalue\" class=\"form-value\">$currentLogIdxTsp</div>\n";
		echo "\t<div class=\"form-button\"><button type=\"button\" onclick=\"javascript:toogleInput(this, 'logidxtsp');\">&gt;&gt;</button></div>\n";
		echo "\t<div class=\"form-input\"><select id=\"logidxtspinput\" name=\"logidxtsp\" disabled";
		if (empty($knownTsp))
			echo " disabled>";
		else {
			echo ">";
			echo "\t\t<option checked value=''>" . htmlspecialchars($lang['strdefaulttsp']) . "\n";
			foreach($knownTsp as $r) {
				echo "\t\t<option>", htmlspecialchars($r['spcname']), "\n";
			}
		}
		echo "\t</select></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo "\t<div class=\"form-input\" style=\"grid-column: span 3;\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"MODIFY_%\" /></div>\n";

		echo"</div>\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" id=\"ok\" name=\"modifytable\" value=\"{$lang['strupdate']}\" disabled/>\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform tables modification
	 */
	function modify_tables_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbtables'] == 1)
			$errMsgAction = sprintf($lang['strmodifytableerr'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['tables']));
		else
			$errMsgAction = sprintf($lang['strmodifytableserr'], $_POST['nbtables'], htmlspecialchars($_POST['schema']));

		// Check that the tables group still exists
		recheckGroups($_POST['groups'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// OK,process the tables properties changes
		$nbTables = $emajdb->modifyTables($_POST['schema'], $_POST['tables'],
							isset($_POST['priority']) ? $_POST['priority'] : null,
							isset($_POST['logdattsp']) ? $_POST['logdattsp'] : null,
							isset($_POST['logidxtsp']) ? $_POST['logidxtsp'] : null,
							$finalMark);

		// Check the result and exit
		if ($nbTables >= 0)
			doDefault(sprintf($lang['strmodifytablesok'], $nbTables));
		else
			doDefault('', $errMsgAction);
	}

	/**
	 * Prepare remove tables: ask for confirmation
	 */
	function remove_tables() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbTbl, $tablesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'table', true);
		} else {
			$nbTbl = 1;
			$tablesList = $_REQUEST['table'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbTbl == 1)
			$errMsgAction = sprintf($lang['strremovetableerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList), htmlspecialchars($groupsList));
		else
			$errMsgAction = sprintf($lang['strremovetableserr'], $nbTbl, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strremovetable']);

		// Build the form
		if ($nbTbl > 1) {
			echo "<p>" . sprintf($lang['strconfirmremovetables'], $nbTbl, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmremovetable'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($tablesList), htmlspecialchars($groupsList)) . "</p>\n";
		}

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"remove_tables_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"tables\" value=\"", htmlspecialchars($tablesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbtables\" value=\"{$nbTbl}\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"REMOVE_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo"</div>\n";

		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"removetable\" value=\"{$lang['strremove']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform tables remove from their tables group
	 */
	function remove_tables_ok() {
		global $lang, $data, $emajdb, $_reload_browser;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbtables'] == 1)
			$errMsgAction = sprintf($lang['strremovetableerr'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['tables']), htmlspecialchars($_POST['groups']));
		else
			$errMsgAction = sprintf($lang['strremovetableserr'], $_POST['nbtables'], htmlspecialchars($_POST['schema']));

		// Check that the tables group still exists
		recheckGroups($_POST['groups'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// OK, process the tables removal
		// get the list of emaj_schema before the removal
		$emajSchemasBefore = $emajdb->getEmajSchemasList();

		$nbTables = $emajdb->removeTables($_POST['schema'], $_POST['tables'], $finalMark);

		// Check the result and exit
		if ($nbTables >= 0) {
			// reload the browser only if emaj schemas have been dropped
			if ($emajdb->getEmajSchemasList() <> $emajSchemasBefore)
				$_reload_browser = true;
			if ($nbTables > 1)
				doDefault(sprintf($lang['strremovetablesok'], $nbTables));
			else
				doDefault(sprintf($lang['strremovetableok'], $nbTables));
		} else {
			doDefault('', $errMsgAction);
		}
	}

?>
