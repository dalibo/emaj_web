<?php
	/*
	 * Manage the E-Maj tables groups
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

		$misc->printHeader('database', 'database', 'emajgroups');
		return;
	}

	function groupDropped($msg = '', $errMsgAction = '', $errMsg = '') {

		if ($errMsg == '')
			// A group has been properly dropped.
			doDefault($msg);
		else
			// An action on a group is impossible because it does not exist anymore.
			doDefault($msg, $errMsgAction . '<br>' . $errMsg);
		return;
	}

/********************************************************************************************************
 * Callback functions (not shared with groupproperties.php)
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

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Show list of created emaj groups
	 */
	function doDefault($msg = '', $errMsg = '') {
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
				'url'   => "groupproperties.php?&amp;{$misc->href}&amp;",
				'vars'  => array('group' => 'group_name'),
			),
			'actions' => array(
				'title' => $lang['stractions'],
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
				'actions' => array(
					'title' => $lang['stractions'],
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
				'latestrollbackable' => array(
					'title' => $lang['strgrouplatesttype'],
					'field' => field('latest_is_rollbackable'),
					'type'	=> 'callback',
					'params'=> array(
							'function' => 'renderGroupType',
							'align' => 'center',
							),
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

	/* shortcuts: these functions exit the script */
	if ($action == 'tree')
		doTree();

	// The export_groups_ok action only builds and downloads the configuration file, but do not resend the main page
	if ($action == 'export_groups_ok') {
		export_groups_ok();
		exit;
	}

	// Check that emaj still exists
	$misc->onErrorRedirect('emaj');

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
		case 'show_groups':
			doDefault();
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
		default:
			doDefault();
	}

	$misc->printFooter();
?>
