<?php

	/*
	 * Display the E-Maj environment characteristics.
	 */

	// Include application functions.
	include_once('./libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';

	// If the Erase Parameters button (that is only available for test) has been hit, forget session data and behave as an initial page display with the form only
	if (isset($_REQUEST['erase_parameters'])) {
		unset($_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']], $_REQUEST['groups-include']);
	}

	/**
	 * Show the changes activity.
	 */
	function doDefault($isAutoRefresh = false) {
		global $misc, $lang, $data, $emajdb, $conf, $rq;

		$misc->printHeader('database', 'database', 'emajactivity');

		$misc->printTitle($lang['strchangesactivity']);

		$activityToBeDisplayed = isset($_REQUEST['groups-include'])
							  || isset($_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousRequest']);

		// Prepare the input values displayed in the form.
		$previousRequest = (isset($_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousRequest'])) ?
							$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousRequest'] : '';

		$rq['groups-include'] = (isset($_REQUEST['groups-include'])) ? $_REQUEST['groups-include'] :
									((isset($previousRequest['groups-include'])) ? $previousRequest['groups-include'] : '');
		$rq['tables-include'] = (isset($_REQUEST['tables-include'])) ? $_REQUEST['tables-include'] :
									((isset($previousRequest['tables-include'])) ? $previousRequest['tables-include'] : '');
		$rq['sequences-include'] = (isset($_REQUEST['sequences-include'])) ? $_REQUEST['sequences-include'] :
									((isset($previousRequest['sequences-include'])) ? $previousRequest['sequences-include'] : '');
		$rq['groups-exclude'] = (isset($_REQUEST['groups-exclude'])) ? $_REQUEST['groups-exclude'] :
									((isset($previousRequest['groups-exclude'])) ? $previousRequest['groups-exclude'] : '');
		$rq['tables-exclude'] = (isset($_REQUEST['tables-exclude'])) ? $_REQUEST['tables-exclude'] :
									((isset($previousRequest['tables-exclude'])) ? $previousRequest['tables-exclude'] : '');
		$rq['sequences-exclude'] = (isset($_REQUEST['sequences-exclude'])) ? $_REQUEST['sequences-exclude'] :
									((isset($previousRequest['sequences-exclude'])) ? $previousRequest['sequences-exclude'] : '');
		$rq['max-groups'] = (isset($_REQUEST['max-groups'])) ? $_REQUEST['max-groups'] :
								((isset($previousRequest['max-groups'])) ? $previousRequest['max-groups'] : 5);
		$rq['max-tables'] = (isset($_REQUEST['max-tables'])) ? $_REQUEST['max-tables'] :
								((isset($previousRequest['max-tables'])) ? $previousRequest['max-tables'] : 20);
		$rq['max-sequences'] = (isset($_REQUEST['max-sequences'])) ? $_REQUEST['max-sequences'] :
								((isset($previousRequest['max-sequences'])) ? $previousRequest['max-sequences'] : 20);
		$rq['sort'] = (isset($_REQUEST['sort'])) ? $_REQUEST['sort'] :
						((isset($previousRequest['sort'])) ? $previousRequest['sort'] : 'previous-mark');

		// Form
		echo "<form id=\"activity_form\" action=\"activity.php?action=refresh-activity&amp;{$misc->href}\"";
		echo "  method=\"post\" enctype=\"multipart/form-data\">\n";
		echo "<div id=\"activity_form\" class=\"form-container-5c\">\n";

		// Header row
		echo "\t<div class=\"form-header\"><button id=\"resetButton\" class=\"filterreset\" onclick=\"javascript:resetForm();\" />{$lang['strreset']}</button></div>\n";
		echo "\t<div></div>\n";
		echo "\t<div class=\"form-header\">{$lang['strgroups']}</div>\n";
		echo "\t<div class=\"form-header\">{$lang['strtables']}</div>\n";
		echo "\t<div class=\"form-header\">{$lang['strsequences']}</div>\n";

		// Include regexp
		echo "\t<div class=\"form-label\">{$lang['strincluderegexp']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strincluderegexphelp']}\"/></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"groups-include\" size=\"20\" value=\"{$rq['groups-include']}\"></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"tables-include\" size=\"25\" value=\"{$rq['tables-include']}\"></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"sequences-include\" size=\"25\" value=\"{$rq['sequences-include']}\"></div>\n";

		// Exclude regexp
		echo "\t<div class=\"form-label\">{$lang['strexcluderegexp']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strexcluderegexphelp']}\"/></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"groups-exclude\" size=\"20\" value=\"{$rq['groups-exclude']}\"></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"tables-exclude\" size=\"25\" value=\"{$rq['tables-exclude']}\"></div>\n";
		echo "\t<div class=\"form-input\"><input name=\"sequences-exclude\" size=\"25\" value=\"{$rq['sequences-exclude']}\"></div>\n";

		// Size limits
		echo "\t<div class=\"form-label\">{$lang['strmaxrows']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmaxrowshelp']}\"/></div>\n";
		echo "\t<div class=\"form-input\"><input type=\"number\" name=\"max-groups\" min=\"0\" value=\"{$rq['max-groups']}\"></div>\n";
		echo "\t<div class=\"form-input\"><input type=\"number\" name=\"max-tables\" min=\"0\" value=\"{$rq['max-tables']}\"></div>\n";
		echo "\t<div class=\"form-input\"><input type=\"number\" name=\"max-sequences\" min=\"0\" value=\"{$rq['max-sequences']}\"></div>\n";

		// Sort criteria
		echo "\t<div class=\"form-label\">{$lang['strmainsortcriteria']}</div>\n";
		echo "\t<div class=\"form-comment\"><img src=\"{$misc->icon('Info')}\" alt=\"info\" title=\"{$lang['strmainsortcriteriahelp']}\"/></div>\n";
		if ($rq['sort'] == 'latest-mark') {
			$latestMarkChecked = 'checked'; $previousDisplayChecked = '';
		} else {
			$latestMarkChecked = ''; $previousDisplayChecked = 'checked';
		}
		echo "\t<div class=\"sort-criteria-radio-buttons\">{$lang['strchangessince']}\n";
		echo "\t\t<input type=\"radio\" name=\"sort\" value=\"latest-mark\" {$latestMarkChecked}>{$lang['strlatestmark']}\n";
		echo "\t\t<input type=\"radio\" name=\"sort\" value=\"previous-display\" {$previousDisplayChecked}>{$lang['strpreviousdisplay']}";
		echo "\t</div>\n";

		echo"</div>\n";
		echo $misc->form;

		// Buttons line
		$buttonValue = ($activityToBeDisplayed) ? $lang['strrefresh'] : $lang['strdisplay'];
		echo "<div class=\"actionslist\">\n";
		echo "\t<input type=\"submit\" name=\"refresh\" id=\"refreshBt\" value=\"$buttonValue\" />\n";

		// The autorefresh toogle button, if relevant (timer > 0)
		// The timer comes from the config.inc.php file (10 seconds if not found)
		$autoRefreshTimeout = (isset($conf['auto_refresh'])) ? $conf['auto_refresh'] : 10;
		if ($autoRefreshTimeout > 0) {
			$refreshUrl = "activity.php?action=auto-refresh-activity&amp;{$misc->href}";
			$checked = ($isAutoRefresh) ? 'checked' : '';
			$disabledClass = ($activityToBeDisplayed) ? '' : 'autorefresh-disable';
			$disabledAttr = ($activityToBeDisplayed) ? '' : 'disabled';
			echo "\t<span class=\"autorefresh-label {$disabledClass}\">{$lang['strautorefresh']}</span>\n";
			echo "\t<label class=\"switch\">\n";
			echo "\t\t<input type=\"checkbox\" name=\"autorefresh\" {$checked} {$disabledAttr} onchange=\"toggleAutoRefresh(this, '" . htmlspecialchars_decode($refreshUrl) . "')\">\n";
			echo "\t\t<span class=\"slider\"></span>";
			echo "\t</label>";
			$helpMsg = sprintf($lang['strautorefreshhelp'], $autoRefreshTimeout);
			echo "\t<img src=\"{$misc->icon('Info')}\" alt=\"info\" class=\"autorefresh-help\" title=\"$helpMsg\"/>";
		}
		// This button is for test only. It behave like the submit but erase the emajStat array from $_SESSION
		// as if the page is called for the fist time in the PHP session. Remove the comment if you need it.
#		echo "\t<input type=\"submit\" name=\"erase_parameters\" value=\"Erase parameters\" />\n";

		echo "</div></form>\n";

		// Add onchange event on each form input
		echo "\t\t<script>setOnchangeEvent();</script>\n";

		// Schedule the page reload when auto-refresh is on.
		if ($autoRefreshTimeout > 0 && $isAutoRefresh) {
			$misc->schedulePageReload($refreshUrl, $autoRefreshTimeout);
		}

		// Display the activity if parameters are known
		if ($activityToBeDisplayed) {
			displayActivity($isAutoRefresh);
		}
	}

	/**
	 * Display the changes statistics.
	 */
	function displayActivity($isAutoRefresh) {
		global $misc, $lang, $emajdb, $rq;
		include_once('classes/ArrayRecordSet.php');

		// Get the E-Maj statistics
		list($errorTrapped,
			 $currentTime, $lastRefreshIntervalStr, $globalChanges, $globalCps,
			 $globalCounters, $loggingGroups, $loggedTables, $loggedSequences) = getEmajStat($isAutoRefresh);

		// If an error has been trapped at db side, just warn and stop the display
		if ($errorTrapped) {
			echo "<p>{$lang['strerrortrapped']}</p>";
		} else {

			// Displayed tables have no actions.
			$actions = array();

			// Display the global data line
			$misc->printSubtitle($lang['strglobalactivity']);

			$columns = array(
				'since' => array(
					'title' => $lang['strsince'],
					'field' => $lastRefreshIntervalStr,
					'type'  => 'numeric',
				),
				'changes_since_previous' => array(
					'title' => $lang['strchanges'],
					'field' => $globalChanges,
					'type'  => 'numeric',
				),
				'cps_since_previous' => array(
					'title' => $lang['strchangespersecond'],
					'field' => $globalCps,
					'type'  => 'numeric',
				),
			);

			$globalData = new ArrayRecordSet(array(''));	// Empty array because all fields to display are literals
			$misc->printTable($globalData, $columns, $actions, 'activity-global');

			// Display the tables groups
			if ($rq['max-groups'] > 0) {
				$misc->printSubtitle($lang['strlogginggroupstitle'], "(x {$globalCounters['nb_logging_groups']}/{$globalCounters['nb_groups']})");

				$loggingGroupsArs = new ArrayRecordSet(array_slice($loggingGroups, 0, $rq['max-groups']));

				$columns = array(
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('group'),
						'url'   => "emajgroups.php?action=show_group&amp;{$misc->href}&amp;",
						'vars'  => array('group' => 'group'),
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
						'upper_title' => $lang['strsincelatestmark'],
						'upper_title_colspan' => 2,
						'title' => $lang['strchanges'],
						'field' => field('changes_since_mark'),
						'type'  => 'numeric',
					),
					'cps_since_mark' => array(
						'title' => $lang['strchangespersecond'],
						'field' => field('cps_since_mark'),
						'type'  => 'numeric',
					),
					'changes_since_previous' => array(
						'upper_title' => $lang['strsincepreviousdisplay'],
						'upper_title_colspan' => 2,
						'title' => $lang['strchanges'],
						'field' => field('changes_since_previous'),
						'type'  => 'numeric',
					),
					'cps_since_previous' => array(
						'title' => $lang['strchangespersecond'],
						'field' => field('cps_since_previous'),
						'type'  => 'numeric',
					),
				);

				$misc->printTable($loggingGroupsArs, $columns, $actions, 'activity-groups', $lang['strnogroupselected']);
			}

			// Display the logged tables
			if ($rq['max-tables'] > 0) {
				$misc->printSubtitle($lang['strtablesinlogginggroups'], "(x {$globalCounters['nb_logged_tables']}/{$globalCounters['nb_tables']})");

				$loggedTablesArs = new ArrayRecordSet(array_slice($loggedTables, 0, $rq['max-tables']));

				$columns = array(
					'schema' => array(
						'title' => $lang['strschema'],
						'field' => field('rel_schema'),
						'url'   => "schemas.php?action=list_schemas&amp;{$misc->href}&amp;",
						'vars'  => array('schema' => 'rel_schema'),
					),
					'table' => array(
						'title' => $lang['strtable'],
						'field' => field('rel_tblseq'),
						'url'	=> "redirect.php?subject=table&amp;{$misc->href}&amp;",
						'vars'  => array('schema' => 'rel_schema', 'table' => 'rel_tblseq'),
					),
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('rel_group'),
						'url'   => "emajgroups.php?action=show_group&amp;{$misc->href}&amp;",
						'vars'  => array('group' => 'rel_group'),
					),
					'changes_since_mark' => array(
						'upper_title' => $lang['strsincelatestmark'],
						'upper_title_colspan' => 2,
						'title' => $lang['strchanges'],
						'field' => field('changes_since_mark'),
						'type'  => 'numeric',
					),
					'cps_since_mark' => array(
						'title' => $lang['strchangespersecond'],
						'field' => field('cps_since_mark'),
						'type'  => 'numeric',
					),
					'changes_since_previous' => array(
						'upper_title' => $lang['strsincepreviousdisplay'],
						'upper_title_colspan' => 2,
						'title' => $lang['strchanges'],
						'field' => field('changes_since_previous'),
						'type'  => 'numeric',
					),
					'cps_since_previous' => array(
						'title' => $lang['strchangespersecond'],
						'field' => field('cps_since_previous'),
						'type'  => 'numeric',
					),
				);

				$misc->printTable($loggedTablesArs, $columns, $actions, 'activity-tables', $lang['strnotableselected']);
			}

			// Display the logged sequences
			if ($rq['max-sequences'] > 0) {
				$misc->printSubtitle($lang['strsequencesinlogginggroups'], "(x {$globalCounters['nb_logged_sequences']}/{$globalCounters['nb_sequences']})");

				$loggedSequencesArs = new ArrayRecordSet(array_slice($loggedSequences, 0, $rq['max-sequences']));
		
				$columns = array(
					'schema' => array(
						'title' => $lang['strschema'],
						'field' => field('rel_schema'),
						'url'   => "schemas.php?action=list_schemas&amp;{$misc->href}&amp;",
						'vars'  => array('schema' => 'rel_schema'),
					),
					'sequence' => array(
						'title' => $lang['strsequence'],
						'field' => field('rel_tblseq'),
						'url'	=> "redirect.php?subject=sequence&amp;{$misc->href}&amp;",
						'vars'  => array('schema' => 'rel_schema', 'sequence' => 'rel_tblseq'),
					),
					'group' => array(
						'title' => $lang['strgroup'],
						'field' => field('rel_group'),
						'url'   => "emajgroups.php?action=show_group&amp;{$misc->href}&amp;",
						'vars'  => array('group' => 'rel_group'),
					),
					'changes_since_mark' => array(
						'upper_title' => $lang['strsincelatestmark'],
						'upper_title_colspan' => 2,
						'title' => $lang['strchanges'],
						'field' => field('changes_since_mark'),
						'type'  => 'numeric',
					),
					'cps_since_mark' => array(
						'title' => $lang['strchangespersecond'],
						'field' => field('cps_since_mark'),
						'type'  => 'numeric',
					),
					'changes_since_previous' => array(
						'upper_title' => $lang['strsincepreviousdisplay'],
						'upper_title_colspan' => 2,
						'title' => $lang['strchanges'],
						'field' => field('changes_since_previous'),
						'type'  => 'numeric',
					),
					'cps_since_previous' => array(
						'title' => $lang['strchangespersecond'],
						'field' => field('cps_since_previous'),
						'type'  => 'numeric',
					),
				);
		
				$misc->printTable($loggedSequencesArs, $columns, $actions, 'activity-sequencesables', $lang['strnosequenceselected']);
			}
		}
	}

	/**
	 * Compute the changes statistics.
	 */
	function getEmajStat($isAutoRefresh) {
		global $emajdb, $rq;

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
			$areRegExpFiltersModified = ! $isAutoRefresh &&
										($rq['groups-include'] != $previousRequest['groups-include']
										 || $rq['groups-exclude'] != $previousRequest['groups-exclude']
										 || $rq['tables-include'] != $previousRequest['tables-include']
										 || $rq['tables-exclude'] != $previousRequest['tables-exclude']
										 || $rq['sequences-include'] != $previousRequest['sequences-include']
										 || $rq['sequences-exclude'] != $previousRequest['sequences-exclude']);
		} else {
			$previousEpoch = 0;
			$previousEmajTimeStampTimeIdSeq = 0;
			$previousEmajGlobalSeq = 0;
			$areRegExpFiltersModified = 1;
		}

		// Get the timestamp and the sequences last_value
#$begin = hrtime(true);
		$seqRs = $emajdb->emajStatGetSeqLastVal($rq['groups-include'], $rq['groups-exclude'],
												$rq['tables-include'], $rq['tables-exclude'],
												$rq['sequences-include'], $rq['sequences-exclude']);
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
			$rs = $emajdb->emajStatGetGroups($rq['groups-include'], $rq['groups-exclude']);
			$loggingGroups = array();
			while (!$rs->EOF) {
				array_push($loggingGroups, $rs->fields);
				$rs->moveNext();
			}
			// Get tables from the database
			$rs = $emajdb->emajStatGetTables($rq['groups-include'], $rq['groups-exclude'], $rq['tables-include'], $rq['tables-exclude']);
			$loggedTables = array();
			while (!$rs->EOF) {
				array_push($loggedTables, $rs->fields);
				$rs->moveNext();
			}
			// Get sequences from the database
			$rs = $emajdb->emajStatGetSequences($rq['groups-include'], $rq['groups-exclude'], $rq['sequences-include'], $rq['sequences-exclude']);
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
			$lastRefreshIntervalStr = sprintf('%.3f s', $lastRefreshInterval);
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
		if (isset($rq['sort']) && $rq['sort'] == 'latest-mark') {
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
		$_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']]['previousRequest'] = $rq;

		// And return 1 flag, 4 single values and 4 arrays to display
		return array(false,
					 $currentTime, $lastRefreshIntervalStr, $globalChanges, $globalCps,
					 $globalCounters, $loggingGroups, $loggedTables, $loggedSequences);
	}

	// redirect to the emajenvir.php page if the emaj extension is not installed or accessible or is too old
	if (!(isset($emajdb) && $emajdb->isEnabled() && $emajdb->isAccessible()
			&& $emajdb->getNumEmajVersion() >= $oldest_supported_emaj_version_num)) {
		unset($_SESSION['emajStat'][$_REQUEST['server']][$_REQUEST['database']], $_REQUEST['groups-include']);
		header('Location: emajenvir.php?' . $_SERVER["QUERY_STRING"]);
	}

	$scripts = "<script src=\"js/activity.js\"></script>";
	$misc->printHtmlHeader($lang['strchangesactivity'], $scripts, 'activity');
	$misc->printBody();

	switch ($action) {
		case 'refresh-activity':
			doDefault();
			break;
		case 'auto-refresh-activity':
			doDefault(true);
			break;
		default:
			doDefault();
			break;
	}

	$misc->printFooter();

?>
