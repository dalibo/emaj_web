<?php

	/*
	 * Actions on tables groups.
	 * This file is included into groupproperties.php and emajgroups.php
	 */

/********************************************************************************************************
 * Functions preparing or performing actions
 *******************************************************************************************************/

	/**
	 * Prepare create group: ask for confirmation
	 */
	function create_group() {
		global $misc, $lang, $emajdb;

		printHeader();
		$misc->printTitle($lang['strcreateagroup']);

		if (!isset($_REQUEST['group'])) $_REQUEST['group'] = '';

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create_group_ok\" />\n";
		echo $misc->form;

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" id=\"group\" name=\"group\" size=\"32\" required pattern=\"\S+.*\" value=\"", htmlspecialchars($_REQUEST['group']) , "\" placeholder='{$lang['strrequiredfield']}' autocomplete=\"off\"></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";

		echo "<p>{$lang['strgrouptype']} : \n";
		if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'AUDIT_ONLY') {
			$auditOnlyChecked = 'checked'; $rollbackableChecked = '';
		} else {
			$rollbackableChecked = 'checked'; $auditOnlyChecked = '';
		}
		echo "\t<input type=\"radio\" name=\"grouptype\" value=\"rollbackable\" {$rollbackableChecked}>{$lang['strrollbackable']}\n";
		echo "\t<input type=\"radio\" name=\"grouptype\" value=\"auditonly\" {$auditOnlyChecked}>{$lang['strauditonly']}\n";
		echo "</p>\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";

		echo "<div class=\"actionslist\">\n";
		echo "\t<input type=\"submit\" name=\"creategroup\" value=\"{$lang['strcreate']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/>\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform create_group
	 */
	function create_group_ok() {
		global $lang, $emajdb, $_reload_browser;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strcreategrouperr'], htmlspecialchars($_POST['group']));

		// If the group is supposed to be empty, check the supplied group name doesn't exist
		// Call existsGroup() instead of isNewEmptyGroupValid() when emaj version < 4.0 will not be supported anymore
		if (!$emajdb->isNewEmptyGroupValid($_POST['group'])) {
			doDefault('', $errMsgAction . '<br>' . sprintf($lang['strgroupalreadyexists'], htmlspecialchars($_POST['group'])));
			return;
		}

		// OK, perform the action
		$status = $emajdb->createGroup($_POST['group'], $_POST['grouptype']=='rollbackable', true, $_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strcreategroupok'], htmlspecialchars($_POST['group'])));
		$_reload_browser = true;
	}

	/**
	 * Prepare create group for groups configured in emaj_group_def: ask for confirmation
	 */
	function create_configured_group() {
		global $misc, $lang, $emajdb;

		printHeader();
		$misc->printTitle($lang['strcreateagroup']);

		$rollbackable = true; $auditonly = true;

		// check the group configuration
		$checks = $emajdb->checkConfNewGroup($_REQUEST['group']);
		if ($checks->recordCount() == 0) {
			echo "<p>" . sprintf($lang['strgroupconfok'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";
		} else {
			echo "<p>" . sprintf($lang['strgroupconfwithdiag'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";

			$columns = array(
				'message' => array(
					'title' => $lang['strdiagnostics'],
					'field' => field('chk_message'),
				),
			);

			$actions = array ();

			$misc->printTable($checks, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

			// determine whether the tables group can be audit_only
			$rollbackable = false;
			$checks->moveFirst();
			while (!$checks->EOF) {
				if ($checks->fields['chk_severity'] == 1) $auditonly = false;
				$checks->moveNext();
			}
		}

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create_configured_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		if ($auditonly) {
			echo "<p>{$lang['strgrouptype']} : \n";
			if ($rollbackable) {$attr = "checked";} else {$attr = "disabled";}
			echo "\t<input type=\"radio\" name=\"grouptype\" value=\"rollbackable\" {$attr}>{$lang['strrollbackable']}\n";
			if ($rollbackable) {$attr = "";} else {$attr = "checked";}
			echo "\t<input type=\"radio\" name=\"grouptype\" value=\"auditonly\" {$attr}>{$lang['strauditonly']}\n";
		}
		echo "</p><p>";
		if ($auditonly)
			echo "<input type=\"submit\" name=\"creategroup\" value=\"{$lang['strcreate']}\" />\n";

		echo $misc->form;

		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform create_group for a configured group
	 */
	function create_configured_group_ok() {
		global $lang, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			doDefault();
			return;
		}

	// OK
		$status = $emajdb->createGroup($_POST['group'],$_POST['grouptype']=='rollbackable',false,'');

		if ($status == 0) {
			doDefault(sprintf($lang['strcreategroupok'], htmlspecialchars($_POST['group'])));
			$_reload_browser = true;
		} else {
			doDefault('', sprintf($lang['strcreategrouperr'], htmlspecialchars($_POST['group'])));
		}
	}

	/**
	 * Prepare drop group: ask for confirmation
	 */
	function drop_group() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdropgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'IDLE');

		printHeader();
		$misc->printTitle($lang['strdropagroup']);

		echo "<p>", sprintf($lang['strconfirmdropgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"drop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"dropgroup\" value=\"{$lang['strdrop']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform drop group
	 */
	function drop_group_ok() {
		global $lang, $emajdb, $_reload_browser;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdropgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'IDLE');

		// OK, perform the action
		$status = $emajdb->dropGroup($_POST['group']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		groupDropped(sprintf($lang['strdropgroupok'], htmlspecialchars($_POST['group'])));
		$_reload_browser = true;
	}

	/**
	 * Prepare drop groups: ask for confirmation
	 */
	function drop_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		checkGroupsSelected();

		// build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdropgroupserr'], htmlspecialchars($groupsList));

		// Check that all groups exist and are in IDLE state
		recheckGroups($groupsList, $errMsgAction, 'IDLE');

		// Ok, build the form
		printHeader();
		$misc->printTitle($lang['strdropgroups']);

		echo "<p>", sprintf($lang['strconfirmdropgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"drop_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"dropgroups\" value=\"{$lang['strdrop']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform drop groups
	 */
	function drop_groups_ok() {
		global $lang, $emajdb, $_reload_browser;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdropgroupserr'], htmlspecialchars($_POST['groups']));

		// Check that all groups exist and are in IDLE state
		recheckGroups($_POST['groups'], $errMsgAction, 'IDLE');

		// OK, perform the action
		$status = $emajdb->dropGroups($_POST['groups']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strdropgroupsok'], htmlspecialchars($_POST['groups'])));
		$_reload_browser = true;
	}

	/**
	 * Prepare forget groups: ask for confirmation
	 */
	function forget_group() {
		global $misc, $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strforgetgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group does not exist
		if ($emajdb->existsGroup($_REQUEST['group'])) {
			doDefault('', sprintf($lang['strgroupstillexists'], htmlspecialchars($_REQUEST['group'])));
			$_reload_browser = true;
			return;
		}

		printHeader();
		$misc->printTitle($lang['strforgetagroup']);

		echo "<p>", sprintf($lang['strconfirmforgetgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"forget_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"forgetgroup\" value=\"{$lang['strforget']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform forget group
	 */
	function forget_group_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strforgetgrouperr'], htmlspecialchars($_POST['group']));

		// Check that the group does not exist
		if ($emajdb->existsGroup($_POST['group'])) {
			doDefault('', sprintf($lang['strgroupstillexists'], htmlspecialchars($_POST['group'])));
			$_reload_browser = true;
			return;
		}

		// OK, perform the action
		$status = $emajdb->forgetGroup($_POST['group']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strforgetgroupok'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare alter group: ask for confirmation
	 */
	function alter_group() {
		global $misc, $lang, $emajdb;

		printHeader();
		$misc->printTitle($lang['straltergroups']);

		// check the group configuration
		$confOK = true;
		$checks = $emajdb->checkConfExistingGroups($_REQUEST['group']);
		if ($checks->recordCount() == 0) {
			echo "<p>" . sprintf($lang['strgroupconfok'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";
		} else {
			$confOK = false;
			echo "<p>" . sprintf($lang['strgroupconfwithdiag'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";

			$columns = array(
				'message' => array(
					'title' => $lang['strdiagnostics'],
					'field' => field('chk_message'),
				),
			);

			$actions = array ();

			$misc->printTable($checks, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

			echo "<form method=\"post\">\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_group_ok\" />\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
				echo $misc->form;
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		}

		if ($confOK) {
			$isGroupLogging = $emajdb->isGroupLogging($_REQUEST['group']);

			echo "<form method=\"post\">\n";

			if ($isGroupLogging) {
				echo "<p>", sprintf($lang['stralteraloggingroup'], htmlspecialchars($_REQUEST['group'])), "</p>";
				echo "<div class=\"form-container\">\n";
				echo "\t<div class=\"form-label required\">{$lang['strmark']}</div>\n";
				echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"ALTER_%\" id=\"mark\"></div>\n";
				echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
				echo "</div>\n";
			} else {
				echo "<p>", sprintf($lang['strconfirmaltergroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
			}

			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_group_ok\" />\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
				echo $misc->form;
			echo "<input type=\"submit\" name=\"altergroup\" value=\"{$lang['strApplyConfChanges']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		}
	}

	/**
	 * Perform alter group
	 */
	function alter_group_ok() {
		global $lang, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		processCancelButton();

		// check the group can be altered by looking at its state and operations that will be performed
		$check = $emajdb->checkAlterGroup($_REQUEST['group']);
		if ($check == 0) {
			doDefault('', sprintf($lang['strcantaltergroup'], htmlspecialchars($_POST['group'])));
			exit();
		}

		// Check the supplied mark is valid
		if ($_POST['mark'] != '') {
			$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'], htmlspecialchars($_POST['mark']));
			if (is_null($finalMarkName)) {
				doDefault('', sprintf($lang['strinvalidmark'], htmlspecialchars($_POST['mark'])));
				return;
			}
		} else {
			$finalMarkName = '';
		}

		// OK
		$status = $emajdb->alterGroup($_POST['group'],$finalMarkName);
		if ($status == 0) {
			$_reload_browser = true;
			doDefault(sprintf($lang['straltergroupok'], htmlspecialchars($_POST['group'])));
		} else
			doDefault('',sprintf($lang['straltergrouperr'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare alter groups: ask for confirmation
	 */
	function alter_groups() {
		global $misc, $lang, $emajdb;

		// if no group has been selected, stop
		checkGroupsSelected();

		printHeader();
		$misc->printTitle($lang['straltergroups']);

		// build the groups list and the global state of this list 
		$groupsList = ''; $anyGroupLogging = 0;
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
			if ($emajdb->isGroupLogging($a['group'])) {
				$anyGroupLogging = 1;
			}
		}
		$groupsList = substr($groupsList,0,strlen($groupsList)-2);

		// check the groups configuration
		$confOK = true;
		$checks = $emajdb->checkConfExistingGroups($groupsList);
		if ($checks->recordCount() == 0) {
			echo "<p>" . sprintf($lang['strgroupsconfok'], htmlspecialchars($groupsList)) . "</p>\n";
		} else {
			$confOK = false;
			echo "<p>" . sprintf($lang['strgroupsconfwithdiag'], htmlspecialchars($groupsList)) . "</p>\n";

			$columns = array(
				'message' => array(
					'title' => $lang['strdiagnostics'],
					'field' => field('chk_message'),
				),
			);

			$actions = array ();

			$misc->printTable($checks, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

			echo "<form method=\"post\">\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_groups_ok\" />\n";
			echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
				echo $misc->form;
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		}

		if ($confOK) {

			echo "<form method=\"post\">\n";

			if ($anyGroupLogging) {
				echo "<p>", sprintf($lang['stralterallloggingroups'], htmlspecialchars($groupsList)), "</p>";
				echo "<div class=\"form-container\">\n";
				echo "\t<div class=\"form-label required\">{$lang['strmark']}</div>\n";
				echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"ALTER_%\" id=\"mark\"></div>\n";
				echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamemultihelp']}\"/></div>\n";
				echo "</div>\n";
			} else {
				echo "<p>", sprintf($lang['strconfirmaltergroups'], htmlspecialchars($groupsList)), "</p>\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
			}

			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_groups_ok\" />\n";
			echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
				echo $misc->form;
			echo "<input type=\"submit\" name=\"altergroups\" value=\"{$lang['strApplyConfChanges']}\" />\n";
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		}
	}

	/**
	 * Perform alter groups
	 */
	function alter_groups_ok() {
		global $lang, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			doDefault();
		} else {

		// check the groups can be altered by looking at their state and operations that will be performed
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				$check = $emajdb->checkAlterGroup($g);
				// exit the loop in case of error
				if ($check == 0) {
					doDefault('', sprintf($lang['strcantaltergroup'], htmlspecialchars($g)));
					exit();
				}
			}
		// Check the supplied mark is valid for the groups
			if ($_POST['mark'] != '') {
				$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark']);
				if (is_null($finalMarkName)) {
					doDefault('', sprintf($lang['strinvalidmark'], htmlspecialchars($_POST['mark'])));
					return;
				}
			} else {
				$finalMarkName = '';
			}

		// OK
			$status = $emajdb->alterGroups($_POST['groups'],$finalMarkName);
			if ($status == 0) {
				$_reload_browser = true;
				doDefault(sprintf($lang['straltergroupsok'], htmlspecialchars($_POST['groups'])));
			} else
				doDefault('',sprintf($lang['straltergroupserr'], htmlspecialchars($_POST['groups'])));
		}
	}

	/**
	 * Prepare comment group: ask for comment and confirmation
	 */
	function comment_group() {
		global $misc, $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strcommentgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists
		recheckGroups($_REQUEST['group'], $errMsgAction);

		printHeader();
		$misc->printTitle($lang['strcommentagroup']);

		$group = $emajdb->getGroup($_REQUEST['group']);

		echo "<p>", sprintf($lang['strcommentgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($group->fields['group_comment']), "\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"comment_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"commentgroup\" value=\"{$lang['strok']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform comment group
	 */
	function comment_group_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strcommentgrouperr'], htmlspecialchars($_POST['group']));

		// Check the group still exists
		recheckGroups($_REQUEST['group'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->setCommentGroup($_POST['group'], $_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strcommentgroupok'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare export group: select the groups to export and confirm
	 */
	function export_groups() {
		global $misc, $lang, $emajdb;

		printHeader();
		$misc->printTitle($lang['strexportgroupsconf']);

		echo "<p>{$lang['strexportgroupsconfselect']}</p>";

		$groups = $emajdb->getGroups();

		$columns = array(
			'group' => array(
				'title' => $lang['strgroup'],
				'field' => field('group_name'),
			),
			'state' => array(
				'title' => $lang['strstate'],
				'field' => field('group_state'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderGroupState','align' => 'center'),
				'filter' => false,
			),
			'rollbackable' => array(
				'title' => $lang['strtype'],
				'field' => field('group_type'),
				'type'	=> 'callback',
				'params'=> array(
						'function' => 'renderGroupType',
						'align' => 'center'
						),
				'sorter_text_extraction' => 'img_alt',
				'filter' => false,
			),
			'nbtbl' => array(
				'title' => $lang['strtables'],
				'field' => field('group_nb_table'),
				'type'  => 'numeric'
			),
			'nbseq' => array(
				'title' => $lang['strsequences'],
				'field' => field('group_nb_sequence'),
				'type'  => 'numeric'
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('group_comment'),
				'type' => 'spanned',
				'params'=> array(
						'cliplen' => 20,
						'class' => 'tooltip right-aligned-tooltip',
						),
			),
		);

		$urlvars = $misc->getRequestVars();

		$actions = array();
		if ($emajdb->isEmaj_Adm()) {
			$actions = array_merge($actions, array(
				'multiactions' => array(
					'keycols' => array('group' => 'group_name'),
					'url' => "emajgroups.php?back=list",
					'checked' => true,								// all groups selected by default
				),
				'export_group' => array(
					'content' => $lang['strexport'],
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'export_group_ok',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'export_groups_ok',
				),
			));
		};

		$misc->printTable($groups, $columns, $actions, 'groups', '', null, array('sorter' => true, 'filter' => true));

		echo "<form method=\"post\">\n";
		echo $misc->form;
		echo "<input type=\"hidden\" name=\"action\" value=\"\" />\n";
		echo "<div class=\"actionslist\">\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strback']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Export a tables groups configuration
	 */
	function export_groups_ok() {
		global $misc, $emajdb, $lang;

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strexportgroupserr'], htmlspecialchars($groupsList));

		// Check that all groups exist
		$missingGroups = $emajdb->missingGroups($groupsList);
		if ($missingGroups->fields['nb_groups'] > 0) {
			$misc->printHtmlHeader($lang['strgroupsmanagement']);
			$misc->printBody();
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

		// OK, build the JSON parameter configuration
		$groupsConfig = $emajdb->exportGroupsConfig($groupsList);

		// Generate a local file name
		$server_info = $misc->getServerInfo();
		$fileName = "emaj_groups_" . $server_info['desc'] . "_" . $_REQUEST['database'] . "_" . date("Ymd_His") . ".json";

		// Send it to the browser
		header('Content-Description: File Transfer');
		header('Content-Type: application/json');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . strlen($groupsConfig));
		print $groupsConfig;
	}

	/**
	 * Import a tables groups configuration
	 */
	function import_groups() {

		global $misc, $lang, $emajdb;

		printHeader();
		$misc->printTitle($lang['strimportgroupsconf']);

		// form to import a tables groups configuration
		echo "<div>\n";
		echo "\t<form name=\"importgroups\" id=\"importgroups\" enctype=\"multipart/form-data\" method=\"post\"";
		echo " action=\"emajgroups.php?action=import_groups_select&amp;{$misc->href}\">\n";
		echo "\t\t<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10000000\">\n";
		echo "\t\t<div class=\"actionslist\">";
		echo "\t\t<label for=\"file-upload\" class=\"custom-file-upload\">{$lang['strselectfile']}</label>";
		echo "\t\t<input type=\"file\" id=\"file-upload\" name=\"file_name\">\n";
		echo "\t\t</div>\n";
		echo "\t\t<div class=\"actionslist\">";
		echo "\t\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />&nbsp;&nbsp;&nbsp;\n";
		echo "\t\t<input type=\"submit\" name=\"openfile\" value=\"{$lang['stropen']}\" disabled>";
		echo "\t\t<span id=\"selected-file\"></span>\n";
		echo "\t\t</div>\n";
		echo "\t\t<script>
			$(document).ready(
				function(){
					$('input:file').change(
						function(){
							if ($(this).val()) { $('input:submit').attr('disabled',false); }
						}
					);
					$('#file-upload').bind('change',
						function(){
							var fileName = '';
							fileName = $(this).val();
							$('#selected-file').html(fileName.replace(/^.*\\\\/, \"\"));
						}
					);
				});
		</script>\n";

		echo "\t</form>\n";
		echo "</div>\n";
	}

	/**
	 * Upload and open the tables group configuration file and let the user select the groups he wants to import
	 */
	function import_groups_select() {

		global $misc, $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Process the uploaded file

		// If the file is properly loaded,
		if (is_uploaded_file($_FILES['file_name']['tmp_name'])) {
			$jsonContent = file_get_contents($_FILES['file_name']['tmp_name']);
			$jsonStructure = json_decode($jsonContent, true);
		// ... and contains a valid JSON structure,
			if (json_last_error()===JSON_ERROR_NONE) {

				printHeader();
				$misc->printTitle($lang['strimportgroupsconf']);

				// check that the json content is valid

				$errors = $emajdb->checkJsonGroupsConf($jsonContent);

				if ($errors->recordCount() == 0) {
					// No error has been detected in the json structure, so display the tables groups to select

					echo "<p>" . sprintf($lang['strimportgroupsinfile'], $_FILES['file_name']['name']) . "</p>\n";

					// Extract the list of configured tables groups
					$groupsArray = array();
					foreach($jsonStructure["tables_groups"] as $jsonGroup){
						if (isSet($jsonGroup["group"])) {
							$groupsArray[] = $jsonGroup["group"];
						}
					}

					// Get data about existing groups
					$groups = $emajdb->getGroupsToImport($groupsArray);

					// Display the groups list
					$columns = array(
						'group' => array(
							'title' => $lang['strgroup'],
							'field' => field('grp_name'),
						),
						'state' => array(
							'title' => $lang['strstate'],
							'field' => field('group_state'),
							'type'	=> 'callback',
							'params'=> array(
								'function' => 'renderGroupState',
								'align' => 'center'
								),
							'filter' => false,
						),
						'rollbackable' => array(
							'title' => $lang['strtype'],
							'field' => field('group_type'),
							'type'	=> 'callback',
							'params'=> array(
								'function' => 'renderGroupType',
								'align' => 'center'
								),
							'sorter_text_extraction' => 'img_alt',
							'filter' => false,
						),
						'nbtbl' => array(
							'title' => $lang['strtables'],
							'field' => field('group_nb_table'),
							'type'  => 'numeric'
						),
						'nbseq' => array(
							'title' => $lang['strsequences'],
							'field' => field('group_nb_sequence'),
							'type'  => 'numeric'
						),
						'comment' => array(
							'title' => $lang['strcomment'],
							'field' => field('group_comment'),
						),
					);

					$urlvars = $misc->getRequestVars();

					$actions = array();
					if ($emajdb->isEmaj_Adm()) {
						$actions = array_merge($actions, array(
							'multiactions' => array(
								'keycols' => array('group' => 'grp_name'),
								'url' => "emajgroups.php",
								'vars' => array (
									'file' => $_FILES['file_name']['name'],
									'json' => json_encode($jsonStructure),
								),
								'checked' => true,							// all groups are selected by default
								'close_form' => false,						// do not close the form in order to add additional inputs
							),
							'import_group' => array(
								'content' => $lang['strimport'],
								'multiaction' => 'import_groups_ok',
							),
						));
					};

					$misc->printTable($groups, $columns, $actions, 'groups', '', null, array('sorter' => true, 'filter' => true));

					// Add an input to specify the mark name to set
					if ($emajdb->getNumEmajVersion() >= 40000){	// version 4.0+
						echo "<div class=\"form-container\" style=\"margin-top: 15px; margin-bottom: 15px;\">\n";
						echo "\t<div class=\"form-label\">{$lang['strmark']}</div>\n";
						echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"IMPORT_%\" /></div>\n";
						echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
						echo "</div>\n";
					} else {
						echo "<input type=\"hidden\" name=\"mark\" value=\"\">\n";
					}
					echo "</form>\n";

					echo "<form method=\"post\">\n";
					echo $misc->form;
					echo "<div class=\"actionslist\">";
					echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
					echo "</div></form>\n";

				} else {
				// The json structure contains errors. Display them.

					echo "<p>" . sprintf($lang['strimportgroupsinfileerr'], $_FILES['file_name']['name']) . "</p>";

					$columns = array(
						'severity' => array(
							'title' => '',
							'field' => field('rpt_severity'),
							'type'	=> 'callback',
							'params'=> array('function' => 'renderMsgSeverity','align' => 'center'),
							'sorter' => false,
						),
						'message' => array(
							'title' => $lang['strdiagnostics'],
							'field' => field('rpt_message'),
						),
					);

					$actions = array ();

					$misc->printTable($errors, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

					echo "<form method=\"post\">\n";
					echo "<input type=\"hidden\" name=\"action\" value=\"import_groups_ok\" />\n";
					echo $misc->form;
					echo "<div class=\"actionslist\">";
					echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strok']}\" />\n";
					echo "</div></form>\n";

				}
			} else {
				doDefault('', sprintf($lang['strnotjsonfile'], $_FILES['file_name']['name']));
			}
		} else {
			switch($_FILES['file_name']['error']){
				case 1: //uploaded file exceeds the upload_max_filesize directive in php.ini
				case 2: //uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
					$errMsg = $lang['strimportfiletoobig'];
					break;
				case 3: //uploaded file was only partially uploaded
				case 4: //no file was uploaded
				case 0: //no error; possible file attack!
				default: //a default error, just in case!  :)
					$errMsg = $lang['strimporterror-uploadedfile'];
					break;
			}
			doDefault('', $errMsg);
		}
	}

	/**
	 * Effectively import a tables groups configuration
	 */
	function import_groups_ok() {

		global $lang, $emajdb, $misc, $_reload_browser;

		// Process the click on the <cancel> button
		processCancelButton();

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// prepare the tables groups configuration import
		$errors = $emajdb->importGroupsConfPrepare($_POST['json'], $groupsList);
		if ($errors->recordCount() == 0) {
			// no error detected, so execute the effective configuration import
			$nbGroup = $emajdb->importGroupsConfig($_POST['json'], $groupsList, $_POST['mark']);
			if ($nbGroup >= 0) {
				$_reload_browser = true;
				doDefault(sprintf($lang['strgroupsconfimported'], $nbGroup, $_POST['file']));
			} else {
				doDefault('', sprintf($lang['strgroupsconfimporterr'], $_POST['file']));
			}
		} else {
			// there are errors to report to the user

			printHeader();
			$misc->printTitle($lang['strimportgroupsconf']);

			echo "<p>" . sprintf($lang['strgroupsconfimportpreperr'], htmlspecialchars($groupsList), htmlspecialchars($_POST['file'])) . "</p>\n";

			$columns = array(
				'severity' => array(
					'title' => '',
					'field' => field('rpt_severity'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderMsgSeverity','align' => 'center'),
					'sorter' => false,
				),
				'message' => array(
					'title' => $lang['strdiagnostics'],
					'field' => field('rpt_message'),
				),
			);

			$actions = array ();

			$misc->printTable($errors, $columns, $actions, 'errors', null, null, array('sorter' => true, 'filter' => false));

			echo "<form method=\"post\">\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"import_group_ok\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strok']}\" /></p>\n";
			echo "</form>\n";
		}
	}

	/**
	 * Prepare start group: enter the initial mark name and confirm
	 */
	function start_group() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstartgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'IDLE');

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		printHeader();
		$misc->printTitle($lang['strstartagroup']);

		echo "<form method=\"post\">\n";
		echo "<p>" . sprintf($lang['strconfirmstartgroup'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strinitmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['strrequiredfield']}' autocomplete=\"off\"/></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$lang['stroldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"start_group_ok\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" value=\"{$lang['strstart']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate />\n";
		echo "</div></form>\n";

		echo "<script>\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#mark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";
	}

	/**
	 * Perform start group
	 */
	function start_group_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstartgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_POST['group'], $errMsgAction, 'IDLE');

		// Check the supplied mark is valid for the group
		$finalMarkName = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction, !isset($_POST['resetlog']));

		// OK, perform the action
		$status = $emajdb->startGroup($_POST['group'], $finalMarkName, isset($_POST['resetlog']));

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strstartgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
	}

	/**
	 * Prepare start groups: enter the initial mark name and confirm
	 */
	function start_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		checkGroupsSelected();

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			start_group();
			return;
		}

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstartgroupserr'], htmlspecialchars($groupsList));

		// Check that all groups exist and are in IDLE state
		recheckGroups($groupsList, $errMsgAction, 'IDLE');

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		printHeader();
		$misc->printTitle($lang['strstartgroups']);

		// Send the form
		echo "<form method=\"post\">\n";
		echo "<p>", sprintf($lang['strconfirmstartgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strinitmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['strrequiredfield']}' autocomplete=\"off\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamemultihelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$lang['stroldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"start_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"list\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" value=\"{$lang['strstart']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate />\n";
		echo "</div></form>\n";

		echo "<script>\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#mark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";
	}

	/**
	 * Perform start groups
	 */
	function start_groups_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstartgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check that all groups exist and are in IDLE state
		recheckGroups($_POST['groups'], $errMsgAction, 'IDLE');

		// Check the supplied mark is valid for the groups
		$finalMarkName = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction, !isset($_POST['resetlog']));

		// OK, perform the action
		$status = $emajdb->startGroups($_POST['groups'], $finalMarkName, isset($_POST['resetlog']));

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strstartgroupsok'], htmlspecialchars($_POST['groups']), htmlspecialchars($finalMarkName)));
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_group() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstopgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING');

		printHeader();
		$misc->printTitle($lang['strstopagroup']);

		echo "<form method=\"post\">\n";
		echo "<p>", sprintf($lang['strconfirmstopgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strstopmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=checkbox name=\"forcestop\" />{$lang['strforcestop']}</p>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"stop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"stopgroup\" value=\"{$lang['strstop']}\"/>\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform stop_group
	 */
	function stop_group_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstopgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_POST['group'], $errMsgAction, 'LOGGING');

		// Check the supplied mark is valid for the group
		$finalMarkName = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction, !isset($_POST['forcestop']));

		// OK, perform the action
		$status = $emajdb->stopGroup($_POST['group'], $finalMarkName, isset($_POST['forcestop']));

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strstopgroupok'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		checkGroupsSelected();

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			stop_group();
			return;
		}

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstopgroupserr'], htmlspecialchars($groupsList));

		// Check that all groups exist and are in LOGGING state
		recheckGroups($groupsList, $errMsgAction, 'LOGGING');

		printHeader();
		$misc->printTitle($lang['strstopgroups']);

		// Send form
		echo "<form method=\"post\">\n";
		echo "<p>", sprintf($lang['strconfirmstopgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strstopmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamemultihelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"stop_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"stopgroups\" value=\"{$lang['strstop']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform stop_groups
	 */
	function stop_groups_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstopgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check that all groups exist and are in LOGGING state
		recheckGroups($_POST['groups'], $errMsgAction, 'LOGGING');

		// Check the supplied mark is valid for the groups
		$finalMarkName = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->stopGroups($_POST['groups'], $finalMarkName);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strstopgroupsok'], htmlspecialchars($_POST['groups'])));
	}

	/**
	 * Prepare reset group: ask for confirmation
	 */
	function reset_group() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strresetgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'IDLE');

		printHeader();
		$misc->printTitle($lang['strresetagroup']);

		echo "<p>", sprintf($lang['strconfirmresetgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"reset_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"resetgroup\" value=\"{$lang['strreset']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform reset group
	 */
	function reset_group_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strresetgrouperr'], htmlspecialchars($_POST['group']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_POST['group'], $errMsgAction, 'IDLE');

		// OK, perform the action
		$status = $emajdb->resetGroup($_POST['group']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strresetgroupok'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare reset groups: ask for confirmation
	 */
	function reset_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		checkGroupsSelected();

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			reset_group();
			return;
		}

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strresetgroupserr'], htmlspecialchars($groupsList));

		// Check that all groups exist and are in IDLE state
		recheckGroups($groupsList, $errMsgAction, 'IDLE');

		printHeader();
		$misc->printTitle($lang['strresetgroups']);

		// Send the form
		echo "<p>", sprintf($lang['strconfirmresetgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"reset_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"resetgroups\" value=\"{$lang['strreset']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform reset groups
	 */
	function reset_groups_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strresetgroupserr'], htmlspecialchars($_POST['groups']));

		// Check that all groups exist and are in IDLE state
		recheckGroups($_POST['groups'], $errMsgAction, 'IDLE');

		// OK, perform the action
		$status = $emajdb->resetGroups($_POST['groups']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strresetgroupsok'], htmlspecialchars($_POST['groups'])));
	}

	/**
	 * Execute protect group (there is no confirmation to ask)
	 */
	function protect_group() {
		global $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strprotectgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING');

		// OK, perform the action
		$status = $emajdb->protectGroup($_REQUEST['group']);
		if ($status == 0)
			doDefault(sprintf($lang['strprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
		else
			doDefault('', $errMsgAction);
	}

	/**
	 * Execute protect groups (there is no confirmation to ask)
	 */
	function protect_groups() {
		global $lang, $emajdb;

		// if no group has been selected, stop
		checkGroupsSelected();

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			protect_group();
			return;
		}

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strprotectgroupserr'], htmlspecialchars($groupsList));

		// Check the groups still exist and are in LOGGING state
		recheckGroups($groupsList, $errMsgAction, 'LOGGING');

		// OK, perform the action
		$nbGroup = $emajdb->protectGroups($groupsList);
		if ($nbGroup >= 0)
			doDefault(sprintf($lang['strprotectgroupsok'], htmlspecialchars($groupsList)));
		else
			doDefault('', $errMsgAction);
	}

	/**
	 * Execute unprotect group (there is no confirmation to ask)
	 */
	function unprotect_group() {
		global $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strunprotectgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING');

		// OK, perform the action
		$status = $emajdb->unprotectGroup($_REQUEST['group']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strunprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
	}

	/**
	 * Execute unprotect groups (there is no confirmation to ask)
	 */
	function unprotect_groups() {
		global $lang, $emajdb;

		// if no group has been selected, stop
		checkGroupsSelected();

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			unprotect_group();
			return;
		}

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strunprotectgroupserr'], htmlspecialchars($groupsList));

		// Check the groups still exist and are in LOGGING state
		recheckGroups($groupsList, $errMsgAction, 'LOGGING');

		// OK, perform the action
		$nbGroup = $emajdb->unprotectGroups($groupsList);
		if ($nbGroup >= 0)
			doDefault(sprintf($lang['strunprotectgroupsok'], htmlspecialchars($groupsList)));
		else
			doDefault('', $errMsgAction);
	}

	/**
	 * Prepare set mark group: ask for the mark name and confirmation
	 */
	function set_mark_group() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strsetmarkgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING');

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		printHeader();
		$misc->printTitle($lang['strsetamark']);

		echo "<p>", sprintf($lang['strconfirmsetmarkgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['strrequiredfield']}' autocomplete=\"off\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo "</div>\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";

		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"set_mark_group_ok\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\"/>\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate />\n";
		echo "</div></form>\n";

		echo "<script>\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#mark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";
	}

	/**
	 * Perform set mark group
	 */
	function set_mark_group_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strsetmarkgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_POST['group'], $errMsgAction, 'LOGGING');

		// Check the supplied mark is valid for the group
		$finalMarkName = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->setMarkGroup($_POST['group'],$finalMarkName,$_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strsetmarkgroupok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare set mark groups: ask for the mark name and confirmation
	 */
	function set_mark_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		checkGroupsSelected();

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			set_mark_group();
			return;
		}

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strsetmarkgroupserr'], htmlspecialchars($groupsList));

		// Check that all groups exist and are in LOGGING state
		recheckGroups($groupsList, $errMsgAction, 'LOGGING');

		printHeader();
		$misc->printTitle($lang['strsetamark']);

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		echo "<p>", sprintf($lang['strconfirmsetmarkgroups'], htmlspecialchars($groupsList)), "</p>\n";
		// send form
		echo "<form method=\"post\">\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['strrequiredfield']}' autocomplete=\"off\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamemultihelp']}\"/></div>\n";
		echo "</div>\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";

		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"set_mark_groups_ok\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate />\n";
		echo "</div></form>\n";

		echo "<script>\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#mark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";
	}

	/**
	 * Perform set mark groups
	 */
	function set_mark_groups_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strsetmarkgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check that all groups exist and are in LOGGING state
		recheckGroups($_POST['groups'], $errMsgAction, 'LOGGING');

		// Check the supplied mark is valid for the groups
		$finalMarkName = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->setMarkGroups($_POST['groups'],$finalMarkName,$_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strsetmarkgroupsok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['groups'])));
	}

	/**
	 * Prepare rollback group: ask for confirmation
	 */
	function rollback_group($estimatedDuration = null) {
		global $misc, $lang, $emajdb, $conf;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrlbkgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING');

		// If the mark is already defined, check the mark still exists for the group
		if (isset($_REQUEST['mark'])) {
			recheckMarksGroup($_REQUEST['group'], $_REQUEST['mark'], $errMsgAction);
		}

		printHeader();
		$misc->printTitle($lang['strrlbkagroup']);

		echo "<form method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_group_confirm_alter\" />\n";

		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		if (!isset($_REQUEST['mark'])) {
		// the mark name is not yet defined (we are coming from the 'list groups' page)
			$marks = $emajdb->getRollbackMarkGroup($_REQUEST['group']);
			echo sprintf($lang['strselectmarkgroup'], htmlspecialchars($_REQUEST['group']));
			echo "<select name=\"mark\">\n";
			$optionDisabled = '';
			foreach($marks as $m) {
				$optionSelected = (isset($_POST['mark']) && $_POST['mark'] == $m['mark_name']) ? ' selected' : '';
				echo "<option value=\"", htmlspecialchars($m['mark_name']), "\"{$optionDisabled}{$optionSelected}>", htmlspecialchars($m['mark_name']), " ({$m['mark_datetime']})</option>\n";
				// if the mark is protected against rollback, disabled the next ones
				if ($m['mark_is_rlbk_protected'] == 't') $optionDisabled = ' disabled';
			}
			echo "</select></p>\n";
		} else {
		// the mark name is already defined (we are coming from the 'detail group' page)
			echo "<p>", sprintf($lang['strconfirmrlbkgroup'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['mark'])), "</p>\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		}
		echo $misc->form;

		// rollback type line
		echo "<p>{$lang['strrollbacktype']} : \n";
		$unloggedChecked = 'checked'; $loggedChecked = '';
		if (isset($_POST['rollbacktype']) && $_POST['rollbacktype'] == 'logged') {
			$unloggedChecked = ''; $loggedChecked = 'checked';
		}
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"unlogged\" {$unloggedChecked}>{$lang['strunlogged']}\n";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"logged\" {$loggedChecked}>{$lang['strlogged']}\n";
		echo "</p>\n";

		// comment line
		if ($emajdb->getNumEmajVersion() >= 40300) {	// version >= 4.3.0) {
			echo "<p>{$lang['strcomment']} : \n";
			$comment = (isset($_REQUEST['comment'])) ? $_REQUEST['comment'] : '';
			echo "\t<input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($comment), "\" />\n";
			echo "</p>\n";
		} else {
			echo "<input type=\"hidden\" name=\"comment\" value=\"\" />\n";
		}

		// estimated duration line
		echo "<p>{$lang['strestimatedduration']}&nbsp;:&nbsp;\n";
		if (isset($estimatedDuration)) {
			// the duration estimate is already known, so display it in a pleasant manner
			if (preg_match('/(\d\d\d\d)\/(\d\d)\/(\d\d) (\d\d):(\d\d):(\d\d)/', $estimatedDuration, $m)) {
				if ($m[1] + $m[2] > 0 || $m[3] > 10) {			// more than 10 days (should it happen one day ?)
					$duration = $lang['strdurationovertendays'];
				} elseif ($m[3] * 24 + $m[4]> 0) {				// more than 1 hour => display hours and minutes
					$duration = sprintf($lang['strdurationhoursminutes'], ($m[3] * 24 + $m[4]), $m[5]);
				} else {										// less than 1 hour => display minutes and seconds
					$duration = sprintf($lang['strdurationminutesseconds'], ($m[5] + 0), $m[6]);
				}
			} else {
				$duration = $estimatedDuration;
			}
			echo $duration . "&nbsp;<input type=\"submit\" name=\"estimaterollbackduration\" value=\"{$lang['strreestimate']}\" /></p>\n";
		} else {
			// the duration estimate is unknown, so propose a button to get it
			echo "{$lang['strunknownestimate']}&nbsp;<input type=\"submit\" name=\"estimaterollbackduration\" value=\"{$lang['strestimate']}\" /></p>\n";
		}

		// main buttons line
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['strrlbk']}\" />\n";
		if ($emajdb->isAsyncRlbkUsable($conf) ) {
			echo "\t<input type=\"submit\" name=\"async\" value=\"{$lang['strrlbkthenmonitor']}\" />\n";
		}
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Ask the user to confirm a rollback targeting a mark set prior alter_group operations
	 */
	function rollback_group_confirm_alter() {
		global $lang, $misc, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages, depending on the real performed action
		if (isset($_POST['estimaterollbackduration']))
			$errMsgAction = sprintf($lang['strestimrlbkgrouperr'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));
		else
			$errMsgAction = sprintf($lang['strrlbkgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_POST['group'], $errMsgAction, 'LOGGING');

		// Check the mark still exists for the group
		recheckMarksGroup($_POST['group'], $_POST['mark'], $errMsgAction);

		if (isset($_POST['estimaterollbackduration'])) {
			// process the click on the <estimate> button (compute the estimaged duration and go back to the previous page
			// check the rollback target mark is always valid
			// if ok, estimate the rollback duration and go back to the rollback_group() function
			if ($emajdb->isMarkActiveGroup($_POST['group'], $_POST['mark']) == 1) {
				$estimatedDuration = $emajdb->estimateRollbackGroups($_POST['group'], $_POST['mark'], $_POST['rollbacktype']);
			} else {
				$estimatedDuration = "-";
			}
			rollback_group($estimatedDuration);
			exit;
		}

		// Process a rollback
		// Check the group is still ROLLBACKABLE (i.e. not protected)
		$group = $emajdb->getGroup($_POST['group']);
		if ($group->fields['group_type'] != 'ROLLBACKABLE') {
			doDefault('', $errMsgAction . '<br>' . sprintf($lang['strgroupprotected'], htmlspecialchars($_POST['group'])));
			return;
		}

		// Check the mark is always valid for a rollback
		if (!$emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
			doDefault('', $errMsgAction . '<br>' . sprintf($lang['strcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['mark'])));
			return;
		}

		$alterGroupSteps = $emajdb->getAlterAfterMarkGroups($_POST['group'], $_POST['mark'], $lang);

		if ($alterGroupSteps->recordCount() > 0) {
			// there are alter_group operation to cross over, so ask for a confirmation

			$columns = array(
				'time' => array(
					'title' => $lang['strtimestamp'],
					'field' => field('time_tx_timestamp'),
				),
				'step' => array(
					'title' => $lang['straction'],
					'field' => field('altr_action'),
				),
				'autorollback' => array(
					'title' => $lang['strautorolledback'],
					'field' => field('altr_auto_rolled_back'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderBooleanIcon','align' => 'center')
				),
			);

			$actions = array ();

			printHeader();
			$misc->printTitle($lang['strrlbkagroup']);

			echo "<p>" . sprintf($lang['strreachaltergroup'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['mark'])) . "</p>\n";

			$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');

			echo "<form method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"rollback_group_ok\" />\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
				echo "<input type=\"hidden\" name=\"rollbacktype\"", htmlspecialchars($_REQUEST['rollbacktype']), "\" />\n";
			if (isset($_POST['async'])) {
				echo "<input type=\"hidden\" name=\"async\"", htmlspecialchars($_REQUEST['async']), "\" />\n";
			}
			echo "<input type=\"hidden\" name=\"comment\" value=\"", htmlspecialchars($_REQUEST['comment']), "\" />\n";
			echo $misc->form;
			echo "<div class=\"actionslist\">";
			echo "\t<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['strconfirm']}\" />\n";
			echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</div></form>\n";

		} else {
			// otherwise, directly execute the rollback
			rollback_group_ok();
		}
	}

	/**
	 * Perform rollback_group (in synchronous mode)
	 */
	function rollback_group_ok() {
		global $lang, $misc, $emajdb, $conf;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrlbkgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_POST['group'], $errMsgAction, 'LOGGING');

		// Check the mark still exists for the group
		recheckMarksGroup($_POST['group'], $_POST['mark'], $errMsgAction);

		// Check the group is still ROLLBACKABLE (i.e. not protected)
		$group = $emajdb->getGroup($_POST['group']);
		if ($group->fields['group_type'] != 'ROLLBACKABLE') {
			doDefault('', $errMsgAction . '<br>' . sprintf($lang['strgroupprotected'], htmlspecialchars($_POST['group'])));
			return;
		}

		// Check the mark is always valid for a rollback
		if (!$emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
			doDefault('', $errMsgAction . '<br>' . sprintf($lang['strcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['mark'])));
			return;
		}

		if (isset($_POST['async'])) {
		// perform the rollback in asynchronous mode, if possible, and switch to the rollback monitoring page

			if (!$emajdb->isAsyncRlbkUsable(false)) {
				doDefault('', sprintf($lang['strbadconfparam'], $conf['psql_path'], $conf['temp_dir']));
				exit;
			}

			// perform the rollback in asynchronous mode and switch to the rollback monitoring page
			$psqlExe = $misc->escapeShellCmd($conf['psql_path']);
			$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
			$rlbkId = $emajdb->asyncRollbackGroups($_POST['group'], $_POST['mark'], $_POST['rollbacktype']=='logged', $psqlExe,$conf['temp_dir'].$sep, false, $_POST['comment']);

			// automatic form to go to the emajrollbacks.php page
			echo "<form id=\"auto\" action=\"emajrollbacks.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"show_rollback\" />\n";
			echo "<input type=\"hidden\" name=\"asyncRlbk\" value=\"true\" />\n";
			echo "<input type=\"hidden\" name=\"rlbkid\" value=\"", htmlspecialchars($rlbkId), "\" />\n";
			echo $misc->form;
			echo "</form>\n";
			echo "<script>document.forms[\"auto\"].submit();</script>";

			exit;
		}

		// perform the rollback in regular synchronous mode

		printHeader();
		$misc->printTitle($lang['strrlbkagroup']);

		echo "<p>" . sprintf($lang['strrlbkgroupreport'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])) . "</p>\n";

		// execute the rollback operation and get the execution report
		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large rollbacks (non-safe mode only)
		$rlbkReportMsgs = $emajdb->rollbackGroup($_POST['group'], $_POST['mark'], $_POST['rollbacktype']=='logged', $_POST['comment']);

		$columns = array(
			'severity' => array(
				'title' => '',
				'field' => field('rlbk_severity'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderRlbkExecSeverity','align' => 'center'),
				'sorter' => false,
			),
			'msg' => array(
				'title' => $lang['strmessage'],
				'field' => field('rlbk_message'),
			),
		);

		$actions = array ();

		$misc->printTable($rlbkReportMsgs, $columns, $actions, 'rlbkGroupReport', null, null, array('sorter' => true, 'filter' => false));

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['strok']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Prepare rollback groups: ask for confirmation
	 */
	function rollback_groups($estimatedDuration = null) {
		global $misc, $lang, $emajdb;

		if ($estimatedDuration === null) {
			// usual function entry
			// if no group has been selected, stop
			checkGroupsSelected();

			// if only one group is selected, switch to the mono-group function
			if (count($_REQUEST['ma']) == 1) {
				$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
				$_REQUEST['group'] = $a['group'];
				rollback_group();
				return;
			}

			// Build the groups list
			$groupsList = groupsArray2list($_REQUEST['ma']);

		} else {
			// the function is called back with the estimated duration, so the groups list is already built
			$groupsList = $_POST['groups'];
		}

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrlbkgroupserr'], htmlspecialchars($groupsList));

		// Check the groups still exist and are in LOGGING state
		recheckGroups($groupsList, $errMsgAction, 'LOGGING');

		// if at least one selected group is protected, stop
		$protectedGroups = $emajdb->getProtectedGroups($groupsList);
		if ($protectedGroups->fields['nb_groups'] > 0) {
			if ($protectedGroups->fields['nb_groups'] == 1)
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupprotected'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			else
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsprotected'], $protectedGroups->fields['nb_groups'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			return;
		}

		// look for marks common to all selected groups
		$marks=$emajdb->getRollbackMarkGroups($groupsList);
		// if no mark is usable for all selected groups, stop
		if ($marks->recordCount()==0) {
			doDefault('',sprintf($lang['strnomarkgroups'], htmlspecialchars($groupsList)));
			return;
		}
		// get the youngest timestamp protected mark for all groups
		$youngestProtectedMarkTimestamp=$emajdb->getYoungestProtectedMarkTimestamp($groupsList);

		printHeader();
		$misc->printTitle($lang['strrlbkgroups']);

		echo "<form method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_groups_confirm_alter\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo sprintf($lang['strselectmarkgroups'], htmlspecialchars($groupsList));
		echo "<select name=\"mark\">\n";
		$optionDisabled = '';
		foreach($marks as $m) {
			// if the mark is older than the youngest protected against rollback, disabled it and the next ones
			if ($m['mark_datetime'] < $youngestProtectedMarkTimestamp) $optionDisabled = ' disabled';
			$optionSelected = (isset($_POST['mark']) && $_POST['mark'] == $m['mark_name']) ? ' selected' : '';
			echo "<option value=\"",htmlspecialchars($m['mark_name']),"\"{$optionDisabled}{$optionSelected}>",htmlspecialchars($m['mark_name'])," (",htmlspecialchars($m['mark_datetime']),")</option>\n";
		}
		echo "</select></p><p>\n";
		echo $misc->form;

		// rollback type line
		echo "<p>{$lang['strrollbacktype']} : \n";
		$unloggedChecked = 'checked'; $loggedChecked = '';
		if (isset($_POST['rollbacktype']) && $_POST['rollbacktype'] == 'logged') {
			$unloggedChecked = ''; $loggedChecked = 'checked';
		}
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"unlogged\" {$unloggedChecked}>{$lang['strunlogged']}\n";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"logged\" {$loggedChecked}>{$lang['strlogged']}\n";
		echo "</p>\n";

		// comment line
		if ($emajdb->getNumEmajVersion() >= 40300) {	// version >= 4.3.0) {
			echo "<p>{$lang['strcomment']} : \n";
			$comment = (isset($_REQUEST['comment'])) ? $_REQUEST['comment'] : '';
			echo "\t<input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($comment), "\" />\n";
			echo "</p>\n";
		} else {
			echo "<input type=\"hidden\" name=\"comment\" value=\"\" />\n";
		}

		// estimated duration line
		echo "<p>{$lang['strestimatedduration']}&nbsp;:&nbsp;\n";
		if (isset($estimatedDuration)) {
			// the duration estimate is already known, so display it in a pleasant manner
			if (preg_match('/(\d\d\d\d)\/(\d\d)\/(\d\d) (\d\d):(\d\d):(\d\d)/', $estimatedDuration, $m)) {
				if ($m[1] + $m[2] > 0 || $m[3] > 10) {			// more than 10 days (should it happen one day ?)
					$duration = $lang['strdurationovertendays'];
				} elseif ($m[3] * 24 + $m[4]> 0) {				// more than 1 hour => display hours and minutes
					$duration = sprintf($lang['strdurationhoursminutes'], ($m[3] * 24 + $m[4]), $m[5]);
				} else {										// less than 1 hour => display minutes and seconds
					$duration = sprintf($lang['strdurationminutesseconds'], ($m[5] + 0), $m[6]);
				}
			} else {
				$duration = $estimatedDuration;
			}
			echo $duration . "&nbsp;<input type=\"submit\" name=\"estimaterollbackduration\" value=\"{$lang['strreestimate']}\" /></p>\n";
		} else {
			// the duration estimate is unknown, so propose a button to get it
			echo "{$lang['strunknownestimate']}&nbsp;<input type=\"submit\" name=\"estimaterollbackduration\" value=\"{$lang['strestimate']}\" /></p>\n";
		}

		// main buttons line
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"rollbackgroups\" value=\"{$lang['strrlbk']}\" />\n";
		if ($emajdb->isAsyncRlbkUsable() ) {
			echo "\t<input type=\"submit\" name=\"async\" value=\"{$lang['strrlbkthenmonitor']}\" />\n";
		}
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Ask the user to confirm a multi groups rollback targeting a mark set prior alter_group operations
	 */
	function rollback_groups_confirm_alter() {
		global $lang, $misc, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages, depending on the real performed action
		if (isset($_POST['estimaterollbackduration']))
			$errMsgAction = sprintf($lang['strestimrlbkgroupserr'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));
		else
			$errMsgAction = sprintf($lang['strrlbkgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check the groups still exist and are in LOGGING state
		recheckGroups($_POST['groups'], $errMsgAction, 'LOGGING');

		// Check the mark still exists for the groups
		recheckMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		if (isset($_POST['estimaterollbackduration'])) {
			// process the click on the <estimate> button (compute the estimaged duration and go back to the previous page)
			// check the rollback target mark is always valid
			if ($emajdb->isRollbackMarkValidGroups($_POST['groups'], $_POST['mark'])) {
				// if ok, estimate the rollback duration and go back to the rollback_group() function
				$estimatedDuration = $emajdb->estimateRollbackGroups($_POST['groups'], $_POST['mark'], $_POST['rollbacktype']);
			} else {
				$estimatedDuration = "-";
			}
			rollback_groups($estimatedDuration);
			exit;
		}

		// process a rollback

		// if at least one selected group is protected, stop
		$protectedGroups = $emajdb->getProtectedGroups($_POST['groups']);
		if ($protectedGroups->fields['nb_groups'] > 0) {
			if ($protectedGroups->fields['nb_groups'] == 1)
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupprotected'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			else
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsprotected'], $protectedGroups->fields['nb_groups'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			return;
		}

		// Check the mark is always valid
		if (!$emajdb->isRollbackMarkValidGroups($_POST['groups'],$_POST['mark'])) {
			doDefault('', sprintf($lang['strcantrlbkinvalidmarkgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
			return;
		}

		// check that the rollback would not reach a mark set before any alter group operation
		$alterGroupSteps = $emajdb->getAlterAfterMarkGroups($_POST['groups'],$_POST['mark'],$lang);

		if ($alterGroupSteps->recordCount() > 0) {
			// there are alter_group operations to cross over, so ask for a confirmation

			$columns = array(
				'time' => array(
					'title' => $lang['strtimestamp'],
					'field' => field('time_tx_timestamp'),
				),
				'step' => array(
					'title' => $lang['straction'],
					'field' => field('altr_action'),
				),
				'autorollback' => array(
					'title' => $lang['strautorolledback'],
					'field' => field('altr_auto_rolled_back'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderBooleanIcon','align' => 'center')
				),
			);

			$actions = array ();

			printHeader();
			$misc->printTitle($lang['strrlbkgroups']);

			echo "<p>" . sprintf($lang['strreachaltergroups'], htmlspecialchars($_REQUEST['groups']), htmlspecialchars($_REQUEST['mark'])) . "</p>\n";
			$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');

			echo "<form method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"rollback_groups_ok\" />\n";
			echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($_REQUEST['groups']), "\" />\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
				echo "<input type=\"hidden\" name=\"rollbacktype\" value=\"", htmlspecialchars($_REQUEST['rollbacktype']), "\" />\n";
			if (isset($_POST['async'])) {
				echo "<input type=\"hidden\" name=\"async\"", htmlspecialchars($_REQUEST['async']), "\" />\n";
			}
			echo "<input type=\"hidden\" name=\"comment\" value=\"", htmlspecialchars($_REQUEST['comment']), "\" />\n";
			echo $misc->form;
			echo "<div class=\"actionslist\">";
			echo "\t<input type=\"submit\" name=\"rollbackgroups\" value=\"{$lang['strconfirm']}\" />\n";
			echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
			echo "</div></form>\n";

		} else {
			// otherwise, directly execute the rollback
			rollback_groups_ok();
		}
	}

	/**
	 * Perform rollback_groups
	 */
	function rollback_groups_ok() {
		global $lang, $misc, $emajdb, $conf;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrlbkgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check the groups still exist and are in LOGGING state
		recheckGroups($_POST['groups'], $errMsgAction, 'LOGGING');

		// Check the mark still exists for the groups
		recheckMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// if at least one selected group is protected, stop
		$protectedGroups=$emajdb->getProtectedGroups($_POST['groups']);
		if ($protectedGroups->fields['nb_groups'] > 0) {
			if ($protectedGroups->fields['nb_groups'] == 1)
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupprotected'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			else
				doDefault('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsprotected'], $protectedGroups->fields['nb_groups'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			return;
		}

		// Check the mark is always valid
		if (!$emajdb->isRollbackMarkValidGroups($_POST['groups'], $_POST['mark'])) {
			doDefault('', sprintf($lang['strcantrlbkinvalidmarkgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
			return;
		}
		// OK

		if (isset($_POST['async'])) {

			// perform the rollback in asynchronous mode and switch to the rollback monitoring page
			$psqlExe = $misc->escapeShellCmd($conf['psql_path']);
			$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
			$rlbkId = $emajdb->asyncRollbackGroups($_POST['groups'], $_POST['mark'], $_POST['rollbacktype']=='logged', $psqlExe, $conf['temp_dir'].$sep, true, $_POST['comment']);

			// automatic form to go to the emajrollbacks.php page
			echo "<form id=\"auto\" action=\"emajrollbacks.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"show_rollback\" />\n";
			echo "<input type=\"hidden\" name=\"asyncRlbk\" value=\"true\" />\n";
			echo "<input type=\"hidden\" name=\"rlbkid\" value=\"", htmlspecialchars($rlbkId), "\" />\n";
			echo $misc->form;
			echo "</form>\n";
			echo "<script>document.forms[\"auto\"].submit();</script>";
			exit;
		}

		// perform the rollback in regular synchronous mode

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large rollbacks (non-safe mode only)

		printHeader();
		$misc->printTitle($lang['strrlbkgroups']);

		echo "<p>" . sprintf($lang['strrlbkgroupsreport'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])) . "</p>\n";

		// execute the rollback operation and get the execution report
		$rlbkReportMsgs = $emajdb->rollbackGroups($_POST['groups'], $_POST['mark'], $_POST['rollbacktype']=='logged', $_POST['comment']);
		$columns = array(
			'severity' => array(
				'title' => '',
				'field' => field('rlbk_severity'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderRlbkExecSeverity','align' => 'center'),
				'sorter' => false,
			),
			'msg' => array(
				'title' => $lang['strmessage'],
				'field' => field('rlbk_message'),
			),
		);

		$actions = array ();

		$misc->printTable($rlbkReportMsgs, $columns, $actions, 'rlbkGroupsReport', null, null, array('sorter' => true, 'filter' => false));

		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"show_groups\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['strok']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Execute protect mark (there is no confirmation to ask)
	 */
	function protect_mark_group() {
		global $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strprotectmarkerr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING');

		// Check the mark still exists for the group
		recheckMarksGroup($_REQUEST['group'], $_REQUEST['mark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->protectMarkGroup($_REQUEST['group'], $_REQUEST['mark']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strprotectmarkok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
	}

	/**
	 * Execute unprotect mark (there is no confirmation to ask)
	 */
	function unprotect_mark_group() {
		global $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strunprotectmarkerr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING');

		// Check the mark still exists for the group
		recheckMarksGroup($_REQUEST['group'], $_REQUEST['mark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->unprotectMarkGroup($_REQUEST['group'],$_REQUEST['mark']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strunprotectmarkok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
	}

	/**
	 * Prepare comment mark group: ask for comment and confirmation
	 */
	function comment_mark_group() {
		global $misc, $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strcommentmarkerr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group']));

		// Check the group still exists
		recheckGroups($_REQUEST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_REQUEST['group'], $_REQUEST['mark'], $errMsgAction);

		printHeader();
		$misc->printTitle($lang['strcommentamark']);

		$mark = $emajdb->getMark($_REQUEST['group'],$_REQUEST['mark']);

		echo "<p>", sprintf($lang['strcommentmark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($mark->fields['mark_comment']), "\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"comment_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"commentmarkgroup\" value=\"{$lang['strok']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform comment mark group
	 */
	function comment_mark_group_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strcommentmarkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']));

		// Check the group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_POST['group'], $_POST['mark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->setCommentMarkGroup($_POST['group'],$_POST['mark'],$_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strcommentmarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare rename_mark_group: ask for the new name for the mark to rename and confirmation
	 */
	function rename_mark_group() {
		global $misc, $lang;

		if (!isset($_POST['group'])) $_POST['group'] = '';
		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrenamemarkerr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group']));

		// Check the group still exists
		recheckGroups($_REQUEST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_REQUEST['group'], $_REQUEST['mark'], $errMsgAction);

		printHeader();
		$misc->printTitle($lang['strrenameamark']);

		echo "<p>", sprintf($lang['strconfirmrenamemark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strnewnamemark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"newmark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"newmark\" required pattern=\"\S+.*\" placeholder='{$lang['strrequiredfield']}' autocomplete=\"off\"/ ></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"rename_mark_group_ok\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" value=\"{$lang['strrename']}\" id=\"ok\" disabled=\"disabled\"/>\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate />\n";
		echo "</div></form>\n";

		echo "<script>\n";
		echo "  $(document).ready(function () {\n";
		echo "    $(\"#newmark\").keyup(function (data) {\n";
		echo "      if ($(this).val() != \"\") {\n";
		echo "        $(\"#ok\").removeAttr(\"disabled\");\n";
		echo "      } else {\n";
		echo "        $(\"#ok\").attr(\"disabled\", \"disabled\");\n";
		echo "      }\n";
		echo "    });\n";
		echo "  });\n";
		echo "</script>";
	}

	/**
	 * Perform rename_mark_group
	 */
	function rename_mark_group_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrenamemarkerr2'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']), htmlspecialchars($_POST['newmark']));

		// Check the group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_POST['group'], $_POST['mark'], $errMsgAction);

		// Check the supplied mark is valid for the group
		$finalMarkName = checkNewMarkGroups($_POST['group'], $_POST['newmark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->renameMarkGroup($_POST['group'],$_POST['mark'], $finalMarkName);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strrenamemarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
	}

	/**
	 * Prepare delete mark group: ask for confirmation
	 */
	function delete_mark() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdeletemarkerr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group']));

		// Check the group still exists
		recheckGroups($_REQUEST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_REQUEST['group'], $_REQUEST['mark'], $errMsgAction);

		printHeader();
		$misc->printTitle($lang['strdeleteamark']);

		echo "<p>", sprintf($lang['strconfirmdeletemark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"delete_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"deletemark\" value=\"{$lang['strdelete']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform delete mark group
	 */
	function delete_mark_ok() {
		global $lang, $emajdb, $lang;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdeletemarkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']));

		// Check the group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_POST['group'], $_POST['mark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->deleteMarkGroup($_POST['group'],$_POST['mark']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strdeletemarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare delete mark group for several marks: ask for confirmation
	 */
	function delete_marks() {
		global $misc, $lang, $emajdb;

		// If only one mark is selected, switch to the mono-mark function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['mark'] = $a['mark'];
			delete_mark();
			return;
		}

		// Build the marks list
		$nbMarks = count($_REQUEST['ma']);
		$marksList='';
		$htmlList = "<div class=\"longlist\"><ul>\n";
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$marksList .= $a['mark'].', ';
			$htmlList .= "\t<li>{$a['mark']}</li>\n";
		}
		$marksList = substr($marksList, 0, strlen($marksList) - 2);
		$htmlList .= "</ul></div>\n";

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdeletemarkserr'], htmlspecialchars($marksList), htmlspecialchars($_REQUEST['group']));

		// Check the group still exists
		recheckGroups($_REQUEST['group'], $errMsgAction);

		// Check the marks still exist for the group
		recheckMarksGroup($_REQUEST['group'], $marksList, $errMsgAction);

		printHeader();
		$misc->printTitle($lang['strdeletemarks']);

		echo "<p>", sprintf($lang['strconfirmdeletemarks'], $nbMarks, htmlspecialchars($_REQUEST['group'])), "</p>\n{$htmlList}\n";
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"delete_marks_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"marks\" value=\"", htmlspecialchars($marksList), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"deletemarks\" value=\"{$lang['strdelete']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform delete mark group for several marks
	 */
	function delete_marks_ok() {
		global $data, $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdeletemarkserr'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group']));

		// Check the group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the marks still exist for the group
		recheckMarksGroup($_POST['group'], $_POST['marks'], $errMsgAction);

		// OK, perform the action
		$marks = explode(', ',$_POST['marks']);
		$status = $data->beginTransaction();
		if ($status == 0) {
			foreach($marks as $m) {
				$status = $emajdb->deleteMarkGroup($_POST['group'],$m);
				if ($status != 0) {
					$data->rollbackTransaction();
					doDefault('', $errMsgAction . '<br>' . sprintf('Internal error on mark %s', htmlspecialchars($m)));
					return;
				}
			}
		}
		if ($data->endTransaction() == 0)
			doDefault(sprintf($lang['strdeletemarksok'], count($marks), htmlspecialchars($_POST['group'])));
		else
			doDefault('', sprintf($lang['strdeletemarkserr'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare delete before mark group: ask for confirmation
	 */
	function delete_before_mark() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdelmarkspriorerr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group']));

		// Check the group still exists
		recheckGroups($_REQUEST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_REQUEST['group'], $_REQUEST['mark'], $errMsgAction);

		printHeader();
		$misc->printTitle($lang['strdelmarksprior']);

		echo "<p>", sprintf($lang['strconfirmdelmarksprior'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"delete_before_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"deletebeforemark\" value=\"{$lang['strdelete']}\" />\n";
		echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
		echo "</div></form>\n";
	}

	/**
	 * Perform delete before mark group
	 */
	function delete_before_mark_ok() {
		global $lang, $emajdb;

		// Process the click on the <cancel> button
		processCancelButton();

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdelmarkspriorerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']));

		// Check the group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_POST['group'], $_POST['mark'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->deleteBeforeMarkGroup($_POST['group'],$_POST['mark']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		doDefault(sprintf($lang['strdelmarkspriorok'], $status, htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
	}

?>
