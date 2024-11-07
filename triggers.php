<?php

	/*
	 * Manage application triggers in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Functions to modify dynamicaly actions list for each application trigger to display
	function appTriggerPre(&$rowdata, $actions) {
		// disable the noAutoDisableTrigger or the autoDisableTriggerbutton depending on the current state
		if ($rowdata->fields('tgisautodisable') == 't') {
			$actions['autoDisableTrigger']['disable'] = true;
		} elseif ($rowdata->fields('tgisautodisable') == 'f') {
			$actions['noAutoDisableTrigger']['disable'] = true;
		} else {
			$actions['autoDisableTrigger']['disable'] = true;
			$actions['noAutoDisableTrigger']['disable'] = true;
		}
		return $actions;
	}

	/**
	 * Show the list of triggers in the database
	 */
	function showTriggers($msg = '') {
		global $emajdb, $misc, $lang;

		$misc->printHeader('database', 'database', 'triggers');

		$misc->printMsg($msg);

		// Get triggers
		$triggers = $emajdb->getAppTriggers();

		$urlvars = $misc->getRequestVars();

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
				'title' => $lang['strtriggeringevent'],
				'field' => field('tgevent'),
			),
			'tgfnct' => array(
				'title' => $lang['strcalledfunction'],
				'field' => field('tgfnct'),
			),
			'tgenabled' => array(
				'title' => $lang['strstate'],
				'field' => field('tgstate'),
			),
		);
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
				$columns = array_merge($columns, array(
					'emajisautodisable' => array(
						'title' => $lang['strisautodisable'],
						'field' => field('tgisautodisable'),
						'info'  => $lang['strisautodisablehelp'],
						'params'=> array(
							'map' => array('t' => 'ON', 'f' => 'OFF'),
							'align' => 'center'
						),
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
						'noAutoDisableTrigger' => array(
							'content' => 'Manuel',
							'icon' => 'Off',
							'attr' => array (
								'href' => array (
									'url' => 'triggers.php',
									'urlvars' => array_merge($urlvars, array (
										'action' => 'no_auto_disable_trigger',
										'schema' => field('nspname'),
										'table' => field('relname'),
										'trigger' => field('tgname'),
									)))),
							'multiaction' => 'no_auto_disable_triggers',
						),
						'autoDisableTrigger' => array(
							'content' => 'Auto',
							'icon' => 'On',
							'attr' => array (
								'href' => array (
									'url' => 'triggers.php',
									'urlvars' => array_merge($urlvars, array (
										'action' => 'auto_disable_trigger',
										'schema' => field('nspname'),
										'table' => field('relname'),
										'trigger' => field('tgname'),
									)))),
							'multiaction' => 'auto_disable_triggers',
						),
					));
				}
			}
		}

		$misc->printTitle($lang['strapptriggers'], $misc->buildTitleRecordsCounter($triggers), $lang['strapptriggershelp']);

		$misc->printTable($triggers, $columns, $actions, 'triggers-triggers', $lang['strnoapptrigger'], 'appTriggerPre', array('sorter' => true, 'filter' => true));

		// Check if orphan triggers exist in the emaj_ignored_app_trigger table
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {
			if ($emajdb->getNumEmajVersion() >= 30100) {			// version >= 3.1.0
				$orphanTriggers = $emajdb->getOrphanAppTriggers();

				if (!$orphanTriggers->EOF) {
					print "<p>{$lang['strorphantriggersexist']}</p>";
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
							'content' => $lang['strremove'],
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
	 * Register the selected trigger as 'not to be automatically disabled at rollback'
	 * Then show the updated triggers properties
	 */
	function noAutoDisableTrigger() {
		global $lang, $emajdb;

		$nbTriggers = $emajdb->ignoreAppTrigger('ADD', $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['trigger']);

		if ($nbTriggers > 0) {
			showTriggers(sprintf($lang['strtriggernoautook'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		} else {
			showTriggers('',sprintf($lang['strtriggerprocerr'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		}
	}

	/**
	 * Register the selected trigger as 'to be automatically disabled at rollback'
	 * Then show the updated triggers properties
	 */
	function autoDisableTrigger() {
		global $lang, $emajdb;

		$nbTriggers = $emajdb->ignoreAppTrigger('REMOVE', $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['trigger']);

		if ($nbTriggers > 0) {
			showTriggers(sprintf($lang['strtriggerautook'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		} else {
			showTriggers('',sprintf($lang['strtriggerprocerr'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		}
	}

	/**
	 * Register the selected triggers as 'not to be automatically disabled at rollback'
	 * Then show the updated triggers properties
	 */
	function noAutoDisableTriggers() {
		global $lang, $data, $emajdb;

		if (!isset($_REQUEST['ma'])) {
			showTriggers('',$lang['strnoselectedtriggers']);
			return;
		}

		$nbTriggers = 0;
		$status = $data->beginTransaction();
		if ($status == 0) {
			foreach($_REQUEST['ma'] as $v) {
				$a = unserialize(htmlspecialchars_decode($v, ENT_QUOTES));
				$status = $emajdb->ignoreAppTrigger('ADD', $a['schema'], $a['table'], $a['trigger']);
				if ($status == 0) {
					$data->rollbackTransaction();
					showTriggers(sprintf($lang['strtriggerprocerr'], htmlspecialchars($a['trigger']), htmlspecialchars($a['schema']), htmlspecialchars($a['table'])));
					return;
				}
				$nbTriggers++;
			}
		}
		if ($data->endTransaction() == 0)
			showTriggers(sprintf($lang['strtriggersnoautook'], $nbTriggers));
	}

	/**
	 * Register the selected triggers as 'to be automatically disabled at rollback'
	 * Then show the updated triggers properties
	 */
	function autoDisableTriggers() {
		global $lang, $data, $emajdb;

		if (!isset($_REQUEST['ma'])) {
			showTriggers('',$lang['strnoselectedtriggers']);
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
					showTriggers(sprintf($lang['strtriggerprocerr'], htmlspecialchars($a['trigger']), htmlspecialchars($a['schema']), htmlspecialchars($a['table'])));
					return;
				}
				$nbTriggers++;
			}
		}
		if ($data->endTransaction() == 0)
			showTriggers(sprintf($lang['strtriggersautook'], $nbTriggers));
	}

	/**
	 * Remove an orphan trigger from the emaj_ignored_app_trigger table
	 */
	function doRemoveTrigger() {
		global $lang, $emajdb;

		$nbTriggers = $emajdb->ignoreAppTrigger('REMOVE', $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['trigger']);

		if ($nbTriggers > 0) {
			showTriggers(sprintf($lang['strtriggersremovedok'], $nbTriggers));
		} else {
			showTriggers(sprintf($lang['strtriggerprocerr'], htmlspecialchars($_REQUEST['trigger']), $_REQUEST['schema'], $_REQUEST['table']));
		}
	}

	/**
	 * Remove several orphan triggers from the emaj_ignored_app_trigger table
	 */
	function doRemoveTriggers() {
		global $lang, $data, $emajdb;

		if (!isset($_REQUEST['ma'])) {
			showTriggers('',$lang['strnoselectedtriggers']);
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
					showTriggers(sprintf($lang['strtriggerprocerr'], htmlspecialchars($a['trigger']), htmlspecialchars($a['schema']), htmlspecialchars($a['table'])));
					return;
				}
				$nbTriggers++;
			}
		}
		if($data->endTransaction() == 0)
		showTriggers(sprintf($lang['strtriggersremovedok'], $nbTriggers));
	}

	$misc->printHtmlHeader($lang['strtriggers']);
	$misc->printBody();

	if (isset($_POST['cancel'])) $action = '';

	switch ($action) {
		case 'auto_disable_trigger':
			autoDisableTrigger();
			break;
		case 'auto_disable_triggers':
			autoDisableTriggers();
			break;
		case 'no_auto_disable_trigger':
			noAutoDisableTrigger();
			break;
		case 'no_auto_disable_triggers':
			noAutoDisableTriggers();
			break;
		case 'remove_trigger':
			doRemoveTrigger();
			break;
		case 'remove_triggers':
			doRemoveTriggers();
			break;
		default:
			showTriggers();
			break;
	}

	$misc->printFooter();

?>
