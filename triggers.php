<?php

	/*
	 * Manage application triggers in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	/**
	 * Show the list of triggers in the database
	 */
	function showTriggers($msg = '') {
		global $emajdb, $misc, $lang;

		$misc->printHeader('database', 'database', 'triggers');

		$misc->printMsg($msg);
		$misc->printTitle("{$lang['emajapptriggers']}<img src=\"{$misc->icon('Info-inv')}\" alt=\"info\" title=\"{$lang['emajapptriggershelp']}\"/>");

		$urlvars = $misc->getRequestVars();

		// Get triggers
		$triggers = $emajdb->getAppTriggers();

		$columns = array(
			'schema' => array(
				'title' => $lang['strschema'],
				'field' => field('nspname'),
			),
			'table' => array(
				'title' => $lang['strtable'],
				'field' => field('relname'),
				'url'   => "tblproperties.php?{$misc->href}&amp;",
				'vars'  => array('schema' => 'nspname', 'table' => 'relname'),
			),
			'tgname' => array(
				'title' => $lang['strtrigger'],
				'field' => field('tgname'),
			),
			'tglevel' => array(
				'title' => $lang['strlevel'],
				'field' => field('tglevel'),
				'params'=> array('align' => 'center'),
			),
			'tgevent' => array(
				'title' => $lang['emajtriggeringevent'],
				'field' => field('tgevent'),
			),
			'tgfnct' => array(
				'title' => $lang['emajcalledfunction'],
				'field' => field('tgfnct'),
			),
			'tgenabled' => array(
				'title' => $lang['emajstate'],
				'field' => field('tgstate'),
			),
		);
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
				$columns = array_merge($columns, array(
					'emajisautodisable' => array(
						'title' => $lang['emajisautodisable'],
						'field' => field('tgisautodisable'),
						'info'  => $lang['emajisautodisablehelp'],
						'type'	=> 'yesno',
					),
					'actions' => array(
						'title' => $lang['stractions'],
					),
				));
			}
		}

		$actions = array();
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
				if ($emajdb->isEmaj_Adm()) {
					$actions = array_merge($actions, array(
						'multiactions' => array(
							'keycols' => array(
								'schema' => 'nspname',
								'table' => 'relname',
								'trigger' => 'tgname',
								'tgisdisableauto' => 'tgisautodisable',
							),
							'url' => 'triggers.php',
						),
						'doSwitchIgnoredAppTriggerState' => array(
							'content' => $lang['emajswitchautodisable'],
							'attr' => array (
								'href' => array (
									'url' => 'triggers.php',
									'urlvars' => array_merge($urlvars, array (
										'action' => 'switch_ignore_app_trigger_state',
										'schema' => field('nspname'),
										'table' => field('relname'),
										'trigger' => field('tgname'),
										'tgisdisableauto' => field('tgisautodisable'),
									)))),
							'multiaction' => 'switch_ignore_app_trigger_states',
							)
						)
					);
				}
			}
		}

		$misc->printTable($triggers, $columns, $actions, 'triggers-triggers', $lang['strnoapptrigger'], null, array('sorter' => true, 'filter' => true));

		// Check if orphan triggers exist in the emaj_ignored_app_trigger table
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
				$orphanTriggers = $emajdb->getOrphanAppTriggers();

				if (!$orphanTriggers->EOF) {
					print "<p>{$lang['emajorphantriggersexist']}</p>";
					$columns = array(
						'schema' => array(
							'title' => $lang['strschema'],
							'field' => field('trg_schema'),
						),
						'table' => array(
							'title' => $lang['strtable'],
							'field' => field('trg_table'),
						),
						'tgname' => array(
							'title' => $lang['strtrigger'],
							'field' => field('trg_name'),
						),
						'actions' => array(
							'title' => $lang['stractions'],
						),
					);
	
					$actions = array(
						'multiactions' => array(
							'keycols' => array(
								'schema' => 'trg_schema',
								'table' => 'trg_table',
								'trigger' => 'trg_name',
							),
							'url' => 'triggers.php',
						),
						'removetrigger' => array(
							'content' => $lang['emajremove'],
							'icon' => 'Remove',
							'attr' => array (
								'href' => array (
									'url' => 'triggers.php',
									'urlvars' => array_merge($urlvars, array (
										'action' => 'remove_trigger',
										'schema' => field('trg_schema'),
										'table' => field('trg_table'),
										'trigger' => field('trg_name'),
									)))),
							'multiaction' => 'remove_triggers',
						),
					);
	
					$misc->printTable($orphanTriggers, $columns, $actions, 'triggers-orphantriggers', null, null, array('sorter' => true, 'filter' => true));
				}
			}
		}
	}

	/**
	 * Switch the ignored_app_trigger state for the selected required trigger.
	 * Then show the updated table's properties
	 */
	function doSwitchIgnoredAppTriggerState() {
		global $lang, $emajdb;

		if ($_REQUEST['tgisdisableauto'] == 't') {
			// the trigger is currently NOT set as 'not to be automatically disabled at rollback', so set it
			$action = 'ADD';
		} else {
			// the trigger is currently set as 'not to be automatically disabled at rollback', so unset it
			$action = 'REMOVE';
		}
		$nbTriggers = $emajdb->ignoreAppTrigger($action, $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['trigger']);

		if ($nbTriggers > 0) {
			showTriggers(sprintf($lang['emajtriggerpropswitchedok'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		} else {
			showTriggers(sprintf($lang['emajtriggerprocerr'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		}
	}

	/**
	 * Switch the keep_enabled_trigger state for the selected triggers.
	 * Then show the updated table's properties
	 */
	function doSwitchIgnoredAppTriggerStates() {
		global $lang, $data, $emajdb;

		if (!isset($_REQUEST['ma'])) {
			showTriggers('',$lang['emajnoselectedtriggers']);
			return;
		}

		$nbTriggers = 0;
		$status = $data->beginTransaction();
		if ($status == 0) {
			foreach($_REQUEST['ma'] as $v) {
				$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
				if ($a['tgisdisableauto'] == 't') {
					// the trigger is currently NOT set as 'not to be automatically disabled at rollback', so set it
					$action = 'ADD';
				} else {
					// the trigger is currently set as 'not to be automatically disabled at rollback', so unset it
					$action = 'REMOVE';
				}
				$status = $emajdb->ignoreAppTrigger($action, $a['schema'], $a['table'], $a['trigger']);
				if ($status = 0) {
					$data->rollbackTransaction();
					showTriggers(sprintf($lang['emajtriggerprocerr'], htmlspecialchars($a['trigger']), htmlspecialchars($a['schema']), htmlspecialchars($a['table'])));
					return;
				}
				$nbTriggers++;
			}
		}
		if($data->endTransaction() == 0)
		showTriggers(sprintf($lang['emajtriggerspropswitchedok'], $nbTriggers));
	}

	/**
	 * Remove an orphan trigger from the emaj_ignored_app_trigger table
	 */
	function doRemoveTrigger() {
		global $lang, $emajdb;

		$nbTriggers = $emajdb->ignoreAppTrigger('REMOVE', $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['trigger']);

		if ($nbTriggers > 0) {
			showTriggers(sprintf($lang['emajtriggersremovedok'], $nbTriggers));
		} else {
			showTriggers(sprintf($lang['emajtriggerprocerr'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		}
	}

	/**
	 * Remove several orphan triggers from the emaj_ignored_app_trigger table
	 */
	function doRemoveTriggers() {
		global $lang, $data, $emajdb;

		if (!isset($_REQUEST['ma'])) {
			showTriggers('',$lang['emajnoselectedtriggers']);
			return;
		}

		$nbTriggers = 0;
		$status = $data->beginTransaction();
		if ($status == 0) {
			foreach($_REQUEST['ma'] as $v) {
				$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
				$status = $emajdb->ignoreAppTrigger('REMOVE', $a['schema'], $a['table'], $a['trigger']);
				if ($status == 0) {
					$data->rollbackTransaction();
					showTriggers(sprintf($lang['emajtriggerprocerr'], htmlspecialchars($a['trigger']), htmlspecialchars($a['schema']), htmlspecialchars($a['table'])));
					return;
				}
				$nbTriggers++;
			}
		}
		if($data->endTransaction() == 0)
		showTriggers(sprintf($lang['emajtriggersremovedok'], $nbTriggers));
	}

	$misc->printHtmlHeader($lang['strtriggers']);
	$misc->printBody();

	if (isset($_POST['cancel'])) $action = '';

	switch ($action) {
		case 'remove_trigger':
			doRemoveTrigger();
			break;
		case 'remove_triggers':
			doRemoveTriggers();
			break;
		case 'switch_ignore_app_trigger_state':
			doSwitchIgnoredAppTriggerState();
			break;
		case 'switch_ignore_app_trigger_states':
			doSwitchIgnoredAppTriggerStates();
			break;
		default:
			showTriggers();
			break;
	}

	$misc->printFooter();

?>
