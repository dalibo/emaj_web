<?php

	/*
	 * Display the E-Maj history of a sequence, i.e. the tables group ownership.
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

	/**
	 * Show the sequence E-Maj history.
	 */
	function showHistory() {
		global $data, $conf, $misc, $lang, $emajdb;

		$misc->printHeader('sequence', 'sequence', 'history');

		// Display the E-Maj history.
		$misc->printTitle(sprintf($lang['stremajhistorysequence'], $_REQUEST['schema'], $_REQUEST['sequence']));

		$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['sequence']);

		if ($type == 'L') {
			echo "<p>{$lang['stremajlogsequence']}</p>\n";
		} elseif ($type == 'E') {
			echo "<p>{$lang['stremajinternalsequence']}</p>\n";
		} else {
			$events = $emajdb->getTblSeqEmajHist($_REQUEST['schema'], $_REQUEST['sequence']);

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
	
				$misc->printTable($events, $columns, $actions, 'seq_emaj_history', null, null, array('sorter' => false, 'filter' => true));
			}
		}

		echo "<hr/>\n";

	}

	$misc->printHtmlHeader($lang['strsequences'] . ' - ' . $_REQUEST['sequence']);
	$misc->printBody();

	switch ($action) {
		default:
			showHistory();
			break;
	}

	$misc->printFooter();

?>
