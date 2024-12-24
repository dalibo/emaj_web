<?php

	/*
	 * Manage servers
	 */

	// Include application functions
	$_no_db_connection = true;
	include_once('./libraries/lib.inc.php');
	
	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';
	
	function doLogout() {
		global $misc, $lang, $_reload_browser;

		$server_info = $misc->getServerInfo($_REQUEST['logoutServer']);
		$misc->setServerInfo(null, null, $_REQUEST['logoutServer']);

		unset($_SESSION['sharedUsername'], $_SESSION['sharedPassword']);

		doDefault(sprintf($lang['strlogoutmsg'], $server_info['desc']));

		$_reload_browser = true;
	}

	function doDefault($msg = '') {
		global $conf, $misc;
		global $lang;
		
		$misc->printHeader('root', 'root', 'servers');
		$misc->printMsg($msg);
		$group = isset($_GET['group']) ? $_GET['group'] : false;

		$groups = $misc->getServersGroups(true, $group);

		if ($groups->recordCount() > 0) {

			if (($group !== false) and (isset($conf['srv_groups'][$group])))
				$misc->printTitle(sprintf($lang['strgroupgroups'], htmlentities($conf['srv_groups'][$group]['desc'], ENT_QUOTES, 'UTF-8')), $misc->buildTitleRecordsCounter($groups));
			else
				$misc->printTitle($lang['strserversgroups'], $misc->buildTitleRecordsCounter($groups));

			$columns = array(
				'group' => array(
					'title' => $lang['strgroup'],
					'field' => field('desc'),
					'url' => 'servers.php?',
					'vars' => array('group' => 'id'),
				),
			);
			$actions = array();

			$misc->printTable($groups, $columns, $actions, 'serversgroups');
		}

		$servers = $misc->getServers(true, $group);
		
		function svPre(&$rowdata, $actions) {
			$actions['logout']['disable'] = empty($rowdata->fields['username']);
			return $actions;
		}
		
		$columns = array(
			'server' => array(
				'title' => $lang['strserver'],
				'field' => field('desc'),
				'url'   => "redirect.php?subject=server&amp;",
				'vars'  => array('server' => 'id'),
			),
			'host' => array(
				'title' => $lang['strhost'],
				'field' => field('host'),
			),
			'port' => array(
				'title' => $lang['strport'],
				'field' => field('port'),
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('comment'),
				'type' => 'spanned',
				'params'=> array(
						'cliplen' => 25,
						'class' => 'tooltip right-aligned-tooltip',
						),
			),
			'username' => array(
				'title' => $lang['strusername'],
				'field' => field('username'),
			),
			'actions' => array(
				'title' => $lang['stractions'],
			),
		);

		$actions = array(
			'logout' => array(
				'content' => $lang['strlogout'],
				'icon' => 'Logout',
				'attr'=> array (
					'href' => array (
						'url' => 'servers.php',
						'urlvars' => array (
							'action' => 'logout',
							'logoutServer' => field('id')
						)
					)
				)
			),
		);

		if (($group !== false) and isset($conf['srv_groups'][$group])) {
			$misc->printTitle(sprintf($lang['strgroupservers'],htmlentities($conf['srv_groups'][$group]['desc'], ENT_QUOTES, 'UTF-8')), $misc->buildTitleRecordsCounter($servers));
			$actions['logout']['attr']['href']['urlvars']['group'] = $group;
		} else
			$misc->printTitle($lang['strconfiguredservers'], $misc->buildTitleRecordsCounter($servers));

		$misc->printTable($servers, $columns, $actions, 'servers', $lang['strnoobjects'], 'svPre', array('sorter' => true, 'filter' => true));
	}
	
	function doTree() {
		global $misc, $conf;

		$nodes = array();
		$group_id = isset($_GET['group']) ? $_GET['group'] : false;

		if (isset($conf['srv_groups']) and count($conf['srv_groups']) > 0 and $group_id === false) {
			/* root with servers groups */
			$nodes = $misc->getServersGroups(true);

		} elseif (isset($conf['srv_groups']) and $group_id !== false) {
			/* group subtree */
				if ($group_id !== 'all')
					$nodes = $misc->getServersGroups(false, $group_id);
				$nodes = array_merge($nodes, $misc->getServers(false, $group_id));
				include_once('./classes/ArrayRecordSet.php');
				$nodes = new ArrayRecordSet($nodes);
		}

		else {
			/* no servers group */
			$nodes = $misc->getServers(true, false);
		}
		
		$reqvars = $misc->getRequestVars('server');
		
		$attrs = array(
			'text'   => field('desc'),
			
			// Show different icons for logged in/out
			'icon'   => field('icon'),
			
			'toolTip'=> field('id'),
			
			'action' => field('action'),

			// Only create a branch url if the user has
			// logged into the server.
			'branch' => field('branch'),
		);
		
		$misc->printTree($nodes, $attrs, 'servers');
		exit;
	}


	if ($action == 'tree') {
		if (isset($_GET['group']))
			doTree($_GET['group']);
		else
			doTree(false);
	}

	$misc->printHtmlHeader($lang['strservers']);
	$misc->printBody();

	switch ($action) {
		case 'logout':
			doLogout();
			break;
		default:
			doDefault($msg);
			break;
	}

	$misc->printFooter();
?>
