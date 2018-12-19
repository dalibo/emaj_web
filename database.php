<?php

	/*
	 * Manage schemas within a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';
	$scripts = '';

	function _highlight($string, $term) {
		return str_replace($term, "<b>{$term}</b>", $string);
	}	

	function doTree() {
		global $misc, $data, $lang;

		$reqvars = $misc->getRequestVars('database');

		$tabs = $misc->getNavTabs('database');

		$items = $misc->adjustTabsForTree($tabs);

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
						),
		);
		
		$misc->printTree($items, $attrs, 'database');

		exit;
	}

	/* shortcuts: these functions exit the script */
	if ($action == 'tree') doTree();

	$misc->printHeader($lang['strdatabase'], $scripts);
	$misc->printBody();

	switch ($action) {
		default:
			break;
	}

	$misc->printFooter();
?>
