<?php

	/**
	 * Alternative SQL editing window
	 *
	 * $Id: history.php,v 1.3 2008/01/10 19:37:07 xzilla Exp $
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

	function doDefault() {
		global $misc, $lang;

		$onchange = "onchange=\"location.href='history.php?server=' + encodeURI(server.options[server.selectedIndex].value) + '&amp;database=' + encodeURI(database.options[database.selectedIndex].value) + '&amp;'\"";

		$misc->printHtmlHeader($lang['strhistory']);
		
		// Bring to the front always
		echo "<body onload=\"window.focus();\">\n";
	
		$misc->printTitle($lang['strsqlhistory']);

		echo "<form action=\"history.php\" method=\"post\">\n";
		$misc->printConnection($onchange);
		echo "</form><br />";
	
		if (!isset($_REQUEST['database'])) {
			echo "<p>{$lang['strnodatabaseselected']}</p>\n";
			return;
		}
			
		if (isset($_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']])) {
			include_once('classes/ArrayRecordSet.php');
						   
			$history = new ArrayRecordSet($_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']]);
			
			$columns = array(
				'query' => array(
					'title' => $lang['strsql'],
					'field' => field('query'),
				),
				'paginate' => array(
					'title' => $lang['strpaginate'],
					'field' => field('paginate'),
					'type' => 'yesno',
				),
				'actions' => array(
					'title' => $lang['stractions'],
				),
			);

			$actions = array(
				'run' => array(
					'content' => $lang['strexecute'],
					'icon' => 'Start',
					'attr'=> array (
						'href' => array (
							'url' => 'sql.php',
							'urlvars' => array (
								'subject' => 'history',
								'nohistory' => 't',
								'queryid' => field('queryid'),
								'paginate' => field('paginate')
							)
						),
						'target' => 'detail'
					)
				),
				'remove' => array(
					'content' => $lang['strdelete'],
					'icon' => 'Bin',
					'attr'=> array (
						'href' => array (
							'url' => 'history.php',
							'urlvars' => array (
								'action' => 'confdelhistory',
								'queryid' => field('queryid'),
							)
						)
					)
				)
			);

			$misc->printTable($history, $columns, $actions, 'history-history', $lang['strnohistory']);
		}
		else echo "<p>{$lang['strnohistory']}</p>\n";

		$dbInUrl = "server=" . urlencode($_REQUEST['server']) . "&amp;database=" . urlencode($_REQUEST['database']);
		echo "<div class=\"actionslist\">\n";
		echo "  <form id=\"refresh_form\" action=\"history.php?action=history&amp;{$dbInUrl}\" method=\"post\">\n";
		echo "    <input type=\"submit\" name=\"refresh\" value=\"{$lang['strrefresh']}\" />";
		echo "  </form>\n";

		if (isset($_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']]) 
				&& count($_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']])) {
			echo "  <form id=\"download_form\" action=\"history.php?action=download&amp;{$dbInUrl}\" method=\"post\">\n";
			echo "    <input type=\"submit\" name=\"download\" value=\"{$lang['strdownload']}\" />";
			echo "  </form>\n";

			echo "  <form id=\"clear_form\" action=\"history.php?action=confclearhistory&amp;{$dbInUrl}\" method=\"post\">\n";
			echo "    <input type=\"submit\" name=\"clear\" value=\"{$lang['strclearhistory']}\" />";
			echo "  </form>\n";
		}

		echo "</div>\n";
	}

	function doDelHistory($qid, $confirm) {
		global $misc, $lang;

		if ($confirm) {
			$misc->printHtmlHeader($lang['strhistory']);

        		// Bring to the front always
	        	echo "<body onload=\"window.focus();\">\n";
			
			$misc->printTitle($lang['strdelhistory']);
			echo "<p>{$lang['strconfdelhistory']}</p>\n";

			echo "<pre>", htmlentities($_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']][$qid]['query'], ENT_QUOTES, 'UTF-8'), "</pre>";
			echo "<form action=\"history.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"delhistory\" />\n";
			echo "<input type=\"hidden\" name=\"queryid\" value=\"$qid\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"yes\" value=\"{$lang['stryes']}\" />\n";
			echo "<input type=\"submit\" name=\"no\" value=\"{$lang['strno']}\" />\n";
			echo "</form>\n";
		}
		else
			unset($_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']][$qid]);
	}       

	function doClearHistory($confirm) {
		global $misc, $lang;

		if ($confirm) {
			$misc->printHtmlHeader($lang['strhistory']);

        		// Bring to the front always
	        	echo "<body onload=\"window.focus();\">\n";

			$misc->printTitle($lang['strclearhistory']);
			echo "<p>{$lang['strconfclearhistory']}</p>\n";

			echo "<form action=\"history.php\" method=\"post\">\n";
			echo "<input type=\"hidden\" name=\"action\" value=\"clearhistory\" />\n";
			echo $misc->form;
			echo "<input type=\"submit\" name=\"yes\" value=\"{$lang['stryes']}\" />\n";
			echo "<input type=\"submit\" name=\"no\" value=\"{$lang['strno']}\" />\n";
			echo "</form>\n";
		}
		else
			unset($_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']]);
	}

	function doDownloadHistory() {
		header('Content-Type: application/download');
		$datetime = date('YmdHis');
		header("Content-Disposition: attachment; filename=history{$datetime}.sql");

		foreach ($_SESSION['history'][$_REQUEST['server']][$_REQUEST['database']] as $queries) {
			$query = rtrim($queries['query']);
			echo $query;
			if (substr($query, -1) != ';')
				echo ';';
			echo "\n";
		}

		exit;
	}
	
	switch ($action) {
		case 'confdelhistory':
			doDelHistory($_REQUEST['queryid'], true);
			break;
		case 'delhistory':
			if (isset($_POST['yes'])) doDelHistory($_REQUEST['queryid'], false);
			doDefault();
			break;
		case 'confclearhistory':
			doClearHistory(true);
			break;
		case 'clearhistory':
			if (isset($_POST['yes'])) doClearHistory(false);
			doDefault();
			break;
		case 'download':
			doDownloadHistory();
			break;
		default:
			doDefault();
	}

	// Set the name of the window
	$misc->setWindowName('history');
	// Do not print the bottom link
	$misc->printFooter(true, false);
	
?>
