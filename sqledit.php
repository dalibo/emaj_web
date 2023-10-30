<?php

	/**
	 * SQL editing window
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
	 * Ask for the options in the sql generation for changes dumps
	 */
	function gen_sql_dump_changes() {
		global $data, $misc, $emajdb;
		global $lang;

		echo "<div id=\"flex-container\">";

		$misc->printTitle($lang['emajsqlgentitle']);

		// Check whether the table has a PK
		$hasPk = $emajdb->hasTablePk($_REQUEST['schema'], $_REQUEST['table']);
		// Get the E-Maj columns list for the related log table
		$emajCols = $emajdb->getEmajColumns($_REQUEST['group'], $_REQUEST['schema'], $_REQUEST['table'], $_REQUEST['startMark'], $_REQUEST['startTs']);

		echo "<div>\n";
		echo "<p>{$lang['strtable']} = \"<b>" . htmlspecialchars($_REQUEST['schema']) . "." . htmlspecialchars($_REQUEST['table']) . "</b>\"<br>\n";
		echo "{$lang['emajtablesgroup']} = \"<b>" . htmlspecialchars($_REQUEST['group']) . "</b>\"<br>\n";
		echo "{$lang['emajsqlgenmarksinterval']} = \"<b>" . htmlspecialchars($_REQUEST['startMark']) . "</b>\" - ";
		if ($_REQUEST['endMark'] != '')
			echo "\"<b>" . htmlspecialchars($_REQUEST['endMark']) . "</b>\"</p>\n";
		else
			echo " {$lang['emajcurrentsituation']}</p>\n";

		if (! $hasPk)
			echo "<p><img src=\"{$misc->icon('Warning')}\" alt=\"Warning\" style=\"width: 20px;\"/> {$lang['emajsqlgennopk']}</p>\n";

		echo "<form action=\"sqledit.php\" method=\"post\">\n";
		echo "\t<p><input type=\"hidden\" name=\"action\" value=\"gen_sql_dump_changes_ok\" />\n";
		echo $misc->form;
		echo "\t<input type=\"hidden\" name=\"group\" value=\"", htmlspecialchars($_REQUEST['group']), "\" />\n";
		echo "\t<input type=\"hidden\" name=\"schema\" value=\"", htmlspecialchars($_REQUEST['schema']), "\" />\n";
		echo "\t<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\" />\n";
		echo "\t<input type=\"hidden\" name=\"startMark\" value=\"", htmlspecialchars($_REQUEST['startMark']), "\" />\n";
		echo "\t<input type=\"hidden\" name=\"startTs\" value=\"", htmlspecialchars($_REQUEST['startTs']), "\" />\n";
		echo "\t<input type=\"hidden\" name=\"endMark\" value=\"", htmlspecialchars($_REQUEST['endMark']), "\" />\n";
		echo "\t<input type=\"hidden\" name=\"endTs\" value=\"", htmlspecialchars($_REQUEST['endTs']), "\" />\n";

		echo "<div class=\"form-container\">\n";

		// Radio button for the consolidation level
		echo "\t<div class=\"form-label\">{$lang['emajsqlgenconsolidation']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "\t\t<input type=\"radio\" name=\"consolidation\" value=\"NONE\" onclick=\"validationSelect('NONE');\" checked />";
		echo "<label for=\"NONE\">{$lang['emajsqlgenconsonone']}</label> \n";
		echo "\t\t<input type=\"radio\" name=\"consolidation\" value=\"PARTIAL\" onclick=\"validationSelect('PARTIAL');\" />";
		echo "<label for=\"PARTIAL\">{$lang['emajsqlgenconsopartial']}</label> \n";
		echo "\t\t<input type=\"radio\" name=\"consolidation\" value=\"FULL\" onclick=\"validationSelect('FULL');\" />";
		echo "<label for=\"FULL\">{$lang['emajsqlgenconsofull']}</label>\n";
		echo "\t</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajsqlgenconsohelp']}\"/></div>\n";

		// Check box for SQL verbs selection
		echo "\t<div class=\"form-label\">{$lang['emajsqlgenverbs']}</div>\n";
		echo "\t<div class=\"form-input\">";
		$check = ($_REQUEST['verb'] == '' || $_REQUEST['verb'] == 'INSERT') ? 'checked' : '';
		echo "\t\t<input type=\"checkbox\" name=\"verbs[]\" value=\"INS\" $check /><label for=\"INS\">INSERT</label> \n";
		$check = ($_REQUEST['verb'] == '' || $_REQUEST['verb'] == 'UPDATE') ? 'checked' : '';
		echo "\t\t<input type=\"checkbox\" name=\"verbs[]\" value=\"UPD\" $check /><label for=\"UPD\">UPDATE</label> \n";
		$check = ($_REQUEST['verb'] == '' || $_REQUEST['verb'] == 'DELETE') ? 'checked' : '';
		echo "\t\t<input type=\"checkbox\" name=\"verbs[]\" value=\"DEL\" $check /><label for=\"DEL\">DELETE</label> \n";
		$check = ($_REQUEST['verb'] == '' || $_REQUEST['verb'] == 'TRUNCATE') ? 'checked' : '';
		echo "\t\t<input type=\"checkbox\" name=\"verbs[]\" value=\"TRU\" $check /><label for=\"TRU\">TRUNCATE</label>\n";
		echo "\t\t<br>\n";
		echo "\t\t&nbsp;<a id=\"allVerbs\" onclick=\"javascript:allVerbs();\" class=\"action\">{$lang['strall']}</a>\n";
		echo "\t</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajsqlgenverbshelp']}\"/></div>\n";

		// Text field for roles selection
		echo "\t<div class=\"form-label\">{$lang['strroles']}</div>\n";
		echo "\t<div class=\"form-input\" style=\"width:400px\">";
		echo "\t\t<input type=\"text\" name=\"roles\" value=\"" . htmlspecialchars($_REQUEST['role']) . "\"/><br>\n";
		if ($_REQUEST['knownRoles'] != '') {
			if (strlen($_REQUEST['knownRoles']) > 100) {
				echo "<div class=\"tooltip right-aligned-tooltip\" style=\"white-space: normal\">{$lang['emajsqlgenknownroles']} " . htmlspecialchars(substr($_REQUEST['knownRoles'], 0, 100) . $lang['strellipsis']);
				echo "<span>" . htmlspecialchars($_REQUEST['knownRoles']) . "</span></div>";
			} else {
				echo "{$lang['emajsqlgenknownroles']} " . htmlspecialchars($_REQUEST['knownRoles']);
			}
			echo "\t\t<br>\n";
		}
		if ($_REQUEST['knownRoles'] != '') {
			echo "\t\t&nbsp;<a id=\"allRoles\" onclick=\"javascript:setRoles('{$_REQUEST['knownRoles']}');\" class=\"action\">{$lang['strall']}</a>&nbsp;\n";
		}
		echo "\t\t&nbsp;<a id=\"clearRoles\" onclick=\"javascript:setRoles();\" class=\"action\">{$lang['strdelete']}</a>\n";
		echo "\t</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajsqlgenroleshelp']}\"/></div>\n";

		// Check box for E-Maj technical columns, with 3 columns per line
		echo "\t<div class=\"form-label\">{$lang['emajsqlgentechcols']}</div>\n";
		echo "\t<div class=\"form-input\">";
		$nbCol = 0;
		foreach($emajCols as $r) {
			$nbCol = $nbCol + 1;
			$col = htmlspecialchars($r['attname']);
			echo "\t\t<input type=\"checkbox\" name=\"emajCols[]\" value=\"$col\" checked/><label for=\"$col\">$col</label> \n";
			if ($nbCol % 3 == 0)
				echo "\t\t<br>\n";
		}
		echo "\t\t<br>\n";
		echo "\t\t&nbsp;<a onclick=\"javascript:emajColsSelect('ALL');\" class=\"action\">{$lang['strall']}</a>&nbsp;\n";
		echo "\t\t&nbsp;<a onclick=\"javascript:emajColsSelect('MIN');\" class=\"action\">Minimum</a>\n";
		echo "\t</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajsqlgentechcolshelp']}\"/></div>\n";

		// Radio button for the columns order
		echo "\t<div class=\"form-label\">{$lang['emajsqlgencolsorder']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "\t\t<input type=\"radio\" name=\"colsOrder\" value=\"LOG_TABLE\" checked /><label for=\"LOG_TABLE\">{$lang['emajsqlgencolsorderlog']}</label>";
		echo "\t\t<input type=\"radio\" name=\"colsOrder\" value=\"PK\" /><label for=\"PK\">{$lang['emajsqlgencolsorderpk']}</label>";
		echo "\t</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajsqlgencolsorderhelp']}\"/></div>\n";

		// Radio button for the rows order
		echo "\t<div class=\"form-label\">{$lang['emajsqlgenroworder']}</div>\n";
		echo "\t<div class=\"form-input\">";
		echo "\t\t<input type=\"radio\" name=\"orderBy\" value=\"TIME\" checked /><label for=\"TIME\">{$lang['emajsqlgenrowordertime']}</label>";
		echo "\t\t<input type=\"radio\" name=\"orderBy\" value=\"PK\" /><label for=\"PK\">{$lang['strpk']}</label>";
		echo "\t</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['emajsqlgenroworderhelp']}\"/></div>\n";

		echo "</div>\n";

		echo "<div class=\"actionslist\">\n";
		echo "\t<input type=\"submit\" value=\"{$lang['emajsqlgenerate']}\" />\n";
		echo "\t<input type=\"reset\" value=\"{$lang['strreset']}\" />\n";
		echo "</div>\n";

		echo "</form>\n";
		echo "</div>\n";

		echo "</div>\n";

		// Initialize the options setting
		echo "<script>initOptions({$hasPk});</script>\n";
	}

	/**
	 * Generate the SQL statement to dump changes and display it into the SQL editor
	 */
	function gen_sql_dump_changes_ok() {
		global $emajdb;

		// Prepare variables
		$consolidation = (isset($_POST['consolidation'])) ? $_POST['consolidation'] : 'NONE';
		$emajColumnsList = implode(",", $_POST['emajCols']);
		$colsOrder = (isset($_POST['colsOrder'])) ? $_POST['colsOrder'] : 'LOG_TABLE';
		$orderBy = (isset($_POST['orderBy'])) ? $_POST['orderBy'] : 'TIME';

		// And call the emaj function that generates the sql
		$sql_text = $emajdb->genSqlDumpChanges($_POST['group'], $_POST['schema'], $_POST['table'],
											   $_POST['startMark'], $_POST['startTs'], $_POST['endMark'], $_POST['endTs'],
											   $consolidation, $emajColumnsList, $colsOrder, $orderBy);

		// Process the sql verbs filter if any
		if (isset($_POST['verbs']) && count($_POST['verbs']) < 4) {
			$verbsList = "'" . implode("','", $_POST['verbs']) . "'";
			$sql_text = str_replace("  ORDER BY", "    AND emaj_verb IN ($verbsList)\n  ORDER BY", $sql_text);
		}

		// Process the roles filter if any
		if (isset($_POST['roles']) && $_POST['roles'] != '') {
			$rolesList = "'" . preg_replace('/\s*,\s*/', "', '", $_POST['roles']) . "'";
			$sql_text = str_replace("  ORDER BY", "    AND emaj_user IN ($rolesList)\n  ORDER BY", $sql_text);
		}

		// Call the sql editor with the suggested statement
		$_SESSION['sqlquery'] = $sql_text;
		$_REQUEST['paginate'] = '';
		sql_editor();
	}


	/**
	 * Allow execution of arbitrary SQL statements on a database
	 */
	function sql_editor() {
		global $data, $misc;
		global $lang; 
		
		if (!isset($_SESSION['sqlquery'])) $_SESSION['sqlquery'] = '';

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
		echo "\t<textarea name=\"query\">" . htmlspecialchars($_SESSION['sqlquery']), "</textarea>\n";
		echo "</div>\n";

		echo "<div id=\"last-block\">\n";
		echo "\t<p><label for=\"paginate\"><input type=\"checkbox\" id=\"paginate\" name=\"paginate\"", (isset($_REQUEST['paginate']) ? ' checked="checked"' : ''), " />&nbsp;{$lang['strpaginate']}</label></p>\n";

		echo "  <div class=\"actionslist\">\n";
		echo "\t<input type=\"submit\" value=\"{$lang['strexecute']}\" />\n";
		echo "\t<input type=\"reset\" value=\"{$lang['strreset']}\" />\n";
		echo "\t</form>\n";
		echo "  </div>\n";
		echo "</div>\n";

		// Default focus
		$misc->setFocus('forms[0].query');

		echo "</div>\n";
	}

	$scripts = "<script src=\"js/sqledit.js\"></script>";
	$misc->printHtmlHeader($lang['strsql'], $scripts, 'sqledit');

	// Bring to the front always
	echo "<body onload=\"window.focus();\">\n";

	switch ($action) {
		case 'gen_sql_dump_changes':
			gen_sql_dump_changes();
			break;
		case 'gen_sql_dump_changes_ok':
			gen_sql_dump_changes_ok();
			break;
		case 'sql':
		default:
			sql_editor();
			break;
	}

	// Set the name of the window
	$misc->setWindowName('sqledit');

	// Do not print the bottom link
	$misc->printFooter(true, false);
	
?>
