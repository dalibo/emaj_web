<?php

	/*
	 * Display the E-Maj environment characteristics.
	 */

	// Include application functions.
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	if (!isset($msg)) $msg = '';

	/**
	 * Show the changes activity.
	 */
	function doDefault($msg = '', $errMsg = '') {
		global $misc, $lang, $data, $emajdb;

		$misc->printHeader('database', 'database', 'emajactivity');

		$misc->printMsg($msg,$errMsg);

		$misc->printTitle($lang['strchangesactivity']);

		// Prepare the input values displayed in the form.
		$previousRequest = (isset($_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousRequest'])) ?
							$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousRequest'] : '';

		$groupsInclude = (isset($_REQUEST['groups-include'])) ? $_REQUEST['groups-include'] : 
							((isset($previousRequest['groups-include'])) ? $previousRequest['groups-include'] : '');
		$tablesInclude = (isset($_REQUEST['tables-include'])) ? $_REQUEST['tables-include'] :
							((isset($previousRequest['tables-include'])) ? $previousRequest['tables-include'] : '');
		$sequencesInclude = (isset($_REQUEST['sequences-include'])) ? $_REQUEST['sequences-include'] :
							((isset($previousRequest['sequences-include'])) ? $previousRequest['sequences-include'] : '');
		$groupsExclude = (isset($_REQUEST['groups-exclude'])) ? $_REQUEST['groups-exclude'] :
							((isset($previousRequest['groups-exclude'])) ? $previousRequest['groups-exclude'] : '');
		$tablesExclude = (isset($_REQUEST['tables-exclude'])) ? $_REQUEST['tables-exclude'] :
							((isset($previousRequest['tables-exclude'])) ? $previousRequest['tables-exclude'] : '');
		$sequencesExclude = (isset($_REQUEST['sequences-exclude'])) ? $_REQUEST['sequences-exclude'] :
								((isset($previousRequest['sequences-exclude'])) ? $previousRequest['sequences-exclude'] : '');
		$maxGroups = (isset($_REQUEST['max-groups'])) ? $_REQUEST['max-groups'] :
						((isset($previousRequest['max-groups'])) ? $previousRequest['max-groups'] : 5);
		$maxTables = (isset($_REQUEST['max-tables'])) ? $_REQUEST['max-tables'] : 
						((isset($previousRequest['max-tables'])) ? $previousRequest['max-tables'] : 20);
		$maxSequences = (isset($_REQUEST['max-sequences'])) ? $_REQUEST['max-sequences'] :
							((isset($previousRequest['max-sequences'])) ? $previousRequest['max-sequences'] : 20);
		$sort = (isset($_REQUEST['sort'])) ? $_REQUEST['sort'] :
					((isset($previousRequest['sort'])) ? $previousRequest['sort'] : 'previous-mark');

		// Form
		echo "<form id=\"statistics_form\" action=\"activity.php?action=refresh-activity&amp;{$misc->href}\"";
		echo "  method=\"post\" enctype=\"multipart/form-data\">\n";
		echo "<div class=\"form-container-5c\">\n";

		// Header row
		echo "\t<div></div>\n";
		echo "\t<div></div>\n";
		echo "\t<div class=\"form-header\">{$lang['strgroups']}</div>\n";
		echo "\t<div class=\"form-header\">{$lang['strtables']}</div>\n";
		echo "\t<div class=\"form-header\">{$lang['strsequences']}</div>\n";

		// Include regexp
		echo "\t<div class=\"form-label\">{$lang['strincluderegexp']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strincluderegexphelp']}\"/></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"groups-include\" size=\"20\" value=\"$groupsInclude\"></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"tables-include\" size=\"25\" value=\"$tablesInclude\"></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"sequences-include\" size=\"25\" value=\"$sequencesInclude\"></div>\n";

		// Exclude regexp
		echo "\t<div class=\"form-label\">{$lang['strexcluderegexp']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strexcluderegexphelp']}\"/></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"groups-exclude\" size=\"20\" value=\"$groupsExclude\"></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"tables-exclude\" size=\"25\" value=\"$tablesExclude\"></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"sequences-exclude\" size=\"25\" value=\"$sequencesExclude\"></div>\n";

		// Size limits
		echo "\t<div class=\"form-label\">{$lang['strmaxrows']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmaxrowshelp']}\"/></div>\n";
		echo "\t<div class=\"form-input\"><input type=\"number\" name=\"max-groups\" min=\"0\" value=\"$maxGroups\"></div>\n";
		echo "\t<div class=\"form-input\"><input type=\"number\" name=\"max-tables\" min=\"0\" value=\"$maxTables\"></div>\n";
		echo "\t<div class=\"form-input\"><input type=\"number\" name=\"max-sequences\" min=\"0\" value=\"$maxSequences\"></div>\n";

		echo"</div>\n";
		echo $misc->form;

		// Buttons line
		if ($sort == 'latest-mark') {
			$latestMarkChecked = 'checked'; $previousDisplayChecked = '';
		} else {
			$latestMarkChecked = ''; $previousDisplayChecked = 'checked';
		}
		echo "<p>{$lang['strmainsortcriteria']}\n";
		echo "<input type=\"radio\" name=\"sort\" value=\"latest-mark\" {$latestMarkChecked}>{$lang['strlatestmark']}\n";
		echo "<input type=\"radio\" name=\"sort\" value=\"previous-display\" {$previousDisplayChecked}>{$lang['strpreviousdisplay']}&nbsp;&nbsp;&nbsp;&nbsp;\n";

		$buttonValue = (isset($_REQUEST['groups-include'])) ? $lang['strrefresh'] : $lang['strdisplay'];
		echo "<input type=\"submit\" name=\"refresh\" id=\"refreshBt\" value=\"$buttonValue\" />\n";
		echo "<input type=\"button\" name=\"reset\" value=\"{$lang['strreset']}\" onclick=\"javascript:resetForm();\" />\n";
		echo "</p></form>\n";

		// Display the activity if parameters are known
		if (isset($_REQUEST['groups-include'])) {
			displayActivity();
		}
	}

	/**
	 * Display the changes statistics.
	 */
	function displayActivity() {
		global $misc, $lang, $emajdb;
		include_once('classes/ArrayRecordSet.php');

		// Get the E-Maj statistics
		list($errorTrapped,
			 $currentTime, $lastRefreshIntervalStr, $globalChanges, $globalCps,
			 $globalCounters, $loggingGroups, $loggedTables, $loggedSequences) = getEmajStat();

		// If an error has been trapped at db side, just warn and stop the display
		if ($errorTrapped) {
			echo "<p>{$lang['strerrortrapped']}</p>";
		} else {

			// Display the global data line

			$misc->printTitle($lang['strglobalactivitytitle']);

			echo "<p>$currentTime - " . sprintf($lang['strglobalactivity'], $lastRefreshIntervalStr, $globalChanges, $globalCps) . "</p>";
	
			// Display the tables groups

			if ($_REQUEST['max-groups'] > 0) {
				$misc->printTitle(sprintf($lang['strlogginggroupstitle'], $globalCounters['nb_logging_groups'], $globalCounters['nb_groups']));

				$loggingGroupsArs = new ArrayRecordSet(array_slice($loggingGroups, 0, $_REQUEST['max-groups']));
		
				$columns = array(
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('group'),
					),
					'latest_mark' => array(
						'title' => $lang['strlatestmark'],
						'field' => field('latest_mark'),
					),
					'latest_mark_ts' => array(
						'title' => $lang['strmarksetat'],
						'field' => field('latest_mark_ts'),
					),
					'changes_since_mark' => array(
						'title' => $lang['strchangessincemark'],
						'field' => field('changes_since_mark'),
					),
					'cps_since_mark' => array(
						'title' => $lang['strcpssincemark'],
						'field' => field('cps_since_mark'),
					),
					'changes_since_previous' => array(
						'title' => $lang['strchanges'],
						'field' => field('changes_since_previous'),
					),
					'cps_since_previous' => array(
						'title' => $lang['strcps'],
						'field' => field('cps_since_previous'),
					),
					'actions' => array(
						'title' => $lang['stractions'],
					),
				);
		
				$actions = array();
		
				$misc->printTable($loggingGroupsArs, $columns, $actions, 'activity-groups', $lang['strnogroupselected']);
			}
	
			// Display the logged tables
			if ($_REQUEST['max-tables'] > 0) {
				$misc->printTitle(sprintf($lang['strtablesinlogginggroups'], $globalCounters['nb_logged_tables'], $globalCounters['nb_tables']));

				$loggedTablesArs = new ArrayRecordSet(array_slice($loggedTables, 0, $_REQUEST['max-tables']));
		
				$columns = array(
					'schema' => array(
						'title' => $lang['strschema'],
						'field' => field('rel_schema'),
					),
					'table' => array(
						'title' => $lang['strtable'],
						'field' => field('rel_tblseq'),
					),
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('rel_group'),
					),
					'changes_since_mark' => array(
						'title' => $lang['strchangessincemark'],
						'field' => field('changes_since_mark'),
					),
					'cps_since_mark' => array(
						'title' => $lang['strcpssincemark'],
						'field' => field('cps_since_mark'),
					),
					'changes_since_previous' => array(
						'title' => $lang['strchanges'],
						'field' => field('changes_since_previous'),
					),
					'cps_since_previous' => array(
						'title' => $lang['strcps'],
						'field' => field('cps_since_previous'),
					),
					'actions' => array(
						'title' => $lang['stractions'],
					),
				);
		
				$actions = array();
		
				$misc->printTable($loggedTablesArs, $columns, $actions, 'activity-tables', $lang['strnotableselected']);
			}
	
			// Display the logged sequences
			if ($_REQUEST['max-sequences'] > 0) {
				$misc->printTitle(sprintf($lang['strsequencesinlogginggroups'], $globalCounters['nb_logged_sequences'], $globalCounters['nb_sequences']));

				$loggedSequencesArs = new ArrayRecordSet(array_slice($loggedSequences, 0, $_REQUEST['max-sequences']));
		
				$columns = array(
					'schema' => array(
						'title' => $lang['strschema'],
						'field' => field('rel_schema'),
					),
					'sequence' => array(
						'title' => $lang['strsequence'],
						'field' => field('rel_tblseq'),
					),
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('rel_group'),
					),
					'changes_since_mark' => array(
						'title' => $lang['strchangessincemark'],
						'field' => field('changes_since_mark'),
					),
					'cps_since_mark' => array(
						'title' => $lang['strcpssincemark'],
						'field' => field('cps_since_mark'),
					),
					'changes_since_previous' => array(
						'title' => $lang['strchanges'],
						'field' => field('changes_since_previous'),
					),
					'cps_since_previous' => array(
						'title' => $lang['strcps'],
						'field' => field('cps_since_previous'),
					),
					'actions' => array(
						'title' => $lang['stractions'],
					),
				);
		
				$actions = array();
		
				$misc->printTable($loggedSequencesArs, $columns, $actions, 'activity-sequencesables', $lang['strnosequenceselected']);
			}
		}
	}

	/**
	 * Compute the changes statistics.
	 */
	function getEmajStat() {
		global $emajdb;

		// Get the emajStat related session variables
		if (isset($_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']])) {
			$previousEpoch = $_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousEpoch'];
			$previousEmajTimeStampTimeIdSeq = $_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousEmajTimeStampTimeIdSeq'];
			$previousEmajGlobalSeq = $_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousEmajGlobalSeq'];
			$globalCounters = $_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['globalCounters'];
			$loggingGroups = $_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['loggingGroups'];
			$loggedTables = $_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['loggedTables'];
			$loggedSequences = $_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['loggedSequences'];
			$previousRequest = $_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousRequest'];
			$areRegExpFiltersModified = $_REQUEST['groups-include'] != $previousRequest['groups-include']
									 || $_REQUEST['groups-exclude'] != $previousRequest['groups-exclude']
									 || $_REQUEST['tables-include'] != $previousRequest['tables-include']
									 || $_REQUEST['tables-exclude'] != $previousRequest['tables-exclude']
									 || $_REQUEST['sequences-include'] != $previousRequest['sequences-include']
									 || $_REQUEST['sequences-exclude'] != $previousRequest['sequences-exclude'];
		} else {
			$previousEpoch = 0;
			$previousEmajTimeStampTimeIdSeq = 0;
			$previousEmajGlobalSeq = 0;
			$areRegExpFiltersModified = 1;
		}

		// Get the timestamp and the sequences last_value
#$begin = hrtime(true);
		$seqRs = $emajdb->emajStatGetSeqLastVal($_REQUEST['groups-include'], $_REQUEST['groups-exclude'],
											$_REQUEST['tables-include'], $_REQUEST['tables-exclude'],
											$_REQUEST['sequences-include'], $_REQUEST['sequences-exclude'],);
#$end = hrtime(true);
#echo "<p>Response time = " . ($end - $begin)/1e+9 . " s</p>";
		// Extract the technical data (timestamp and emaj sequences last_value
		// and memorize other sequence last_values
		$seq_last_values = array ();
		$errorTrapped = false;
		while (!$seqRs->EOF) {
			switch ($seqRs->fields['p_key']) {
				case 'current_epoch':
					$currentEpoch = $seqRs->fields['p_value'];
					break;
				case 'emaj.emaj_time_stamp_time_id_seq':
					$currentEmajTimeStampTimeIdSeq = $seqRs->fields['p_value'];
					break;
				case 'emaj.emaj_global_seq':
					$currentEmajGlobalSeq = $seqRs->fields['p_value'];
					break;
				case 'error':
					$errorTrapped = true;
					break;
				default:
					$seq_last_values[$seqRs->fields['p_key']] = $seqRs->fields['p_value'];
					break;
			}
			$seqRs->moveNext();
		}
		// If an error has been trapped at db side, just return the error flag
		if ($errorTrapped)
			return array(true, null, null, null, null, null, null, null, null);

		// If the emaj_time_stamp sequence value has changed rebuild the global counters.
		// A new emaj_time_stamp row is often due to a tables group structure change or a new mark set for a group.
		if ($currentEmajTimeStampTimeIdSeq != $previousEmajTimeStampTimeIdSeq) {
			// Get global counters from the database
			$rs = $emajdb->emajStatGetGlobalCounters();
			$globalCounters = $rs->fields;
			$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['globalCounters'] = $globalCounters;
		}

		// If the emaj_time_stamp sequence value has changed or if filters have been modified by the user,
		//   rebuild the groups, tables and sequences arrays.
		if ($currentEmajTimeStampTimeIdSeq != $previousEmajTimeStampTimeIdSeq || $areRegExpFiltersModified) {
			// Get tables groups from the database
			$rs = $emajdb->emajStatGetGroups($_REQUEST['groups-include'], $_REQUEST['groups-exclude']);
			$loggingGroups = array();
			while (!$rs->EOF) {
				array_push($loggingGroups, $rs->fields);
				$rs->moveNext();
			}
			// Get tables from the database
			$rs = $emajdb->emajStatGetTables($_REQUEST['groups-include'], $_REQUEST['groups-exclude'], $_REQUEST['tables-include'], $_REQUEST['tables-exclude']);
			$loggedTables = array();
			while (!$rs->EOF) {
				array_push($loggedTables, $rs->fields);
				$rs->moveNext();
			}
			// Get sequences from the database
			$rs = $emajdb->emajStatGetSequences($_REQUEST['groups-include'], $_REQUEST['groups-exclude'], $_REQUEST['sequences-include'], $_REQUEST['sequences-exclude']);
			$loggedSequences = array();
			while (!$rs->EOF) {
				array_push($loggedSequences, $rs->fields);
				$rs->moveNext();
			}
		}

		// Compute global aggregates
		$currentTime = date("Y-m-d H:i:s");
		if ($previousEpoch > 0) {
			$lastRefreshInterval = $currentEpoch - $previousEpoch;
			$lastRefreshIntervalStr = sprintf('%.3f', $lastRefreshInterval);
			$globalChanges = $currentEmajGlobalSeq - $previousEmajGlobalSeq;
			$globalCps = sprintf('%.3f', $globalChanges / $lastRefreshInterval);
		} else {
			$lastRefreshIntervalStr = '-';
			$globalChanges = '-';
			$globalCps = '-';
		}

		// Reset the groups changes aggregates and prepare an assocciative array with group names
		$gA = array();
		$i = 0;
		foreach ($loggingGroups AS &$lg) {
			$lg['changes_since_mark'] = 0;
			$lg['changes_since_previous'] = 0;
			$gA[$lg['group']] = $i;
			$i++;
		}
		// Compute changes on tables and groups
		foreach ($loggedTables as &$t) {
			$fullTableName = $t['rel_schema'] . '.' . $t['rel_tblseq'];
			$gI = $gA[$t['rel_group']];
			if (isset($seq_last_values[$fullTableName])) {
				// Tables aggregates
				$t['seq_previous'] = $t['seq_current'];
				$t['seq_current'] = $seq_last_values[$fullTableName];
				$t['changes_since_mark'] = $t['seq_current'] - $t['seq_at_mark'];
				$t['cps_since_mark'] = sprintf('%.3f', $t['changes_since_mark'] / ($currentEpoch - $loggingGroups[$gI]['latest_mark_epoch']));
				if ($t['seq_previous'] != '') {
					$t['changes_since_previous'] = $t['seq_current'] - $t['seq_previous'];
					$t['cps_since_previous'] = sprintf('%.3f', $t['changes_since_previous'] / $lastRefreshInterval);
				} else {
					$t['changes_since_previous'] = '';
					$t['cps_since_previous'] = '';
				}
				// Groups aggregates
				$loggingGroups[$gI]['changes_since_mark'] += $t['changes_since_mark'];
				if ($t['seq_previous'] != '') {
					$loggingGroups[$gI]['changes_since_previous'] += $t['changes_since_previous'];
				}
			}
		}

		// Compute the changes per second aggregates for groups
		foreach ($loggingGroups AS &$lg) {
			$lg['cps_since_mark'] = sprintf('%.3f', $lg['changes_since_mark'] / ($currentEpoch - $lg['latest_mark_epoch']));
			if ($previousEpoch > 0) {
				$lg['cps_since_previous'] = sprintf('%.3f', $lg['changes_since_previous'] / $lastRefreshInterval);
			} else {
				$lg['cps_since_previous'] = '';
			}
		}

		// Compute changes on sequences
		foreach ($loggedSequences as &$s) {
			$fullSeqName = $t['rel_schema'] . '.' . $t['rel_tblseq'];
			$gI = $gA[$t['rel_group']];
			if (isset($seq_last_values[$fullSeqName])) {
				$s['seq_previous'] = $s['seq_current'];
				$s['seq_current'] = $seq_last_values[$fullSeqName];
				$s['changes_since_mark'] = ($s['seq_current'] - $s['seq_at_mark']) / $s['sequ_increment'];
				$s['cps_since_mark'] = sprintf('%.3f', $s['changes_since_mark'] / ($currentEpoch - $loggingGroups[$gI]['latest_mark_epoch']));
				if ($s['seq_previous'] != '') {
					$s['changes_since_previous'] = ($s['seq_current'] - $s['seq_previous']) / $s['sequ_increment'];
					$s['cps_since_previous'] = sprintf('%.3f', $s['changes_since_previous'] / $lastRefreshInterval);
				} else {
					$s['changes_since_previous'] = '';
					$s['cps_since_previous'] = '';
				}
			}
		}

		// Sort groups by (changes_since_mark DESC, group_name ASC) or (changes_since_previous DESC, group_name ASC)
		// and sort tables and sequences by (changes_since_mark DESC, full_relation_name ASC) or (changes_since_previous DESC, full_relation_name ASC)
		function cmpGroupsMark($a, $b)
		{
			if ($a['changes_since_mark'] == $b['changes_since_mark']) {
				return strcmp($a['group'], $b['group']);
			}
			return ($a['changes_since_mark'] < $b['changes_since_mark']) ? 1 : -1;
		}
		function cmpGroupsPrevious($a, $b)
		{
			if ($a['changes_since_previous'] == $b['changes_since_previous']) {
				return strcmp($a['group'], $b['group']);
			}
			return ($a['changes_since_previous'] < $b['changes_since_previous']) ? 1 : -1;
		}
		function cmpRelationsMark($a, $b)
		{
			if ($a['changes_since_mark'] == $b['changes_since_mark']) {
				return strcmp($a['rel_schema'].'.'.$a['rel_tblseq'], $b['rel_schema'].'.'.$b['rel_tblseq']);
			}
			return ($a['changes_since_mark'] < $b['changes_since_mark']) ? 1 : -1;
		}
		function cmpRelationsPrevious($a, $b)
		{
			if ($a['changes_since_previous'] == $b['changes_since_previous']) {
				return strcmp($a['rel_schema'].'.'.$a['rel_tblseq'], $b['rel_schema'].'.'.$b['rel_tblseq']);
			}
			return ($a['changes_since_previous'] < $b['changes_since_previous']) ? 1 : -1;
		}
		if (isset($_POST['sort']) && $_POST['sort'] == 'latest-mark') {
			usort($loggingGroups, "cmpGroupsMark");
			usort($loggedTables, "cmpRelationsMark");
			usort($loggedSequences, "cmpRelationsMark");
		} else {
			usort($loggingGroups, "cmpGroupsPrevious");
			usort($loggedTables, "cmpRelationsPrevious");
			usort($loggedSequences, "cmpRelationsPrevious");
		}

		// Save emajStat data into session variables
		$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousEpoch'] = $currentEpoch;
		$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousEmajTimeStampTimeIdSeq'] = $currentEmajTimeStampTimeIdSeq;
		$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousEmajGlobalSeq'] = $currentEmajGlobalSeq;
		$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['loggingGroups'] = $loggingGroups;
		$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['loggedTables'] = $loggedTables;
		$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['loggedSequences'] = $loggedSequences;
		$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousRequest'] = $_REQUEST;

		// And return 1 flag, 4 single values and 4 arrays to display
		return array(false,
					 $currentTime, $lastRefreshIntervalStr, $globalChanges, $globalCps,
					 $globalCounters, $loggingGroups, $loggedTables, $loggedSequences);
	}

	$scripts = "<script src=\"js/activity.js\"></script>";
	$misc->printHtmlHeader($lang['strchangesactivity'], $scripts, 'activity');
	$misc->printBody();

	switch ($action) {
		case 'refresh-activity':
			doDefault();
			break;
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
