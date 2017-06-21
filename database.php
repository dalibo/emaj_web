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

	/**
	 * Allow execution of arbitrary SQL statements on a database
	 */
	function doSQL() {
		global $data, $misc;
		global $lang;

		if ((!isset($_SESSION['sqlquery'])) || isset($_REQUEST['new'])) {
			$_SESSION['sqlquery'] = '';
			$_REQUEST['paginate'] = 'on';
		}

		$misc->printTrail('database');
		$misc->printTabs('database','sql');
		echo "<p>{$lang['strentersql']}</p>\n";
		echo "<form action=\"sql.php\" method=\"post\" enctype=\"multipart/form-data\">\n";
		echo "<p>{$lang['strsql']}<br />\n";
		echo "<textarea style=\"width:100%;\" rows=\"20\" cols=\"50\" name=\"query\">",
			htmlspecialchars($_SESSION['sqlquery']), "</textarea></p>\n";

		// Check that file uploads are enabled
		if (ini_get('file_uploads')) {
			// Don't show upload option if max size of uploads is zero
			$max_size = $misc->inisizeToBytes(ini_get('upload_max_filesize'));
			if (is_double($max_size) && $max_size > 0) {
				echo "<p><input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"{$max_size}\" />\n";
				echo "<label for=\"script\">{$lang['struploadscript']}</label> <input id=\"script\" name=\"script\" type=\"file\" /></p>\n";
			}
		}

		echo "<p><input type=\"checkbox\" id=\"paginate\" name=\"paginate\"", (isset($_REQUEST['paginate']) ? ' checked="checked"' : ''), " /><label for=\"paginate\">{$lang['strpaginate']}</label></p>\n";
		echo "<p><input type=\"submit\" name=\"execute\" value=\"{$lang['strexecute']}\" />\n";
		echo $misc->form;
		echo "<input type=\"reset\" value=\"{$lang['strreset']}\" /></p>\n";
		echo "</form>\n";

		// Default focus
		$misc->setFocus('forms[0].query');
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
	if ($action == 'refresh_locks') currentLocks(true);
	if ($action == 'refresh_processes') currentProcesses(true);

	$misc->printHeader($lang['strdatabase'], $scripts);
	$misc->printBody();

	switch ($action) {
		case 'sql':
			doSQL();
			break;
		default:
			if (adminActions($action, 'database') === false) doSQL();
			break;
	}

	$misc->printFooter();
?>
