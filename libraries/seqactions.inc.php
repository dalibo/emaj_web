<?php

	/*
	 * Actions on sequences.
	 * This file is included into schemas.php and seqproperties.php
	 */

	/**
	 * Prepare assign sequences to a group: ask for properties and confirmation
	 */
	function assign_sequences() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbSeq, $sequencesList, $fullList) = processMultiActions($_REQUEST['ma'], 'sequence');
		} else {
			$nbSeq = 1;
			$sequencesList = $_REQUEST['sequence'];
		}

		// Prepare the action part of potential error messages
		if ($nbSeq == 1)
			$errMsgAction = sprintf($lang['strassignsequenceerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($sequencesList));
		else
			$errMsgAction = sprintf($lang['strassignsequenceserr'], $nbSeq, htmlspecialchars($_REQUEST['schema']));

		// Check that the schema and the sequences still exist
		checkRelations($_REQUEST['schema'], $sequencesList, 'sequence', $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strassignsequence']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the form
		if ($nbSeq > 1) {
			echo "<p>" . sprintf($lang['strconfirmassignsequences'], $nbSeq, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmassignsequence'], $_REQUEST['schema'], $sequencesList) . "</p>\n";
		}

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"assign_sequences_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbsequences\" value=\"{$nbSeq}\" />\n";

		// Display the input fields depending on the context
		echo "<div class=\"form-container\">\n";

		// group name
		echo "\t<div class=\"form-label required\">{$lang['strgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><select name=\"group\">";
		foreach($knownGroups as $r)
			echo "\t\t<option>", htmlspecialchars($r['group_name']), "\n";
		echo "\t</select></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";

		// mark
		echo "\t<div class=\"form-label\">{$lang['strmarkiflogginggroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" name=\"mark\" size=\"22\" value=\"ASSIGN_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";

		echo"</div>\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"assignsequence\" value=\"{$lang['strassign']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform sequences assignment into a tables group
	 */
	function assign_sequences_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbsequences'] == 1)
			$errMsgAction = sprintf($lang['strassignsequenceerr2'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['sequences']), $_POST['group']);
		else
			$errMsgAction = sprintf($lang['strassignsequenceserr2'], $_POST['nbsequences'], htmlspecialchars($_POST['schema']), $_POST['group']);

		// Check that the schema and the sequences still exist
		checkRelations($_REQUEST['schema'], $_POST['sequences'], 'sequence', $errMsgAction);

		// Check that the tables group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction);

		// OK, process the sequences assignment
		$nbSequences = $emajdb->assignSequences($_POST['schema'], $_POST['sequences'], $_POST['group'], $finalMark);

		// Check the result and exit
		if ($nbSequences >= 0)
			if ($nbSequences > 1)
				doDefault(sprintf($lang['strassignsequencesok'], $nbSequences, htmlspecialchars($_POST['group'])));
			else
				doDefault(sprintf($lang['strassignsequenceok'], $nbSequences, htmlspecialchars($_POST['group'])));
		else
			doDefault('', $errMsgAction);
	}

	/**
	 * Prepare move sequences to another group: ask for confirmation
	 */
	function move_sequences() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbSeq, $sequencesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'sequence', true);
		} else {
			$nbSeq = 1;
			$sequencesList = $_REQUEST['sequence'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbSeq == 1)
			$errMsgAction = sprintf($lang['strmovesequenceerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($sequencesList));
		else
			$errMsgAction = sprintf($lang['strmovesequenceserr'], $nbSeq, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strmovesequence']);

		// Get created group names
		$knownGroups = $emajdb->getCreatedGroups();

		// Build the form
		if ($nbSeq > 1) {
			echo "<p>" . sprintf($lang['strconfirmmovesequences'], $nbSeq, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmmovesequence'], $_REQUEST['schema'], $sequencesList, $groupsList) . "</p>\n";
		}

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"move_sequences_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbsequences\" value=\"{$nbSeq}\" />\n";
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
		echo "\t<input type=\"submit\" name=\"movesequence\" value=\"{$lang['strmove']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform sequences move into another tables group
	 */
	function move_sequences_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbsequences'] == 1)
			$errMsgAction = sprintf($lang['strmovesequenceerr2'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['sequences']), htmlspecialchars($_POST['oldgroups']), htmlspecialchars($_POST['newgroup']));
		else
			$errMsgAction = sprintf($lang['strmovesequenceserr2'], $_POST['nbsequences'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($_POST['newgroup']));

		$allGroups = $_POST['oldgroups'] . ', ' . $_POST['newgroup'];

		// Check that the tables group still exists
		recheckGroups($allGroups, $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($allGroups, $_POST['mark'], $errMsgAction);

		// OK, process the sequences move
		$nbSequences = $emajdb->moveSequences($_POST['schema'], $_POST['sequences'], $_POST['newgroup'], $finalMark);

		// Check the result and exit
		if ($nbSequences>= 0)
			if ($nbSequences > 1)
				doDefault(sprintf($lang['strmovesequencesok'], $nbSequences, htmlspecialchars($_POST['newgroup'])));
			else
				doDefault(sprintf($lang['strmovesequenceok'], $nbSequences, htmlspecialchars($_POST['newgroup'])));
		else
			doDefault('', $errMsgAction);
	}

	/**
	 * Prepare remove sequences: ask for confirmation
	 */
	function remove_sequences() {
		global $misc, $lang, $emajdb;

		// Process the multi-actions array
		if (isset($_REQUEST['ma'])) {
			list($nbSeq, $sequencesList, $fullList, $groupsList) = processMultiActions($_REQUEST['ma'], 'sequence', true);
		} else {
			$nbSeq = 1;
			$sequencesList = $_REQUEST['sequence'];
			$groupsList = $_REQUEST['group'];
		}

		// Prepare the action part of potential error messages
		if ($nbSeq == 1)
			$errMsgAction = sprintf($lang['strremovesequenceerr'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($sequencesList), htmlspecialchars($groupsList));
		else
			$errMsgAction = sprintf($lang['strremovesequenceserr'], $nbSeq, htmlspecialchars($_REQUEST['schema']));

		// Check that the tables group still exists
		recheckGroups($groupsList, $errMsgAction);

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printTitle($lang['strremovesequence']);

		// Build the form
		if ($nbSeq > 1) {
			echo "<p>" . sprintf($lang['strconfirmremovesequences'], $nbSeq, $_REQUEST['schema']) . "</p>\n{$fullList}";
		} else {
			echo "<p>" . sprintf($lang['strconfirmremovesequence'], htmlspecialchars($_REQUEST['schema']), htmlspecialchars($sequencesList), htmlspecialchars($groupsList)) . "</p>\n";
		}
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"remove_sequences_ok\" />\n";
		echo "<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "<input type=\"hidden\" name=\"sequences\" value=\"", htmlspecialchars($sequencesList), "\" />\n";
		echo "<input type=\"hidden\" name=\"nbsequences\" value=\"{$nbSeq}\" />\n";
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
		echo "\t<input type=\"submit\" name=\"removesequence\" value=\"{$lang['strremove']}\" id=\"ok\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform sequences remove from their tables group
	 */
	function remove_sequences_ok() {
		global $lang, $data, $emajdb;

		// Process the click on the <cancel> button.
		processCancelButton();

		// Prepare the action part of potential error messages
		if ($_POST['nbsequences'] == 1)
			$errMsgAction = sprintf($lang['strremovesequenceerr'], htmlspecialchars($_POST['schema']), htmlspecialchars($_POST['sequences']), htmlspecialchars($_POST['groups']));
		else
			$errMsgAction = sprintf($lang['strremovesequenceserr'], $_POST['nbsequences'], htmlspecialchars($_POST['schema']));

		// Check that the tables group still exists
		recheckGroups($_POST['groups'], $errMsgAction);

		// Check the mark name
		$finalMark = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// OK, process the sequences removal
		$nbSequences = $emajdb->removeSequences($_POST['schema'], $_POST['sequences'], $finalMark);

		// Check the result and exit
		if ($nbSequences>= 0)
			if ($nbSequences > 1)
				doDefault(sprintf($lang['strremovesequencesok'], $nbSequences));
			else
				doDefault(sprintf($lang['strremovesequenceok'], $nbSequences));
		else
			doDefault($errMsgAction);
	}

?>
