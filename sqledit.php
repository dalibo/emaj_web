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
		
		$misc->printHtmlHeader($lang['strsql'], null, 'sqledit');

		// Bring to the front always
		echo "<body onload=\"window.focus();\">\n";
		echo "<div id=\"flex-container\">";

		$misc->printTitle($lang['strsqledit']);

		echo "<div>\n";
		echo "\t<form action=\"sql.php\" method=\"post\" target=\"detail\">\n";
		_printConnection();
		echo "\n";
		if (!isset($_REQUEST['search_path']))
			$_REQUEST['search_path'] = implode(',',$data->getSearchPath());

		echo "\t<p><label>{$lang['strsearchpath']}:<br><input type=\"text\" name=\"search_path\" value=\"",
			htmlspecialchars($_REQUEST['search_path']), "\" /></label></p>\n";
		echo "</div>\n";

		// The SQL text area
		echo "<div class=\"flex-1\">\n";
		echo "\t<textarea name=\"query\">",
			htmlspecialchars($_SESSION['sqlquery']), "</textarea>\n";
		echo "</div>\n";

		echo "<div id=\"last-block\">\n";
		echo "\t<p><label for=\"paginate\"><input type=\"checkbox\" id=\"paginate\" name=\"paginate\"", (isset($_REQUEST['paginate']) ? ' checked="checked"' : ''), " />&nbsp;{$lang['strpaginate']}</label></p>\n";

		echo "\t<p><input type=\"submit\" value=\"{$lang['strexecute']}\" />\n";
		echo "\t<input type=\"reset\" value=\"{$lang['strreset']}\" /></p>\n";
		echo "\t</form>\n";
		echo "</div>\n";

		// Default focus
		$misc->setFocus('forms[0].query');

		echo "</div>\n";
	}

	switch ($action) {
		case 'sql':
		default:
			doDefault();
			break;
	}
	
	// Set the name of the window
	$misc->setWindowName('sqledit');

	// Do not print the bottom link
	$misc->printFooter(true, false);
	
?>
