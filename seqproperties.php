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
							'title' => $lang['emajfrom'],
							'field' => field('start_datetime')
						),
						'stoptime' => array(
							'title' => $lang['emajto'],
							'field' => field('stop_datetime')
						),
					);
			
					$misc->printTable($groups, $columns, $actions, 'sequences-groups', $lang['emajseqnogroupownership']);
				}
			}
		}
		else echo "<p>{$lang['strnodata']}</p>\n";
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
