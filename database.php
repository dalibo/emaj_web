<?php

	/*
	 * Manage schemas within a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

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
			'branch' => ifempty(field('branch'), '',
							url(field('url'),
								$reqvars,
								field('urlvars'),
								array('action' => 'tree')
							)),
		);

		$misc->printTree($items, $attrs, 'database');

		exit;
	}

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	/* shortcuts: these functions exit the script */
	if ($action == 'tree') doTree();

	$misc->printHtmlHeader($lang['strdatabase']);
	$misc->printBody();

	switch ($action) {
		default:
			break;
	}

	$misc->printFooter();
?>
