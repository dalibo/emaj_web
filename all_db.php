<?php

	/*
	 * Manage databases within a server
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	/**
	 * Show default list of databases in the server
	 */
	function doDefault($msg = '') {
		global $data, $conf, $misc;
		global $lang;

		$misc->printHeader('server', 'server', 'databases');
		$misc->printMsg($msg);
		$misc->printTitle($lang['strdatabaseslist']);

		$databases = $data->getDatabases();

		$columns = array(
			'database' => array(
				'title' => $lang['strdatabase'],
				'field' => field('datname'),
				'url'   => "redirect.php?subject=database&amp;{$misc->href}&amp;",
				'vars'  => array('database' => 'datname'),
			),
			'owner' => array(
				'title' => $lang['strowner'],
				'field' => field('datowner'),
			),
			'encoding' => array(
				'title' => $lang['strencoding'],
				'field' => field('datencoding'),
			),
			'dbsize' => array(
				'title' => $lang['strsize'],
				'field' => field('dbsize'),
				'type' => 'prettysize',
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('datcomment'),
				'type' => 'spanned',
				'params'=> array(
						'cliplen' => 32,
						'class' => 'tooltip right-aligned-tooltip',
						),
			),
		);

		$actions = array();

		if (!$data->hasServerAdminFuncs()) unset($columns['dbsize']);

		$misc->printTable($databases, $columns, $actions, 'all_db-databases', $lang['strnodatabases'],null, array('sorter' => true, 'filter' => true));

	}

	function doTree() {
		global $misc, $data, $lang;

		$databases = $data->getDatabases();

		$reqvars = $misc->getRequestVars('database');

		$attrs = array(
			'text'   => field('datname'),
			'icon'   => 'Database',
			'toolTip'=> field('datcomment'),
			'action' => url('redirect.php',
							$reqvars,
							array('database' => field('datname'))
						),
			'branch' => url('database.php',
							$reqvars,
							array(
								'action' => 'tree',
								'database' => field('datname')
							)
						),
		);

		$misc->printTree($databases, $attrs, 'databases');
		exit;
	}

	if ($action == 'tree') doTree();

	$misc->printHtmlHeader($lang['strdatabases']);
	$misc->printBody();

	switch ($action) {
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
