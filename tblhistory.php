<?php

	/*
	 * Display the E-Maj history of a table, i.e. the tables group ownership and E-Maj properties changes.
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

	/**
	 * Show the table E-Maj history.
	 */
	function doDefault() {
		global $data, $conf, $misc, $lang, $emajdb;

		$misc->printHeader('table', 'table', 'history');

		// Display the E-Maj history.
		$misc->printTitle(sprintf($lang['stremajhistorytable'], $_REQUEST['schema'], $_REQUEST['table']));

		$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['table']);

		if ($type == 'L') {
			echo "<p>{$lang['stremajlogtable']}</p>\n";
		} elseif ($type == 'E') {
			echo "<p>{$lang['stremajinternaltable']}</p>\n";
		} elseif ($type == 'U') {
			echo "<p>{$lang['strnotassignabletable']}</p>\n";
		} else {
			$events = $emajdb->getTblSeqEmajHist($_REQUEST['schema'], $_REQUEST['table']);

			if ($events->recordCount() < 1) {
	
				// There is no history to display
				echo "<p>{$lang['strnoemajhistory']}</p>\n";
	
			} else {
	
				echo "<p>{$lang['strdescendingeventsorder']}</p>\n";

				$columns = array(
					'time' => array(
						'title' => $lang['strdatetime'],
						'field' => field('ev_ts'),
						'type' => 'spanned',
						'params'=> array(
							'dateformat' => $lang['stroldtimestampformat'],
							'locale' => $lang['applocale'],
							'class' => 'tooltip left-aligned-tooltip',
							),
					),
					'event' => array(
						'title' => $lang['strevent'],
						'field' => field('ev_text'),
					),
				);
	
				$misc->printTable($events, $columns, $actions, 'tbl_emaj_history', null, null, array('sorter' => false, 'filter' => true));
			}
		}

		echo "<hr/>\n";

	}

	$misc->printHtmlHeader($lang['strtables'] . ' - ' . $_REQUEST['table']);
	$misc->printBody();

	switch ($action) {
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
