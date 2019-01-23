<?php

	/*
	 * Manage sequences in a database
	 */

	// Include application functions
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	/**
	 * Display list of all sequences in the database/schema
	 */
	function doDefault($msg = '')	{
		global $data, $conf, $misc;
		global $lang;

		$misc->printHeader('schema', 'schema', 'sequences');
		$misc->printMsg($msg);
		$misc->printTitle(sprintf($lang['strsequenceslist'], $_REQUEST['schema']));

		// Get all sequences
		$sequences = $data->getSequences();

		$columns = array(
			'sequence' => array(
				'title' => $lang['strsequence'],
				'field' => field('seqname'),
				'url'   => "sequences.php?action=properties&amp;{$misc->href}&amp;",
				'vars'  => array('sequence' => 'seqname'),
			),
			'owner' => array(
				'title' => $lang['strowner'],
				'field' => field('seqowner'),
			),
			'comment' => array(
				'title' => $lang['strcomment'],
				'field' => field('seqcomment'),
			),
		);

		$misc->printTable($sequences, $columns, $actions, 'sequences-sequences', $lang['strnosequences'], null, array('sorter' => true, 'filter' => true));

	}

	/**
	 * Display the properties of a sequence
	 */
	function doProperties($msg = '') {
		global $data, $misc, $lang;

		$misc->printHeader('sequence', '', '');
		$misc->printTitle($lang['strproperties'],'pg.sequence');
		$misc->printMsg($msg);

		// Fetch the sequence information
		$sequence = $data->getSequence($_REQUEST['sequence']);

		if (is_object($sequence) && $sequence->recordCount() > 0) {
			$sequence->fields['is_cycled'] = $data->phpBool($sequence->fields['is_cycled']);
			$sequence->fields['is_called'] = $data->phpBool($sequence->fields['is_called']);

			// Show comment if any
			if ($sequence->fields['seqcomment'] !== null)
				echo "<p>{$lang['strcommentlabel']}<span class=\"comment\">{$misc->printVal($sequence->fields['seqcomment'])}</span></p>\n";

			echo "<table border=\"0\">";
			echo "<tr><th class=\"data\">{$lang['strname']}</th>";
			if ($data->hasAlterSequenceStart()) {
				echo "<th class=\"data\">{$lang['strstartvalue']}</th>";
			}
			echo "<th class=\"data\">{$lang['strlastvalue']}</th>";
			echo "<th class=\"data\">{$lang['strincrementby']}</th>";
			echo "<th class=\"data\">{$lang['strmaxvalue']}</th>";
			echo "<th class=\"data\">{$lang['strminvalue']}</th>";
			echo "<th class=\"data\">{$lang['strcachevalue']}</th>";
			echo "<th class=\"data\">{$lang['strlogcount']}</th>";
			echo "<th class=\"data\">{$lang['strcancycle']}</th>";
			echo "<th class=\"data\">{$lang['striscalled']}</th></tr>";
			echo "<tr>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['seqname']), "</td>";
			if ($data->hasAlterSequenceStart()) {
				echo "<td class=\"data1\">", $misc->printVal($sequence->fields['start_value']), "</td>";
			}
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['last_value']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['increment_by']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['max_value']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['min_value']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['cache_value']), "</td>";
			echo "<td class=\"data1\">", $misc->printVal($sequence->fields['log_cnt']), "</td>";
			echo "<td class=\"data1\">", ($sequence->fields['is_cycled'] ? $lang['stryes'] : $lang['strno']), "</td>";
			echo "<td class=\"data1\">", ($sequence->fields['is_called'] ? $lang['stryes'] : $lang['strno']), "</td>";
			echo "</tr>";
			echo "</table>";

			// Navigation links
			$navlinks = array();
	
			// Refresh
			$navlinks['refresh'] = array (
				'attr'=> array (
					'href' => array (
						'url' => 'sequences.php',
						'urlvars' => $_REQUEST,
					)
				),
				'content' => $lang['strrefresh']
			);
			$misc->printNavLinks($navlinks);
		}
		else echo "<p>{$lang['strnodata']}</p>\n";
	}

	/**
	 * Generate XML for the browser tree.
	 */
	function doTree() {
		global $misc, $data;

		$sequences = $data->getSequences();

		$reqvars = $misc->getRequestVars('sequence');

		$attrs = array(
			'text'   => field('seqname'),
			'icon'   => 'Sequence',
			'toolTip'=> field('seqcomment'),
			'action' => url('sequences.php',
							$reqvars,
							array (
								'action' => 'properties',
								'sequence' => field('seqname')
							)
						)
		);

		$misc->printTree($sequences, $attrs, 'sequences');
		exit;
	}

	if ($action == 'tree') doTree();

	// Print header
	$misc->printHtmlHeader($lang['strsequences']);
	$misc->printBody();

	switch($action) {
		case 'properties':
			doProperties();
			break;
		default:
			doDefault();
			break;
	}

	// Print footer
	$misc->printFooter();

?>
