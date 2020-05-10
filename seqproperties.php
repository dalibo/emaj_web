<?php

	/*
	 * Display the properties of a given sequence
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	// Callback function to dynamicaly add a link to the tables group's description when the group name is suffixed by "###LINK###"
	function renderlinktogroup($val) {
		global $misc;

		if (preg_match("/(.*)###LINK###$/", $val, $matches)) {
			$val = $matches[1];
			return "<a href=\"emajgroups.php?action=show_group&amp;" . $misc->href . "&amp;group=". urlencode($val) . "\">" . $val . "</a>";
		} else {
			return $val;
		}
	}

	/**
	 * Display the properties of a sequence
	 */
	function doProperties($msg = '') {
		global $data, $misc, $lang, $emajdb;

		$misc->printHeader('sequence', 'sequence', 'properties');
		$misc->printTitle(sprintf($lang['strseqproperties'], $_REQUEST['schema'], $_REQUEST['sequence']));
		$misc->printMsg($msg);

		// Fetch the sequence information
		$sequence = $emajdb->getSequenceProperties($_REQUEST['schema'], $_REQUEST['sequence']);

		$columns = array(
			'lastvalue' => array(
				'title' => $lang['strlastvalue'],
				'field' => field('last_value'),
				'type'  => 'numeric',
				'params'=> array('class' => 'bold'),
			),
			'iscalled' => array(
				'title' => $lang['striscalled'],
				'field' => field('is_called'),
				'type'  => 'bool',
				'params'=> array('true' => $lang['stryes'], 'false' => $lang['strno'], 'class' => 'bold'),
			),
			'startvalue' => array(
				'title' => $lang['strstartvalue'],
				'field' => field('start_value'),
				'type'  => 'numeric',
			),
			'minvalue' => array(
				'title' => $lang['strminvalue'],
				'field' => field('min_value'),
				'type'  => 'numeric',
			),
			'maxvalue' => array(
				'title' => $lang['strmaxvalue'],
				'field' => field('max_value'),
				'type'  => 'numeric',
			),
			'increment' => array(
				'title' => $lang['strincrement'],
				'field' => field('increment_by'),
				'type'  => 'numeric',
			),
			'cancycle' => array(
				'title' => $lang['strcancycle'],
				'field' => field('cycle'),
				'type'  => 'bool',
				'params'=> array('true' => $lang['stryes'], 'false' => $lang['strno']),
			),
			'cachesize' => array(
				'title' => $lang['strcachesize'],
				'field' => field('cache_size'),
				'type'  => 'numeric',
			),
			'logcount' => array(
				'title' => $lang['strlogcount'],
				'field' => field('log_cnt'),
				'type'  => 'numeric',
			),
		);

		$actions = array();

		$misc->printTable($sequence, $columns, $actions, 'seqproperties-columns', $lang['strnodata']);

		// Show comment if any
		$sequence->moveFirst();
		if ($sequence->fields['seqcomment'] !== null)
			echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$misc->printVal($sequence->fields['seqcomment'])}</span></p>\n";

		echo "<hr/>\n";

		// Display the E-Maj properties, if any
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {

			$misc->printTitle($lang['emajproperties']);

			$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['sequence']);

			if ($type == 'L') {
				echo "<p>{$lang['emajemajlogsequence']}</p>\n";
			} elseif ($type == 'E') {
				echo "<p>{$lang['emajinternalsequence']}</p>\n";
			} else {
				$groups = $emajdb->getTableGroupsTblSeq($_REQUEST['schema'], $_REQUEST['sequence']);

				$columns = array(
					'group' => array(
						'title' => $lang['emajgroup'],
						'field' => field('rel_group'),
						'type'	=> 'callback',
						'params'=> array('function' => 'renderlinktogroup')
					),
					'starttime' => array(
						'title' => $lang['strbegin'],
						'field' => field('start_datetime')
					),
					'stoptime' => array(
						'title' => $lang['strend'],
						'field' => field('stop_datetime')
					),
				);

				$misc->printTable($groups, $columns, $actions, 'sequences-groups', $lang['emajseqnogroupownership']);
			}
		}
	}

	// Print header
	$misc->printHtmlHeader($lang['strsequences']);
	$misc->printBody();

	switch($action) {
		case 'properties':
			doProperties();
			break;
		default:
			doProperties();
			break;
	}

	// Print footer
	$misc->printFooter();

?>
