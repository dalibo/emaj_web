<?php
	/*
	 * Display the properties and marks of a single table group.
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');
	include_once('./libraries/groupcommon.inc.php');
	include_once('./libraries/groupactions.inc.php');

/********************************************************************************************************
 * Functions called by groupactions.inc.php functions, but whose content is different between
 * emajgroups.php and groupproperties.php.
 *******************************************************************************************************/

	function printHeader() {
		global $misc;

		$misc->printHeader('emaj', 'emajgroup','emajgroupproperties');
		return;
	}

	function groupDropped($msg = '', $errMsgAction = '', $errMsg = '') {
		global $misc, $lang;

		printHeader();

		if ($errMsg == '') {
			// A group has been properly dropped.
			$misc->printTitle($lang['strdropagroup']);
			$misc->printMsg($msg);
		} else {
			// An action on a group is impossible because it does not exist anymore.
			$misc->printTitle($errMsgAction);
			$misc->printMsg('', $errMsg);
		}

		// OK button to go back to the groups list.
		echo "<form action=\"emajgroups.php\" method=\"post\">\n";
		echo "<input type=\"hidden\" name=\"action\" value=\"\" />\n";
		echo $misc->form;
		echo "<div class=\"actionslist\">";
		echo "\t<input type=\"submit\" name=\"dropgroups\" value=\"{$lang['strok']}\" />\n";
		echo "</div></form>\n";
		return;
	}

/********************************************************************************************************
 * Callback functions (not shared with emajgroups.php)
 *******************************************************************************************************/

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

	// Callback function to dynamicaly modify the mark position in log session column content
	// The value is composed of 3 parts: the position of the mark in its log session and the log session start and stop times
	function renderLogSession($val) {
		global $misc, $lang;

		$parts = explode('#', $val);
		if ($parts[0] == '')
			$parts[0] = 'Straight';
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

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Displays all detailed information about one group, including marks
	 */
	function doDefault($msg = '', $errMsg = '') {
		global $misc, $lang, $emajdb, $_reload_browser;

		if (! $emajdb->existsGroup($_REQUEST['group'])) {
			groupDropped('', sprintf($lang['strgroupmissing'], htmlspecialchars($_REQUEST['group'])));
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
							'url' => "groupproperties.php",
							'urlvars' => array(
								'action' => 'start_group',
								'group' => $_REQUEST['group'],
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
							'url' => "groupproperties.php",
							'urlvars' => array(
								'action' => 'set_mark_group',
								'group' => $_REQUEST['group'],
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
							'url' => "groupproperties.php",
							'urlvars' => array(
								'action' => 'reset_group',
								'group' => $_REQUEST['group'],
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
							'url' => "groupproperties.php",
							'urlvars' => array(
								'action' => 'protect_group',
								'group' => $_REQUEST['group'],
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
							'url' => "groupproperties.php",
							'urlvars' => array(
								'action' => 'unprotect_group',
								'group' => $_REQUEST['group'],
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
							'url' => "groupproperties.php",
							'urlvars' => array(
								'action' => 'stop_group',
								'group' => $_REQUEST['group'],
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
						'url' => "groupproperties.php",
						'urlvars' => array(
							'action' => 'comment_group',
							'group' => $_REQUEST['group'],
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
							'url' => "groupproperties.php",
							'urlvars' => array(
								'action' => 'alter_group',
								'group' => $_REQUEST['group'],
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
							'url' => "groupproperties.php",
							'urlvars' => array(
								'action' => 'drop_group',
								'group' => $_REQUEST['group'],
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
			'actions' => array(
				'title' => $lang['stractions'],
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
			'state' => array(
				'title' => ($emajdb->getNumEmajVersion() >= 40400) ?
								"<img src=\"{$misc->icon('EmajPadlock')}\"/ title=\"{$lang['strprotectedmarkindicator']}\"> ?" :
								$lang['strstate'],
				'field' => field('mark_state'),
				'type'	=> 'callback',
				'params'=> array('function' => 'renderMarkState', 'align' => 'center'),
				'filter'=> false,
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
					'url' => "groupproperties.php?group={$_REQUEST['group']}&amp;",
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
							'url' => 'groupproperties.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'rollback_group',
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
							'url' => 'groupproperties.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'protect_mark_group',
								'group' => field('mark_group'),
								'mark' => field('mark_name'),
							))))
				),
				'unprotectmark' => array(
					'content' => $lang['strunprotect'],
					'icon' => 'PadLockOff',
					'attr' => array (
						'href' => array (
							'url' => 'groupproperties.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'unprotect_mark_group',
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
							'url' => 'groupproperties.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'rename_mark_group',
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
							'url' => 'groupproperties.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'delete_mark',
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
							'url' => 'groupproperties.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'delete_before_mark',
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
							'url' => 'groupproperties.php',
							'urlvars' => array_merge($urlvars, array (
								'action' => 'comment_mark_group',
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
 * Main piece of code
 *******************************************************************************************************/

	// Check that emaj and the group still exist.
	$misc->onErrorRedirect('emajgroup');

	$misc->printHtmlHeader($lang['strgroupsmanagement']);
	$misc->printBody();

	switch ($action) {
		case 'alter_group':
			alter_group();
			break;
		case 'alter_group_ok':
			alter_group_ok();
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
		case 'drop_group_ok':
			drop_group_ok();
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
		case 'reset_group_ok':
			reset_group_ok();
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
		case 'set_mark_group':
			set_mark_group();
			break;
		case 'set_mark_group_ok':
			set_mark_group_ok();
			break;
		case 'show_group':
			doDefault();
			break;
		case 'start_group':
			start_group();
			break;
		case 'start_group_ok':
			start_group_ok();
			break;
		case 'stop_group':
			stop_group();
			break;
		case 'stop_group_ok':
			stop_group_ok();
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
			doDefault();
	}

	$misc->printFooter();
?>
