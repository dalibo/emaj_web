<?php

	/*
	 * Manage databases within a server
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	/**
	 * Show default list of databases in the server
	 */
	function doDefault() {
		global $data, $conf, $misc;
		global $lang;

		$misc->printHeader('server', 'server', 'databases');

		$databases = $data->getDatabases();

		$misc->printTitle($lang['strdatabaseslist'], $misc->buildTitleRecordsCounter($databases));

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

		$misc->printTable($databases, $columns, $actions, 'databases-databases', $lang['strnodatabases'],null, array('sorter' => true, 'filter' => true));

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

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

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
