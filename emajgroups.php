<?php
	/*
	 * Manage the E-Maj tables groups
	 */

	global $previous_cumlogrows;								// used to compute accumulated updates in marks'table
	global $protected_mark_flag;								// used to hide the rollback button for marks prior a protected mark 

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

/********************************************************************************************************
 * Callback functions 
 *******************************************************************************************************/

	/**
	 * callback functions to define authorized actions on lists
	 */

	// Functions to dynamicaly modify actions list for each table group in logging state to display
	function loggingGroupPre(&$rowdata, $loggingActions) {
		global $emajdb;
		// disable the rollback button for audit_only groups
		$isGroupRollbackable = $emajdb->isGroupRollbackable($rowdata->fields['group_name']);
		if (isset($loggingActions['rollback_group']) && !$isGroupRollbackable) {
			$loggingActions['rollback_group']['disable'] = true;
		}
		$isGroupProtected = $emajdb->isGroupProtected($rowdata->fields['group_name']);
		// disable the protect button for audit_only or protected groups
		if (isset($loggingActions['protect_group']) && (!$isGroupRollbackable || $isGroupProtected)) {
			$loggingActions['protect_group']['disable'] = true;
			$loggingActions['rollback_group']['disable'] = true;
		}
		// disable the unprotect button for audit_only or unprotected groups
		if (isset($loggingActions['unprotect_group']) && (!$isGroupRollbackable || !$isGroupProtected)) {
			$loggingActions['unprotect_group']['disable'] = true;
		}
		if ($emajdb->getNumEmajVersion() >= 30000) {
		// disable the alter_group button when there is no configuration change to apply
			if (isset($loggingActions['alter_group']) && (!$rowdata->fields['has_waiting_changes'])) {
				$loggingActions['alter_group']['disable'] = true;
			}
		}
		return $loggingActions;
	}

	// Functions to dynamicaly modify actions list for each table group in idle state to display
	function idleGroupPre(&$rowdata, $idleActions) {
		global $emajdb;
		if ($emajdb->getNumEmajVersion() >= 30000) {
		// disable the alter_group button when there is no configuration change to apply
			if (isset($idleActions['alter_group']) && (!$rowdata->fields['has_waiting_changes'])) {
				$idleActions['alter_group']['disable'] = true;
			}
		}
		return $idleActions;
	}

	// Function to dynamicaly modify actions list for each mark
	function markPre(&$rowdata, $actions) {
		global $emajdb, $protected_mark_flag;

		// disable the rollback button if the mark is deleted
		if (isset($actions['rollbackgroup']) && $rowdata->fields['mark_state'] == 'DELETED') {
			$actions['rollbackgroup']['disable'] = true;
		}
		// disable the rollback button if a previous mark is protected
		if ($protected_mark_flag == 1) {
			$actions['rollbackgroup']['disable'] = true;
		}
		// disable the protect button if the mark is already protected
		if (isset($actions['protectmark']) && $rowdata->fields['mark_state'] != 'ACTIVE') {
			$actions['protectmark']['disable'] = true;
		}
		// disable the unprotect button if the mark is not protected
		if (isset($actions['unprotectmark']) && $rowdata->fields['mark_state'] != 'ACTIVE-PROTECTED') {
			$actions['unprotectmark']['disable'] = true;
		}
		// if the mark is protected, set the flag to disable the rollback button for next marks
		// (this is not done in SQL because windowing functions are not available with pg version 8.3-)
		if ($rowdata->fields['mark_state']== 'ACTIVE-PROTECTED') {
			$protected_mark_flag = 1;
		}
		return $actions;
	}

	/**
	 * Render callback functions
	 */

	// Callback function to dynamicaly add an icon to each diagnostic message
	function renderDiagnostic($val) {
		global $misc;
		if (preg_match("/[Nn]o error /",$val)) {
			$icon = 'CheckConstraint';
		} else {
			$icon = 'CorruptedDatabase';
		}
		return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />" . $val;
	}

	// Callback function to dynamicaly modify the Table/Sequence columns content
	// It replaces the database value by an icon representing either a table or a sequence
	function renderTblSeq($val) {
		global $misc, $lang;
		if ($val == 'r+') {							// regular table
			$icon = $misc->icon('Table');
			$alt = $lang['strtable'];
		} elseif ($val == 'S+') {					// sequence
			$icon = $misc->icon('Sequence');
			$alt = $lang['strsequence'];
		} elseif ($val == '!') {					// object declared in the emaj_group_def table but unknown in the catalog
			$icon = $misc->icon('ObjectNotFound');
			$alt = $lang['emajunknownobject'];
		} else {									// unsupported type
			$icon = $misc->icon('ObjectNotFound');
			$alt = $lang['emajunsupportedobject'];
		}
		return "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
	}

	// Callback function to dynamicaly replace the group type from the database by one or two icons
	function renderGroupType($val) {
		global $misc, $lang;
		if ($val == 'ROLLBACKABLE') {
			$icon = $misc->icon('EmajRollbackable');
			$alt = $lang['emajrollbackable'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'AUDIT_ONLY') {
			$icon = $misc->icon('EmajAuditOnly');
			$alt = $lang['emajauditonly'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'ROLLBACKABLE-PROTECTED') {
			$icon = $misc->icon('EmajRollbackable');
			$alt = $lang['emajrollbackable'];
			$img = "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
			$icon = $misc->icon('EmajPadlock');
			$alt = $lang['emajprotected'];
			$img .= "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return $img;
	}

	// Callback function to dynamicaly modify the diagnostic column of configured but not yet created groups
	// It replaces the database value by an icon
	function renderDiagnosticNewGroup($val) {
		global $misc, $lang;

		if ($val == '{0,0,0,0,0}') {
			$icon = 'CheckConstraint';
			return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />";
		} else {
			if (preg_match("/{(\d+),(\d+),(\d+),(\d+),(\d+)}/",$val,$cpt)) {
				$msg = '';
				if ($cpt[1] > 0) $msg .= sprintf($lang['emajnoschema'], $cpt[1]);
				if ($cpt[2] > 0) $msg .= sprintf($lang['emajinvalidschema'], $cpt[2]);
				if ($cpt[3] > 0) $msg .= sprintf($lang['emajnorelation'], $cpt[3]);
				if ($cpt[4] > 0) $msg .= sprintf($lang['emajinvalidtable'], $cpt[4]);
				if ($cpt[5] > 0) $msg .= sprintf($lang['emajduplicaterelation'], $cpt[5]);
				$msg = substr($msg,0,-3);
				return $msg;
			} else {
				return "$val not decoded";
			}
		}
	}

	// Callback function to dynamicaly modify the group state column content
	// It replaces the database value by an icon
	function renderGroupState($val) {
		global $misc, $lang;
		if ($val == 'IDLE') {
			$icon = $misc->icon('EmajIdle');
			$alt = $lang['emajidle'];
		} else {
			$icon = $misc->icon('EmajLogging');
			$alt = $lang['emajlogging'];
		}
		return "<img src=\"{$icon}\" style=\"vertical-align:bottom;\" alt=\"{$alt}\" title=\"{$alt}\"/>";
	}

	// Callback function to dynamicaly translate a boolean column into an icon
	function renderBooleanIcon($val) {
		global $misc;
		if ($val == 't') {
			$icon = 'CheckConstraint';
		} else {
			$icon = 'Delete';
		}
		return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />";
	}

	// Callback function to dynamicaly translate a comment cell in a simple '...' text and the real comment in tooltop, using css
	function renderCommentInTooltip($val, $params) {
		global $lang;
		if (isset($params['cliplen']) && strlen($val) >= $params['cliplen'])
			return "<div class=\"comment-in-tooltip\">" . substr($val,0,$params['cliplen']) . "{$lang['strellipsis']}<span>{$val}</span></div>";
		else
			return $val;
	}

	// Callback function to dynamicaly modify the mark state column content
	// It replaces the database value by an icon
	function renderMarkState($val) {
		global $misc, $lang;
		if ($val == 'ACTIVE') {
			$img = "<img src=\"{$misc->icon('ActiveMark')}\" style=\"vertical-align:bottom;\" alt=\"active_mark\" title=\"{$lang['emajactivemark']}\"/>";
		} elseif ($val == 'DELETED') {
			$img = "<img src=\"{$misc->icon('DeletedMark')}\" style=\"vertical-align:bottom;\" alt=\"deleted_mark\" title=\"{$lang['emajdeletedmark']}\"/>";
		} elseif ($val == 'ACTIVE-PROTECTED') {
			$img = "<img src=\"{$misc->icon('ActiveMark')}\" style=\"vertical-align:bottom;\" alt=\"active_mark\" title=\"{$lang['emajactivemark']}\"/>";
			$img .= "<img src=\"{$misc->icon('EmajPadlock')}\" style=\"vertical-align:bottom;\" alt=\"protected\" title=\"{$lang['emajprotectedmark']}\"/>";
		}
		return $img;
	}

	// Callback function to dynamicaly add an icon to each rollback execution report
	function renderRlbkExecSeverity($val) {
		global $misc;
		if ($val == 'Notice') {
			$icon = 'CheckConstraint';
		} else {
			$icon = 'EmajWarning';
		}
		return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />";
	}

	// Callback function to dynamicaly transform a message severity level into an icon
	function renderMsgSeverity($val) {
		global $misc;
		if ($val == '1' || $val == '2') {
			$icon = 'Delete';
		} elseif ($val == '3') {
			$icon = 'EmajWarning';
		} elseif ($val == '4') {
			$icon = 'CheckConstraint';
		} else {
			return '?';
		}
		return "<img src=\"".$misc->icon($icon)."\" style=\"vertical-align:bottom;\" />";
	}

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Show list of created emaj groups
	 */
	function show_groups($msg = '', $errMsg = '') {
		global $lang, $misc, $emajdb;

		$misc->printHeader('database', 'database', 'emajgroups');

		$misc->printMsg($msg,$errMsg);

		$idleGroups = $emajdb->getIdleGroups();
		$loggingGroups = $emajdb->getLoggingGroups();

		$nbGroup = $idleGroups->recordCount() + $loggingGroups->recordCount();

		$columns = array(
			'group' => array(
				'title' => $lang['emajgroup'],
				'field' => field('group_name'),
				'url'   => "emajgroups.php?action=show_group&amp;{$misc->href}&amp;",
				'vars'  => array('group' => 'group_name'),
			),
			'creationdatetime' => array(
				'title' => $lang['emajcreationdatetime'],
				'field' => field('creation_datetime'),
				'params'=> array('align' => 'center'),
			),
			'nbtbl' => array(
				'title' => $lang['emajnbtbl'],
				'field' => field('group_nb_table'),
				'type'  => 'numeric'
			),
			'nbseq' => array(
				'title' => $lang['emajnbseq'],
				'field' => field('group_nb_sequence'),
				'type'  => 'numeric'
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
			'nbmark' => array(
				'title' => $lang['emajnbmark'],
				'field' => field('nb_mark'),
				'type'  => 'numeric'
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('group_comment'),
				'type'	=> 'callback',
				'params'=> array(
						'function' => 'renderCommentInTooltip',
						'cliplen' => 8,
						),
			),
		);

		$urlvars = $misc->getRequestVars();

		$loggingActions = array();
		if ($emajdb->isEmaj_Adm()) {
			$loggingActions = array_merge($loggingActions, array(
				'multiactions' => array(
					'keycols' => array('group' => 'group_name'),
					'url' => "emajgroups.php?back=list",
				),
				'set_mark_group' => array(
					'content' => $lang['emajsetmark'],
					'icon' => 'Pin',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'set_mark_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'set_mark_groups',
				),
				'protect_group' => array(
					'content' => $lang['emajprotect'],
					'icon' => 'PadLockOn',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'protect_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
					),
				'unprotect_group' => array(
					'content' => $lang['emajunprotect'],
					'icon' => 'PadLockOff',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'unprotect_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
					),
				'rollback_group' => array(
					'content' => $lang['emajrlbk'],
					'icon' => 'Rewind',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'rollback_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'rollback_groups',
				),
				'stop_group' => array(
					'content' => $lang['strstop'],
					'icon' => 'Stop',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'stop_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'stop_groups',
				),
				'comment_group' => array(
					'content' => $lang['emajsetcomment'],
					'icon' => 'Bubble',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'comment_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
				))
			);
			if ($emajdb->getNumEmajVersion() >= 20100 && $emajdb->getNumEmajVersion() < 30200) {	// 2.1 <= version < 3.2
				$loggingActions = array_merge($loggingActions, array(
					'alter_group' => array(
						'content' => $lang['emajApplyConfChanges'],
						'attr' => array (
							'href' => array (
								'url' => 'emajgroups.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'alter_group',
									'back' => 'list',
									'group' => field('group_name'),
							))))
					),
				));
				$loggingActions['alter_group']['multiaction'] = 'alter_groups';
			};
		};

		$idleActions = array();
		if ($emajdb->isEmaj_Adm()) {
			$idleActions = array_merge($idleActions, array(
				'multiactions' => array(
					'keycols' => array('group' => 'group_name'),
					'url' => "emajgroups.php?back=list",
				),
				'start_group' => array(
					'content' => $lang['strstart'],
					'icon' => 'Start',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'start_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'start_groups',
				),
				'reset_group' => array(
					'content' => $lang['strreset'],
					'icon' => 'Eraser',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'reset_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'reset_groups',
				),
			));

			if ($emajdb->getNumEmajVersion() < 30200) {				// version < 3.2
				$idleActions = array_merge($idleActions, array(
					'alter_group' => array(
						'content' => $lang['emajApplyConfChanges'],
						'attr' => array (
							'href' => array (
								'url' => 'emajgroups.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'alter_group',
									'back' => 'list',
									'group' => field('group_name'),
							))))
					),
				));
				if ($emajdb->getNumEmajVersion() >= 20100) {		// version >= 2.1.0
					$idleActions['alter_group']['multiaction'] = 'alter_groups';
				}
			}
			$idleActions = array_merge($idleActions, array(
				'drop_group' => array(
					'content' => $lang['strdrop'],
					'icon' => 'Bin',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'drop_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'drop_groups',
				),
				'comment_group' => array(
					'content' => $lang['emajsetcomment'],
					'icon' => 'Bubble',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'comment_group',
								'back' => 'list',
								'group' => field('group_name'),
							))))
				),
			));
		};

		$configuredColumns = array(
			'group' => array(
				'title' => $lang['emajgroup'],
				'field' => field('grpdef_group'),
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
			'nbtbl' => array(
				'title' => $lang['emajnbtbl'],
				'field' => field('group_nb_table'),
				'type'  => 'numeric'
			),
			'nbseq' => array(
				'title' => $lang['emajnbseq'],
				'field' => field('group_nb_sequence'),
				'type'  => 'numeric'
			),
		);
		if ($emajdb->getNumEmajVersion() < 30000) {	// version < 3.0.0
			$configuredColumns = array_merge($configuredColumns, array(
				'diagnostic' => array(
					'title' => $lang['emajdiagnostics'],
					'field' => field('group_diagnostic'),
					'type'	=> 'callback',
					'params'=> array(
							'function' => 'renderDiagnosticNewGroup',
							)
				),
			));
		}

		if ($emajdb->isEmaj_Adm()) {
			$configuredActions = array(
				'create_group' => array(
					'content' => $lang['strcreate'],
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'create_configured_group',
								'back' => 'list',
								'group' => field('grpdef_group'),
							))))
				),
			);
		} else {
			$configuredActions = array();
		}

		$misc->printTitle("{$lang['emajlogginggroups']}<img src=\"{$misc->icon('Info-inv')}\" alt=\"info\" title=\"{$lang['emajlogginggrouphelp']}\"/>");

		$misc->printTable($loggingGroups, $columns, $loggingActions, 'loggingGroups', $lang['emajnologginggroup'], 'loggingGroupPre', array('sorter' => true, 'filter' => true));

		echo "<hr>";
		$misc->printTitle("{$lang['emajidlegroups']}<img src=\"{$misc->icon('Info-inv')}\" alt=\"info\" title=\"{$lang['emajidlegrouphelp']}\"/>");

		$misc->printTable($idleGroups, $columns, $idleActions, 'idleGroups', $lang['emajnoidlegroup'], 'idleGroupPre', array('sorter' => true, 'filter' => true));

		echo "<hr/>\n";

		if ($emajdb->getNumEmajVersion() < 30200) {					// version < 3.2
			// configured but not yet created tables groups section
			$configuredGroups = $emajdb->getConfiguredGroups();

			$misc->printTitle("{$lang['emajconfiguredgroups']}<img src=\"{$misc->icon('Info-inv')}\" alt=\"info\" title=\"{$lang['emajconfiguredgrouphelp']}\"/>");

			$misc->printTable($configuredGroups, $configuredColumns, $configuredActions, 'configuredGroups', $lang['emajnoconfiguredgroups'], null, array('sorter' => true, 'filter' => true));

			// for emaj_adm role only, give information about how to create a group
			if ($emajdb->isEmaj_Adm()) {
				if ($emajdb->getNumEmajVersion() >= 20100) {			// version >= 2.1.0
					echo "<p>{$lang['emajnoconfiguredgroup']}</p>\n";
					echo "<form id=\"createEmptyGroup_form\" action=\"emajgroups.php?action=create_group&amp;back=list&amp;empty=true&amp;{$misc->href}\"";
					echo " method=\"post\" enctype=\"multipart/form-data\">\n";
					echo "\t<input type=\"submit\" value=\"{$lang['emajcreateemptygroup']}\" />\n";
					echo "</form>\n";
				}
			}
		} else {
			// Emaj Version 3.2+
			// for emaj_adm role only, display additional buttons
			if ($emajdb->isEmaj_Adm()) {
				echo "<div class=\"actionslist\">\n";
				// display the "new group" button
				echo "\t<form id=\"createEmptyGroup_form\" action=\"emajgroups.php?action=create_group&amp;back=list&amp;{$misc->href}\"";
				echo " method=\"post\" enctype=\"multipart/form-data\">\n";
				echo "\t\t<input type=\"submit\" value=\"{$lang['emajnewgroup']}\" />\n";
				echo "\t</form>\n";

				// display the "export groups configuration" and "import groups configuration" buttons
				if ($emajdb->getNumEmajVersion() >= 30300) {			// version >= 3.3.0
					// form to export groups configuration
					// the export button is disabled when no group exists
					echo "\t<form id=\"exportGroupsConf_form\" action=\"emajgroups.php?action=export_groups&amp;back=list&amp;{$misc->href}\"";
					echo " method=\"post\" enctype=\"multipart/form-data\">\n";
					$disabled = ''; if ($nbGroup = 0) $disabled = ' disabled';
					echo "\t\t<input type=\"submit\" name=\"exportButton\" value=\"${lang['strexport']}\"{$disabled}>\n";
					echo "\t</form>\n";

					// form to import groups configuration
					echo "\t<form name=\"importGroupsConf\" id=\"importGroupsConf\" method=\"POST\"";
					echo " action=\"emajgroups.php?action=import_groups&amp;back=list&amp;{$misc->href}\">\n";
					echo "\t\t<input type=\"submit\" name=\"importButton\" value=\"${lang['strimport']}\">\n";
					echo "\t</form>\n";
				}
				echo "</div>\n";
			}
		}
	}

	/**
	 * Displays all detailed information about one group, including marks
	 */
	function show_group($msg = '', $errMsg = '') {
		global $misc, $lang, $emajdb, $previous_cumlogrows;

		$misc->printHeader('emaj', 'emajgroup', 'emajgroupproperties');

		$misc->printMsg($msg,$errMsg);

		// general information about the group
		$group = $emajdb->getGroup($_REQUEST['group']);

		// save some fields before calling printTable()
		$comment = $group->fields['group_comment'];
		$nbMarks = $group->fields['nb_mark'];
		$groupState = $group->fields['group_state'];
		$groupType = $group->fields['group_type'];
		$hasWaitingChanges = $group->fields['has_waiting_changes'];

		$columns = array(
			'state' => array(
				'title' => $lang['emajstate'],
				'field' => field('group_state'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderGroupState','align' => 'center')
			),
			'creationdatetime' => array(
				'title' => $lang['emajcreationdatetime'],
				'field' => field('group_creation_datetime'),
				'params'=> array('align' => 'center'),
			),
			'rollbackable' => array(
				'title' => $lang['strtype'],
				'field' => field('group_type'),
				'type'	=> 'callback',
				'params'=> array(
						'function' => 'renderGroupType',
						'align' => 'center',
						),
			),
			'nbtbl' => array(
				'title' => $lang['emajnbtbl'],
				'field' => field('group_nb_table'),
				'type'  => 'numeric'
			),
			'nbseq' => array(
				'title' => $lang['emajnbseq'],
				'field' => field('group_nb_sequence'),
				'type'  => 'numeric'
			),
			'nbmark' => array(
				'title' => $lang['emajnbmark'],
				'field' => field('nb_mark'),
				'type'  => 'numeric'
			),
			'logsize' => array(
				'title' => $lang['emajlogsize'],
				'field' => field('log_size'),
				'params'=> array('align' => 'center'),
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
		);

		$urlvars = $misc->getRequestVars();

		$groupActions = array();

		// print group's characteristics
		$misc->printTitle(sprintf($lang['emajgroupproperties'], htmlspecialchars($_REQUEST['group'])));
		$misc->printTable($group, $columns, $groupActions, 'detailGroup', 'no group, internal error !');

		// display group's comment if exists
		if ($comment<>'') {
			echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$comment}</span></p>\n";
		}

		// display the buttons corresponding to the available functions for the group, depending on its state

		if ($emajdb->isEmaj_Adm()) {
			echo "<p>\n";

			// start_group
			if ($groupState == 'IDLE') {
				echo "<form id=\"start_group_form\" action=\"emajgroups.php?action=start_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline;\">\n";
				echo "  <input type=\"submit\" name=\"startgroup\" value=\"{$lang['strstart']}\" />";
				echo "</form>\n";
			}

			// set_mark_group
			if ($groupState == 'LOGGING') {
				echo "<form id=\"set_mark_group_form\" action=\"emajgroups.php?action=set_mark_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline;\">\n";
				echo "  <input type=\"submit\" name=\"setmarkgroup\" value=\"{$lang['emajsetmark']}\" />";
				echo "</form>\n";
			}

			// reset_group
			if ($groupState == 'IDLE') {
				echo "<form id=\"reset_group_form\" action=\"emajgroups.php?action=reset_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
				echo "  <input type=\"submit\" name=\"resetgroup\" value=\"{$lang['strreset']}\" />";
				echo "</form>\n";
			}

			// protect_group
			if ($groupState == 'LOGGING' && $groupType == "ROLLBACKABLE") {
				echo "<form id=\"protect_group_form\" action=\"emajgroups.php?action=protect_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
				echo "  <input type=\"submit\" name=\"protectgroup\" value=\"{$lang['emajprotect']}\" />";
				echo "</form>\n";
			}

			// unprotect_group
			if ($groupState == 'LOGGING' && $groupType == "ROLLBACKABLE-PROTECTED") {
				echo "<form id=\"unprotect_group_form\" action=\"emajgroups.php?action=unprotect_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
				echo "  <input type=\"submit\" name=\"unprotectgroup\" value=\"{$lang['emajunprotect']}\" />";
				echo "</form>\n";
			}

			// stop_group
			if ($groupState == 'LOGGING') {
				echo "<form id=\"stop_group_form\" action=\"emajgroups.php?action=stop_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
				echo "  <input type=\"submit\" name=\"stopgroup\" value=\"{$lang['strstop']}\" />";
				echo "</form>\n";
			}

			// comment_group
			echo "<form id=\"comment_group_form\" action=\"emajgroups.php?action=comment_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:30px;\">\n";
			echo "  <input type=\"submit\" name=\"commentgroup\" value=\"{$lang['emajsetcomment']}\" />";
			echo "</form>\n";

			// alter_group
			if ($hasWaitingChanges && ($groupState == 'IDLE' || $emajdb->getNumEmajVersion() >= 20100) && $emajdb->getNumEmajVersion() < 30200) {
				echo "<form id=\"alter_group_form\" action=\"emajgroups.php?action=alter_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
				echo "  <input type=\"submit\" name=\"altergroup\" value=\"{$lang['emajApplyConfChanges']}\" />";
				echo "</form>\n";
			}

			// drop_group
			if ($groupState == 'IDLE') {
				echo "<form id=\"drop_group_form\" action=\"emajgroups.php?action=drop_group&amp;&amp;group=",urlencode($_REQUEST['group']),"&amp;back=detail&amp;{$misc->href}\" method=\"post\" style=\"display:inline; margin-left:5px;\">\n";
				echo "  <input type=\"submit\" name=\"dropgroup\" value=\"{$lang['strdrop']}\" />";
				echo "</form>\n";
			}

			echo "</p>\n";
		}

		// Show marks of the groups

		// get marks from database
		$marks = $emajdb->getMarks($_REQUEST['group']);

		echo "<hr/>\n";
		$misc->printTitle(sprintf($lang['emajgroupmarks'], htmlspecialchars($_REQUEST['group'])));

		$columns = array(
			'mark' => array(
				'title' => $lang['emajmark'],
				'field' => field('mark_name'),
			),
			'state' => array(
				'title' => $lang['emajstate'],
				'field' => field('mark_state'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderMarkState','align' => 'center'),
				'filter'=> false,
			),
			'datetime' => array(
				'title' => $lang['emajtimestamp'],
				'field' => field('mark_datetime'),
			),
			'logrows' => array(
				'title' => $lang['emajnbchanges'],
				'field' => field('mark_logrows'),
				'type'  => 'numeric'
			),
			'cumlogrows' => array(
				'title' => $lang['emajcumchanges'],
				'info'  => $lang['emajcumchangeshelp'],
				'field' => field('mark_cumlogrows'),
				'type'  => 'numeric'
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('mark_comment'),
				'type'	=> 'callback',
				'params'=> array(
						'function' => 'renderCommentInTooltip',
						'cliplen' => 8,
						),
			),
		);

		$urlvars = $misc->getRequestVars();

		$actions = array();
		if ($emajdb->isEmaj_Adm() && ($nbMarks > 1)) {
			$actions = array_merge($actions, array(
				'multiactions' => array(
					'keycols' => array('group' => 'mark_group', 'mark' => 'mark_name'),
					'url' => "emajgroups.php?group={$_REQUEST['group']}&amp;back=detail&amp;",
				),
			));
		}
		if ($emajdb->isEmaj_Adm() && $groupType == "ROLLBACKABLE") {
			$actions = array_merge($actions, array(
				'rollbackgroup' => array(
					'content' => $lang['emajrlbk'],
					'icon' => 'Rewind',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'rollback_group',
								'back' => 'detail',
								'group' => field('mark_group'),
								'mark' => field('mark_name'),
							))))
				),
			));
		}
		if ($emajdb->isEmaj_Adm()) {
			$actions = array_merge($actions, array(
				'renamemark' => array(
					'content' => $lang['emajrename'],
					'icon' => 'Pencil',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'rename_mark_group',
								'back' => 'detail',
								'group' => field('mark_group'),
								'mark' => field('mark_name'),
							))))
				),
			));
		}
		if ($emajdb->isEmaj_Adm() && ($nbMarks > 1)) {
			$actions = array_merge($actions, array(
				'deletemark' => array(
					'content' => $lang['strdelete'],
					'icon' => 'Eraser',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'delete_mark',
								'back' => 'detail',
								'group' => field('mark_group'),
								'mark' => field('mark_name'),
							)))),
					'multiaction' => 'delete_marks',
				),
			));
		}
		if ($emajdb->isEmaj_Adm()) {
			$actions = array_merge($actions, array(
				'deletebeforemark' => array(
					'content' => $lang['emajfirstmark'],
					'icon' => 'First',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'delete_before_mark',
								'back' => 'detail',
								'group' => field('mark_group'),
								'mark' => field('mark_name'),
							))))
				),
			));
		}
		if ($emajdb->isEmaj_Adm() && $groupState == 'LOGGING' && $groupType != "AUDIT_ONLY") {
			$actions = array_merge($actions, array(
				'protectmark' => array(
					'content' => $lang['emajprotect'],
					'icon' => 'PadLockOn',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'protect_mark_group',
								'back' => 'detail',
								'group' => field('mark_group'),
								'mark' => field('mark_name'),
							))))
				),
				'unprotectmark' => array(
					'content' => $lang['emajunprotect'],
					'icon' => 'PadLockOff',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'unprotect_mark_group',
								'back' => 'detail',
								'group' => field('mark_group'),
								'mark' => field('mark_name'),
							))))
				),
			));
		};
		if ($emajdb->isEmaj_Adm()) {
			$actions = array_merge($actions, array(
				'commentmark' => array(
					'content' => $lang['emajsetcomment'],
					'icon' => 'Bubble',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'comment_mark_group',
								'back' => 'detail',
								'group' => field('mark_group'),
								'mark' => field('mark_name'),
							))))
				),
			));
		};

		// reset the flag for protected marks that will be used in the markPre function
		$protected_mark_flag = 0;

		// display the marks list
		$misc->printTable($marks, $columns, $actions, 'marks', $lang['emajnomark'], 'markPre', array('sorter' => false, 'filter' => true));

		// JQuery to remove the last deleteBeforeMark button as it is meaningless on the first set mark
		echo "<script>\n";
		echo "  $(\"table.data tr:last td.textbutton a:contains('{$lang['emajfirstmark']}')\").remove()\n";
		echo "  $(\"table.data tr:last td.iconbutton a img[alt='{$lang['emajfirstmark']}']\").parent('a').remove()\n";
		echo "  $(\"table.data tr:last td:empty\").removeClass()\n";
		echo "  $(\"table.data tr:last td:empty\").addClass('emptybutton')\n";
		echo "</script>\n";
	}

	/**
	 * Show global or detailed log statistics between 2 marks or since a mark
	 */
	function log_stat_group() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('emaj', 'emajgroup', 'emajlogstat');

		$misc->printTitle(sprintf($lang['emajshowstat'], htmlspecialchars($_REQUEST['group'])));

		// display the stat form

		$globalStat = false; $detailedStat = false; $simRlbk = false;
		if (isset($_REQUEST['simrlbk'])) {
			$simRlbk = true;
		}
		if (isset($_REQUEST['globalstatgroup'])) {
			$globalStat = true;
			$urlExt = 'globalstatgroup='.urlencode($_REQUEST['globalstatgroup']);
		}
		if (isset($_REQUEST['detailedstatgroup'])) {
			$detailedStat = true;
			$urlExt = 'detailedstatgroup='.urlencode($_REQUEST['detailedstatgroup']);
		}

		// get marks from database
		$marks = $emajdb->getMarks($_REQUEST['group']);

		// get group's characteristics
		$group = $emajdb->getGroup($_REQUEST['group']);

		if ($marks->recordCount() < 1) {

			// No mark recorded for the group => no update logged => no stat to display
			echo "<p>{$lang['emajnomark']}</p>\n"; 

		} else {

			// form for statistics selection
			echo "<form id=\"statistics_form\" action=\"emajgroups.php?action=log_stat_group&amp;back=detail&amp;{$misc->href}\"";
			echo "  method=\"post\" enctype=\"multipart/form-data\">\n";

			// First mark defining the marks range to analyze
			echo "<p>{$lang['emajfrom']}\n";
			echo "  <select name=\"rangestart\" id=\"rangestart\">\n";
			foreach($marks as $r)
				echo "    <option value=\"", htmlspecialchars($r['mark_name']), "\" >", htmlspecialchars($r['mark_name']), " ({$r['mark_datetime']})</option>\n";
			echo "  </select>\n";

			// Last mark defining the marks range to analyze
			echo "{$lang['emajto']}\n";
			echo "  <select name=\"rangeend\" id=\"rangeend\" >\n";
			echo "    <option value=\"currentsituation\">{$lang['emajcurrentsituation']}</option>\n";
			foreach($marks as $r)
				echo "    <option value=\"", htmlspecialchars($r['mark_name']), "\" >", htmlspecialchars($r['mark_name']), " ({$r['mark_datetime']})</option>\n";
			echo "  </select></p>\n";

			// Other elements of the form
			echo "  <p><input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";

			// Rollback simulation checkbox is only available for rollbackable groups in logging state
			if ($group->fields['group_type'] == 'ROLLBACKABLE' && $group->fields['group_state'] == 'LOGGING') {
				echo "  {$lang['emajsimrlbk']} <input type=\"checkbox\" name=\"simrlbk\" id=\"simrlbk\"/>\n";
			}

			echo "  <input type=\"submit\" name=\"globalstatgroup\" value=\"{$lang['emajestimates']}\" />\n";
			echo "  <input type=\"submit\" name=\"detailedstatgroup\" value=\"{$lang['emajdetailedstat']}\" />\n";
			echo "  <img src=\"{$misc->icon('EmajWarning')}\" alt=\"warning\" title=\"{$lang['emajdetailedlogstatwarning']}\" style=\"vertical-align:middle\"/>";
			echo "</p></form>\n";

			// JQuery scripts
			echo "<script>\n";

			// JQuery to remove the last mark as it cannot be selected as end mark
			echo "  $(\"#rangeend option:last-child\").remove();\n";

			// JQuery to set the selected start mark by default 
			// (the previous requested start mark or the first mark if no stat are already displayed)
			if (isset($_REQUEST['rangestart'])) {
				echo "  $(\"#rangestart option[value='{$_REQUEST['rangestart']}']\").attr(\"selected\", true);\n";
			} else {
				echo "  $(\"#rangestart option:first-child\").attr(\"selected\", true);\n";
			}

			// JQuery to set the selected end mark by default 
			// (the previous requested end mark or the current situation if no stat are already displayed)
			if (isset($_REQUEST['rangeend'])) {
				echo "  $(\"#rangeend option[value='{$_REQUEST['rangeend']}']\").attr(\"selected\", true);\n";
			} else {
				echo "  $(\"#rangeend option:first-child\").attr(\"selected\", true);\n";
			}

			// JQuery script to avoid rangestart > rangeend
				// After document loaded
			echo "  $(document).ready(function() {\n";
			echo "    mark = $(\"#rangestart option:selected\").val();\n";
			echo "    todisable = false;\n";
			echo "    $(\"#rangeend option\").each(function() {\n";
			echo "      $(this).prop('disabled', todisable);\n";
			echo "      if ($(this).val() == mark) {todisable = true;}\n";
			echo "    });\n";
			echo "    mark = $(\"#rangeend option:selected\").val();\n";
			echo "    todisable = true;\n";
			echo "    if (mark == \"currentsituation\") {todisable = false;}\n";
			echo "    $(\"#rangestart option\").each(function() {\n";
			echo "      if ($(this).val() == mark) { todisable = false; }\n";
			echo "      $(this).prop('disabled', todisable);\n";
			echo "    });\n";
					// and disable the simrlbk checkbox if the rangeend is not "currentsituation"
			echo "    if ($(\"#rangeend option:selected\").val() == \"currentsituation\") { \n";
			echo "      $(\"#simrlbk\").removeAttr('checked');\n";
			echo "      $(\"#simrlbk\").removeAttr('disabled');\n";
			echo "    } else {\n";
			echo "      $(\"#simrlbk\").prop('disabled',true);\n";
			echo "    }\n";
			echo "  });\n";

				// At each list box change
			echo "  $(\"#rangestart\").change(function () {\n";
			echo "    mark = $(\"#rangestart option:selected\").val();\n";
			echo "    todisable = false;\n";
			echo "    $(\"#rangeend option\").each(function() {\n";
			echo "      $(this).prop('disabled', todisable);\n";
			echo "      if ($(this).val() == mark) {todisable = true;}\n";
			echo "    });\n";
			echo "  });\n";
			echo "  $(\"#rangeend\").change(function () {\n";
			echo "    mark = $(\"#rangeend option:selected\").val();\n";
			echo "    todisable = true;\n";
			echo "    if (mark == \"currentsituation\") {todisable = false;}\n";
			echo "    $(\"#rangestart option\").each(function() {\n";
			echo "      if ($(this).val() == mark) { todisable = false; }\n";
			echo "      $(this).prop('disabled', todisable);\n";
			echo "    });\n";
					// and disable the simrlbk checkbox if the rangeend is not "currentsituation"
			echo "    if ($(\"#rangeend option:selected\").val() == \"currentsituation\") { \n";
			echo "      $(\"#simrlbk\").removeAttr('checked');\n";
			echo "      $(\"#simrlbk\").removeAttr('disabled');\n";
			echo "    } else {\n";
			echo "      $(\"#simrlbk\").prop('disabled',true);\n";
			echo "    }\n";
			echo "  });\n";

			echo "</script>\n";

			// If rollback simulation is requested, compute the duration estimate
			$rlbkDuration = '';
			if ($simRlbk) {
				// check the start mark is not deleted
				if ($emajdb->isMarkActiveGroup($_REQUEST['group'],$_REQUEST['rangestart']) == 1) {
					$rlbkDuration = $emajdb->estimateRollbackGroup($_REQUEST['group'],$_REQUEST['rangestart']);
				} else {
					$rlbkDuration = "-";
				}
			}

			// If global stat display is requested
			if ($globalStat) {
				disp_global_log_stat_section($rlbkDuration);
			}

			// If detailed stat display is requested
			if ($detailedStat) {
				disp_detailed_log_stat_section($rlbkDuration);
			}
		}
	}

	/**
	 * This function is called by the log_stat_group() function.
	 * It generates the page section corresponding to the rollback simulation output
	 */
	function disp_global_log_stat_section($rlbkDuration) {
		global $misc, $lang, $emajdb;

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend']=='currentsituation') {
			$w1 = $lang['emajlogstatcurrentsituation'];
			$stats = $emajdb->getLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],'');
		} else {
			$w1 = sprintf($lang['emajlogstatmark'], $_REQUEST['rangeend']);
			$stats = $emajdb->getLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);
		}
		$summary = $emajdb->getLogStatSummary();

		// Title
		echo "<hr/>\n";
		$misc->printTitle(sprintf($lang['emajlogstattittle'], htmlspecialchars($_REQUEST['rangestart']), htmlspecialchars($w1), htmlspecialchars($_REQUEST['group'])));

		// Display summary statistics
		echo "<div style=\"margin-bottom:15px\">\n";
		echo "<table class=\"data\"><tr>\n";
		echo "<th class=\"data\" colspan=2>{$lang['emajestimates']}</th>\n";
		echo "</tr><tr>\n";
		echo "<th class=\"data\">{$lang['emajnbtbl']}</th>";
		echo "<th class=\"data\">{$lang['emajnbchanges']}</th>";
		echo "</tr><tr class=\"data1\">\n";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_tables']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['sum_rows']}</div></td>";
		echo "</tr></table>\n";
		echo "</div>\n";

		// Display rollback duration estimate if requested
		if ($rlbkDuration != '') {
			if ($rlbkDuration == '-') {
				// the start mark cannot be used for a rollback
				echo "<p>{$lang['emajnosimrlbkduration']}</p>";
			} else {
				// dispay the duration estimate
				echo "<p>",sprintf($lang['emajsimrlbkduration'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['rangestart']),$rlbkDuration),"</p>\n";
			}
		}

		if ($summary->fields['nb_tables'] > 0) {

			// Display per table statistics
			$urlvars = $misc->getRequestVars();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('stat_schema'),
					'url'   => "redirect.php?subject=schema&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema'),
				),
				'table' => array(
					'title' => $lang['strtable'],
					'field' => field('stat_table'),
					'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema', 'table' => 'stat_table'),
				));
			if ($emajdb->getNumEmajVersion() >= 20300) {			// version >= 2.3.0
				$columns = array_merge($columns, array(
					'stat_first_mark' => array(
						'title' => $lang['emajstatfirstmark'],
						'field' => field('stat_first_mark'),
					),
					'stat_first_mark_datetime' => array(
						'title' => $lang['emajstatfirstmarkdatetime'],
						'field' => field('stat_first_mark_datetime'),
					),
					'stat_last_mark' => array(
						'title' => $lang['emajstatlastmark'],
						'field' => field('stat_last_mark'),
					),
					'stat_last_mark_datetime' => array(
						'title' => $lang['emajstatlastmarkdatetime'],
						'field' => field('stat_last_mark_datetime'),
					),
				));
			}
			$columns = array_merge($columns, array(
				'nbrow' => array(
					'title' => $lang['emajstatrows'],
					'field' => field('stat_rows'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			));

			$actions = array(
				'sql' => array(
					'content' => $lang['emajbrowsechanges'],
					'icon' => 'Eye',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'call_sqledit',
								'subject' => 'table',
								'sqlquery' => field('sql_text'),
								'paginate' => 'true',
					)))),
				),
			);

			$misc->printTable($stats, $columns, $actions, 'logStats', null, null, array('sorter' => true, 'filter' => true));

			// dynamicaly change the behaviour of the SQL link using JQuery code: open a new window
			// the link may be either a text button with a SQL content (td of type textbutton) or an icon (td of type iconbutton)
			$sql_window_id = htmlentities('emaj_sqledit:'.$_REQUEST['server']);
			echo "<script>
			$(\"#logStats\").find(\"td.textbutton a:contains('SQL'), td.iconbutton a\").click(function() {
				window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=400,resizable=yes,scrollbars=yes').focus();
				return false;
			});
			</script>\n";
		}
	}

	/**
	 * This function is called by the log_stat_group() function.
	 * It generates the page section corresponding to the rollback simulation output
	 */
	function disp_detailed_log_stat_section($rlbkDuration) {
		global $misc, $lang, $emajdb;

		if (!ini_get('safe_mode')) set_time_limit(0);		// Prevent timeouts on large stats (non-safe mode only)

		// Get statistics from E-Maj
		if ($_REQUEST['rangeend']=='currentsituation') {
			$w1 = $lang['emajlogstatcurrentsituation'];
			$stats = $emajdb->getDetailedLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],'');
		} else {
			$w1 = sprintf($lang['emajlogstatmark'], $_REQUEST['rangeend']);
			$stats = $emajdb->getDetailedLogStatGroup($_REQUEST['group'],$_REQUEST['rangestart'],$_REQUEST['rangeend']);
		}
		$summary = $emajdb->getDetailedLogStatSummary();

		$roles = $emajdb->getDetailedLogStatRoles();
		$roleList = '';
		while (!$roles->EOF) {
			if ($roleList == '') {
				$roleList = $roles->fields['stat_role'];
			} else {
				$roleList .= ', '.$roles->fields['stat_role'];
			}
			$roles->moveNext();
		}

		// Title
		echo "<hr/>\n";
		$misc->printTitle(sprintf($lang['emajlogstattittle'], htmlspecialchars($_REQUEST['rangestart']), htmlspecialchars($w1), htmlspecialchars($_REQUEST['group'])));

		// Display summary statistics
		echo "<div style=\"margin-bottom:15px\">\n";
		echo "<table class=\"data\"><tr>\n";
		echo "<th class=\"data\">{$lang['emajnbtbl']}</th>";
		echo "<th class=\"data\">{$lang['emajnbchanges']}</th>";
		echo "<th class=\"data\">{$lang['emajnbinsert']}</th>";
		echo "<th class=\"data\">{$lang['emajnbupdate']}</th>";
		echo "<th class=\"data\">{$lang['emajnbdelete']}</th>";
		echo "<th class=\"data\">{$lang['emajnbtruncate']}</th>";
		echo "<th class=\"data\">{$lang['emajnbrole']}</th>";
		echo "<th class=\"data\">{$lang['strroles']}</th>";
		echo "</tr><tr class=\"data1\">\n";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_tables']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['sum_rows']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_ins']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_upd']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_del']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_tru']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$summary->fields['nb_roles']}</div></td>";
		echo "<td><div style=\"text-align: center\">{$roleList}</div></td>";
		echo "</tr></table>\n";
		echo "</div>\n";

		// Display rollback duration estimate if requested
		if ($rlbkDuration != '') {
			if ($rlbkDuration == '-') {
				// the start mark cannot be used for a rollback
				echo "<p>{$lang['emajnosimrlbkduration']}</p>";
			} else {
				// dispay the duration estimate
				echo "<p>",sprintf($lang['emajsimrlbkduration'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['rangestart']),$rlbkDuration),"</p>\n";
			}
		}

		if ($summary->fields['nb_tables'] > 0) {

			// Display per table statistics
			$urlvars = $misc->getRequestVars();

			$columns = array(
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('stat_schema'),
					'url'   => "redirect.php?subject=schema&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema'),
				),
				'table' => array(
					'title' => $lang['strtable'],
					'field' => field('stat_table'),
					'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'stat_schema', 'table' => 'stat_table'),
				));
			if ($emajdb->getNumEmajVersion() >= 20300) {			// version >= 2.3.0
				$columns = array_merge($columns, array(
					'stat_first_mark' => array(
						'title' => $lang['emajstatfirstmark'],
						'field' => field('stat_first_mark'),
					),
					'stat_first_mark_datetime' => array(
						'title' => $lang['emajstatfirstmarkdatetime'],
						'field' => field('stat_first_mark_datetime'),
					),
					'stat_last_mark' => array(
						'title' => $lang['emajstatlastmark'],
						'field' => field('stat_last_mark'),
					),
					'stat_last_mark_datetime' => array(
						'title' => $lang['emajstatlastmarkdatetime'],
						'field' => field('stat_last_mark_datetime'),
					),
				));
			}
			$columns = array_merge($columns, array(
				'role' => array(
					'title' => $lang['strrole'],
					'field' => field('stat_role'),
				),
				'statement' => array(
					'title' => $lang['emajstatverb'],
					'field' => field('stat_verb'),
				),
				'nbrow' => array(
					'title' => $lang['emajstatrows'],
					'field' => field('stat_rows'),
					'type'  => 'numeric'
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			));

			$actions = array(
				'sql' => array(
					'content' => $lang['emajbrowsechanges'],
					'icon' => 'Eye',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'call_sqledit',
								'subject' => 'table',
								'sqlquery' => field('sql_text'),
								'paginate' => 'true',
					)))),
				),
			);

			$misc->printTable($stats, $columns, $actions, 'detailedLogStats', null, null, array('sorter' => true, 'filter' => true));

			// dynamicaly change the behaviour of the SQL link using JQuery code: open a new window
			// the link may be either a text button with a SQL content (td of type textbutton) or an icon (td of type iconbutton)
			$sql_window_id = htmlentities('emaj_sqledit:'.$_REQUEST['server']);
			echo "<script>
			$(\"#detailedLogStats\").find(\"td.textbutton a:contains('SQL'), td.iconbutton a\").click(function() {
				window.open($(this).attr('href'),'{$sql_window_id}','toolbar=no,width=700,height=400,resizable=yes,scrollbars=yes').focus();
				return false;
			});
			</script>\n";
		}
	}

	/**
	 * Displays the list of tables and sequences that composes a group
	 */
	function show_content_group() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('emaj', 'emajgroup', 'emajcontent');

		$misc->printTitle(sprintf($lang['emajgroupcontent'],htmlspecialchars($_REQUEST['group'])));

		$groupContent = $emajdb->getContentGroup($_REQUEST['group']);

		if ($groupContent->recordCount() < 1) {

			// The group is empty
			echo "<p>" . sprintf($lang['emajemptygroup'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";

		} else {

			$columns = array(
				'type' => array(
					'title' => $lang['strtype'],
					'field' => field('relkind'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderTblSeq','align' => 'center'),
					'sorter_text_extraction' => 'img_alt',
					'filter'=> false,
				),
				'schema' => array(
					'title' => $lang['strschema'],
					'field' => field('rel_schema'),
					'url'   => "schemas.php?action=list_schemas&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'rel_schema'),
				),
				'tblseq' => array(
					'title' => $lang['strname'],
					'field' => field('rel_tblseq'),
					'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
					'vars'  => array('schema' => 'rel_schema', 'table' => 'rel_tblseq'),
				),
				'priority' => array(
					'title' => $lang['emajpriority'],
					'field' => field('rel_priority'),
					'params'=> array('align' => 'center'),
				),
				'log_dat_tsp' => array(
					'title' => $lang['emajlogdattsp'],
					'field' => field('rel_log_dat_tsp'),
				),
				'log_idx_tsp' => array(
					'title' => $lang['emajlogidxtsp'],
					'field' => field('rel_log_idx_tsp'),
				),
				'log_table' => array(
					'title' => $lang['emajlogtable'],
					'field' => field('full_log_table'),
				),
				'bytelogsize' => array(
					'title' => $lang['emajlogsize'],
					'field' => field('byte_log_size'),
					'params'=> array('align' => 'right'),
					'filter'=> false,
				),
				'prettylogsize' => array(
					'title' => $lang['emajlogsize'],
					'field' => field('pretty_log_size'),
					'params'=> array('align' => 'center'),
					'sorter'=> false,
				),
			);

			$actions = array ();

			echo "<p></p>";
			$misc->printTable($groupContent, $columns, $actions, 'groupContent', null, null, array('sorter' => true, 'filter' => true));
		}
	}

/********************************************************************************************************
 * Functions preparing or performing actions
 *******************************************************************************************************/

	/**
	 * Prepare create group: ask for confirmation
	 */
	function create_group() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajcreateagroup']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['emajgroup']}</div>\n";
		echo "\t<div class=\"form-input\"><input type=\"text\" id=\"group\" name=\"group\" size=\"32\" required pattern=\"\S+.*\" value=\"\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\"></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";

		echo "<p>{$lang['emajgrouptype']} : \n";
		echo "\t<input type=\"radio\" name=\"grouptype\" value=\"rollbackable\" checked>{$lang['emajrollbackable']}\n";
		echo "\t<input type=\"radio\" name=\"grouptype\" value=\"auditonly\">{$lang['emajauditonly']}\n";
		echo "</p>\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";

		echo "<p><input type=\"submit\" name=\"creategroup\" value=\"{$lang['strcreate']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate/></p>\n";
		echo $misc->form;
		echo "</form>\n";
	}

	/**
	 * Perform create_group
	 */
	function create_group_ok() {
		global $lang, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

		// if the group is supposed to be empty, check the supplied group name doesn't exist
			if (!$emajdb->isNewEmptyGroupValid($_POST['group'])) {
				show_groups('',sprintf($lang['emajinvalidemptygroup'], htmlspecialchars($_POST['group'])));
				return;
			}

			$status = $emajdb->createGroup($_POST['group'],$_POST['grouptype']=='rollbackable',true,$_POST['comment']);
			if ($status == 0) {
				$_reload_browser = true;
				show_groups(sprintf($lang['emajcreategroupok'], htmlspecialchars($_POST['group'])));
			} else
				show_groups('',sprintf($lang['emajcreategrouperr'], htmlspecialchars($_POST['group'])));
		}
	}

	/**
	 * Prepare create group for groups configured in emaj_group_def: ask for confirmation
	 */
	function create_configured_group() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajcreateagroup']);

		$rollbackable = true; $auditonly = true;

		if ($emajdb->getNumEmajVersion() >= 30000) {			// version >= 3.0.0
		// check the group configuration
			$checks = $emajdb->checkConfNewGroup($_REQUEST['group']);
			if ($checks->recordCount() == 0) {
				echo "<p>" . sprintf($lang['emajgroupconfok'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";
			} else {
				echo "<p>" . sprintf($lang['emajgroupconfwithdiag'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";

				$columns = array(
					'message' => array(
						'title' => $lang['emajdiagnostics'],
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
		} else {
			echo "<p>" . sprintf($lang['emajconfirmcreategroup'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";
		}

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create_configured_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		if ($auditonly) {
			echo "<p>{$lang['emajgrouptype']} : \n";
			if ($rollbackable) {$attr = "checked";} else {$attr = "disabled";}
			echo "\t<input type=\"radio\" name=\"grouptype\" value=\"rollbackable\" {$attr}>{$lang['emajrollbackable']}\n";
			if ($rollbackable) {$attr = "";} else {$attr = "checked";}
			echo "\t<input type=\"radio\" name=\"grouptype\" value=\"auditonly\" {$attr}>{$lang['emajauditonly']}\n";
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
			show_groups();
		} else {

			$status = $emajdb->createGroup($_POST['group'],$_POST['grouptype']=='rollbackable',false,'');
			if ($status == 0) {
				$_reload_browser = true;
				show_groups(sprintf($lang['emajcreategroupok'], htmlspecialchars($_POST['group'])));
			} else
				show_groups('',sprintf($lang['emajcreategrouperr'], htmlspecialchars($_POST['group'])));
		}
	}

	/**
	 * Prepare drop group: ask for confirmation
	 */
	function drop_group() {
		global $misc, $lang;

		if ($_REQUEST['back']=='list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajdropagroup']);

		echo "<p>", sprintf($lang['emajconfirmdropgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"drop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"dropgroup\" value=\"{$lang['strdrop']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform drop group
	 */
	function drop_group_ok() {
		global $lang, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_groups();
			} else {
				show_group();
			}
		} else {

		// Check the group is always in IDLE state
			$group = $emajdb->getGroup($_REQUEST['group']);
			if ($group->fields['group_state'] != 'IDLE') {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcantdropgroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcantdropgroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}

		// OK
			$status = $emajdb->dropGroup($_POST['group']);
			if ($status == 0) {
				$_reload_browser = true;
				show_groups(sprintf($lang['emajdropgroupok'], htmlspecialchars($_POST['group'])));
			} else
				show_groups('',sprintf($lang['emajdropgrouperr'], htmlspecialchars($_POST['group'])));
		}
	}

	/**
	 * Prepare drop groups: ask for confirmation
	 */
	function drop_groups() {
		global $misc, $lang;

		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajdropgroups']);

		// build the groups list
		$groupsList = '';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
		}
		$groupsList = substr($groupsList,0,strlen($groupsList)-2);

		echo "<p>", sprintf($lang['emajconfirmdropgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"drop_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"dropgroups\" value=\"{$lang['strdrop']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform drop groups
	 */
	function drop_groups_ok() {
		global $lang, $emajdb, $_reload_browser;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

		// Check that all groups are always in IDLE state
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				$group = $emajdb->getGroup($g);
				// exit the loop in case of error
				if ($group->fields['group_state'] != 'IDLE') {
					show_groups('',sprintf($lang['emajcantdropgroups'], htmlspecialchars($_POST['groups'])));
					exit();
				}
			}

		// OK
			$status = $emajdb->dropGroups($_POST['groups']);
			if ($status == 0) {
				$_reload_browser = true;
				show_groups(sprintf($lang['emajdropgroupsok'], htmlspecialchars($_POST['groups'])));
			} else
				show_groups('',sprintf($lang['emajdropgroupserr'], htmlspecialchars($_POST['groups'])));
		}
	}

	/**
	 * Prepare alter group: ask for confirmation
	 */
	function alter_group() {
		global $misc, $lang, $emajdb;

		if ($_REQUEST['back']=='list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');
		$misc->printTitle($lang['emajaltergroups']);

		$confOK = true;
		if ($emajdb->getNumEmajVersion() >= 30000) {			// version >= 3.0.0
		// check the group configuration
			$checks = $emajdb->checkConfExistingGroups($_REQUEST['group']);
			if ($checks->recordCount() == 0) {
				echo "<p>" . sprintf($lang['emajgroupconfok'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";
			} else {
				$confOK = false;
				echo "<p>" . sprintf($lang['emajgroupconfwithdiag'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";

				$columns = array(
					'message' => array(
						'title' => $lang['emajdiagnostics'],
						'field' => field('chk_message'),
					),
				);

				$actions = array ();

				$misc->printTable($checks, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

				echo "<form action=\"emajgroups.php\" method=\"post\">\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_group_ok\" />\n";
				echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
				echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
				echo $misc->form;
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";
			}
		}

		if ($confOK) {
			$isGroupLogging = $emajdb->isGroupLogging($_REQUEST['group']);

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";

			if ($emajdb->getNumEmajVersion() >= 20100) {			// version >= 2.1.0
				if ($isGroupLogging) {
					echo "<p>", sprintf($lang['emajalteraloggingroup'], htmlspecialchars($_REQUEST['group'])), "</p>";
					echo "<div class=\"form-container\">\n";
					echo "\t<div class=\"form-label required\">{$lang['emajmark']}</div>\n";
					echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"ALTER_%\" id=\"mark\"></div>\n";
					echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";
					echo "</div>\n";
				} else {
					echo "<p>", sprintf($lang['emajconfirmaltergroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
				}
			} else {
					echo "<p>", sprintf($lang['emajconfirmaltergroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
					echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
			}

			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_group_ok\" />\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
			echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"altergroup\" value=\"{$lang['emajApplyConfChanges']}\" />\n";
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
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_groups();
			} else {
				show_group();
			}
		} else {

		// check the group can be altered by looking at its state and operations that will be performed
			$check = $emajdb->checkAlterGroup($_REQUEST['group']);
			if ($check == 0) {
				if ($_POST['back'] == 'list') {
					show_groups('',sprintf($lang['emajcantaltergroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcantaltergroup'], htmlspecialchars($_POST['group'])));
				}
				exit();
			}

		// Check the supplied mark is valid
			if ($_POST['mark'] != '') {
				$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'], htmlspecialchars($_POST['mark']));
				if (is_null($finalMarkName)) {
					if ($_POST['back']=='list') {
						show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
					} else {
						show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
					}
					return;
				}
			} else {
				$finalMarkName = '';
			}

		// OK
			$status = $emajdb->alterGroup($_POST['group'],$finalMarkName);
			if ($status == 0) {
				$_reload_browser = true;
				if ($_POST['back'] == 'list') {
					show_groups(sprintf($lang['emajaltergroupok'], htmlspecialchars($_POST['group'])));
				} else {
					show_group(sprintf($lang['emajaltergroupok'], htmlspecialchars($_POST['group'])));
				}
			} else
				if ($_POST['back'] == 'list') {
					show_groups('',sprintf($lang['emajaltergrouperr'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajaltergrouperr'], htmlspecialchars($_POST['group'])));
				}
		}
	}

	/**
	 * Prepare alter groups: ask for confirmation
	 */
	function alter_groups() {
		global $misc, $lang, $emajdb;

		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		$misc->printHeader('database', 'database','emajgroups');
		$misc->printTitle($lang['emajaltergroups']);

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

		$confOK = true;
		if ($emajdb->getNumEmajVersion() >= 30000) {			// version >= 3.0.0
		// check the groups configuration
			$checks = $emajdb->checkConfExistingGroups($groupsList);
			if ($checks->recordCount() == 0) {
				echo "<p>" . sprintf($lang['emajgroupsconfok'], htmlspecialchars($groupsList)) . "</p>\n";
			} else {
				$confOK = false;
				echo "<p>" . sprintf($lang['emajgroupsconfwithdiag'], htmlspecialchars($groupsList)) . "</p>\n";

				$columns = array(
					'message' => array(
						'title' => $lang['emajdiagnostics'],
						'field' => field('chk_message'),
					),
				);

				$actions = array ();

				$misc->printTable($checks, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

				echo "<form action=\"emajgroups.php\" method=\"post\">\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_groups_ok\" />\n";
				echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
				echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
				echo $misc->form;
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
				echo "</form>\n";
			}
		}

		if ($confOK) {

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";

			if ($emajdb->getNumEmajVersion() >= 20100) {			// version >= 2.1.0
				if ($anyGroupLogging) {
					echo "<p>", sprintf($lang['emajalterallloggingroups'], htmlspecialchars($groupsList)), "</p>";
					echo "<div class=\"form-container\">\n";
					echo "\t<div class=\"form-label required\">{$lang['emajmark']}</div>\n";
					echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"ALTER_%\" id=\"mark\"></div>\n";
					echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamemultihelp']}\"/></div>\n";
					echo "</div>\n";
				} else {
					echo "<p>", sprintf($lang['emajconfirmaltergroups'], htmlspecialchars($groupsList)), "</p>\n";
					echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
				}
			} else {
				echo "<p>", sprintf($lang['emajconfirmaltergroups'], htmlspecialchars($groupsList)), "</p>\n";
				echo "<input type=\"hidden\" name=\"mark\" value=\"\">";
			}

			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_groups_ok\" />\n";
			echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
			echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"altergroups\" value=\"{$lang['emajApplyConfChanges']}\" />\n";
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
			show_groups();
		} else {

		// check the groups can be altered by looking at their state and operations that will be performed
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				$check = $emajdb->checkAlterGroup($g);
				// exit the loop in case of error
				if ($check == 0) {
					if ($_POST['back'] == 'list') {
						show_groups('',sprintf($lang['emajcantaltergroup'], htmlspecialchars($g)));
					} else {
						show_group('',sprintf($lang['emajcantaltergroup'], htmlspecialchars($g)));
					}
					exit();
				}
			}
		// Check the supplied mark is valid for the groups
			if ($_POST['mark'] != '') {
				$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark']);
				if (is_null($finalMarkName)) {
					show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
					return;
				}
			} else {
				$finalMarkName = '';
			}

		// OK
			$status = $emajdb->alterGroups($_POST['groups'],$finalMarkName);
			if ($status == 0) {
				$_reload_browser = true;
				if ($_POST['back'] == 'list') {
					show_groups(sprintf($lang['emajaltergroupsok'], htmlspecialchars($_POST['groups'])));
				} else {
					show_group(sprintf($lang['emajaltergroupsok'], htmlspecialchars($_POST['groups'])));
				}
			}else
				if ($_POST['back'] == 'list') {
					show_groups('',sprintf($lang['emajaltergroupserr'], htmlspecialchars($_POST['groups'])));
				} else {
					show_group('',sprintf($lang['emajaltergroupserr'], htmlspecialchars($_POST['groups'])));
				}
		}
	}

	/**
	 * Prepare comment group: ask for comment and confirmation
	 */
	function comment_group() {
		global $misc, $lang, $emajdb;

		if ($_REQUEST['back']=='list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajcommentagroup']);

		$group = $emajdb->getGroup($_REQUEST['group']);

		echo "<p>", sprintf($lang['emajcommentgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($group->fields['group_comment']), "\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"comment_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"commentgroup\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform comment group
	 */
	function comment_group_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_groups();
			} else {
				show_group();
			}
		} else {

			$status = $emajdb->setCommentGroup($_POST['group'],$_POST['comment']);
			if ($status == 0)
				if ($_POST['back']=='list') {
					show_groups(sprintf($lang['emajcommentgroupok'], htmlspecialchars($_POST['group'])));
				} else {
					show_group(sprintf($lang['emajcommentgroupok'], htmlspecialchars($_POST['group'])));
				}
			else
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcommentgrouperr'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcommentgrouperr'], htmlspecialchars($_POST['group'])));
				}
		}
	}

	/**
	 * Prepare export group: select the groups to export and confirm
	 */
	function export_groups() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajexportgroupsconf']);

		echo "<p>{$lang['emajexportgroupsconfselect']}</p>";

		$groups = $emajdb->getGroups();

		$columns = array(
			'group' => array(
				'title' => $lang['emajgroup'],
				'field' => field('group_name'),
			),
			'state' => array(
				'title' => $lang['emajstate'],
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
				'title' => $lang['emajnbtbl'],
				'field' => field('group_nb_table'),
				'type'  => 'numeric'
			),
			'nbseq' => array(
				'title' => $lang['emajnbseq'],
				'field' => field('group_nb_sequence'),
				'type'  => 'numeric'
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('group_comment'),
				'type'	=> 'callback',
				'params'=> array(
						'function' => 'renderCommentInTooltip',
						'cliplen' => 20,
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
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'export_groups_ok',
				),
			));
		};

		$misc->printTable($groups, $columns, $actions, 'groups', '', null, array('sorter' => true, 'filter' => true));

		echo "<p></p>";

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strback']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Export a tables groups configuration
	 */
	function export_groups_ok() {

		global $misc, $emajdb;

	// Build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);

	// Build the JSON parameter configuration
		$groupsConfig = $emajdb->exportGroupsConfig($groupsList);

	// Generate a suggested local file name
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

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajimportgroupsconf']);

		// form to import a tables groups configuration
		echo "<div>\n";
		echo "\t<form name=\"importgroups\" id=\"importgroups\" enctype=\"multipart/form-data\" method=\"POST\"";
		echo " action=\"emajgroups.php?action=import_groups_select&amp;{$misc->href}\">\n";
		echo "\t\t<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"10000000\">\n";
		echo "\t\t<label for=\"file-upload\" class=\"custom-file-upload\">${lang['emajselectfile']}</label>";
		echo "\t\t<p><input type=\"file\" id=\"file-upload\" name=\"file_name\"></p>\n";
		echo "\t\t<p><input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />&nbsp;&nbsp;&nbsp;\n";
		echo "\t\t<input type=\"submit\" name=\"openfile\" value=\"${lang['stropen']}\" disabled>";
		echo "\t\t<span id=\"selected-file\"></span></p>\n";
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

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

		// Process the uploaded file

			// If the file is properly loaded,
			if (is_uploaded_file($_FILES['file_name']['tmp_name'])) {
				$jsonContent = file_get_contents($_FILES['file_name']['tmp_name']);
				$jsonStructure = json_decode($jsonContent, true);
			// ... and contains a valid JSON structure,
				if (json_last_error()===JSON_ERROR_NONE) {

					$misc->printHeader('database', 'database','emajgroups');

					$misc->printTitle($lang['emajimportgroupsconf']);

					// check that the json content is valid

					$errors = $emajdb->checkJsonGroupsConf($jsonContent);

					if ($errors->recordCount() == 0) {
						// No error has been detected in the json structure, so display the tables groups to select

						echo "<p>" . sprintf($lang['emajimportgroupsinfile'], $_FILES['file_name']['name']) . "</p>";

						// Extract the list of configured tables groups
						$groupsList='';
						foreach($jsonStructure["tables_groups"] as $jsonGroup){
							if (isSet($jsonGroup["group"])) {
								$groupsList .= "('" . htmlspecialchars_decode($jsonGroup["group"], ENT_QUOTES) . "'), ";
							}
						}
						$groupsList=substr($groupsList,0,strlen($groupsList)-2);

						// Get data about existing groups
						$groups = $emajdb->getGroupsToImport($groupsList);

						// Display the groups list
						$columns = array(
							'group' => array(
								'title' => $lang['emajgroup'],
								'field' => field('grp_name'),
							),
							'state' => array(
								'title' => $lang['emajstate'],
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
								'title' => $lang['emajnbtbl'],
								'field' => field('group_nb_table'),
								'type'  => 'numeric'
							),
							'nbseq' => array(
								'title' => $lang['emajnbseq'],
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
										'back' => 'list',
										'file' => $_FILES['file_name']['name'],
										'json' => json_encode($jsonStructure),
									),
									'checked' => true,								// all groups are selected by default
								),
								'import_group' => array(
									'content' => $lang['strimport'],
									'multiaction' => 'import_groups_ok',
								),
							));
						};

						$misc->printTable($groups, $columns, $actions, 'groups', '', null, array('sorter' => true, 'filter' => true));

						echo "<p></p>";

						echo "<form action=\"emajgroups.php\" method=\"post\">\n";
						echo $misc->form;
						echo "<input type=\"hidden\" name=\"json\" value=\"" . htmlspecialchars(json_encode($jsonStructure)) . "\">\n";
						echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
						echo "</form>\n";

					} else {
					// The json structure contains errors. Display them.

						echo "<p>" . sprintf($lang['emajimportgroupsinfileerr'], $_FILES['file_name']['name']) . "</p>";

						$columns = array(
							'severity' => array(
								'title' => '',
								'field' => field('rpt_severity'),
								'type'	=> 'callback',
								'params'=> array('function' => 'renderMsgSeverity','align' => 'center'),
								'sorter' => false,
							),
							'message' => array(
								'title' => $lang['emajdiagnostics'],
								'field' => field('rpt_message'),
							),
						);

						$actions = array ();

						$misc->printTable($errors, $columns, $actions, 'checks', null, null, array('sorter' => true, 'filter' => false));

						echo "<form action=\"emajgroups.php\" method=\"post\">\n";
						echo "<p><input type=\"hidden\" name=\"action\" value=\"import_groups_ok\" />\n";
						echo $misc->form;
						echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strok']}\" /></p>\n";
						echo "</form>\n";

					}
				} else {
					show_groups('', sprintf($lang['emajnotjsonfile'], $_FILES['file_name']['name']));
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
				show_groups('', $errMsg);
			}
		}
	}

	/**
	 * Effectively import a tables groups configuration
	 */
	function import_groups_ok() {

		global $lang, $emajdb, $misc, $_reload_browser;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

			// build the groups list
			$groupsList='';
			foreach($_REQUEST['ma'] as $v) {
				$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
				$groupsList .= $a['group'] . ', ';
			}
			$groupsList = substr($groupsList,0,strlen($groupsList)-2);

			// prepare the tables groups configuration import
			$errors = $emajdb->importGroupsConfPrepare($_POST['json'], $groupsList);

			if ($errors->recordCount() == 0) {
				// no error detected, so execute the effective configuration import
				$nbGroup = $emajdb->importGroupsConfig($_POST['json'], $groupsList);
				if ($nbGroup >= 0) {
					$_reload_browser = true;
					show_groups(sprintf($lang['emajgroupsconfimported'], $nbGroup, $_POST['file']));
				} else {
					show_groups('', sprintf($lang['emajgroupsconfimporterr'], $_POST['file']));
				}
			} else {
				// there are errors to report to the user

				$misc->printHeader('database', 'database','emajgroups');

				$misc->printTitle($lang['emajimportgroupsconf']);

				echo "<p>" . sprintf($lang['emajgroupsconfimportpreperr'], htmlspecialchars($groupsList), htmlspecialchars($_POST['file'])) . "</p>\n";

				$columns = array(
					'severity' => array(
						'title' => '',
						'field' => field('rpt_severity'),
						'type'	=> 'callback',
						'params'=> array('function' => 'renderMsgSeverity','align' => 'center'),
						'sorter' => false,
					),
					'message' => array(
						'title' => $lang['emajdiagnostics'],
						'field' => field('rpt_message'),
					),
				);

				$actions = array ();

				$misc->printTable($errors, $columns, $actions, 'errors', null, null, array('sorter' => true, 'filter' => false));

				echo "<form action=\"emajgroups.php\" method=\"post\">\n";
				echo "<p><input type=\"hidden\" name=\"action\" value=\"import_group_ok\" />\n";
				echo $misc->form;
				echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strok']}\" /></p>\n";
				echo "</form>\n";
			}
		}
	}

	/**
	 * Prepare start group: enter the initial mark name and confirm
	 */
	function start_group() {
		global $misc, $lang;

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		if ($_REQUEST['back']=='list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajstartagroup']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p>", sprintf($lang['emajconfirmstartgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['emajinitmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\"/></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$lang['emajoldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"start_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strstart']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate /></p>\n";
		echo "</form>\n";

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

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_groups();
			} else {
				show_group();
			}
		} else {

			// Check the group is always in IDLE state
			$group = $emajdb->getGroup($_REQUEST['group']);
			if ($group->fields['group_state'] != 'IDLE') {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcantstartgroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcantstartgroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}
			// check the supplied mark is valid for the group
			$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'],$_POST['mark']);
			if (is_null($finalMarkName)) {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				} else {
					show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				}
				return;
			}
			// OK
			$status = $emajdb->startGroup($_POST['group'],$finalMarkName,isSet($_POST['resetlog']));
			if ($status == 0)
				if ($_POST['back']=='list') {
					show_groups(sprintf($lang['emajstartgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
				} else {
					show_group(sprintf($lang['emajstartgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
				}
			else
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajstartgrouperr'], htmlspecialchars($finalMarkName)));
				} else {
					show_group('',sprintf($lang['emajstartgrouperr'], htmlspecialchars($finalMarkName)));
				}
		}
	}

	/**
	 * Prepare start groups: enter the initial mark name and confirm
	 */
	function start_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			start_group();
			return;
		}

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$misc->printHeader('database', 'database','emajgroups');
		$misc->printTitle($lang['emajstartgroups']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		// send form
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p>", sprintf($lang['emajconfirmstartgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['emajinitmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamemultihelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$lang['emajoldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"start_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strstart']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate /></p>\n";
		echo "</form>\n";

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

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

			// Check the groups are always in IDLE state
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				if ($emajdb->getGroup($g)->fields['group_state'] != 'IDLE') {
					show_groups('',sprintf($lang['emajcantstartgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($g)));
					return;
				}
			}
			// check the supplied mark is valid for the groups
			$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark']);
			if (is_null($finalMarkName)) {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				} else {
					show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				}
				return;
			}
			// OK
			$status = $emajdb->startGroups($_POST['groups'],$finalMarkName,isSet($_POST['resetlog']));
			if ($status == 0)
				if ($_POST['back']=='list')
					show_groups(sprintf($lang['emajstartgroupsok'], htmlspecialchars($_POST['groups']), htmlspecialchars($finalMarkName)));
			else
				if ($_POST['back']=='list')
					show_groups('',sprintf($lang['emajstartgroupserr'], htmlspecialchars($finalMarkName)));
		}
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_group() {
		global $misc, $lang;

		if ($_REQUEST['back']=='list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajstopagroup']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p>", sprintf($lang['emajconfirmstopgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['emajstopmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=checkbox name=\"forcestop\" />{$lang['emajforcestop']}</p>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"stop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"stopgroup\" value=\"{$lang['strstop']}\"/>\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform stop_group
	 */
	function stop_group_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_groups();
			} else {
				show_group();
			}
		} else {

			// Check the group is always in LOGGING state
			$group = $emajdb->getGroup($_REQUEST['group']);
			if ($group->fields['group_state'] != 'LOGGING') {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcantstopgroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcantstopgroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}
			// OK
			$status = $emajdb->stopGroup($_POST['group'],$_POST['mark'],isSet($_POST['forcestop']));
			if ($status == 0)
				if ($_POST['back']=='list') {
					show_groups(sprintf($lang['emajstopgroupok'], htmlspecialchars($_POST['group'])));
				} else {
					show_group(sprintf($lang['emajstopgroupok'], htmlspecialchars($_POST['group'])));
				}
			else
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajstopgrouperr'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajstopgrouperr'], htmlspecialchars($_POST['group'])));
				}
		}
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			stop_group();
			return;
		}

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajstopgroups']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		// send form
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p>", sprintf($lang['emajconfirmstopgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['emajstopmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamemultihelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"stop_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"stopgroups\" value=\"{$lang['strstop']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform stop_groups
	 */
	function stop_groups_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

			// Check the groups are always in LOGGING state
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				if ($emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
					show_groups('',sprintf($lang['emajcantstopgroups'], htmlspecialchars($_POST['groups']),$g));
					return;
				}
			}
			// OK
			$status = $emajdb->stopGroups($_POST['groups'],$_POST['mark']);
			if ($status == 0)
				show_groups(sprintf($lang['emajstopgroupsok'], htmlspecialchars($_POST['groups'])));
			else
				show_groups('',sprintf($lang['emajstopgroupserr'], htmlspecialchars($_POST['groups'])));
		}
	}

	/**
	 * Prepare reset group: ask for confirmation
	 */
	function reset_group() {
		global $misc, $lang;

		if ($_REQUEST['back']=='list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajresetagroup']);

		echo "<p>", sprintf($lang['emajconfirmresetgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"reset_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"resetgroup\" value=\"{$lang['strreset']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform reset group
	 */
	function reset_group_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_groups();
			} else {
				show_group();
			}
		} else {

			// Check the group is always in IDLE state
			$group = $emajdb->getGroup($_POST['group']);
			if ($group->fields['group_state'] != 'IDLE') {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcantresetgroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcantresetgroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}
			// OK
			$status = $emajdb->resetGroup($_POST['group']);
			if ($status == 0)
				if ($_POST['back']=='list') {
					show_groups(sprintf($lang['emajresetgroupok'], htmlspecialchars($_POST['group'])));
				} else {
					show_group(sprintf($lang['emajresetgroupok'], htmlspecialchars($_POST['group'])));
				}
			else
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajresetgrouperr'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajresetgrouperr'], htmlspecialchars($_POST['group'])));
				}
		}
	}

	/**
	 * Prepare reset groups: ask for confirmation
	 */
	function reset_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			reset_group();
			return;
		}

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajresetgroups']);

		// build the groups list
		$groupsList = '';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
		}
		$groupsList = substr($groupsList,0,strlen($groupsList)-2);

		echo "<p>", sprintf($lang['emajconfirmresetgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"reset_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"resetgroups\" value=\"{$lang['strreset']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform reset groups
	 */
	function reset_groups_ok() {
		global $lang, $emajdb;

	// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

		// Check that all groups are always in IDLE state
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				$group = $emajdb->getGroup($g);
				// exit the loop in case of error
				if ($group->fields['group_state'] != 'IDLE') {
					show_groups('',sprintf($lang['emajcantresetgroups'], htmlspecialchars($_POST['groups'])));
					exit();
				}
			}

		// OK
			$status = $emajdb->resetGroups($_POST['groups']);
			if ($status == 0) {
				show_groups(sprintf($lang['emajresetgroupsok'], htmlspecialchars($_POST['groups'])));
			} else
				show_groups('',sprintf($lang['emajresetgroupserr'], htmlspecialchars($_POST['groups'])));
		}
	}

	/**
	 * Execute protect group (there is no confirmation to ask)
	 */
	function protect_group() {
		global $lang, $emajdb;

		// Check the group is always in LOGGING state
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				show_groups('',sprintf($lang['emajcantprotectgroup'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group('',sprintf($lang['emajcantprotectgroup'], htmlspecialchars($_REQUEST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->protectGroup($_REQUEST['group']);
		if ($status == 0)
			if ($_REQUEST['back']=='list') {
				show_groups(sprintf($lang['emajprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group(sprintf($lang['emajprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back']=='list') {
				show_groups('',sprintf($lang['emajprotectgrouperr'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group('',sprintf($lang['emajprotectgrouperr'], htmlspecialchars($_REQUEST['group'])));
			}
	}

	/**
	 * Execute unprotect group (there is no confirmation to ask)
	 */
	function unprotect_group() {
		global $lang, $emajdb;

		// Check the group is always in LOGGING state
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				show_groups('',sprintf($lang['emajcantunprotectgroup'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group('',sprintf($lang['emajcantunprotectgroup'], htmlspecialchars($_REQUEST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->unprotectGroup($_REQUEST['group']);
		if ($status == 0)
			if ($_REQUEST['back']=='list') {
				show_groups(sprintf($lang['emajunprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group(sprintf($lang['emajunprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back']=='list') {
				show_groups('',sprintf($lang['emajunprotectgrouperr'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group('',sprintf($lang['emajunprotectgrouperr'], htmlspecialchars($_REQUEST['group'])));
			}
	}

	/**
	 * Prepare set mark group: ask for the mark name and confirmation
	 */
	function set_mark_group() {
		global $misc, $lang;

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		if ($_REQUEST['back']=='list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajsetamark']);
		echo "<p>", sprintf($lang['emajconfirmsetmarkgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['emajmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamehelp']}\"/></div>\n";
		echo "</div>\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";

		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"set_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\"/>\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate /></p>\n";
		echo "</form>\n";

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

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_groups();
			} else {
				show_group();
			}
		} else {

			// Check the group is always in LOGGING state
			$group = $emajdb->getGroup($_POST['group']);
			if ($group->fields['group_state'] != 'LOGGING') {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcantsetmarkgroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcantsetmarkgroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}
			// Check the supplied mark group is valid
			$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'],$_POST['mark']);
			if (is_null($finalMarkName)) {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				} else {
					show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				}
				return;
			}
			// OK
			$status = $emajdb->setMarkGroup($_POST['group'],$finalMarkName,$_POST['comment']);
			if ($status == 0)
				if ($_POST['back']=='list') {
					show_groups(sprintf($lang['emajsetmarkgroupok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
				} else {
					show_group(sprintf($lang['emajsetmarkgroupok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
				}
			else
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajsetmarkgrouperr'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajsetmarkgrouperr'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
				}
		}
	}

	/**
	 * Prepare set mark groups: ask for the mark name and confirmation
	 */
	function set_mark_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			set_mark_group();
			return;
		}

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajsetamark']);

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);
		echo "<p>", sprintf($lang['emajconfirmsetmarkgroups'], htmlspecialchars($groupsList)), "</p>\n";
		// send form
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['emajmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajmarknamemultihelp']}\"/></div>\n";
		echo "</div>\n";

		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";

		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"set_mark_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['strok']}\" id=\"ok\" disabled=\"disabled\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate /></p>\n";
		echo "</form>\n";

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

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

			// Check the groups are always in LOGGING state
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				if ($emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
					show_groups('',(sprintf($lang['emajcantsetmarkgroups'], htmlspecialchars($_POST['groups']),$g)));
					return;
				}
			}
			// Check the supplied mark group is valid
			$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark']);
			if (is_null($finalMarkName)) {
				show_groups('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['mark'])));
				return;
			}
			// OK
			$status = $emajdb->setMarkGroups($_POST['groups'],$finalMarkName,$_POST['comment']);
			if ($status == 0)
				show_groups(sprintf($lang['emajsetmarkgroupsok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['groups'])));
			else
				show_groups('',sprintf($lang['emajsetmarkgroupserr'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['groups'])));
		}
	}

	/**
	 * Execute protect mark (there is no confirmation to ask)
	 */
	function protect_mark_group() {
		global $lang, $emajdb;

		// Check the group is always in LOGGING state
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				show_groups('',sprintf($lang['emajcantprotectmarkgroup'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group('',sprintf($lang['emajcantprotectmarkgroup'], htmlspecialchars($_REQUEST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->protectMarkGroup($_REQUEST['group'],$_REQUEST['mark']);
		if ($status == 0)
			if ($_REQUEST['back']=='list') {
				show_groups(sprintf($lang['emajprotectmarkgroupok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group(sprintf($lang['emajprotectmarkgroupok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back']=='list') {
				show_groups('',sprintf($lang['emajprotectmarkgrouperr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group('',sprintf($lang['emajprotectmarkgrouperr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			}
	}

	/**
	 * Execute unprotect mark (there is no confirmation to ask)
	 */
	function unprotect_mark_group() {
		global $lang, $emajdb;

		// Check the group is always in LOGGING state
		$group = $emajdb->getGroup($_REQUEST['group']);
		if ($group->fields['group_state'] != 'LOGGING') {
			if ($_REQUEST['back']=='list') {
				show_groups('',sprintf($lang['emajcantunprotectmarkgroup'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group('',sprintf($lang['emajcantunprotectmarkgroup'], htmlspecialchars($_REQUEST['group'])));
			}
			return;
		}
		// OK
		$status = $emajdb->unprotectMarkGroup($_REQUEST['group'],$_REQUEST['mark']);
		if ($status == 0)
			if ($_REQUEST['back']=='list') {
				show_groups(sprintf($lang['emajunprotectmarkgroupok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group(sprintf($lang['emajunprotectmarkgroupok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back']=='list') {
				show_groups('',sprintf($lang['emajunprotectmarkgrouperr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group('',sprintf($lang['emajunprotectmarkgrouperr'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
			}
	}

	/**
	 * Prepare comment mark group: ask for comment and confirmation
	 */
	function comment_mark_group() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajcommentamark']);

		$mark = $emajdb->getMark($_REQUEST['group'],$_REQUEST['mark']);

		echo "<p>", sprintf($lang['emajcommentmark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($mark->fields['mark_comment']), "\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"comment_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"commentmarkgroup\" value=\"{$lang['strok']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform comment mark group
	 */
	function comment_mark_group_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_group();
		} else {

			$status = $emajdb->setCommentMarkGroup($_POST['group'],$_POST['mark'],$_POST['comment']);
			if ($status >= 0)
				show_group(sprintf($lang['emajcommentmarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
			else
				show_group('',sprintf($lang['emajcommentmarkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		}
	}

	/**
	 * Prepare rollback group: ask for confirmation
	 */
	function rollback_group() {
		global $misc, $lang, $emajdb, $conf;

		if ($_REQUEST['back']=='list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajrlbkagroup']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_group_confirm_alter\" />\n";

		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		if (isset($_REQUEST['mark'])) {
		// the mark name is already defined (we are coming from the 'detail group' page)
			echo "<p>", sprintf($lang['emajconfirmrlbkgroup'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['mark'])), "</p>\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		} else {
		// the mark name is not yet defined (we are coming from the 'list groups' page)
			$marks=$emajdb->getRollbackMarkGroup($_REQUEST['group']);
			echo sprintf($lang['emajselectmarkgroup'], htmlspecialchars($_REQUEST['group']));
			echo "<select name=\"mark\">\n";
			$optionDisabled = '';
			foreach($marks as $m) {
				echo "<option value=\"", htmlspecialchars($m['mark_name']), "\" $optionDisabled>", htmlspecialchars($m['mark_name']), " ({$m['mark_datetime']})</option>\n";
				// if the mark is protected against rollback, disabled the next ones
				if ($m['mark_is_rlbk_protected'] == 't') $optionDisabled = 'disabled';
			}
			echo "</select></p><p>\n";
		}
		echo $misc->form;
		echo "{$lang['emajrollbacktype']} : ";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"unlogged\" checked>{$lang['emajunlogged']}";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"logged\">{$lang['emajlogged']}\n";
		echo "</p><p>";
		echo "<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['emajrlbk']}\" />\n";
		if ($emajdb->isAsyncRlbkUsable($conf) ) {
			echo "<input type=\"submit\" name=\"async\" value=\"{$lang['emajrlbkthenmonitor']}\" />\n";
		}
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Ask the user to confirm a rollback targeting a mark set prior alter_group operations
	 */
	function rollback_group_confirm_alter() {
		global $lang, $misc, $emajdb;

		if ($emajdb->getNumEmajVersion() < 20100) {	// version < 2.1.0) {
			// for emaj version prior 2.1, directly call the function that executes the rollback
			rollback_group_ok();
		} else {
			// check that the rollback would not reach a mark set before any alter group operation

			// process the click on the <cancel> button
			if (isset($_POST['cancel'])) {
				if ($_POST['back'] == 'list') {
					show_groups();
				} else {
					show_group();
				}
			} else {

				// Check the group is always in LOGGING state and ROLLBACKABLE (i.e. not protected)
				$group = $emajdb->getGroup($_POST['group']);
				if ($group->fields['group_state'] != 'LOGGING') {
					if ($_POST['back']=='list') {
						show_groups('',sprintf($lang['emajcantrlbkidlegroup'], htmlspecialchars($_POST['group'])));
					} else {
						show_group('',sprintf($lang['emajcantrlbkidlegroup'], htmlspecialchars($_POST['group'])));
					}
					return;
				}
				if ($group->fields['group_type'] != 'ROLLBACKABLE') {
					if ($_POST['back']=='list') {
						show_groups('',sprintf($lang['emajcantrlbkprotgroup'], htmlspecialchars($_POST['group'])));
					} else {
						show_group('',sprintf($lang['emajcantrlbkprotgroup'], htmlspecialchars($_POST['group'])));
					}
					return;
				}
				// Check the mark is always valid for a rollback
				if (!$emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
					if ($_POST['back']=='list') {
						show_groups('',sprintf($lang['emajcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
					} else {
						show_group('',sprintf($lang['emajcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
					}
					return;
				}

				$alterGroupSteps = $emajdb->getAlterAfterMarkGroups($_POST['group'],$_POST['mark'],$lang);

				if ($alterGroupSteps->recordCount() > 0) {
					// there are alter_group operation to cross over, so ask for a confirmation

					$columns = array(
						'time' => array(
							'title' => $lang['emajtimestamp'],
							'field' => field('time_tx_timestamp'),
						),
						'step' => array(
							'title' => $lang['straction'],
							'field' => field('altr_action'),
						),
						'autorollback' => array(
							'title' => $lang['emajautorolledback'],
							'field' => field('altr_auto_rolled_back'),
							'type'	=> 'callback',
							'params'=> array('function' => 'renderBooleanIcon','align' => 'center')
						),
					);

					$actions = array ();

					if ($_REQUEST['back']=='list')
						$misc->printHeader('database', 'database','emajgroups');
					else
						$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

					$misc->printTitle($lang['emajrlbkagroup']);

					echo "<p>" . sprintf($lang['emajreachaltergroup'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['mark'])) . "</p>\n";

					echo "<div id=\"alterGroupStep\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
					$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');
					echo "</div>\n";

					echo "<form action=\"emajgroups.php\" method=\"post\">\n";
					echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_group_ok\" />\n";
					echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
					echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
					echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
					echo "<input type=\"hidden\" name=\"rollbacktype\"", htmlspecialchars($_REQUEST['rollbacktype']), "\" />\n";
					if (isset($_POST['async'])) {
						echo "<input type=\"hidden\" name=\"async\"", htmlspecialchars($_REQUEST['async']), "\" />\n";
					}
					echo $misc->form;
					echo "<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['strconfirm']}\" />\n";
					echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
					echo "</form>\n";

				} else {
					// otherwise, directly execute the rollback
					rollback_group_ok();
				}
			}
		}
	}

	/**
	 * Perform rollback_group (in synchronous mode)
	 */
	function rollback_group_ok() {
		global $lang, $misc, $emajdb, $conf;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			if ($_POST['back'] == 'list') {
				show_groups();
			} else {
				show_group();
			}
		} else {

			// Check the group is always in LOGGING state and ROLLBACKABLE (i.e. not protected)
			$group = $emajdb->getGroup($_POST['group']);
			if ($group->fields['group_state'] != 'LOGGING') {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcantrlbkidlegroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcantrlbkidlegroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}
			if ($group->fields['group_type'] != 'ROLLBACKABLE') {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcantrlbkprotgroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['emajcantrlbkprotgroup'], htmlspecialchars($_POST['group'])));
				}
				return;
			}
			// Check the mark is always valid for a rollback
			if (!$emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
				if ($_POST['back']=='list') {
					show_groups('',sprintf($lang['emajcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
				} else {
					show_group('',sprintf($lang['emajcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
				}
				return;
			}

			if (isset($_POST['async'])) {
			// perform the rollback in asynchronous mode and switch to the rollback monitoring page

				if (!$emajdb->isAsyncRlbkUsable(false)) {
					if ($_POST['back']=='list') {
						show_groups('',sprintf($lang['emajbadconfparam'], $conf['psql_path'], $conf['temp_dir']));
					} else {
						show_group('',sprintf($lang['emajbadconfparam'], $conf['psql_path'], $conf['temp_dir']));
					}
					exit;
				}

				// perform the rollback in asynchronous mode and switch to the rollback monitoring page
				$psqlExe = $misc->escapeShellCmd($conf['psql_path']);
				$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
				$rlbkId = $emajdb->asyncRollbackGroups($_POST['group'],$_POST['mark'],$_POST['rollbacktype']=='logged', $psqlExe, $conf['temp_dir'].$sep, false);

				// automatic form to go to the emajrollbacks.php page
				echo "<form id=\"auto\" action=\"emajrollbacks.php\" method=\"get\">\n";
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

			if ($emajdb->getNumEmajVersion() < 20100) {	// version < 2.1.0) {
				// for emaj version prior 2.1, call the old emaj_rollback_group() function
				if (isset($_POST['rollbacktype'])) {
					$status = $emajdb->oldRollbackGroup($_POST['group'],$_POST['mark'],$_POST['rollbacktype']=='logged');
				} else {
					$status = $emajdb->oldRollbackGroup($_POST['group'],$_POST['mark'],false);
				}
				if ($status == 0) {
					if ($_POST['back']=='list') {
						show_groups(sprintf($lang['emajrlbkgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
					} else {
						show_group(sprintf($lang['emajrlbkgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
					}
				} else {
					if ($_POST['back']=='list') {
						show_groups('',sprintf($lang['emajrlbkgrouperr'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
					} else {
						show_group('',sprintf($lang['emajrlbkgrouperr'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])));
					}
				}
			} else {

				// recent version: get and display the full rollback execution report
				if ($_REQUEST['back']=='list')
					$misc->printHeader('database', 'database','emajgroups');
				else
					$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

				$misc->printTitle($lang['emajrlbkagroup']);

				echo "<p>" . sprintf($lang['emajrlbkgroupreport'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark'])) . "</p>\n";

				// execute the rollback operation and get the execution report
				$rlbkReportMsgs = $emajdb->rollbackGroup($_POST['group'],$_POST['mark'],$_POST['rollbacktype']=='logged');

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

				echo "<div id=\"rlbkGroupReport\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
				$misc->printTable($rlbkReportMsgs, $columns, $actions, 'rlbkGroupReport', null, null, array('sorter' => true, 'filter' => false));
				echo "</div>\n";

				echo "<form action=\"emajgroups.php\" method=\"post\">\n";
				if ($_POST['back']=='list') {
					echo "<p><input type=\"hidden\" name=\"action\" value=\"show_groups\" />\n";
				} else {
					echo "<p><input type=\"hidden\" name=\"action\" value=\"show_group\" />\n";
				}
				echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
				echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
				echo $misc->form;
				echo "<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['strok']}\" />\n";
				echo "</form>\n";
			}
		}
	}

	/**
	 * Prepare rollback groups: ask for confirmation
	 */
	function rollback_groups() {
		global $misc, $lang, $emajdb;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['emajnoselectedgroup']);
			return;
		}

		// if only one group is selected, switch to the mono-group function
		if (count($_REQUEST['ma']) == 1) {
			$a = unserialize(htmlspecialchars_decode($_REQUEST['ma'][0], ENT_QUOTES));
			$_REQUEST['group'] = $a['group'];
			rollback_group();
			return;
		}

		// build the groups list
		$groupsList='';
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$groupsList .= $a['group'].', ';
		}
		$groupsList=substr($groupsList,0,strlen($groupsList)-2);

		// if at least one selected group is protected, stop
		$protectedGroups=$emajdb->getProtectedGroups($groupsList);
		if ($protectedGroups != '') {
			show_groups('',sprintf($lang['emajcantrlbkprotgroups'], htmlspecialchars($groupsList), htmlspecialchars($protectedGroups)));
			return;
		}
		// look for marks common to all selected groups
		$marks=$emajdb->getRollbackMarkGroups($groupsList);
		// if no mark is usable for all selected groups, stop
		if ($marks->recordCount()==0) {
			show_groups('',sprintf($lang['emajnomarkgroups'], htmlspecialchars($groupsList)));
			return;
		}
		// get the youngest timestamp protected mark for all groups
		$youngestProtectedMarkTimestamp=$emajdb->getYoungestProtectedMarkTimestamp($groupsList);

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['emajrlbkgroups']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_groups_confirm_alter\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo sprintf($lang['emajselectmarkgroups'], htmlspecialchars($groupsList));
		echo "<select name=\"mark\">\n";
		$optionDisabled = '';
		foreach($marks as $m) {
			// if the mark is older than the youngest protected against rollback, disabled it and the next ones
			if ($m['mark_datetime'] < $youngestProtectedMarkTimestamp) $optionDisabled = 'disabled';
			echo "<option value=\"",htmlspecialchars($m['mark_name']),"\" $optionDisabled>",htmlspecialchars($m['mark_name'])," (",htmlspecialchars($m['mark_datetime']),")</option>\n";
		}
		echo "</select></p><p>\n";
		echo $misc->form;
		echo "{$lang['emajrollbacktype']} : ";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"unlogged\" checked>{$lang['emajunlogged']}";
		echo "<input type=\"radio\" name=\"rollbacktype\" value=\"logged\">{$lang['emajlogged']}\n";
		echo "</p><p>";
		echo "<input type=\"submit\" name=\"rollbackgroups\" value=\"{$lang['emajrlbk']}\" />\n";
		if ($emajdb->isAsyncRlbkUsable() ) {
			echo "<input type=\"submit\" name=\"async\" value=\"{$lang['emajrlbkthenmonitor']}\" />\n";
		}
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Ask the user to confirm a multi groups rollback targeting a mark set prior alter_group operations
	 */
	function rollback_groups_confirm_alter() {
		global $lang, $misc, $emajdb;

		if ($emajdb->getNumEmajVersion() < 20100) {	// version < 2.1.0) {
			// for emaj version prior 2.1, directly go call the function that executes the rollback
			rollback_groups_ok();
		} else {
			// check that the rollback would not reach a mark set before any alter group operation

			// process the click on the <cancel> button
			if (isset($_POST['cancel'])) {
				if ($_POST['back'] == 'list') {
					show_groups();
				} else {
					show_group();
				}
			} else {

				// Check the groups are always in LOGGING state and not protected
				$groups = explode(', ',$_POST['groups']);
				foreach($groups as $g) {
					if ($emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
						show_groups('',sprintf($lang['emajcantrlbkidlegroups'], htmlspecialchars($groups), htmlspecialchars($g)));
						return;
					}
				}
				// if at least one selected group is protected, stop
				$protectedGroups=$emajdb->getProtectedGroups($_POST['groups']);
				if ($protectedGroups != '') {
					show_groups('',sprintf($lang['emajcantrlbkprotgroups'], htmlspecialchars($groups), htmlspecialchars($protectedGroups)));
					return;
				}

				// Check the mark is always valid
				if (!$emajdb->isRollbackMarkValidGroups($_POST['groups'],$_POST['mark'])) {
					show_groups('',sprintf($lang['emajcantrlbkinvalidmarkgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
					return;
				}

				$alterGroupSteps = $emajdb->getAlterAfterMarkGroups($_POST['groups'],$_POST['mark'],$lang);

				if ($alterGroupSteps->recordCount() > 0) {
					// there are alter_group operations to cross over, so ask for a confirmation

					$columns = array(
						'time' => array(
							'title' => $lang['emajtimestamp'],
							'field' => field('time_tx_timestamp'),
						),
						'step' => array(
							'title' => $lang['straction'],
							'field' => field('altr_action'),
						),
						'autorollback' => array(
							'title' => $lang['emajautorolledback'],
							'field' => field('altr_auto_rolled_back'),
							'type'	=> 'callback',
							'params'=> array('function' => 'renderBooleanIcon','align' => 'center')
						),
					);

					$actions = array ();

					$misc->printHeader('database', 'database','emajgroups');

					$misc->printTitle($lang['emajrlbkgroups']);

					echo "<p>" . sprintf($lang['emajreachaltergroups'], htmlspecialchars($_REQUEST['groups']), htmlspecialchars($_REQUEST['mark'])) . "</p>\n";
					echo "<div id=\"alterGroupStep\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
					$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');
					echo "</div>\n";

					echo "<form action=\"emajgroups.php\" method=\"post\">\n";
					echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_groups_ok\" />\n";
					echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($_REQUEST['groups']), "\" />\n";
					echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
					echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
					echo "<input type=\"hidden\" name=\"rollbacktype\" value=\"", htmlspecialchars($_REQUEST['rollbacktype']), "\" />\n";
					if (isset($_POST['async'])) {
						echo "<input type=\"hidden\" name=\"async\"", htmlspecialchars($_REQUEST['async']), "\" />\n";
					}
					echo $misc->form;
					echo "<input type=\"submit\" name=\"rollbackgroups\" value=\"{$lang['strconfirm']}\" />\n";
					echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
					echo "</form>\n";

				} else {
					// otherwise, directly execute the rollback
					rollback_groups_ok();
				}
			}
		}
	}

	/**
	 * Perform rollback_groups
	 */
	function rollback_groups_ok() {
		global $lang, $misc, $emajdb, $conf;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_groups();
		} else {

		// Check the groups are always in LOGGING state and not protected
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				if ($emajdb->getGroup($g)->fields['group_state'] != 'LOGGING') {
					show_groups('',sprintf($lang['emajcantrlbkidlegroups'],htmlspecialchars($groups), htmlspecialchars($g)));
					return;
				}
			}
		}
		// if at least one selected group is protected, stop
		$protectedGroups=$emajdb->getProtectedGroups($_POST['groups']);
		if ($protectedGroups != '') {
			show_groups('',sprintf($lang['emajcantrlbkprotgroups'], htmlspecialchars($groups), htmlspecialchars($protectedGroups)));
			return;
		}

		// Check the mark is always valid
		if (!$emajdb->isRollbackMarkValidGroups($_POST['groups'],$_POST['mark'])) {
			show_groups('',sprintf($lang['emajcantrlbkinvalidmarkgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
			return;
		}
		// OK

		if (isset($_POST['async'])) {

			// perform the rollback in asynchronous mode and switch to the rollback monitoring page
			$psqlExe = $misc->escapeShellCmd($conf['psql_path']);
			$sep = (substr(php_uname(), 0, 3) == "Win") ? '\\' : '/';
			$rlbkId = $emajdb->asyncRollbackGroups($_POST['groups'],$_POST['mark'],$_POST['rollbacktype']=='logged', $psqlExe, $conf['temp_dir'].$sep, true);

			// automatic form to go to the emajrollbacks.php page
			echo "<form id=\"auto\" action=\"emajrollbacks.php\" method=\"get\">\n";
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

		if ($emajdb->getNumEmajVersion() < 20100) {	// version < 2.1.0) {
				// for emaj version prior 2.1, call the old emaj_rollback_group() function
			if (isset($_POST['rollbacktype'])) {
				$status = $emajdb->rollbackGroups($_POST['groups'],$_POST['mark'],$_POST['rollbacktype']=='logged');
			} else {
				$status = $emajdb->rollbackGroups($_POST['groups'],$_POST['mark'],false);
			}
			if ($status == 0) {
				show_groups(sprintf($lang['emajrlbkgroupsok'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
			} else {
				show_groups('',sprintf($lang['emajrlbkgroupserr'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
			}
		} else {
			// recent version: get and display the full rollback execution report
			$misc->printHeader('database', 'database','emajgroups');

			$misc->printTitle($lang['emajrlbkgroups']);

			echo "<p>" . sprintf($lang['emajrlbkgroupsreport'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])) . "</p>\n";

			// execute the rollback operation and get the execution report
			$rlbkReportMsgs = $emajdb->rollbackGroups($_POST['groups'],$_POST['mark'],$_POST['rollbacktype']=='logged');

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

			echo "<div id=\"rlbkGroupsReport\" style=\"margin-top:15px;margin-bottom:15px\" >\n";
			$misc->printTable($rlbkReportMsgs, $columns, $actions, 'rlbkGroupsReport', null, null, array('sorter' => true, 'filter' => false));
			echo "</div>\n";

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"show_groups\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"rollbackgroup\" value=\"{$lang['strok']}\" />\n";
			echo "</form>\n";
		}
	}

	/**
	 * Prepare rename_mark_group: ask for the new name for the mark to rename and confirmation
	 */
	function rename_mark_group() {
		global $misc, $lang;

		if (!isset($_POST['group'])) $_POST['group'] = '';
		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajrenameamark']);

		echo "<p>", sprintf($lang['emajconfirmrenamemark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['emajnewnamemark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"newmark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"newmark\" required pattern=\"\S+.*\" placeholder='{$lang['emajrequiredfield']}' autocomplete=\"off\"/ ></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rename_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" value=\"{$lang['emajrename']}\" id=\"ok\" disabled=\"disabled\"/>\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" formnovalidate /></p>\n";
		echo "</form>\n";

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

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_group();
		} else {

		// Check the supplied mark group is valid
			$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'],$_POST['newmark']);
			if (is_null($finalMarkName)) {
				show_group('',sprintf($lang['emajinvalidmark'], htmlspecialchars($_POST['newmark'])));
			} else {
			// OK
				$status = $emajdb->renameMarkGroup($_POST['group'],$_POST['mark'], $finalMarkName);
				if ($status >= 0)
					show_group(sprintf($lang['emajrenamemarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
				else
					show_group('',sprintf($lang['emajrenamemarkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
			}
		}
	}

	/**
	 * Prepare delete mark group: ask for confirmation
	 */
	function delete_mark() {
		global $misc, $lang;

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajdelamark']);

		echo "<p>", sprintf($lang['emajconfirmdelmark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"delete_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"deletemark\" value=\"{$lang['strdelete']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform delete mark group
	 */
	function delete_mark_ok() {
		global $lang, $emajdb, $lang;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_group();
		} else {

			$status = $emajdb->deleteMarkGroup($_POST['group'],$_POST['mark']);
			if ($status >= 0)
				show_group(sprintf($lang['emajdelmarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
			else
				show_group('',sprintf($lang['emajdelmarkerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		}
	}

	/**
	 * Prepare delete mark group for several marks: ask for confirmation
	 */
	function delete_marks() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajdelmarks']);

		// build the marks list
		$marksList=''; $nbMarks=0;
		foreach($_REQUEST['ma'] as $v) {
			$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
			$marksList.=$a['mark'].', ';
			$nbMarks++;
		}
		$marksList=substr($marksList,0,strlen($marksList)-2);

		// Chech that at least 1 mark will remain
		if ($nbMarks >= $emajdb->getNbMarks($_REQUEST['group'])) {
			show_group('',sprintf($lang['emajdelmarkserr2'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
			return;
		}

		echo "<p>", sprintf($lang['emajconfirmdelmarks'], htmlspecialchars($marksList), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"delete_marks_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"marks\" value=\"", htmlspecialchars($marksList), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"deletemarks\" value=\"{$lang['strdelete']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform delete mark group for several marks
	 */
	function delete_marks_ok() {
		global $data, $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_group();
		} else {

			$marks = explode(', ',$_POST['marks']);
			$status = $data->beginTransaction();
			if ($status == 0) {
				foreach($marks as $m) {
					$status = $emajdb->deleteMarkGroup($_POST['group'],$m);
					if ($status != 0) {
						$data->rollbackTransaction();
						show_group('',sprintf($lang['emajdelmarkserr'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
						return;
					}
				}
			}
			if($data->endTransaction() == 0)
				show_group(sprintf($lang['emajdelmarksok'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
			else
				show_group('',sprintf($lang['emajdelmarkserr'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
		}
	}

	/**
	 * Prepare delete before mark group: ask for confirmation
	 */
	function delete_before_mark() {
		global $misc, $lang;

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['emajdelmarksprior']);

		echo "<p>", sprintf($lang['emajconfirmdelmarksprior'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"delete_before_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		echo $misc->form;
		echo "<input type=\"submit\" name=\"deletebeforemark\" value=\"{$lang['strdelete']}\" />\n";
		echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
		echo "</form>\n";
	}

	/**
	 * Perform delete before mark group
	 */
	function delete_before_mark_ok() {
		global $lang, $emajdb;

		// process the click on the <cancel> button
		if (isset($_POST['cancel'])) {
			show_group();
		} else {

			$status = $emajdb->deleteBeforeMarkGroup($_POST['group'],$_POST['mark']);
			if ($status > 0)
				show_group(sprintf($lang['emajdelmarkspriorok'],$status, htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
			else
				show_group('',sprintf($lang['emajdelmarkspriorerr'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
		}
	}

	/**
	 * Call the sqleditor.php page passing the sqlquery to display in $_SESSION
	 * We are already in the target frame
	 */
	function call_sqledit() {
		global $misc;

		$_SESSION['sqlquery'] = $_REQUEST['sqlquery'];
		echo "<meta http-equiv=\"refresh\" content=\"0;url=sqledit.php?subject=table&amp;{$misc->href}&amp;action=sql&amp;paginate=true\">";
	}

	function doTree() {
		global $misc, $emajdb;

		$groups = $emajdb->getGroups();

		$reqvars = $misc->getRequestVars('database');

		$attrs = array(
			'text' => field('group_name'),
			'icon' => 'EmajGroup',
			'toolTip' => field('group_comment'),
			'action' => url	(
				'redirect.php',
				$reqvars,
				array(
					'subject' => 'emajgroup',
					'action'  => 'show_group',
					'group'  => field('group_name')
					)
				),
		);

		$misc->printTree($groups, $attrs, 'emajgroups');
		exit;
	}

	/* shortcuts: these functions exit the script */
	if ($action == 'tree') doTree();

	// redirect to the emajenvir.php page if the emaj extension is not installed or accessible or is too old
	if (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
		&& $emajdb->getNumEmajVersion() >= $oldest_supported_emaj_version_num)) {
		header('Location: emajenvir.php?' . $_SERVER["QUERY_STRING"]);
	}

// The export_groups_ok action only builds and downloads the configuration file, but do not resend the main page
	if ($action == 'export_groups_ok') {
		export_groups_ok();
		exit;
	}

	$misc->printHtmlHeader($lang['emajgroupsmanagement']);
	$misc->printBody();

	switch ($action) {
		case 'alter_group':
			alter_group();
			break;
		case 'alter_group_ok':
			alter_group_ok();
			break;
		case 'alter_groups':
			alter_groups();
			break;
		case 'alter_groups_ok':
			alter_groups_ok();
			break;
		case 'call_sqledit':
			call_sqledit();
			break;
		case 'comment_group':
			comment_group();
			break;
		case 'comment_group_ok':
			comment_group_ok();
			break;
		case 'comment_mark_group':
			comment_mark_group();
			break;
		case 'comment_mark_group_ok':
			comment_mark_group_ok();
			break;
		case 'create_configured_group':
			create_configured_group();
			break;
		case 'create_configured_group_ok':
			create_configured_group_ok();
			break;
		case 'create_group':
			create_group();
			break;
		case 'create_group_ok':
			create_group_ok();
			break;
		case 'delete_before_mark':
			delete_before_mark();
			break;
		case 'delete_before_mark_ok':
			delete_before_mark_ok();
			break;
		case 'delete_mark':
			delete_mark();
			break;
		case 'delete_mark_ok':
			delete_mark_ok();
			break;
		case 'delete_marks':
			delete_marks();
			break;
		case 'delete_marks_ok':
			delete_marks_ok();
			break;
		case 'drop_group':
			drop_group();
			break;
		case 'drop_groups':
			drop_groups();
			break;
		case 'drop_group_ok':
			drop_group_ok();
			break;
		case 'drop_groups_ok':
			drop_groups_ok();
			break;
		case 'export_groups':
			export_groups();
			break;
		case 'import_groups':
			import_groups();
			break;
		case 'import_groups_select':
			import_groups_select();
			break;
		case 'import_groups_ok':
			import_groups_ok();
			break;
		case 'log_stat_group':
			log_stat_group();
			break;
		case 'protect_group':
			protect_group();
			break;
		case 'protect_mark_group':
			protect_mark_group();
			break;
		case 'rename_mark_group':
			rename_mark_group();
			break;
		case 'rename_mark_group_ok':
			rename_mark_group_ok();
			break;
		case 'reset_group':
			reset_group();
			break;
		case 'reset_groups':
			reset_groups();
			break;
		case 'reset_group_ok':
			reset_group_ok();
			break;
		case 'reset_groups_ok':
			reset_groups_ok();
			break;
		case 'rollback_group':
			rollback_group();
			break;
		case 'rollback_group_confirm_alter':
			rollback_group_confirm_alter();
			break;
		case 'rollback_group_ok':
			rollback_group_ok();
			break;
		case 'rollback_groups':
			rollback_groups();
			break;
		case 'rollback_groups_confirm_alter':
			rollback_groups_confirm_alter();
			break;
		case 'rollback_groups_ok':
			rollback_groups_ok();
			break;
		case 'set_mark_group':
			set_mark_group();
			break;
		case 'set_mark_group_ok':
			set_mark_group_ok();
			break;
		case 'set_mark_groups':
			set_mark_groups();
			break;
		case 'set_mark_groups_ok':
			set_mark_groups_ok();
			break;
		case 'show_content_group':
			show_content_group();
			break;
		case 'show_group':
			show_group();
			break;
		case 'show_groups':
			show_groups();
			break;
		case 'start_group':
			start_group();
			break;
		case 'start_group_ok':
			start_group_ok();
			break;
		case 'start_groups':
			start_groups();
			break;
		case 'start_groups_ok':
			start_groups_ok();
			break;
		case 'stop_group':
			stop_group();
			break;
		case 'stop_group_ok':
			stop_group_ok();
			break;
		case 'stop_groups':
			stop_groups();
			break;
		case 'stop_groups_ok':
			stop_groups_ok();
			break;
		case 'unprotect_group':
			unprotect_group();
			break;
		case 'unprotect_mark_group':
			unprotect_mark_group();
			break;
		default:
			show_groups();
	}

	$misc->printFooter();
?>
