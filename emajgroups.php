<?php
	/*
	 * Manage the E-Maj tables groups
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

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
		// disable the alter_group button when there is no configuration change to apply
		if (isset($loggingActions['alter_group']) && (!$rowdata->fields['has_waiting_changes'])) {
			$loggingActions['alter_group']['disable'] = true;
		}
		return $loggingActions;
	}

	// Functions to dynamicaly modify actions list for each table group in idle state to display
	function idleGroupPre(&$rowdata, $idleActions) {
		global $emajdb;

		// disable the alter_group button when there is no configuration change to apply
		if (isset($idleActions['alter_group']) && (!$rowdata->fields['has_waiting_changes'])) {
			$idleActions['alter_group']['disable'] = true;
		}
		return $idleActions;
	}

	// Function to dynamicaly modify actions list for each mark
	function markPre(&$rowdata, $actions) {

		// disable the rollback button if the mark is deleted or if a previous mark is protected
		if (isset($actions['rollbackgroup']) && $rowdata->fields['no_rollback_action'] == 't') {
			$actions['rollbackgroup']['disable'] = true;
		}
		// disable the first mark button if the mark is the last displayed
		if (isset($actions['deletebeforemark']) && $rowdata->fields['no_first_mark_action'] == 't') {
			$actions['deletebeforemark']['disable'] = true;
		}
		// disable the protect button if the mark is already protected
		if (isset($actions['protectmark']) && $rowdata->fields['no_protect_action'] == 't') {
			$actions['protectmark']['disable'] = true;
		}
		// disable the unprotect button if the mark is not protected
		if (isset($actions['unprotectmark']) && $rowdata->fields['no_unprotect_action'] == 't') {
			$actions['unprotectmark']['disable'] = true;
		}
		return $actions;
	}

	// Callback function to dynamicaly replace the group type from the database by one or two icons
	function renderGroupType($val) {
		global $misc, $lang;

		if ($val == 'ROLLBACKABLE') {
			$icon = $misc->icon('EmajRollbackable');
			$alt = $lang['strrollbackable'];
			$img = "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'AUDIT_ONLY') {
			$icon = $misc->icon('EmajAuditOnly');
			$alt = $lang['strauditonly'];
			$img = "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		} elseif ($val == 'ROLLBACKABLE-PROTECTED') {
			$icon = $misc->icon('EmajRollbackable');
			$alt = $lang['strrollbackable'];
			$img = "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
			$icon = $misc->icon('EmajPadlock');
			$alt = $lang['strprotected'];
			$img .= "<img src=\"{$icon}\" alt=\"{$alt}\" title=\"{$alt}\"/>";
		}
		return $img;
	}

	// Callback function to dynamicaly modify the diagnostic column of configured but not yet created groups
	// It replaces the database value by an icon
	function renderDiagnosticNewGroup($val) {
		global $misc, $lang;

		if ($val == '{0,0,0,0,0}') {
			$icon = 'CheckConstraint';
			return "<img src=\"".$misc->icon($icon)."\" />";
		} else {
			if (preg_match("/{(\d+),(\d+),(\d+),(\d+),(\d+)}/",$val,$cpt)) {
				$msg = '';
				if ($cpt[1] > 0) $msg .= sprintf($lang['strnoschema'], $cpt[1]);
				if ($cpt[2] > 0) $msg .= sprintf($lang['strinvalidschema'], $cpt[2]);
				if ($cpt[3] > 0) $msg .= sprintf($lang['strnorelation'], $cpt[3]);
				if ($cpt[4] > 0) $msg .= sprintf($lang['strinvalidtable'], $cpt[4]);
				if ($cpt[5] > 0) $msg .= sprintf($lang['strduplicaterelation'], $cpt[5]);
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

	// Callback function to dynamicaly modify the mark position in log session column content
	// The value is composed of 3 parts: the position of the mark in its log session and the log session start and stop times
	function renderLogSession($val) {
		global $misc, $lang;

		$parts = explode('#', $val);
		if ($parts[0] == '')
			$parts[0] = 'Simple';
		$logSessionInfo = sprintf($lang['strlogsessionstart'], $parts[1]);
		if ($parts[2] == '') {
			$color = 'Green';
		} else {
			$color = 'Grey';
			$logSessionInfo .= "\n" . sprintf($lang['strlogsessionstop'], $parts[2]);
		}
		$icon = $color . $parts[0];
		$div = "<div title=\"$logSessionInfo\"><img src=\"{$misc->icon($icon)}\" alt=\"$icon\" title=\"$logSessionInfo\" class=\"fullsizecellicon\" /></div>";
		return $div;
	}

	// Callback function to dynamicaly modify the mark state column content
	// It replaces the database value by an icon
	function renderMarkState($val) {
		global $misc, $lang, $emajdb;

		if ($emajdb->getNumEmajVersion() >= 40400) {	// version >= 4.4
			if ($val == 'PROTECTED') {
				$img = "<img src=\"{$misc->icon('EmajPadlock')}\" alt=\"protected\" title=\"{$lang['strprotectedmark']}\"/>";
			} else {
				$img = '';
			}
		} else {
			if ($val == 'ACTIVE') {
				$img = "<img src=\"{$misc->icon('ActiveMark')}\" alt=\"active_mark\" title=\"{$lang['stractivemark']}\"/>";
			} elseif ($val == 'DELETED') {
				$img = "<img src=\"{$misc->icon('DeletedMark')}\" alt=\"deleted_mark\" title=\"{$lang['strdeletedmark']}\"/>";
			} elseif ($val == 'ACTIVE-PROTECTED') {
				$img = "<img src=\"{$misc->icon('ActiveMark')}\" alt=\"active_mark\" title=\"{$lang['stractivemark']}\"/>";
				$img .= "<img src=\"{$misc->icon('EmajPadlock')}\" alt=\"protected\" title=\"{$lang['strprotectedmark']}\"/>";
			}
		}
		return $img;
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

/********************************************************************************************************
 * Other elementary functions
 *******************************************************************************************************/

	/**
	 * Process the click on the <cancel> button.
	 */
	function processCancelButton($back) {
		global $misc;

		// Call either the show_groups or the show_group function depending on the $back parameter.
		if (isset($_POST['cancel'])) {
			if ($back == 'list')
				show_groups();
			else
				show_group();
			$misc->printFooter();
			exit();
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
	function recheckGroups($groupsList, $errMsgAction, $requiredLoggingState = null, $back = null) {
		global $lang, $emajdb, $_reload_browser, $misc;

		// Check the groups existence
		$missingGroups = $emajdb->missingGroups($groupsList);
		if ($missingGroups->fields['nb_groups'] > 0) {
			if ($missingGroups->fields['nb_groups'] == 1)
				// One group doesn't exist anymore
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupmissing'], htmlspecialchars($missingGroups->fields['groups_list'])));
			else
				// Several groups do not exist anymore
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsmissing'], $missingGroups->fields['nb_groups'], htmlspecialchars($missingGroups->fields['groups_list'])));
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
				if ($back == 'list') {
					show_groups('', $errMsgAction . '<br>' . $errorMessage);
				} else {
					show_group('', $errMsgAction . '<br>' . $errorMessage);
				}
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
				show_group('', $errMsgAction . '<br>' .
					sprintf($lang['strmarkmissing'], htmlspecialchars($missingMarks->fields['marks_list'])));
			else
				// Several marks do not exist anymore
				show_group('', $errMsgAction . '<br>' .
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
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strmissingmarkgroup'], htmlspecialchars($missingGroups->fields['groups_list'])));
			else
				// The mark doesn't exist anymore for several groups
				show_groups('', $errMsgAction . '<br>' .
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
	function checkNewMarkGroups($groupsList, $mark, $errMsgAction, $back, $duplicateCheck = 1) {
		global $emajdb, $lang, $misc;

		// Check the forbidden values.
		if ($mark == '' or $mark == 'EMAJ_LAST_MARK') {
			$errorMessage = sprintf($lang['strinvalidmark'], htmlspecialchars($mark));
			if ($back == 'list') {
				show_groups('', $errMsgAction . '<br>' . $errorMessage);
			} else {
				show_group('', $errMsgAction . '<br>' . $errorMessage);
			}
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
				if ($back == 'list') {
					show_groups('', $errMsgAction . '<br>' . $errorMessage);
				} else {
					show_group('', $errMsgAction . '<br>' . $errorMessage);
				}
				$misc->printFooter();
				exit();
			}
		}

		return $finalMarkName;
	}

	/**
	 * Check an ADO return code. In case of error, display an error message and exit the current function.
	 */
	function checkADOReturnCode($AdoRetCode, $errMsgAction, $back = null) {
		global $lang, $misc;

		if ($AdoRetCode < 0) {
			$errorMessage = sprintf($lang['stradoreturncode'], htmlspecialchars($AdoRetCode));
			if ($back == 'list') {
				show_groups('', $errMsgAction . '<br>' . $errorMessage);
			} else {
				show_group('', $errMsgAction . '<br>' . $errorMessage);
			}
			$misc->printFooter();
			exit();
		}

		return;
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
				'title' => $lang['strgroup'],
				'field' => field('group_name'),
				'url'   => "emajgroups.php?action=show_group&amp;{$misc->href}&amp;",
				'vars'  => array('group' => 'group_name'),
			),
			'creationdatetime' => array(
				'title' => $lang['strgroupcreatedat'],
				'field' => field('creation_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['stroldtimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
					),
				'sorter_text_extraction' => 'span_text',
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
				'title' => $lang['strmarks'],
				'field' => field('nb_mark'),
				'type'  => 'numeric'
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('group_comment'),
				'type' => 'spanned',
				'params'=> array(
						'cliplen' => 12,
						'class' => 'tooltip right-aligned-tooltip',
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
					'content' => $lang['strsetmark'],
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
					'content' => $lang['strprotect'],
					'icon' => 'PadLockOn',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'protect_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'protect_groups',
					),
				'unprotect_group' => array(
					'content' => $lang['strunprotect'],
					'icon' => 'PadLockOff',
					'attr' => array (
						'href' => array (
							'url' => 'emajgroups.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'unprotect_group',
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'unprotect_groups',
					),
				'rollback_group' => array(
					'content' => $lang['strrlbk'],
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
					'content' => $lang['strsetcomment'],
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
			if ($emajdb->getNumEmajVersion() < 30200) {	// version < 3.2
				$loggingActions = array_merge($loggingActions, array(
					'alter_group' => array(
						'content' => $lang['strApplyConfChanges'],
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
						'content' => $lang['strApplyConfChanges'],
						'attr' => array (
							'href' => array (
								'url' => 'emajgroups.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'alter_group',
									'back' => 'list',
									'group' => field('group_name'),
							)))),
					'multiaction' => 'alter_groups',
					),
				));
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
					'content' => $lang['strsetcomment'],
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
				'title' => $lang['strgroup'],
				'field' => field('grpdef_group'),
			),
			'actions' => array(
				'title' => $lang['stractions'],
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
		);

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

		$misc->printTitle($lang['strlogginggroups'], $misc->buildTitleRecordsCounter($loggingGroups), $lang['strlogginggrouphelp']);

		$misc->printTable($loggingGroups, $columns, $loggingActions, 'loggingGroups', $lang['strnologginggroup'], 'loggingGroupPre', array('sorter' => true, 'filter' => true));

		echo "<hr>";
		$misc->printTitle($lang['stridlegroups'], $misc->buildTitleRecordsCounter($idleGroups), $lang['stridlegrouphelp']);

		$misc->printTable($idleGroups, $columns, $idleActions, 'idleGroups', $lang['strnoidlegroup'], 'idleGroupPre', array('sorter' => true, 'filter' => true));

		echo "<hr/>\n";

		if ($emajdb->getNumEmajVersion() < 30200) {					// version < 3.2
			// configured but not yet created tables groups section
			$configuredGroups = $emajdb->getConfiguredGroups();

			$misc->printTitle($lang['strconfiguredgroups'], null, $lang['strconfiguredgrouphelp']);

			$misc->printTable($configuredGroups, $configuredColumns, $configuredActions, 'configuredGroups', $lang['strnoconfiguredgroups'], null, array('sorter' => true, 'filter' => true));

			// for emaj_adm role only, give information about how to create a group
			if ($emajdb->isEmaj_Adm()) {
				echo "<p>{$lang['strnoconfiguredgroup']}</p>\n";
				echo "<form id=\"createEmptyGroup_form\" action=\"emajgroups.php?action=create_group&amp;back=list&amp;empty=true&amp;{$misc->href}\"";
				echo " method=\"post\" enctype=\"multipart/form-data\">\n";
				echo "\t<input type=\"submit\" value=\"{$lang['strcreateemptygroup']}\" />\n";
				echo "</form>\n";
			}
		} else {
			// Emaj Version 3.2+
			// for emaj_adm role only, display additional buttons
			if ($emajdb->isEmaj_Adm()) {
				echo "<div class=\"actionslist\">\n";
				// display the "new group" button
				echo "\t<form id=\"createEmptyGroup_form\" method=\"post\" action=\"emajgroups.php?action=create_group&amp;back=list&amp;{$misc->href}\">\n";
				echo "\t\t<input type=\"submit\" value=\"{$lang['strnewgroup']}\" />\n";
				echo "\t</form>\n";

				// display the "export groups configuration" and "import groups configuration" buttons
				if ($emajdb->getNumEmajVersion() >= 30300) {			// version >= 3.3.0
					// form to export groups configuration
					// the export button is disabled when no group exists
					echo "\t<form id=\"exportGroupsConf_form\" method=\"post\" action=\"emajgroups.php?action=export_groups&amp;back=list&amp;{$misc->href}\">\n";
					$disabled = ($nbGroup == 0) ? 'disabled' : '';
					echo "\t\t<input type=\"submit\" name=\"exportButton\" value=\"{$lang['strexport']}\" {$disabled}>\n";
					echo "\t</form>\n";

					// form to import groups configuration
					echo "\t<form name=\"importGroupsConf\" id=\"importGroupsConf\" method=\"post\"";
					echo " action=\"emajgroups.php?action=import_groups&amp;back=list&amp;{$misc->href}\">\n";
					echo "\t\t<input type=\"submit\" name=\"importButton\" value=\"{$lang['strimport']}\">\n";
					echo "\t</form>\n";
				}
				echo "</div>\n";
			}
		}
		if ($emajdb->getNumEmajVersion() >= 40400) {					// version >= 4.4
		// display the list of dropped tables group

			$droppedGroups = $emajdb->getDroppedGroups();

			$columns = array(
				'group' => array(
					'title' => $lang['strgroup'],
					'field' => field('grph_group'),
					'url'   => "grouphistory.php?action=show_history_group&amp;{$misc->href}&amp;",
					'vars'  => array('group' => 'grph_group'),
				),
				'latestrollbackable' => array(
					'title' => $lang['strgrouplatesttype'],
					'field' => field('latest_is_rollbackable'),
					'type'	=> 'callback',
					'params'=> array(
							'function' => 'renderGroupType',
							'align' => 'center',
							),
				),
				'latestdropdatetime' => array(
					'title' => $lang['strgrouplatestdropat'],
					'field' => field('latest_drop_datetime'),
					'type' => 'spanned',
					'params'=> array(
						'dateformat' => $lang['stroldtimestampformat'],
						'locale' => $lang['applocale'],
						'class' => 'tooltip left-aligned-tooltip',
						),
					'sorter_text_extraction' => 'span_text',
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			);

			if ($emajdb->isEmaj_Adm()) {
				$droppedActions = array(
					'create_group' => array(
						'content' => $lang['strrecreate'],
						'icon' => 'Create',
						'attr' => array (
							'href' => array (
								'url' => 'emajgroups.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'create_group',
									'back' => 'list',
									'group' => field('grph_group'),
									'type' => field('latest_is_rollbackable'),
								))))
					),
					'forget_group' => array(
						'content' => $lang['strforget'],
						'icon' => 'Eraser',
						'attr' => array (
							'href' => array (
								'url' => 'emajgroups.php',
								'urlvars' => array_merge($urlvars, array (
									'action' => 'forget_group',
									'back' => 'list',
									'group' => field('grph_group'),
								))))
					),
				);
			} else {
				$droppedActions = array();
			}

			echo "<hr>";
			$misc->printTitle($lang['strdroppedgroupslist'], $misc->buildTitleRecordsCounter($droppedGroups));

			$misc->printTable($droppedGroups, $columns, $droppedActions, 'droppedgroups', $lang['strnodroppedgroup'], null, array('sorter' => true, 'filter' => true));

		}
	}

	/**
	 * Displays all detailed information about one group, including marks
	 */
	function show_group($msg = '', $errMsg = '') {
		global $misc, $lang, $emajdb, $_reload_browser;

		if (! $emajdb->existsGroup($_REQUEST['group'])) {
			show_groups('', sprintf($lang['strgroupmissing'], htmlspecialchars($_REQUEST['group'])));
			$_reload_browser = true;
			return;
		}

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
			'createdat' => array(
				'title' => $lang['strgroupcreatedat'],
				'field' => field('group_creation_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['stroldtimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
					),
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
				'title' => $lang['strtables'],
				'field' => field('group_nb_table'),
				'type'  => 'numeric'
			),
			'nbseq' => array(
				'title' => $lang['strsequences'],
				'field' => field('group_nb_sequence'),
				'type'  => 'numeric'
			),
			'state' => array(
				'title' => $lang['strstate'],
				'field' => field('group_state'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderGroupState','align' => 'center')
			),
		);
		if ($emajdb->getNumEmajVersion() >= 40400) {					// version 4.4+
			if ($groupState == 'LOGGING') {
				$columns = array_merge($columns, array(
					'startedat' => array(
						'title' => $lang['strgroupstartedat'],
						'field' => field('group_start_datetime'),
						'type' => 'spanned',
						'params'=> array(
							'dateformat' => $lang['stroldtimestampformat'],
							'locale' => $lang['applocale'],
							'class' => 'tooltip left-aligned-tooltip',
							),
					),
				));
			}
		}
		$columns = array_merge($columns, array(
			'nbmark' => array(
				'title' => $lang['strmarks'],
				'field' => field('nb_mark'),
				'type'  => 'numeric'
			),
			'logsize' => array(
				'title' => $lang['strlogsize'],
				'field' => field('log_size'),
				'params'=> array('align' => 'center'),
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
		));

		$urlvars = $misc->getRequestVars();

		$groupActions = array();

		// print group's characteristics
		$misc->printTitle(sprintf($lang['strgroupproperties'], htmlspecialchars($_REQUEST['group'])));
		$misc->printTable($group, $columns, $groupActions, 'detailGroup', 'no group, internal error !');

		// display group's comment if exists
		if ($comment<>'') {
			echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$comment}</span></p>\n";
		}

		// display the buttons corresponding to the available functions for the group, depending on its state

		if ($emajdb->isEmaj_Adm()) {
			$navlinks = array();

			// start_group
			if ($groupState == 'IDLE') {
				$navlinks['start_group'] = array (
					'content' => $lang['strstart'],
					'attr'=> array (
						'href' => array (
							'url' => "emajgroups.php",
							'urlvars' => array(
								'action' => 'start_group',
								'group' => $_REQUEST['group'],
								'back' => 'detail',
							)
						)
					),
				);
			}

			// set_mark_group
			if ($groupState == 'LOGGING') {
				$navlinks['set_mark_group'] = array (
					'content' => $lang['strsetmark'],
					'attr'=> array (
						'href' => array (
							'url' => "emajgroups.php",
							'urlvars' => array(
								'action' => 'set_mark_group',
								'group' => $_REQUEST['group'],
								'back' => 'detail',
							)
						)
					),
				);
			}

			// reset_group
			if ($groupState == 'IDLE') {
				$navlinks['reset_group'] = array (
					'content' => $lang['strreset'],
					'attr'=> array (
						'href' => array (
							'url' => "emajgroups.php",
							'urlvars' => array(
								'action' => 'reset_group',
								'group' => $_REQUEST['group'],
								'back' => 'detail',
							)
						)
					),
				);
			}

			// protect_group
			if ($groupState == 'LOGGING' && $groupType == "ROLLBACKABLE") {
				$navlinks['protect_group'] = array (
					'content' => $lang['strprotect'],
					'attr'=> array (
						'href' => array (
							'url' => "emajgroups.php",
							'urlvars' => array(
								'action' => 'protect_group',
								'group' => $_REQUEST['group'],
								'back' => 'detail',
							)
						)
					),
				);
			}

			// unprotect_group
			if ($groupState == 'LOGGING' && $groupType == "ROLLBACKABLE-PROTECTED") {
				$navlinks['unprotect_group'] = array (
					'content' => $lang['strunprotect'],
					'attr'=> array (
						'href' => array (
							'url' => "emajgroups.php",
							'urlvars' => array(
								'action' => 'unprotect_group',
								'group' => $_REQUEST['group'],
								'back' => 'detail',
							)
						)
					),
				);
			}

			// stop_group
			if ($groupState == 'LOGGING') {
				$navlinks['stop_group'] = array (
					'content' => $lang['strstop'],
					'attr'=> array (
						'href' => array (
							'url' => "emajgroups.php",
							'urlvars' => array(
								'action' => 'stop_group',
								'group' => $_REQUEST['group'],
								'back' => 'detail',
							)
						)
					),
				);
			}

			// comment_group
			$navlinks['comment_group'] = array (
				'content' => $lang['strsetcomment'],
				'attr'=> array (
					'href' => array (
						'url' => "emajgroups.php",
						'urlvars' => array(
							'action' => 'comment_group',
							'group' => $_REQUEST['group'],
							'back' => 'detail',
						)
					)
				),
			);

			// alter_group
			if ($hasWaitingChanges && $emajdb->getNumEmajVersion() < 30200) {
				$navlinks['alter_group'] = array (
					'content' => $lang['strApplyConfChanges'],
					'attr'=> array (
						'href' => array (
							'url' => "emajgroups.php",
							'urlvars' => array(
								'action' => 'alter_group',
								'group' => $_REQUEST['group'],
								'back' => 'detail',
							)
						)
					),
				);
			}

			// drop_group
			if ($groupState == 'IDLE') {
				$navlinks['drop_group'] = array (
					'content' => $lang['strdrop'],
					'attr'=> array (
						'href' => array (
							'url' => "emajgroups.php",
							'urlvars' => array(
								'action' => 'drop_group',
								'group' => $_REQUEST['group'],
								'back' => 'detail',
							)
						)
					),
				);
			}

			$misc->printLinksList($navlinks, 'buttonslist');
		}

		// Show marks of the groups

		// get marks from database
		$marks = $emajdb->getMarks($_REQUEST['group']);

		echo "<hr/>\n";
		$misc->printTitle(sprintf($lang['strgroupmarks'], htmlspecialchars($_REQUEST['group'])));

		$columns = array();
		if ($emajdb->getNumEmajVersion() >= 40400) {
			$columns = array_merge($columns, array(
				'log_session' => array(
					'title' => '',
					'info'  => $lang['strlogsessionshelp'],
					'field' => field('mark_log_session'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderLogSession'),
					'class' => 'nopadding center',
					'filter'=> false,
			),
			));
		}

		$columns = array_merge($columns, array(
			'mark' => array(
				'title' => $lang['strmark'],
				'field' => field('mark_name'),
			),
			'state' => array(
				'title' => $lang['strstate'],
				'field' => field('mark_state'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderMarkState', 'align' => 'center'),
				'filter'=> false,
			),
			'datetime' => array(
				'title' => $lang['strmarksetat'],
				'field' => field('mark_datetime'),
				'type' => 'spanned',
				'params'=> array(
					'dateformat' => $lang['strrecenttimestampformat'],
					'locale' => $lang['applocale'],
					'class' => 'tooltip left-aligned-tooltip',
					),
			),
			'logrows' => array(
				'upper_title' => $lang['strchanges'],
				'upper_title_colspan' => 2,
				'title' => $lang['strnumber'],
				'field' => field('mark_logrows'),
				'type'  => 'numeric'
			),
			'cumlogrows' => array(
				'title' => $lang['strcumulated'],
				'info'  => $lang['strcumchangeshelp'],
				'field' => field('mark_cumlogrows'),
				'type'  => 'numeric'
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('mark_comment'),
				'type' => 'spanned',
				'params'=> array(
						'cliplen' => 15,
						'class' => 'tooltip right-aligned-tooltip',
						),
			),
		));

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
					'content' => $lang['strrlbk'],
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
		if ($emajdb->isEmaj_Adm() && $groupState == 'LOGGING' && $groupType != "AUDIT_ONLY") {
			$actions = array_merge($actions, array(
				'protectmark' => array(
					'content' => $lang['strprotect'],
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
					'content' => $lang['strunprotect'],
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
				'renamemark' => array(
					'content' => $lang['strrename'],
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
					'content' => $lang['strfirstmark'],
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
		if ($emajdb->isEmaj_Adm()) {
			$actions = array_merge($actions, array(
				'commentmark' => array(
					'content' => $lang['strsetcomment'],
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

		// display the marks list
		$misc->printTable($marks, $columns, $actions, 'marks', $lang['strnomark'], 'markPre', array('sorter' => false, 'filter' => true));
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

		$misc->printTitle($lang['strcreateagroup']);

		if (!isset($_REQUEST['group'])) $_REQUEST['group'] = '';

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strcreategrouperr'], htmlspecialchars($_POST['group']));

		// If the group is supposed to be empty, check the supplied group name doesn't exist
		// Call existsGroup() instead of isNewEmptyGroupValid() when emaj version < 4.0 will not be supported anymore
		if (!$emajdb->isNewEmptyGroupValid($_POST['group'])) {
			show_groups('', $errMsgAction . '<br>' . sprintf($lang['strgroupalreadyexists'], htmlspecialchars($_POST['group'])));
			return;
		}

		// OK, perform the action
		$status = $emajdb->createGroup($_POST['group'], $_POST['grouptype']=='rollbackable', true, $_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, 'list');
		show_groups(sprintf($lang['strcreategroupok'], htmlspecialchars($_POST['group'])));
		$_reload_browser = true;
	}

	/**
	 * Prepare create group for groups configured in emaj_group_def: ask for confirmation
	 */
	function create_configured_group() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database','emajgroups');

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

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"create_configured_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
			show_groups();
			return;
		}

	// OK
		$status = $emajdb->createGroup($_POST['group'],$_POST['grouptype']=='rollbackable',false,'');

		if ($status == 0) {
			show_groups(sprintf($lang['strcreategroupok'], htmlspecialchars($_POST['group'])));
			$_reload_browser = true;
		} else {
			show_groups('', sprintf($lang['strcreategrouperr'], htmlspecialchars($_POST['group'])));
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
		recheckGroups($_REQUEST['group'], $errMsgAction, 'IDLE', $_REQUEST['back']);

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strdropagroup']);

		echo "<p>", sprintf($lang['strconfirmdropgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"drop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdropgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'IDLE', $_REQUEST['back']);

		// OK, perform the action
		$status = $emajdb->dropGroup($_POST['group']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, 'list');
		show_groups(sprintf($lang['strdropgroupok'], htmlspecialchars($_POST['group'])));
		$_reload_browser = true;
	}

	/**
	 * Prepare drop groups: ask for confirmation
	 */
	function drop_groups() {
		global $misc, $lang;

		// build the groups list
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['strnoselectedgroup']);
			return;
		}
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdropgroupserr'], htmlspecialchars($groupsList));

		// Check that all groups exist and are in IDLE state
		recheckGroups($groupsList, $errMsgAction, 'IDLE', 'list');

		// Ok, build the form
		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['strdropgroups']);

		echo "<p>", sprintf($lang['strconfirmdropgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
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
		processCancelButton('list');

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strdropgroupserr'], htmlspecialchars($_POST['groups']));

		// Check that all groups exist and are in IDLE state
		recheckGroups($_POST['groups'], $errMsgAction, 'IDLE', 'list');

		// OK, perform the action
		$status = $emajdb->dropGroups($_POST['groups']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, 'list');
		show_groups(sprintf($lang['strdropgroupsok'], htmlspecialchars($_POST['groups'])));
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
			show_groups('', sprintf($lang['strgroupstillexists'], htmlspecialchars($_REQUEST['group'])));
			$_reload_browser = true;
			return;
		}

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['strforgetagroup']);

		echo "<p>", sprintf($lang['strconfirmforgetgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
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
		processCancelButton('list');

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strforgetgrouperr'], htmlspecialchars($_POST['group']));

		// Check that the group does not exist
		if ($emajdb->existsGroup($_POST['group'])) {
			show_groups('', sprintf($lang['strgroupstillexists'], htmlspecialchars($_POST['group'])));
			$_reload_browser = true;
			return;
		}

		// OK, perform the action
		$status = $emajdb->forgetGroup($_POST['group']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, 'list');
		show_groups(sprintf($lang['strforgetgroupok'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare alter group: ask for confirmation
	 */
	function alter_group() {
		global $misc, $lang, $emajdb;

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');
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

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_group_ok\" />\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
			echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		}

		if ($confOK) {
			$isGroupLogging = $emajdb->isGroupLogging($_REQUEST['group']);

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";

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
			echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
					show_groups('', sprintf($lang['strcantaltergroup'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('', sprintf($lang['strcantaltergroup'], htmlspecialchars($_POST['group'])));
				}
				exit();
			}

		// Check the supplied mark is valid
			if ($_POST['mark'] != '') {
				$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['group'], htmlspecialchars($_POST['mark']));
				if (is_null($finalMarkName)) {
					if ($_POST['back']=='list') {
						show_groups('', sprintf($lang['strinvalidmark'], htmlspecialchars($_POST['mark'])));
					} else {
						show_group('', sprintf($lang['strinvalidmark'], htmlspecialchars($_POST['mark'])));
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
					show_groups(sprintf($lang['straltergroupok'], htmlspecialchars($_POST['group'])));
				} else {
					show_group(sprintf($lang['straltergroupok'], htmlspecialchars($_POST['group'])));
				}
			} else
				if ($_POST['back'] == 'list') {
					show_groups('',sprintf($lang['straltergrouperr'], htmlspecialchars($_POST['group'])));
				} else {
					show_group('',sprintf($lang['straltergrouperr'], htmlspecialchars($_POST['group'])));
				}
		}
	}

	/**
	 * Prepare alter groups: ask for confirmation
	 */
	function alter_groups() {
		global $misc, $lang, $emajdb;

		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['strnoselectedgroup']);
			return;
		}

		$misc->printHeader('database', 'database','emajgroups');
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

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";
			echo "<p><input type=\"hidden\" name=\"action\" value=\"alter_groups_ok\" />\n";
			echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
			echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" /></p>\n";
			echo "</form>\n";
		}

		if ($confOK) {

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";

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
			echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
			show_groups();
		} else {

		// check the groups can be altered by looking at their state and operations that will be performed
			$groups = explode(', ',$_POST['groups']);
			foreach($groups as $g) {
				$check = $emajdb->checkAlterGroup($g);
				// exit the loop in case of error
				if ($check == 0) {
					if ($_POST['back'] == 'list') {
						show_groups('', sprintf($lang['strcantaltergroup'], htmlspecialchars($g)));
					} else {
						show_group('', sprintf($lang['strcantaltergroup'], htmlspecialchars($g)));
					}
					exit();
				}
			}
		// Check the supplied mark is valid for the groups
			if ($_POST['mark'] != '') {
				$finalMarkName = $emajdb->isNewMarkValidGroups($_POST['groups'],$_POST['mark']);
				if (is_null($finalMarkName)) {
					show_groups('', sprintf($lang['strinvalidmark'], htmlspecialchars($_POST['mark'])));
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
					show_groups(sprintf($lang['straltergroupsok'], htmlspecialchars($_POST['groups'])));
				} else {
					show_group(sprintf($lang['straltergroupsok'], htmlspecialchars($_POST['groups'])));
				}
			}else
				if ($_POST['back'] == 'list') {
					show_groups('',sprintf($lang['straltergroupserr'], htmlspecialchars($_POST['groups'])));
				} else {
					show_group('',sprintf($lang['straltergroupserr'], htmlspecialchars($_POST['groups'])));
				}
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

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strcommentagroup']);

		$group = $emajdb->getGroup($_REQUEST['group']);

		echo "<p>", sprintf($lang['strcommentgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($group->fields['group_comment']), "\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"comment_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strcommentgrouperr'], htmlspecialchars($_POST['group']));

		// Check the group still exists
		recheckGroups($_REQUEST['group'], $errMsgAction);

		// OK, perform the action
		$status = $emajdb->setCommentGroup($_POST['group'], $_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, $_POST['back']);
		if ($_POST['back'] == 'list')
			show_groups(sprintf($lang['strcommentgroupok'], htmlspecialchars($_POST['group'])));
		else
			show_group(sprintf($lang['strcommentgroupok'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare export group: select the groups to export and confirm
	 */
	function export_groups() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('database', 'database','emajgroups');

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
								'back' => 'list',
								'group' => field('group_name'),
							)))),
					'multiaction' => 'export_groups_ok',
				),
			));
		};

		$misc->printTable($groups, $columns, $actions, 'groups', '', null, array('sorter' => true, 'filter' => true));

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
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
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupmissing'], htmlspecialchars($missingGroups->fields['groups_list'])));
			else
				// Several groups do not exist anymore
				show_groups('', $errMsgAction . '<br>' .
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

		$misc->printHeader('database', 'database','emajgroups');

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
		processCancelButton('list');

		// Process the uploaded file

		// If the file is properly loaded,
		if (is_uploaded_file($_FILES['file_name']['tmp_name'])) {
			$jsonContent = file_get_contents($_FILES['file_name']['tmp_name']);
			$jsonStructure = json_decode($jsonContent, true);
		// ... and contains a valid JSON structure,
			if (json_last_error()===JSON_ERROR_NONE) {

				$misc->printHeader('database', 'database','emajgroups');

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
									'back' => 'list',
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

					echo "<form action=\"emajgroups.php\" method=\"post\">\n";
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

					echo "<form action=\"emajgroups.php\" method=\"post\">\n";
					echo "<input type=\"hidden\" name=\"action\" value=\"import_groups_ok\" />\n";
					echo $misc->form;
					echo "<div class=\"actionslist\">";
					echo "\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strok']}\" />\n";
					echo "</div></form>\n";

				}
			} else {
				show_groups('', sprintf($lang['strnotjsonfile'], $_FILES['file_name']['name']));
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

	/**
	 * Effectively import a tables groups configuration
	 */
	function import_groups_ok() {

		global $lang, $emajdb, $misc, $_reload_browser;

		// Process the click on the <cancel> button
		processCancelButton('list');

		// Build the groups list
		$groupsList = groupsArray2list($_REQUEST['ma']);

		// prepare the tables groups configuration import
		$errors = $emajdb->importGroupsConfPrepare($_POST['json'], $groupsList);
		if ($errors->recordCount() == 0) {
			// no error detected, so execute the effective configuration import
			$nbGroup = $emajdb->importGroupsConfig($_POST['json'], $groupsList, $_POST['mark']);
			if ($nbGroup >= 0) {
				$_reload_browser = true;
				show_groups(sprintf($lang['strgroupsconfimported'], $nbGroup, $_POST['file']));
			} else {
				show_groups('', sprintf($lang['strgroupsconfimporterr'], $_POST['file']));
			}
		} else {
			// there are errors to report to the user

			$misc->printHeader('database', 'database','emajgroups');

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

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";
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
		recheckGroups($_REQUEST['group'], $errMsgAction, 'IDLE', $_REQUEST['back']);

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strstartagroup']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p>" . sprintf($lang['strconfirmstartgroup'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strinitmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"mark\" required pattern=\"\S+.*\" placeholder='{$lang['strrequiredfield']}' autocomplete=\"off\"/></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=checkbox name=\"resetlog\" checked/>{$lang['stroldlogsdeletion']}</p>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"start_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstartgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_POST['group'], $errMsgAction, 'IDLE', $_POST['back']);

		// Check the supplied mark is valid for the group
		$finalMarkName = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction, $_POST['back'], !isset($_POST['resetlog']));

		// OK, perform the action
		$status = $emajdb->startGroup($_POST['group'], $finalMarkName, isset($_POST['resetlog']));

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, $_POST['back']);
		if ($_POST['back']=='list')
			show_groups(sprintf($lang['strstartgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
		else
			show_group(sprintf($lang['strstartgroupok'], htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
	}

	/**
	 * Prepare start groups: enter the initial mark name and confirm
	 */
	function start_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['strnoselectedgroup']);
			return;
		}

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
		recheckGroups($groupsList, $errMsgAction, 'IDLE', 'list');

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		$misc->printHeader('database', 'database','emajgroups');
		$misc->printTitle($lang['strstartgroups']);

		// Send the form
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
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
		processCancelButton('list');

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstartgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check that all groups exist and are in IDLE state
		recheckGroups($_POST['groups'], $errMsgAction, 'IDLE', 'list');

		// Check the supplied mark is valid for the groups
		$finalMarkName = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction, $_POST['back'], !isset($_POST['resetlog']));

		// OK, perform the action
		$status = $emajdb->startGroups($_POST['groups'], $finalMarkName, isset($_POST['resetlog']));

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, 'list');
		show_groups(sprintf($lang['strstartgroupsok'], htmlspecialchars($_POST['groups']), htmlspecialchars($finalMarkName)));
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_group() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstopgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING', $_REQUEST['back']);

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strstopagroup']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p>", sprintf($lang['strconfirmstopgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strstopmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamehelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<p><input type=checkbox name=\"forcestop\" />{$lang['strforcestop']}</p>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"stop_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstopgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_POST['group'], $errMsgAction, 'LOGGING', $_POST['back']);

		// Check the supplied mark is valid for the group
		$finalMarkName = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction, $_POST['back'], !isset($_POST['forcestop']));

		// OK, perform the action
		$status = $emajdb->stopGroup($_POST['group'], $finalMarkName, isset($_POST['forcestop']));

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, $_POST['back']);
		if ($_POST['back']=='list')
			show_groups(sprintf($lang['strstopgroupok'], htmlspecialchars($_POST['group'])));
		else
			show_group(sprintf($lang['strstopgroupok'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare stop group: ask for confirmation
	 */
	function stop_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['strnoselectedgroup']);
			return;
		}

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
		recheckGroups($groupsList, $errMsgAction, 'LOGGING', 'list');

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['strstopgroups']);

		// Send form
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p>", sprintf($lang['strconfirmstopgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strstopmark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"mark\" size=\"32\" value=\"STOP_%\" /></div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmarknamemultihelp']}\"/></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"stop_groups_ok\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton('list');

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strstopgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check that all groups exist and are in LOGGING state
		recheckGroups($_POST['groups'], $errMsgAction, 'LOGGING', 'list');

		// Check the supplied mark is valid for the groups
		$finalMarkName = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction, $_POST['back']);

		// OK, perform the action
		$status = $emajdb->stopGroups($_POST['groups'], $finalMarkName);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, 'list');
		show_groups(sprintf($lang['strstopgroupsok'], htmlspecialchars($_POST['groups'])));
	}

	/**
	 * Prepare reset group: ask for confirmation
	 */
	function reset_group() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strresetgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'IDLE', $_REQUEST['back']);

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strresetagroup']);

		echo "<p>", sprintf($lang['strconfirmresetgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"reset_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strresetgrouperr'], htmlspecialchars($_POST['group']));

		// Check the group still exists and is in IDLE state
		recheckGroups($_POST['group'], $errMsgAction, 'IDLE', $_POST['back']);

		// OK, perform the action
		$status = $emajdb->resetGroup($_POST['group']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, $_POST['back']);
		if ($_POST['back']=='list')
			show_groups(sprintf($lang['strresetgroupok'], htmlspecialchars($_POST['group'])));
		else
			show_group(sprintf($lang['strresetgroupok'], htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare reset groups: ask for confirmation
	 */
	function reset_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['strnoselectedgroup']);
			return;
		}

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
		recheckGroups($groupsList, $errMsgAction, 'IDLE', 'list');

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['strresetgroups']);

		// Send the form
		echo "<p>", sprintf($lang['strconfirmresetgroups'], htmlspecialchars($groupsList)), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
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
		processCancelButton('list');

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strresetgroupserr'], htmlspecialchars($_POST['groups']));

		// Check that all groups exist and are in IDLE state
		recheckGroups($_POST['groups'], $errMsgAction, 'IDLE', 'list');

		// OK, perform the action
		$status = $emajdb->resetGroups($_POST['groups']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, 'list');
		show_groups(sprintf($lang['strresetgroupsok'], htmlspecialchars($_POST['groups'])));
	}

	/**
	 * Execute protect group (there is no confirmation to ask)
	 */
	function protect_group() {
		global $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strprotectgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING', $_REQUEST['back']);

		// OK, perform the action
		$status = $emajdb->protectGroup($_REQUEST['group']);
		if ($status == 0)
			if ($_REQUEST['back'] == 'list') {
				show_groups(sprintf($lang['strprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			} else {
				show_group(sprintf($lang['strprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
			}
		else
			if ($_REQUEST['back'] == 'list') {
				show_groups('', $errMsgAction);
			} else {
				show_group('', $errMsgAction);
			}
	}

	/**
	 * Execute protect groups (there is no confirmation to ask)
	 */
	function protect_groups() {
		global $lang, $emajdb;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['strnoselectedgroup']);
			return;
		}

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
		recheckGroups($groupsList, $errMsgAction, 'LOGGING', 'list');

		// OK, perform the action
		$nbGroup = $emajdb->protectGroups($groupsList);
		if ($nbGroup >= 0)
			show_groups(sprintf($lang['strprotectgroupsok'], htmlspecialchars($groupsList)));
		else
			show_groups('', $errMsgAction);
	}

	/**
	 * Execute unprotect group (there is no confirmation to ask)
	 */
	function unprotect_group() {
		global $lang, $emajdb;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strunprotectgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING', $_REQUEST['back']);

		// OK, perform the action
		$status = $emajdb->unprotectGroup($_REQUEST['group']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, $_REQUEST['back']);
		if ($_REQUEST['back'] == 'list')
			show_groups(sprintf($lang['strunprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
		else
			show_group(sprintf($lang['strunprotectgroupok'], htmlspecialchars($_REQUEST['group'])));
	}

	/**
	 * Execute unprotect groups (there is no confirmation to ask)
	 */
	function unprotect_groups() {
		global $lang, $emajdb;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['strnoselectedgroup']);
			return;
		}

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
		recheckGroups($groupsList, $errMsgAction, 'LOGGING', 'list');

		// OK, perform the action
		$nbGroup = $emajdb->unprotectGroups($groupsList);
		if ($nbGroup >= 0)
			show_groups(sprintf($lang['strunprotectgroupsok'], htmlspecialchars($groupsList)));
		else
			show_groups('', $errMsgAction);
	}

	/**
	 * Prepare set mark group: ask for the mark name and confirmation
	 */
	function set_mark_group() {
		global $misc, $lang;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strsetmarkgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING', $_REQUEST['back']);

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strsetamark']);
		echo "<p>", sprintf($lang['strconfirmsetmarkgroup'], htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";

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
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strsetmarkgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_POST['group'], $errMsgAction, 'LOGGING', $_POST['back']);

		// Check the supplied mark is valid for the group
		$finalMarkName = checkNewMarkGroups($_POST['group'], $_POST['mark'], $errMsgAction, $_POST['back']);

		// OK, perform the action
		$status = $emajdb->setMarkGroup($_POST['group'],$finalMarkName,$_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, $_REQUEST['back']);
		if ($_POST['back']=='list')
			show_groups(sprintf($lang['strsetmarkgroupok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
		else
			show_group(sprintf($lang['strsetmarkgroupok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['group'])));
	}

	/**
	 * Prepare set mark groups: ask for the mark name and confirmation
	 */
	function set_mark_groups() {
		global $misc, $lang;

		// if no group has been selected, stop
		if (!isset($_REQUEST['ma'])) {
			show_groups('',$lang['strnoselectedgroup']);
			return;
		}

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
		recheckGroups($groupsList, $errMsgAction, 'LOGGING', 'list');

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['strsetamark']);

		if (!isset($_POST['mark'])) $_POST['mark'] = '';

		echo "<p>", sprintf($lang['strconfirmsetmarkgroups'], htmlspecialchars($groupsList)), "</p>\n";
		// send form
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";

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
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton('list');

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strsetmarkgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check that all groups exist and are in LOGGING state
		recheckGroups($_POST['groups'], $errMsgAction, 'LOGGING', 'list');

		// Check the supplied mark is valid for the groups
		$finalMarkName = checkNewMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction, $_POST['back']);

		// OK, perform the action
		$status = $emajdb->setMarkGroups($_POST['groups'],$finalMarkName,$_POST['comment']);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction, 'list');
		show_groups(sprintf($lang['strsetmarkgroupsok'], htmlspecialchars($finalMarkName), htmlspecialchars($_POST['groups'])));
	}

	/**
	 * Prepare rollback group: ask for confirmation
	 */
	function rollback_group($estimatedDuration = null) {
		global $misc, $lang, $emajdb, $conf;

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrlbkgrouperr'], htmlspecialchars($_REQUEST['group']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_REQUEST['group'], $errMsgAction, 'LOGGING', $_REQUEST['back']);

		// If the mark is already defined, check the mark still exists for the group
		if ($_REQUEST['back'] != 'list') {
			recheckMarksGroup($_REQUEST['group'], $_REQUEST['mark'], $errMsgAction);
		}

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strrlbkagroup']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_group_confirm_alter\" />\n";

		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
		if ($_REQUEST['back'] == 'list') {
		// the mark name is not yet defined (we are coming from the 'list groups' page)
			$marks=$emajdb->getRollbackMarkGroup($_REQUEST['group']);
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages, depending on the real performed action
		if (isset($_POST['estimaterollbackduration']))
			$errMsgAction = sprintf($lang['strestimrlbkgrouperr'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));
		else
			$errMsgAction = sprintf($lang['strrlbkgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_POST['group'], $errMsgAction, 'LOGGING', $_POST['back']);

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
			if ($_POST['back'] == 'list') {
				show_groups('', $errMsgAction . '<br>' . sprintf($lang['strgroupprotected'], htmlspecialchars($_POST['group'])));
			} else {
				show_group('', $errMsgAction . '<br>' . sprintf($lang['strgroupprotected'], htmlspecialchars($_POST['group'])));
			}
			return;
		}

		// Check the mark is always valid for a rollback
		if (!$emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
			if ($_POST['back'] == 'list') {
				show_groups('', $errMsgAction . '<br>' . sprintf($lang['strcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['mark'])));
			} else {
				show_group('', $errMsgAction . '<br>' . sprintf($lang['strcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['mark'])));
			}
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

			if ($_REQUEST['back'] == 'list')
				$misc->printHeader('database', 'database','emajgroups');
			else
				$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

			$misc->printTitle($lang['strrlbkagroup']);

			echo "<p>" . sprintf($lang['strreachaltergroup'], htmlspecialchars($_REQUEST['group']), htmlspecialchars($_REQUEST['mark'])) . "</p>\n";

			$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"rollback_group_ok\" />\n";
			echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
			echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton($_POST['back']);

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrlbkgrouperr2'], htmlspecialchars($_POST['group']), htmlspecialchars($_POST['mark']));

		// Check the group still exists and is in LOGGING state
		recheckGroups($_POST['group'], $errMsgAction, 'LOGGING', $_POST['back']);

		// Check the mark still exists for the group
		recheckMarksGroup($_POST['group'], $_POST['mark'], $errMsgAction);

		// Check the group is still ROLLBACKABLE (i.e. not protected)
		$group = $emajdb->getGroup($_POST['group']);
		if ($group->fields['group_type'] != 'ROLLBACKABLE') {
			if ($_POST['back']=='list') {
				show_groups('', $errMsgAction . '<br>' . sprintf($lang['strgroupprotected'], htmlspecialchars($_POST['group'])));
			} else {
				show_group('', $errMsgAction . '<br>' . sprintf($lang['strgroupprotected'], htmlspecialchars($_POST['group'])));
			}
			return;
		}

		// Check the mark is always valid for a rollback
		if (!$emajdb->isRollbackMarkValidGroup($_POST['group'],$_POST['mark'])) {
			if ($_POST['back']=='list') {
				show_groups('', $errMsgAction . '<br>' . sprintf($lang['strcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['mark'])));
			} else {
				show_group('', $errMsgAction . '<br>' . sprintf($lang['strcantrlbkinvalidmarkgroup'], htmlspecialchars($_POST['mark'])));
			}
			return;
		}

		if (isset($_POST['async'])) {
		// perform the rollback in asynchronous mode, if possible, and switch to the rollback monitoring page

			if (!$emajdb->isAsyncRlbkUsable(false)) {
				if ($_POST['back']=='list') {
					show_groups('', sprintf($lang['strbadconfparam'], $conf['psql_path'], $conf['temp_dir']));
				} else {
					show_group('', sprintf($lang['strbadconfparam'], $conf['psql_path'], $conf['temp_dir']));
				}
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

		if ($_REQUEST['back'] == 'list')
			$misc->printHeader('database', 'database','emajgroups');
		else
			$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

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

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		if ($_POST['back']=='list') {
			echo "<input type=\"hidden\" name=\"action\" value=\"show_groups\" />\n";
		} else {
			echo "<input type=\"hidden\" name=\"action\" value=\"show_group\" />\n";
		}
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
			if (!isset($_REQUEST['ma'])) {
				show_groups('',$lang['strnoselectedgroup']);
				return;
			}

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
		recheckGroups($groupsList, $errMsgAction, 'LOGGING', 'list');

		// if at least one selected group is protected, stop
		$protectedGroups = $emajdb->getProtectedGroups($groupsList);
		if ($protectedGroups->fields['nb_groups'] > 0) {
			if ($protectedGroups->fields['nb_groups'] == 1)
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupprotected'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			else
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsprotected'], $protectedGroups->fields['nb_groups'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			return;
		}

		// look for marks common to all selected groups
		$marks=$emajdb->getRollbackMarkGroups($groupsList);
		// if no mark is usable for all selected groups, stop
		if ($marks->recordCount()==0) {
			show_groups('',sprintf($lang['strnomarkgroups'], htmlspecialchars($groupsList)));
			return;
		}
		// get the youngest timestamp protected mark for all groups
		$youngestProtectedMarkTimestamp=$emajdb->getYoungestProtectedMarkTimestamp($groupsList);

		$misc->printHeader('database', 'database','emajgroups');

		$misc->printTitle($lang['strrlbkgroups']);

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<p><input type=\"hidden\" name=\"action\" value=\"rollback_groups_confirm_alter\" />\n";
		echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($groupsList), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton('list');

		// Prepare the action part of potential error messages, depending on the real performed action
		if (isset($_POST['estimaterollbackduration']))
			$errMsgAction = sprintf($lang['strestimrlbkgroupserr'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));
		else
			$errMsgAction = sprintf($lang['strrlbkgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check the groups still exist and are in LOGGING state
		recheckGroups($_POST['groups'], $errMsgAction, 'LOGGING', 'list');

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
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupprotected'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			else
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsprotected'], $protectedGroups->fields['nb_groups'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			return;
		}

		// Check the mark is always valid
		if (!$emajdb->isRollbackMarkValidGroups($_POST['groups'],$_POST['mark'])) {
			show_groups('', sprintf($lang['strcantrlbkinvalidmarkgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
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

			$misc->printHeader('database', 'database','emajgroups');

			$misc->printTitle($lang['strrlbkgroups']);

			echo "<p>" . sprintf($lang['strreachaltergroups'], htmlspecialchars($_REQUEST['groups']), htmlspecialchars($_REQUEST['mark'])) . "</p>\n";
			$misc->printTable($alterGroupSteps, $columns, $actions, 'alterGroupStep');

			echo "<form action=\"emajgroups.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"rollback_groups_ok\" />\n";
			echo "<input type=\"hidden\" name=\"groups\" value=\"", htmlspecialchars($_REQUEST['groups']), "\" />\n";
			echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
			echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton('list');

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrlbkgroupserr2'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark']));

		// Check the groups still exist and are in LOGGING state
		recheckGroups($_POST['groups'], $errMsgAction, 'LOGGING', 'list');

		// Check the mark still exists for the groups
		recheckMarkGroups($_POST['groups'], $_POST['mark'], $errMsgAction);

		// if at least one selected group is protected, stop
		$protectedGroups=$emajdb->getProtectedGroups($_POST['groups']);
		if ($protectedGroups->fields['nb_groups'] > 0) {
			if ($protectedGroups->fields['nb_groups'] == 1)
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupprotected'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			else
				show_groups('', $errMsgAction . '<br>' .
					sprintf($lang['strgroupsprotected'], $protectedGroups->fields['nb_groups'], htmlspecialchars($protectedGroups->fields['groups_list'])));
			return;
		}

		// Check the mark is always valid
		if (!$emajdb->isRollbackMarkValidGroups($_POST['groups'], $_POST['mark'])) {
			show_groups('', sprintf($lang['strcantrlbkinvalidmarkgroups'], htmlspecialchars($_POST['groups']), htmlspecialchars($_POST['mark'])));
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

		$misc->printHeader('database', 'database','emajgroups');

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

		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
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
		checkADOReturnCode($status, $errMsgAction, 'detail');
		show_group(sprintf($lang['strprotectmarkok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
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
		show_group(sprintf($lang['strunprotectmarkok'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])));
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

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strcommentamark']);

		$mark = $emajdb->getMark($_REQUEST['group'],$_REQUEST['mark']);

		echo "<p>", sprintf($lang['strcommentmark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strcomment']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"comment\" size=\"80\" value=\"", htmlspecialchars($mark->fields['mark_comment']), "\" /></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"comment_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton('detail');

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
		show_group(sprintf($lang['strcommentmarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
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

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strrenameamark']);

		echo "<p>", sprintf($lang['strconfirmrenamemark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<div class=\"form-container\">\n";
		echo "\t<div class=\"form-label required\">{$lang['strnewnamemark']}</div>\n";
		echo "\t<div class=\"form-input\"><input name=\"newmark\" size=\"32\" value=\"", htmlspecialchars($_POST['mark']), "\" id=\"newmark\" required pattern=\"\S+.*\" placeholder='{$lang['strrequiredfield']}' autocomplete=\"off\"/ ></div>\n";
		echo "\t<div class=\"form-comment\"></div>\n";
		echo "</div>\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"rename_mark_group_ok\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton('detail');

		// Prepare the action part of potential error messages
		$errMsgAction = sprintf($lang['strrenamemarkerr2'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']), htmlspecialchars($_POST['newmark']));

		// Check the group still exists
		recheckGroups($_POST['group'], $errMsgAction);

		// Check the mark still exists for the group
		recheckMarksGroup($_POST['group'], $_POST['mark'], $errMsgAction);

		// Check the supplied mark is valid for the group
		$finalMarkName = checkNewMarkGroups($_POST['group'], $_POST['newmark'], $errMsgAction, 'detail');

		// OK, perform the action
		$status = $emajdb->renameMarkGroup($_POST['group'],$_POST['mark'], $finalMarkName);

		// Check the result and exit
		checkADOReturnCode($status, $errMsgAction);
		show_group(sprintf($lang['strrenamemarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group']), htmlspecialchars($finalMarkName)));
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

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strdeleteamark']);

		echo "<p>", sprintf($lang['strconfirmdeletemark'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"delete_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton('detail');

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
		show_group(sprintf($lang['strdeletemarkok'], htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
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

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strdeletemarks']);

		echo "<p>", sprintf($lang['strconfirmdeletemarks'], $nbMarks, htmlspecialchars($_REQUEST['group'])), "</p>\n{$htmlList}\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
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
		processCancelButton('detail');

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
					show_group('', $errMsgAction . '<br>' . sprintf('Internal error on mark %s', htmlspecialchars($m)));
					return;
				}
			}
		}
		if ($data->endTransaction() == 0)
			show_group(sprintf($lang['strdeletemarksok'], count($marks), htmlspecialchars($_POST['group'])));
		else
			show_group('', sprintf($lang['strdeletemarkserr'], htmlspecialchars($_POST['marks']), htmlspecialchars($_POST['group'])));
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

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');

		$misc->printTitle($lang['strdelmarksprior']);

		echo "<p>", sprintf($lang['strconfirmdelmarksprior'], htmlspecialchars($_REQUEST['mark']), htmlspecialchars($_REQUEST['group'])), "</p>\n";
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"delete_before_mark_ok\" />\n";
		echo "<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "<input type=\"hidden\" name=\"mark\" value=\"", htmlspecialchars($_REQUEST['mark']), "\" />\n";
		echo "<input type=\"hidden\" name=\"back\" value=\"", htmlspecialchars($_REQUEST['back']), "\" />\n";
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
		processCancelButton('detail');

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
		show_group(sprintf($lang['strdelmarkspriorok'], $status, htmlspecialchars($_POST['mark']), htmlspecialchars($_POST['group'])));
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

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

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

	$misc->printHtmlHeader($lang['strgroupsmanagement']);
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
		case 'forget_group':
			forget_group();
			break;
		case 'forget_group_ok':
			forget_group_ok();
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
		case 'protect_group':
			protect_group();
			break;
		case 'protect_groups':
			protect_groups();
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
		case 'unprotect_groups':
			unprotect_groups();
			break;
		case 'unprotect_mark_group':
			unprotect_mark_group();
			break;
		default:
			if (isset($_REQUEST['group']))
				show_group();
			else
				show_groups();
	}

	$misc->printFooter();
?>
