<?php

	/**
	 * Alternative SQL editing window
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	/**
	 * Private function to display server and list of databases
	 */
	function _printConnection() {
		global $data, $action, $misc;
		
		// The javascript action on the select box reloads the
		// popup whenever the server or database is changed.
		// This ensures that the correct page encoding is used.
		$onchange = "onchange=\"location.href='sqledit.php?action=" . 
				urlencode($action) . "&amp;server=' + encodeURI(server.options[server.selectedIndex].value) + '&amp;database=' + encodeURI(database.options[database.selectedIndex].value) + ";
		
		// The exact URL to reload to is different between SQL and Find mode, however.
		if ($action == 'find') {
			$onchange .= "'&amp;term=' + encodeURI(term.value) + '&amp;filter=' + encodeURI(filter.value) + '&amp;'\"";
		} else {
			$onchange .= "'&amp;query=' + encodeURI(query.value) + '&amp;search_path=' + encodeURI(search_path.value) + (paginate.checked ? '&amp;paginate=on' : '')  + '&amp;'\"";
		}
		
		$misc->printConnection($onchange);
	}
	

	/**
	 * Allow execution of arbitrary SQL statements on a database
	 */
	function doDefault() {
		global $data, $misc;
		global $lang; 
		
		if (!isset($_SESSION['sqlquery'])) $_SESSION['sqlquery'] = '';
		
		$misc->printHtmlHeader($lang['strsql']);
		
		// Bring to the front always
		echo "<body onload=\"window.focus();\">\n";
		
		$misc->printTabs($misc->getNavTabs('popup'), 'sql');
		
		echo "<form action=\"sql.php\" method=\"post\" target=\"detail\">\n";
		_printConnection();
		echo "\n";
		if (!isset($_REQUEST['search_path']))
			$_REQUEST['search_path'] = implode(',',$data->getSearchPath());
		
		echo "<p><label>{$lang['strsearchpath']}: <input type=\"text\" name=\"search_path\" size=\"50\" value=\"",
			htmlspecialchars($_REQUEST['search_path']), "\" /></label></p>\n";
		
		echo "<textarea style=\"width:98%;\" rows=\"10\" cols=\"50\" name=\"query\">",
			htmlspecialchars($_SESSION['sqlquery']), "</textarea>\n";
		echo "<p><label for=\"paginate\"><input type=\"checkbox\" id=\"paginate\" name=\"paginate\"", (isset($_REQUEST['paginate']) ? ' checked="checked"' : ''), " />&nbsp;{$lang['strpaginate']}</label></p>\n";
		
		echo "<p><input type=\"submit\" value=\"{$lang['strexecute']}\" />\n";
		echo "<input type=\"reset\" value=\"{$lang['strreset']}\" /></p>\n";
		echo "</form>\n";
		
		// Default focus
		$misc->setFocus('forms[0].query');
	}

	switch ($action) {
		case 'sql':
		default:
			doDefault();
			break;
	}
	
	// Set the name of the window
	$misc->setWindowName('sqledit');
	
	$misc->printFooter();
	
?>
