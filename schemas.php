<?php

	/*
	 * Manage schemas in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	/**
	 * Show default list of schemas in the database
	 */
	function doDefault($msg = '') {
		global $data, $misc, $conf;
		global $lang;

		$misc->printHeader('database', 'database', 'schemas');
		$misc->printMsg($msg);
		$misc->printTitle($lang['strallschemas']);

		// Check that the DB actually supports schemas
		$schemas = $data->getSchemas();

		$columns = array(
			'schema' => array(
				'title' => $lang['strschema'],
				'field' => field('nspname'),
				'url'   => "redirect.php?subject=schema&amp;{$misc->href}&amp;",
				'vars'  => array('schema' => 'nspname'),
			),
			'owner' => array(
				'title' => $lang['strowner'],
				'field' => field('nspowner'),
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('nspcomment'),
			),
		);


		$misc->printTable($schemas, $columns, $actions, 'schemas-schemas', $lang['strnoschemas']);

	}

	/**
	 * Generate XML for the browser tree.
	 */
	function doTree() {
		global $misc, $data, $lang;

		$schemas = $data->getSchemas();

		$reqvars = $misc->getRequestVars('schema');

		$attrs = array(
			'text'   => field('nspname'),
			'icon'   => 'Schema',
			'toolTip'=> field('nspcomment'),
			'action' => url('redirect.php',
							$reqvars,
							array(
								'subject' => 'schema',
								'schema'  => field('nspname')
							)
						),
			'branch' => url('schemas.php',
							$reqvars,
							array(
								'action'  => 'subtree',
								'schema'  => field('nspname')
							)
						),
		);

		$misc->printTree($schemas, $attrs, 'schemas');

		exit;
	}

	function doSubTree() {
		global $misc, $data, $lang;

		$tabs = $misc->getNavTabs('schema');

		$items = $misc->adjustTabsForTree($tabs);

		$reqvars = $misc->getRequestVars('schema');

		$attrs = array(
			'text'   => field('title'),
			'icon'   => field('icon'),
			'action' => url(field('url'),
							$reqvars,
							field('urlvars', array())
						),
			'branch' => url(field('url'),
							$reqvars,
							field('urlvars'),
							array('action' => 'tree')
						)
		);

		$misc->printTree($items, $attrs, 'schema');
		exit;
	}

	if ($action == 'tree') doTree();
	if ($action == 'subtree') doSubTree();

	$misc->printHtmlHeader($lang['strschemas']);
	$misc->printBody();

	if (isset($_POST['cancel'])) $action = '';

	switch ($action) {
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
