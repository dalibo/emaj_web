<?php
	/*
	 * Manage the tables groups history
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

/********************************************************************************************************
 * Callback functions 
 *******************************************************************************************************/

	// Callback function to dynamicaly add an icon to each group history row
	function renderHistoryGraphic($val) {
		global $misc, $lang;

		$parts = explode('#', $val);
		$title = $lang[$parts[0]];
		$icon = $misc->icon($parts[1]);

		$div = "<div title=\"$title\"><img src=\"$icon\" alt=\"$icon\" title=\"$title\" class=\"fullsizecellicon\" /></div>";
		return $div;
	}

/********************************************************************************************************
 * Main functions displaying pages
 *******************************************************************************************************/

	/**
	 * Displays the history of the tables group, ie. group creation, drop, start and stop events
	 */
	function show_history_group() {
		global $misc, $lang, $emajdb;

		$misc->printHeader('emaj', 'emajgroup', 'emajhistory');

		$misc->printTitle(sprintf($lang['strgrouphistory'],htmlspecialchars($_REQUEST['group'])));

		$groupHistory = $emajdb->getHistoryGroup($_REQUEST['group']);

		if ($groupHistory->recordCount() < 1) {

			// There is no history to display
			echo "<p>" . sprintf($lang['stremajnohistory'], htmlspecialchars($_REQUEST['group'])) . "</p>\n";

		} else {

			echo "<p>{$lang['strgrouphistoryorder']}</p>\n";

			$columns = array(
				'graphic' => array(
					'title' => '',
					'field' => field('graphic'),
					'type'	=> 'callback',
					'params'=> array('function' => 'renderHistoryGraphic'),
					'class' => 'nopadding center',
					'filter'=> false,
				),
				'createdroptime' => array(
					'title' => $lang['strgroupcreateddroppedat'],
					'field' => field('create_drop_time'),
					'type' => 'spanned',
					'params'=> array(
						'dateformat' => $lang['stroldtimestampformat'],
						'locale' => $lang['applocale'],
						'class' => 'tooltip left-aligned-tooltip',
					),
					'sorter_text_extraction' => 'span_text',
				),
				'nb_log_sessions' => array(
					'title' => $lang['strnblogsessions'],
					'field' => field('grph_log_sessions'),
					'type'  => 'numeric'
				),
				'starttime' => array(
					'title' => $lang['strgroupstartedat'],
					'field' => field('start_time'),
					'type' => 'spanned',
					'params'=> array(
						'dateformat' => $lang['stroldtimestampformat'],
						'locale' => $lang['applocale'],
						'class' => 'tooltip left-aligned-tooltip',
					),
					'sorter_text_extraction' => 'span_text',
				),
				'stoptime' => array(
					'title' => $lang['strgroupstoppedat'],
					'field' => field('stop_time'),
					'type' => 'spanned',
					'params'=> array(
						'dateformat' => $lang['stroldtimestampformat'],
						'locale' => $lang['applocale'],
						'class' => 'tooltip left-aligned-tooltip',
					),
					'sorter_text_extraction' => 'span_text',
				),
				'nb_marks' => array(
					'title' => $lang['strmarks'],
					'field' => field('lses_marks'),
					'type'  => 'numeric'
				),
				'nb_log_rows' => array(
					'title' => $lang['strchanges'],
					'field' => field('lses_log_rows'),
					'type'  => 'numeric'
				),
			);

			$actions = array ();

			echo "<p></p>";
			$misc->printTable($groupHistory, $columns, $actions, 'groupHistory', null, null, array('sorter' => false, 'filter' => true));
		}
	}

/********************************************************************************************************
 * Main piece of code
 *******************************************************************************************************/

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	$misc->printHtmlHeader($lang['strgroupsmanagement']);
	$misc->printBody();

	switch ($action) {
		case 'show_history_group':
			show_history_group();
			break;
		default:
			show_history_group();
	}

	$misc->printFooter();
?>
