<?php

	/*
	 * Display the properties of a given sequence
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	/**
	 * Display the properties of a sequence
	 */
	function doProperties($msg = '') {
		global $data, $misc, $lang, $emajdb;

		$misc->printHeader('sequence', 'sequence', 'properties');
		$misc->printTitle(sprintf($lang['strnamedsequence'], $_REQUEST['schema'], $_REQUEST['sequence']));
		$misc->printMsg($msg);

		// Display the E-Maj properties, if any
		if ($emajdb->isEnabled() && $emajdb->isAccessible()) {

			$misc->printSubtitle($lang['stremajproperties']);

			$type = $emajdb->getEmajTypeTblSeq($_REQUEST['schema'], $_REQUEST['sequence']);

			if ($type == 'L') {
				echo "<p>{$lang['stremajlogsequence']}</p>\n";
			} elseif ($type == 'E') {
				echo "<p>{$lang['stremajinternalsequence']}</p>\n";
			} else {

				$prop = $emajdb->getRelationEmajProperties($_REQUEST['schema'], $_REQUEST['sequence']);

				$columns = array(
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('rel_group'),
						'url'   => "emajgroups.php?action=show_group&amp;{$misc->href}&amp;",
						'vars'  => array('group' => 'rel_group')
					),
					'starttime' => array(
						'title' => $lang['strsince'],
						'field' => field('assign_ts'),
						'type' => 'spanned',
						'params'=> array(
							'dateformat' => $lang['stroldtimestampformat'],
							'locale' => $lang['applocale'],
							'class' => 'tooltip left-aligned-tooltip',
							),
					),
				);

				$misc->printTable($prop, $columns, $actions, 'seqproperties-emaj', $lang['strseqnogroupownership']);
			}

			echo "<hr/>\n";
		}

		// Display the sequence properties

		$misc->printSubtitle($lang['strseqproperties']);

		// Verify that the user has enough privileges to read the sequence
		$privilegeOk = $emajdb->hasSelectPrivilegeOnSequence($_REQUEST['schema'], $_REQUEST['sequence']);

		if (! $privilegeOk) {
			echo $lang['strnograntonsequence'];
		} else {

			// Fetch the sequence information
			$sequence = $emajdb->getSequenceProperties($_REQUEST['schema'], $_REQUEST['sequence']);

			// Show comment if any
			if ($sequence->fields['seqcomment'] !== null)
				echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$misc->printVal($sequence->fields['seqcomment'])}</span></p>\n";
			$sequence->moveFirst();

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
